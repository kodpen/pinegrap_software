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

validate_token_field();

include_once('liveform.class.php');

// if at least one contact was selected
if ($_POST['contacts']) {
    $number_of_contacts = 0;

    switch ($_POST['action']) {
        // if contacts are being organized
        case 'organize':
            if ($_POST['add_to_contact_groups']) {
                $add_to_contact_groups = explode(',', $_POST['add_to_contact_groups']);
            } else {
                $add_to_contact_groups = array();
            }
            
            if ($_POST['remove_from_contact_groups']) {
                $remove_from_contact_groups = explode(',', $_POST['remove_from_contact_groups']);
            } else {
                $remove_from_contact_groups = array();
            }
            
            foreach ($_POST['contacts'] as $contact_id) {
                // if user has access to contact, then continue
                if (validate_contact_access($user, $contact_id) == true) {
                    // loop through all contact groups that contact should be added to
                    foreach ($add_to_contact_groups as $contact_group_id) {
                        // check to see if contact is already in contact group
                        $query =
                            "SELECT contact_id
                            FROM contacts_contact_groups_xref
                            WHERE
                                (contact_id = '" . escape($contact_id) . "')
                                AND (contact_group_id = '" . escape($contact_group_id) . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // if contact is not already in contact group, then add contact to contact group
                        if (mysqli_num_rows($result) == 0) {
                            $query =
                                "INSERT INTO contacts_contact_groups_xref (
                                    contact_id,
                                    contact_group_id)
                                VALUES (
                                    '" . escape($contact_id) . "',
                                    '" . escape($contact_group_id) . "')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                    }
                    
                    // loop through all contact groups that contact should be removed from
                    foreach ($remove_from_contact_groups as $contact_group_id) {
                        // remove contact from contact group
                        $query =
                            "DELETE FROM contacts_contact_groups_xref
                            WHERE
                                (contact_id = '" . escape($contact_id) . "')
                                AND (contact_group_id = '" . escape($contact_group_id) . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }

                    $number_of_contacts++;
                }
            }
            
            // if more than 0 contacts were organized, then log activity
            if ($number_of_contacts > 0) {
                log_activity(number_format($number_of_contacts) . ' contact(s) were organized', $_SESSION['sessionusername']);
            }
            
            $liveform_view_contacts = new liveform('view_contacts');
            $liveform_view_contacts->add_notice(number_format($number_of_contacts) . ' contact(s) were organized.');
            
            break;

        // if contacts are being opted-in
        case 'optin':
            foreach ($_POST['contacts'] as $contact_id) {
                // if user has access to contact, then continue
                if (validate_contact_access($user, $contact_id) == true) {
                    $query =    "UPDATE contacts ".
                                "SET opt_in = 1 ".
                                "WHERE id = '" . escape($contact_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $number_of_contacts++;
                }
            }
            
            // if at least one contact was opted-in, log activity
            if ($number_of_contacts > 0) {
                log_activity(number_format($number_of_contacts) . ' contact(s) were opted-in', $_SESSION['sessionusername']);
            }
            
            $liveform_view_contacts = new liveform('view_contacts');
            $liveform_view_contacts->add_notice(number_format($number_of_contacts) . ' contact(s) were opted-in.');
            
            break;

        // if contacts are being opted-out
        case 'optout':
            foreach ($_POST['contacts'] as $contact_id) {
                // if user has access to contact, then continue
                if (validate_contact_access($user, $contact_id) == true) {
                    $query =    "UPDATE contacts ".
                                "SET opt_in = 0 ".
                                "WHERE id = '" . escape($contact_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $number_of_contacts++;
                }
            }
            
            // if more than 0 contacts were opted-out, then log activity
            if ($number_of_contacts > 0) {
                log_activity(number_format($number_of_contacts) . ' contact(s) were opted-out', $_SESSION['sessionusername']);
            }
            
            $liveform_view_contacts = new liveform('view_contacts');
            $liveform_view_contacts->add_notice(number_format($number_of_contacts) . ' contact(s) were opted-out.');
            
            break;

        // if contacts are being deleted
        case 'delete':
            foreach ($_POST['contacts'] as $contact_id) {
                // if user has access to contact, then continue
                if (validate_contact_access($user, $contact_id) == true) {
                    // delete contact
                    $query = "DELETE FROM contacts WHERE id = '" . escape($contact_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete contact references in contacts_contact_groups_xref
                    $query = "DELETE FROM contacts_contact_groups_xref WHERE contact_id = '" . escape($contact_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete contact references in opt_in
                    $query = "DELETE FROM opt_in WHERE contact_id = '" . escape($contact_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $number_of_contacts++;
                }
            }
            
            // if more than 0 contacts were deleted, then log activity
            if ($number_of_contacts > 0) {
                log_activity(number_format($number_of_contacts) . ' contact(s) were deleted', $_SESSION['sessionusername']);
            }
            
            $liveform_view_contacts = new liveform('view_contacts');
            $liveform_view_contacts->add_notice(number_format($number_of_contacts) . ' contact(s) were deleted.');
            
            break;
            
        // if contacts are being merged, then merge them
        case 'merge':
            // if user has a user role then user does not have access to this filter so output error
            if ($user['role'] == 3) {
                log_activity("access denied to merge contacts", $_SESSION['sessionusername']);
                output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
            }
            
            $where = '';
            
            // loop through selected contacts and build an sql where statement to get the data for only these contacts
            foreach ($_POST['contacts'] as $contact_id) {
                // If where is not blank then add OR
                if ($where != '') {
                    $where .= ' OR ';
                }
                
                $where .= "(contacts.id = '" . escape($contact_id) . "')";
            }
            
            // if where is not blank, then prepare it for sql query
            if ($where != '') {
                $where = "WHERE (" . $where . ")";
            }
            
            $selected_contacts = array();
            
            // get contacts to be merged information
            $query = 
                "SELECT
                    contacts.id,
                    contacts.salutation,
                    contacts.first_name,
                    contacts.last_name,
                    contacts.suffix,
                    contacts.nickname,
                    contacts.company,
                    contacts.title,
                    contacts.department,
                    contacts.office_location,
                    contacts.business_address_1,
                    contacts.business_address_2,
                    contacts.business_city,
                    contacts.business_state,
                    contacts.business_country,
                    contacts.business_zip_code,
                    contacts.business_phone,
                    contacts.business_fax,
                    contacts.home_address_1,
                    contacts.home_address_2,
                    contacts.home_city,
                    contacts.home_state,
                    contacts.home_country,
                    contacts.home_zip_code,
                    contacts.home_phone,
                    contacts.home_fax,
                    contacts.mobile_phone,
                    contacts.email_address,
                    contacts.website,
                    contacts.lead_source,
                    contacts.opt_in,
                    contacts.description,
                    contacts.member_id,
                    contacts.expiration_date,
                    contacts.warning_expiration_date,
                    contacts.affiliate_approved,
                    contacts.affiliate_name,
                    contacts.affiliate_code,
                    contacts.affiliate_commission_rate,
                    contacts.user,
                    contact_user.user_id as user_id,
                    contacts.timestamp
                FROM contacts
                LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact
                LEFT JOIN contacts_contact_groups_xref ON contacts.id = contacts_contact_groups_xref.contact_id
                $where
                ORDER BY contacts.email_address ASC";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            while($row = mysqli_fetch_assoc($result)) {
                $selected_contacts[] = $row;
            }
            
            $duplicate_contacts_to_merge = array();
            
            // loop through all contacts to remove any contacts that are not duplicates
            foreach ($selected_contacts as $selected_contact) {
                $duplicate_counter = 0;
                
                // loop through all contacts to determine if this contact has any duplicates
                foreach ($selected_contacts as $current_contact) {
                    // if the selected contact email is the same as the current contact email then increment the counter
                    if (mb_strtolower($selected_contact['email_address']) == mb_strtolower($current_contact['email_address'])) {
                        $duplicate_counter++;
                    }
                }
                
                // if the counter is greater than one, then add this contact to the duplicate contacts to be merged array
                if ($duplicate_counter > 1) {
                    $duplicate_contacts_to_merge[] = $selected_contact;
                }
            }
            
            // merge the contacts, then refresh the screen with a notice
            $number_of_merged_contacts = merge_contacts($duplicate_contacts_to_merge);
            
            $notice = '';
            
            // if contacts were merged then output a notice informing the user how many were merged
            if ($number_of_merged_contacts > 0) {
                $notice = number_format($number_of_merged_contacts) . " contact(s) have been merged successfully.";
                log_activity(number_format($number_of_merged_contacts) . " contact(s) were merged successfully", $_SESSION['sessionusername']);
            
            // else output a notice informing the user that no contacts where merged.
            } else {
                $notice = "No contacts were merged. Contacts tied to User accounts cannot be merged.";
            }
            
            $liveform_view_contacts = new liveform('view_contacts');
            $liveform_view_contacts->add_notice($notice);
            
            break;
    }
}

// If there is a send to value then send user back to that screen
if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
    header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
    
// else send user to the default view
} else {
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_contacts.php');
}
?>