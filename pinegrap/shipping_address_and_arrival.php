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
require_once(dirname(__FILE__) . '/shipping.php');

validate_token_field();

initialize_order();

// get ship to info
$query =
    "SELECT
        order_id,
        ship_to_name,
        first_name,
        address_verified
    FROM ship_tos
    WHERE id = '" . escape($_POST['ship_to_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if ship to is not found, output error
if (mysqli_num_rows($result) == 0) {
    output_error('The recipient could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);

// store ship to info for later use
$ship_to_name = $row['ship_to_name'];
$address_verified = $row['address_verified'];

// if order id for ship to is not equal to order id in session, output error
if ($row['order_id'] != $_SESSION['ecommerce']['order_id']) {
    output_error('You do not have access to this recipient. <a href="javascript:history.go(-1)">Go back</a>.');
}

// get page info and make sure that a shipping address & arrival page exists for the id that the user passed
$query =
    "SELECT
        page.page_name,
        shipping_address_and_arrival_pages.address_type,
        shipping_address_and_arrival_pages.form,
        next_page.page_name as next_page_name
    FROM page
    LEFT JOIN shipping_address_and_arrival_pages ON page.page_id = shipping_address_and_arrival_pages.page_id
    LEFT JOIN page AS next_page ON shipping_address_and_arrival_pages.next_page_id = next_page.page_id
    WHERE
        (page.page_id = '" . escape($_POST['page_id']) . "')
        AND (page.page_type = 'shipping address and arrival')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if a shipping address & arrival page was not found for the id that was passed, then output error
if (mysqli_num_rows($result) == 0) {
    output_error('A shipping address &amp; arrival page could not be found for the id that was passed. <a href="javascript:history.go(-1)">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);

$page_name = $row['page_name'];
$address_type = $row['address_type'];
$form = $row['form'];
$next_page_name = $row['next_page_name'];

$liveform = new liveform('shipping_address_and_arrival');

$ghost = $_SESSION['software']['ghost'];

$liveform->add_fields_to_session();

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

// if the shipping address and arrival page has the address type field enabled, then require the field
if ($address_type == 1) {
    $liveform->validate_required_field('address_type', 'Address Type is required.');
}

// If a custom shipping form is enabled, then submit form.
if ($form == 1) {

    submit_custom_form(array(
        'form' => $liveform,
        'page_id' => $_POST['page_id'],
        'page_type' => 'shipping address and arrival',
        'form_type' => 'shipping',
        'ship_to_id' => $_POST['ship_to_id'],
        'require' => true));
}

// get the number of active arrival dates in order to figure out if the requested arrival date field should be required
$query = "SELECT count(*)
         FROM arrival_dates
         WHERE (status = 'enabled') AND (start_date <= CURRENT_DATE()) AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);

// if there was at least one active arrival date, require arrival date
if ($row[0] > 0) {
    $liveform->validate_required_field('arrival_date', 'Requested Arrival Date is required.');
}

// if arrival date was submitted
if (isset($_POST['arrival_date'])) {
    // get arrival date info
    $query =
        "SELECT
            name,
            arrival_date,
            code,
            custom,
            custom_maximum_arrival_date
        FROM arrival_dates WHERE id = '" . escape($liveform->get_field_value('arrival_date')) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $arrival_date_name = $row['name'];
    $arrival_date = $row['arrival_date'];
    $arrival_date_code = $row['code'];
    $arrival_date_custom = $row['custom'];
    $arrival_date_custom_maximum_arrival_date = $row['custom_maximum_arrival_date'];

    // if selected arrival date has a custom field, set arrival date to custom arrival date and require custom field
    if ($arrival_date_custom == 1) {
        $arrival_date = prepare_form_data_for_input($liveform->get_field_value('custom_arrival_date_' . $liveform->get_field_value('arrival_date')), 'date');
        $liveform->validate_required_field('custom_arrival_date_' . $liveform->get_field_value('arrival_date'), 'A custom arrival date is required, because you selected ' . $arrival_date_name . '.');
        
        // if there is not already an error for the custom date field, then validate date
        if ($liveform->check_field_error('custom_arrival_date_' . $liveform->get_field_value('arrival_date')) == false) {
            // split date into parts
            $arrival_date_parts = explode('-', $arrival_date);
            $year = $arrival_date_parts[0];
            $month = $arrival_date_parts[1];
            $day = $arrival_date_parts[2];
            
            // if the first two digits of the year are "00", then the user probably entered a 2 digit year, so set the year to a 4 digit year with "20" at the beginning
            if (mb_substr($year, 0, 2) == '00') {
                $year = '20' . mb_substr($year, 2, 2);
                $arrival_date = $year . '-' . $month . '-' . $day;
                $liveform->assign_field_value('custom_arrival_date_' . $liveform->get_field_value('arrival_date'), prepare_form_data_for_output($arrival_date, 'date'));
            }
            
            // if custom date is not valid, then mark error
            if ((is_numeric($month) == false) || (is_numeric($day) == false) || (is_numeric($year) == false) || (checkdate($month, $day, $year) == false)) {
                $liveform->mark_error('custom_arrival_date_' . $liveform->get_field_value('arrival_date'), 'The custom arrival date is not valid.');
                $arrival_date = '';
            }
        }
        
        // if there is not already an error for the custom date field
        // and there is a maximum arrival date
        // and the submitted arrival date is greater than the maximum arrival date,
        // then prepare error and clear arrival date
        if (
            ($liveform->check_field_error('custom_arrival_date_' . $liveform->get_field_value('arrival_date')) == false)
            && ($arrival_date_custom_maximum_arrival_date != '0000-00-00')
            && ($arrival_date > $arrival_date_custom_maximum_arrival_date)
        ) {
            $liveform->mark_error('custom_arrival_date_' . $liveform->get_field_value('arrival_date'), 'The custom arrival date that you entered is after the latest allowed arrival date. Please enter a date that is on or before ' . prepare_form_data_for_output($arrival_date_custom_maximum_arrival_date, 'date') . '.');
            $arrival_date = '';
        }
    }

    // if there are no errors so far AND requested arrival date is not At Once, determine if there are any shipping methods that will deliver in time for requested arrival date
    if (($liveform->check_form_errors() == false) && ($arrival_date != '0000-00-00')) {

        // declare shipping_methods array that we will use to store all valid shipping methods for recipient's address
        $shipping_methods = array();
        
        $street_address_or_po_box = get_address_type($address_1);
        $street_address_or_po_box = str_replace(' ', '_', $street_address_or_po_box);
        
        // get the current day of the week
        $day_of_week = mb_strtolower(date('l'));
        
        // get all valid shipping methods where address type is correct
        $query = "SELECT id
                 FROM shipping_methods
                 WHERE
                    (status = 'enabled')
                    AND (start_time <= NOW())
                    AND (end_time >= NOW())
                    " . get_protected_shipping_method_filter() . "
                    AND ($street_address_or_po_box = 1)
                    AND (available_on_" . $day_of_week . " = '1')
                    AND ((available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while ($row = mysqli_fetch_assoc($result)) {
            $shipping_methods[] = $row['id'];
        }

        // get valid zones
        $zones = get_valid_zones($_POST['ship_to_id'], $liveform->get_field_value('country'), $liveform->get_field_value('state'));

        // foreach valid shipping method
        foreach ($shipping_methods as $key => $shipping_method_id) {
            $zone_found = false;

            // foreach valid zone
            foreach ($zones as $zone_id) {
                // see if this zone is allowed for this shipping method
                $query = "SELECT shipping_method_id
                         FROM shipping_methods_zones_xref
                         WHERE (shipping_method_id = $shipping_method_id) AND (zone_id = '$zone_id')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                // if this zone is allowed for this shipping method, remember that
                if (mysqli_num_rows($result) > 0) {
                    $zone_found = true;
                    // we have found a valid zone for this shipping method, so the shipping method is valid, so break
                    break;
                }
            }

            // if a valid zone was not found for this shipping method, then this shipping method is no longer valid
            if ($zone_found == false) {
                unset($shipping_methods[$key]);
            }
        }
        
        /* begin: if necessary, work out issue with no intersecting shipping methods being found for all products */
        
        // if there are no valid shipping methods
        if (count($shipping_methods) == 0) {
            /*
               No shipping methods intercepted for all order items, so we need to force shipping methods to be used.
               We force the shipping methods by the following.  We find the greatest transit time allowed for each order item,
               by looking at all of the shipping methods for an order item. We then use the shipping methods for the order item
               that had the lowest transit time.
            */

            // get all products in cart for this ship to
            $order_items = db_items(
                "SELECT order_items.product_id
                FROM order_items
                WHERE
                    order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "'
                    AND order_items.ship_to_id = '" . e($_POST['ship_to_id']) . "'");
            
            $zones_for_destination = get_valid_zones_for_destination($liveform->get_field_value('country'), $liveform->get_field_value('state'));
            
            foreach ($order_items as $key => $value) {
                
                // Get zones that are allowed for this item.
                $zones_for_order_item = db_values(
                    "SELECT zone_id
                    FROM products_zones_xref
                    WHERE product_id = '" . e($order_items[$key]['product_id']) . "'");
                
                // Get zones that are valid for both item and destination.
                $valid_zones = array_intersect($zones_for_order_item, $zones_for_destination);

                // If there are no valid zones, the skip to next item.
                if (!$valid_zones) {
                    continue;
                }

                $sql_zones = "";

                foreach ($valid_zones as $zone_id) {

                    if ($sql_zones) {
                        $sql_zones .= " OR ";
                    }

                    $sql_zones .= "shipping_methods_zones_xref.zone_id = '" . e($zone_id) . "'";
                }
                
                /* begin: get valid shipping methods for order item and find largest base transit days */
                
                $query = "SELECT
                            DISTINCT(shipping_methods_zones_xref.shipping_method_id),
                            shipping_methods.base_transit_days
                         FROM shipping_methods_zones_xref
                         LEFT JOIN shipping_methods on shipping_methods_zones_xref.shipping_method_id = shipping_methods.id
                         WHERE
                            ($sql_zones)
                            AND (shipping_methods.status = 'enabled')
                            AND (shipping_methods.start_time <= NOW())
                            AND (shipping_methods.end_time >= NOW())
                            " . get_protected_shipping_method_filter() . "
                            AND ($street_address_or_po_box = 1)
                            AND (available_on_" . $day_of_week . " = '1')
                            AND ((available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // get prepared to store shipping methods for this order item
                $order_items[$key]['shipping_methods'] = array();
                
                $largest_transit_for_order_item = 0;
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $shipping_method_id = $row['shipping_method_id'];
                    $base_transit_days = $row['base_transit_days'];
                    
                    // if the base transit days for this shipping method are larger than the largest base transit days so far, set largest base transit days
                    if ($base_transit_days > $largest_transit_for_order_item) {
                        $largest_transit_for_order_item = $base_transit_days;
                    }
                    
                    // store shipping method for this order item
                    $order_items[$key]['shipping_methods'][] = $shipping_method_id;
                }
                
                // if $smallest_transit_for_order_items has not been set yet or this order item has the smallest transit,
                // update smallest transit and remember order item that has the smallest transit
                if ((isset($smallest_transit_for_order_items) == false) || ($largest_transit_for_order_item < $smallest_transit_for_order_items)) {
                    $smallest_transit_for_order_items = $largest_transit_for_order_item;
                    $order_item_with_smallest_transit = $key;
                }
                
                /* end: get valid shipping methods for order item and find largest base transit days */
            }
            
            // valid shipping methods are all valid shipping methods for order item with smallest largest transit
            $shipping_methods = $order_items[$order_item_with_smallest_transit]['shipping_methods'];
        }

        /* end: if necessary, work out issue with no intersecting shipping methods being found for all products */

        // before we loop through all of the shipping methods we are going to assume that there are no valid shipping methods
        $valid_shipping_methods_exist = false;

        // loop through all shipping methods in order to determine if shipping method will get products to recipient by requested arrival date
        foreach ($shipping_methods as $shipping_method_id) {

            // determine if there is a shipping cut-off for the arrival date and shipping method
            $query =
                "SELECT
                    shipping_cutoffs.date_and_time
                FROM shipping_cutoffs
                LEFT JOIN arrival_dates ON shipping_cutoffs.arrival_date_id = arrival_dates.id
                WHERE
                    (arrival_dates.arrival_date = '" . escape($arrival_date) . "')
                    AND (shipping_cutoffs.shipping_method_id = '$shipping_method_id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if there is a shipping cut-off, then determine if this shipping method is valid by looking at the cut-off date & time
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $shipping_cutoff_date_and_time = $row['date_and_time'];
                
                // if the shipping cut-off date & time is greater than the current date & time, then remember that there are valid shipping methods and break out of loop
                if ($shipping_cutoff_date_and_time > date('Y-m-d H:i:s')) {
                    $valid_shipping_methods_exist = true;
                    break;
                }
                
            // else there is not a shipping cut-off, so determine if this shipping method is valid by looking at the transit time
            } else {

                $response = get_delivery_date(array(
                    'ship_to_id' => $_POST['ship_to_id'],
                    'shipping_method' => array('id' => $shipping_method_id),
                    'zip_code' => $liveform->get('zip_code'),
                    'country' => $liveform->get('country')));

                $delivery_date = $response['delivery_date'];

                // If a delivery date was found, and it is before or on the arrival date, then there
                // is at least one valid shipping method.
                if ($delivery_date and $delivery_date <= $arrival_date) {
                    $valid_shipping_methods_exist = true;
                    break;
                }
            }
        }

        // if no valid shipping methods could be found for requested arrival date, mark error for requested arrival date
        if ($valid_shipping_methods_exist == false) {
            $liveform->mark_error('arrival_date', 'We could not find a shipping method that would guarantee delivery of your shipment by the Requested Arrival Date. Please select a different Requested Arrival Date to continue.');
        }
    }
}

// save the values that were originally entered by the customer, because if the address is verified, then they will be converted to all uppercase
// if billing is the same as shipping then we don't want to save all uppercase values for billing information
$first_name_with_case_preserved = $liveform->get_field_value('first_name');
$last_name_with_case_preserved = $liveform->get_field_value('last_name');
$company_with_case_preserved = $liveform->get_field_value('company');

// Verify address if address verification is enabled and it is a US address
$address_verified = verify_address(array(
    'liveform' => $liveform,
    'address_type' => 'shipping',
    'old_address_verified' => $address_verified,
    'ship_to_id' => $_POST['ship_to_id']));

// update ship to
$query = "UPDATE ship_tos SET
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
            address_type = '" . escape($liveform->get_field_value('address_type')) . "',
            address_verified = '" . escape($address_verified) . "',
            phone_number = '" . escape($liveform->get_field_value('phone_number')) . "',
            arrival_date_id = '" . escape($liveform->get_field_value('arrival_date')) . "',
            arrival_date_code = '" . escape($arrival_date_code) . "',
            arrival_date = '" . escape($arrival_date) . "',
            complete = '0'
         WHERE id = '" . escape($_POST['ship_to_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// If the user is logged in and not ghosting, then update address book.
if (USER_LOGGED_IN and !$ghost) {

    // find recipient in address book
    $query = "SELECT id FROM address_book WHERE user = '" . e(USER_ID) . "' AND ship_to_name = '" . e($ship_to_name) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $address_book_id = $row['id'];

    // if recipient is already in the address book, update recipient
    if ($address_book_id) {
        $sql_address_type = "";
        
        // if the address type field is enabled, then update address type in address book
        if ($address_type == 1) {
            $sql_address_type = "address_type = '" . escape($liveform->get_field_value('address_type')) . "',";
        }
        
        $query = "UPDATE address_book SET
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
                 WHERE id = '$address_book_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // else recipient is not already in the address book, so create recipient
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
                    '" . e(USER_ID) . "',
                    '" . escape($ship_to_name) . "',
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
}

// if an error does not exist
if ($liveform->check_form_errors() == false) {
    // if user selected shipping same as billing option, then update the orders billing information
    if ($liveform->get_field_value('shipping_same_as_billing') == '1') {
        $query =
            "UPDATE orders
            SET
                billing_salutation = '" . escape($liveform->get_field_value('salutation')) . "',
                billing_first_name = '" . escape($first_name_with_case_preserved) . "',
                billing_last_name = '" . escape($last_name_with_case_preserved) . "',
                billing_company = '" . escape($company_with_case_preserved) . "',
                billing_address_1 = '" . escape($liveform->get_field_value('address_1')) . "',
                billing_address_2 = '" . escape($liveform->get_field_value('address_2')) . "',
                billing_city = '" . escape($liveform->get_field_value('city')) . "',
                billing_state = '" . escape($liveform->get_field_value('state')) . "',
                billing_zip_code = '" . escape($liveform->get_field_value('zip_code')) . "',
                billing_country = '" . escape($liveform->get_field_value('country')) . "',
                billing_phone_number = '" . escape($liveform->get_field_value('phone_number')) . "'
             WHERE id = '" . escape($_SESSION['ecommerce']['order_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // remove liveform because software does not need it anymore
    $liveform->remove_form('shipping_address_and_arrival');

    // send user to shipping method screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . $next_page_name . '?ship_to_id=' . $_POST['ship_to_id']);

// else an error does exist
} else {
    // send user back to previous form
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . $page_name . '?ship_to_id=' . $_POST['ship_to_id']);
}