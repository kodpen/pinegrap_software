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

// if user does not have access to edit calendar event location, output error
if ($user['role'] == 3) {
    log_activity("access denied to edit calendar event location.", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('edit_calendar_event_location', $_REQUEST['id']);

if (!$_POST) {
    // get calendar event location data
    $query =
        "SELECT name
        FROM calendar_event_locations
        WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $liveform->assign_field_value('name', $row['name']);

    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($liveform->get_field_value('name')) . '</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Event Location</h1>
            <div class="subheading">View or rename this common event location.</div>
            <form name="form" action="edit_calendar_event_location.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Event Location Name</h2></th>
                    </tr>
                    <tr>
                        <td>Event Location Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'maxlength'=>'100')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This location will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if calendar event location was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // get calendar event location name for log
        $query = "SELECT name FROM calendar_event_locations WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $calendar_event_location_name = $row['name'];
        
        // delete calendar event location
        $query = "DELETE FROM calendar_event_locations WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('calendar event location (' . $calendar_event_location_name . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_calendar_event_locations = new liveform('view_calendar_event_locations');
        $liveform_view_calendar_event_locations->add_notice('The location has been deleted.');
        
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_calendar_event_locations.php');
        
        $liveform->remove_form();
        
    // else calendar event location was not selected for delete
    } else {
        $liveform->add_fields_to_session();
        
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is an error, forward user back to edit calendar event location screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_calendar_event_location.php?id=' . $_POST['id']);
            exit();
        }
        
        // check to see if name is already in use by a different calendar location
        $query =
            "SELECT id
            FROM calendar_event_locations
            WHERE
                (name = '" . escape($liveform->get_field_value('name')) . "')
                AND (id != '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if name is already in use by a different calendar event location, prepare error and forward user back to screen
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            
            // forward user to edit calendar event location screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_calendar_event_location.php?id=' . $_POST['id']);
            exit();
        }
        
        // update calendar event location
        $query =
            "UPDATE calendar_event_locations
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('calendar event location (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_calendar_event_locations = new liveform('view_calendar_event_locations');
        $liveform_view_calendar_event_locations->add_notice('The location has been saved.');
        
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_calendar_event_locations.php');
        
        $liveform->remove_form();
    }
}
?>