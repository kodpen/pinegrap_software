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

$old_order_id = $_GET['id'];

// get order information
$query =
    "SELECT
        user_id,
        status
    FROM orders
    WHERE id = '" . escape($old_order_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if the order was not found, output error
if (mysqli_num_rows($result) == 0) {
    output_error('The order could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);
$order_user_id = $row['user_id'];
$status = $row['status'];

// get user information
$query =
    "SELECT
        user_id,
        user_contact
    FROM user
    WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];
$contact_id = $row['user_contact'];

// if order user id is different than user id, output error
if ($order_user_id != $user_id) {
    output_error('You do not have access to this order. <a href="javascript:history.go(-1)">Go back</a>.');
}

// if the order is incomplete, output error
if ($status == 'incomplete') {
    output_error('The order is incomplete. You may only reorder complete orders. <a href="javascript:history.go(-1)">Go back</a>.');
}

$reference_code = generate_order_reference_code();

$offline_payment_allowed = '0';

// if offline payment is on, and if only on specific orders is off, then set the allow offline payment to 1
if ((ECOMMERCE_OFFLINE_PAYMENT == TRUE) && (ECOMMERCE_OFFLINE_PAYMENT_ONLY_SPECIFIC_ORDERS == FALSE)) {
    $offline_payment_allowed = '1';
}

$query =
    "INSERT INTO orders (
        order_date,
        last_modified_timestamp,
        user_id,
        contact_id,
        reference_code,
        tracking_code,
        utm_source,
        utm_medium,
        utm_campaign,
        utm_term,
        utm_content,
        affiliate_code,
        http_referer,
        ip_address,
        currency_code,
        offline_payment_allowed)
    VALUES (
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP(),
        '$user_id',
        '$contact_id',
        '$reference_code',
        '" . escape(get_tracking_code()) . "',
        '" . e($_SESSION['software']['utm_source']) . "',
        '" . e($_SESSION['software']['utm_medium']) . "',
        '" . e($_SESSION['software']['utm_campaign']) . "',
        '" . e($_SESSION['software']['utm_term']) . "',
        '" . e($_SESSION['software']['utm_content']) . "',
        '" . escape(get_affiliate_code()) . "',
        '" . escape($_SESSION['software']['http_referer']) . "',
        IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0),
        '" . escape(VISITOR_CURRENCY_CODE) . "',
        '" . $offline_payment_allowed . "')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$new_order_id = mysqli_insert_id(db::$con);

$order_switched = false;

// if there is already an active order in this user's session, then take note of that
if ($_SESSION['ecommerce']['order_id']) {
    $order_switched = true;
}

// store order id in session
$_SESSION['ecommerce']['order_id'] = $new_order_id;

$ship_tos = array();

// check if there is at least one unshippable order item
$query =
    "SELECT COUNT(*)
    FROM order_items
    LEFT JOIN products ON order_items.product_id = products.id
    WHERE
        (order_items.order_id = '" . escape($old_order_id) . "')
        AND (order_items.ship_to_id = '0')
        AND (products.id IS NOT NULL)
        AND (order_items.added_by_offer = '0')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);

// if there is at least one unshippable order item, add empty ship to to ship tos array
if ($row[0] > 0) {
    $ship_tos[] = array('id' => '0');
}

// get all ship tos
$query =
    "SELECT
        id,
        ship_to_name
    FROM ship_tos
    WHERE order_id = '" . escape($old_order_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

while($row = mysqli_fetch_assoc($result)) {
    $ship_tos[] = $row;
}

