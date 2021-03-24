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

function get_shipping_method($properties) {

    $page_id = $properties['page_id'];

    $properties = get_page_type_properties($page_id, 'shipping method');
    
    $product_description_type = $properties['product_description_type'];
    $submit_button_label = $properties['submit_button_label'];
    $next_page_id = $properties['next_page_id'];

    $layout_type = get_layout_type($page_id);

    require_once(dirname(__FILE__) . '/shipping.php');

    $form = new liveform('shipping_method');
    
    // store page id for billing information page in case we need to direct the user to the page sometime in the future (i.e. after a user removes an item from his/her cart)
    $_SESSION['ecommerce']['billing_information_page_id'] = $next_page_id;

    // if the user came from the control panel, clear ship_to_id, to prevent edit user from accessing ship to that user should not have access to
    if ((isset($_GET['from']) == true) && ($_GET['from'] == 'control_panel')) {
        unset($_GET['ship_to_id']);

    // else the user did not come from the control panel, so perform validation to make sure that this user has access to the ship to
    } else {

        // if no ship to is found, return error message
        if (!isset($_GET['ship_to_id']) || empty($_GET['ship_to_id'])) {
            $form->mark_error('', 'We\'re sorry, shipping information can only be gathered during the checkout process. <a href="javascript:history.go(-1)">Go back</a>');
            $content =
                '<div class="software_shipping_method">
                    '  . $form->get_messages() . '
                </div>';
            $form->remove();
            return $content;
        }

        $recipient = db_item(
            "SELECT
                ship_tos.order_id,
                ship_tos.ship_to_name,
                ship_tos.salutation,
                ship_tos.first_name,
                ship_tos.last_name,
                ship_tos.company,
                ship_tos.address_1,
                ship_tos.address_2,
                ship_tos.city,
                ship_tos.state,
                ship_tos.zip_code,
                ship_tos.country,
                ship_tos.arrival_date,
                ship_tos.arrival_date_id,
                ship_tos.address_verified,
                orders.status AS order_status
            FROM ship_tos
            LEFT JOIN orders ON ship_tos.order_id = orders.id
            WHERE ship_tos.id = '" . e($_GET['ship_to_id']) . "'");

        if (!$recipient) {
           output_error('Sorry, that recipient could not be found.  This might happen if you used the back button to go back to a recipient that no longer exists in the order.');
        }

        if (
            ($recipient['order_status'] == 'complete')
            or ($recipient['order_status'] == 'exported')
        ) {
            output_error('Sorry, the order for that recipient has already been completed, so that recipient may not be updated.  This might happen if you already completed your order and then used the back button to go back to a recipient.  Since your order is complete, there is nothing further you need to do.');
        }

        if (!$_SESSION['ecommerce']['order_id']) {
            output_error('Sorry, we could not find your order, so we can\'t allow you to update that recipient.  This might happen if you were inactive for a while and your session expired.  You might try starting a new order or retrieving a past order.');
        }

        if ($recipient['order_id'] != $_SESSION['ecommerce']['order_id']) {
            output_error('Sorry, that recipient belongs to a different order, so we can\'t allow you to update that recipient.  This might happen if your session expired and you started a new order, and then you used the back button to go back to a recipient for an old order.');
        }

    }

    $order_id = $_SESSION['ecommerce']['order_id'];

    if ($layout_type == 'system') {

        $ship_to_name = h($recipient['ship_to_name']);
        $salutation = h($recipient['salutation']);
        $first_name = h($recipient['first_name']);
        $last_name = h($recipient['last_name']);
        $company = h($recipient['company']);
        $address_1 = h($recipient['address_1']);
        $address_2 = h($recipient['address_2']);
        $city = h($recipient['city']);
        $state = h($recipient['state']);
        $state_code = $recipient['state'];
        $zip_code = $recipient['zip_code'];
        $country_code = $recipient['country'];
        $arrival_date = $recipient['arrival_date'];
        $arrival_date_id = $recipient['arrival_date_id'];
        $address_verified = $recipient['address_verified'];
        
        // get country name
        $query = "SELECT name FROM countries WHERE code = '" . escape($country_code) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $country = h($row['name']);
        
        // If this address is verified, then convert values that might not be upper-case to upper-case,
        // in order to match the other values.
        if ($address_verified == 1) {
            $salutation = mb_strtoupper($salutation);
            $country = mb_strtoupper($country);
        }

        // if recipient mode is multi-recipient, then prepare "for [ship to name]"
        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
            $for_ship_to_name = ' for' . ' ' . '<span class="software_highlight">' . $ship_to_name . '</span> &nbsp;';
        } else {
            $for_ship_to_name = '';
        }

        // if there is a salutation, prepare full name with salutation
        if ($salutation) {
           $full_name = $salutation . ' ' . $first_name . ' ' . $last_name;

        // else there is not a salutation, so prepare full name
        } else {
           $full_name = $first_name . ' ' . $last_name;
        }

        $address = '';

        // If there is a company, then output it.
        if ($company != '') {
            $address .= $company . '<br />';
        }

        $address .= $address_1 . '<br />';

        if ($address_2) {
            $address .= $address_2 . '<br />';
        }

        if ($city) {
            $address .= $city . ', ';
        }

        $address .= $state . ' ' . h($zip_code) . '<br />
            ' . $country;

        // get arrival date information
        $query = "SELECT name, custom FROM arrival_dates WHERE id = '$arrival_date_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if an arrival date was selected, prepare requested arrival date area
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // if selected arrival date had a custom field, use actual date in description
            if ($row['custom'] == 1) {
                $output_arrival_date = get_absolute_time(array('timestamp' => strtotime($arrival_date), 'type' => 'date', 'size' => 'long'));

            // else selected arrival date did not have a custom field, so use arrival date name
            } else {
                $output_arrival_date = h($row['name']);
            }

            $output_requested_arrival_date =
                '<td style="vertical-align: top">
                    <div class="arrival heading">Requested Arrival Date &nbsp;<a class="software_button_tiny_secondary" href="' . OUTPUT_PATH . h(encode_url_path(get_page_name($_SESSION['ecommerce']['shipping_address_and_arrival_page_id']))) . '?ship_to_id=' . h(urlencode($_GET['ship_to_id'])) . '">Change</a></div>
                    <div class="data">
                        ' . $output_arrival_date . '
                    </div>
                </td>';
        }

        // if recipient mode is multi-recipient, then add header with ship to label
        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
            $output_ship_to_header =
                '<div class="ship_to heading">Ship to <span class="software_highlight">'. $ship_to_name . '</span></div>';
        }
        
        // get all products in cart for this ship to
        $query = "SELECT
                    order_items.id,
                    order_items.product_id,
                    order_items.product_name,
                    order_items.quantity,
                    order_items.price as price,
                    order_items.discounted_by_offer,
                    order_items.calendar_event_id,
                    order_items.recurrence_number,
                    products.short_description,
                    products.full_description,
                    products.inventory,
                    products.inventory_quantity,
                    products.out_of_stock_message,
                    products.price as original_product_price,
                    products.weight,
                    products.primary_weight_points,
                    products.secondary_weight_points,
                    products.length,
                    products.width,
                    products.height,
                    products.container_required,
                    products.free_shipping,
                    products.extra_shipping_cost
                 FROM order_items
                 LEFT JOIN products ON products.id = order_items.product_id
                 WHERE order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "' AND order_items.ship_to_id = '" . escape($_GET['ship_to_id']) . "'
                 ORDER BY order_items.id ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        $order_items = array();

        // add all items in cart to array
        while ($row = mysqli_fetch_assoc($result)) {
            $order_items[] = $row;
        }

        // we will assume that all products are valid until we find one that is invalid
        $products_are_valid = true;
        
        // create array for storing inventory quantity for products,
        // so we can keep track of remaining inventory quantity as we loop through order items,
        // so that we can determine if out of stock message should be show for an order item
        $inventory_quantities = array();
        $row_count = 1;

        foreach ($order_items as $key => $value) {
            $order_item_id = $order_items[$key]['id'];
            $product_id = $order_items[$key]['product_id'];
            $name = h($order_items[$key]['product_name']);
            $quantity = $order_items[$key]['quantity'];
            $product_price = $order_items[$key]['price'] / 100;
            $original_product_price = $order_items[$key]['original_product_price'] / 100;
            $discounted_by_offer = $order_items[$key]['discounted_by_offer'];
            $calendar_event_id = $order_items[$key]['calendar_event_id'];
            $recurrence_number = $order_items[$key]['recurrence_number'];
            $short_description = $order_items[$key]['short_description'];
            $full_description = $order_items[$key]['full_description'];
            $inventory = $order_items[$key]['inventory'];
            $inventory_quantity = $order_items[$key]['inventory_quantity'];
            $out_of_stock_message = $order_items[$key]['out_of_stock_message'];

            // If the product description type for this page is full description, then use the full description.
            if ($product_description_type == 'full_description') {
                $output_description = $full_description;

            // Otherwise the product description type is short description, so use the short description.
            } else {
                $output_description = h($short_description);
            }
            
            // if inventory is enabled for the product and there is an out of stock message, then determine if we should show out of stock message
            if (
                ($inventory == 1)
                && ($out_of_stock_message != '')
                && ($out_of_stock_message != '<p></p>')
            ) {
                // if the initial inventory quantity for this product has not already been set, then set it
                if (isset($inventory_quantities[$product_id]) == FALSE) {
                    $inventory_quantities[$product_id] = $inventory_quantity;
                }
                
                // if the quantity of this order item is greater than the inventory quantity, then show out of stock message and set inventory quantity to 0
                if ($quantity > $inventory_quantities[$product_id]) {
                    $output_description .= ' ' . $out_of_stock_message;
                    $inventory_quantities[$product_id] = 0;
                    
                // else the quantity of this order items is less than the inventory quantity, so decrement inventory quantity,
                // so when we look at more products we have an accurate inventory quantity for what has been used so far
                } else {
                    $inventory_quantities[$product_id] = $inventory_quantities[$product_id] - $quantity;
                }
            }
            
            // if calendars is enabled and this order item is for a calendar event reservation, then add calendar event name and date and time range to description
            if ((CALENDARS == TRUE) && ($calendar_event_id != 0)) {
                $calendar_event = get_calendar_event($calendar_event_id, $recurrence_number);
                
                $output_description .=
                    '<p>
                        ' . h($calendar_event['name']) . '<br />
                        ' . $calendar_event['date_and_time_range'] . '
                    </p>';
            }
            
            $total_price = $product_price * $quantity;

            // if product is valid for destination address, do not prepare product warning
            if (validate_product_for_destination($product_id, $country_code, $state_code) == true) {
                $output_product_name = $name;
                $output_product_description = $output_description;

            // else product is not valid for destination address, so prepare product warning
            } else {
                $output_product_name = '<span style="color: red">' . $name . '</span>';

                // if product restriction message is set, then use it
                if (ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE) {
                    $product_restriction_message = ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE;

                // else product restriction message is not set, so use default message
                } else {
                    $product_restriction_message = 'This item cannot be delivered to the specified shipping address. Please remove it from your order to continue.';
                }

                $output_product_description =
                    '<div style="color: red">
                        ' . $output_description .
                        '<div><strong>' . h($product_restriction_message) . '</strong></div>
                    </div>';

                // remember that an invalid product exists (for later, because we don't need to find any valid shipping methods if there is an invalid product)
                $products_are_valid = false;
            }
            
            // assume that the order item is not discounted, until we find out otherwise
            $discounted = FALSE;

            // if the order item is discounted, then prepare to show that
            if ($discounted_by_offer == 1) {
                $discounted = TRUE;
            }
            
            $output_product_price = prepare_price_for_output($original_product_price * 100, $discounted, $product_price * 100, 'html');
            
            // prepare output for this product
            $output_products .=
                '<tr class="products data row_' . ($row_count % 2) . '">
                    <td class="mobile_left" style="vertical-align: top">' . $output_product_name . '</td>
                    <td class="mobile_right mobile_width" style="vertical-align: top">' . $output_product_description . '</td>
                    <td class="mobile_left" style="vertical-align: top">' . $quantity . '</td>
                    <td class="mobile_left" style="vertical-align: top; text-align: right;">' . $output_product_price . '</td>
                    <td class="mobile_left" style="vertical-align: top; text-align: right; white-space: nowrap">' . prepare_price_for_output($total_price * 100, FALSE, $discounted_price = '', 'html') . '</td>
                    <td class="mobile_right" style="vertical-align: top; text-align: center; padding-top: 0px; padding-bottom: 5px"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/remove_item_from_cart.php?order_item_id=' . $order_item_id . '&screen=shipping_method&send_to=' . h(urlencode(get_request_uri())) . get_token_query_string_field() . '" class="software_button_small_secondary remove_button">X</a></td>
                </tr>';

            $row_count++;
        }

        /* begin: find all valid shipping methods for this recipient's shipping address and requested arrival date */

        // declare shipping_methods array that we will use to store all valid shipping methods for recipient's address
        $shipping_methods = array();

        // if all products are valid (a product that can be shipped to recipient's address) then find valid shipping methods
        if ($products_are_valid == true) {
            $address_type = get_address_type($address_1);
            $address_type = str_replace(' ', '_', $address_type);
            
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
                        AND ($address_type = 1)
                        AND (available_on_" . $day_of_week . " = '1')
                        AND ((available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            while ($row = mysqli_fetch_assoc($result)) {
                $shipping_methods[] = $row['id'];
            }

            // get valid zones
            $zones = get_valid_zones($_GET['ship_to_id'], $country_code, $state_code);

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
                   At this point, we know that all order items are valid for destination,
                   however no shipping methods intercepted for all order items, so we need to force shipping methods to be used.
                   We force the shipping methods by the following.  We find the greatest transit time allowed for each order item,
                   by looking at all of the shipping methods for an order item. We then use the shipping methods for the order item
                   that had the lowest transit time.
                */
                
                $zones_for_destination = get_valid_zones_for_destination($country_code, $state_code);
                
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
                                AND ($address_type = 1)
                                AND (shipping_methods.available_on_" . $day_of_week . " = '1')
                                AND ((shipping_methods.available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (shipping_methods.available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
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

            // if requested arrival date was set (and it was not At Once), determine which shipping methods can guarantee arrival by requested arrival date
            if ($arrival_date > '0000-00-00') {
                
                // loop through all shipping methods in order to determine if shipping method will get products to recipient by requested arrival date
                foreach ($shipping_methods as $key => $shipping_method_id) {

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
                        
                        // if the shipping cut-off date & time is less than or equal to the current date & time, then this shipping method is not valid, so remove it from shipping methods array
                        if ($shipping_cutoff_date_and_time <= date('Y-m-d H:i:s')) {
                            unset($shipping_methods[$key]);
                        }
                        
                    // Otherwise there is not a shipping cut-off, so determine if this shipping
                    // method is valid by looking at the transit time
                    } else {

                        $response = get_delivery_date(array(
                            'ship_to_id' => $_GET['ship_to_id'],
                            'shipping_method' => array('id' => $shipping_method_id),
                            'zip_code' => $zip_code,
                            'country' => $country_code));

                        $delivery_date = $response['delivery_date'];

                        // If a delivery date could not be found, or it is after the requested arrival date,
                        // then this shipping method is not valid, so remove it.
                        if (!$delivery_date or $delivery_date > $arrival_date) {
                            unset($shipping_methods[$key]);
                        }
                    }
                }
            }
        }

        /* end: find all valid shipping methods for this recipient's shipping address and requested arrival date */

        // if form has not been filled out yet, get recipient data in order to populate fields
        if ($form->field_in_session('shipping_method') == false) {
            $query = "SELECT shipping_method_id FROM ship_tos WHERE id = '" . escape($_GET['ship_to_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);

            // set field values
            $form->assign_field_value('shipping_method', $row['shipping_method_id']);
        }

        // if there is at least one valid shipping method
        if (count($shipping_methods) > 0) {
            // create another array for shipping methods so that we can eventually sort shipping methods by shipping cost
            $shipping_methods_for_output = array();
            
            // determine if an active shipping discount offer exists (we will use this later below)
            $active_shipping_discount_offer_exists = check_if_active_shipping_discount_offer_exists();
            
            // loop through all valid shipping methods in order to prepare output list of shipping methods
            foreach ($shipping_methods as $shipping_method_id) {
                // get shipping method info
                $query =
                    "SELECT
                        name,
                        description,
                        service,
                        realtime_rate,
                        base_rate,
                        variable_base_rate,
                        base_rate_2,
                        base_rate_2_subtotal,
                        base_rate_3,
                        base_rate_3_subtotal,
                        base_rate_4,
                        base_rate_4_subtotal,
                        primary_weight_rate,
                        primary_weight_rate_first_item_excluded,
                        secondary_weight_rate,
                        secondary_weight_rate_first_item_excluded,
                        item_rate,
                        item_rate_first_item_excluded,
                        protected
                    FROM shipping_methods
                    WHERE id = '$shipping_method_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $shipping_method_name = $row['name'];
                $shipping_method_description = $row['description'];
                $shipping_method_service = $row['service'];
                $shipping_method_realtime_rate = $row['realtime_rate'];
                $shipping_method_base_rate = $row['base_rate'];
                $shipping_method_variable_base_rate = $row['variable_base_rate'];
                $shipping_method_base_rate_2 = $row['base_rate_2'];
                $shipping_method_base_rate_2_subtotal = $row['base_rate_2_subtotal'];
                $shipping_method_base_rate_3 = $row['base_rate_3'];
                $shipping_method_base_rate_3_subtotal = $row['base_rate_3_subtotal'];
                $shipping_method_base_rate_4 = $row['base_rate_4'];
                $shipping_method_base_rate_4_subtotal = $row['base_rate_4_subtotal'];
                $shipping_method_primary_weight_rate = $row['primary_weight_rate'];
                $shipping_method_primary_weight_rate_first_item_excluded = $row['primary_weight_rate_first_item_excluded'];
                $shipping_method_secondary_weight_rate = $row['secondary_weight_rate'];
                $shipping_method_secondary_weight_rate_first_item_excluded = $row['secondary_weight_rate_first_item_excluded'];
                $shipping_method_item_rate = $row['item_rate'];
                $shipping_method_item_rate_first_item_excluded = $row['item_rate_first_item_excluded'];
                $protected = $row['protected'];

                // loop through all valid zones in order to find a zone that is allowed for this shipping method
                foreach ($zones as $zone_id) {
                    $query = "SELECT shipping_method_id
                             FROM shipping_methods_zones_xref
                             WHERE (shipping_method_id = '$shipping_method_id') AND (zone_id = '$zone_id')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    if (mysqli_num_rows($result) > 0) {
                        // get zone info
                        $query = "SELECT base_rate, primary_weight_rate, secondary_weight_rate, item_rate FROM zones WHERE id = '$zone_id'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);

                        $zone_base_rate = $row['base_rate'];
                        $zone_primary_weight_rate = $row['primary_weight_rate'];
                        $zone_secondary_weight_rate = $row['secondary_weight_rate'];
                        $zone_item_rate = $row['item_rate'];

                        // we have found a zone, so we need to break out of this loop
                        break;
                    }
                }

                /* begin: get shipping cost for this shipping method */

                $shipping_cost = 0;

                $realtime_rate = get_shipping_realtime_rate(array(
                    'service' => $shipping_method_service,
                    'realtime_rate' => $shipping_method_realtime_rate,
                    'state' => $state_code,
                    'zip_code' => $zip_code,
                    'items' => $order_items));

                // If there was an error getting the realtime rate, then this shipping method is
                // not valid, so skip to the next method.
                if ($realtime_rate === false) {
                    continue;
                }

                $shipping_cost += $realtime_rate;

                $shipping_cost += get_shipping_method_base_rate(array(
                    'base_rate' => $shipping_method_base_rate,
                    'variable_base_rate' => $shipping_method_variable_base_rate,
                    'base_rate_2' => $shipping_method_base_rate_2,
                    'base_rate_2_subtotal' => $shipping_method_base_rate_2_subtotal,
                    'base_rate_3' => $shipping_method_base_rate_3,
                    'base_rate_3_subtotal' => $shipping_method_base_rate_3_subtotal,
                    'base_rate_4' => $shipping_method_base_rate_4,
                    'base_rate_4_subtotal' => $shipping_method_base_rate_4_subtotal,
                    'ship_to_id' => $_GET['ship_to_id']));

                $shipping_cost += $zone_base_rate;

                // Create a counter so that later we can determine if the first item
                // should be excluded from shipping charges based on shipping method settings.
                $count = 0;

                // loop through all items in cart for this recipient in order to calculate shipping cost
                foreach ($order_items as $key => $value) {
                    $quantity = $order_items[$key]['quantity'];
                    $primary_weight_points = $order_items[$key]['primary_weight_points'];
                    $secondary_weight_points = $order_items[$key]['secondary_weight_points'];
                    $free_shipping = $order_items[$key]['free_shipping'];
                    $extra_shipping_cost = $order_items[$key]['extra_shipping_cost'];

                    // if this is not a free shipping product, calculate shipping cost for product
                    if ($free_shipping == 0) {
                        $count++;

                        $shipping_method_primary_weight_rate_quantity = $quantity;
                        $shipping_method_secondary_weight_rate_quantity = $quantity;
                        $shipping_method_item_rate_quantity = $quantity;

                        // If this is the first order item that has a shipping charge,
                        // then check if we need to reduce the quantity in order to exclude the first item
                        // from shipping charges.
                        if ($count == 1) {
                            // If the first item should be excluded for the primary weight calculation
                            // for the shipping method, then reduce the quantity by one.
                            if ($shipping_method_primary_weight_rate_first_item_excluded == 1) {
                                $shipping_method_primary_weight_rate_quantity--;
                            }

                            // If the first item should be excluded for the secondary weight calculation
                            // for the shipping method, then reduce the quantity by one.
                            if ($shipping_method_secondary_weight_rate_first_item_excluded == 1) {
                                $shipping_method_secondary_weight_rate_quantity--;
                            }

                            // If the first item should be excluded for the item calculation
                            // for the shipping method, then reduce the quantity by one.
                            if ($shipping_method_item_rate_first_item_excluded == 1) {
                                $shipping_method_item_rate_quantity--;
                            }
                        }

                        $shipping_method_cost = ($primary_weight_points * $shipping_method_primary_weight_rate * $shipping_method_primary_weight_rate_quantity) + ($secondary_weight_points * $shipping_method_secondary_weight_rate * $shipping_method_secondary_weight_rate_quantity) + ($shipping_method_item_rate * $shipping_method_item_rate_quantity);
                        $zone_cost = ($primary_weight_points * $zone_primary_weight_rate * $quantity) + ($secondary_weight_points * $zone_secondary_weight_rate * $quantity) + ($zone_item_rate * $quantity);
                        $extra_shipping_cost = $extra_shipping_cost * $quantity;
                        $shipping_cost_for_item = $shipping_method_cost + $zone_cost + $extra_shipping_cost;
                        $shipping_cost = $shipping_cost + $shipping_cost_for_item;
                    }
                }
                
                // prepare for checking for shipping discounts
                $original_shipping_cost = 0;
                
                // if an active shipping discount offer exists, then check if there is an offer that will discount this shipping method
                if ($active_shipping_discount_offer_exists == TRUE) {
                    $offer = get_best_shipping_discount_offer($_GET['ship_to_id'], $shipping_method_id);
                    
                    // if a shipping discount offer was found, then discount shipping
                    if ($offer != FALSE) {
                        // remember the original shipping cost, before it is updated
                        $original_shipping_cost = $shipping_cost;
                        
                        // update shipping cost to contain discount
                        $shipping_cost = $shipping_cost - ($shipping_cost * ($offer['discount_shipping_percentage'] / 100));
                    }
                }
                
                // convert shipping cost from cents to dollars
                $shipping_cost = $shipping_cost / 100;
                $original_shipping_cost = $original_shipping_cost / 100;
                
                /* end: get shipping cost for this shipping method */

                // add this shipping method data to array so we can order shipping methods by shipping cost
                $shipping_methods_for_output[] =
                    array(
                        'shipping_cost'=>$shipping_cost,
                        'original_shipping_cost'=>$original_shipping_cost,
                        'shipping_method_name'=>$shipping_method_name,
                        'shipping_method_id'=>$shipping_method_id,
                        'shipping_method_description'=>$shipping_method_description,
                        'protected' => $protected);
            }
            
            // sort shipping methods by cost
            sort($shipping_methods_for_output);

            // If there is only one shipping method, then select it.
            if (count($shipping_methods_for_output) == 1) {
                $form->assign_field_value('shipping_method', $shipping_methods_for_output[0]['shipping_method_id']);
            }

            $row_count = 1;

            // loop through all shipping methods to prepare list of shipping methods
            foreach ($shipping_methods_for_output as $key => $value) {

                $software_notice_class = '';

                if ($value['protected']) {
                    $software_notice_class = ' software_notice';
                }

                // if the shipping cost is discounted, then prepare shipping cost in a certain way
                if ($shipping_methods_for_output[$key]['original_shipping_cost'] != 0) {
                    $original_price = $shipping_methods_for_output[$key]['original_shipping_cost'] * 100;
                    $discounted = TRUE;
                    $discounted_price = $shipping_methods_for_output[$key]['shipping_cost'] * 100;
                    
                // else the shipping cost is not discounted, so prepare shipping cost in a different way
                } else {
                    $original_price = $shipping_methods_for_output[$key]['shipping_cost'] * 100;
                    $discounted = FALSE;
                    $discounted_price = '';
                }
                
                $output_shipping_method_rows .=
                    '<tr class="data row_' . ($row_count % 2) . $software_notice_class . '">
                        <td style="vertical-align: top; padding-right: 15px">' . $form->output_field(array('type'=>'radio', 'name'=>'shipping_method', 'id'=>$shipping_methods_for_output[$key]['shipping_method_id'], 'value'=>$shipping_methods_for_output[$key]['shipping_method_id'],  'class'=>'software_input_radio', 'required' => 'true')) . '<label for="' . $shipping_methods_for_output[$key]['shipping_method_id'] . '"> ' . $shipping_methods_for_output[$key]['shipping_method_name'] . '</label></td>
                        <td style="vertical-align: top; text-align: right; padding-right: 15px">' . prepare_price_for_output($original_price, $discounted, $discounted_price, 'html') . '</td>
                        <td style="vertical-align: top">' . $shipping_methods_for_output[$key]['shipping_method_description'] . '</td>
                    </tr>';

                $row_count++;
            }

            $output_shipping_methods =
                '<table class="shipping_methods" width="100%" cellspacing="2" cellpadding="2" border="0" style="margin-bottom: 15px">
                    <tr class="heading" style="border: none">
                        <th style="text-align:left;">Select One</th>
                        <th style="text-align:right;padding-right:15px;">Cost</th>
                        <th style="text-align:left;">Details</th>
                    </tr>
                    ' . $output_shipping_method_rows . '
                </table>';
                        
            // if a submit button label was entered for the page, then use that
            if ($submit_button_label) {
                $output_submit_button_label = h($submit_button_label);
                
            // else a submit button label could not be found, so use a default label
            } else {
                $output_submit_button_label = 'Continue';
            }

            $output_submit_button = '<div style="text-align: right"><input type="submit" name="submit" value="' . $output_submit_button_label . '" class="software_input_submit_primary ship_method_button" /></div>';

        // else there are no valid shipping methods
        } else {
            // if product restriction message is set, then use it
            if (ECOMMERCE_NO_SHIPPING_METHODS_MESSAGE) {
                $no_shipping_methods_message = ECOMMERCE_NO_SHIPPING_METHODS_MESSAGE;

            // else product restriction message is not set, so use default message
            } else {
                $no_shipping_methods_message = 'We could not find any shipping methods that would meet the requirements of this recipient.';
            }

            $output_shipping_methods = '<div style="color: red; font-weight: bold; margin-bottom: 15px">' . h($no_shipping_methods_message) . '</div>';
        }

        $output =
            $form->output_errors() .
            '<form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shipping_method.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="page_id" value="' . $page_id . '" />
                <input type="hidden" name="next_page_id" value="' . $next_page_id . '" />
                <input type="hidden" name="ship_to_id" value="' . h($_GET['ship_to_id']) . '" />
                <table class="shipping_info" style="width: 100%; margin-bottom: 15px">
                    <tr>
                        <td style="vertical-align: top">
                            <div class="address heading">Shipping Address' . $for_ship_to_name . ' <a class="software_button_tiny_secondary" href="' . OUTPUT_PATH . h(encode_url_path(get_page_name($_SESSION['ecommerce']['shipping_address_and_arrival_page_id']))) . '?ship_to_id=' . h(urlencode($_GET['ship_to_id'])) . '">Change</a></div>
                            <div class="data">
                                ' . $full_name . '<br />
                                ' . $address . '
                            </div>
                        </td>
                        ' . $output_requested_arrival_date . '
                    </tr>
                </table>
                ' . $output_ship_to_header . '
                <table class="products" width="100%" cellspacing="2" cellpadding="2" border="0" style="margin-bottom: 15px">
                    <tr class="products heading mobile_hide" style="border: none">
                        <th class="heading_item" style="text-align: left">Item</th>
                        <th class="heading_description" style="text-align: left">Description</th>
                        <th class="heading_selection" style="text-align: left">Qty</th>
                        <th class="heading_price" style="text-align: right">Price</th>
                        <th class="heading_amount" style="text-align: right">Amount</th>
                        <th>&nbsp;</th>
                    </tr>
                    ' . $output_products . '
                </table>
                <div class="methods heading">Possible Shipping Methods' . $for_ship_to_name . '</div>
                ' . $output_shipping_methods . '
                ' . $output_submit_button . '
            </form>
            ' . get_update_currency_form();

        $form->remove('shipping_method');

        return
            '<div class="software_shipping_method">
                '  . $output . '
            </div>';

    // Otherwise this is a custom layout.
    } else {

        $ship_to_name = $recipient['ship_to_name'];
        $salutation = $recipient['salutation'];
        $first_name = $recipient['first_name'];
        $last_name = $recipient['last_name'];
        $company = $recipient['company'];
        $address_1 = $recipient['address_1'];
        $address_2 = $recipient['address_2'];
        $city = $recipient['city'];
        $state = $recipient['state'];
        $state_code = $recipient['state'];
        $zip_code = $recipient['zip_code'];
        $country_code = $recipient['country'];
        $address_verified = $recipient['address_verified'];
        $requested_arrival_date = $recipient['arrival_date'];
        $arrival_date_id = $recipient['arrival_date_id'];
        
        // get country name
        $country = db_value("SELECT name FROM countries WHERE code = '" . e($country_code) . "'");
        
        // If this address is verified, then convert values that might not be upper-case to upper-case,
        // in order to match the other values.
        if ($address_verified) {
            $salutation = mb_strtoupper($salutation);
            $country = mb_strtoupper($country);
        }

        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shipping_method.php" ' .
            'method="post"';

        $update_url = PATH . encode_url_path(get_page_name($_SESSION['ecommerce']['shipping_address_and_arrival_page_id'])) . '?ship_to_id=' . urlencode($_GET['ship_to_id']);

        $arrival_date = array();

        if ($arrival_date_id) {

            $arrival_date = db_item(
                "SELECT id, name, custom
                FROM arrival_dates
                WHERE id = '$arrival_date_id'");

            if ($arrival_date) {
                $arrival_date['date'] = $requested_arrival_date;
                $arrival_date['date_info'] = get_absolute_time(array('timestamp' => strtotime($arrival_date['date']), 'type' => 'date', 'size' => 'long'));
            }

        }

        // if product restriction message is set, then use it
        if (ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE) {
            $product_restriction_message = ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE;

        // else product restriction message is not set, so use default message
        } else {
            $product_restriction_message = 'This item cannot be delivered to the specified shipping address. Please remove it from your order to continue.';
        }

        // Get all items in cart for this ship to.
        $items = db_items(
            "SELECT
                order_items.id,
                order_items.product_id,
                order_items.product_name AS name,
                order_items.quantity,
                order_items.price / 100 AS price,
                order_items.discounted_by_offer,
                order_items.calendar_event_id,
                order_items.recurrence_number,
                products.image_name,
                products.short_description,
                products.full_description,
                products.selection_type,
                products.inventory,
                products.inventory_quantity,
                products.out_of_stock_message,
                products.price / 100 AS product_price,
                products.weight,
                products.primary_weight_points,
                products.secondary_weight_points,
                products.length,
                products.width,
                products.height,
                products.container_required,
                products.free_shipping,
                products.extra_shipping_cost
            FROM order_items
            LEFT JOIN products ON products.id = order_items.product_id
            WHERE
                (order_items.order_id = '" . e($order_id) . "')
                AND (order_items.ship_to_id = '" . e($_GET['ship_to_id']) . "')
            ORDER BY order_items.id");

        // we will assume that all products are valid until we find one that is invalid
        $products_are_valid = true;

        // create array for storing inventory quantity for products,
        // so we can keep track of remaining inventory quantity as we loop through order items,
        // so that we can determine if out of stock message should be show for an order item
        $inventory_quantities = array();

        foreach ($items as $key => $item) {

            $item['amount'] = $item['price'] * $item['quantity'];

            $item['image_url'] = '';

            if ($item['image_name'] != '') {
                $item['image_url'] = PATH . encode_url_path($item['image_name']);
            }

            $item['show_out_of_stock_message'] = false;

            // if inventory is enabled for the product and there is an out of stock message, then determine if we should show out of stock message
            if (
                $item['inventory']
                and ($item['out_of_stock_message'] != '')
            ) {
                // if the initial inventory quantity for this product has not already been set, then set it
                if (!isset($inventory_quantities[$item['product_id']])) {
                    $inventory_quantities[$item['product_id']] = $item['inventory_quantity'];
                }
                
                // if the quantity of this order item is greater than the inventory quantity, then show out of stock message and set inventory quantity to 0
                if ($item['quantity'] > $inventory_quantities[$item['product_id']]) {
                    $item['show_out_of_stock_message'] = true;
                    $inventory_quantities[$item['product_id']] = 0;
                    
                // else the quantity of this order items is less than the inventory quantity, so decrement inventory quantity,
                // so when we look at more products we have an accurate inventory quantity for what has been used so far
                } else {
                    $inventory_quantities[$item['product_id']] = $inventory_quantities[$item['product_id']] - $item['quantity'];
                }
            }

            // If calendars is enabled and this order item is for a calendar event,
            // then get calendar event info like the name and date & time.
            if (CALENDARS and $item['calendar_event_id']) {
                $item['calendar_event'] = get_calendar_event($item['calendar_event_id'], $item['recurrence_number']);
            }

            $item['product_restriction'] = false;

            if (!validate_product_for_destination($item['product_id'], $country_code, $state_code)) {
                $item['product_restriction'] = true;

                // remember that an invalid product exists (for later, because we don't need to find any valid shipping methods if there is an invalid product)
                $products_are_valid = false;
            }

            $item['discounted'] = false;

            if ($item['discounted_by_offer']) {
                $item['discounted'] = true;
            }

            $item['price_info'] = prepare_price_for_output($item['product_price'] * 100, $item['discounted'], $item['price'] * 100, 'html');

            $item['amount_info'] = prepare_price_for_output($item['amount'] * 100, false, $discounted_price = '', 'html');

            $item['remove_url'] = PATH . SOFTWARE_DIRECTORY . '/remove_item_from_cart.php?order_item_id=' . $item['id'] . '&screen=shipping_method&send_to=' . urlencode(REQUEST_URL) . '&token=' . $_SESSION['software']['token'];

            $items[$key] = $item;

        }

        /* begin: find all valid shipping methods for this recipient's shipping address and requested arrival date */

        // declare shipping_methods array that we will use to store all valid shipping methods for recipient's address
        $shipping_methods = array();

        // if all products are valid (a product that can be shipped to recipient's address) then find valid shipping methods
        if ($products_are_valid == true) {
            $address_type = get_address_type($address_1);
            $address_type = str_replace(' ', '_', $address_type);
            
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
                        AND ($address_type = 1)
                        AND (available_on_" . $day_of_week . " = '1')
                        AND ((available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            while ($row = mysqli_fetch_assoc($result)) {
                $shipping_methods[] = $row['id'];
            }

            // get valid zones
            $zones = get_valid_zones($_GET['ship_to_id'], $country_code, $state_code);

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
                   At this point, we know that all order items are valid for destination,
                   however no shipping methods intercepted for all order items, so we need to force shipping methods to be used.
                   We force the shipping methods by the following.  We find the greatest transit time allowed for each order item,
                   by looking at all of the shipping methods for an order item. We then use the shipping methods for the order item
                   that had the lowest transit time.
                */
                
                $zones_for_destination = get_valid_zones_for_destination($country_code, $state_code);
                
                foreach ($items as $key => $value) {
                    
                    // Get zones that are allowed for this item.
                    $zones_for_order_item = db_values(
                        "SELECT zone_id
                        FROM products_zones_xref
                        WHERE product_id = '" . e($items[$key]['product_id']) . "'");
                    
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
                                AND ($address_type = 1)
                                AND (shipping_methods.available_on_" . $day_of_week . " = '1')
                                AND ((shipping_methods.available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (shipping_methods.available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // get prepared to store shipping methods for this order item
                    $items[$key]['shipping_methods'] = array();
                    
                    $largest_transit_for_order_item = 0;
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $shipping_method_id = $row['shipping_method_id'];
                        $base_transit_days = $row['base_transit_days'];
                        
                        // if the base transit days for this shipping method are larger than the largest base transit days so far, set largest base transit days
                        if ($base_transit_days > $largest_transit_for_order_item) {
                            $largest_transit_for_order_item = $base_transit_days;
                        }
                        
                        // store shipping method for this order item
                        $items[$key]['shipping_methods'][] = $shipping_method_id;
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
                $shipping_methods = $items[$order_item_with_smallest_transit]['shipping_methods'];
            }

            /* end: if necessary, work out issue with no intersecting shipping methods being found for all products */

            // if requested arrival date was set (and it was not At Once), determine which shipping methods can guarantee arrival by requested arrival date
            if ($requested_arrival_date > '0000-00-00') {
                
                // loop through all shipping methods in order to determine if shipping method will get products to recipient by requested arrival date
                foreach ($shipping_methods as $key => $shipping_method_id) {

                    // determine if there is a shipping cut-off for the arrival date and shipping method
                    $query =
                        "SELECT
                            shipping_cutoffs.date_and_time
                        FROM shipping_cutoffs
                        LEFT JOIN arrival_dates ON shipping_cutoffs.arrival_date_id = arrival_dates.id
                        WHERE
                            (arrival_dates.arrival_date = '" . e($requested_arrival_date) . "')
                            AND (shipping_cutoffs.shipping_method_id = '$shipping_method_id')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // if there is a shipping cut-off, then determine if this shipping method is valid by looking at the cut-off date & time
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $shipping_cutoff_date_and_time = $row['date_and_time'];
                        
                        // if the shipping cut-off date & time is less than or equal to the current date & time, then this shipping method is not valid, so remove it from shipping methods array
                        if ($shipping_cutoff_date_and_time <= date('Y-m-d H:i:s')) {
                            unset($shipping_methods[$key]);
                        }
                        
                    // Otherwise there is not a shipping cut-off, so determine if this shipping
                    // method is valid by looking at the transit time.
                    } else {

                        $response = get_delivery_date(array(
                            'ship_to_id' => $_GET['ship_to_id'],
                            'shipping_method' => array('id' => $shipping_method_id),
                            'zip_code' => $zip_code,
                            'country' => $country_code));

                        $delivery_date = $response['delivery_date'];

                        // If a delivery date could not be found, or it is after the requested arrival date,
                        // then this shipping method is not valid, so remove it.
                        if (!$delivery_date or $delivery_date > $requested_arrival_date) {
                            unset($shipping_methods[$key]);
                        }
                    }
                }
            }
        }

        /* end: find all valid shipping methods for this recipient's shipping address and requested arrival date */

        // if form has not been filled out yet, get selected shipping method.
        if (!$form->field_in_session('shipping_method')) {

            $shipping_method_id = db_value("SELECT shipping_method_id FROM ship_tos WHERE id = '" . e($_GET['ship_to_id']) . "'");

            if ($shipping_method_id) {
                $form->set('shipping_method', $shipping_method_id);
            }
            
        }

        // if there is at least one valid shipping method
        if (count($shipping_methods) > 0) {
            // create another array for shipping methods so that we can eventually sort shipping methods by shipping cost
            $shipping_methods_for_output = array();
            
            // determine if an active shipping discount offer exists (we will use this later below)
            $active_shipping_discount_offer_exists = check_if_active_shipping_discount_offer_exists();
            
            // loop through all valid shipping methods in order to prepare output list of shipping methods
            foreach ($shipping_methods as $shipping_method_id) {
                // get shipping method info
                $query =
                    "SELECT
                        name,
                        description,
                        service,
                        realtime_rate,
                        base_rate,
                        variable_base_rate,
                        base_rate_2,
                        base_rate_2_subtotal,
                        base_rate_3,
                        base_rate_3_subtotal,
                        base_rate_4,
                        base_rate_4_subtotal,
                        primary_weight_rate,
                        primary_weight_rate_first_item_excluded,
                        secondary_weight_rate,
                        secondary_weight_rate_first_item_excluded,
                        item_rate,
                        item_rate_first_item_excluded,
                        protected
                    FROM shipping_methods
                    WHERE id = '$shipping_method_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $shipping_method_name = $row['name'];
                $shipping_method_description = $row['description'];
                $shipping_method_service = $row['service'];
                $shipping_method_realtime_rate = $row['realtime_rate'];
                $shipping_method_base_rate = $row['base_rate'];
                $shipping_method_variable_base_rate = $row['variable_base_rate'];
                $shipping_method_base_rate_2 = $row['base_rate_2'];
                $shipping_method_base_rate_2_subtotal = $row['base_rate_2_subtotal'];
                $shipping_method_base_rate_3 = $row['base_rate_3'];
                $shipping_method_base_rate_3_subtotal = $row['base_rate_3_subtotal'];
                $shipping_method_base_rate_4 = $row['base_rate_4'];
                $shipping_method_base_rate_4_subtotal = $row['base_rate_4_subtotal'];
                $shipping_method_primary_weight_rate = $row['primary_weight_rate'];
                $shipping_method_primary_weight_rate_first_item_excluded = $row['primary_weight_rate_first_item_excluded'];
                $shipping_method_secondary_weight_rate = $row['secondary_weight_rate'];
                $shipping_method_secondary_weight_rate_first_item_excluded = $row['secondary_weight_rate_first_item_excluded'];
                $shipping_method_item_rate = $row['item_rate'];
                $shipping_method_item_rate_first_item_excluded = $row['item_rate_first_item_excluded'];
                $protected = $row['protected'];

                // loop through all valid zones in order to find a zone that is allowed for this shipping method
                foreach ($zones as $zone_id) {
                    $query = "SELECT shipping_method_id
                             FROM shipping_methods_zones_xref
                             WHERE (shipping_method_id = '$shipping_method_id') AND (zone_id = '$zone_id')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    if (mysqli_num_rows($result) > 0) {
                        // get zone info
                        $query = "SELECT base_rate, primary_weight_rate, secondary_weight_rate, item_rate FROM zones WHERE id = '$zone_id'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);

                        $zone_base_rate = $row['base_rate'];
                        $zone_primary_weight_rate = $row['primary_weight_rate'];
                        $zone_secondary_weight_rate = $row['secondary_weight_rate'];
                        $zone_item_rate = $row['item_rate'];

                        // we have found a zone, so we need to break out of this loop
                        break;
                    }
                }

                /* begin: get shipping cost for this shipping method */

                $shipping_cost = 0;

                $realtime_rate = get_shipping_realtime_rate(array(
                    'service' => $shipping_method_service,
                    'realtime_rate' => $shipping_method_realtime_rate,
                    'state' => $state_code,
                    'zip_code' => $zip_code,
                    'items' => $items));

                // If there was an error getting the realtime rate, then this shipping method is
                // not valid, so skip to the next method.
                if ($realtime_rate === false) {
                    continue;
                }

                $shipping_cost += $realtime_rate;

                $shipping_cost += get_shipping_method_base_rate(array(
                    'base_rate' => $shipping_method_base_rate,
                    'variable_base_rate' => $shipping_method_variable_base_rate,
                    'base_rate_2' => $shipping_method_base_rate_2,
                    'base_rate_2_subtotal' => $shipping_method_base_rate_2_subtotal,
                    'base_rate_3' => $shipping_method_base_rate_3,
                    'base_rate_3_subtotal' => $shipping_method_base_rate_3_subtotal,
                    'base_rate_4' => $shipping_method_base_rate_4,
                    'base_rate_4_subtotal' => $shipping_method_base_rate_4_subtotal,
                    'ship_to_id' => $_GET['ship_to_id']));

                $shipping_cost += $zone_base_rate;

                // Create a counter so that later we can determine if the first item
                // should be excluded from shipping charges based on shipping method settings.
                $count = 0;

                // loop through all items in cart for this recipient in order to calculate shipping cost
                foreach ($items as $key => $value) {
                    $quantity = $items[$key]['quantity'];
                    $primary_weight_points = $items[$key]['primary_weight_points'];
                    $secondary_weight_points = $items[$key]['secondary_weight_points'];
                    $free_shipping = $items[$key]['free_shipping'];
                    $extra_shipping_cost = $items[$key]['extra_shipping_cost'];

                    // if this is not a free shipping product, calculate shipping cost for product
                    if ($free_shipping == 0) {
                        $count++;

                        $shipping_method_primary_weight_rate_quantity = $quantity;
                        $shipping_method_secondary_weight_rate_quantity = $quantity;
                        $shipping_method_item_rate_quantity = $quantity;

                        // If this is the first order item that has a shipping charge,
                        // then check if we need to reduce the quantity in order to exclude the first item
                        // from shipping charges.
                        if ($count == 1) {
                            // If the first item should be excluded for the primary weight calculation
                            // for the shipping method, then reduce the quantity by one.
                            if ($shipping_method_primary_weight_rate_first_item_excluded == 1) {
                                $shipping_method_primary_weight_rate_quantity--;
                            }

                            // If the first item should be excluded for the secondary weight calculation
                            // for the shipping method, then reduce the quantity by one.
                            if ($shipping_method_secondary_weight_rate_first_item_excluded == 1) {
                                $shipping_method_secondary_weight_rate_quantity--;
                            }

                            // If the first item should be excluded for the item calculation
                            // for the shipping method, then reduce the quantity by one.
                            if ($shipping_method_item_rate_first_item_excluded == 1) {
                                $shipping_method_item_rate_quantity--;
                            }
                        }

                        $shipping_method_cost = ($primary_weight_points * $shipping_method_primary_weight_rate * $shipping_method_primary_weight_rate_quantity) + ($secondary_weight_points * $shipping_method_secondary_weight_rate * $shipping_method_secondary_weight_rate_quantity) + ($shipping_method_item_rate * $shipping_method_item_rate_quantity);
                        $zone_cost = ($primary_weight_points * $zone_primary_weight_rate * $quantity) + ($secondary_weight_points * $zone_secondary_weight_rate * $quantity) + ($zone_item_rate * $quantity);
                        $extra_shipping_cost = $extra_shipping_cost * $quantity;
                        $shipping_cost_for_item = $shipping_method_cost + $zone_cost + $extra_shipping_cost;
                        $shipping_cost = $shipping_cost + $shipping_cost_for_item;
                    }
                }
                
                // prepare for checking for shipping discounts
                $original_shipping_cost = 0;

                $discounted = false;
                
                // if an active shipping discount offer exists, then check if there is an offer that will discount this shipping method
                if ($active_shipping_discount_offer_exists == TRUE) {
                    $offer = get_best_shipping_discount_offer($_GET['ship_to_id'], $shipping_method_id);
                    
                    // if a shipping discount offer was found, then discount shipping
                    if ($offer != FALSE) {
                        // remember the original shipping cost, before it is updated
                        $original_shipping_cost = $shipping_cost;
                        
                        // update shipping cost to contain discount
                        $shipping_cost = $shipping_cost - ($shipping_cost * ($offer['discount_shipping_percentage'] / 100));

                        $discounted = true;

                    }
                }
                
                // convert shipping cost from cents to dollars
                $shipping_cost = $shipping_cost / 100;
                $original_shipping_cost = $original_shipping_cost / 100;

                if ($discounted) {
                    $cost_info = prepare_price_for_output($original_shipping_cost * 100, $discounted, $shipping_cost * 100, 'html');
                } else {
                    $discounted_shipping_cost = '';
                    $original_shipping_cost = $shipping_cost;

                    $cost_info = prepare_price_for_output($shipping_cost * 100, $discounted, $discounted_shipping_cost, 'html');
                }
                
                /* end: get shipping cost for this shipping method */

                // add this shipping method data to array so we can order shipping methods by shipping cost
                $shipping_methods_for_output[] =
                    array(
                        'cost' => $shipping_cost,
                        'original_cost' => $original_shipping_cost,
                        'cost_info' => $cost_info,
                        'discounted' => $discounted,
                        'name' => $shipping_method_name,
                        'id' => $shipping_method_id,
                        'description' => $shipping_method_description,
                        'protected' => $protected);
            }
            
            // sort shipping methods by cost
            sort($shipping_methods_for_output);

            $shipping_methods = $shipping_methods_for_output;

            // If there is only one shipping method, then select it.
            if (count($shipping_methods) == 1) {
                $form->set('shipping_method', $shipping_methods[0]['id']);
            }

        }

        if (ECOMMERCE_NO_SHIPPING_METHODS_MESSAGE) {
            $no_shipping_methods_message = ECOMMERCE_NO_SHIPPING_METHODS_MESSAGE;

        } else {
            $no_shipping_methods_message = 'We could not find any shipping methods that would meet the requirements of this recipient.';
        }

        // If a submit button label was not entered for the page, then set default label.
        if ($submit_button_label == '') {
            $submit_button_label = 'Continue';
        }

        $system =
            get_token_field() . '

            <input type="hidden" name="page_id" value="' . h($page_id) . '">
            <input type="hidden" name="next_page_id" value="' . $next_page_id . '">
            <input type="hidden" name="ship_to_id" value="' . h($_GET['ship_to_id']) . '">';

        $currency = false;
        $currency_attributes = '';
        $currencies = array();
        $currency_system = '';

        if (ECOMMERCE_MULTICURRENCY) {
            // Get all currencies where the exchange rate is not 0, with base currency first.
            $currencies = db_items(
                "SELECT
                    id,
                    name,
                    base,
                    code,
                    symbol,
                    exchange_rate
                FROM currencies
                WHERE exchange_rate != '0'
                ORDER BY
                    base DESC,
                    name ASC");

            // If there is at least one extra currency, in addition to the base currency,
            // then continue to prepare currency info.
            if (count($currencies) > 1) {

                $currency = true;

                $currency_attributes =
                    'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/update_currency.php" ' .
                    'method="post"';

                $currency_options = array();

                foreach ($currencies as $currency) {
                    $label = h($currency['name'] . ' (' . $currency['code'] . ')');

                    $currency_options[$label] = $currency['id'];
                }

                $form->set('currency_id', 'options', $currency_options);
                $form->set('currency_id', VISITOR_CURRENCY_ID);
                $form->set('send_to', REQUEST_URL);

                $currency_system =
                    get_token_field() . '
                    <input type="hidden" name="send_to">
                    <script>software.init_currency()</script>';

            } else {
                $currencies = array();
            }
        }

        $content = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'ship_to_name' => $ship_to_name,
            'update_url' => $update_url,
            'salutation' => $salutation,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'company' => $company,
            'address_1' => $address_1,
            'address_2' => $address_2,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zip_code,
            'country' => $country,
            'address_verified' => $address_verified,
            'arrival_date' => $arrival_date,
            'product_description_type' => $product_description_type,
            'product_restriction_message' => $product_restriction_message,
            'items' => $items,
            'no_shipping_methods_message' => $no_shipping_methods_message,
            'shipping_methods' => $shipping_methods,
            'number_of_shipping_methods' => count($shipping_methods),
            'submit_button_label' => $submit_button_label,
            'system' => $system,
            'currency_symbol' => VISITOR_CURRENCY_SYMBOL,
            'currency_code' => VISITOR_CURRENCY_CODE_FOR_OUTPUT,
            'currency' => $currency,
            'currency_attributes' => $currency_attributes,
            'currencies' => $currencies,
            'currency_system' => $currency_system));

        $content = $form->prepare($content);

        $form->remove();

        return
            '<div class="software_shipping_method">
                '  . $content . '
            </div>';

    }

}