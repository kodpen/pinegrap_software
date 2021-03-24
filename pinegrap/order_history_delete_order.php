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
    output_error('The order is complete. You may only delete incomplete orders. <a href="javascript:history.go(-1)">Go back</a>.');
}

$order['id'] = $order_id;

require_once(dirname(__FILE__) . '/delete_order.php');

$response = delete_order(array('order' => $order));

if ($response['status'] == 'error') {
    output_error(h($response['message']));
}

// prepare confirmation notice for my account screen
include_once('liveform.class.php');
$my_account = new liveform('my_account');
$my_account->add_notice('The order has been deleted.');

go(get_page_type_url('my account'));