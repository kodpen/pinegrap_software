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

// we allow users and managers to use this script, even though it is in the design tab, however they can't update the name
validate_area_access($user, 'user');

// if user has a user role and if they do not have access to this ad region, output error
if (($user['role'] == 3) && (in_array($_REQUEST['id'], get_items_user_can_edit('ad_regions', $user['id'])) == FALSE)) {
    // get ad region name
    $query = "SELECT name FROM ad_regions WHERE id = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    log_activity("access denied because user does not have access to edit the ad region (" . $row['name'] . ")", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('edit_ad_region');

// assume that there are no ads assigned to this ad region until we find out otherwise
$ads_assigned_to_this_ad_region = false;

// determine if there are ads assigned to this ad region
$query = "SELECT id FROM ads WHERE ad_region_id = '" . escape($_REQUEST['id']) . "' LIMIT 1";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if there is a result, then there are ads assigned to this ad region, so remember that
if (mysqli_num_rows($result) > 0) {
    $ads_assigned_to_this_ad_region = true;
}

// if the form has not just been submitted
if (!$_POST) {
    // get ad region data
    $query =
        "SELECT
            name,
            display_type,
            transition_type,
            transition_duration,
            slideshow,
            slideshow_interval,
            slideshow_continuous
        FROM ad_regions
        WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $name = $row['name'];
    
    // if the form has not been submitted yet, pre-populate fields with data
    if ($liveform->field_in_session('id') == false) {
        $display_type = $row['display_type'];
        $transition_type = $row['transition_type'];
        $transition_duration = $row['transition_duration'];
        $slideshow = $row['slideshow'];
        $slideshow_interval = $row['slideshow_interval'];
        $slideshow_continuous = $row['slideshow_continuous'];
        
        // if the transition duration is 0, then set it to empty string
        if ($transition_duration == 0) {
            $transition_duration = '';
        }
        
        // set field values
        $liveform->assign_field_value('name', $name);
        $liveform->assign_field_value('display_type', $display_type);
        $liveform->assign_field_value('transition_type', $transition_type);
        $liveform->assign_field_value('transition_duration', $transition_duration);
        $liveform->assign_field_value('slideshow', $slideshow);
        $liveform->assign_field_value('slideshow_interval', $slideshow_interval);
        $liveform->assign_field_value('slideshow_continuous', $slideshow_continuous);
    }
    
    $output_name = h($name);
    
    $output_subheading = '';
    
    // if the user is a designer or above, then prepare to display subheading and name field
    if ($user['role'] <= 1) {
        $output_subheading = '<div class="subheading">Page Style Body Tag: &lt;ad&gt;' . $output_name . '&lt;/ad&gt;</div>';
        
        $output_name_field_or_value = $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100'));
    
    // else the user is a manager, so prepare to just display name value,
    // because we don't want a manager to have access to update the name
    } else {
        $output_name_field_or_value = $output_name;
    }
    
    // if the display type has not been set or is static, then hide dynamic rows
    if ($liveform->get_field_value('display_type') == 'static') {
        $output_transition_type_row_style = ' style="display: none"';
        $output_transition_duration_row_style = ' style="display: none"';
        $output_slideshow_row_style = ' style="display: none"';
        $output_slideshow_interval_row_style = ' style="display: none"';
        $output_slideshow_continuous_row_style = ' style="display: none"';
    
    // else the display type is dynamic, so show dynamic rows
    } else {
        $output_transition_type_row_style = '';
        $output_transition_duration_row_style = '';
        $output_slideshow_row_style = '';
        
        // if slideshow is enabled, then show slideshow rows
        if ($liveform->get_field_value('slideshow') == 1) {
            $output_slideshow_interval_row_style = '';
            $output_slideshow_continuous_row_style = '';
            
        // else slideshow is disabled, so hide slideshow rows
        } else {
            $output_slideshow_interval_row_style = ' style="display: none"';
            $output_slideshow_continuous_row_style = ' style="display: none"';
        }
    }
    
    $output_delete_button = '';
    
    // if the user is a designer or above, then prepare to output delete button
    if ($user['role'] <= 1) {
        // if there are ads assigned to this ad region, then prepare inactive delete button with alert
        if ($ads_assigned_to_this_ad_region == true) {
            $output_delete_button = '&nbsp;&nbsp;&nbsp<input type="button" value="Delete" class="delete" onclick="alert(\'Please delete or remove all ads from this ad region before deleting this ad region.\')" />';
            
        // else there are no ads assigned to this ad region, so prepare active delete button with warning
        } else {
            $output_delete_button = '&nbsp;&nbsp;&nbsp<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This ad region will be permanently deleted.\')" />';
        }
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . $output_name . '</h1>
            ' . $output_subheading . '
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Ad Region</h1>
            <div class="subheading">Update this ad region which displays rotating ad content.</div>
            <form name="form" action="edit_ad_region.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Ad Region Name</h2></th>
                    </tr>
                    <tr>
                        <td>Ad Region Name:</td>
                        <td>' . $output_name_field_or_value . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Ad Region Behavior</h2></th>
                    </tr>
                    <tr>
                        <td>Display Type:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'display_type', 'id'=>'static', 'value'=>'static', 'class'=>'radio', 'onclick'=>'show_or_hide_display_type(\'static\')')) . '<label for="static"> Static (i.e. display one ad per page view)</label><br />
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'display_type', 'id'=>'dynamic', 'value'=>'dynamic', 'class'=>'radio', 'onclick'=>'show_or_hide_display_type(\'dynamic\')')) . '<label for="dynamic"> Dynamic (i.e. display multiple ads per page view)</label>
                        </td>
                    </tr>
                    <tr id="transition_type_row"' . $output_transition_type_row_style . '">
                        <td style="padding-left: 2em">Transition Type:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'transition_type', 'id'=>'slide', 'value'=>'slide', 'class'=>'radio')) . '<label for="slide"> Slide</label><br />
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'transition_type', 'id'=>'fade', 'value'=>'fade', 'class'=>'radio')) . '<label for="fade"> Fade</label>
                        </td>
                    </tr>
                    <tr id="transition_duration_row"' . $output_transition_duration_row_style . '">
                        <td style="padding-left: 2em">Transition Duration:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'transition_duration', 'size'=>'5', 'maxlength'=>'4')) . ' milliseconds (leave blank for default, 1 for instant, 1000 for 1 second)</td>
                    </tr>
                    <tr id="slideshow_row"' . $output_slideshow_row_style . '">
                        <td style="padding-left: 2em"><label for="slideshow">Enable Autoplay:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'slideshow', 'id'=>'slideshow', 'id'=>'slideshow', 'value'=>'1', 'class'=>'checkbox', 'onclick'=>'show_or_hide_slideshow()')) . '</td>
                    </tr>
                    <tr id="slideshow_interval_row"' . $output_slideshow_interval_row_style . '">
                        <td style="padding-left: 4em">Interval:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'slideshow_interval', 'size'=>'4', 'value'=>'3', 'maxlength'=>'3')) . ' seconds</td>
                    </tr>
                    <tr id="slideshow_continuous_row"' . $output_slideshow_continuous_row_style . '">
                        <td style="padding-left: 4em"><label for="slideshow_continuous">Play Continuously:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'slideshow_continuous', 'id'=>'slideshow_continuous', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />' . $output_delete_button . '
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // if the user selected to delete the ad region
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        // if the user is a manager who does not have access to delete the ad region,
        // then mark error and send user back to previous screen
        if ($user['role'] > 1) {
            $liveform->mark_error('', 'You do not have access to delete ad regions.');
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_ad_region.php?id=' . $liveform->get_field_value('id') . '&send_to=' . urlencode($liveform->get_field_value('send_to')));
            exit();
        }
        
        // if there are ads assigned to this ad region,
        // then mark error and send user back to previous screen
        if ($ads_assigned_to_this_ad_region == true) {
            $liveform->mark_error('', 'Please delete or remove all ads from this ad region before deleting this ad region.');
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_ad_region.php?id=' . $liveform->get_field_value('id') . '&send_to=' . urlencode($liveform->get_field_value('send_to')));
            exit();
        }        
        
        // delete ad region
        $query = "DELETE FROM ad_regions WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete users_ad_regions_xref records
        $query = "DELETE FROM users_ad_regions_xref WHERE ad_region_id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('ad region (' . $liveform->get_field_value('name') . ') was deleted', $_SESSION['sessionusername']);
        
        // if there is a send to, then forward user to send to
        if ($liveform->get_field_value('send_to') != '') {
            header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
            
        // else there is not a send to, so prepare notice and send user to view ad regions screen
        } else {
            $liveform_view_ad_regions = new liveform('view_regions');
            $liveform_view_ad_regions->add_notice('The ad region has been deleted.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_ad_regions');
        }
        
        $liveform->remove_form();
        
        exit();
        
    // else the user selected to save the ad region
    } else {
        $sql_name = "";
        $name = '';
        
        // if the user is a designer or above, then validate name field and prepare to update it
        if ($user['role'] <= 1) {
            $liveform->validate_required_field('name', 'Name is required.');
            
            // if there is not already an error for the name field, then check that valid characters were entered for name field
            if ($liveform->check_field_error('name') == false) {
                // Get previous name in order to determine if we should allow underscores or not.
                // We don't want to allow ad regions to be created or new ad region names to be set with underscores,
                // because the theme designer does not support underscores in names.  We are going to allow
                // underscores as long as it was set in the past in order to prevent their styles or themes
                // from being messed up.
                $query = "SELECT name FROM ad_regions WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $previous_name = $row['name'];
                
                // If the previous name contains underscores, then set regex code to allow underscores.
                if (mb_strpos($previous_name, '_') !== FALSE) {
                    $regex = '/[^A-Za-z0-9_-]/';
                    $message = 'Please only enter letters, numbers, dashes, and underscores for the name.';

                // Otherwise the previous name does not contain underscores, so set regex code so it does not allow underscores.
                } else {
                    $regex = '/[^A-Za-z0-9-]/';
                    $message = 'Please only enter letters, numbers, or dashes for the name.';
                }

                // If the name is not valid, then add error.
                if (preg_match($regex, $liveform->get_field_value('name')) == 1) {
                    $liveform->mark_error('name', $message);
                }
            }

            // if there is not already an error for the name field, check to see if name is already in use by a different ad region
            if ($liveform->check_field_error('name') == false) {
                $query =
                    "SELECT id
                    FROM ad_regions
                    WHERE
                        (name = '" . escape($liveform->get_field_value('name')) . "')
                        AND (id != '" . escape($liveform->get_field_value('id')) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if the name is already in use by a different ad region, then mark error
                if (mysqli_num_rows($result) > 0) {
                    $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
                }
            }
            
            // if there is an error, forward user back to edit ad region screen
            if ($liveform->check_form_errors() == true) {
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_ad_region.php?id=' . $liveform->get_field_value('id') . '&send_to=' . urlencode($liveform->get_field_value('send_to')));
                exit();
            }
            
            $sql_name = "name = '" . escape($liveform->get_field_value('name')) . "',";
            
            $name = $liveform->get_field_value('name');
            
        // else the user is a manager, so get ad region name for log message
        } else {
            $query = "SELECT name FROM ad_regions WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $name = $row['name'];
        }
        
        // update ad region
        $query =
            "UPDATE ad_regions
            SET
                $sql_name
                display_type = '" . escape($liveform->get_field_value('display_type')) . "',
                transition_type = '" . escape($liveform->get_field_value('transition_type')) . "',
                transition_duration = '" . escape($liveform->get_field_value('transition_duration')) . "',
                slideshow = '" . escape($liveform->get_field_value('slideshow')) . "',
                slideshow_interval = '" . escape($liveform->get_field_value('slideshow_interval')) . "',
                slideshow_continuous = '" . escape($liveform->get_field_value('slideshow_continuous')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('ad region (' . $name . ') was modified', $_SESSION['sessionusername']);
        
        // if there is a send to, then forward user to send to
        if ($liveform->get_field_value('send_to') != '') {
            header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
            
        // else there is not a send to, so prepare notice and send user to view ad regions screen
        } else {
            $liveform_view_regions = new liveform('view_regions');
            $liveform_view_regions->add_notice('The ad region has been saved.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_ad_regions');
        }
        
        $liveform->remove_form();
        
        exit();
    }
}
?>