// loop through all ship tos
foreach ($ship_tos as $ship_to) {
    // Get all order items for ship to where the product still exists and is enabled
    // and the order item was not originally added by an offer.
    $query =
        "SELECT
            products.id as product_id,
            products.name as product_name,
            order_items.quantity,
            order_items.price as old_price,
            products.price as new_price,
            products.selection_type
        FROM order_items
        LEFT JOIN products ON order_items.product_id = products.id
        WHERE
            (order_items.order_id = '" . escape($old_order_id) . "')
            AND (order_items.ship_to_id = '" . $ship_to['id'] . "')
            AND (products.id IS NOT NULL)
            AND (products.enabled = '1')
            AND (order_items.added_by_offer = '0')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // add order items to array
    $order_items = array();

    while($row = mysqli_fetch_assoc($result)) {
        $order_items[] = $row;
    }
    
    // if there are order items for this ship to, then continue
    if ($order_items) {
        // if this ship to is a real ship to, then create ship to record for new order
        if ($ship_to['id'] != 0) {
            $query =
                "INSERT INTO ship_tos (
                    order_id,
                    ship_to_name)
                VALUES (
                    '$new_order_id',
                    '" . escape($ship_to['ship_to_name']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $new_ship_to_id = mysqli_insert_id(db::$con);
            
        // else this ship to is not a real ship to, so set ship to id to 0
        } else {
            $new_ship_to_id = 0;
        }
        
        // loop through order items in order to create order item records for new order
        foreach ($order_items as $order_item) {
            // if order item is a donation, then use donation amount from old order
            if ($order_item['selection_type'] == 'donation') {
                $price = $order_item['old_price'];
                
            // else order item is not a donation, so use new product price
            } else {
                $price = $order_item['new_price'];
            }
            
            $query =
                "INSERT INTO order_items (
                    order_id,
                    ship_to_id,
                    product_id,
                    product_name,
                    quantity,
                    price)
                VALUES (
                    '$new_order_id',
                    '$new_ship_to_id',
                    '" . $order_item['product_id'] . "',
                    '" . $order_item['product_name'] . "',
                    '" . $order_item['quantity'] . "',
                    '" . $price . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
}

// if visitor tracking is on, update visitor record with order information, if visitor has not already created a previous order
if (VISITOR_TRACKING == true) {
    $query =
        "UPDATE visitors
        SET
            order_id = '" . $new_order_id . "',
            order_created = '1',
            stop_timestamp = UNIX_TIMESTAMP()
        WHERE
            (id = '" . $_SESSION['software']['visitor_id'] . "')
            AND (order_created = '0')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}

include_once('liveform.class.php');

// if there is a retrieve order next page set, then prepare notice for page
if (ECOMMERCE_RETRIEVE_ORDER_NEXT_PAGE_ID) {
    // get page type for page, in order to determine which liveform to set
    $query = "SELECT page_type FROM page WHERE page_id = '" . escape(ECOMMERCE_RETRIEVE_ORDER_NEXT_PAGE_ID) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $page_type = $row['page_type'];
    
    switch ($page_type) {
        case 'shopping cart':
            $liveform = new liveform('shopping_cart');
            break;
            
        case 'express order':
            $liveform = new liveform('express_order');
            break;
    }
    
    // if a liveform was set, then prepare notice
    if ($liveform) {
        $order_switched_notice = '';
        
        // if orders were switched then prepare to alert user to that fact
        if ($order_switched == true) {
            $order_switched_notice = ' Your previous order has been saved and may be retrieved when needed.';
        }
        
        // prepare confirmation notice for next screen
        $liveform->add_notice('A new order has been created, and it appears below.' . $order_switched_notice);
    }
    
    // send user to next page
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . encode_url_path(get_page_name(ECOMMERCE_RETRIEVE_ORDER_NEXT_PAGE_ID)));
    
// else there is not a retrieve order next page set, so prepare notice for my account screen
} else {
    $liveform = new liveform('my_account');
    
    $order_switched_notice = '';
    
    // if orders were switched then prepare to alert user to that fact
    if ($order_switched == true) {
        $order_switched_notice = ' Your previous order has been saved and may be retrieved when needed.';
    }
    
    // prepare confirmation notice for next screen
    $liveform->add_notice('A new order has been created.' . $order_switched_notice);

    go(get_page_type_url('my account'));
}