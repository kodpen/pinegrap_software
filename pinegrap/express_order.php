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


//we get order id with GET and set session again
//this is because of iyzipay 3DS payment return loses order and token numbers than say user logged in
//so we include them in callback url and set them again after return.
if($_GET['order_id'] and $_GET['mode'] == 'iyzipay_threedsecure_return'){
    $_SESSION['ecommerce']['order_id'] = $_GET['order_id'];
}
if($_GET['token'] and $_GET['mode'] == 'iyzipay_threedsecure_return'){
    $_SESSION['software']['token'] = $_GET['token'];
}



// if this is not the request that PayPal Express Checkout returns,
// then validate token field.  PayPal Express Checkout uses "token" for a query string parameter,
// like we do, so that is why we can't validate the token.  This should not expose any major problem.
if ($_GET['mode'] != 'paypal_express_checkout_return') {
    validate_token_field();
}

require_cookies();
initialize_order();

$liveform = new liveform('express_order');

$ghost = $_SESSION['software']['ghost'];

// if the mode is not paypal_express_checkout_return, then add fields to session
if ($_GET['mode'] != 'paypal_express_checkout_return' and $_GET['mode'] != 'iyzipay_threedsecure_return') {
    $liveform->add_fields_to_session();
}

$liveform->clear_notices();

$order_id = $_SESSION['ecommerce']['order_id'];

// Get properties for the express order page.
$query =
    "SELECT
        shopping_cart_label,
        shipping_form,
        special_offer_code_label,
        form
    FROM express_order_pages
    WHERE page_id = '" . escape($_POST['page_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$shopping_cart_label = $row['shopping_cart_label'];
$custom_shipping_form = $row['shipping_form'];
$special_offer_code_label = $row['special_offer_code_label'];
$custom_billing_form = $row['form'];

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

// assume that the order has not been submitted until we find out otherwise
$order_submitted = false;

// if purchase now button was clicked, or Continue to PayPal was clicked, or this is a return from PayPal, then the order has been submitted
if (
    (isset($_POST['submit_purchase_now']) == true)
    || ($_GET['mode'] == 'paypal_express_checkout_return')
    || ($_GET['mode'] == 'iyzipay_threedsecure_return')
) {
    $order_submitted = true;
}

