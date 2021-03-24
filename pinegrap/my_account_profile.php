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

// if user is not logged in, send user to registration entrance screen to login or register
if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri()));
    exit();
}

// if user has not yet completed form
if (!$_POST) {
    print get_my_account_profile_screen();

// else user has completed form, so process form
} else {
    validate_token_field();
    
    $liveform = new liveform('my_account_profile');
    $my_account = new liveform('my_account');

    $liveform->add_fields_to_session();

    $liveform->validate_required_field('first_name', 'First Name is required.');
    $liveform->validate_required_field('last_name', 'Last Name is required.');

    // If this PHP version supports user timezones, and a timezone was set,
    // and timezone is not valid, then output error.
    if (
        (version_compare(PHP_VERSION, '5.2.0', '>=') == true)
        && ($liveform->get_field_value('timezone') != '')
        && (in_array($liveform->get_field_value('timezone'), get_timezones()) == false)
    ) {
        $liveform->mark_error('timezone', 'Sorry, that timezone is not valid.');
    }
    
    // get my account profile page, if one exists
    $query = "SELECT page_id FROM page WHERE page_type = 'my account profile'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if there is a my account profile page, prepare path with page name
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $my_account_profile_path = PATH . encode_url_path(get_page_name($row['page_id']));
        
    // else there is not a my account profile page, so prepare default path
    } else {
        $my_account_profile_path = PATH . SOFTWARE_DIRECTORY . '/my_account_profile.php';
    }
    
    // if an error does not exist
    if ($liveform->check_form_errors() == false) {
        // get user information
        $query = "SELECT user_id, user_contact FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        $row = mysqli_fetch_assoc($result);
        $user_id = $row['user_id'];
        $contact_id = $row['user_contact'];

        // look for contact
        $query = "SELECT id FROM contacts WHERE id = '" . $contact_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        // if a contact was found, update contact
        if (mysqli_num_rows($result) > 0) {
            $query = "UPDATE contacts
                     SET
                        salutation = '" . escape($liveform->get_field_value('salutation')) . "',
                        first_name = '" . escape($liveform->get_field_value('first_name')) . "',
                        last_name = '" . escape($liveform->get_field_value('last_name')) . "',
                        suffix = '" . escape($liveform->get_field_value('suffix')) . "',
                        company = '" . escape($liveform->get_field_value('company')) . "',
                        title = '" . escape($liveform->get_field_value('title')) . "',
                        business_address_1 = '" . escape($liveform->get_field_value('business_address_1')) . "',
                        business_address_2 = '" . escape($liveform->get_field_value('business_address_2')) . "',
                        business_city = '" . escape($liveform->get_field_value('business_city')) . "',
                        business_state = '" . escape($liveform->get_field_value('business_state')) . "',
                        business_zip_code = '" . escape($liveform->get_field_value('business_zip_code')) . "',
                        business_phone = '" . escape($liveform->get_field_value('business_phone')) . "',
                        business_fax = '" . escape($liveform->get_field_value('business_fax')) . "',
                        business_country = '" . escape($liveform->get_field_value('business_country')) . "',
                        home_phone = '" . escape($liveform->get_field_value('home_phone')) . "',
                        mobile_phone = '" . escape($liveform->get_field_value('mobile_phone')) . "',
                        user = $user_id,
                        timestamp = UNIX_TIMESTAMP()
                     WHERE id = $contact_id";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        // else a contact was not found, so create contact
        } else {
            $query = "INSERT INTO contacts (
                        salutation,
                        first_name,
                        last_name,
                        suffix,
                        company,
                        title,
                        business_address_1,
                        business_address_2,
                        business_city,
                        business_state,
                        business_zip_code,
                        business_phone,
                        business_fax,
                        business_country,
                        home_phone,
                        mobile_phone,
                        user,
                        timestamp)
                     VALUES (
                        '" . escape($liveform->get_field_value('salutation')) . "',
                        '" . escape($liveform->get_field_value('first_name')) . "',
                        '" . escape($liveform->get_field_value('last_name')) . "',
                        '" . escape($liveform->get_field_value('suffix')) . "',
                        '" . escape($liveform->get_field_value('company')) . "',
                        '" . escape($liveform->get_field_value('title')) . "',
                        '" . escape($liveform->get_field_value('business_address_1')) . "',
                        '" . escape($liveform->get_field_value('business_address_2')) . "',
                        '" . escape($liveform->get_field_value('business_city')) . "',
                        '" . escape($liveform->get_field_value('business_state')) . "',
                        '" . escape($liveform->get_field_value('business_zip_code')) . "',
                        '" . escape($liveform->get_field_value('business_phone')) . "',
                        '" . escape($liveform->get_field_value('business_fax')) . "',
                        '" . escape($liveform->get_field_value('business_country')) . "',
                        '" . escape($liveform->get_field_value('home_phone')) . "',
                        '" . escape($liveform->get_field_value('mobile_phone')) . "',
                        '$user_id',
                        UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            // get contact id so we can connect user to contact
            $contact_id = mysqli_insert_id(db::$con);
            
            // check if registration contact group exists
            $query = "SELECT id FROM contact_groups WHERE id = '" . REGISTRATION_CONTACT_GROUP_ID  . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if registration contact group exists, assign contact to contact group
            if (mysqli_num_rows($result) > 0) {
                $query =
                    "INSERT INTO contacts_contact_groups_xref (
                        contact_id,
                        contact_group_id)
                    VALUES (
                        '" . $contact_id . "',
                        '" . REGISTRATION_CONTACT_GROUP_ID . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        $sql_timezone = "";

        // If this PHP version supports user timezones, then save timezone in database.
        if (version_compare(PHP_VERSION, '5.2.0', '>=') == true) {
            $sql_timezone = ", timezone = '" . escape($liveform->get_field_value('timezone')) . "'";
        }

        // update user
        $query =
            "UPDATE user
            SET
                user_contact = '$contact_id'
                $sql_timezone
            WHERE user_id = '" . $user_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        log_activity("user ($_SESSION[sessionusername]) updated account", $_SESSION['sessionusername']);
        
        $my_account->add_notice('Your profile has been updated.');

        // remove liveform because software does not need it anymore
        $liveform->remove_form('my_account_profile');

        go(get_page_type_url('my account'));

    // else an error does exist
    } else {
        // send user back to previous form
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $my_account_profile_path);
    }
}