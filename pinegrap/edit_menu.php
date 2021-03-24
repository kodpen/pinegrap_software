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
$liveform = new liveform('edit_menu', $_REQUEST['id']);

// if the form has not been submitted
if (!$_POST) {
    // if edit menu screen has not been submitted already, pre-populate fields with data
    if (!$liveform->field_in_session('name')) {
        // get menu information
        $query =
            "SELECT
                name,
                effect,
                first_level_popup_position,
                second_level_popup_position,
                class
            FROM menus
            WHERE id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $menu_name = $row['name'];
        
        // Assign the values to the fields.
        $liveform->assign_field_value('name', $row['name']);
        $liveform->assign_field_value('effect', $row['effect']);
        $liveform->assign_field_value('first_level_popup_position', $row['first_level_popup_position']);
        $liveform->assign_field_value('second_level_popup_position', $row['second_level_popup_position']);
        $liveform->set('class', $row['class']);
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
    
    // Set options for First and Second Level Pop-up Positions
    $popup_position_options = 
        array(
            'Top' => 'Top',
            'Bottom' => 'Bottom',
            'Left' => 'Left',
            'Right' => 'Right');
    
    echo
        output_header() . '
        <div id="subnav">
            <h1>' . h($row['name']) . '</h1>
            <div class="subheading">Page Style Body Tag: ' . h('<menu>' . $menu_name . '</menu>') . '</div>
        </div>
        <div id="button_bar">
            <a href="duplicate_menu.php?id=' . h(urlencode($_GET['id'])) . '&from=' . h(urlencode($_GET['from'])) . '&send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '">Duplicate</a>
        </div>
        <div id="content">
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Menu Properties</h1>
            <div class="subheading"></div>
            <form name="form" action="edit_menu.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="from" value="' . h($_GET['from']) . '" />
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Menu Name</h2></th>
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
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This menu and all of its menu items will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if menu was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // get menu name for the log
        $query = "SELECT name FROM menus WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $menu_name = $row['name'];
        
        // delete menu
        $query = "DELETE FROM menus WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete menu items
        $query = "DELETE FROM menu_items WHERE menu_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete users_menus_xref records
        $query = "DELETE FROM users_menus_xref WHERE menu_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('menu (' . $menu_name . ') was deleted', $_SESSION['sessionusername']);
        
        // if the user came from the pages tab, then forward user back to page
        if ($_POST['from'] == 'pages') {
            header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
            
        // else the user came from the design tab, so prepare notice and forward user to view menus screen
        } else {
            $liveform_view_menus = new liveform('view_menus');
            $liveform_view_menus->add_notice('The menu has been deleted.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_menus.php');
        }
        
        $liveform->remove_form();
        
    // else the menu was not selected for delete
    } else {
        $liveform->add_fields_to_session();
        
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is not already an error for the name field, then check that valid characters were entered for name field
        if ($liveform->check_field_error('name') == false) {
            // Get previous name in order to determine if we should allow underscores or not.
            // We don't want to allow menus to be created or new menu names to be set with underscores,
            // because the theme designer does not support underscores in names.  We are going to allow
            // underscores as long as it was set in the past in order to prevent their styles or themes
            // from being messed up.
            $query = "SELECT name FROM menus WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $previous_name = $row['name'];

            // If the previous name contains underscores, then set regex code and message to allow underscores.
            if (mb_strpos($previous_name, '_') !== FALSE) {
                $regex = '/[^A-Za-z0-9_-]/';
                $message = 'Please only enter letters, numbers, dashes, and underscores for the name.';

            // Otherwise the previous name does not contain underscores, so set regex code and message so they do not allow underscores.
            } else {
                $regex = '/[^A-Za-z0-9-]/';
                $message = 'Please only enter letters, numbers, or dashes for the name.';
            }

            // If the name is not valid, then add error.
            if (preg_match($regex, $liveform->get_field_value('name')) == 1) {
                $liveform->mark_error('name', $message);
            }
        }
        
        // if there is not already an error for the name field, then check to see if name is already in use
        if ($liveform->check_field_error('name') == false) {
            // check to see if name is already in use by a different menu
            $query =
                "SELECT id
                FROM menus
                WHERE
                    (name = '" . escape($liveform->get_field_value('name')) . "')
                    AND (id != '" . escape($_POST['id']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if name is already in use, prepare error
            if (mysqli_num_rows($result) > 0) {
                $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            }
        }
        
        // if there is an error, forward user back to edit menu screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_menu.php?id=' . $_POST['id'] . '&from=' . urlencode($_POST['from']) . '&send_to=' . urlencode($_POST['send_to']));
            exit();
        }
        
        // update menu
        $query =
            "UPDATE menus
            SET
                name = '" . e($liveform->get_field_value('name')) . "',
                effect = '" . e($liveform->get_field_value('effect')) . "',
                first_level_popup_position = '" . e($liveform->get_field_value('first_level_popup_position')) . "',
                second_level_popup_position = '" . e($liveform->get_field_value('second_level_popup_position')) . "',
                class = '" . e($liveform->get('class')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('menu (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_menu_items = new liveform('view_menu_items');
        $liveform_view_menu_items->add_notice('The menu has been saved.');
        
        // forward user to view menu items screen
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_menu_items.php?id=' . $_POST['id'] . '&from=' . urlencode($_POST['from']) . '&send_to=' . urlencode($_POST['send_to']));
        
        $liveform->remove_form();
    }
}