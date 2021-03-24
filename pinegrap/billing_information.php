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

validate_token_field();

initialize_order();

$liveform = new liveform('billing_information');

$ghost = $_SESSION['software']['ghost'];

$liveform->add_fields_to_session();

// Get page info.
$query =
    "SELECT
        custom_field_1_label,
        custom_field_1_required,
        custom_field_2_label,
        custom_field_2_required,
        form
    FROM billing_information_pages
    WHERE page_id = '" . escape($_POST['page_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$custom_field_1_label = $row['custom_field_1_label'];
$custom_field_1_required = $row['custom_field_1_required'];
$custom_field_2_label = $row['custom_field_2_label'];
$custom_field_2_required = $row['custom_field_2_required'];
$form = $row['form'];

// if there is a custom field 1 label and custom field 1 is required, then validate required field
if (($custom_field_1_label != '') && ($custom_field_1_required == 1)) {
    $liveform->validate_required_field('custom_field_1', $custom_field_1_label . ' is required.');
}

// if there is a custom field 2 label and custom field 2 is required, then validate required field
if (($custom_field_2_label != '') && ($custom_field_2_required == 1)) {
    $liveform->validate_required_field('custom_field_2', $custom_field_2_label . ' is required.');
}

$liveform->validate_required_field('billing_first_name', 'First Name is required.');
$liveform->validate_required_field('billing_last_name', 'Last Name is required.');
$liveform->validate_required_field('billing_address_1', 'Address 1 is required.');
$liveform->validate_required_field('billing_city', 'City is required.');
$liveform->validate_required_field('billing_country', 'Country is required.');

