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

function get_view_order_screen_content($properties)
{
    $device_type = $properties['device_type'];

    // if the user came from the control panel, then return placeholder content
    if ((isset($_GET['from']) == true) && ($_GET['from'] == 'control_panel')) {
        return '<p class="software_notice">Order information will be displayed here when this page is linked to from a My Account Page Type.</p>';
    }
    
    $order_id = $_GET['id'];
    
    // get order information
    $query =
        "SELECT
            orders.billing_salutation,
            orders.billing_first_name,
            orders.billing_last_name,
            orders.billing_company,
            orders.billing_address_1,
            orders.billing_address_2,
            orders.billing_city,
            orders.billing_state,
            orders.billing_zip_code,
            countries.name as billing_country,
            orders.billing_phone_number,
            orders.billing_fax_number,
            orders.billing_email_address,
            orders.payment_method,
            orders.card_type,
            orders.cardholder,
            orders.card_number,
            orders.po_number,
            orders.tax_exempt,
            orders.subtotal,
            orders.discount,
            orders.tax,
            orders.shipping,
            orders.gift_card_discount,
            orders.surcharge,
            orders.payment_installment,
            orders.installment_charges,
            orders.total,
            orders.order_number,
            orders.order_date,
            orders.user_id,
            orders.status,
            orders.reference_code
        FROM orders
        LEFT JOIN countries ON orders.billing_country = countries.code
        WHERE orders.id = '" . escape($order_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if the order was not found, output error
    if (mysqli_num_rows($result) == 0) {
        output_error('The order could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    $row = mysqli_fetch_assoc($result);
    
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
    $payment_method = $row['payment_method'];
    $card_type = $row['card_type'];
    $cardholder = $row['cardholder'];
    $card_number = $row['card_number'];
    $po_number = $row['po_number'];
    $tax_exempt = $row['tax_exempt'];
    $subtotal = $row['subtotal'] / 100;
    $discount = $row['discount'] / 100;
    $tax = $row['tax'] / 100;
    $shipping = $row['shipping'] / 100;
    $gift_card_discount = $row['gift_card_discount'] / 100;
    $surcharge = $row['surcharge'] / 100;
    $payment_installment = $row['payment_installment'];
    $installment_charges = $row['installment_charges'] / 100;
    $total = $row['total'] / 100;
    $order_number = $row['order_number'];
    $order_date = $row['order_date'];
    $order_user_id = $row['user_id'];
    $status = $row['status'];
    $reference_code = $row['reference_code'];
    
    // get user id for user
    $query = "SELECT user_id FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['user_id'];
    
    // if order user id is different than user id, output error
    if ($order_user_id != $user_id) {
        output_error('You do not have access to this order. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    // if order is complete, then prepare to output order number
    if ($status != 'incomplete') {
        $output_order_number_or_reference_code = 'Order Number: <strong>' . $order_number . '</strong>';
        
    // else order is incomplete, so prepare to output reference code
    } else {
        $output_order_number_or_reference_code = 'Reference Code: <strong>' . $reference_code . '</strong>';
    }
    
    // prepare shipped quantity variables
    $shipped_quantities_exist = FALSE;
    $output_shipped_quantity_heading_cell = '';
    
    // the number of shipped quantity columns will either be 0 or 1 and will be used to set colspans
    $number_of_shipped_quantity_columns = 0;
    
    // if the order is complete, then continue to check if there are shipped quantities
    if ($status != 'incomplete') {
        $query =
            "SELECT COUNT(*)
            FROM order_items
            WHERE
                (order_id = '" . escape($order_id) . "')
                AND (show_shipped_quantity = '1')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        
        // if there is at least one order item where shipped quantities will be shown, then set various values for that
        if ($row[0] > 0) {
            $shipped_quantities_exist = TRUE;
            $output_shipped_quantity_heading_cell = '<th style="text-align: left">(Shipped)</th>';
            $number_of_shipped_quantity_columns = 1;
        }
    }
    
    $ship_tos = array();
    
    // check if there is at least one unshippable order item
    $query =
        "SELECT COUNT(*)
        FROM order_items
        WHERE
            (order_id = '" . escape($order_id) . "')
            AND (ship_to_id = '0')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    
    // if there is at least one unshippable order item, add empty ship to to ship tos array
    if ($row[0] > 0) {
        $ship_tos[] = array('id' => '0');
    }
    
    // get all ship tos
    $query =
        "SELECT
            ship_tos.id,
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
            countries.name as country,
            ship_tos.address_verified,
            ship_tos.arrival_date,
            ship_tos.shipping_cost,
            shipping_method_code
        FROM ship_tos
        LEFT JOIN countries ON ship_tos.country = countries.code
        WHERE ship_tos.order_id = '" . escape($order_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // foreach ship to, add ship to to array
    while ($row = mysqli_fetch_assoc($result)) {
        $ship_tos[] = $row;
    }
    
    $output_ship_tos = '';
    
    // loop through all ship tos
    foreach ($ship_tos as $ship_to) {
        // if this shipping address is verified, then convert salutation and country to all uppercase
        if ($ship_to['address_verified'] == 1) {
            $ship_to['salutation'] = mb_strtoupper($ship_to['salutation']);
            $ship_to['country'] = mb_strtoupper($ship_to['country']);
        }
        
        // get all order items in order for this ship to
        $query =
            "SELECT
                order_items.id,
                order_items.product_name,
                order_items.quantity,
                order_items.price,
                order_items.tax,
                order_items.recurring_payment_period,
                order_items.recurring_number_of_payments,
                order_items.recurring_start_date,
                order_items.show_shipped_quantity,
                order_items.shipped_quantity,
                order_items.calendar_event_id,
                order_items.recurrence_number,
                products.short_description,
                products.recurring_schedule_editable_by_customer
            FROM order_items
            LEFT JOIN products ON order_items.product_id = products.id
            WHERE
                (order_items.order_id = '" . escape($order_id) . "')
                AND (order_items.ship_to_id = '" . $ship_to['id'] . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $order_items = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            $order_items[] = $row;
        }
        
        $output_order_items = '';
        $row_count = 1;
        
        // loop through all order items for this ship to
        foreach ($order_items as $order_item) {
            $output_shipped_quantity_cell = '';
            
            // if shipped quantities exist in this order, then output shipped quantity cell
            if ($shipped_quantities_exist == TRUE) {
                // if this order item has a shipped quantity, then output it
                if ($order_item['show_shipped_quantity'] == 1) {
                    $output_shipped_quantity_cell = '<td class="mobile_left" style="vertical-align: top; text-align: center; white-space: nowrap; margin-left: .5em">(' . number_format($order_item['shipped_quantity']) . ')</td>';
                    
                // else this order item does not have a shipped quantity, so output empty cell
                } else {
                    $output_shipped_quantity_cell = '<td class="mobile_left" style="vertical-align: top; text-align: right; white-space: nowrap; margin-left: .5em"></td>';
                }
            }
            
            $order_item['price'] = $order_item['price'] / 100;
            $order_item['tax'] = $order_item['tax'] / 100;
            
            $order_item['total_price'] = $order_item['price'] * $order_item['quantity'];
            $order_item['total_tax'] = $order_item['tax'] * $order_item['quantity'];

            $output_description = h($order_item['short_description']);

            // if this was a reservation product, append the specific reservation time purchased to the short description
            if ((CALENDARS == TRUE) && ($order_item['calendar_event_id'] != 0)) {
                    $calendar_event = get_calendar_event($order_item['calendar_event_id'], $order_item['recurrence_number']);
                    $output_description .= $calendar_event['date_and_time_range'];
            }
            
            // if order is not complete, then add price and tax to subtotal and grand tax
            if ($status == 'incomplete') {
                $subtotal += $order_item['total_price'];
                $tax += $order_item['total_tax'];
            }
            
            // assume that we don't need to output a recurring schedule fieldset, until we find out otherwise
            $output_recurring_schedule_fieldset = '';
            
            // if the product is a recurring product and the recurring schedule is editable by the customer, then output recurring schedule fieldset
            if (($order_item['recurring_payment_period'] != '') && ($order_item['recurring_schedule_editable_by_customer'] == 1)) {
                // if the number of payments is set to 0, then change value to [no limit]
                if ($order_item['recurring_number_of_payments'] == 0) {
                    $output_recurring_number_of_payments = '[no limit]';
                    
                // else the number of payments is greater than 0, so show value
                } else {
                    $output_recurring_number_of_payments = number_format($order_item['recurring_number_of_payments']);
                }
                
                $output_recurring_schedule_fieldset =
                    '<fieldset class="software_fieldset">
                        <legend class="software_legend">Payment Schedule</legend>
                            <table>
                                <tr>
                                    <td>Frequency:</td>
                                    <td>' . $order_item['recurring_payment_period'] . '</td>
                                </tr>
                                <tr>
                                    <td>Number of Payments:</td>
                                    <td>' . $output_recurring_number_of_payments . '</td>
                                </tr>
                                <tr>
                                    <td>Start Date:</td>
                                    <td>' . get_absolute_time(array('timestamp' => strtotime($order_item['recurring_start_date']), 'type' => 'date', 'size' => 'long')) . '</td>
                                </tr>
                            </table>
                    </fieldset>';
            }

            $output_gift_cards = '';
            
            // get maximum quantity number, so we can determine how many gift cards there are for this order item
            $query = "SELECT MAX(quantity_number) as number_of_gift_cards FROM order_item_gift_cards WHERE order_item_id = '" . $order_item['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $number_of_gift_cards = $row['number_of_gift_cards'];
            
            // if there is a gift card for this order item, then prepare to output gift card
            if ($number_of_gift_cards > 0) {
                // create loop in order to output all forms
                for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
                    $output_legend_content = 'Gift Card';
                    
                    // if number of gift cards is greater than 1, then add quantity number to legend
                    if ($number_of_gift_cards > 1) {
                        $output_legend_content .= ' (' . $quantity_number . ' of ' . $number_of_gift_cards . ')';
                    }
                    
                    $output_legend = '';
                    
                    // if the legend content is not blank, then output a legend
                    if ($output_legend_content != '') {
                        $output_legend = '<legend class="software_legend">' . $output_legend_content . '</legend>';
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
                            (order_item_id = '" . $order_item['id'] . "')
                            AND (quantity_number = '" . $quantity_number . "')");

                    $output_delivery_date = '';

                    if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                        $output_delivery_date = 'Immediate';

                    } else {
                        $output_delivery_date = get_absolute_time(array('timestamp' => strtotime($order_item_gift_card['delivery_date']), 'type' => 'date', 'size' => 'long'));
                    }
                    
                    $output_gift_cards .=
                        '<fieldset class="software_fieldset" style="margin-bottom: 1em">
                            ' . $output_legend . '
                            <table>
                                <tr>
                                    <td>Amount:</td>
                                    <td>' . BASE_CURRENCY_SYMBOL . number_format($order_item['price'], 2, '.', ',') . '</td>
                                </tr>
                                <tr>
                                    <td>Recipient Email:</td>
                                    <td>' . h($order_item_gift_card['recipient_email_address']) . '</td>
                                </tr>
                                <tr>
                                    <td>From Name:</td>
                                    <td>' . h($order_item_gift_card['from_name']) . '</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top">Message:</td>
                                    <td>' . nl2br(h($order_item_gift_card['message'])) . '</td>
                                </tr>
                                <tr>
                                    <td>Delivery Date:</td>
                                    <td>' . $output_delivery_date . '</td>
                                </tr>
                            </table>
                        </fieldset>';
                }
            }
            
            // assume that there is not a form to output until we find out otherwse
            $output_forms = '';
            
            // get maximum quantity number, so we can determine how many product forms there are for this order item
            $query = "SELECT MAX(quantity_number) as number_of_forms FROM form_data WHERE order_item_id = '" . $order_item['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $number_of_forms = $row['number_of_forms'];
            
            // if there is a form for this order item, then prepare to output form
            if ($number_of_forms > 0) {
                // create loop in order to output all forms
                for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                    $output_legend_content = 'Form';
                    
                    // if number of forms is greater than 1, then add quantity number to legend
                    if ($number_of_forms > 1) {
                        $output_legend_content .= ' (' . $quantity_number . ' of ' . $number_of_forms . ')';
                    }
                    
                    $output_legend = '';
                    
                    // if the legend content is not blank, then output a legend
                    if ($output_legend_content != '') {
                        $output_legend = '<legend class="software_legend">' . $output_legend_content . '</legend>';
                    }
                    
                    $output_forms .=
                        '<fieldset class="software_fieldset" style="margin-bottom: 1em">
                            ' . $output_legend . '
                                <table>
                                    ' . get_submitted_product_form_content_without_form_fields($order_item['id'], $quantity_number, 'frontend') . '
                                </table>
                        </fieldset>';
                }
            }
            
            $output_recurring_schedule_row = '';
            
            // if there is a recurring schedule fieldset to output, then prepare row
            if ($output_recurring_schedule_fieldset != '') {
                $output_recurring_schedule_row =
                     '<tr class="products data row_' . ($row_count % 2) . '">
                        <td class="mobile_hide">&nbsp;</td>
                        <td colspan="' . (4 + $number_of_shipped_quantity_columns) . '">
                            ' . $output_recurring_schedule_fieldset . '
                        </td>
                    </tr>';
            }

            $output_gift_card_row = '';
            
            // if there is a gift card to output, then prepare row
            if ($output_gift_cards != '') {
                $output_gift_card_row =
                     '<tr class="products data row_' . ($row_count % 2) . '">
                        <td class="mobile_hide">&nbsp;</td>
                        <td colspan="' . (4 + $number_of_shipped_quantity_columns) . '">
                            ' . $output_gift_cards . '
                        </td>
                    </tr>';
            }
            
            $output_form_row = '';
            
            // if there is a form to output, then prepare form row
            if ($output_forms != '') {
                $output_form_row =
                     '<tr class="products data row_' . ($row_count % 2) . '">
                        <td class="mobile_hide">&nbsp;</td>
                        <td colspan="' . (4 + $number_of_shipped_quantity_columns) . '">
                            ' . $output_forms . '
                        </td>
                    </tr>';
            }

            $output_order_items .=
                 '<tr class="products data row_' . ($row_count % 2) . '">
                    <td class="mobile_left" style="vertical-align: top">' . h($order_item['product_name']) . '</td>
                    <td class="mobile_right mobile_width" style="vertical-align: top">' . $output_description . '</td>
                    <td class="mobile_left" style="vertical-align: top; margin-right: .5em">' . $order_item['quantity'] . '</td>';

            if ($shipped_quantities_exist == TRUE) {
                $output_order_items .= $output_shipped_quantity_cell;
            }
            
            $output_order_items .=
                    '<td class="mobile_left" style="vertical-align: top; text-align: right">' . prepare_amount($order_item['price']) . '</td>
                    <td class="mobile_right" style="vertical-align: top; text-align: right">' . prepare_amount($order_item['total_price']) . '</td>
                </tr>
                ' . $output_recurring_schedule_row . '
                ' . $output_gift_card_row . '
                ' . $output_form_row;

            $row_count++;
        }

        // if there is at least one order item for this ship to, then output header and order item information
        if ($output_order_items) {
            // if this ship to is a real ship to, then add header with ship to label
            if ($ship_to['id'] != 0) {
                $output_ship_to_name = '';

                // If multi-recipient shipping is enabled, then output ship to name.
                // We can't output ship to name for single-recipient because we don't know if it is being sent to "myself" or someone else.
                if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                    $output_ship_to_name = ' <span class="software_highlight">' . h($ship_to['ship_to_name']) . '</span>';
                }

                $output_shipping_tracking_numbers = '';
                
                // if the order is complete, then get shipping tracking numbers for this ship to if they exist
                if ($status != 'incomplete') {
                    $query =
                        "SELECT number
                        FROM shipping_tracking_numbers
                        WHERE ship_to_id = '" . $ship_to['id'] . "'
                        ORDER BY id ASC";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $shipping_tracking_numbers = mysqli_fetch_items($result);
                    
                    // if there is at least one shipping tracking number, then output them
                    if (count($shipping_tracking_numbers) > 0) {
                        $plural_suffix = '';
                        
                        // if there is more than one number, then output plural suffix
                        if (count($shipping_tracking_numbers) > 1) {
                            $plural_suffix = 's';
                        }
                        
                        $output_shipping_tracking_numbers .= '<div style="margin-bottom: .5em">Tracking Number' . $plural_suffix . ': ';
                        
                        // loop through the shipping tracking numbers in order to output them
                        foreach ($shipping_tracking_numbers as $key => $shipping_tracking_number) {
                            // if this is not the first shipping tracking number then add a comma and space for separation
                            if ($key != 0) {
                                $output_shipping_tracking_numbers .= ', ';
                            }
                            
                            $shipping_tracking_url = get_shipping_tracking_url($shipping_tracking_number['number'],$ship_to['shipping_method_code']);
                            
                            // if a shipping tracking url was found, then output link
                            if ($shipping_tracking_url != '') {
                                $output_shipping_tracking_numbers .= '<a href="' . h($shipping_tracking_url) . '" target="_blank">' . h($shipping_tracking_number['number']) . '</a>';
                                
                            } else {
                                $output_shipping_tracking_numbers .= h($shipping_tracking_number['number']);
                            }
                        }
                        
                        $output_shipping_tracking_numbers .= '</div>';
                    }
                }
                
                $ship_to['address'] = '';
                
                // if there is a salutation and a last name, then add salutation to address
                if (($ship_to['salutation'] != '') && ($ship_to['last_name'] != '')) {
                    $ship_to['address'] .= h($ship_to['salutation']);
                }
                
                // if there is a first name, then add it to address
                if ($ship_to['first_name'] != '') {
                    // if the address is not blank, then add a space
                    if ($ship_to['address'] != '') {
                        $ship_to['address'] .= ' ';
                    }
                    
                    $ship_to['address'] .= h($ship_to['first_name']);
                }
                
                // if there is a last name, then add it to address
                if ($ship_to['last_name'] != '') {
                    // if the address is not blank, then add a space
                    if ($ship_to['address'] != '') {
                        $ship_to['address'] .= ' ';
                    }
                    
                    $ship_to['address'] .= h($ship_to['last_name']);
                }
                
                // if the address is not blank, then add a line break
                if ($ship_to['address'] != '') {
                    $ship_to['address'] .= '<br />';
                }
                
                if ($ship_to['company']) {
                   $ship_to['address'] .= h($ship_to['company']) . ', ';
                }
                
                $ship_to['address'] .= h($ship_to['address_1']);
                
                if ($ship_to['address_2']) {
                   if ($ship_to['address']) {
                       $ship_to['address'] .= ', ';
                   }
                   
                   $ship_to['address'] .= h($ship_to['address_2']);
                }
                
                if ($ship_to['city']) {
                   if ($ship_to['address']) {
                       $ship_to['address'] .= ', ';
                   }
                   
                   $ship_to['address'] .= h($ship_to['city']);
                }
                
                if ($ship_to['state']) {
                   if ($ship_to['address']) {
                       $ship_to['address'] .= ', ';
                   }
                   
                   $ship_to['address'] .= h($ship_to['state']);
                }
                
                if ($ship_to['zip_code']) {
                   if ($ship_to['address']) {
                       $ship_to['address'] .= ', ';
                   }
                   
                   $ship_to['address'] .= h($ship_to['zip_code']);
                }
                
                if ($ship_to['country']) {
                   if ($ship_to['address']) {
                       $ship_to['address'] .= ', ';
                   }
                   
                   $ship_to['address'] .= h($ship_to['country']);
                }
                
                $output_address = '';
                
                if ($ship_to['address']) {
                    $output_address = '<div>' . $ship_to['address'] . '</div>';
                }
                
                $output_ship_tos .=
                    '<tr class="ship_tos">
                        <td colspan="' . (5 + $number_of_shipped_quantity_columns) . '">
                            <div class="heading">Ship to' . $output_ship_to_name . '</div>
                            <div class="data">
                                ' . $output_shipping_tracking_numbers . '
                                ' . $output_address . '
                                ' . get_submitted_form_content_without_form_fields(array('type' => 'custom_shipping_form', 'ship_to_id' => $ship_to['id'])) . '
                            </div>
                        </td>
                    </tr>';
            }
            
            $output_shipping = '';
            
            // if this is a real ship to, output shipping row
            if ($ship_to['id'] != 0) {
                $ship_to['shipping_cost'] = $ship_to['shipping_cost'] / 100;
                
                // if order is not complete, then add shipping to shipping total
                if ($status == 'incomplete') {
                    $shipping += $ship_to['shipping_cost'];
                }
                
                $output_shipping =
                    '<tr class="ship_tos data">
                        <td class="mobile_hide" style="vertical-align: top">&nbsp;</td>
                        <td class="mobile_left" style="vertical-align: top">Shipping</td>
                        <td class="mobile_hide" colspan="' . (2 + $number_of_shipped_quantity_columns) . '" style="vertical-align: top">&nbsp;</td>
                        <td class="mobile_right" style="vertical-align: top; text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($ship_to['shipping_cost'], 2, '.', ',') . '</td>
                    </tr>';
            }
            
            // prepare output for this ship to
            $output_ship_tos .=
                '<tr class="products heading mobile_hide" style="border: none">
                    <th class="heading_item" style="text-align: left">Item</th>
                    <th class="heading_description" style="text-align: left">Description</th>
                    <th class="heading_selection" style="text-align: left">Qty</th>
                    ' . $output_shipped_quantity_heading_cell . '
                    <th class="heading_price" style="text-align: right">Price</th>
                    <th class="heading_amount" style="text-align: right">Amount</th>
                </tr>
                ' . $output_order_items . '
                ' . $output_shipping . '
                <tr class="ship_tos data">
                    <td colspan="' . (5 + $number_of_shipped_quantity_columns) . '">&nbsp;</td>
                </tr>';
        }
    }
    
    $output_discount = '';
    
    // if there is a discount, then prepare to output discount row
    if ($discount) {
        $output_discount =
                '<tr class="order_totals data">
                    <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Discount:</td>
                    <td class="mobile_right" style="text-align: right; white-space: nowrap">-' . BASE_CURRENCY_SYMBOL . number_format($discount, 2, '.', ',') . '</td>
                </tr>';
    }
    
    $output_tax = '';

    // If the tax is negative then set it to zero.  The tax might be negative
    // if there is an offer that discounts the order or if there are
    // negative price products.  We don't want to allow a negative tax though.
    if ($tax < 0) {
        $tax = 0;
    }
    
    // if there is tax, then prepare to output tax row
    if ($tax) {
        $output_tax =
            '<tr class="order_totals data">
                <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Tax:</td>
                <td class="mobile_right" style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($tax, 2, '.', ',') . '</td>
            </tr>';
    }
    
    $output_shipping = '';
    
    // if there is a shipping charge, then prepare to output shipping charge row
    if ($shipping) {
        $output_shipping =
            '<tr class="order_totals data">
                <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Shipping:</td>
                <td class="mobile_right" style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($shipping, 2, '.', ',') . '</td>
            </tr>';
    }
    
    $output_gift_card_discount = '';
    
    // if the order is complete and there is a gift card discount, then prepare to output it
    // we might want to eventually show gift card information for incomplete orders also
    if (($status != 'incomplete') && ($gift_card_discount > 0)) {
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
        
        $output_gift_card_discount =
            '<tr class="order_totals data">
                <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Gift Card' . $output_gift_card_label_plural_suffix . ':</td>
                <td class="mobile_right" style="text-align: right; white-space: nowrap">-' . BASE_CURRENCY_SYMBOL . number_format($gift_card_discount, 2, '.', ',') . '</td>
            </tr>';
    }

    $output_surcharge = '';

    // If there is a credit card surcharge, then output row for it.
    if ($surcharge > 0) {
        $output_surcharge =
            '<tr class="order_totals data">
                <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Surcharge:</td>
                <td class="mobile_right" style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($surcharge, 2, '.', ',') . '</td>
            </tr>';
    }
    //if there is installment charge and payment installment((1) is no installment)
    $output_number_of_installment = '';
    $output_installment_charges = '';
    if(($installment_charges != 0)&&($payment_installment >= 2)){
        $output_number_of_installment = 
        '<tr class="order_totals data">
            <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Number of Installments:</td>
            <td class="mobile_right" style="text-align: right">' . $payment_installment . '</td>
        </tr>';
        $output_installment_charges =
            '<tr class="order_totals data">
                <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Installment Charge:</td>
                <td class="mobile_right" style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($installment_charges, 2, '.', ',') . '</td>
            </tr>';
    }

    // if order is incomplete, then update total
    if ($status == 'incomplete') {
        $total = $subtotal + $tax + $shipping;
    }
    
    // prepare billing information
    $output_billing_information = '';
    
    // if billing information has been completed, then prepare to output billing information
    if ($billing_first_name) {
        $output_billing_information =
            '<div class="billing heading">Billing Information</div>
            <div class="billing data" style="margin-bottom: 15px">';
        
        if ($billing_salutation) {
            $output_billing_information .= h($billing_salutation) . ' ' . h($billing_first_name) . ' ' . h($billing_last_name) . '<br />';
        } else {
            $output_billing_information .= h($billing_first_name) . ' ' . h($billing_last_name) . '<br />';
        }
        
        if ($billing_company) {
            $output_billing_information .= h($billing_company) . '<br />';
        }
        
        $output_billing_information .= h($billing_address_1) . '<br />';
        
        if ($billing_address_2) {
            $output_billing_information .= h($billing_address_2) . '<br />';
        }

        if ($billing_city) {
            $output_billing_information .= h($billing_city) . ', ';
        }

        $output_billing_information .= h($billing_state) . ' ' . h($billing_zip_code) . '<br />
            ' . h($billing_country) . '<br />';

        if ($billing_phone_number) {
            $output_billing_information .= 'Phone: ' . h($billing_phone_number) . '<br />';
        }

        if ($billing_fax_number) {
            $output_billing_information .= 'Fax: ' . h($billing_fax_number) . '<br />';
        }
        
        $output_billing_information .= h($billing_email_address) . '<br />';
        
        if ($po_number) {
            $output_billing_information .= 'PO Number: ' . h($po_number) . '<br />';
        }
        
        // if tax-exempt was checked
        if ($tax_exempt) {
            $output_billing_information .= 'Tax-Exempt';
        }

        $output_billing_information .= get_submitted_form_content_without_form_fields(array('type' => 'custom_billing_form', 'order_id' => $order_id));
        
        $output_billing_information .= '</div>';
    }

    $output_applied_gift_cards = '';
    
    // if the order is complete and there is a gift card discount and there is at least one applied gift card, then prepare to output applied gift cards
    // this double check is not redundant, because there can be a situation where there is a discount with no gift cards if there was an error when the gift card transaction was submitted
    if (($status != 'incomplete') && ($gift_card_discount > 0)  && (count($applied_gift_cards) > 0)) {
        $output_applied_gift_cards =
            '<div class="applied_gift_cards" style="margin-bottom: 1em">
                <div class="heading">Applied Gift Cards</div>
                <div class="data">
                <ul style="margin-top: 0em">';
        
        // loop through applied gift cards in order to prepare to output them
        foreach ($applied_gift_cards as $applied_gift_card) {
            if ($applied_gift_card['givex'] == 0) {
                $protected_gift_card_code = protect_gift_card_code($applied_gift_card['code']);
            } else {
                $protected_gift_card_code = protect_givex_gift_card_code($applied_gift_card['code']);
            }
            
            $output_applied_gift_cards .= '<li>' . $protected_gift_card_code . ' (Remaining Balance: ' . BASE_CURRENCY_SYMBOL . number_format($applied_gift_card['new_balance'] / 100, 2, '.', ',') . ')</li>';
        }
        
        $output_applied_gift_cards .=
            '   </ul></div>
            </div>';
    }
    
    $output_payment_information = '';
    $output_primary_button = '';
    $output_delete_button = '';
    
    // if order is complete
    if ($status != 'incomplete') {
        // if there was a payment method, then prepare to output payment information
        if ($payment_method != '') {
            $output_credit_debit_card_information = '';
            
            // if Credit/Debit Card payment method was selected, then prepare to output values for that
            if ($payment_method == 'Credit/Debit Card') {
                // if the credit card number is not already protected, then protect card number
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
                
                $output_credit_debit_card_information =
                    'Card Type: ' . $card_type . '<br />
                    Card Number: ' . $card_number . '<br />';
            }
            
            $output_payment_information =
                '<div class="payment heading">Payment Information</div>
                <div class="payment data" style="margin-bottom: 15px">
                    Payment Method: ' . $payment_method . '<br />
                    ' . $output_credit_debit_card_information . '
                </div>';
        }
        
        $output_primary_button = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/order_history_reorder.php?id=' . $order_id . get_token_query_string_field() . '" class="software_button_primary">Reorder</a>&nbsp;&nbsp;&nbsp;';
    
    // else order is incomplete
    } else {
        // if this incomplete order is not the active order, then prepare to output retrieve button
        if ($order_id != $_SESSION['ecommerce']['order_id']) {
            $output_primary_button = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/order_history_retrieve_order.php?id=' . $order_id . get_token_query_string_field() . '" class="software_button_primary">Retrieve</a>&nbsp;&nbsp;&nbsp;';
        }
        
        $output_delete_button = '&nbsp;&nbsp;&nbsp;<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/order_history_delete_order.php?id=' . $order_id . get_token_query_string_field() . '" class="software_button_secondary delete_button" onclick="return confirm(\'The order will be deleted.\')">Delete</a>';
    }
    
    return
        '<div class="order data" style="margin-bottom: 15px">' . $output_order_number_or_reference_code . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: ' . get_absolute_time(array('timestamp' => $order_date, 'size' => 'long')) . '</div>
        <table class="products" style="width: 100%">
            ' . $output_ship_tos . '
            <tr class="order_totals">
                <td colspan="' . (5 + $number_of_shipped_quantity_columns) . '">
                    <div class="heading">Order Totals</div>
                </td>
            </tr>
            <tr class="order_totals data">
                <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right">Subtotal:</td>
                <td class="mobile_right" style="text-align: right">' . prepare_amount($subtotal) . '</td>
            </tr>
            ' . $output_discount . '
            ' . $output_tax . '
            ' . $output_shipping . '
            ' . $output_gift_card_discount . '
            ' . $output_surcharge . '
            ' . $output_installment_charges . '
            <tr class="order_totals data">
                <td class="mobile_left" colspan="' . (4 + $number_of_shipped_quantity_columns) . '" style="text-align: right"><strong>Total:</strong></td>
                <td class="mobile_right" style="text-align: right"><strong>' . prepare_amount($total) . '</strong></td>
            </tr>
        </table>
        ' . $output_billing_information . '
        ' . $output_applied_gift_cards . '
        ' . $output_payment_information . '
        <div>' . $output_primary_button . '<a href="' . h(get_page_type_url('my account')) . '" class="software_button_secondary back_button">Back</a>' . $output_delete_button . '</div>';
}
?>