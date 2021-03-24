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

// if user does not have access to add calendar event location, output error
if ($user['role'] == 3) {
    log_activity("access denied to add calendar event location", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('add_calendar_event_location');

// if the form has not been submitted
if (!$_POST) {
    print
        output_header() . '
        <div id="subnav">
            <h1>[new event location]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Event Location</h1>
            <div class="subheading">Create a new location where events are held (such as a room, building, or campus), which can be shared by all calendars.</div>
            <form name="form" action="add_calendar_event_location.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                     <tr>
                        <th colspan="2"><h2>New Event Location Name</h2></th>
                    </tr>
                    <tr>
                        <td>Event Location Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'maxlength'=>'100')) . '</td>
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
    
    // if there is an error, forward user back to add calendar event location screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_calendar_event_location.php');
        exit();
    }
    
    // check to see if name is already in use by a different calendar event location
    $query =
        "SELECT id
        FROM calendar_event_locations
        WHERE (name = '" . escape($liveform->get_field_value('name')) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if name is already in use by a different calendar event location, prepare error and forward user back to screen
    if (mysqli_num_rows($result) > 0) {
        $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
        
        // forward user to add calendar event location screen
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_calendar_event_location.php');
        exit();
    }
    
    // create calendar event location
    $query =
        "INSERT INTO calendar_event_locations(
            name,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    log_activity('calendar event location (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);

    $liveform_view_calendar_event_locations = new liveform('view_calendar_event_locations');
    $liveform_view_calendar_event_locations->add_notice('The location has been created.');
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_calendar_event_locations.php');
    
    $liveform->remove_form();
}
?>