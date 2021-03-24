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

$order_id = $_GET['id'];

// get order information
$query =
    "SELECT
        user_id,
        status
    FROM orders
    WHERE id = '" . escape($order_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if the order was not found, output error
if (mysqli_num_rows($result) == 0) {
    output_error('The order could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);
$order_user_id = $row['user_id'];
$status = $row['status'];

// get user id for user
$query = "SELECT user_id FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];

// if order user id is different than user id, output error
if ($order_user_id != $user_id) {
    output_error('You do not have access to this order. <a href="javascript:history.go(-1)">Go back</a>.');
}

// if the order is complete, output error
if ($status != 'incomplete') {
    output_error('The order is complete. You may only retrieve incomplete orders. <a href="javascript:history.go(-1)">Go back</a>.');
}

$order_switched = false;

// if there is already an active order in this user's session, then take note of that
if ($_SESSION['ecommerce']['order_id']) {
    $order_switched = true;
}

// set order in user's session
$_SESSION['ecommerce']['order_id'] = $order_id;

// set the ship tos so that they are incomplete, so that the customer will be required to complete the shipping screens again
// this is the only retrieve order area where we do this.  we do not do this for the retrieve order feature for shopping cart and express order pages.
// this is so someone can complete the ship tos for an order and then send the retrieve order link to someone else and the recipient won't have to complete the shipping
$query = "UPDATE ship_tos SET complete = 0 WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// remove applied gift cards, because they might not be valid anymore
$query = "DELETE FROM applied_gift_cards WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// If this visitor has a tracking code, then update tracking
// code in order.  If this visitor does not have a tracking code,
// then we don't update the tracking code for the order,
// because we don't want to lose a previous tracking code.

$tracking_code = get_tracking_code();

$sql_tracking_code = "";

if ($tracking_code != '') {
    $sql_tracking_code = "tracking_code = '" . e($tracking_code) . "',";
}

$sql_utm = "";

if ($_SESSION['software']['utm_source']) {
    $sql_utm =
        "utm_source = '" . e($_SESSION['software']['utm_source']) . "',
        utm_medium = '" . e($_SESSION['software']['utm_medium']) . "',
        utm_campaign = '" . e($_SESSION['software']['utm_campaign']) . "',
        utm_term = '" . e($_SESSION['software']['utm_term']) . "',
        utm_content = '" . e($_SESSION['software']['utm_content']) . "',";
}

// clear billing information for order, in case the billing information contains information for a different person
// (e.g. the person that setup the order)
// update the IP address for the order because the visitor might have a different IP address now

$query =
    "UPDATE orders
    SET
        billing_salutation = '',
        billing_first_name = '',
        billing_last_name = '',
        billing_company = '',
        billing_address_1 = '',
        billing_address_2 = '',
        billing_city = '',
        billing_state = '',
        billing_zip_code = '',
        billing_country = '',
        billing_address_verified = '0',
        billing_phone_number = '',
        billing_fax_number = '',
        billing_email_address = '',
        billing_complete = '0',
        opt_in = '1',
        referral_source_code = '',
        $sql_tracking_code
        $sql_utm
        ip_address = IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0)
    WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if visitor tracking is on, update visitor record with order information, if visitor has not already created order or retrieved an order
if (VISITOR_TRACKING == true) {
    $query =
        "UPDATE visitors
        SET
            order_id = '" . $_SESSION['ecommerce']['order_id'] . "',
            order_retrieved = '1',
            stop_timestamp = UNIX_TIMESTAMP()
        WHERE
            (id = '" . $_SESSION['software']['visitor_id'] . "')
            AND (order_created = '0')
            AND (order_retrieved = '0')";
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
        $liveform->add_notice('The order has been retrieved, and it appears below.' . $order_switched_notice);
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
    $liveform->add_notice('The order has been retrieved.' . $order_switched_notice);

    go(get_page_type_url('my account'));
}