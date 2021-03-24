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
validate_contacts_access($user);

// if user does not have access to add contact group, output error
if ($user['role'] == 3) {
    log_activity("access denied to add contact group", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('add_contact_group');

if (!$_POST) {
    // if email subscription is on, prepare to show email subscription fields
    if ($liveform->get_field_value('email_subscription') == 1) {
        $email_subscription_type_row_style = '';
        $description_row_style = '';
    
    // else email subscription is off, so prepare to hide email subscription fields
    } else {
        $email_subscription_type_row_style = 'display: none';
        $description_row_style = 'display: none';
        $description_heading_row_style = 'display: none';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new contact group]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Contact Group</h1>
            <div class="subheading">Create a new contact group to collect and organize contacts.</div>
            <form name="form" action="add_contact_group.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Contact Group Name</h2></th>
                    </tr>
                    <tr>
                        <td>Contact Group Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Allow Campaigns to send to this Contact Group</h2></th>
                    </tr>
                    <tr>
                        <td>Enable E-mail Subscription:</td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'email_subscription', 'id'=>'email_subscription', 'value'=>'1', 'class'=>'checkbox', 'onclick'=>'show_or_hide_email_subscription()')) . '</td>
                    </tr>
                    <tr id="email_subscription_type_row" style="' . $email_subscription_type_row_style . '">
                        <td style="padding-left: 20px">E-mail Subscription Type:</td>
                        <td>' . $liveform->output_field(array('type'=>'radio', 'name'=>'email_subscription_type', 'id'=>'open', 'value'=>'open', 'checked'=>'checked', 'class'=>'radio')) . '<label for="open">Open</label> ' . $liveform->output_field(array('type'=>'radio', 'name'=>'email_subscription_type', 'id'=>'closed', 'value'=>'closed', 'class'=>'radio')) . '<label for="closed">Closed</label></td>
                    </tr>
                    <tr id="description_heading_row" style="' . $description_heading_row_style . '">
                        <th colspan="2"><h2>Description/Subscription Message on My Account Pages</h2></th>
                    </tr>
                    <tr id="description_row" style="' . $description_row_style . '">
                        <td style="vertical-align: top; padding-left: 20px">Description:</td>
                        <td>' . $liveform->output_field(array('type'=>'textarea', 'name'=>'description', 'rows'=>'5', 'cols'=>'50')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');
    
    // if there is an error, forward user back to add contact group screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_contact_group.php');
        exit();
    }
    
    // check to see if name is already in use by a different contact group
    $query =
        "SELECT id
        FROM contact_groups
        WHERE (name = '" . escape($liveform->get_field_value('name')) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if name is already in use by a different contact group, prepare error and forward user back to screen
    if (mysqli_num_rows($result) > 0) {
        $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
        
        // forward user to add contact group screen
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_contact_group.php');
        exit();
    }
    
    // create contact group
    $query =
        "INSERT INTO contact_groups (
            name,
            email_subscription,
            email_subscription_type,
            description,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            '" . escape($liveform->get_field_value('email_subscription')) . "',
            '" . escape($liveform->get_field_value('email_subscription_type')) . "',
            '" . escape($liveform->get_field_value('description')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    log_activity('contact group (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);

    $liveform_view_contact_groups = new liveform('view_contact_groups');
    $liveform_view_contact_groups->add_notice('The contact group has been created.');
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_contact_groups.php');
    
    $liveform->remove_form();
}
?>