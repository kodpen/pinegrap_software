<?php

/**
 *
 * liveSite - Enterprise Website Platform
 *
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

include('init.php');
$user = validate_user();
validate_area_access($user, 'user');

validate_token_field();

include_once('liveform.class.php');
$liveform = new liveform('allow_or_disallow_new_comments', $_POST['page_id']);
$liveform->add_fields_to_session();

// get values from form
$send_to = $liveform->get_field_value('send_to');
$page_id = $liveform->get_field_value('page_id');
$item_id = $liveform->get_field_value('item_id');
$item_type = $liveform->get_field_value('item_type');
$action = $liveform->get_field_value('action');

// get page properties
$query =
    "SELECT
        page_name,
        page_folder as folder_id
    FROM page
    WHERE page_id = '" . escape($page_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$page_name = $row['page_name'];
$folder_id = $row['folder_id'];

// if user does not have access then output error
if (check_edit_access($folder_id) == false) {
    log_activity("access denied to allow or disallow new comments for an item for page ($page_name) because user does not have access to modify folder that the page is in", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

// if action is allow, then prepare value for database
if ($action == 'allow') {
    $allow_new_comments = 1;
    
// else action is disallow, so prepare different value for database
} else {
    $allow_new_comments = 0;
}

// if this is just for a page, not an item, then just update the setting for the page
if ($item_id == 0) {
    $query = "UPDATE page SET comments_allow_new_comments = '$allow_new_comments' WHERE page_id = '" . escape($page_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
// else this is for an item, so add setting for item
} else {
    // delete any existing records in the database for this page and item
    $query = 
        "DELETE FROM allow_new_comments_for_items
        WHERE
            (page_id = '" . escape($page_id) . "')
            AND (item_id = '" . escape($item_id) . "')
            AND (item_type = '" . escape($item_type) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // add record in database in order to store whether new comments are allowed or not for this item and page
    $query = 
        "INSERT INTO allow_new_comments_for_items (
            page_id,
            item_id,
            item_type,
            allow_new_comments)
         VALUES (
            '" . escape($page_id) . "',
            '" . escape($item_id) . "',
            '" . escape($item_type) . "',
            '" . $allow_new_comments . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}
    
// remove form so that we get fresh values when we take the visitor back to the previous page
$liveform->remove_form();

// send user back to previous page
header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_allow_or_disallow_new_comments');
?>