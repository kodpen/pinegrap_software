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

// if user does not have access to edit contact group, output error
if (validate_contact_group_access($user, $_REQUEST['id']) == false) {
    log_activity("access denied to edit contact group", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('edit_contact_group', $_REQUEST['id']);

// get number of contacts in this contact group
$query = "SELECT COUNT(contact_id) FROM contacts_contact_groups_xref WHERE contact_group_id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$number_of_contacts = $row[0];

if (!$_POST) {
    // if edit contact group screen has not been submitted already, pre-populate fields with data
    if (isset($_SESSION['software']['liveforms']['edit_contact_group'][$_GET['id']]) == false) {
        // get contact group data
        $query =
            "SELECT
                name,
                email_subscription,
                email_subscription_type,
                description
            FROM contact_groups
            WHERE id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $liveform->assign_field_value('name', $row['name']);
        $liveform->assign_field_value('email_subscription', $row['email_subscription']);
        $liveform->assign_field_value('email_subscription_type', $row['email_subscription_type']);
        $liveform->assign_field_value('description', $row['description']);
    }
    
    // if email subscription is on, prepare to show email subscription fields
    if ($liveform->get_field_value('email_subscription') == 1) {
        $email_subscription_type_row_style = '';
        $description_row_style = '';
        $description_heading_row_style = '';
    
    // else email subscription is off, so prepare to hide email subscription fields
    } else {
        $email_subscription_type_row_style = 'display: none';
        $description_row_style = 'display: none';
        $description_heading_row_style = 'display: none';
    }
    
    // if there are no contacts in this contact group, allow delete
    if ($number_of_contacts == 0) {
        $output_delete_button = '<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This contact group will be permanently deleted.\')" />';
        
    // else there is at least one contact in this contact group, so disable delete button
    } else {
        $output_delete_button = '<input type="button" value="Delete" class="delete" onclick="alert(\'Please delete or remove all contacts from this contact group before deleting this contact group.\')" />';
    }

    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($row['name']) . '</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Contact Group</h1>
            <div class="subheading">Update this contact group and its subscription features.</div>
            <form name="form" action="edit_contact_group.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Contact Group Name</h2></th>
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
                        <td>' . $liveform->output_field(array('type'=>'radio', 'name'=>'email_subscription_type', 'id'=>'open', 'value'=>'open', 'class'=>'radio')) . '<label for="open">Open</label> ' . $liveform->output_field(array('type'=>'radio', 'name'=>'email_subscription_type', 'id'=>'closed', 'value'=>'closed', 'class'=>'radio')) . '<label for="closed">Closed</label></td>
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
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />&nbsp;&nbsp;&nbsp;' . $output_delete_button . '
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if contact group was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // if there are no contacts in this contact group, proceed with deleting contact group
        if ($number_of_contacts == 0) {
            // get contact group name for log
            $query = "SELECT name FROM contact_groups WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $contact_group_name = $row['name'];
            
            // delete contact group
            $query = "DELETE FROM contact_groups WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete contact group references in contacts_contact_groups_xref
            $query = "DELETE FROM contacts_contact_groups_xref WHERE contact_group_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete contact group references in users_contact_groups_xref
            $query = "DELETE FROM users_contact_groups_xref WHERE contact_group_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete contact group references in opt_in
            $query = "DELETE FROM opt_in WHERE contact_group_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete contact group references in contact_groups_email_campaigns_xref
            $query = "DELETE FROM contact_groups_email_campaigns_xref WHERE contact_group_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            log_activity('contact group (' . $contact_group_name . ') was deleted', $_SESSION['sessionusername']);
            
            $liveform_view_contact_groups = new liveform('view_contact_groups');
            $liveform_view_contact_groups->add_notice('The contact group has been deleted.');
            
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_contact_groups.php');
            
            $liveform->remove_form();
        
        // else there is at least one contact in this contact group, so prepare error
        } else {
            $liveform->add_fields_to_session();
            
            $liveform->mark_error('', 'Please delete or remove all contacts from this contact group before deleting this contact group.');
            
            // forward user to edit contact group screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_contact_group.php?id=' . $_POST['id']);
        }
        
    // else contact group was not selected for delete
    } else {
        $liveform->add_fields_to_session();
        
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is an error, forward user back to edit contact group screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_contact_group.php?id=' . $_POST['id']);
            exit();
        }
        
        // check to see if name is already in use by a different contact group
        $query =
            "SELECT id
            FROM contact_groups
            WHERE
                (name = '" . escape($liveform->get_field_value('name')) . "')
                AND (id != '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if name is already in use by a different contact group, prepare error and forward user back to screen
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            
            // forward user to edit contact group screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_contact_group.php?id=' . $_POST['id']);
            exit();
        }
        
        // update contact group
        $query =
            "UPDATE contact_groups
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                email_subscription = '" . escape($liveform->get_field_value('email_subscription')) . "',
                email_subscription_type = '" . escape($liveform->get_field_value('email_subscription_type')) . "',
                description = '" . escape($liveform->get_field_value('description')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('contact group (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_contact_groups = new liveform('view_contact_groups');
        $liveform_view_contact_groups->add_notice('The contact group has been saved.');
        
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_contact_groups.php');
        
        $liveform->remove_form();
    }
}
?>