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

header("Content-Type: text/xml;charset=utf-8");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");

// Try to find user for login info.
$user = db_item(
    "SELECT
        user_id AS id,
        user_role AS role,
        user_manage_ecommerce AS manage_ecommerce
    FROM user
    WHERE
        (
            (user_username = '" . escape($_REQUEST['username']) . "')
            OR (user_email = '" . escape($_REQUEST['username']) . "')
        )
        AND (user_password = '" . escape(md5($_REQUEST['password'])) . "')");

// If a user was not found (login invalid), then output error.
if ($user['id'] == '') {
    print
        '<?xml version="1.0" standalone="yes" ?>
        <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
            <Error>
                <Code>1</Code>
                <Description>The username or password is incorrect.</Description>
            </Error>
        </ShipWorks>';

    exit;

// Otherwise, a user was found, so if the user has a user role
// and the user does not have access to manage commerce, then output error.
} else if (($user['role'] == 3) && ($user['manage_ecommerce'] != 'yes')) {
    print
        '<?xml version="1.0" standalone="yes" ?>
        <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
            <Error>
                <Code>2</Code>
                <Description>The user does not have access to manage commerce.</Description>
            </Error>
        </ShipWorks>';

    exit;
}

switch ($_REQUEST['action']) {
    case 'getmodule':
        print
            '<?xml version="1.0" standalone="yes" ?>
            <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                <Module>
                    <Platform>liveSite</Platform>
                    <Developer>Camelback Web Architects</Developer>
                    <Capabilities>
                        <DownloadStrategy>ByModifiedTime</DownloadStrategy>
                        <OnlineCustomerID supported="true" dataType="numeric" />
                        <OnlineStatus supported="false" />
                        <OnlineShipmentUpdate supported="true" />
                    </Capabilities>
                </Module>
            </ShipWorks>';

        exit();

        break;

    case 'getstore':
        print
            '<?xml version="1.0" standalone="yes" ?>
            <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                <Store>
                    <Name>' . h(ORGANIZATION_NAME) . '</Name>
                    <Email>' . h(EMAIL_ADDRESS) . '</Email>
                    <State>' . h(ORGANIZATION_STATE) . '</State>
                    <Country>' . h(ORGANIZATION_COUNTRY) . '</Country>
                    <Website>' . URL_SCHEME . h(HOSTNAME_SETTING) . '</Website>
                </Store>
            </ShipWorks>';

        exit();
            
        break;

    case 'getcount':
        $start_timestamp = strtotime($_REQUEST['start'] . 'Z');

        $order_count = db_value(
            "SELECT COUNT(*)
            FROM ship_tos
            LEFT JOIN orders ON ship_tos.order_id = orders.id
            WHERE
                (orders.status != 'incomplete')
                AND (orders.last_modified_timestamp > '$start_timestamp')");

        print
            '<?xml version="1.0" standalone="yes" ?>
            <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                <OrderCount>' . $order_count . '</OrderCount>
            </ShipWorks>';

        exit();
            
        break;

    case 'getorders':
        $start_timestamp = strtotime($_REQUEST['start'] . 'Z');

        // Get all orders after last modified start timestamp in order to output them.
        $orders = db_items(
            "SELECT
                orders.id,
                orders.order_number,
                orders.order_date,
                orders.last_modified_timestamp,
                orders.user_id,
                orders.billing_first_name,
                orders.billing_last_name,
                orders.billing_company,
                orders.billing_address_1,
                orders.billing_address_2,
                orders.billing_city,
                orders.billing_state,
                orders.billing_zip_code,
                orders.billing_country,
                orders.billing_phone_number,
                orders.billing_email_address,
                orders.status,
                contacts.member_id
            FROM orders
            LEFT JOIN contacts ON orders.contact_id = contacts.id
            WHERE
                (orders.status != 'incomplete')
                AND (orders.last_modified_timestamp > '$start_timestamp')
            ORDER BY orders.order_number ASC");

        $output_orders = '';

        // Loop through the orders in order to output them.
        foreach ($orders as $order) {
            // Get ship tos for this order.
            $ship_tos = db_items(
                "SELECT
                    ship_tos.id,
                    ship_tos.first_name,
                    ship_tos.last_name,
                    ship_tos.company,
                    ship_tos.address_1,
                    ship_tos.address_2,
                    ship_tos.city,
                    ship_tos.state,
                    ship_tos.zip_code,
                    ship_tos.country,
                    ship_tos.phone_number,
                    ship_tos.ship_date,
                    shipping_methods.name AS shipping_method_name
                FROM ship_tos
                LEFT JOIN shipping_methods ON ship_tos.shipping_method_id = shipping_methods.id
                WHERE ship_tos.order_id = '" . $order['id'] . "'
                ORDER BY ship_tos.id ASC");

            // If there is at least one ship to for this order, then output order.
            if (count($ship_tos) > 0) {
                $output_customer_id = '';

                // If we know the user that submitted this order, then include that info.
                if ($order['user_id'] != 0) {
                    $output_customer_id = '<CustomerID>' . $order['user_id'] . '</CustomerID>';
                }

                $output_member_id = '';

                // If the customer has a member id, then include that in a note.
                if ($order['member_id'] != '') {
                    $output_member_id = '<Note>' . h(MEMBER_ID_LABEL) . ': ' . h($order['member_id']) . '</Note>';
                }

                $count = 0;

                // Loop through the ship tos in order to output each ship to as a separate order.
                foreach ($ship_tos as $ship_to) {
                    $count++;

                    $output_order_number_postfix = '';

                    // If there is more than one ship to for this order,
                    // then output order number postfix.
                    if (count($ship_tos) > 1) {
                        $output_order_number_postfix = '<OrderNumberPostfix>-' . $count . '</OrderNumberPostfix>';
                    }

                    // If there is a ship date for the ship to, then use that for the order date,
                    // so that the manager can easily manage orders in ShipWorks by ship date.
                    // We pass the order date as a note.
                    if ($ship_to['ship_date'] != '0000-00-00') {
                        $output_order_date = $ship_to['ship_date'] . 'T12:00:00';

                    // Otherwise, there is not a ship date for the ship to, so use order date.
                    } else {
                        $output_order_date = gmdate('Y-m-d\TH:i:s', $order['order_date']);
                    }

                    // Get custom shipping form data for this ship to.
                    $form_data_items = db_items(
                        "SELECT
                            form_data.form_field_id,
                            form_data.data,
                            form_data.name,
                            form_fields.label
                        FROM form_data
                        LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                        WHERE form_data.ship_to_id = '" . $ship_to['id'] . "'
                        ORDER BY form_data.id ASC");

                    $form_fields = array();

                    // Loop through the form data items in order to combine answers
                    // for fields where there are multiple answers.
                    foreach ($form_data_items as $form_data_item) {
                        // If this is the first data item for a field, then add data to array.
                        if (isset($form_fields[$form_data_item['form_field_id']]) == false) {
                            $form_fields[$form_data_item['form_field_id']] = $form_data_item;

                        // Otherwise this is not the first data item for this field,
                        // so add this data value to this field.
                        } else {
                            // If the existing data is not blank, then add a comma and space for separation.
                            if ($form_fields[$form_data_item['form_field_id']]['data'] != '') {
                                $form_fields[$form_data_item['form_field_id']]['data'] .= ', ';
                            }

                            // Add this data value to array.
                            $form_fields[$form_data_item['form_field_id']]['data'] .= $form_data_item['data'];
                        }
                    }

                    $output_notes = '';

                    // Loop through the form fields in order to output a note for each field.
                    foreach ($form_fields as $form_field) {
                        // If the label is not blank, then use it.
                        if ($form_field['label'] != '') {
                            $output_label = h($form_field['label']) . ' ';

                        // Otherwise the label is blank (e.g. form field has been deleted since order was submitted)
                        // so use name for the label.
                        } else {
                            $output_label = h($form_field['name']) . ': ';
                        }

                        $output_notes .= '<Note public="true">' . $output_label . h($form_field['data']) . '</Note>';
                    }

                    // Get order items for this ship.
                    $order_items = db_items(
                        "SELECT
                            order_items.id,
                            order_items.product_id,
                            order_items.product_name,
                            order_items.quantity,
                            order_items.price,
                            products.short_description,
                            products.weight
                        FROM order_items
                        LEFT JOIN products ON order_items.product_id = products.id
                        WHERE order_items.ship_to_id = '" . $ship_to['id'] . "'
                        ORDER BY order_items.id ASC");

                    $output_items = '';

                    // Look through the order items in order to output them.
                    foreach ($order_items as $order_item) {

                        // Update weight to be zero instead of blank, so ShipWorks won't error.
                        // The weight might be blank if the product was deleted.
                        if ($order_item['weight'] == '') {
                            $order_item['weight'] = 0;
                        }

                        $output_items .=
                            '<Item>
                                <ItemID>' . $order_item['id'] . '</ItemID>
                                <ProductID>' . $order_item['product_id'] . '</ProductID>
                                <Code>' . h($order_item['product_name']) . '</Code>
                                <SKU>' . h($order_item['product_name']) . '</SKU>
                                <Name>' . h($order_item['short_description']) . '</Name>
                                <Quantity>' . $order_item['quantity'] . '</Quantity>
                                <UnitPrice>0</UnitPrice>
                                <Weight>' . $order_item['weight'] . '</Weight>
                            </Item>';
                    }

                    $output_orders .=
                        '<Order>
                            <OrderNumber>' . $order['order_number'] . '</OrderNumber>
                            ' . $output_order_number_postfix . '
                            <OrderDate>' . $output_order_date . '</OrderDate>
                            <LastModified>' . gmdate('Y-m-d\TH:i:s', $order['last_modified_timestamp']) . '</LastModified>
                            <ShippingMethod>' . h($ship_to['shipping_method_name']) . '</ShippingMethod>
                            ' . $output_customer_id . '
                            <Notes>
                                <Note>Order Date: ' . get_absolute_time(array('timestamp' => $order['order_date'], 'format' => 'plain_text')) . '</Note>
                                ' . $output_member_id . '
                                ' . $output_notes . '
                            </Notes>
                            <ShippingAddress>
                                <FullName>' . h($ship_to['first_name'] . ' ' . $ship_to['last_name']) . '</FullName>
                                <Company>' . h($ship_to['company']) . '</Company>
                                <Street1>' . h($ship_to['address_1']) . '</Street1>
                                <Street2>' . h($ship_to['address_2']) . '</Street2>
                                <City>' . h($ship_to['city']) . '</City>
                                <State>' . h($ship_to['state']) . '</State>
                                <PostalCode>' . h($ship_to['zip_code']) . '</PostalCode>
                                <Country>' . h($ship_to['country']) . '</Country>
                                <Phone>' . h($ship_to['phone_number']) . '</Phone>
                            </ShippingAddress>
                            <BillingAddress>
                                <FullName>' . h($order['billing_first_name'] . ' ' . $order['billing_last_name']) . '</FullName>
                                <Company>' . h($order['billing_company']) . '</Company>
                                <Street1>' . h($order['billing_address_1']) . '</Street1>
                                <Street2>' . h($order['billing_address_2']) . '</Street2>
                                <City>' . h($order['billing_city']) . '</City>
                                <State>' . h($order['billing_state']) . '</State>
                                <PostalCode>' . h($order['billing_zip_code']) . '</PostalCode>
                                <Country>' . h($order['billing_country']) . '</Country>
                                <Phone>' . h($order['billing_phone_number']) . '</Phone>
                                <Email>' . h($order['billing_email_address']) . '</Email>
                            </BillingAddress>
                            <Items>
                                ' . $output_items . '
                            </Items>
                            <Totals></Totals>
                        </Order>';
                }

                // If the status of this order is complete, then update the status to be exported.
                if ($order['status'] == 'complete') {
                    db("UPDATE orders SET status = 'exported' WHERE id = '" . $order['id'] . "'");
                }
            }
        }

        print
            '<?xml version="1.0" standalone="yes" ?>
            <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                <Orders>
                    ' . $output_orders . '
                </Orders>
            </ShipWorks>';

        exit();
            
        break;

    case 'updateshipment':
        $order_number = $_REQUEST['order'];

        // Remember the requested order number, for error messages further below.
        $requested_order_number = $_REQUEST['order'];

        // If there is a dash in the order number, then this is a multi-recipient order,
        // so remove dash and number, to get actual order number.
        if (mb_strpos($order_number, '-') !== false) {
            $order_number_parts = explode('-', $order_number);
            $order_number = $order_number_parts[0];
            $recipient_number = $order_number_parts[1];

            // If the recipient number is not a positive integer, then output error.
            // This check is necessary in order to prevent a database error further below
            // related to the limit claus.
            if (
                (is_numeric($recipient_number) == false)
                || ($recipient_number < 1)
                || ($recipient_number != round($recipient_number))
            ) {
                echo
                    '<?xml version="1.0" standalone="yes" ?>
                    <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                        <Error>
                            <Code>4</Code>
                            <Description>The order number is not valid (' . h($requested_order_number) . '), so the shipment could not be updated.</Description>
                        </Error>
                    </ShipWorks>';

                exit();
            }

        // Otherwise this is a single-recipient order, so set recipient number to 1.
        } else {
            $recipient_number = 1;
        }

        // Try to find an order for the order number.
        $order_id = db_value(
            "SELECT id
            FROM orders
            WHERE
                (order_number = '$order_number')
                AND (status != 'incomplete')");

        // If an order was not found, then output error.
        if (!$order_id) {
            echo
                '<?xml version="1.0" standalone="yes" ?>
                <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                    <Error>
                        <Code>5</Code>
                        <Description>An order could not be found for the order number (' . h($requested_order_number) . '), so the shipment could not be updated.</Description>
                    </Error>
                </ShipWorks>';

            exit();
        }

        $limit_offset = $recipient_number - 1;

        // Try to find a recipient.
        $ship_to_id = db_value(
            "SELECT id
            FROM ship_tos
            WHERE order_id = '$order_id'
            ORDER BY id ASC
            LIMIT $limit_offset,1");

        // If a recipient was not found, then output error.
        if (!$ship_to_id) {
            echo
                '<?xml version="1.0" standalone="yes" ?>
                <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                    <Error>
                        <Code>6</Code>
                        <Description>A recipient could not be found for the order number (' . h($requested_order_number) . '), so the shipment could not be updated.</Description>
                    </Error>
                </ShipWorks>';

            exit();
        }

        // Update shipped quantity for all order items for this recipient
        // to match the quantity ordered.
        db(
            "UPDATE order_items
            SET
                show_shipped_quantity = '1',
                shipped_quantity = quantity
            WHERE ship_to_id = '$ship_to_id'");

        $tracking_number = trim($_REQUEST['tracking']);

        // If the tracking number is not blank, then determine if we should add it.
        if ($tracking_number != '') {
            // If the tracking number has not already been added to this recipient, then add it.
            if (db_value(
                "SELECT COUNT(*)
                FROM shipping_tracking_numbers
                WHERE
                    (ship_to_id = '$ship_to_id')
                    AND (number = '" . escape($tracking_number) . "')") == 0
            ) {
                db(
                    "INSERT INTO shipping_tracking_numbers (
                        order_id,
                        ship_to_id,
                        number)
                    VALUES (
                        '$order_id',
                        '$ship_to_id',
                        '" . escape($tracking_number) . "')");
            }
        }

        // Tell ShipWorks that request was a success.
        echo
            '<?xml version="1.0" standalone="yes" ?>
            <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                <UpdateSuccess/>
            </ShipWorks>';

        exit();
            
        break;

    default:
        print
            '<?xml version="1.0" standalone="yes" ?>
            <ShipWorks moduleVersion="3.0.0" schemaVersion="1.0.0">
                <Error>
                    <Code>3</Code>
                    <Description>Action (' . h($_REQUEST['action']) . ') not supported.</Description>
                </Error>
            </ShipWorks>';

        exit;

        break;
}
?>