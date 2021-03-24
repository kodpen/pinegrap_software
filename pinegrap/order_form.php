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

include_once('liveform.class.php');
$liveform = new liveform('order_form');
$liveform->add_fields_to_session();

// get page type properties for order form
$query =
    "SELECT
        order_form_pages.product_group_id,
        order_form_pages.product_layout,
        order_form_pages.add_button_next_page_id,
        order_form_pages.skip_button_next_page_id,
        page.page_name,
        page.page_folder AS folder_id
    FROM order_form_pages
    LEFT JOIN page ON order_form_pages.page_id = page.page_id
    WHERE order_form_pages.page_id = '" . escape($liveform->get_field_value('page_id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

if (mysqli_num_rows($result) == 0) {
    output_error('The page, for the order form you submitted, can no longer be found. <a href="javascript:history.go(-1);">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);

$product_group_id = $row['product_group_id'];
$product_layout = $row['product_layout'];
$add_button_next_page_id = $row['add_button_next_page_id'];
$skip_button_next_page_id = $row['skip_button_next_page_id'];
$page_name = $row['page_name'];
$folder_id = $row['folder_id'];

// If visitor does not have view access to the order that he/she claimed they submitted, log and output error.
if (check_view_access($folder_id) == false) {
    log_activity('access denied to submit order form (' . $page_name . ') because visitor does not have access to view order form', $_SESSION['sessionusername']);
    output_error('You do not have access to submit this order form. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if the add button was clicked or the enter key was pressed, then proceed with adding products to order
if (($liveform->field_in_session('submit_add') == true) || ($liveform->field_in_session('submit_skip') == false)) {
    // process products differently depending on page layout
    switch ($product_layout) {
        case '';
        case 'list';
            // get all products that are in product group
            $query =
                "SELECT
                    products.id,
                    products.name,
                    products.enabled,
                    products.short_description,
                    products.selection_type,
                    products.default_quantity,
                    products.recurring,
                    products.recurring_schedule_editable_by_customer,
                    products.start,
                    products.number_of_payments,
                    products.payment_period,
                    products.shippable,
                    products.inventory,
                    products.inventory_quantity,
                    products.backorder
                FROM products_groups_xref
                LEFT JOIN products ON products.id = products_groups_xref.product
                WHERE products_groups_xref.product_group = '$product_group_id'
                ORDER BY products_groups_xref.sort_order, products.name";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $products = array();
            
            // loop through results in order to add results to products array
            while ($row = mysqli_fetch_assoc($result)) {
                $products[] = $row;
            }

            $selected_products = array();

            // loop through products in order to determine which products were selected and perform validation
            foreach ($products as $product) {
                // assume that the product is not available until we find out otheriwse
                $available = FALSE;
                
                // If the product is enabled and there are not any inventory issues,
                // then the product is available.
                if (
                    ($product['enabled'] == 1)
                    &&
                    (
                        ($product['inventory'] == 0)
                        || ($product['inventory_quantity'] > 0)
                        || ($product['backorder'] == 1)
                    )
                ) {
                    $available = TRUE;
                }
                
                // prepare product description in case we need to add an error
                $product_description = '';
                
                // if there is a name, then add it to the description
                if ($product['name'] != '') {
                    $product_description .= $product['name'];
                }
                
                // if there is a short description, then add it to the description
                if ($product['short_description'] != '') {
                    // if the description is not blank, then add separator
                    if ($product_description != '') {
                        $product_description .= ' - ';
                    }
                    
                    $product_description .= $product['short_description'];
                }
                
                // assume that the product was not selected, until we find out otherwise
                $selected = false;
                
                // determine if the product is being added differently based on selection type
                switch ($product['selection_type']) {
                    case 'checkbox':
                        // if product was checked, then determine if we should add product to order
                        if ($liveform->get_field_value('product_' . $product['id'])) {
                            // if the product is available, then add product
                            if ($available == TRUE) {
                                $selected = true;
                                
                                $selected_products[] = array(
                                    'id' => $product['id'],
                                    'quantity' => $product['default_quantity'],
                                    'donation_amount' => 0,
                                    'ship_to' => $liveform->get_field_value('ship_to'),
                                    'add_name' => $liveform->get_field_value('add_name'),
                                    'recurring' => $product['recurring'],
                                    'recurring_schedule_editable_by_customer' => $product['recurring_schedule_editable_by_customer'],
                                    'start' => $product['start'],
                                    'number_of_payments' => $product['number_of_payments'],
                                    'payment_period' => $product['payment_period']
                                );
                                
                            // else the product is not available, so add error
                            } else {
                                $liveform->mark_error('product_' . $product['id'], 'Sorry, ' . h($product_description) . ' is not currently available.');
                            }
                        }
                        break;
                        
                    case 'quantity':
                        // Remove commas from quantity.
                        $liveform->assign_field_value('product_' . $product['id'], str_replace(',', '', $liveform->get_field_value('product_' . $product['id'])));

                        // if quantity entered is valid and greater than zero, then determine if we should add quantity to product in order
                        if ((preg_match("/^\d+$/", $liveform->get_field_value('product_' . $product['id'])) == true) && ($liveform->get_field_value('product_' . $product['id']) > 0)) {
                            // if the product is available, then add quantity
                            if ($available == TRUE) {
                                $selected = true;
                                
                                $selected_products[] = array(
                                    'id' => $product['id'],
                                    'quantity' => $liveform->get_field_value('product_' . $product['id']),
                                    'donation_amount' => 0,
                                    'ship_to' => $liveform->get_field_value('ship_to'),
                                    'add_name' => $liveform->get_field_value('add_name'),
                                    'recurring' => $product['recurring'],
                                    'recurring_schedule_editable_by_customer' => $product['recurring_schedule_editable_by_customer'],
                                    'start' => $product['start'],
                                    'number_of_payments' => $product['number_of_payments'],
                                    'payment_period' => $product['payment_period']
                                );
                                
                            // else the product is not available, so add error
                            } else {
                                $liveform->mark_error('product_' . $product['id'], 'Sorry, ' . h($product_description) . ' is not currently available.');
                            }
                        }
                        break;
                        
                    case 'donation':
                        // remove commas from donation amount if they exist
                        $donation_amount = str_replace(',', '', $liveform->get_field_value('donation_' . $product['id']));
                        
                        // convert donation amount into USD
                        // Suppressing error for PHP 7.1+ support
                        $donation_amount = @($donation_amount / VISITOR_CURRENCY_EXCHANGE_RATE);
                        
                        // convert dollars into cents for database storage
                        $donation_amount = round($donation_amount * 100);
                        
                        // if donation entered is greater than zero, then add donation to cart
                        if ($donation_amount > 0) {
                            // if the product is available, then add donation
                            if ($available == TRUE) {
                                $selected = true;
                                
                                $selected_products[] = array(
                                    'id' => $product['id'],
                                    'quantity' => 1,
                                    'donation_amount' => $donation_amount,
                                    'ship_to' => $liveform->get_field_value('ship_to'),
                                    'add_name' => $liveform->get_field_value('add_name'),
                                    'recurring' => $product['recurring'],
                                    'recurring_schedule_editable_by_customer' => $product['recurring_schedule_editable_by_customer'],
                                    'start' => $product['start'],
                                    'number_of_payments' => $product['number_of_payments'],
                                    'payment_period' => $product['payment_period']
                                );
                                
                            // else the product is not available, so add error
                            } else {
                                $liveform->mark_error('donation_' . $product['id'], 'Sorry, ' . h($product_description) . ' is not currently available.');
                            }
                        }
                        break;
                        
                    case 'autoselect':
                        // if the product is available, then check if we should add product
                        if ($available == TRUE) {
                            // assume that there is no ship to id for this product until we find out otherwise
                            $ship_to_id = 0;
                            
                            // If shipping is on and product is shippable, and mode is single-recipient
                            // or if mode is multi-recipient and a recipient has been selected or entered,
                            // then create ship to record or get existing ship to id.
                            // We do the extra checks to make sure that the visitor has selected/entered
                            // a recipient, because we don't want to create a ship to record until that is done.
                            // We deal with showing an error for that condition further below.
                            if (
                                (ECOMMERCE_SHIPPING == true)
                                && ($product['shippable'] == 1)
                                &&
                                (
                                    (ECOMMERCE_RECIPIENT_MODE == 'single recipient')
                                    ||
                                    (
                                        (
                                            ($liveform->get_field_value('ship_to') != '')
                                            && ($liveform->get_field_value('ship_to') != '- add name below -')
                                        )
                                        || ($liveform->get_field_value('add_name') != '')
                                    )
                                )
                            ) {
                                $ship_to_id = create_or_get_ship_to($liveform->get_field_value('ship_to'), $liveform->get_field_value('add_name'));
                            }

                            require_once(dirname(__FILE__) . '/check_for_products_in_cart.php');
                            
                            // if the product is not already in the order or ship to (if applicable), then continue to prepare to add product to order
                            if (
                                !check_for_products_in_cart(array(
                                    'products' => array($product),
                                    'quantity' => 1,
                                    'recipient' => array('id' => $ship_to_id)))
                            ) {
                                $selected = true;
                                
                                $selected_products[] = array(
                                    'id' => $product['id'],
                                    'quantity' => $product['default_quantity'],
                                    'donation_amount' => 0,
                                    'ship_to' => $liveform->get_field_value('ship_to'),
                                    'add_name' => $liveform->get_field_value('add_name'),
                                    'recurring' => $product['recurring'],
                                    'recurring_schedule_editable_by_customer' => $product['recurring_schedule_editable_by_customer'],
                                    'start' => $product['start'],
                                    'number_of_payments' => $product['number_of_payments'],
                                    'payment_period' => $product['payment_period']
                                );
                            }
                        
                        // else the product is not available, so add error
                        } else {
                            $liveform->mark_error('autoselect_' . $product['id'], 'Sorry, ' . h($product_description) . ' is not currently available.');
                        }
                        
                        break;
                }

                // If this product was selected, then validate a few things.
                if ($selected == true) {
                    // If the product is a recurring product
                    // and the product's recurring schedule is editable by the customer,
                    // then validate data for product
                    if (
                        ($product['recurring'] == 1)
                        && ($product['recurring_schedule_editable_by_customer'] == 1)
                    ) {
                        $liveform->validate_required_field('recurring_payment_period_' . $product['id'], 'Frequency is required.');
                        
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
                            $liveform->validate_required_field('recurring_number_of_payments_' . $product['id'], 'Number of Payments is required.');
                        }
                        
                        // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then require start date
                        if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                            $liveform->validate_required_field('recurring_start_date_' . $product['id'], 'Start Date is required.');
                        }
                        
                        // if credit/debit card is selected as a payment method and a payment gateway is selected and there is a value for the number of payments field,
                        // then perform validation that is specific to payment gateways for number of payments
                        if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && (ECOMMERCE_PAYMENT_GATEWAY != '') && ($liveform->get_field_value('recurring_number_of_payments_' . $product['id']) != '')) {
                            switch (ECOMMERCE_PAYMENT_GATEWAY) {
                                case 'ClearCommerce':
                                    // if the value is not 2-999, then mark error
                                    if (($liveform->get_field_value('recurring_number_of_payments_' . $product['id']) < 2) || ($liveform->get_field_value('recurring_number_of_payments_' . $product['id']) > 999)) {
                                        $liveform->mark_error('recurring_number_of_payments_' . $product['id'], 'Number of Payments requires a value from 2-999.');
                                    }
                                    break;
                                    
                                case 'First Data Global Gateway':
                                    // if the value is not 1-99, then mark error
                                    if (($liveform->get_field_value('recurring_number_of_payments_' . $product['id']) < 1) || ($liveform->get_field_value('recurring_number_of_payments_' . $product['id']) > 99)) {
                                        $liveform->mark_error('recurring_number_of_payments_' . $product['id'], 'Number of Payments requires a value from 1-99.');
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
                            && ($liveform->get_field_value('recurring_start_date_' . $product['id']) != '')
                        ) {
                            // if the date is not valid, then mark error
                            if (validate_date($liveform->get_field_value('recurring_start_date_' . $product['id'])) == false) {
                                $liveform->mark_error('recurring_start_date_' . $product['id'], 'Start Date must contain a valid date.');
                                
                            // else the date is valid, so check if date is in the past
                            } else {
                                $start_date_for_comparison = prepare_form_data_for_input($liveform->get_field_value('recurring_start_date_' . $product['id']), 'date');
                                
                                // if the date is in the past, then mark error
                                if ($start_date_for_comparison < date('Y-m-d')) {
                                    $liveform->mark_error('recurring_start_date_' . $product['id'], 'Start Date may not contain a date in the past.');
                                }
                            }
                        }
                    }

                    // If multi-recipient shipping is enabled and this product is shippable,
                    // and we have not already added an error for the ship to field,
                    // and a recipient was not selected,
                    // and the add name field is blank,
                    // then add error to ship to field and add name field.
                    if (
                        (ECOMMERCE_SHIPPING == true)
                        && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient')
                        && ($product['shippable'] == 1)
                        && ($liveform->check_field_error('ship_to') == false)
                        && (
                            ($liveform->get_field_value('ship_to') == '')
                            || ($liveform->get_field_value('ship_to') == '- add name below -')
                        )
                        && ($liveform->get_field_value('add_name') == '')
                    ) {
                        $liveform->mark_error('ship_to', 'Please select or enter a recipient.');
                        $liveform->mark_error('add_name');
                    }
                }
            }
            
            // if there is an error, then forward user back to previous screen
            if ($liveform->check_form_errors() == true) {
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($liveform->get_field_value('page_id'))));
                exit();
            }
            
            // loop through selected products in order to add products to order
            foreach ($selected_products as $product) {
                // add product to order
                $order_item_id = add_order_item($product['id'], $product['quantity'], $product['donation_amount'], $product['ship_to'], $product['add_name']);
                
                // if the product is a recurring product, then update recurring values for order item
                if ($product['recurring'] == 1) {
                    // if the customer can set the recurring schedule for this product then set values that customer entered
                    if ($product['recurring_schedule_editable_by_customer'] == 1) {
                        // set values that will be updated
                        $recurring_payment_period = $liveform->get_field_value('recurring_payment_period_' . $product['id']);
                        $recurring_number_of_payments = $liveform->get_field_value('recurring_number_of_payments_' . $product['id']);
                        
                        // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then set start date to the value that the customer entered
                        if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                            $recurring_start_date = prepare_form_data_for_input($liveform->get_field_value('recurring_start_date_' . $product['id']), 'date');
                            
                        // otherwise, set start date to today's date, because there was not a start date field
                        } else {
                            $recurring_start_date = date('Y-m-d');
                        }
                        
                    // else the customer cannot set the recurring schedule for this product, so use values from product
                    } else {
                        // if the payment period is blank, then default to Monthly
                        if ($product['payment_period'] == '') {
                            $recurring_payment_period = 'Monthly';
                            
                        // else the payment period is not blank, so set to the value in the product
                        } else {
                            $recurring_payment_period = $product['payment_period'];
                        }
                        
                        $recurring_number_of_payments = $product['number_of_payments'];
                        $recurring_start_date = date('Y-m-d', time() + (86400 * $product['start']));
                    }
                    
                    // update recurring fields for order item
                    $query =
                        "UPDATE order_items
                        SET
                            recurring_payment_period = '" . escape($recurring_payment_period) . "',
                            recurring_number_of_payments = '" . escape($recurring_number_of_payments) . "',
                            recurring_start_date = '" . escape($recurring_start_date) . "'
                        WHERE id = '" . $order_item_id . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }

            // If at least one product was added to the order, then offers for this order
            // need to be refreshed, so that the subtotal in a cart region is accurate.
            if (count($selected_products) > 0) {
                update_order_item_prices();
                apply_offers_to_cart();
            }
            
            break;

        case 'drop-down selection':
            // get information about product
            $query =
                "SELECT
                    name,
                    enabled,
                    short_description,
                    selection_type,
                    price,
                    default_quantity,
                    inventory,
                    inventory_quantity,
                    backorder
                FROM products
                WHERE id = '" . escape($liveform->get_field_value('product_id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $product = mysqli_fetch_assoc($result);
            
            // If product is enabled and there are not any inventory issues,
            // then allow product to be added.
            if (
                ($product['enabled'] == 1)
                &&
                (
                    ($product['inventory'] == 0)
                    || ($product['inventory_quantity'] > 0)
                    || ($product['backorder'] == 1)
                )
            ) {
                // If a quantity was entered, then remove commas.
                if ($liveform->get_field_value('quantity') != '') {
                    $liveform->assign_field_value('quantity', str_replace(',', '', $liveform->get_field_value('quantity')));
                }

                // if product is a donation
                if ($product['selection_type'] == 'donation') {
                    $quantity = 1;
                    
                    // If a quantity field was displayed to the user, and the quantity is valid,
                    // then use that for the quantity.
                    if (
                        ($liveform->get_field_value('quantity') != '')
                        && (preg_match('/^\d+$/', $liveform->get_field_value('quantity')) == 1)
                    ) {
                        $actual_quantity = $liveform->get_field_value('quantity');
                        
                    // Otherwise a quantity field was not displayed to the user, or the quantity is invalid,
                    // so use default quantity.
                    } else {
                        $actual_quantity = $product['default_quantity'];
                    }

                    $donation_amount = $product['price'] * $actual_quantity;

                // else product is not a donation, so just prepare quantity
                } else {
                    // If the quantity is not valid, then output error.
                    if (preg_match('/^\d+$/', $liveform->get_field_value('quantity')) == 0) {
                        $liveform->mark_error('quantity', 'Please enter a valid quantity.');
                        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($liveform->get_field_value('page_id'))));
                        exit();
                    }

                    $quantity = $liveform->get_field_value('quantity');
                    $donation_amount = 0;
                }
                
                add_order_item($liveform->get_field_value('product_id'), $quantity, $donation_amount, $liveform->get_field_value('ship_to'), $liveform->get_field_value('add_name'));

                // An item has been added to the order, so offers for this order
                // need to be refreshed, so that the subtotal in a cart region is accurate.
                update_order_item_prices();
                apply_offers_to_cart();
                
            // else the product is not available, so add error and forward user back to previous page
            } else {
                // prepare product description for error
                $product_description = '';
                
                // if there is a name, then add it to the description
                if ($product['name'] != '') {
                    $product_description .= $product['name'];
                }
                
                // if there is a short description, then add it to the description
                if ($product['short_description'] != '') {
                    // if the description is not blank, then add separator
                    if ($product_description != '') {
                        $product_description .= ' - ';
                    }
                    
                    $product_description .= $product['short_description'];
                }
                
                $liveform->mark_error('product_id', 'Sorry, ' . h($product_description) . ' is not currently available.');
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($liveform->get_field_value('page_id'))));
                exit();
            }
            
            break;
    }

    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($add_button_next_page_id));
    
// else the skip button was clicked, so forward user to next page
} else {
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($skip_button_next_page_id));
}

$liveform->remove_form('order_form');
?>
