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

function get_order_receipt_in_plain_text($order_id)
{
    // get order info that we will need to use below
    $query =
        "SELECT
            order_date,
            gift_card_discount,
            surcharge
        FROM orders
        WHERE id = '$order_id'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $order_date = $row['order_date'];
    $gift_card_discount = $row['gift_card_discount'] / 100;
    $surcharge = $row['surcharge'] / 100;

    // set shipping to false until we find out that this is a shipping order
    $shipping = false;
    
    // get all ship tos
    $query = "SELECT DISTINCT ship_to_id FROM order_items WHERE order_id = '$order_id' ORDER BY ship_to_id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $ship_tos = array();

    // foreach ship to, add ship to to array
    while ($row = mysqli_fetch_assoc($result)) {
        $ship_tos[] = $row['ship_to_id'];
    }
    
    // if there is at least one product in cart
    if ($ship_tos) {
        // set recurring to false before we check all products for recurring
        $recurring_products_exist = false;
        
        // intialize payment periods array that we will use to store data about payment periods for recurring products
        $payment_periods = array(
            'Monthly' => '',
            'Weekly' => '',
            'Every Two Weeks' => '',
            'Twice every Month' => '',
            'Every Four Weeks' => '',
            'Quarterly' => '',
            'Twice every Year' => '',
            'Yearly' => '');
        
        foreach ($payment_periods as $key => $value) {
            $payment_periods[$key] = array(
                'exists' => false,
                'subtotal' => 0,
                'tax' => 0);
        }
        
        $applied_offers = array();
        
        // loop through all ship tos
        foreach ($ship_tos as $ship_to_id) {
            // get ship to information
            $query =
                "SELECT
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
                    ship_tos.address_verified,
                    ship_tos.arrival_date,
                    ship_tos.arrival_date_id,
                    ship_tos.shipping_method_id,
                    shipping_methods.name as shipping_method_name,
                    shipping_methods.description as shipping_method_description,
                    ship_tos.shipping_cost,
                    ship_tos.original_shipping_cost,
                    ship_tos.offer_id
                FROM ship_tos
                LEFT JOIN shipping_methods ON shipping_methods.id = ship_tos.shipping_method_id
                WHERE ship_tos.id = $ship_to_id";
            
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $ship_to_name = $row['ship_to_name'];
            $salutation = $row['salutation'];
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $company = $row['company'];
            $address_1 = $row['address_1'];
            $address_2 = $row['address_2'];
            $city = $row['city'];
            $state = $row['state'];
            $zip_code = $row['zip_code'];
            $country = $row['country'];
            $address_verified = $row['address_verified'];
            $arrival_date = $row['arrival_date'];
            $arrival_date_id = $row['arrival_date_id'];
            $shipping_method_id = $row['shipping_method_id'];
            $shipping_method_name = $row['shipping_method_name'];
            $shipping_method_description = $row['shipping_method_description'];
            $shipping_cost = $row['shipping_cost'] / 100;
            $original_shipping_cost = $row['original_shipping_cost'] / 100;
            $ship_to_offer_id = $row['offer_id'];
            
            // get country name
            $query = "SELECT name FROM countries WHERE code = '" . escape($country) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $country = $row['name'];
            
            // if this shipping address is verified, then convert salutation and country to all uppercase
            if ($address_verified == 1) {
                $salutation = mb_strtoupper($salutation);
                $country = mb_strtoupper($country);
            }

            // get all order items in order for this ship to
            $query =
                "SELECT
                    order_items.id,
                    order_items.product_id,
                    order_items.product_name,
                    order_items.quantity,
                    order_items.price,
                    order_items.tax,
                    order_items.offer_id,
                    order_items.discounted_by_offer,
                    order_items.recurring_payment_period,
                    order_items.recurring_number_of_payments,
                    order_items.recurring_start_date,
                    order_items.calendar_event_id,
                    order_items.recurrence_number,
                    products.short_description,
                    products.price as product_price,
                    products.recurring,
                    products.recurring_schedule_editable_by_customer,
                    products.selection_type,
                    products.gift_card,
                    products.form,
                    products.form_name,
                    products.form_label_column_width,
                    products.form_quantity_type
                FROM order_items
                LEFT JOIN products ON order_items.product_id = products.id
                WHERE
                    (order_items.order_id = '$order_id')
                    AND (order_items.ship_to_id = '$ship_to_id')
                ORDER BY order_items.id";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            $order_items = array();

            // loop through order items in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $order_items[] = $row;
            }

            $output_products = '';
            $output_recurring_products = '';

            // loop through order items
            foreach ($order_items as $order_item) {
                $order_item_id = $order_item['id'];
                $product_id = $order_item['product_id'];
                $name = $order_item['product_name'];
                $quantity = $order_item['quantity'];
                $product_price = $order_item['price'] / 100;
                $product_tax = $order_item['tax'] / 100;
                $offer_id = $order_item['offer_id'];
                $discounted_by_offer = $order_item['discounted_by_offer'];
                $recurring_payment_period = $order_item['recurring_payment_period'];
                $recurring_number_of_payments = $order_item['recurring_number_of_payments'];
                $recurring_start_date = $order_item['recurring_start_date'];
                $calendar_event_id = $order_item['calendar_event_id'];
                $recurrence_number = $order_item['recurrence_number'];
                $short_description = $order_item['short_description'];
                $original_product_price = $order_item['product_price'] / 100;
                $recurring = $order_item['recurring'];
                $recurring_schedule_editable_by_customer = $order_item['recurring_schedule_editable_by_customer'];
                $start = $order_item['start'];
                $payment_period = $order_item['payment_period'];
                $selection_type = $order_item['selection_type'];
                $gift_card = $order_item['gift_card'];
                $form = $order_item['form'];
                $form_name = $order_item['form_name'];
                $form_label_column_width = $order_item['form_label_column_width'];
                $form_quantity_type = $order_item['form_quantity_type'];
                
                // if calendars is enabled and this order item is for a calendar event reservation, then add calendar event name and date and time range to full description
                if ((CALENDARS == TRUE) && ($calendar_event_id != 0)) {
                    $calendar_event = get_calendar_event($calendar_event_id, $recurrence_number);
                    
                    $short_description .= ' - ' . $calendar_event['name'] . ' - ' . $calendar_event['date_and_time_range'];
                }

                $total_price = $product_price * $quantity;
                $total_tax = $product_tax * $quantity;
                
                $output_amount = '';

                // if order item is a donation, then just display the amount
                if ($selection_type == 'donation') {
                    $output_amount = prepare_price_for_output($total_price * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE);
                
                // else order item is not a donation, so output quantity, price, and amount
                } else {
                    // assume that the order item is not discounted, until we find out otherwise
                    $discounted = FALSE;

                    // if the order item is discounted, then prepare to show that
                    if ($discounted_by_offer == 1) {
                        $discounted = TRUE;
                    }
                    
                    $output_amount = $quantity . ' x '. prepare_price_for_output($original_product_price * 100, $discounted, $product_price * 100, 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . ' = ' . prepare_price_for_output($total_price * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE);
                }
                
                // assume that we don't need to output a recurring schedule, until we find out otherwise
                $output_recurring_schedule = '';
                
                // if the product is a recurring product and the recurring schedule is editable by the customer, then output recurring schedule
                if (($recurring == 1) && ($recurring_schedule_editable_by_customer == 1)) {
                    $output_recurring_number_of_payments = '';

                    // if the number of payments is not 0, then show the number of payments
                    if ($recurring_number_of_payments != 0) {
                        $output_recurring_number_of_payments = ', Payments: ' . number_format($recurring_number_of_payments);
                    }
                    
                    // determine if start should be outputted
                    $output_start_date = '';
                    
                    // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then output start date
                    if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                        $output_start_date = ', Start: ' . get_absolute_time(array('timestamp' => strtotime($recurring_start_date), 'type' => 'date', 'size' => 'long', 'format' => 'plain_text'));
                    }
                    
                    $output_recurring_schedule =
                        'Payment Schedule: ' . $recurring_payment_period . $output_recurring_number_of_payments . $output_start_date . "\n" .
                        "\n";
                }

                $output_gift_cards = '';
                
                // If this order item is a gift card, then output gift card data.
                if ($gift_card == 1) {
                    // If the quantity is 100 or less, then set the number of gift cards to the quantity.
                    if ($quantity <= 100) {
                        $number_of_gift_cards = $quantity;
                        
                    // Otherwise the quantity is greater than 100, so set the number of gift cards to 100.
                    // We do this in order to prevent a ton of forms from appearing and causing a slowdown.
                    } else {
                        $number_of_gift_cards = 100;
                    }
                    
                    // Loop through all quantities in order to output data for each gift card.
                    for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
                        $output_quantity_number = '';
                        
                        // If number of gift cards is greater than 1, then output quantity number.
                        if ($number_of_gift_cards > 1) {
                            $output_quantity_number .= ' (' . $quantity_number . ' of ' . $number_of_gift_cards . ')';
                        }

                        // Get gift card data from database.
                        $order_item_gift_card = db_item(
                            "SELECT
                                id,
                                from_name,
                                recipient_email_address,
                                message,
                                delivery_date
                            FROM order_item_gift_cards
                            WHERE
                                (order_item_id = '" . $order_item_id . "')
                                AND (quantity_number = '" . $quantity_number . "')");

                        $output_delivery_date = '';

                        if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                            $output_delivery_date = 'Immediate';

                        } else {
                            $output_delivery_date = get_absolute_time(array('timestamp' => strtotime($order_item_gift_card['delivery_date']), 'type' => 'date', 'size' => 'long', 'format' => 'plain_text'));
                        }
                        
                        $output_gift_cards .=
                            'Gift Card' . $output_quantity_number . ':' . "\n" .
                            'Amount: ' . prepare_price_for_output($product_price * 100, false, $discounted_price = '', 'plain_text', $show_code = true, $show_html_entity_symbol = false) . "\n" .
                            'Recipient Email: ' . $order_item_gift_card['recipient_email_address'] . "\n" .
                            'Your Name: ' . $order_item_gift_card['from_name'] . "\n" .
                            'Message:' . "\n" .
                            $order_item_gift_card['message'] . "\n" .
                            'Delivery Date: ' . $output_delivery_date . "\n" .
                            "\n";
                    }
                }                
                
                // assume that there is not a form to output until we find out otherwse
                $output_forms = '';
                
                // if there is a form for this product, then prepare to output form
                if ($form == 1) {
                    // if there should be one form per quantity, then set the number of forms to the quantity of this order item
                    if ($form_quantity_type == 'One Form per Quantity') {
                        // if the quantity is 100 or less, then set the number of forms to the quantity
                        if ($quantity <= 100) {
                            $number_of_forms = $quantity;
                            
                        // else the quantity is greater than 100, so set the number of forms to 100
                        } else {
                            $number_of_forms = 100;
                        }
                        
                    // else there should be one form per product, so set the number of forms to 1
                    } elseif ($form_quantity_type == 'One Form per Product') {
                        $number_of_forms = 1;
                    }
                    
                    // create loop in order to output all forms
                    for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                        $output_form_header = '';
                        
                        // if there is a form name, then add name to header
                        if ($form_name != '') {
                            $output_form_header .= $form_name;
                        }
                        
                        // if number of forms is greater than 1, then add quantity number to header
                        if ($number_of_forms > 1) {
                            $output_form_header .= ' (' . $quantity_number . ' of ' . $number_of_forms . ')';
                        }
                        
                        // if there is a header, then add colon and new line
                        if ($output_form_header != '') {
                            $output_form_header .= ':' . "\n";
                        }
                        
                        // we don't have time to create a version of get_submitted_product_form_content_with_form_fields
                        // that outputs plain text, so we will just convert its output to plain text
                        $output_forms .=
                            $output_form_header .
                            rtrim(convert_html_to_text('<table>' . get_submitted_product_form_content_with_form_fields($order_item_id, $quantity_number) . '</table>')) . "\n" .
                            "\n";
                    }
                }
                
                // if product is not a recurring product or if start date is less than or equal to the order date and payment gateway is not ClearCommerce, then it is in the non-recurring order
                if (
                    ($recurring == 0)
                    ||
                    (
                        ($recurring_start_date <= date('Y-m-d', $order_date))
                        && ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce'))
                    )
                ) {
                    $in_nonrecurring = true;
                    
                } else {
                    $in_nonrecurring = false;
                }

                // if product is in non-recurring order
                if ($in_nonrecurring) {
                    $subtotal = $subtotal + $total_price;
                    $grand_tax = $grand_tax + $total_tax;

                    $output_products .=
                        '- ' . $name . ' - ' . $short_description . ', ' . $output_amount . "\n" .
                        "\n" .
                        $output_recurring_schedule .
                        $output_gift_cards .
                        $output_forms;
                }

                // if product is a recurring product
                if ($recurring) {
                    $recurring_products_exist = true;
                    
                    $output_recurring_products .=
                        '- ' . $name . ' - ' . $short_description . ', ' . $recurring_payment_period . ', ' . $output_amount . "\n" .
                        "\n";
                    
                    // if product is not in non-recurring, then prepare recurring schedule and form rows
                    if ($in_nonrecurring == false) {
                        $output_recurring_products .=
                            $output_recurring_schedule .
                            $output_gift_cards .
                            $output_forms;
                    }
                    
                    // store information for payment period
                    $payment_periods[$recurring_payment_period]['exists'] = true;
                    $payment_periods[$recurring_payment_period]['subtotal'] += $total_price;
                    $payment_periods[$recurring_payment_period]['tax'] += $total_tax;
                }
                
                // if there is an offer applied to this order item and offer has not already been added to applied offers array,
                // store this offer as an applied offer
                if ($offer_id && (in_array($offer_id, $applied_offers) == false)) {
                    $applied_offers[] = $offer_id;
                }
            }

            // if there is at least one product in non-recurring area for this ship to, then output header and product information
            if ($output_products) {
                // if shipping is on and this ship to is a real ship to, then add header with ship to label
                if ((ECOMMERCE_SHIPPING == true) && ($ship_to_id != 0)) {
                    $output_ship_to_name = '';

                    // If multi-recipient shipping is enabled, then output ship to name.
                    // We can't output ship to name for single-recipient because we don't know if it is being sent to "myself" or someone else.
                    if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                        $output_ship_to_name = ' ' . $ship_to_name;
                    }

                    $address = '';
                    
                    // if there is a salutation and a last name, then add salutation to address
                    if (($salutation != '') && ($last_name != '')) {
                        $address .= $salutation;
                    }
                    
                    // if there is a first name, then add it to address
                    if ($first_name != '') {
                        // if the address is not blank, then add a space
                        if ($address != '') {
                            $address .= ' ';
                        }
                        
                        $address .= $first_name;
                    }
                    
                    // if there is a last name, then add it to address
                    if ($last_name != '') {
                        // if the address is not blank, then add a space
                        if ($address != '') {
                            $address .= ' ';
                        }
                        
                        $address .= $last_name;
                    }
                    
                    // if the address is not blank, then add a line break
                    if ($address != '') {
                        $address .= "\n";
                    }
                    
                    if ($company) {
                        $address .= $company . ', ';
                    }
                    
                    $address .= $address_1;

                    if ($address_2) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $address_2;
                    }

                    if ($city) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $city;
                    }
                    
                    if ($state) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $state;
                    }

                    if ($zip_code) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $zip_code;
                    }

                    if ($zip_code) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $country;
                    }

                    $output_shipping_form = rtrim(convert_html_to_text(get_submitted_form_content_with_form_fields(array('type' => 'custom_shipping_form', 'ship_to_id' => $ship_to_id))));

                    // If the shipping form is not blank then add a new line after it.
                    if ($output_shipping_form != '') {
                        $output_shipping_form .= "\n";
                    }

                    $output_ship_tos .=
                        'Ship to' . $output_ship_to_name . ':' . "\n" .
                        '-------------------------' . "\n" .
                        $address . "\n" .
                        $output_shipping_form .
                        "\n";
                }

                // if shipping is on and this is a real ship to, output shipping row
                if ((ECOMMERCE_SHIPPING == true) && ($ship_to_id != 0)) {
                    // set shipping to true because we now know that this is a shipping order
                    $shipping = true;

                    // update grand shipping total
                    $grand_shipping += $shipping_cost;

                    $shipping_description = $shipping_method_name;

                    // get arrival date information
                    $query = "SELECT name, custom FROM arrival_dates WHERE id = '$arrival_date_id'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // if an arrival date was selected, then prepare requested arrival date content
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        
                        $shipping_description .= '; Requested Arrival Date: ';
                        
                        // if selected arrival date had a custom field, use actual date in description
                        if ($row['custom'] == 1) {
                            $shipping_description .= get_absolute_time(array('timestamp' => strtotime($arrival_date), 'type' => 'date', 'size' => 'long', 'format' => 'plain_text'));

                        // else selected arrival date did not have a custom field, so use arrival date name
                        } else {
                            $shipping_description .= $row['name'];
                        }
                    }

                    $shipping_description .= '; ' . $shipping_method_description;
                    
                    // if there is an offer applied to this ship to, then prepare to output shipping cost in a certain way and add offer to applied offers array if necessary
                    if ($ship_to_offer_id != 0) {
                        $original_price = $original_shipping_cost * 100;
                        $discounted = TRUE;
                        $discounted_price = $shipping_cost * 100;
                        
                        // if the offer has not already been added to the applied offers array, then store this offer as an applied offer
                        if (in_array($ship_to_offer_id, $applied_offers) == false) {
                            $applied_offers[] = $ship_to_offer_id;
                        }
                        
                    // else there is not an offer applies to this ship to, so prepare shipping cost in a different way
                    } else {
                        $original_price = $shipping_cost * 100;
                        $discounted = FALSE;
                        $discounted_price = '';
                    }

                    $output_shipping =
                        'Shipping: ' . $shipping_description . ', ' . prepare_price_for_output($original_price, $discounted, $discounted_price, 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n" .
                        "\n";
                }

                // prepare output for this ship to
                $output_ship_tos .=
                    $output_products .
                    $output_shipping;
            }

            // if there is at least one product in recurring area for this ship to, then output header and product information
            if ($output_recurring_products) {
                // if shipping is on and this ship to is a real ship to, then add header with ship to label
                if ((ECOMMERCE_SHIPPING == true) && ($ship_to_id != 0)) {
                    $output_ship_to_name = '';

                    // If multi-recipient shipping is enabled, then output ship to name.
                    // We can't output ship to name for single-recipient because we don't know if it is being sent to "myself" or someone else.
                    if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                        $output_ship_to_name = ' ' . $ship_to_name;
                    }
                    
                    $address = '';
                    
                    // if there is a salutation and a last name, then add salutation to address
                    if (($salutation != '') && ($last_name != '')) {
                        $address .= $salutation;
                    }
                    
                    // if there is a first name, then add it to address
                    if ($first_name != '') {
                        // if the address is not blank, then add a space
                        if ($address != '') {
                            $address .= ' ';
                        }
                        
                        $address .= $first_name;
                    }
                    
                    // if there is a last name, then add it to address
                    if ($last_name != '') {
                        // if the address is not blank, then add a space
                        if ($address != '') {
                            $address .= ' ';
                        }
                        
                        $address .= $last_name;
                    }
                    
                    // if the address is not blank, then add a line break
                    if ($address != '') {
                        $address .= "\n";
                    }
                    
                    if ($company) {
                        $address .= $company . ', ';
                    }
                    
                    $address .= $address_1;

                    if ($address_2) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $address_2;
                    }

                    if ($city) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $city;
                    }
                    
                    if ($state) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $state;
                    }

                    if ($zip_code) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $zip_code;
                    }

                    if ($zip_code) {
                        if ($address) {
                            $address .= ', ';
                        }
                        
                        $address .= $country;
                    }

                    $output_shipping_form = rtrim(convert_html_to_text(get_submitted_form_content_with_form_fields(array('type' => 'custom_shipping_form', 'ship_to_id' => $ship_to_id))));

                    // If the shipping form is not blank then add a new line after it.
                    if ($output_shipping_form != '') {
                        $output_shipping_form .= "\n";
                    }

                    $output_recurring_ship_tos .=
                        'Ship to' . $output_ship_to_name . ':' . "\n" .
                        '-------------------------' . "\n" .
                        $address . "\n" .
                        $output_shipping_form .
                        "\n";
                }

                // prepare recurring output for this ship to
                $output_recurring_ship_tos .= $output_recurring_products;
            }
        }
    }
    
    // get all unique products that are in order, in order to prepare order receipt messages
    $query =
        "SELECT
           DISTINCT(order_items.product_id),
           products.order_receipt_message
        FROM order_items
        LEFT JOIN products ON order_items.product_id = products.id
        WHERE
            (order_items.order_id = '$order_id')
            AND (products.order_receipt_message != '')
            AND (products.order_receipt_message != '<p />')
        ORDER BY ship_to_id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $output_order_receipt_messages = '';
    
    // loop through the order receipt messages in order to output them
    // we are going to convert them from HTML to plain text
    while ($row = mysqli_fetch_assoc($result)) {
        $output_order_receipt_messages .=
            trim(convert_html_to_text($row['order_receipt_message'])) . "\n" .
            "\n";
    }

    // format subtotal, tax, and total
    $subtotal = $subtotal;

    // start grand total off with just the subtotal (we will add tax and shipping later, if necessary)
    $grand_total = $subtotal;

    $output_discount = '';

    // if there is an order discount from an offer, prepare order discount
    if ($_SESSION['ecommerce']['order_discount']) {
        $order_discount = $_SESSION['ecommerce']['order_discount'] / 100;
        
        $grand_total = $subtotal - $order_discount;
        
        if ($grand_total < 0) {
            $grand_total = 0;
        }
        
        $output_discount =
            'Discount: -' . prepare_price_for_output($order_discount * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n";
    }

    // if tax is on, update grand total and prepare tax row
    if (ECOMMERCE_TAX == true) {
        // if there is an order discount, adjust tax
        if ($order_discount > 0) {
            $grand_tax = $grand_tax - ($grand_tax * ($order_discount / $subtotal));
        }

        // If the tax is negative then set it to zero.  The tax might be negative
        // if there is an offer that discounts the order or if there are
        // negative price products.  We don't want to allow a negative tax though.
        if ($grand_tax < 0) {
            $grand_tax = 0;
        }
        
        $grand_total = $grand_total + $grand_tax;

        $output_grand_tax =
            'Tax: ' . prepare_price_for_output($grand_tax * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n";
    }

    // if there was at least one shipping recipient for this order, output grand shipping total
    if ($shipping == true) {
        // update grand total
        $grand_total = $grand_total + $grand_shipping;

        $output_grand_shipping =
            'Shipping: ' . prepare_price_for_output($grand_shipping * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n";
    }
    
    $output_gift_card_discount = '';
    
    // if there is a gift card discount, then prepare to output it
    if ($gift_card_discount > 0) {
        // get applied gift cards in order to output them
        $query =
            "SELECT
                code,
                new_balance,
                givex
            FROM applied_gift_cards
            WHERE order_id = '$order_id'
            ORDER BY id ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        $applied_gift_cards = array();
        
        // loop through applied gift cards in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $applied_gift_cards[] = $row;
        }
        
        $output_gift_card_label_plural_suffix = '';
        
        // if there is more than 1 applied gift card, then prepare to output gift card label plural suffix
        if (count($applied_gift_cards) > 1) {
            $output_gift_card_label_plural_suffix = 's';
        }
        
        // update the grand total
        $grand_total = $grand_total - $gift_card_discount;
        
        // if the grand total is less than 0, then set the grand total to 0
        // this should not happen, however for some reason with gift card discounts, PHP is setting the grand total to -0.00000001 for example instead of 0
        // which results in $-0.00, which we don't want
        if ($grand_total < 0) {
            $grand_total = 0;
        }

        $output_gift_card_discount =
            'Gift Card' . $output_gift_card_label_plural_suffix . ': -' . prepare_price_for_output($gift_card_discount * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n" .
            "\n";
    }

    $output_surcharge = '';
    
    // If there is a credit card surcharge, then output it.
    if ($surcharge > 0) {        
        $grand_total = $grand_total + $surcharge;

        $output_surcharge =
            'Surcharge: ' . prepare_price_for_output($surcharge * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n" .
            "\n";
    }

    $output_recurring_charges = '';

    // if there is a recurring product
    if ($recurring_products_exist == true) {
        $output_payment_periods = '';
        
        foreach ($payment_periods as $payment_period_name => $payment_period) {
            // if there is a recurring product in the order for this payment period
            if ($payment_period['exists'] == true) {
                $output_payment_periods .= $payment_period_name . ' Subtotal: ' . prepare_price_for_output($payment_period['subtotal'] * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n";
                
                // if tax is on, prepare tax row
                if (ECOMMERCE_TAX == true) {

                    // If the tax is negative then set it to zero.  The tax
                    // might be negative if there are negative price products.
                    // We don't want to allow a negative tax though.
                    if ($payment_period['tax'] < 0) {
                        $payment_period['tax'] = 0;
                    }
                    
                    $payment_period_total = $payment_period['subtotal'] + $payment_period['tax'];

                    $output_payment_periods .= $payment_period_name . ' Tax: ' . prepare_price_for_output($payment_period['tax'] * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n";
                    
                // else tax is off, so just update total
                } else {
                    $payment_period_total = $payment_period['subtotal'];
                }

                $output_payment_periods .=
                    $payment_period_name . ' Total: ' . prepare_price_for_output($payment_period_total * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n" .
                    "\n";
            }
        }
        
        $output_recurring_charges =
            'Recurring Charges:' . "\n" .
            '-------------------------' . "\n" .
            $output_recurring_ship_tos .
            $output_payment_periods;
    }
    
    $query = "SELECT * FROM orders WHERE id = '$order_id'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    // set field values
    $billing_salutation = $row['billing_salutation'];
    $billing_first_name = $row['billing_first_name'];
    $billing_last_name = $row['billing_last_name'];
    $billing_company = $row['billing_company'];
    $billing_address_1 = $row['billing_address_1'];
    $billing_address_2 = $row['billing_address_2'];
    $billing_city = $row['billing_city'];
    $billing_state = $row['billing_state'];
    $billing_zip_code = $row['billing_zip_code'];
    $billing_country = $row['billing_country'];
    $billing_phone_number = $row['billing_phone_number'];
    $billing_fax_number = $row['billing_fax_number'];
    $billing_email_address = $row['billing_email_address'];
    $custom_field_1 = $row['custom_field_1'];
    $custom_field_2 = $row['custom_field_2'];
    $po_number = $row['po_number'];
    $tax_exempt = $row['tax_exempt'];
    $payment_method = $row['payment_method'];
    
    if ($payment_method == 'Credit/Debit Card') {
        $card_type = $row['card_type'];
        $card_number = $row['card_number'];
    }
    
    $order_number = $row['order_number'];
    $order_date = $row['order_date'];
    $discount_offer_id = $row['discount_offer_id'];
    
    // if there is an order discount and this offer has not already been added to the applied offers,
    // add order discount offer to applied offers
    if ($discount_offer_id && (in_array($discount_offer_id, $applied_offers) == FALSE)) {
        $applied_offers[] = $discount_offer_id;
    }

    $output_applied_offers = '';

    // if offer(s) have been applied, prepare list of applied offer(s)
    if ($applied_offers) {
        $output_applied_offers .=
            'Applied Offers:' . "\n" .
            '-------------------------' . "\n";
        
        // loop through each applied offer
        foreach ($applied_offers as $offer_id) {
            // get offer data
            $query = "SELECT description FROM offers WHERE id = '$offer_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $offer_description = $row['description'];
            
            // the offer description can contain HTML, so convert HTML to plain text
            $output_applied_offers .= '- ' . convert_html_to_text($offer_description) . "\n";
        }
        
        $output_applied_offers .= "\n";
    }
    
    // get country name
    $query = "SELECT name FROM countries WHERE code = '" . escape($billing_country) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $billing_country = $row['name'];
    
    // if the credit card number is not already protected, then protect it
    if (mb_substr($card_number, 0, 1) != '*') {
        // if the credit card number is encrypted, then decrypt it and then protect it
        if (mb_strlen($card_number) > 16) {
            // if encryption is enabled, then decrypt the credit card number
            if (
                (defined('ENCRYPTION_KEY') == TRUE)
                && (extension_loaded('mcrypt') == TRUE)
                && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
            ) {
                $card_number = decrypt_credit_card_number($card_number, ENCRYPTION_KEY);
                
                // if the credit card number is not numeric, then there was a decryption error, so clear credit card number
                if (is_numeric($card_number) == FALSE) {
                    $card_number = '';
                    
                // else the decryption was successful, so protect credit card number
                } else {
                    $card_number = protect_credit_card_number($card_number);
                }
                
            // else encryption is disabled, so clear credit card number
            } else {
                $card_number = '';
            }
            
        // else the credit card number is not encrypted, so just protect it
        } else {
            $card_number = protect_credit_card_number($card_number);
        }
    }
    
    // prepare billing information
    $output_billing_information = '';
    
    // get custom field information
    $query =
        "SELECT
            custom_field_1_label,
            custom_field_1_required,
            custom_field_2_label,
            custom_field_2_required
        FROM billing_information_pages
        WHERE page_id = '" . escape($_SESSION['ecommerce']['billing_information_page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $custom_field_1_label = $row['custom_field_1_label'];
    $custom_field_1_required = $row['custom_field_1_required'];
    $custom_field_2_label = $row['custom_field_2_label'];
    $custom_field_2_required = $row['custom_field_2_required'];

    // if there is a custom field 1 label and custom field 1 is not blank, then prepare to output data
    if (($custom_field_1_label != '') && ($custom_field_1 != '')) {
        $output_billing_information .= $custom_field_1_label . ': ' . $custom_field_1 . "\n";
    }

    // if there is a custom field 2 label and custom field 2 is not blank, then prepare to output data
    if (($custom_field_2_label != '') && ($custom_field_2 != '')) {
        $output_billing_information .= $custom_field_2_label . ': ' . $custom_field_2 . "\n";
    }
    
    if ($billing_salutation) {
        $output_billing_information .= $billing_salutation . ' ' . $billing_first_name . ' ' . $billing_last_name . "\n";
    } else {
        $output_billing_information .= $billing_first_name . ' ' . $billing_last_name . "\n";
    }

    if ($billing_company) {
        $output_billing_information .= $billing_company . "\n";
    }

    $output_billing_information .= $billing_address_1 . "\n";

    if ($billing_address_2) {
        $output_billing_information .= $billing_address_2 . "\n";
    }

    $output_billing_information .=
        $billing_city . ', ' . $billing_state . ' ' . $billing_zip_code . "\n" .
        $billing_country . "\n" .
        'Phone: ' . $billing_phone_number . "\n";

    if ($billing_fax_number) {
        $output_billing_information .= 'Fax: ' . $billing_fax_number . "\n";
    }

    $output_billing_information .= $billing_email_address . "\n";
    
    if ($po_number) {
        $output_billing_information .= 'PO Number: ' . $po_number . "\n";
    }
    
    if (ECOMMERCE_TAX_EXEMPT == true) {
        // if tax-exempt was checked
        if ($tax_exempt) {
            $output_billing_information .= 'Tax-Exempt' . "\n";
        }
    }
    
    $output_unconverted_total = '';
    $output_multicurrency_disclaimer = '';
    
    // If the visitor's currency is different from the base currency,
    // then show actual base currency amount and disclaimer because the base currency will be charged.
    if (VISITOR_CURRENCY_CODE != BASE_CURRENCY_CODE) {
        $output_unconverted_total = '* (' . prepare_amount($grand_total) . ' ' . h(BASE_CURRENCY_CODE) . ')';

        $base_currency_name = db_value("SELECT name FROM currencies WHERE id = '" . BASE_CURRENCY_ID . "'");

        // If a base currency name was not found (e.g. no currencies),
        // then set to US dollar.
        if ($base_currency_name == '') {
            $base_currency_name = 'US Dollar';
        }

        $output_multicurrency_disclaimer =
            '*This amount is based on our current currency exchange rate to ' . h($base_currency_name) . ' and may differ from the exact charges (displayed above in ' . h($base_currency_name) . ').' . "\n" .
            "\n";
    }

    $output_billing_form = rtrim(convert_html_to_text(get_submitted_form_content_with_form_fields(array('type' => 'custom_billing_form', 'order_id' => $order_id))));

    // If the billing form is not blank then add a new line after it.
    if ($output_billing_form != '') {
        $output_billing_form .= "\n";
    }

    $output_applied_gift_cards = '';
    
    // if there is a gift card discount and there is at least one applied gift card, then prepare to output applied gift cards
    // this double check is not redundant, because there can be a situation where there is a discount with no gift cards if there was an error when the gift card transaction was submitted
    if (($gift_card_discount > 0)  && (count($applied_gift_cards) > 0)) {
        $output_applied_gift_cards .=
            'Applied Gift Cards:' . "\n" .
            '-------------------------' . "\n";
        
        // loop through applied gift cards in order to prepare to output them
        foreach ($applied_gift_cards as $applied_gift_card) {
            if ($applied_gift_card['givex'] == 0) {
                $protected_gift_card_code = protect_gift_card_code($applied_gift_card['code']);
            } else {
                $protected_gift_card_code = protect_givex_gift_card_code($applied_gift_card['code']);
            }
            
            $output_applied_gift_cards .= '- ' . $protected_gift_card_code . ' (Remaining Balance: ' . prepare_price_for_output($applied_gift_card['new_balance'], FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . ')' . "\n";
        }

        $output_applied_gift_cards .= "\n";
    }
    
    $output_payment_information = '';
    
    // if there was a payment method, then prepare to output payment information
    if ($payment_method != '') {
        // if the payment method is offline and there is an offline payment label in the session, then update payment method name,
        // so that the customer sees the same payment method name on the order receipt as he/she did on the order preview or express order screen
        if (($payment_method == 'Offline Payment') && (isset($_SESSION['ecommerce']['offline_payment_label']) == TRUE)) {
            $output_payment_method = $_SESSION['ecommerce']['offline_payment_label'];
            
        // else use the default payment method name
        } else {
            $output_payment_method = $payment_method;
        }
        
        $output_credit_debit_card_information = '';
        
        // if Credit/Debit Card payment method was selected, then prepare to output value for that
        if ($payment_method == 'Credit/Debit Card') {
            $output_credit_debit_card_information =
                "\n" .
                'Card Type: ' . $card_type . "\n" .
                'Card Number: ' . $card_number;
        }

        $output_payment_information .=
            'Payment Information:' . "\n" .
            '-------------------------' . "\n" .
            'Payment Method: ' . $output_payment_method .
            $output_credit_debit_card_information;
    }

    $output_auto_registration = '';

    // If a user account was created via the auto-registration feature,
    // then show user account info.
    if ($_SESSION['software']['auto_registration']['email_address'] != '') {
        $output_auto_registration =
            "\n" .
            "\n" .
            'New Account:' . "\n" .
            '-------------------------' . "\n" .
            'We have created a new account for you so you can view your orders on our site. You can find your login info below.' . "\n" .
            "\n" .
            'Email: ' . $_SESSION['software']['auto_registration']['email_address'] . "\n" .
            'Password: ' . $_SESSION['software']['auto_registration']['password'];
    }
    
    return
        $output_order_receipt_messages .
        'Order Number: ' . $order_number . "\n" .
        'Order Date: ' . get_absolute_time(array('timestamp' => $order_date, 'size' => 'long', 'format' => 'plain_text')) . "\n" .
        "\n" .
        $output_ship_tos .
        'Order Totals:' . "\n" .
        '-------------------------' . "\n" .
        'Subtotal: ' . prepare_price_for_output($subtotal * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . "\n" .
        $output_discount .
        $output_grand_tax .
        $output_grand_shipping .
        $output_gift_card_discount .
        $output_surcharge .
        'Total: ' . prepare_price_for_output($grand_total * 100, FALSE, $discounted_price = '', 'plain_text', $show_code = TRUE, $show_html_entity_symbol = FALSE) . $output_unconverted_total . "\n" .
        "\n" .
        $output_multicurrency_disclaimer .
        $output_recurring_charges .
        $output_applied_offers .
        'Billing Information:' . "\n" .
        '-------------------------' . "\n" .
        $output_billing_information .
        $output_billing_form .
        "\n" .
        $output_applied_gift_cards .
        $output_payment_information .
        $output_auto_registration;
}
?>