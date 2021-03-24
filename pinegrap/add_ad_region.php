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
validate_area_access($user, 'designer');

include_once('liveform.class.php');
$liveform = new liveform('add_ad_region');

// if the form has not been submitted
if (!$_POST) {
    // if the display type has not been set or is static, then hide dynamic rows
    if (($liveform->get_field_value('display_type') == '') || ($liveform->get_field_value('display_type') == 'static')) {
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
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new ad region]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Ad Region</h1>
            <div class="subheading">Create an ad region to display rotating ad content.</div>
            <form name="form" action="add_ad_region.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Ad Region Name</h2></th>
                    </tr>
                    <tr>
                        <td>Ad Region Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Ad Region Behavior</h2></th>
                    </tr>
                    <tr>
                        <td>Display Type:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'display_type', 'id'=>'static', 'value'=>'static', 'checked'=>'checked', 'class'=>'radio', 'onclick'=>'show_or_hide_display_type(\'static\')')) . '<label for="static"> Static (i.e. display one ad per page view)</label><br />
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'display_type', 'id'=>'dynamic', 'value'=>'dynamic', 'class'=>'radio', 'onclick'=>'show_or_hide_display_type(\'dynamic\')')) . '<label for="dynamic"> Dynamic (i.e. display multiple ads per page view)</label>
                        </td>
                    </tr>
                    <tr id="transition_type_row"' . $output_transition_type_row_style . '">
                        <td style="padding-left: 2em">Transition Type:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'transition_type', 'id'=>'slide', 'value'=>'slide', 'checked'=>'checked', 'class'=>'radio')) . '<label for="slide"> Slide</label><br />
                            ' . $liveform->output_field(array('type'=>'radio', 'name'=>'transition_type', 'id'=>'fade', 'value'=>'fade', 'class'=>'radio')) . '<label for="fade"> Fade</label>
                        </td>
                    </tr>
                    <tr id="transition_duration_row"' . $output_transition_duration_row_style . '">
                        <td style="padding-left: 2em">Transition Duration:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'transition_duration', 'size'=>'5', 'maxlength'=>'4')) . ' milliseconds (leave blank for default, 1 for instant, 1000 for 1 second)</td>
                    </tr>
                    <tr id="slideshow_row"' . $output_slideshow_row_style . '">
                        <td style="padding-left: 2em"><label for="slideshow">Enable Autoplay:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'slideshow', 'id'=>'slideshow', 'value'=>'1', 'class'=>'checkbox', 'onclick'=>'show_or_hide_slideshow()')) . '</td>
                    </tr>
                    <tr id="slideshow_interval_row"' . $output_slideshow_interval_row_style . '">
                        <td style="padding-left: 4em">Interval:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'slideshow_interval', 'value'=>'10', 'size'=>'4', 'maxlength'=>'3')) . ' seconds</td>
                    </tr>
                    <tr id="slideshow_continuous_row"' . $output_slideshow_continuous_row_style . '">
                        <td style="padding-left: 4em"><label for="slideshow_continuous">Play Continuously:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'slideshow_continuous', 'id'=>'slideshow_continuous', 'value'=>'1', 'checked'=>'checked', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');
    
    // if there is not already an error for the name field, then check that valid characters were entered for name field
    if ($liveform->check_field_error('name') == false) {
        // if the name is not valid, then mark error
        if (preg_match('/[^A-Za-z0-9-]/', $liveform->get_field_value('name')) == 1) {
            $liveform->mark_error('name', 'Please only enter letters, numbers, and dashes for the name.');
        }
    }
    
    // if there is not already an error for the name field, check to see if name is already in use by a different ad region
    if ($liveform->check_field_error('name') == false) {
        $query =
            "SELECT id
            FROM ad_regions
            WHERE (name = '" . escape($liveform->get_field_value('name')) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the name is already in use by a different ad region, then mark error
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
        }
    }
    
    // if there is an error, forward user back to add ad region screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_ad_region.php');
        exit();
    }
    
    // create ad region
    $query =
        "INSERT INTO ad_regions (
            name,
            display_type,
            transition_type,
            transition_duration,
            slideshow,
            slideshow_interval,
            slideshow_continuous,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            '" . escape($liveform->get_field_value('display_type')) . "',
            '" . escape($liveform->get_field_value('transition_type')) . "',
            '" . escape($liveform->get_field_value('transition_duration')) . "',
            '" . escape($liveform->get_field_value('slideshow')) . "',
            '" . escape($liveform->get_field_value('slideshow_interval')) . "',
            '" . escape($liveform->get_field_value('slideshow_continuous')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    log_activity('ad region (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);

    $liveform_view_regions = new liveform('view_regions');
    $liveform_view_regions->add_notice('The ad region has been created.');
    
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_ad_regions');
    
    $liveform->remove_form();
}
?>