// If a country has been selected and then determine if state and zip code are required.
if ($liveform->get('billing_country')) {

    // If there is a state in this system for the selected country, then require state.
    if (db(
        "SELECT states.id FROM states
        LEFT JOIN countries ON countries.id = states.country_id
        WHERE countries.code = '" . e($liveform->get('billing_country')) . "'
        LIMIT 1")
    ) {
        $liveform->validate_required_field('billing_state', 'State/Province is required.');
    }

    // If this country requires a zip code, then require it.
    if (
        db(
            "SELECT zip_code_required FROM countries
            WHERE code = '" . e($liveform->get('billing_country')) . "'")
    ) {
        $liveform->validate_required_field('billing_zip_code', 'Zip/Postal Code is required.');
    }
}

$liveform->validate_required_field('billing_phone_number', 'Phone is required.');
$liveform->validate_required_field('billing_email_address', 'Email is required.');

// if there is not already an error for the e-mail address field, validate e-mail address
if ($liveform->check_field_error('billing_email_address') == false) {
    if (validate_email_address($liveform->get_field_value('billing_email_address')) == false) {
        $liveform->mark_error('billing_email_address', 'Email is invalid.');
    }
}

// If a custom billing form is enabled, then submit form.
if ($form == 1) {

    submit_custom_form(array(
        'form' => $liveform,
        'page_id' => $_POST['page_id'],
        'page_type' => 'billing information',
        'form_type' => 'billing',
        'require' => true));
}

// get old address verified value from the database
$query = "SELECT billing_address_verified FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$address_verified = $row['address_verified'];

// Verify address if address verification is enabled and it is a US address

require_once(dirname(__FILE__) . '/shipping.php');

$address_verified = verify_address(array(
    'liveform' => $liveform,
    'address_type' => 'billing',
    'old_address_verified' => $address_verified));

// if there was no opt-in field on previous screen or if the opt-in checkbox was checked
if (($liveform->get_field_value('opt_in_displayed') == 'false') || ($liveform->get_field_value('opt_in') == '1')) {
    $opt_in = 1;
    
// else this order is not opted in
} else {
    $opt_in = 0;
}

// if tax-exempt was checked
if ($liveform->get_field_value('tax_exempt')) {
    $tax_exempt = 1;
    
// else tax-exempt was not checked
} else {
    $tax_exempt = 0;
}

// add data to orders table
$query =
    "UPDATE orders
     SET
        billing_salutation = '" . escape($liveform->get_field_value('billing_salutation')) . "',
        billing_first_name = '" . escape($liveform->get_field_value('billing_first_name')) . "',
        billing_last_name = '" . escape($liveform->get_field_value('billing_last_name')) . "',
        billing_company = '" . escape($liveform->get_field_value('billing_company')) . "',
        billing_address_1 = '" . escape($liveform->get_field_value('billing_address_1')) . "',
        billing_address_2 = '" . escape($liveform->get_field_value('billing_address_2')) . "',
        billing_city = '" . escape($liveform->get_field_value('billing_city')) . "',
        billing_state = '" . escape($liveform->get_field_value('billing_state')) . "',
        billing_zip_code = '" . escape($liveform->get_field_value('billing_zip_code')) . "',
        billing_country = '" . escape($liveform->get_field_value('billing_country')) . "',
        billing_address_verified = '" . escape($address_verified) . "',
        billing_phone_number = '" . escape($liveform->get_field_value('billing_phone_number')) . "',
        billing_fax_number = '" . escape($liveform->get_field_value('billing_fax_number')) . "',
        billing_email_address = '" . escape($liveform->get_field_value('billing_email_address')) . "',
        custom_field_1 = '" . escape($liveform->get_field_value('custom_field_1')) . "',
        custom_field_2 = '" . escape($liveform->get_field_value('custom_field_2')) . "',
        opt_in = '$opt_in',
        tax_exempt = '$tax_exempt',
        po_number = '" . escape($liveform->get_field_value('po_number')) . "',
        referral_source_code = '" . escape($liveform->get_field_value('referral_source')) . "',
        billing_complete = '0'
    WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if an error does not exist
if ($liveform->check_form_errors() == false) {

    $user_id = '';
    $contact_id = '';

    // If the user is logged in and not ghosting, then use this user and contact.
    if (USER_LOGGED_IN and !$ghost) {
        $user_id = USER_ID;
        $contact_id = USER_CONTACT_ID;
    }

    // If a contact has not been found, then look for one in order.
    if (!$contact_id) {
        
        // get contact id for this order
        $query = "SELECT contact_id FROM orders WHERE id = '" . e($_SESSION['ecommerce']['order_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $contact_id = $row['contact_id'];
        
        // check to see if this contact is connected to a user
        $query = "SELECT user_id FROM user WHERE user_contact = '$contact_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the contact is connected to a user, then the contact should not be used
        if (mysqli_num_rows($result) > 0) {
            $contact_id = 0;
        }
    }

    // If a contact id has been found, then check to make sure contact still exists.
    if ($contact_id) {
        $contact_id = db("SELECT id FROM contacts WHERE id = '" . e($contact_id) . "'");
    }
    
    // If we could not find a contact, then create one.
    if (!$contact_id) {

        $query =
            "INSERT INTO contacts (
                user,
                timestamp)
            VALUES (
                '$user_id',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $contact_id = mysqli_insert_id(db::$con);

        // if user is logged in, connect new contact record to user record
        if ($user_id) {
            $query = "UPDATE user SET user_contact = '$contact_id' WHERE user_id = '$user_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
    
    // check if there is already a contact group record for Orders
    $query = "SELECT id FROM contact_groups WHERE name = 'Orders'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if there is a contact group record for Orders contact group, get id
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $contact_group_id = $row['id'];
        
    // else there is not a contact group record for the Orders contact group, so create contact group
    } else {
        $query =
            "INSERT INTO contact_groups (
                name,
                created_timestamp,
                last_modified_timestamp)
            VALUES (
                'Orders',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $contact_group_id = mysqli_insert_id(db::$con);
    }
    
    // check if contact is already in Orders contact group
    $query =
        "SELECT contact_id
        FROM contacts_contact_groups_xref
        WHERE
            (contact_id = '$contact_id')
            AND (contact_group_id = '$contact_group_id')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if contact is not already in contact group, then add contact to Orders contact group
    if (mysqli_num_rows($result) == 0) {
        $query =
            "INSERT INTO contacts_contact_groups_xref (
                contact_id,
                contact_group_id)
            VALUES (
                '$contact_id',
                '$contact_group_id')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    // update order record
    $query = "UPDATE orders
             SET
                user_id = '$user_id',
                contact_id = '$contact_id',
                billing_complete = '1'
             WHERE id = " . $_SESSION['ecommerce']['order_id'];
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // If the user is logged in and not ghosting and the user selected that he/she
    // does not want his/her contact info updated with billing info,
    // then remember that in the session, so if the user comes back
    // to the billing information screen the check box will be unchecked.
    if (USER_LOGGED_IN and !$ghost and !$liveform->get_field_value('update_contact')) {
        $_SESSION['software']['update_contact'] = false;
    } else {
        unset($_SESSION['software']['update_contact']);
    }
    
    // If the user is not logged in or is ghosting or if the user is logged in and wants
    // his/her contact info updated with billing info, then update contact.
    if (!USER_LOGGED_IN or $ghost or ($_SESSION['software']['update_contact'] !== false)) {

        $sql_fax = '';

        // If a fax field appeared on the form, then update fax number for contact.
        // We don't want to clear the contact's fax number if there was no fax field.
        if ($liveform->field_in_session('billing_fax_number')) {
            $sql_fax = "business_fax = '" . e($liveform->get('billing_fax_number')) . "', ";
        }
        
        $query = "UPDATE contacts " .
                 "SET " .
                     "salutation = '" . escape($liveform->get_field_value('billing_salutation')) . "', " .
                     "first_name = '" . escape($liveform->get_field_value('billing_first_name')) . "', " .
                     "last_name = '" . escape($liveform->get_field_value('billing_last_name')) . "', " .
                     "company = '" . escape($liveform->get_field_value('billing_company')) . "', " .
                     "business_address_1 = '" . escape($liveform->get_field_value('billing_address_1')) . "', " .
                     "business_address_2 = '" . escape($liveform->get_field_value('billing_address_2')) . "', " .
                     "business_city = '" . escape($liveform->get_field_value('billing_city')) . "', " .
                     "business_state = '" . escape($liveform->get_field_value('billing_state')) . "', " .
                     "business_zip_code = '" . escape($liveform->get_field_value('billing_zip_code')) . "', " .
                     "business_country = '" . escape($liveform->get_field_value('billing_country')) . "', " .
                     "business_phone = '" . escape($liveform->get_field_value('billing_phone_number')) . "', " .
                     $sql_fax .
                     "email_address = '" . escape($liveform->get_field_value('billing_email_address')) . "', " .
                     "lead_source = '" . escape($liveform->get_field_value('referral_source')) . "', " .
                     "opt_in = '$opt_in', " .
                     "user = '$user_id', " .
                     "timestamp = UNIX_TIMESTAMP() " .
                 "WHERE id = '" . $contact_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    // if visitor tracking is on, update geographic data for visitor, using billing information
    if (VISITOR_TRACKING == true) {
        $query = "UPDATE visitors
                 SET
                    city = '" . escape($liveform->get_field_value('billing_city')) . "',
                    state = '" . escape($liveform->get_field_value('billing_state')) . "',
                    zip_code = '" . escape($liveform->get_field_value('billing_zip_code')) . "',
                    country = '" . escape($liveform->get_field_value('billing_country')) . "',
                    stop_timestamp = UNIX_TIMESTAMP()
                 WHERE id = '" . $_SESSION['software']['visitor_id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    // remove liveform because software does not need it anymore
    $liveform->remove_form('billing_information');

    // send user to next form
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_POST['next_page_id']));
    
// else an error does exist
} else {
    // send user back to previous form
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_POST['page_id']));
}