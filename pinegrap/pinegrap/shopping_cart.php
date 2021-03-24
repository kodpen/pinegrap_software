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
require_cookies();
initialize_order();

$liveform = new liveform('shopping_cart');
$liveform->add_fields_to_session();
$liveform->clear_notices();

// Get page type properties that we will need in various places below.
$page = db_item(
    "SELECT
        shopping_cart_label,
        special_offer_code_label,
        next_page_id_with_shipping,
        next_page_id_without_shipping
    FROM shopping_cart_pages
    WHERE page_id = '" . e($_POST['page_id']) . "'");

add_pending_offers($liveform);

// if quick add form was submitted
if ($_POST['quick_add']) {
    // if a quick add product was selected, continue
    if ($_POST['quick_add_product_id']) {
        // get data for product
        $query =
            "SELECT
                name,
                enabled,
                short_description,
                price,
                shippable,
                selection_type,
                default_quantity,
                inventory,
                inventory_quantity,
                backorder
            FROM products
            WHERE id = '" . escape($_POST['quick_add_product_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if quick add product was found, continue
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            $name = $row['name'];
            $enabled = $row['enabled'];
            $short_description = $row['short_description'];
            $price = $row['price'];
            $shippable = $row['shippable'];
            $selection_type = $row['selection_type'];
            $default_quantity = $row['default_quantity'];
            $inventory = $row['inventory'];
            $inventory_quantity = $row['inventory_quantity'];
            $backorder = $row['backorder'];
            
            // If product is enabled and there are not any inventory issues,
            // then the product is available so add it.
            if (
                ($enabled == 1)
                &&
                (
                    ($inventory == 0)
                    || ($inventory_quantity > 0)
                    || ($backorder == 1)
                )
            ) {
                // if a recipient was not required to be selected or entered or a recipient was selected or entered, continue
                if ((ECOMMERCE_SHIPPING == false) || (ECOMMERCE_RECIPIENT_MODE == 'single recipient') || ($shippable == 0) || ($_POST['quick_add_ship_to'] != '') || ($_POST['quick_add_add_name'] != '')) {
                    // We use the following in order to remember if an order item has been added,
                    // so we know later if we need to refresh offers for this order.
                    $order_item_added = false;

                    switch ($selection_type) {
                        case 'checkbox':
                        case 'autoselect':
                            add_order_item($_POST['quick_add_product_id'], $default_quantity, 0, $_POST['quick_add_ship_to'], $_POST['quick_add_add_name']);
                            $order_item_added = true;
                            break;
                            
                        case 'quantity':
                            // Remove commas from quantity.
                            $_POST['quick_add_quantity'] = str_replace(',', '', $_POST['quick_add_quantity']);

                            // if quantity entered is valid and greater than zero, then add order item
                            if ((preg_match("/^\d+$/", $_POST['quick_add_quantity']) == true) && ($_POST['quick_add_quantity'] > 0)) {
                                add_order_item($_POST['quick_add_product_id'], $_POST['quick_add_quantity'], 0, $_POST['quick_add_ship_to'], $_POST['quick_add_add_name']);
                                $order_item_added = true;
                            }
                            break;
                            
                        case 'donation':
                            // remove commas from donation amount if they exist
                            $donation_amount = str_replace(',', '', $_POST['quick_add_amount']);
                            
                            // convert donation amount into USD
                            // Suppressing error for PHP 7.1+ support
                            $donation_amount = @($donation_amount / VISITOR_CURRENCY_EXCHANGE_RATE);
                        
                            // convert dollars into cents for database storage
                            $donation_amount = round($donation_amount * 100);
                            
                            // if donation entered is greater than zero, then add donation to cart
                            if ($donation_amount > 0) {
                                add_order_item($_POST['quick_add_product_id'], 1, $donation_amount, $_POST['quick_add_ship_to'], $_POST['quick_add_add_name']);
                                $order_item_added = true;
                            }
                            break;
                    }

                    // If a quick add order item was added, then offers for this order need to be refreshed,
                    // so that the subtotal in a cart region is accurate.
                    if ($order_item_added == true) {
                        update_order_item_prices();
                        apply_offers_to_cart();
                    }
                    
                    // clear quick add fields, because we are done with fields
                    $liveform->assign_field_value('quick_add_product_id', '');
                    $liveform->assign_field_value('quick_add_quantity', '');
                    $liveform->assign_field_value('quick_add_amount', '');
                    $liveform->assign_field_value('quick_add_ship_to', '');
                    $liveform->assign_field_value('quick_add_add_name', '');
                    
                // else ship to should have been selected or entered, so prepare error
                } else {
                    $liveform->mark_error('quick_add_ship_to', 'The item that you attempted to add requires a recipient.');
                    $liveform->mark_error('quick_add_add_name', '');
                }
                
            // else the product is not available, so add error
            } else {
                // prepare product description for error
                $product_description = '';
                
                // if there is a name, then add it to the description
                if ($name != '') {
                    $product_description .= $name;
                }
                
                // if there is a short description, then add it to the description
                if ($short_description != '') {
                    // if the description is not blank, then add separator
                    if ($product_description != '') {
                        $product_description .= ' - ';
                    }
                    
                    $product_description .= $short_description;
                }
                
                $liveform->mark_error('quick_add_product_id', 'Sorry, ' . h($product_description) . ' is not currently available.');
            }
            
        // else product was not found, so prepare error
        } else {
            $liveform->mark_error('quick_add_product_id', 'The item that you selected could not be found. Please select a different item to add.');
        }
        
    // else no quick add product was selected, so prepare error
    } else {
        $liveform->mark_error('quick_add_product_id', 'Please select an item to add.');
    }
    
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($_POST['page_id']));
    exit();
}

