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
$liveform = new liveform('add_menu');

// if the form has not been submitted
if (!$_POST) {
    // Set options for First and Second Level Pop-up Positions
    $popup_position_options = 
        array(
            'Top' => 'Top',
            'Bottom' => 'Bottom',
            'Left' => 'Left',
            'Right' => 'Right');
    
    // if the form has not been submitted yet, then set default values
    if ($liveform->field_in_session('name') == false) {
        $liveform->assign_field_value('effect', 'Pop-up');
        $liveform->assign_field_value('first_level_popup_position', 'Bottom');
        $liveform->assign_field_value('second_level_popup_position', 'Right');
    }
    
    // find out if popup position rows and header should be visible or not
    if ($liveform->get_field_value('effect') == 'Pop-up') {
        $pop_up_properties_row_style = '';
        $first_level_popup_position_row_style = '';
        $second_level_popup_position_row_style = '';
    } else {
        $pop_up_properties_row_style = 'display: none';
        $first_level_popup_position_row_style = 'display: none';
        $second_level_popup_position_row_style = 'display: none';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new menu]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Menu</h1>
            <div class="subheading">Create a shared menu that can be added to any page style and managed by any site manager.</div>
            <form name="form" action="add_menu.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Menu Name</h2></th>
                    </tr>
                    <tr>
                        <td>Menu Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Submenu Effect</h2></th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td style="width: 33%; padding-right: 2em">
                                        <div style="white-space: nowrap; margin-bottom: 1em">' . $liveform->output_field(array('type'=>'radio', 'id'=>'effect_popup', 'name'=>'effect', 'value'=>'Pop-up', 'class'=>'radio', 'onclick'=>'show_or_hide_effect(\'Pop-up\')')) . '<label for="effect_popup"> Pop-up</label></div>
                                        <img src="images/menu_effect_popup.png" width="71" height="75" alt="Pop-up diagram" title="" />
                                    </td>
                                    <td style="width: 33%; padding-right: 2em">
                                        <div style="white-space: nowrap; margin-bottom: 1em">' . $liveform->output_field(array('type'=>'radio', 'id'=>'effect_accordion', 'name'=>'effect', 'value'=>'Accordion', 'class'=>'radio', 'onclick'=>'show_or_hide_effect(\'Accordion\')')) . '<label for="effect_accordion"> Accordion</label></div>
                                        <img src="images/menu_effect_accordion.png" width="56" height="75" alt="Accordion diagram" title="" />
                                    </td>
                                    <td style="width: 33%">
                                        <div style="white-space: nowrap; margin-bottom: 1em">' . $liveform->output_field(array('type'=>'radio', 'id'=>'effect_none', 'name'=>'effect', 'value'=>'', 'class'=>'radio', 'onclick'=>'show_or_hide_effect(\'\')')) . '<label for="effect_none"> None</label></div>
                                        <img src="images/menu_effect_none.png" width="56" height="75" alt="None diagram" title="" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr id="popup_properties_heading_row" style="' . $pop_up_properties_row_style . '">
                        <th colspan="2"><h2>Pop-up Properties</h2></th>
                    </tr>
                    <tr id="first_level_popup_position_row" style="' . $first_level_popup_position_row_style . '">
                        <td>First Expand Menu:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'first_level_popup_position', 'options'=>$popup_position_options)) . '</td>
                    </tr>
                    <tr id="second_level_popup_position_row" style="' . $second_level_popup_position_row_style . '">
                        <td>Then Expand Menu:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'second_level_popup_position', 'options'=>$popup_position_options)) . '</td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2>One or More Custom Classes for the Menu</h2></th>
                    </tr>
                    
                    <tr>
                        <td style="vertical-align: top">Classes:</td>
                        <td>&lt;ul class="software_menu ' .
                            $liveform->output_field(array(
                                'type' => 'text',
                                'name' => 'class',
                                'size' => '40',
                                'maxlength' => '255')) . ' "&gt;

                            <div style="margin-top: 15px">
                                Separate classes with a space. Do not include period.
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary" />
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
        if (preg_match('/[^A-Za-z0-9-]/', $liveform->get_field_value('name')) == 1) {
            $liveform->mark_error('name', 'Please only enter letters, numbers, and dashes for the name.');
        }
    }
    
    // if there is not already an error for the name field, then check to see if name is already in use
    if ($liveform->check_field_error('name') == false) {
        $query =
            "SELECT id
            FROM menus
            WHERE (name = '" . escape($liveform->get_field_value('name')) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if name is already in use by a different menu, prepare error
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
        }
    }
    
    // if there is an error, forward user back to add menu screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_menu.php');
        exit();
    }
    
    // create menu
    $query =
        "INSERT INTO menus (
            name,
            effect,
            first_level_popup_position,
            second_level_popup_position,
            class,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . e($liveform->get_field_value('name')) . "',
            '" . e($liveform->get_field_value('effect')) . "',
            '" . e($liveform->get_field_value('first_level_popup_position')) . "',
            '" . e($liveform->get_field_value('second_level_popup_position')) . "',
            '" . e($liveform->get('class')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    log_activity('menu (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);

    $liveform_view_menus = new liveform('view_menus');
    $liveform_view_menus->add_notice('The menu has been created.');
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_menus.php');
    
    $liveform->remove_form();
}
?>