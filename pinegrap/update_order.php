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

// Updates an order (currently just supports updating shipping & tracking for completed order).

function update_order($request) {

    $order = db_item(
        "SELECT id, billing_email_address, order_number, contact_id FROM orders
        WHERE id = '" . e($request['order']['id']) . "'");

    if (!$order) {
        return error_response('The order could not be found.');
    }

    if (!is_array($request['order']['recipients'])) {
        return error_response('There are no recipients in the request.');
    }

    $order['recipients'] = array();

    foreach ($request['order']['recipients'] as $recipient_request) {

        $recipient = db_item(
            "SELECT id, order_id, ship_to_name, arrival_date, ship_date, delivery_date
            FROM ship_tos WHERE id = '" . e($recipient_request['id']) . "'");

        if (!$recipient) {
            return error_response('The recipient could not be found.');
        }

        if ($recipient['order_id'] != $order['id']) {
            return error_response('The recipient does not belong to that order.');
        }

        // If the request contains a shipped override, then remember that. This allows an email to
        // be sent to customer, even if there is no tracking code (foreign address).  This overrides
        // the manual detection below for new tracking numbers or shipped qty, to determine if the
        // recipient was just shipped, so we know if we should send email or not.
        if (isset($recipient_request['shipped'])) {
            $recipient['shipped'] = $recipient_request['shipped'];
        }

        // If there is a ship date or delivery date in the request, then update recipient.
        if (isset($recipient_request['ship_date']) or isset($recipient_request['delivery_date'])) {

            db(
                "UPDATE ship_tos SET
                    ship_date = '" . e($recipient_request['ship_date']) . "',
                    delivery_date = '" . e($recipient_request['delivery_date']) . "'
                WHERE id = '" . e($recipient['id']) . "'");

            $recipient['ship_date'] = $recipient_request['ship_date'];
            $recipient['delivery_date'] = $recipient_request['delivery_date'];
        }

        // If there are tracking numbers in the request, then handle them.
        if (isset($recipient_request['tracking_numbers'])) {

            $sql_delete_exception = '';

            foreach ($recipient_request['tracking_numbers'] as $number) {

                $number = trim($number);

                // If there is no number then skip to next number.
                if ($number == '') {
                    continue;
                }

                // If this tracking number does not already exist, then add it.
                if (!db("SELECT id FROM shipping_tracking_numbers WHERE
                    ship_to_id = '" . e($recipient['id']) . "'
                    AND number = '" . e($number) . "'")
                ) {
                    db(
                        "INSERT INTO shipping_tracking_numbers (
                            order_id,
                            ship_to_id,
                            number)
                        VALUES (
                            '" . e($order['id']) . "',
                            '" . e($recipient['id']) . "',
                            '" . e($number) . "')");

                    // If we do not already know if this recipient was just shipped, now we know,
                    // so remember for later.
                    if (!isset($recipient['shipped'])) {
                        $recipient['shipped'] = true;
                    }
                }

                $recipient['tracking_numbers'][] = $number;

                $sql_delete_exception .= " AND number != '" . e($number) . "'";
            }

            // Delete old tracking numbers that were not included in this request.
            db("DELETE FROM shipping_tracking_numbers WHERE ship_to_id = '" . e($recipient['id']) . "'
                $sql_delete_exception");
        }

        // If there are items in the request, then deal with updating shipped qty for them.
        if (is_array($recipient_request['items'])) {
            
            foreach ($recipient_request['items'] as $item_request) {

                $item = db_item(
                    "SELECT id, ship_to_id AS recipient_id, shipped_quantity FROM order_items
                    WHERE id = '" . e($item_request['id']) . "'");

                if (!$item) {
                    return error_response('The item could not be found (id: ' . $item_request['id'] . ').');
                }

                if ($item['recipient_id'] != $recipient['id']) {
                    return error_response('The item (id: ' . $item_request['id'] . ') does not belong to that recipient (id: ' . $recipient['id'] . ').');
                }

                if (!isset($item_request['shipped_quantity'])) {
                    continue;
                }

                $show_shipped_quantity = 0;
                $shipped_quantity = 0;
                
                // If shipped quantity was not blank, then mark it to be shown.
                if ($item_request['shipped_quantity'] != '') {
                    $show_shipped_quantity = 1;
                    $shipped_quantity = $item_request['shipped_quantity'];
                }
            
                db("UPDATE order_items
                    SET
                        show_shipped_quantity = '" . e($show_shipped_quantity) . "',
                        shipped_quantity = '" . e($shipped_quantity) . "'
                    WHERE id = '" . e($item['id']) . "'");

                // If we do not already know if this recipient was just shipped, and the shipped qty
                // is larger than before, then we know it was just shipped, so remember for later.
                if (!isset($recipient['shipped']) and $shipped_quantity > $item['shipped_quantity']) {
                    $recipient['shipped'] = true;
                }
            }
        }

        // If this recipient was just shipped, then remember that order was just shipped, so we
        // know below whether to create auto email campaigns or not for entire order.
        if ($recipient['shipped']) {
            $order['shipped'] = true;
        }

        // Add this recipient to order array so we can pass data to create_auto_email_campaigns().
        $order['recipients'][] = $recipient;
    }

    db("UPDATE orders SET last_modified_timestamp = UNIX_TIMESTAMP()
        WHERE id = '" . e($order['id']) . "'");

    log_activity('Shipping info for order (' . $order['order_number'] . ') was updated.');

    // If at least one recipient was just shipped, then check if email needs to be sent to customer.
    if ($order['shipped']) {

        // Allow mail-merge fields, like ^^order_number^^ to be put in the subject or body of email.

        $fields = array();

        $fields[] = array(
            'name' => 'order_number',
            'data' => $order['order_number']);

        create_auto_email_campaigns(array(
            'action' => 'order_shipped',
            'contact_id' => $order['contact_id'],
            'email_address' => $order['billing_email_address'],
            'fields' => $fields,
            'data' => array('order' => $order)));
    }

    return array(
        'status' => 'success',
        'order' => $order);
}