// if the order has not been submitted yet, then update order items
if ($order_submitted == false) {
    // loop through all order items in order
    foreach ($order_items as $key => $order_item) {
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
                unset($order_items[$key]);
                
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
                unset($order_items[$key]);

            // else if quantity is a positive integer and new quantity is different from old quantity, then update quantity
            } elseif ((preg_match("/^\d+$/", $_POST['quantity'][$order_item['id']]) == true) && ($_POST['quantity'][$order_item['id']] != $order_item['quantity'])) {
                $query = "UPDATE order_items SET quantity = '" . escape($_POST['quantity'][$order_item['id']]) . "' WHERE id = '" . $order_item['id'] . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $order_items[$key]['quantity'] = $_POST['quantity'][$order_item['id']];
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
                WHERE offers.code = '" . escape($offer_code) . "'";
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
    
    // if gift cards are enabled, then check to see if a gift card code was entered
    if (ECOMMERCE_GIFT_CARD == TRUE) {
        // if the gift card code field was not blank, then continue to check if the code is valid
        if ($liveform->get_field_value('gift_card_code') != '') {
            // If there are only gift card products in the order,
            // then do not allow a gift card to be applied to order.
            if (
                db_value(
                    "SELECT COUNT(*)
                    FROM order_items
                    LEFT JOIN products ON order_items.product_id = products.id
                    WHERE
                        (order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                        AND products.gift_card = '0'") == 0
            ) {
                $liveform->assign_field_value('gift_card_code', '');
                $liveform->mark_error('gift_card_code', 'Sorry, we do not allow a gift card to be applied to an order that only contains gift cards.  Please add a different type of item to your order in order to apply the gift card.');
                go(PATH . encode_url_path(get_page_name($_POST['page_id'])));
            }
            
            // Remove non-alphanumeric characters from gift card code,
            // in order to get rid of dashes, spaces, and etc.
            $gift_card_code = preg_replace('/[^A-Za-z0-9]/', '', $liveform->get_field_value('gift_card_code'));

            // check if the gift card has already been added to the order
            $query = "SELECT COUNT(*) FROM applied_gift_cards WHERE (order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (code = '" . escape($gift_card_code) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            
            // if the gift card has already been added to the order, then add error
            if ($row[0] > 0) {
                $liveform->mark_error('gift_card_code', 'Sorry, the gift card has already been applied. Please enter a different code.');
                
            // else the gift card has not already been added to the order, so determine if the gift card code is valid
            } else {
                // Check for a gift card in this system.
                $gift_card = db_item(
                    "SELECT
                        id,
                        balance,
                        expiration_date
                    FROM gift_cards
                    WHERE code = '" . escape($gift_card_code)  . "'");

                // If a gift card could not be found in this system,
                // and givex is disabled, then output error.
                if (($gift_card['id'] == '') && (ECOMMERCE_GIVEX == false)) {
                    $liveform->mark_error('gift_card_code', 'Sorry, we could not find a gift card for that code.  Please check that you entered the code correctly.');
                    go(PATH . encode_url_path(get_page_name($_POST['page_id'])));
                }

                // If a gift card was found in this system, but it has expired, then output error.
                if (
                    ($gift_card['id'] != '')
                    && ($gift_card['expiration_date'] != '0000-00-00')
                    && ($gift_card['expiration_date'] < date('Y-m-d'))
                ) {
                    $liveform->mark_error('gift_card_code', 'Sorry, we cannot accept the gift card because it has expired. You may enter a different gift card code.');
                    $liveform->assign_field_value('gift_card_code', '');
                    go(PATH . encode_url_path(get_page_name($_POST['page_id'])));
                }

                // If a gift card was found in this system, but the balance is 0, then output error.
                if (($gift_card['id'] != '') && ($gift_card['balance'] == 0)) {
                    $liveform->mark_error('gift_card_code', 'Sorry, we cannot accept the gift card because there is no remaining balance. You may enter a different gift card code.');
                    $liveform->assign_field_value('gift_card_code', '');
                    go(PATH . encode_url_path(get_page_name($_POST['page_id'])));
                }

                // If a gift card was found in this system, then prepare values.
                if ($gift_card['id'] != '') {
                    $gift_card_id = $gift_card['id'];
                    $old_balance = $gift_card['balance'];
                    $givex = 0;

                // Otherwise a gift card was not found in this system,
                // so if givex is enabled, then check if card exists at givex.
                } else if (ECOMMERCE_GIVEX == true) {
                    // if cURL does not exist, then add error and send user back to previous screen
                    if (function_exists('curl_init') == FALSE) {
                        $liveform->mark_error('gift_card_code', 'Sorry, we cannot communicate with the gift card service, because cURL is not installed, so we cannot accept the gift card. The administrator of this website should install cURL. If you would like to proceed without using the gift card, then you may remove the code and continue.');
                        
                        // send visitor back to the previous screen
                        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($_POST['page_id'])));
                        exit();
                    }
                    
                    // send a balance request to Givex in order to check if the gift card code is valid
                    $result = send_givex_request('balance', $gift_card_code);
                    
                    // if there was an error or the balance is 0 then prepare error message and send user back to previous screen
                    if ((isset($result['curl_errno']) == TRUE) || (isset($result['error_message']) == TRUE) || ($result['balance'] == 0)) {
                        // if there was a curl error, then prepare error message
                        if (isset($result['curl_errno']) == TRUE) {
                            $error_message = 'Sorry, we cannot communicate with the gift card service at the moment, so we cannot accept the gift card. Please try again later. If the problem continues, then please contact the administrator of this website. If you would like to proceed without using the gift card, then you may remove the code and continue. cURL Error Number: ' . h($result['curl_errno']) . '. cURL Error Message: ' . h($result['curl_error']) . '.';
                            
                        // else if there was a Givex error, then prepare error message
                        } else if (isset($result['error_message']) == TRUE) {
                            $error_message = 'Sorry, we cannot accept the gift card because there was an error from the gift card service: ' . h($result['error_message']) . '. Please verify that you have entered the gift card code correctly.';
                            
                        // else if the balance is 0, then prepare error message
                        } else if ($result['balance'] == 0) {
                            $error_message = 'Sorry, we cannot accept the gift card because there is no remaining balance. You may enter a different gift card code.';
                            $liveform->assign_field_value('gift_card_code', '');
                        }
                        
                        // add error
                        $liveform->mark_error('gift_card_code', $error_message);
                        
                        // send visitor back to the previous screen
                        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($_POST['page_id'])));
                        exit();
                    }

                    $gift_card_id = '';
                    $old_balance = $result['balance'] * 100;
                    $givex = 1;
                }
                
                // add record to database for applied gift card
                $query =
                    "INSERT INTO applied_gift_cards (
                        gift_card_id,
                        order_id,
                        code,
                        old_balance,
                        givex)
                    VALUES (
                        '" . $gift_card_id . "',
                        '" . $_SESSION['ecommerce']['order_id'] . "',
                        '" . escape($gift_card_code) . "',
                        '" . escape($old_balance) . "',
                        '" . $givex . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // clear the gift card code form field
                $liveform->assign_field_value('gift_card_code', '');
            }
        }
    }
    
    // if there was no opt-in field on previous screen or if the opt-in checkbox was checked
    if (($liveform->get_field_value('opt_in_displayed') == 'false') || ($liveform->get_field_value('opt_in') == '1')) {
        $opt_in = 1;
        
    // else this order is not opted in
    } else {
        $opt_in = 0;
    }
    
    // if tax-exempt was checked
    if ($liveform->get_field_value('tax_exempt') == '1') {
        $tax_exempt = 1;
        
    // else tax-exempt was not checked
    } else {
        $tax_exempt = 0;
    }
    
    $sql_offline_payment_allowed = "";
    
    // if offline payment is enabled and the visitor is logged in, then check if we need to update offline payment allowed for this order
    if ((ECOMMERCE_OFFLINE_PAYMENT == TRUE) && (isset($_SESSION['sessionusername']) == TRUE)) {
        $user = validate_user();
        
        // if the user is at least a manager or if the user has access to set offline payment, then prepare to update offline payment allowed for order
        if (($user['role'] < 3) || ($user['set_offline_payment'] == TRUE)) {
            $sql_offline_payment_allowed = "offline_payment_allowed = '" . escape($liveform->get_field_value('offline_payment_allowed')) . "',";
        }
    }
    
    // update order information
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
            billing_phone_number = '" . escape($liveform->get_field_value('billing_phone_number')) . "',
            billing_fax_number = '" . escape($liveform->get_field_value('billing_fax_number')) . "',
            billing_email_address = '" . escape($liveform->get_field_value('billing_email_address')) . "',
            custom_field_1 = '" . escape($liveform->get_field_value('custom_field_1')) . "',
            custom_field_2 = '" . escape($liveform->get_field_value('custom_field_2')) . "',
            opt_in = '$opt_in',
            tax_exempt = '$tax_exempt',
            po_number = '" . escape($liveform->get_field_value('po_number')) . "',
            $sql_offline_payment_allowed
            referral_source_code = '" . escape($liveform->get_field_value('referral_source')) . "',
            billing_complete = '0'
        WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // If a custom billing form is enabled, then add custom billing form data to database,
    // but don't require fields yet because update button was clicked and not submit button.
    if ($custom_billing_form) {

        submit_custom_form(array(
            'form' => $liveform,
            'page_id' => $_POST['page_id'],
            'page_type' => 'express order',
            'form_type' => 'billing',
            'require' => false));
    }
    
    $require_state = false;
    $require_zip_code = false;

    // If a country has been selected and then determine if state and zip code are required.
    if ($liveform->get('billing_country')) {

        // If there is a state in this system for the selected country, then require state.
        if (db(
            "SELECT states.id
            FROM states
            LEFT JOIN countries ON countries.id = states.country_id
            WHERE countries.code = '" . e($liveform->get('billing_country')) . "'
            LIMIT 1")
        ) {
            $require_state = true;
        }

        // If this country requires a zip code, then require it.
        if (
            db(
                "SELECT zip_code_required FROM countries
                WHERE code = '" . e($liveform->get('billing_country')) . "'")
        ) {
            $require_zip_code = true;
        }
    }

    $user_id = '';
    $contact_id = '';
    
    // if an error does not exist, and if there is some billing info, then add/update the contact record for this order/user
    if (
        ($liveform->check_form_errors() == false)
        &&
        (
            ($liveform->get_field_value('billing_first_name') != '')
            && ($liveform->get_field_value('billing_last_name') != '')
            && ($liveform->get_field_value('billing_address_1') != '')
            && ($liveform->get_field_value('billing_city') != '')
            && ($liveform->get_field_value('billing_country') != '')
            && ($liveform->get_field_value('billing_phone_number') != '')
            && ($liveform->get_field_value('billing_email_address') != '')
            && (validate_email_address($liveform->get_field_value('billing_email_address')) == true)
            &&
            (
                ($require_state == false) 
                || (
                    ($require_state == true) 
                    && ($liveform->get('billing_state') != '')
                )
            )
            and (!$require_zip_code or $liveform->get('billing_zip_code'))
        )
    ) {

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
    }
    
    update_order_item_prices();
    
    // if tax is on, apply taxes to order
    if (ECOMMERCE_TAX == true) {
        update_order_item_taxes();
    }
    
    apply_offers_to_cart();
}

// Loop through order items in order to validate and store recurring schedule,
// gift card data, and product form data if any exists.
foreach ($order_items as $order_item) {
    // if order item is a recurring order item, then save recurring data
    if ($order_item['recurring'] == 1) {
        // if the customer can set the recurring schedule for this order item then validate values that customer entered and possibly save values
        if ($order_item['recurring_schedule_editable_by_customer'] == 1) {
            // if the order has been submitted, then require recurring schedule fields
            if ($order_submitted == true) {
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
        if ($order_item['quantity'] <= 100) {
            $number_of_gift_cards = $order_item['quantity'];
            
        // Otherwise the quantity is greater than 100, so set the number of gift cards to 100.
        } else {
            $number_of_gift_cards = 100;
        }
        
        // Loop through each gift card quantity.
        for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
            // If the order has been submitted, then require recipient email address.
            if ($order_submitted == true) {
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
            if ($order_item['quantity'] <= 100) {
                $number_of_forms = $order_item['quantity'];
                
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
                
                // if the order has been submitted and if field is required, then validate field
                if (($order_submitted == true) && ($form_field['required'] == 1)) {
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

// If shipping is enabled then deal with shipping
if (ECOMMERCE_SHIPPING) {

    // Get all recipients for this order.  It is important that we get fresh recipient info now from
    // the db, because order items might have been removed above (e.g. quantity updated to 0) which
    // would remove recipients.

    $recipients = db_items(
        "SELECT id, ship_to_name, address_verified
        FROM ship_tos
        WHERE order_id = '" . e($order_id) . "'
        ORDER BY id");

    if ($recipients) {

        require_once(dirname(__FILE__) . '/shipping.php');

        // Get all active arrival dates, so later we know which arrival dates are valid for recipients
        $arrival_dates = db_items(
            "SELECT id, name, arrival_date, code, custom, custom_maximum_arrival_date
            FROM arrival_dates
            WHERE
                (status = 'enabled')
                AND (start_date <= CURRENT_DATE())
                AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())",
            'id');

        $number_of_recipients = count($recipients);
    }

    // Loop through the recipients in order to deal with shipping info
    foreach ($recipients as $recipient) {

        // Get unique prefix for all shipping fields for this recipient, because there might be
        // multiple recipients.
        $prefix = 'shipping_' . $recipient['id'] . '_';

        // If there is more than one recipient, then prepare to add ship to name to the end of all
        // messages/errors, so visitor will understand the context of the error.

        $message_ship_to_name = '';
        $output_message_ship_to_name = '';

        if ($number_of_recipients > 1) {
            $message_ship_to_name = ' (' . $recipient['ship_to_name'] . ')';
            $output_message_ship_to_name = h($message_ship_to_name);
        }

        // Figure out if a state is required based on the selected country.  If there are states
        // in the db for a country, then a state is required.  Otherwise, the state is optional.
        // We are preparing this now because we will need it in a couple of areas below.

        $state_required = false;
        $zip_code_required = false;

        $state = $liveform->get($prefix . 'state');
        $zip_code = $liveform->get($prefix . 'zip_code');
        $country = $liveform->get($prefix . 'country');

        // If a country has been selected and then determine if state and zip code are required.
        if ($country) {

            // If there is a state in this system for the selected country, then require state.
            if (db(
                "SELECT states.id FROM states
                LEFT JOIN countries ON countries.id = states.country_id
                WHERE countries.code = '" . e($country) . "'
                LIMIT 1")
            ) {
                $state_required = true;
            }

            // If this country requires a zip code, then require it.
            if (
                db(
                    "SELECT zip_code_required FROM countries
                    WHERE code = '" . e($country) . "'")
            ) {
                $zip_code_required = true;
            }
        }

        // If the order has been submitted (not just updated), then require address fields
        if ($order_submitted) {

            $liveform->validate_required_field(
                $prefix . 'first_name',
                'First Name is required.' . $output_message_ship_to_name);

            $liveform->validate_required_field(
                $prefix . 'last_name',
                'Last Name is required.' . $output_message_ship_to_name);

            $liveform->validate_required_field(
                $prefix . 'address_1',
                'Address 1 is required.' . $output_message_ship_to_name);

            $liveform->validate_required_field(
                $prefix . 'city',
                'City is required.' . $output_message_ship_to_name);

            $liveform->validate_required_field(
                $prefix . 'country',
                'Country is required.' . $output_message_ship_to_name);

            if ($state_required) {
                $liveform->validate_required_field(
                    $prefix . 'state',
                    'State/Province is required.' . $output_message_ship_to_name);
            }

            if ($zip_code_required) {
                $liveform->validate_required_field(
                    $prefix . 'zip_code',
                    'Zip/Postal Code is required.' . $output_message_ship_to_name);
            }
        }

        // If the country has been selected and the state has also been selected or the state is not
        // required, then check if items are valid for destination.

        if ($country and ($state or !$state_required)) {

            $items = db_items(
                "SELECT
                    order_items.id,
                    order_items.product_id,
                    order_items.product_name AS name,
                    products.short_description
                FROM order_items
                LEFT JOIN products ON products.id = order_items.product_id
                WHERE
                    (order_items.order_id = '" . e($order_id) . "')
                    AND (order_items.ship_to_id = '" . e($recipient['id']) . "')
                ORDER BY order_items.id");

            // Loop through the items for this recipient in order to determine if any are not valid for
            // the destination.
            foreach ($items as $item) {

                // If product is not valid for destination then return error
                if (!validate_product_for_destination($item['product_id'], $country, $state)) {

                    // If product restriction message is set, then use it
                    if (ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE) {
                        $message = ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE;

                    // Otherwise product restriction message is not set, so use default message
                    } else {
                        $message = 'Sorry, this item cannot be delivered to the specified shipping address. Please remove it from your order to continue.';
                    }

                    $message .= ' (' . $item['name'] . ' - ' . $item['short_description'] . ')';

                    $liveform->mark_error('', h($message) . $output_message_ship_to_name);
                }
            }
        }

        $address_verified = $recipient['address_verified'];

        // If address verification is enabled and the country is US and all of the necessary
        // shipping fields have been completed then verify address.
        if (
            ECOMMERCE_ADDRESS_VERIFICATION
            and ($country == 'US' or $country == 'USA')
            and $liveform->get($prefix . 'address_1')
            and $liveform->get($prefix . 'city')
            and $state
            and $liveform->get($prefix . 'zip_code')
        ) {

            $address_verified = verify_address(array(
                'liveform' => $liveform,
                'address_type' => 'shipping',
                'prefix' => $prefix,
                'old_address_verified' => $address_verified,
                'ship_to_id' => $recipient['id']));
        }

        // If a custom shipping form is enabled, then submit form.
        if ($custom_shipping_form) {

            submit_custom_form(array(
                'form' => $liveform,
                'page_id' => $_POST['page_id'],
                'page_type' => 'express order',
                'form_type' => 'shipping',
                'ship_to_id' => $recipient['id'],
                'prefix' => $prefix,
                // Require fields if order has been submitted
                'require' => $order_submitted));
        }

        // If the order has been submitted and there is at least one active arrival date, then
        // require arrival date
        if ($order_submitted and $arrival_dates) {
            $liveform->validate_required_field(
                $prefix . 'arrival_date',
                'Requested Arrival Date is required.' . $output_message_ship_to_name);
        }

        $arrival_date_id = $liveform->get($prefix . 'arrival_date');

        // If the visitor selected an arrival date, then validate it
        if ($arrival_date_id) {

            // Try to find an active/valid arrival date for the arrival date the visitor selected
            $arrival_date = $arrival_dates[$arrival_date_id];

            // If the selected arrival date is active/valid, then continue to validate
            if ($arrival_date) {

                // If the arrival date has a custom field, set arrival date to custom arrival date and require custom field
                if ($arrival_date['custom']) {
                    
                    $arrival_date['arrival_date'] = prepare_form_data_for_input(
                        $liveform->get($prefix . 'custom_arrival_date_' . $arrival_date['id']),
                        'date');

                    $liveform->validate_required_field(
                        $prefix . 'custom_arrival_date_' . $arrival_date['id'],
                        'A custom arrival date is required, because you selected ' . h($arrival_date['name']) . '.' . $output_message_ship_to_name);
                    
                    // If there is not already an error for the custom date field, then validate date
                    if (!$liveform->check_field_error($prefix . 'custom_arrival_date_' . $arrival_date['id'])) {

                        // split date into parts
                        $arrival_date_parts = explode('-', $arrival_date['arrival_date']);
                        $year = $arrival_date_parts[0];
                        $month = $arrival_date_parts[1];
                        $day = $arrival_date_parts[2];
                        
                        // if the first two digits of the year are "00", then the user probably entered a 2 digit year, so set the year to a 4 digit year with "20" at the beginning
                        if (mb_substr($year, 0, 2) == '00') {
                            $year = '20' . mb_substr($year, 2, 2);
                            $arrival_date['arrival_date'] = $year . '-' . $month . '-' . $day;
                            $liveform->set(
                                $prefix . 'custom_arrival_date_' . $arrival_date['id'],
                                prepare_form_data_for_output($arrival_date['arrival_date'], 'date'));
                        }
                        
                        // if custom date is not valid, then mark error
                        if (
                            !is_numeric($month) or !is_numeric($day) or !is_numeric($year)
                            or !checkdate($month, $day, $year)
                        ) {
                            $liveform->mark_error(
                                $prefix . 'custom_arrival_date_' . $arrival_date['id'],
                                'The custom arrival date is not valid.' . $output_message_ship_to_name);

                            $arrival_date['arrival_date'] = '';
                        }
                    }
                    
                    // if there is not already an error for the custom date field
                    // and there is a maximum arrival date
                    // and the submitted arrival date is greater than the maximum arrival date,
                    // then prepare error and clear arrival date
                    if (
                        !$liveform->check_field_error($prefix . 'custom_arrival_date_' . $arrival_date['id'])
                        and $arrival_date['custom_maximum_arrival_date'] != '0000-00-00'
                        and $arrival_date['arrival_date'] > $arrival_date['custom_maximum_arrival_date']
                    ) {
                        $liveform->mark_error(
                            $prefix . 'custom_arrival_date_' . $arrival_date['id'],
                            'The custom arrival date that you entered is after the latest allowed arrival date. Please enter a date that is on or before ' . prepare_form_data_for_output($arrival_date['custom_maximum_arrival_date'], 'date') . '.' . $output_message_ship_to_name);

                        $arrival_date['arrival_date'] = '';
                    }
                }

            // Otherwise the selected arrival date is not active/valid, so prepare error
            } else {
                $liveform->mark_error(
                    $prefix . 'arrival_date',
                    'Sorry, that arrival date is not currently available.' . $output_message_ship_to_name);
            }
        }

        // If the order has been submitted then require shipping method
        if ($order_submitted) {
            $liveform->validate_required_field(
                $prefix . 'method',
                'Please select a shipping method.' . $output_message_ship_to_name);
        }

        $shipping_method_id = $liveform->get($prefix . 'method');
        $shipping_method = array();
        $zone = array();

        // If a shipping method was selected then make sure it is still available and valid for the
        // address and arrival date.
        if ($shipping_method_id) {

            $request = array(
                'shipping_method_id' => $shipping_method_id,
                'ship_to_id' => $recipient['id'],
                'address_1' => $liveform->get($prefix . 'address_1'),
                'state' => $state,
                'zip_code' => $zip_code,
                'country' => $country,
                'arrival_date' => $arrival_date['arrival_date']);

            $response = check_shipping_method($request);

            // If the shipping method is valid then store info for method and zone
            if ($response['status'] == 'success') {
                $shipping_method = $response['shipping_method'];
                $zone = $response['zone'];

            // Otherwise the shipping method is not valid, so add error
            } else {
                $liveform->mark_error($prefix . 'method', h($response['message']) . $output_message_ship_to_name);

                // Log activity because it is important for an admin to know if there are shipping
                // method issues preventing orders from being completed.
                log_activity($response['message'] . ' (Order ID: ' . $order_id . ', Reference Code: ' . db("SELECT reference_code FROM orders WHERE id = '"  . e($order_id) . "'") . ', Ship to Name: ' . $recipient['ship_to_name'] . ') Request: ' . print_r($request, true));
            }
        }

        // If there is a valid shipping method selected for this recipient, then we can mark the
        // recipient complete, which other logic in different areas uses for various purposes.
        if ($shipping_method) {
            $complete = 1;
        } else {
            $complete = 0;
        }

        // Update recipient in db
        db(
            "UPDATE ship_tos SET
                salutation = '" . e($liveform->get($prefix . 'salutation')) . "',
                first_name = '" . e($liveform->get($prefix . 'first_name')) . "',
                last_name = '" . e($liveform->get($prefix . 'last_name')) . "',
                company = '" . e($liveform->get($prefix . 'company')) . "',
                address_1 = '" . e($liveform->get($prefix . 'address_1')) . "',
                address_2 = '" . e($liveform->get($prefix . 'address_2')) . "',
                city = '" . e($liveform->get($prefix . 'city')) . "',
                state = '" . e($liveform->get($prefix . 'state')) . "',
                zip_code = '" . e($liveform->get($prefix . 'zip_code')) . "',
                country = '" . e($liveform->get($prefix . 'country')) . "',
                address_type = '" . e($liveform->get($prefix . 'address_type')) . "',
                address_verified = '" . e($address_verified) . "',
                phone_number = '" . e($liveform->get($prefix . 'phone_number')) . "',
                arrival_date_id = '" . e($arrival_date['id']) . "',
                arrival_date_code = '" . e($arrival_date['code']) . "',
                arrival_date = '" . e($arrival_date['arrival_date']) . "',
                shipping_method_id = '" . e($shipping_method['id']) . "',
                shipping_method_code = '" . e($shipping_method['code']) . "',
                zone_id = '" . e($zone['id']) . "',
                complete = '$complete'
            WHERE id = '" . e($recipient['id']) . "'");

        // If the recipient is complete, then update shipping cost for recipient
        if ($complete) {
            update_shipping_cost_for_ship_to($recipient['id']);
        }

        // If user is logged in and not ghosting, then update address book.
        if (USER_LOGGED_IN and !$ghost) {

            // Try to find an existing recipient in address book
            $address_book_id = db(
                "SELECT id FROM address_book
                WHERE
                    user = '" . e(USER_ID) . "'
                    AND ship_to_name = '" . e($recipient['ship_to_name']) . "'");

            // If recipient is already in the address book, update recipient
            if ($address_book_id) {

                $sql_address_type = "";
                $address_type = $liveform->get($prefix . 'address_type');
                
                // If the visitor selected an address type, then update it. An address type field
                // might not appear on the form, so we don't want to update the address book unless
                // we have an actual value.
                if ($address_type) {
                    $sql_address_type = "address_type = '" . e($address_type) . "',";
                }
                
                db(
                    "UPDATE address_book SET
                        salutation = '" . e($liveform->get($prefix . 'salutation')) . "',
                        first_name = '" . e($liveform->get($prefix . 'first_name')) . "',
                        last_name = '" . e($liveform->get($prefix . 'last_name')) . "',
                        company = '" . e($liveform->get($prefix . 'company')) . "',
                        address_1 = '" . e($liveform->get($prefix . 'address_1')) . "',
                        address_2 = '" . e($liveform->get($prefix . 'address_2')) . "',
                        city = '" . e($liveform->get($prefix . 'city')) . "',
                        state = '" . e($liveform->get($prefix . 'state')) . "',
                        zip_code = '" . e($liveform->get($prefix . 'zip_code')) . "',
                        country = '" . e($liveform->get($prefix . 'country')) . "',
                        $sql_address_type
                        phone_number = '" . e($liveform->get($prefix . 'phone_number')) . "'
                    WHERE id = '" . e($address_book_id) . "'");

            // Otherwise recipient is not already in the address book, so create recipient
            } else {
                db(
                    "INSERT INTO address_book (
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
                        '" . e($recipient['ship_to_name']) . "',
                        '" . e($liveform->get($prefix . 'salutation')) . "',
                        '" . e($liveform->get($prefix . 'first_name')) . "',
                        '" . e($liveform->get($prefix . 'last_name')) . "',
                        '" . e($liveform->get($prefix . 'company')) . "',
                        '" . e($liveform->get($prefix . 'address_1')) . "',
                        '" . e($liveform->get($prefix . 'address_2')) . "',
                        '" . e($liveform->get($prefix . 'city')) . "',
                        '" . e($liveform->get($prefix . 'state')) . "',
                        '" . e($liveform->get($prefix . 'zip_code')) . "',
                        '" . e($liveform->get($prefix . 'country')) . "',
                        '" . e($liveform->get($prefix . 'address_type')) . "',
                        '" . e($liveform->get($prefix . 'phone_number')) . "')");
            }
        }
    }
}

// if the order has not been submitted yet, then forward user back to express order screen
if ($order_submitted == false) {
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_POST['page_id']));
    exit();
    
// else the order has been submitted, so perform validation and then submit order
} else {
    require(dirname(__FILE__) . '/submit_order.php');

    // validate billing and payment information and submit order
    submit_order('express order');
}