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
validate_area_access($user, 'manager');

$liveform = new liveform('add_auto_dialog');

// If the form has not been submitted, then output it.
if (!$_POST) {
    $screen = 'create';

    echo output_header();

    require('templates/edit_auto_dialog.php');

    echo output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted so process it.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');
    $liveform->validate_required_field('url', 'URL is required.');

    // If there is not already an error for the name field,
    // and that name is already in use, then output error.
    if (
        (!$liveform->check_field_error('name'))
        && (db_value("SELECT COUNT(*) FROM auto_dialogs WHERE name = '" . e($liveform->get_field_value('name')) . "'") != 0)
    ) {
        $liveform->mark_error('name', 'Sorry, the name that you entered is already in use, so please enter a different name.');
    }
    
    if ($liveform->check_form_errors()) {
        go($_SERVER['PHP_SELF']);
    }

    $page = $liveform->get_field_value('page');

    // If the first character is a slash, then the user
    // has accidentally entered a URL instead of a page name,
    // so correct the mistake and remove the slash for the user.
    if (mb_substr($page, 0, 1) == '/') {
        $page = mb_substr($page, 1);
    }
    
    db(
        "INSERT INTO auto_dialogs (
            name,
            enabled,
            url,
            width,
            height,
            delay,
            frequency,
            page,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . e($liveform->get_field_value('name')) . "',
            '" . e($liveform->get_field_value('enabled')) . "',
            '" . e($liveform->get_field_value('url')) . "',
            '" . e($liveform->get_field_value('width')) . "',
            '" . e($liveform->get_field_value('height')) . "',
            '" . e($liveform->get_field_value('delay')) . "',
            '" . e($liveform->get_field_value('frequency')) . "',
            '" . e($page) . "',
            '" . USER_ID . "',
            UNIX_TIMESTAMP(),
            '" . USER_ID . "',
            UNIX_TIMESTAMP())");

    $auto_dialog_id = mysqli_insert_id(db::$con);
    
    log_activity('auto dialog (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);

    $home_page_name = db_value("SELECT page_name FROM page WHERE page_home = 'yes' ORDER BY page_timestamp DESC LIMIT 1");

    $preview_url = PATH . encode_url_path($home_page_name) . '?preview_auto_dialog=' . $auto_dialog_id;
    
    $liveform_view_auto_dialogs = new liveform('view_auto_dialogs');
    $liveform_view_auto_dialogs->add_notice('The auto dialog has been created, but it has not been enabled yet.  Please <a href="' .  h($preview_url) . '" target="_blank">preview</a> it, and then you may <a href="edit_auto_dialog.php?id=' . $auto_dialog_id . '">edit</a> it to enable it for all visitors.');

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/view_auto_dialogs.php');
}
?>