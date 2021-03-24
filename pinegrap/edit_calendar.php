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
validate_calendars_access($user);

// if user does not have access to edit calendar, output error
if (validate_calendar_access($_REQUEST['id']) == false) {
    log_activity("access denied to edit calendar", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

$liveform = new liveform('edit_calendar', $_REQUEST['id']);

// get number of calendar events in this calendar
$query = "SELECT COUNT(calendar_event_id) FROM calendar_events_calendars_xref WHERE calendar_id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$number_of_calendar_events = $row[0];

if (!$_POST) {
    // get calendar data
    $query =
        "SELECT name
        FROM calendars
        WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $liveform->assign_field_value('name', $row['name']);
    
    // if there are no calendar events in this calendar, allow delete
    if ($number_of_calendar_events == 0) {
        $output_delete_button = '<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This calendar will be permanently deleted.\')" />';
        
    // else there is at least one calendar event in this calendar, so disable delete button
    } else {
        $output_delete_button = '<input type="button" value="Delete" class="delete" onclick="alert(\'Please delete or remove all calendar events from this calendar before deleting this calendar.\')" />';
    }

    echo
        output_header() . '
        <div id="subnav">
            <h1>' . h($liveform->get_field_value('name')) . '</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Calendar Properties</h1>
            <div class="subheading">Edit the properties for this calendar.</div>
            <br />
            <form name="form" action="edit_calendar.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <table class="field">
                     <tr>
                        <th colspan="2"><h2>New Calendar Name</h2></th>
                    </tr>
                    <tr>
                        <td>Calendar Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'maxlength'=>'100')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />&nbsp;&nbsp;&nbsp;' . $output_delete_button . '
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if calendar was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // if there are no calendar events in this calendar, proceed with deleting calendar
        if ($number_of_calendar_events == 0) {
            // get calendar name for log
            $query = "SELECT name FROM calendars WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $calendar_name = $row['name'];
            
            // delete calendar
            $query = "DELETE FROM calendars WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete users_calendars_xref records
            $query = "DELETE FROM users_calendars_xref WHERE calendar_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete calendar_views_calendars_xref records
            $query = "DELETE FROM calendar_views_calendars_xref WHERE calendar_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete calendar_event_views_calendars_xref records
            $query = "DELETE FROM calendar_event_views_calendars_xref WHERE calendar_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete calendar_events_calendars_xref records
            $query = "DELETE FROM calendar_events_calendars_xref WHERE calendar_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            log_activity('calendar (' . $calendar_name . ') was deleted', $_SESSION['sessionusername']);
            
            $liveform_view_calendars = new liveform('view_calendars');
            $liveform_view_calendars->add_notice('The calendar has been deleted.');
            
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_calendars.php');
            
            $liveform->remove_form();
        
        // else there is at least one calendar event in this calendar, so prepare error
        } else {
            $liveform->add_fields_to_session();
            
            $liveform->mark_error('', 'Please delete or remove all calendar events from this calendar before deleting this calendar.');
            
            // forward user to edit calendar screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_calendar.php?id=' . $_POST['id']);
        }
        
    // else calendar was not selected for delete
    } else {
        $liveform->add_fields_to_session();
        
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is an error, forward user back to edit calendar screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_calendar.php?id=' . $_POST['id']);
            exit();
        }
        
        // check to see if name is already in use by a different calendar
        $query =
            "SELECT id
            FROM calendars
            WHERE
                (name = '" . escape($liveform->get_field_value('name')) . "')
                AND (id != '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if name is already in use by a different calendar, prepare error and forward user back to screen
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            
            // forward user to edit calendar screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_calendar.php?id=' . $_POST['id']);
            exit();
        }
        
        // update calendar
        $query =
            "UPDATE calendars
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('calendar (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_calendars = new liveform('calendars');
        $liveform_calendars->add_notice('The calendar has been saved.');
        
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/calendars.php?calendar_id=' . $_POST['id']);
        
        $liveform->remove_form();
    }
}