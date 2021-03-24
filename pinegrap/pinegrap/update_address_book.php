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
    print get_update_address_book_screen();

// else user has completed form, so process form
} else {

    validate_token_field();
    
    // get address type value for page, so we know if it was enabled or not
    $query = "SELECT address_type FROM update_address_book_pages WHERE page_id = '" . escape($_POST['page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $address_type = $row['address_type'];

    $liveform = new liveform('update_address_book');

    $liveform->add_fields_to_session();

    $liveform->validate_required_field('ship_to_name', 'Ship to Name is required.');
    $liveform->validate_required_field('first_name', 'First Name is required.');
    $liveform->validate_required_field('last_name', 'Last Name is required.');
    $liveform->validate_required_field('address_1', 'Address 1 is required.');
    $liveform->validate_required_field('city', 'City is required.');
    $liveform->validate_required_field('country', 'Country is required.');

    // If a country has been selected and then determine if state and zip code are required.
    if ($liveform->get('country')) {

        // If there is a state in this system for the selected country, then require state.
        if (db(
            "SELECT states.id FROM states
            LEFT JOIN countries ON countries.id = states.country_id
            WHERE countries.code = '" . e($liveform->get('country')) . "'
            LIMIT 1")
        ) {
            $liveform->validate_required_field('state', 'State/Province is required.');
        }

        // If this country requires a zip code, then require it.
        if (
            db(
                "SELECT zip_code_required FROM countries
                WHERE code = '" . e($liveform->get('country')) . "'")
        ) {
            $liveform->validate_required_field('zip_code', 'Zip/Postal Code is required.');
        }
    }
    
    // if this page has the address type field enabled, then require the field
    if ($address_type == 1) {
        $liveform->validate_required_field('address_type', 'Address Type is required.');
    }

    // get user id
    $query = "SELECT user_id FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['user_id'];

    // check to see if ship to name is already in use
    $query = "SELECT id FROM address_book WHERE (user = $user_id) AND (id != '" . escape($_POST['id']) . "') AND (ship_to_name = '" . escape($liveform->get_field_value('ship_to_name')) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    if (mysqli_num_rows($result) > 0) {
        $liveform->mark_error('ship_to_name', 'That ship to name is already in use. Please enter a different ship to name.');
        $liveform->assign_field_value('ship_to_name', '');
    }
    
    // get update address book page, if one exists
    $query = "SELECT page_id FROM page WHERE page_type = 'update address book'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if there is a update address book page, prepare path with page name
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $update_address_book_path = PATH . encode_url_path(get_page_name($row['page_id']));
        
    // else there is not a update address book, so prepare default path
    } else {
        $update_address_book_path = PATH . SOFTWARE_DIRECTORY . '/update_address_book.php';
    }

    // if an error does not exist
    if ($liveform->check_form_errors() == false) {
        // if an id was supplied, then update current recipient in address book
        if ($_POST['id']) {
            $sql_address_type = "";
            
            // if the address type field is enabled, then update address type in address book
            if ($address_type == 1) {
                $sql_address_type = "address_type = '" . escape($liveform->get_field_value('address_type')) . "',";
            }
            
            $query = "UPDATE address_book SET
                        ship_to_name = '" . escape($liveform->get_field_value('ship_to_name')) . "',
                        salutation = '" . escape($liveform->get_field_value('salutation')) . "',
                        first_name = '" . escape($liveform->get_field_value('first_name')) . "',
                        last_name = '" . escape($liveform->get_field_value('last_name')) . "',
                        company = '" . escape($liveform->get_field_value('company')) . "',
                        address_1 = '" . escape($liveform->get_field_value('address_1')) . "',
                        address_2 = '" . escape($liveform->get_field_value('address_2')) . "',
                        city = '" . escape($liveform->get_field_value('city')) . "',
                        state = '" . escape($liveform->get_field_value('state')) . "',
                        zip_code = '" . escape($liveform->get_field_value('zip_code')) . "',
                        country = '" . escape($liveform->get_field_value('country')) . "',
                        $sql_address_type
                        phone_number = '" . escape($liveform->get_field_value('phone_number')) . "'
                     WHERE id = " . escape($_POST['id']) . " AND user = '$user_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // else an id was not supplied, so create new recipient in address book
        } else {
            $query = "INSERT INTO address_book (
                        user,
                        ship_to_name,
                        salutation,
                        first_name,
                        last_name,
                        company,
                        address_1,
                        address_2,
                        city,
                        state,
                        zip_code,
                        country,
                        address_type,
                        phone_number)
                     VALUES (
                        '$user_id',
                        '" . escape($liveform->get_field_value('ship_to_name')) . "',
                        '" . escape($liveform->get_field_value('salutation')) . "',
                        '" . escape($liveform->get_field_value('first_name')) . "',
                        '" . escape($liveform->get_field_value('last_name')) . "',
                        '" . escape($liveform->get_field_value('company')) . "',
                        '" . escape($liveform->get_field_value('address_1')) . "',
                        '" . escape($liveform->get_field_value('address_2')) . "',
                        '" . escape($liveform->get_field_value('city')) . "',
                        '" . escape($liveform->get_field_value('state')) . "',
                        '" . escape($liveform->get_field_value('zip_code')) . "',
                        '" . escape($liveform->get_field_value('country')) . "',
                        '" . escape($liveform->get_field_value('address_type')) . "',
                        '" . escape($liveform->get_field_value('phone_number')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        // add recipient to session so that ship to name appears in ship to pick lists
        add_recipient($liveform->get_field_value('ship_to_name'));

        // remove liveform because software does not need it anymore
        $liveform->remove_form('update_address_book');

        go(get_page_type_url('my account'));

    // else an error does exist
    } else {
        // send user back to previous form
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $update_address_book_path . '?id=' . $_POST['id']);
    }
}