// get all order items in cart
$query =
    "SELECT
        order_items.id,
        order_items.product_id,
        order_items.quantity,
        products.name,
        products.required_product,
        products.recurring,
        products.recurring_schedule_editable_by_customer,
        products.start,
        products.number_of_payments,
        products.payment_period,
        products.form,
        products.form_quantity_type,
        products.gift_card,
        products.submit_form,
        products.submit_form_custom_form_page_id,
        products.submit_form_update,
        products.submit_form_update_where_field,
        products.submit_form_update_where_value,
        products.submit_form_quantity_type
    FROM order_items
    LEFT JOIN products ON order_items.product_id = products.id
    WHERE order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$order_items = array();

// foreach product in cart, add product to array
while ($row = mysqli_fetch_assoc($result)) {
    $order_items[] = $row;
}

// loop through all order items in order
foreach ($order_items as $order_item) {
    // assume that the new quantity is equal to the old quantity until we find out otherwise
    $new_quantity = $order_item['quantity'];
    
    // if this order item is a donation
    if (isset($_POST['donations'][$order_item['id']]) == true) {
        // remove commas from donation amount if they exist
        $donation_amount = str_replace(',', '', $_POST['donations'][$order_item['id']]);
        
        // convert donation amount into USD
        // Suppressing error for PHP 7.1+ support
        $donation_amount = @($donation_amount / VISITOR_CURRENCY_EXCHANGE_RATE);
        
        // convert dollars into cents for database storage
        $donation_amount = round($donation_amount * 100);
        
        // if donation is less than or equal to 0, then remove order item from order
        if ($donation_amount <= 0) {
            remove_order_item($order_item['id']);
            $new_quantity = 0;
            
        // else donation is greater than zero, so update order item price with donation amount
        } else {
            $query = "UPDATE order_items SET price = '" . escape($donation_amount) . "' WHERE id = '" . $order_item['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    
    // else this order item is not a donation
    } else {
        // Remove commas from quantity.
        $_POST['quantity'][$order_item['id']] = str_replace(',', '', $_POST['quantity'][$order_item['id']]);

        // if quantity was set to 0, remove order item from order
        if ($_POST['quantity'][$order_item['id']] == '0') {
            remove_order_item($order_item['id']);
            $new_quantity = 0;

        // else if quantity is a positive integer and new quantity is different from old quantity, update quantity for product
        } elseif ((preg_match("/^\d+$/", $_POST['quantity'][$order_item['id']]) == true) && ($_POST['quantity'][$order_item['id']] != $order_item['quantity'])) {
            $query = "UPDATE order_items SET quantity = '" . escape($_POST['quantity'][$order_item['id']]) . "' WHERE id = '" . $order_item['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $new_quantity = $_POST['quantity'][$order_item['id']];
            
            // check if there is a ship to and if ship to is complete
            $query =
                "SELECT
                    order_items.ship_to_id,
                    ship_tos.complete
                FROM order_items
                LEFT JOIN ship_tos ON order_items.ship_to_id = ship_tos.id
                WHERE order_items.id = '" . $order_item['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $ship_to_id = $row['ship_to_id'];
            $complete = $row['complete'];
                
            // if there is a ship to and ship to is complete, then update shipping cost for ship to
            if ($ship_to_id && ($complete == 1)) {
                require_once(dirname(__FILE__) . '/shipping.php');
                update_shipping_cost_for_ship_to($ship_to_id);
            }
        }
    }
    
    // If the order item still exists then validate and save recurring, gift card,
    // and product form data if it exists.
    if ($new_quantity > 0 ) {
        // if order item is a recurring order item, then save recurring data
        if ($order_item['recurring'] == 1) {
            // if the customer can set the recurring schedule for this order item then validate values that customer entered and possibly save values
            if ($order_item['recurring_schedule_editable_by_customer'] == 1) {
                // if customer requested to check out, then require recurring schedule fields
                if (isset($_POST['submit_checkout']) == true) {
                    $liveform->validate_required_field('recurring_payment_period_' . $order_item['id'], 'Frequency is required.');
                    
                    // if credit/debit card is enabled as a payment method and the payment gateway is set to ClearCommerce or First Data Global Gateway
                    // then require number of payments
                    if (
                        (ECOMMERCE_CREDIT_DEBIT_CARD == true)
                        &&
                        (
                            (ECOMMERCE_PAYMENT_GATEWAY == 'ClearCommerce')
                            || (ECOMMERCE_PAYMENT_GATEWAY == 'First Data Global Gateway')
                        )
                    ) {
                        $liveform->validate_required_field('recurring_number_of_payments_' . $order_item['id'], 'Number of Payments is required.');
                    }
                    
                    // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then require start date
                    if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                        $liveform->validate_required_field('recurring_start_date_' . $order_item['id'], 'Start Date is required.');
                    }
                }
                
                // if credit/debit card is selected as a payment method and a payment gateway is selected and there is not already an error for the number of payments field,
                // then perform validation that is specific to payment gateways for number of payments
                if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && (ECOMMERCE_PAYMENT_GATEWAY != '') && ($liveform->get_field_value('recurring_number_of_payments_' . $order_item['id']) != '')) {
                    switch (ECOMMERCE_PAYMENT_GATEWAY) {
                        case 'ClearCommerce':
                            // if the value is not 2-999, then mark error
                            if (($liveform->get_field_value('recurring_number_of_payments_' . $order_item['id']) < 2) || ($liveform->get_field_value('recurring_number_of_payments_' . $order_item['id']) > 999)) {
                                $liveform->mark_error('recurring_number_of_payments_' . $order_item['id'], 'Number of Payments requires a value from 2-999.');
                            }
                            break;
                            
                        case 'First Data Global Gateway':
                            // if the value is not 1-99, then mark error
                            if (($liveform->get_field_value('recurring_number_of_payments_' . $order_item['id']) < 1) || ($liveform->get_field_value('recurring_number_of_payments_' . $order_item['id']) > 99)) {
                                $liveform->mark_error('recurring_number_of_payments_' . $order_item['id'], 'Number of Payments requires a value from 1-99.');
                            }
                            break;
                    }
                }
                
                // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce and there is a value for the start date, then validate start date
                if (
                    (
                        (ECOMMERCE_CREDIT_DEBIT_CARD == false)
                        || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')
                    )
                    && ($liveform->get_field_value('recurring_start_date_' . $order_item['id']) != '')
                ) {
                    // if the date is not valid, then mark error
                    if (validate_date($liveform->get_field_value('recurring_start_date_' . $order_item['id'])) == false) {
                        $liveform->mark_error('recurring_start_date_' . $order_item['id'], 'Start Date must contain a valid date.');
                        
                    // else the date is valid, so check if date is in the past
                    } else {
                        $start_date_for_comparison = prepare_form_data_for_input($liveform->get_field_value('recurring_start_date_' . $order_item['id']), 'date');
                        
                        // if the date is in the past, then mark error
                        if ($start_date_for_comparison < date('Y-m-d')) {
                            $liveform->mark_error('recurring_start_date_' . $order_item['id'], 'Start Date may not contain a date in the past.');
                        }
                    }
                }
                
                // set values that will be updated
                $recurring_payment_period = $liveform->get_field_value('recurring_payment_period_' . $order_item['id']);
                $recurring_number_of_payments = $liveform->get_field_value('recurring_number_of_payments_' . $order_item['id']);
                
                // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then set start date to the value that the customer entered
                if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                    $recurring_start_date = prepare_form_data_for_input($liveform->get_field_value('recurring_start_date_' . $order_item['id']), 'date');
                    
                // otherwise, set start date to today's date, because there was not a start date field
                } else {
                    $recurring_start_date = date('Y-m-d');
                }
                
            // else the customer cannot set the recurring schedule for this order item, so use values from product
            } else {
                // if the payment period is blank, then default to Monthly
                if ($order_item['payment_period'] == '') {
                    $recurring_payment_period = 'Monthly';
                    
                // else the payment period is not blank, so set to the value in the product
                } else {
                    $recurring_payment_period = $order_item['payment_period'];
                }
                
                $recurring_number_of_payments = $order_item['number_of_payments'];
                $recurring_start_date = date('Y-m-d', time() + (86400 * $order_item['start']));
            }
            
            // update recurring fields for order item
            $query =
                "UPDATE order_items
                SET
                    recurring_payment_period = '" . escape($recurring_payment_period) . "',
                    recurring_number_of_payments = '" . escape($recurring_number_of_payments) . "',
                    recurring_start_date = '" . escape($recurring_start_date) . "'
                WHERE id = '" . $order_item['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        // If this order item is a gift card, then save gift card data.
        if ($order_item['gift_card'] == 1) {
            // Delete existing gift card data for this order item, so that we can easily insert new data.
            db("DELETE FROM order_item_gift_cards WHERE order_item_id = '" . $order_item['id'] . "'");

            // If the quantity is 100 or less, then set the number of gift cards to the quantity.
            if ($new_quantity <= 100) {
                $number_of_gift_cards = $new_quantity;
                
            // Otherwise the quantity is greater than 100, so set the number of gift cards to 100.
            } else {
                $number_of_gift_cards = 100;
            }
            
            // Loop through each gift card quantity.
            for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
                // If customer requested to check out, then require recipient email address.
                if (isset($_POST['submit_checkout']) == true) {
                    $liveform->validate_required_field('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address', 'Recipient Email is required.');
                }

                // If there is not already an error for the recipient email address field,
                // and a value has been entered, and the value is not a valid email address,
                // then add error.
                if (
                    ($liveform->check_field_error('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address') == false)
                    && ($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address') != '')
                    && (validate_email_address($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address')) == false)
                ) {
                    $liveform->mark_error('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address', 'Please enter a valid email address for Recipient Email.');
                }

                // If a delivery date has been entered, then validate it.
                if ($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date') != '') {
                    // If the date is not valid, then add error.
                    if (validate_date($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date')) == false) {
                        $liveform->mark_error('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', 'Delivery Date must contain a valid date.');
                        
                    // Otherwise if the date is in the past, then add error.
                    } else if (prepare_form_data_for_input($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date'), 'date') < date('Y-m-d')) {
                        $liveform->mark_error('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', 'Delivery Date may not contain a date in the past.');
                    }
                }

                $delivery_date = prepare_form_data_for_input($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date'), 'date');

                // If the delivery date is less than or equal to today, then store blank (immediate)
                // value in the database.
                if ($delivery_date <= date('Y-m-d')) {
                    $delivery_date = '';
                }

                // Save gift card data in database.
                db(
                    "INSERT INTO order_item_gift_cards (
                        order_id,
                        order_item_id,
                        quantity_number,
                        from_name,
                        recipient_email_address,
                        message,
                        delivery_date)
                    VALUES (
                        '" . $_SESSION['ecommerce']['order_id'] . "',
                        '" . $order_item['id'] . "',
                        '" . $quantity_number . "',
                        '" . escape($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_from_name')) . "',
                        '" . escape($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address')) . "',
                        '" . escape($liveform->get_field_value('order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_message')) . "',
                        '" . escape($delivery_date) . "')");
            }
        }
        
        // if the order item has a form, then save form data
        if ($order_item['form'] == 1) {
            // delete existing form data for this order item, so that we can easily insert new data
            $query = "DELETE FROM form_data WHERE (order_item_id = '" . $order_item['id'] . "') AND (order_item_id != '0')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // get fields for this product form
            $query =
                "SELECT
                    id,
                    name,
                    label,
                    type,
                    required,
                    wysiwyg
                FROM form_fields
                WHERE (product_id = '" . $order_item['product_id'] . "') AND (type != 'information')
                ORDER BY sort_order";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $form_fields = array();
            
            // loop through all fields in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $form_fields[] = $row;
            }
            
            // if there should be one form per quantity, then set the number of forms to the quantity of this order item
            if ($order_item['form_quantity_type'] == 'One Form per Quantity') {
                // if the quantity is 100 or less, then set the number of forms to the quantity
                if ($new_quantity <= 100) {
                    $number_of_forms = $new_quantity;
                    
                // else the quantity is greater than 100, so set the number of forms to 100
                } else {
                    $number_of_forms = 100;
                }
                
            // else there should be one form per product, so set the number of forms to 1
            } elseif ($order_item['form_quantity_type'] == 'One Form per Product') {
                $number_of_forms = 1;
            }
            
            // create loop in order to loop through all forms for order item
            for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                // loop through all form fields, so that we can save form data
                foreach ($form_fields as $form_field) {
                    $html_field_name = 'order_item_' . $order_item['id'] . '_quantity_number_' . $quantity_number . '_form_field_' . $form_field['id'];
                    
                    // if customer requested to check out and if field is required, then validate field
                    if ((isset($_POST['submit_checkout']) == true) && ($form_field['required'] == 1)) {
                        $error_message = '';
                        
                        // if there is a field label, then prepare error message
                        if ($form_field['label']) {
                            $error_message = $form_field['label'] . ' is required.';
                        }
                        
                        $liveform->validate_required_field($html_field_name, $error_message);
                    }
                    
                    // validate data differently depending on field type
                    switch ($form_field['type']) {
                        case 'date':
                            // if value is not blank and date is not valid, then mark error
                            if (($liveform->get_field_value($html_field_name) != '') && (validate_date($liveform->get_field_value($html_field_name)) == false)) {
                                $liveform->mark_error($html_field_name, 'Please enter a valid date for ' . $form_field['label']);
                            }
                            
                            break;
                            
                        case 'date and time':
                            // if value is not blank and date and time is not valid, then mark error
                            if (($liveform->get_field_value($html_field_name) != '') && (validate_date_and_time($liveform->get_field_value($html_field_name)) == false)) {
                                $liveform->mark_error($html_field_name, 'Please enter a valid date &amp; time for ' . $form_field['label']);
                            }
                            
                            break;
                            
                        case 'email address':
                            // if value is not blank and e-mail address is not valid, then mark error
                            if (($liveform->get_field_value($html_field_name) != '') && (validate_email_address($liveform->get_field_value($html_field_name)) == false)) {
                                $liveform->mark_error($html_field_name, 'Please enter a valid e-mail address for ' . $form_field['label']);
                            }
                            
                            break;
                            
                        case 'time':
                            // if value is not blank and time is not valid, then mark error
                            if (($liveform->get_field_value($html_field_name) != '') && (validate_time($liveform->get_field_value($html_field_name)) == false)) {
                                $liveform->mark_error($html_field_name, 'Please enter a valid time for ' . $form_field['label']);
                            }
                            
                            break;
                    }

                    // If product updates a submitted form and this is the reference code field,
                    // and this is the first quantity number field or a submitted form is updated
                    // for every quantity, and there is not already an error for this field
                    // and the field is not blank, then check if reference code is valid.
                    if (
                        ($order_item['submit_form'] == 1)
                        && ($order_item['submit_form_custom_form_page_id'])
                        && ($order_item['submit_form_update'] == 1)
                        && ($order_item['submit_form_update_where_field'] == 'reference_code')
                        && ($order_item['submit_form_update_where_value'] == '^^' . $form_field['name'] . '^^')
                        && (($quantity_number == 1) or ($order_item['submit_form_quantity_type'] == 'One Form per Quantity'))
                        && ($liveform->check_field_error($html_field_name) == false)
                        && ($liveform->get_field_value($html_field_name) != '')
                    ) {
                        // Check if we can find a submitted form for the reference code and custom form page
                        // that is set for the product.
                        $reference_code = db_value(
                            "SELECT reference_code
                            FROM forms
                            WHERE
                                (page_id = '" . $order_item['submit_form_custom_form_page_id'] . "')
                                AND (reference_code = '" . escape($liveform->get_field_value($html_field_name)) . "')");

                        // If the reference code was not found, then it is not valid, so output error.
                        if (!$reference_code) {
                            $liveform->mark_error($html_field_name, 'Sorry, the value you entered for ' . $form_field['label'] . ' is not valid.');
                        }
                    }
                    
                    // if the field does not have an error, then store data for field
                    if ($liveform->check_field_error($html_field_name) == false) {
                        // assume that the form data type is standard until we find out otherwise
                        $form_data_type = 'standard';
                        
                        // if the form field's type is date, date and time, or time, then set form data type to the form field type
                        if (
                            ($form_field['type'] == 'date')
                            || ($form_field['type'] == 'date and time')
                            || ($form_field['type'] == 'time')
                        ) {
                            $form_data_type = $form_field['type'];
                            
                        // else if the form field is a wysiwyg text area, then set type to html and prepare content for input
                        } elseif (($form_field['type'] == 'text area') && ($form_field['wysiwyg'] == 1)) {
                            $form_data_type = 'html';
                            
                            $liveform->assign_field_value($html_field_name, prepare_rich_text_editor_content_for_input($liveform->get_field_value($html_field_name)));
                        }
                        
                        // if this field has multiple values (i.e. check box group or pick list)
                        if (is_array($liveform->get_field_value($html_field_name)) == true) {
                            foreach ($liveform->get_field_value($html_field_name) as $value) {
                                // store form data
                                $query =
                                    "INSERT INTO form_data (
                                        order_id,
                                        order_item_id,
                                        quantity_number,
                                        form_field_id,
                                        data,
                                        name,
                                        type)
                                    VALUES (
                                        '" . $_SESSION['ecommerce']['order_id'] . "',
                                        '" . $order_item['id'] . "',
                                        '$quantity_number',
                                        '" . $form_field['id'] . "',
                                        '" . escape(prepare_form_data_for_input($value, $form_field['type'])) . "',
                                        '" . escape($form_field['name']) . "',
                                        '$form_data_type')";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }

                        // else this field does not have multiple values
                        } else {
                            // store form data
                            $query =
                                "INSERT INTO form_data (
                                    order_id,
                                    order_item_id,
                                    quantity_number,
                                    form_field_id,
                                    data,
                                    name,
                                    type)
                                VALUES (
                                    '" . $_SESSION['ecommerce']['order_id'] . "',
                                    '" . $order_item['id'] . "',
                                    '$quantity_number',
                                    '" . $form_field['id'] . "',
                                    '" . escape(prepare_form_data_for_input($liveform->get_field_value($html_field_name), $form_field['type'])) . "',
                                    '" . escape($form_field['name']) . "',
                                    '$form_data_type')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                    }
                }
            }
        }
    }
}

// if a special offer code field existed
if (isset($_POST['special_offer_code'])) {

    $sql_affiliate_code = "";

    $special_offer_code = $liveform->get('special_offer_code');
    
    // if a special offer code was entered
    if ($special_offer_code != '') {

        $offer_code = get_offer_code_for_special_offer_code($special_offer_code);
        
        $shopping_cart_label = $page['shopping_cart_label'];
        $special_offer_code_label = $page['special_offer_code_label'];
        
        // get offers for the code that the customer entered
        // there can be multiple offers that share the same code
        $query =
            "SELECT
                offers.id,
                offers.description,
                offers.status,
                offers.start_date,
                offers.end_date,
                offers.scope
            FROM offers
            WHERE offers.code = '" . e($offer_code) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if an offer was found for special offer code, then continue to check if there is an active offer
        if (mysqli_num_rows($result) > 0) {
            $offers = mysqli_fetch_items($result);
            
            // assume that an active offer does not exist until we find out otherwise
            $active_offer_exists = FALSE;
            
            $current_date = date('Y-m-d');
            
            // loop through the offers, in order to determine if there is an active offer
            foreach ($offers as $offer) {
                // if this offer is active, then an active offer exists, so remember that and then break out of loop
                if (
                    ($offer['status'] == 'enabled')
                    && ($offer['start_date'] <= $current_date)
                    && ($current_date <= $offer['end_date'])
                ) {
                    $active_offer_exists = TRUE;
                    break;
                }
            }
            
            // if an active offer exists, then determine if we should check if a valid offer exists
            if ($active_offer_exists == TRUE) {
                // if a notice has not already been displayed to the user for this offer code, then continue to check if a valid offer exists
                if (
                    (isset($_SESSION['ecommerce']['offer_codes_that_generated_notices']) == FALSE)
                    || (in_array($offer_code, $_SESSION['ecommerce']['offer_codes_that_generated_notices']) == FALSE)
                ) {
                    // assume that a valid offer does not exist until we find out otherwise
                    $valid_offer_exists = FALSE;
                    
                    $ship_tos = array();
                    
                    // prepare variable that we will use to remember if ship tos have been retrieved
                    // so that we don't have to retrieve them multiple times (for performance)
                    $ship_tos_retrieved = FALSE;
                    
                    // loop through the offers, in order to determine if there is a valid offer
                    foreach ($offers as $offer) {
                        // if this offer has an order scope, then validate offer at the order level
                        if (
                            (ECOMMERCE_SHIPPING == FALSE)
                            || (ECOMMERCE_RECIPIENT_MODE == 'single recipient')
                            || ($offer['scope'] == 'order')
                        ) {
                            // if this offer is valid, then remember that and break out of loop
                            if (validate_offer($offer['id']) == TRUE) {
                                $valid_offer_exists = TRUE;
                                break;
                            }
                            
                        // else this offer has a recipient scope, so loop through recipients in order to validate offer at the recipient level
                        } else {
                            // if the ship tos have not been retrieved yet, then get them
                            if ($ship_tos_retrieved == FALSE) {
                                $query = "SELECT id FROM ship_tos WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                $ship_tos = mysqli_fetch_items($result);
                                $ship_tos_retrieved = TRUE;
                            }
                            
                            // loop through the ship tos in order to determine if offer is valid for any
                            foreach ($ship_tos as $ship_to) {
                                // if this offer is valid, then remember that and break out of ship to loop and offer loop
                                if (validate_offer($offer['id'], $ship_to['id']) == TRUE) {
                                    $valid_offer_exists = TRUE;
                                    break 2;
                                }
                            }
                        }
                    }
                    
                    // if a valid offer does not exist, then add notice
                    if ($valid_offer_exists == FALSE) {
                        // prepare variable that will keep track of descriptions that have been added
                        // so that we don't add the same description multiple times (multiple grouped offers might have the same description)
                        $descriptions = array();
                        
                        $output_descriptions = '';
                        
                        // loop through the offers in order to prepare descriptions
                        foreach ($offers as $offer) {
                            // if this offer is active and this description has not already been added, then add it
                            if (
                                ($offer['status'] == 'enabled')
                                && ($offer['start_date'] <= $current_date)
                                && ($current_date <= $offer['end_date'])
                                && (in_array(trim(mb_strtolower($offer['description'])), $descriptions) == FALSE)
                            ) {
                                // if a description has already been added, then add a comma and space for separation
                                if ($output_descriptions != '') {
                                    $output_descriptions .= ', ';
                                }
                                
                                $output_descriptions .= h($offer['description']);
                                
                                // remember that this description has been added
                                $descriptions[] = trim(mb_strtolower($offer['description']));
                            }
                        }
                        
                        $liveform->add_notice('The ' . h($special_offer_code_label) . ' that you entered (' . $output_descriptions . ') is valid, but the offer cannot be applied to your ' . h($shopping_cart_label) . ' until the requirements of the offer are met.');
                        
                        // record that a notice has been displayed to the shopper for this offer, so that we do not display a notice again in the future
                        $_SESSION['ecommerce']['offer_codes_that_generated_notices'][] = $offer_code;
                    }
                }
                
            // else an active offer does not exist, so add error
            } else {
                $liveform->mark_error('special_offer_code', 'The ' . h($special_offer_code_label) . ' that you entered (' . h($special_offer_code) . ') is not available at this time. Please try a different code.');
                $liveform->set('special_offer_code', '');
                $special_offer_code = '';
            }
        
        // else an offer was not found, so prepare error
        } else {
            $liveform->mark_error('special_offer_code', 'The ' . h($special_offer_code_label) . ' that you entered (' . h($special_offer_code) . ') could not be found. Please try a different code.');
            $liveform->set('special_offer_code', '');
            $special_offer_code = '';
        }
        
        // if the affiliate program is enabled
        // then check if the special offer code is also an affiliate code, so we can set affiliate code for visitor and order
        if (AFFILIATE_PROGRAM == TRUE) {
            $query = "SELECT id FROM contacts WHERE (affiliate_code = '" . escape($special_offer_code) . "') AND (affiliate_approved = '1')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if an affiliate was found then the special offer code is a valid affiliate code, so set affiliate code for visitor and order
            if (mysqli_num_rows($result) > 0) {
                // if visitor tracking is on then set affiliate code for visitor
                if (VISITOR_TRACKING == TRUE) {
                    $_SESSION['software']['affiliate_code'] = $special_offer_code;
                    setcookie('software[affiliate_code]', $special_offer_code, time() + 315360000, '/');
                    
                    $query = "UPDATE visitors SET affiliate_code = '" . escape($special_offer_code) . "' WHERE id = '" . $_SESSION['software']['visitor_id'] . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
                
                // prepare to update affiliate code for order
                $sql_affiliate_code = ", affiliate_code = '" . escape($special_offer_code) . "'";
            }
        }
    }
    
    $query =
        "UPDATE orders
        SET
            special_offer_code = '" . escape($special_offer_code) . "'
            $sql_affiliate_code
        WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}

update_order_item_prices();
                
apply_offers_to_cart();

// if offline payment is enabled and the visitor is logged in, then check if we need to update offline payment allowed for this order
if ((ECOMMERCE_OFFLINE_PAYMENT == TRUE) && (isset($_SESSION['sessionusername']) == TRUE)) {
    $user = validate_user();
    
    // if the user is at least a manager or if the user has access to set offline payment, then update offline payment allowed for order
    if (($user['role'] < 3) || ($user['set_offline_payment'] == TRUE)) {
        $query = "UPDATE orders SET offline_payment_allowed = '" . escape($liveform->get_field_value('offline_payment_allowed')) . "' WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
}

// If the visitor clicked the update button or hit enter.
if ((isset($_POST['submit_update']) == true) || (isset($_POST['submit_checkout']) == false)) {
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_POST['page_id']));
    exit;
}
    
// Otherwise the visitor clicked the checkout button.

// Check min and max quantity requirements for products.
check_quantity($liveform);

// check inventory for order items in order to make sure they are still all in stock
check_inventory($liveform);

// check reservations for calendar events in order to make sure they are still valid and available
check_reservations($liveform);

// before we proceed with checkout, we need to check product dependencies
foreach ($order_items as $order_item) {
    // if product has a required product, then make sure required product is in cart
    if ($order_item['required_product']) {
        // find out if required product is in cart
        $query =
            "SELECT id
            FROM order_items
            WHERE
                (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                AND (product_id = '" . $order_item['required_product'] . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if required product is not in cart, add product to cart and add notice, so user knows that required product was added to cart
        if (mysqli_num_rows($result) == 0) {
            // get information about required product
            $query = "SELECT name FROM products WHERE id = '" . $order_item['required_product'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $required_product_name = $row['name'];

            // add required product to cart
            add_order_item($order_item['required_product'], 1, 0, '', '');

            $liveform->add_notice(h($order_item['name']) . ' requires ' . h($required_product_name) . ', so ' . h($required_product_name) . ' has been added to your order.');
        }
    }
}

/* begin: check that there are no free order items alone in a ship to */

$query = "SELECT ship_to_id, product_name
         FROM order_items
         WHERE
            (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
            AND (ship_to_id > 0)
            AND (price <= 0)";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$free_order_items = array();

while ($row = mysqli_fetch_assoc($result)) {
    $free_order_items[] = $row;
}

// loop through all free order items to see if they are alone in ship to
foreach ($free_order_items as $key => $value) {
    // try to get a non-free order item in the same ship to as the free order item
    $query = "SELECT id
             FROM order_items
             WHERE
                (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                AND (ship_to_id = '" . $free_order_items[$key]['ship_to_id'] . "')
                AND (price > 0)";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if a non-free order item could not be found, add error
    if (mysqli_num_rows($result) == 0) {
        $liveform->mark_error('free_order_item_error', h($free_order_items[$key]['product_name']) . ' is a free item that you have requested to ship with no non-free items.  Free items must be shipped with at least one non-free item. You may update your order so that the item is shipped with at least one non-free item or you may remove the item.');
        break;
    }
}

/* end: check that there are no free order items alone in a ship to */

// If hooks are enabled, then get hook code.
if (defined('PHP_REGIONS') and PHP_REGIONS) {
    $hook_code = db_value("SELECT hook_code FROM shopping_cart_pages WHERE page_id = '" . escape($_POST['page_id']) . "'");

    // If there is hook code, then run it.
    if ($hook_code != '') {
        eval(prepare_for_eval($hook_code));
    }
}

// If there is an error or notice send user back to shopping cart page.
if (
    ($liveform->check_form_errors() == true)
    || ($liveform->check_form_notices() == true)
) {
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_POST['page_id']));
    exit;
}

// Determine if either one of the next pages is an express order, because we handle the process
// differently for that type of next page.  If an admin does something weird like only sets one of
// next page properties to an express order page, then we just assume they want to use express order
// for everything, and we ignore the non-express order property.

// First, look for an express order page in the next page with shipping.
$express_order_name = db(
    "SELECT page_name FROM page
    WHERE
        page_id = '" . e($page['next_page_id_with_shipping']) . "'
        AND page_type = 'express order'");

// If one was not found there, then look for one in the next page without shipping
if (!$express_order_name) {
    $express_order_name = db(
        "SELECT page_name FROM page
        WHERE
            page_id = '" . e($page['next_page_id_without_shipping']) . "'
            AND page_type = 'express order'");
}

// If an express order page was found for a next page, then just forward the user there.
if ($express_order_name) {

    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . encode_url_path($express_order_name));
    
// Otherwise we are dealing with a traditional order pathway, so deal with that.
} else {

    // If an order preview page has not been saved in the session yet, get order preview page for
    // this order pathway (in case shipping and billing information screens can be bypassed)
    if (!isset($_SESSION['ecommerce']['order_preview_page_id'])) {
        $_SESSION['ecommerce']['order_preview_page_id'] = db(
            "SELECT next_page_id FROM billing_information_pages
            WHERE page_id = '" . e($page['next_page_id_without_shipping']) . "'");
    }

    // find out if billing information is complete (we will use this later is several places)
    $query = "SELECT billing_complete FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $billing_complete = $row['billing_complete'];

    // find out if there are any products in the cart that are shippable
    $query = "SELECT order_items.id " .
             "FROM order_items " .
             "LEFT JOIN products ON products.id = order_items.product_id " .
             "WHERE order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "' AND products.shippable = 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if there is at least one product in cart that is shippable
    if (mysqli_num_rows($result) > 0) {
        /* begin: update completed ship tos
           We must do this in case order items have been added, quantities have been changed, shipping rules have changed,
           or etc. because the user will bypass the shipping screens for completed ship tos
           We are going to:
           1) check that order items are valid for destination
           2) check that shipment can get to recipient by requested arrival date
           3) If there are any problems, we will mark the ship to as incomplete.  Otherwise, we will update the shipping cost
                for the ship to and all order items.
        */
        
        // determine if an active shipping discount offer exists (we will use this later below)
        $active_shipping_discount_offer_exists = check_if_active_shipping_discount_offer_exists();
        
        $ship_tos = array();
        
        // get completed ship tos
        $query = "SELECT id, address_1, state, zip_code, country, arrival_date, shipping_method_id FROM ship_tos WHERE (order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (complete = 1)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        while ($row = mysqli_fetch_assoc($result)) {
            $ship_tos[] = $row;
        }
        
        // loop through all completed ship tos
        foreach ($ship_tos as $ship_to) {
            
            $ship_to_id = $ship_to['id'];
            $address_1 = $ship_to['address_1'];
            $state_code = $ship_to['state'];
            $zip_code = $ship_to['zip_code'];
            $country_code = $ship_to['country'];
            $arrival_date = $ship_to['arrival_date'];
            $shipping_method_id = $ship_to['shipping_method_id'];
            
            // get all order items for this ship to
            $query = "SELECT
                        order_items.id,
                        order_items.product_id,
                        order_items.quantity,
                        products.name,
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
                     WHERE order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "' AND order_items.ship_to_id = '$ship_to_id'
                     ORDER BY order_items.id ASC";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            $order_items = array();

            // add all items for this ship to to array
            while ($row = mysqli_fetch_assoc($result)) {
                $order_items[] = $row;
            }
            
            // loop through all order items for this ship to
            foreach ($order_items as $order_item) {

                // if order item is valid for destination
                if (validate_product_for_destination($order_item['product_id'], $country_code, $state_code) == true) {
                    $order_items_are_valid_for_destination = true;
                    
                } else {
                    $order_items_are_valid_for_destination = false;
                    break;
                }
            }
            
            // if all order items were valid for the destination, continue with checking if shipping method is still valid
            if ($order_items_are_valid_for_destination == true) {
                // get the current day of the week
                $day_of_week = mb_strtolower(date('l'));
                
                // get shipping method information
                $query = "SELECT id
                         FROM shipping_methods
                         WHERE
                            (id = '$shipping_method_id')
                            AND (status = 'enabled')
                            AND (start_time <= NOW())
                            AND (end_time >= NOW())
                            " . get_protected_shipping_method_filter() . "
                            AND (available_on_" . $day_of_week . " = '1')
                            AND ((available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if shipping method is still valid
                if (mysqli_num_rows($result) > 0) {
                    $shipping_method_is_valid = true;
                
                // else shipping method is no longer valid
                } else {
                    $shipping_method_is_valid = false;
                }

                // if shipping method is still valid, continue with checking if shipment can get to recipient by requested arrival date
                if ($shipping_method_is_valid == true) {

                    // if the requested arrival date is not at once
                    if ($arrival_date != '0000-00-00') {

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
                            
                            // if the shipping cut-off date & time is greater than the current date & time, then the shipment can be sent to the recipient in time for the requested arrival date
                            if ($shipping_cutoff_date_and_time > date('Y-m-d H:i:s')) {
                                $shipment_can_be_delivered_in_time = TRUE;
                                
                            // else the shipment cannot be sent to the recipient in time
                            } else {
                                $shipment_can_be_delivered_in_time = FALSE;
                            }
                            
                        // else there is not a shipping cut-off, so determine if this shipping method is valid by looking at the transit time
                        } else {

                            require_once(dirname(__FILE__) . '/shipping.php');
                            
                            $response = get_delivery_date(array(
                                'ship_to_id' => $ship_to_id,
                                'shipping_method' => array('id' => $shipping_method_id),
                                'zip_code' => $zip_code,
                                'country' => $country_code));

                            $delivery_date = $response['delivery_date'];

                            // If a delivery date was found, and it is before or on the arrival
                            // date, then remember that shipment can be delivered in time.
                            if ($delivery_date and $delivery_date <= $arrival_date) {
                                $shipment_can_be_delivered_in_time = true;
                            } else {
                                $shipment_can_be_delivered_in_time = false;
                            }
                        }
                        
                    // else the requested arrival date is at once, so the shipment can be delivered in time
                    } else {
                        $shipment_can_be_delivered_in_time = true;
                    }
                }
            }
            
            // if the order items are valid for the destination and shipping method is still valid and the shipment can be delivered in time,
            // this ship to has no problems so it can remain completed.
            // we just need to update the shipping cost for this ship to and all order items
            if (($order_items_are_valid_for_destination == true) && ($shipping_method_is_valid == true) && ($shipment_can_be_delivered_in_time == true)) {
                // get shipping method information
                $query = "SELECT
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
                            item_rate_first_item_excluded
                         FROM shipping_methods
                         WHERE id = '$shipping_method_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);

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
                
                $zones = get_valid_zones_for_destination($country_code, $state_code);
                
                // loop through all valid zones in order to find a zone that is allowed for this shipping method
                foreach ($zones as $zone_id) {
                    $query = "SELECT shipping_method_id
                             FROM shipping_methods_zones_xref
                             WHERE (shipping_method_id = '$shipping_method_id') AND (zone_id = '$zone_id')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    // if this zone is allowed for selected shipping method, get zone info
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

                $shipping_cost = 0;

                require_once(dirname(__FILE__) . '/shipping.php');

                $realtime_rate = get_shipping_realtime_rate(array(
                    'ship_to_id' => $ship_to_id,
                    'service' => $shipping_method_service,
                    'realtime_rate' => $shipping_method_realtime_rate,
                    'state' => $state_code,
                    'zip_code' => $zip_code,
                    'items' => $order_items));

                // If there was an error getting the realtime rate, then this shipping method
                // is not valid for the recipient, so mark recipient incomplete, so customer
                // will be forced to choose a different shipping method.  Also, skip to next
                // recipient.
                if ($realtime_rate === false) {
                    db("UPDATE ship_tos SET complete = '0' WHERE id = '" . e($ship_to_id) . "'");
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
                    'ship_to_id' => $ship_to_id));

                $shipping_cost += $zone_base_rate;

                // Create a counter so that later we can determine if the first item
                // should be excluded from shipping charges based on shipping method settings.
                $count = 0;
                
                // loop through all order items for this ship to in order to calculate shipping cost
                foreach ($order_items as $order_item) {
                    $quantity = $order_item['quantity'];
                    $primary_weight_points = $order_item['primary_weight_points'];
                    $secondary_weight_points = $order_item['secondary_weight_points'];
                    $free_shipping = $order_item['free_shipping'];
                    $extra_shipping_cost = $order_item['extra_shipping_cost'];

                    // if this is not a free shipping product, calculate shipping cost for product
                    if (($free_shipping == 0)) {
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
                        
                        // update shipping cost column for order item record
                        $shipping_cost_for_item_per_unit = round($shipping_cost_for_item / $quantity);
                        $query = "UPDATE order_items SET shipping = '$shipping_cost_for_item_per_unit' WHERE id = '" . $order_item['id'] . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                }
                
                // prepare for checking for shipping discounts
                $original_shipping_cost = 0;
                $offer = array();
                
                // if there is at least one active shipping discount offer, then continue with check
                if ($active_shipping_discount_offer_exists == TRUE) {
                    $offer = get_best_shipping_discount_offer($ship_to_id, $shipping_method_id);
                    
                    // if a shipping discount offer was found, then discount shipping
                    if ($offer != FALSE) {
                        // remember the original shipping cost, before it is updated
                        $original_shipping_cost = $shipping_cost;
                        
                        // update shipping cost to contain discount
                        $shipping_cost = $shipping_cost - ($shipping_cost * ($offer['discount_shipping_percentage'] / 100));
                    }
                }
                
                // update shipping cost for ship to
                $query =
                    "UPDATE ship_tos
                    SET
                        shipping_cost = '$shipping_cost',
                        original_shipping_cost = '$original_shipping_cost',
                        offer_id = '" . $offer['id'] . "'
                    WHERE id = '$ship_to_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
            // else the ship to has problems, so we will mark it incomplete,
            // and that will force the user to go through the shipping screens for this ship to again
            } else {
                $query = "UPDATE ship_tos SET complete = '0' WHERE id = '$ship_to_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        /* end: update completed ship tos */
        
        // get first ship to id that is not complete, so we can forward user to correct shipping address & arrival page
        $query = "SELECT id FROM ship_tos WHERE (order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (complete = 0) ORDER BY id LIMIT 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if an incomplete ship to was found, forward user to shipping address & arrival page for ship to
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page['next_page_id_with_shipping']) . '?ship_to_id=' . $row['id']);
        
        // else all ship tos are complete, so send user to billing information or order preview screen   
        } else {
            // find out if billing information is complete
            $query = "SELECT billing_complete FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $billing_complete = $row['billing_complete'];
            
            // if billing information is not complete, send user to billing information screen
            if ($billing_complete == 0) {
                header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page['next_page_id_without_shipping']));
                
            // else billing information is complete, so send user to order preview screen
            } else {
                header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['order_preview_page_id']));
            }
        }
        
    // else there is not a product that is shippable in the cart,
    // so send user to billing information or order preview screen
    } else {
        // find out if billing information is complete
        $query = "SELECT billing_complete FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $billing_complete = $row['billing_complete'];

        // if billing information is not complete, send user to billing information screen
        if ($billing_complete == 0) {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page['next_page_id_without_shipping']));
            
        // else billing information is complete, so send user to order preview screen
        } else {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['order_preview_page_id']));
        }
    }
    
    $liveform->remove_form('shopping_cart');
    
    // if visitor tracking is on, update visitor record with check out information, if visitor has not already checked out
    if (VISITOR_TRACKING == true) {
        $query = "UPDATE visitors
                 SET
                    order_checked_out = '1',
                    stop_timestamp = UNIX_TIMESTAMP()
                 WHERE id = '" . $_SESSION['software']['visitor_id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
}