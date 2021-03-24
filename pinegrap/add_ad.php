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

// if user has a user role and if they do not have access to edit any ad regions, output error
if (($user['role'] == 3) && (count(get_items_user_can_edit('ad_regions', $user['id'])) == 0)) {
    log_activity("access denied because user does not have access to ads", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('add_ad');

// if the form has not been submitted
if (!$_POST) {
    print
        output_header() . '
        <div id="subnav">
            <h1>[new ad]</h1>
        </div>
        <div id="content">
            
            ' . get_wysiwyg_editor_code(array('content_textarea', 'caption')) . '
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Ad</h1>
            <div class="subheading">Create a new ad and assign it to any existing ad region.</div>
            <form action="add_ad.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Ad Name</h2></th>
                    </tr>
                    <tr>
                        <td>Ad Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Insert Ad Content</h2></th>
                    </tr>
                    <tr>
                        <td>Content:</td>
                        <td>' . $liveform->output_field(array('type'=>'textarea', 'id'=>'content_textarea', 'name'=>'content', 'style'=>'width: 600px; height: 300px')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Assign to Ad Region</h2></th>
                    </tr>
                    <tr>
                        <td>Ad Region:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'ad_region_id', 'options'=>get_ad_region_options())) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Dynamic Ad Region Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Label:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'label', 'size'=>'60', 'maxlength'=>'255')) . '</td>
                    </tr>
                    <tr>
                        <td>Sort Order:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'sort_order', 'size'=>'5', 'maxlength'=>'4')) . ' (leave blank for random order)</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Add Caption on Top of Content</h2></th>
                    </tr>
                    <tr>
                        <td>Caption:</td>
                        <td>' . $liveform->output_field(array('type'=>'textarea', 'id'=>'caption', 'name'=>'caption', 'style'=>'width: 600px; height: 200px')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();

    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');
    $liveform->validate_required_field('ad_region_id', 'Ad Region is required.');
    
    // if there is not already an error for the name field, check to see if name is already in use by a different ad
    if ($liveform->check_field_error('name') == false) {
        $query =
            "SELECT id
            FROM ads
            WHERE (name = '" . escape($liveform->get_field_value('name')) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the name is already in use by a different ad, then mark error
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
        }
    }
    
    // if there is not already an error for the ad region field and the user has a user role and if they do not have access to the selected ad region, then do not allow the user to create the ad
    if (($liveform->check_field_error('ad_region_id') == false) && ($user['role'] == 3) && (in_array($liveform->get_field_value('ad_region_id'), get_items_user_can_edit('ad_regions', $user['id'])) == FALSE)) {
        $liveform->mark_error('ad_region_id', 'You do not have access to the selected Ad Region, so please select a different Ad Region.');
    }
    
    // if there is an error, forward user back to add ad screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_ad.php');
        exit();
    }
    
    // create ad
    $query =
        "INSERT ads (
            name,
            content,
            caption,
            ad_region_id,
            label,
            sort_order,
            created_user_id, 
            created_timestamp,
            last_modified_user_id, 
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            '" . escape(prepare_rich_text_editor_content_for_input($liveform->get_field_value('content'))) . "',
            '" . escape(prepare_rich_text_editor_content_for_input($liveform->get_field_value('caption'))) . "',
            '" . escape($liveform->get_field_value('ad_region_id')) . "',
            '" . escape($liveform->get_field_value('label')) . "',
            '" . escape($liveform->get_field_value('sort_order')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('ad (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);
    
    $liveform_view_ads = new liveform('view_ads');
    $liveform_view_ads->add_notice('The ad has been created.');

    // forward user to view ads screen
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_ads.php');
    
    $liveform->remove_form();
}
?>