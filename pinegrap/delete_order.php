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

function delete_order($request) {

    $order = db_item(
        "SELECT id, order_number, reference_code FROM orders
        WHERE id = '" . e($request['order']['id']) . "'");

    if (!$order) {
        return error_response('The order could not be found.');
    }

    db("DELETE FROM orders WHERE id = '" . e($order['id']) . "'");
    db("DELETE FROM order_items WHERE order_id = '" . e($order['id']) . "'");
    db("DELETE FROM ship_tos WHERE order_id = '" . e($order['id']) . "'");
    db("DELETE FROM order_item_gift_cards WHERE order_id = '" . e($order['id']) . "'");
    db("DELETE FROM applied_gift_cards WHERE order_id = '" . e($order['id']) . "'");
    db("DELETE FROM form_data WHERE (order_id = '" . e($order['id']) . "') AND (order_id != '0')");
    db("DELETE FROM shipping_tracking_numbers WHERE order_id = '" . e($order['id']) . "'");

    // Cancel order abandoned auto campaign for this order, if one exists.
    db(
        "UPDATE email_campaigns
        SET
            email_campaigns.status = 'cancelled',
            email_campaigns.last_modified_user_id = '',
            email_campaigns.last_modified_timestamp = UNIX_TIMESTAMP()
        WHERE
            (email_campaigns.action = 'order_abandoned')
            AND
            (
                (email_campaigns.status = 'ready')
                OR (email_campaigns.status = 'paused')
            )
            AND (email_campaigns.order_id = '" . e($order['id']) . "')");

    // If the order that is being deleted is the active order then remove active order from session
    if ($order['id'] == $_SESSION['ecommerce']['order_id']) {
        unset($_SESSION['ecommerce']['order_id']);
    }

    if ($order['order_number']) {
        $log_code = $order['order_number'];
    } else {
        $log_code = $order['reference_code'];
    }

    log_activity('Order (' . $log_code . ') was deleted.');

    return array(
        'status' => 'success',
        'order' => $order);
}