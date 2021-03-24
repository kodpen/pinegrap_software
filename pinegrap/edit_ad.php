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

// if the user has a user role, then determine if user has access to the ad region that this ad is in
if ($user['role'] == 3) {
    // get this ad's name and ad region id
    $query = "SELECT name, ad_region_id FROM ads WHERE id = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];
    $ad_region_id = $row['ad_region_id'];
    
    // if the user does not have access to the ad region that this ad is in, then log activity and output error
    if (in_array($ad_region_id, get_items_user_can_edit('ad_regions', $user['id'])) == FALSE) {
        log_activity("access denied because user does not have access to edit ad (" . $name . ")", $_SESSION['sessionusername']);
        output_error('Access denied.');
    }
}

include_once('liveform.class.php');
$liveform = new liveform('edit_ad');

// if the form has not just been submitted
if (!$_POST) {
    // get ad data
    $query =
        "SELECT 
            ads.name,
            ads.content,
            ads.caption,
            ads.ad_region_id,
            ads.label,
            ads.sort_order,
            ad_regions.name as ad_region_name
        FROM ads
        LEFT JOIN ad_regions ON ads.ad_region_id = ad_regions.id
        WHERE ads.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $name = $row['name'];
    $ad_region_name = $row['ad_region_name'];
    
    // if the form has not been submitted yet, pre-populate fields with data
    if ($liveform->field_in_session('id') == false) {
        $content = $row['content'];
        $caption = $row['caption'];
        $ad_region_id = $row['ad_region_id'];
        $label = $row['label'];
        $sort_order = $row['sort_order'];
        
        // if the sort order is 0, then set it to empty string
        if ($sort_order == 0) {
            $sort_order = '';
        }
        
        // Assign values to fields
        $liveform->assign_field_value('name', $name);
        $liveform->assign_field_value('content', prepare_rich_text_editor_content_for_output($content));
        $liveform->assign_field_value('caption', prepare_rich_text_editor_content_for_output($caption));
        $liveform->assign_field_value('ad_region_id', $ad_region_id);
        $liveform->assign_field_value('label', $label);
        $liveform->assign_field_value('sort_order', $sort_order);
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($name) . '</h1>
            <div class="subheading">Ad Region: ' . h($ad_region_name) . '</div>
        </div>
        <div id="content">
            
            ' . get_wysiwyg_editor_code(array('content_textarea', 'caption')) . '
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Ad</h1>
            <div class="subheading">Update this ad and assign it to any existing ad region.</div>
            <form action="edit_ad.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Ad Name</h2></th>
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
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This ad will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // if the user selected to delete the ad
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        // delete ad
        $query = "DELETE FROM ads WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('ad (' . $liveform->get_field_value('name') . ') was deleted', $_SESSION['sessionusername']);
        
        // if there is a send to, then forward user to send to
        if ($liveform->get_field_value('send_to') != '') {
            header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
            
        // else there is not a send to, so prepare notice and send user to view ads screen
        } else {
            $liveform_view_ads = new liveform('view_ads');
            $liveform_view_ads->add_notice('The ad has been deleted.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_ads.php');
        }
        
        $liveform->remove_form();
        
        exit();
        
    // else the user selected to save the ad region
    } else {
        $liveform->validate_required_field('name', 'Name is required.');
        $liveform->validate_required_field('ad_region_id', 'Ad Region is required.');
        
        // if there is not already an error for the name field, check to see if name is already in use by a different ad
        if ($liveform->check_field_error('name') == false) {
            $query =
                "SELECT id
                FROM ads
                WHERE
                    (name = '" . escape($liveform->get_field_value('name')) . "')
                    AND (id != '" . escape($liveform->get_field_value('id')) . "')";
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
        
        // if there is an error, forward user back to edit ad screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_ad.php?id=' . $liveform->get_field_value('id') . '&send_to=' . urlencode($liveform->get_field_value('send_to')));
            exit();
        }
        
        // update ad
        $query =
            "UPDATE ads
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                content = '" . escape(prepare_rich_text_editor_content_for_input($liveform->get_field_value('content'))) . "',
                caption = '" . escape(prepare_rich_text_editor_content_for_input($liveform->get_field_value('caption'))) . "',
                ad_region_id = '" . escape($liveform->get_field_value('ad_region_id')) . "',
                label = '" . escape($liveform->get_field_value('label')) . "',
                sort_order = '" . escape($liveform->get_field_value('sort_order')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('ad (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        // if there is a send to, then forward user to send to
        if ($liveform->get_field_value('send_to') != '') {
            header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
            
        // else there is not a send to, so prepare notice and send user to view ads screen
        } else {
            $liveform_view_ads = new liveform('view_ads');
            $liveform_view_ads->add_notice('The ad has been saved.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_ads.php');
        }
        
        $liveform->remove_form();
        
        exit();
    }
}
?>