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

function get_order_receipt($properties) {

    $page_id = $properties['page_id'];
    $device_type = $properties['device_type'];

    $properties = get_page_type_properties($page_id, 'order receipt');

    $product_description_type = $properties['product_description_type'];

    $layout_type = get_layout_type($page_id);

    $form = new liveform('order_receipt');
    
    // get order info that we will need to use below
    $query =
        "SELECT
            orders.id,
            orders.order_number,
            orders.order_date,
            orders.billing_salutation,
            orders.billing_first_name,
            orders.billing_last_name,
            orders.billing_company,
            orders.billing_address_1,
            orders.billing_address_2,
            orders.billing_city,
            orders.billing_state,
            orders.billing_zip_code,
            countries.name AS billing_country,
            orders.billing_phone_number,
            orders.billing_fax_number,
            orders.billing_email_address,
            orders.custom_field_1,
            orders.custom_field_2,
            orders.po_number,
            orders.tax_exempt,
            orders.discount_offer_id,
            orders.gift_card_discount,
            orders.surcharge,
            orders.payment_installment,
            orders.installment_charges,
            orders.payment_method,
            orders.card_type,
            orders.card_number,
            orders.cardholder
        FROM orders
        LEFT JOIN countries ON orders.billing_country = countries.code
        WHERE
            (orders.id = '" . e($_SESSION['ecommerce']['completed_order_id']) . "')
            AND (orders.status != 'incomplete')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $order_id = $row['id'];

    // If a completed order could not be found, then output error.
    if (!$order_id) {
        return error('Sorry, it appears that you have not completed an order yet.', 404);
    }

    $order_number = $row['order_number'];
    $order_date = $row['order_date'];
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
    $custom_field_1 = $row['custom_field_1'];
    $custom_field_2 = $row['custom_field_2'];
    $po_number = $row['po_number'];
    $tax_exempt = $row['tax_exempt'];
    $discount_offer_id = $row['discount_offer_id'];
    $gift_card_discount = $row['gift_card_discount'] / 100;
    $surcharge = $row['surcharge'] / 100;
    $payment_installment = $row['payment_installment'];
    $installment_charges = $row['installment_charges'] / 100;
    $payment_method = $row['payment_method'];
    $card_type = $row['card_type'];
    $card_number = $row['card_number'];
    $cardholder = $row['cardholder'];

    if ($layout_type == 'system') {

        // set shipping to false until we find out that this is a shipping order
        $shipping = false;
        
        // get all ship tos
        $query = "SELECT DISTINCT ship_to_id FROM order_items WHERE order_id = '$order_id' ORDER BY ship_to_id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        $ship_tos = array();

        // foreach ship to, add ship to to array
        while ($row = mysqli_fetch_assoc($result)) {
            $ship_tos[] = $row['ship_to_id'];
        }
        
        // if there is at least one product in cart
        if ($ship_tos) {
            // set recurring to false before we check all products for recurring
            $recurring_products_exist = false;
            
            // intialize payment periods array that we will use to store data about payment periods for recurring products
            $payment_periods = array(
                'Monthly' => '',
                'Weekly' => '',
                'Every Two Weeks' => '',
                'Twice every Month' => '',
                'Every Four Weeks' => '',
                'Quarterly' => '',
                'Twice every Year' => '',
                'Yearly' => '');
            
            foreach ($payment_periods as $key => $value) {
                $payment_periods[$key] = array(
                    'exists' => false,
                    'subtotal' => 0,
                    'tax' => 0);
            }
            
            $applied_offers = array();
            
            // loop through all ship tos
            foreach ($ship_tos as $ship_to_id) {
                // get ship to information
                $query =
                    "SELECT
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
                        ship_tos.country,
                        ship_tos.address_verified,
                        ship_tos.arrival_date,
                        ship_tos.arrival_date_id,
                        ship_tos.shipping_method_id,
                        shipping_methods.name as shipping_method_name,
                        shipping_methods.description as shipping_method_description,
                        ship_tos.shipping_cost,
                        ship_tos.original_shipping_cost,
                        ship_tos.offer_id
                    FROM ship_tos
                    LEFT JOIN shipping_methods ON shipping_methods.id = ship_tos.shipping_method_id
                    WHERE ship_tos.id = $ship_to_id";
                
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $ship_to_name = $row['ship_to_name'];
                $salutation = $row['salutation'];
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
                $company = $row['company'];
                $address_1 = $row['address_1'];
                $address_2 = $row['address_2'];
                $city = $row['city'];
                $state = $row['state'];
                $zip_code = $row['zip_code'];
                $country = $row['country'];
                $address_verified = $row['address_verified'];
                $arrival_date = $row['arrival_date'];
                $arrival_date_id = $row['arrival_date_id'];
                $shipping_method_id = $row['shipping_method_id'];
                $shipping_method_name = $row['shipping_method_name'];
                $shipping_method_description = $row['shipping_method_description'];
                $shipping_cost = $row['shipping_cost'] / 100;
                $original_shipping_cost = $row['original_shipping_cost'] / 100;
                $ship_to_offer_id = $row['offer_id'];
                
                // get country name
                $query = "SELECT name FROM countries WHERE code = '" . escape($country) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $country = $row['name'];
                
                // if this shipping address is verified, then convert salutation and country to all uppercase
                if ($address_verified == 1) {
                    $salutation = mb_strtoupper($salutation);
                    $country = mb_strtoupper($country);
                }

                // get all order items in order for this ship to
                $query =
                    "SELECT
                        order_items.id,
                        order_items.product_id,
                        order_items.product_name,
                        order_items.quantity,
                        order_items.price,
                        order_items.tax,
                        order_items.offer_id,
                        order_items.discounted_by_offer,
                        order_items.recurring_payment_period,
                        order_items.recurring_number_of_payments,
                        order_items.recurring_start_date,
                        order_items.calendar_event_id,
                        order_items.recurrence_number,
                        products.short_description,
                        products.full_description,
                        products.price as product_price,
                        products.recurring,
                        products.recurring_schedule_editable_by_customer,
                        products.selection_type,
                        products.gift_card,
                        products.form,
                        products.form_name,
                        products.form_label_column_width,
                        products.form_quantity_type
                    FROM order_items
                    LEFT JOIN products ON order_items.product_id = products.id
                    WHERE
                        (order_items.order_id = '$order_id')
                        AND (order_items.ship_to_id = '$ship_to_id')
                    ORDER BY order_items.id";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                $order_items = array();

                // loop through order items in order to add them to array
                while ($row = mysqli_fetch_assoc($result)) {
                    $order_items[] = $row;
                }

                $output_products = '';
                $output_recurring_products = '';

                // initialize variables to determine which types of products are being displayed
                // these variables will be used later to determine column heading labels
                $non_donations_exist_in_non_recurring = false;
                $non_donations_exist_in_recurring = false;
                $donations_exist_in_non_recurring = false;
                $donations_exist_in_recurring = false;
                $row_count = 1;

                // loop through order items
                foreach ($order_items as $order_item) {
                    $order_item_id = $order_item['id'];
                    $product_id = $order_item['product_id'];
                    $name = $order_item['product_name'];
                    $quantity = $order_item['quantity'];
                    $product_price = $order_item['price'] / 100;
                    $product_tax = $order_item['tax'] / 100;
                    $offer_id = $order_item['offer_id'];
                    $discounted_by_offer = $order_item['discounted_by_offer'];
                    $recurring_payment_period = $order_item['recurring_payment_period'];
                    $recurring_number_of_payments = $order_item['recurring_number_of_payments'];
                    $recurring_start_date = $order_item['recurring_start_date'];
                    $calendar_event_id = $order_item['calendar_event_id'];
                    $recurrence_number = $order_item['recurrence_number'];
                    $short_description = $order_item['short_description'];
                    $full_description = $order_item['full_description'];
                    $original_product_price = $order_item['product_price'] / 100;
                    $recurring = $order_item['recurring'];
                    $recurring_schedule_editable_by_customer = $order_item['recurring_schedule_editable_by_customer'];
                    $start = $order_item['start'];
                    $payment_period = $order_item['payment_period'];
                    $selection_type = $order_item['selection_type'];
                    $gift_card = $order_item['gift_card'];
                    $form = $order_item['form'];
                    $form_name = $order_item['form_name'];
                    $form_label_column_width = $order_item['form_label_column_width'];
                    $form_quantity_type = $order_item['form_quantity_type'];

                    // if the product description type for this order receipt page is full description, then use the full description
                    if ($product_description_type == 'full_description') {
                        $output_description = $full_description;

                    // else the product description type is short description, so use the short description
                    } else {
                        $output_description = h($short_description);
                    }
                    
                    // if calendars is enabled and this order item is for a calendar event reservation, then add calendar event name and date and time range to the description
                    if ((CALENDARS == TRUE) && ($calendar_event_id != 0)) {
                        $calendar_event = get_calendar_event($calendar_event_id, $recurrence_number);
                        
                        $output_description .=
                            '<p>
                                ' . h($calendar_event['name']) . '<br />
                                ' . $calendar_event['date_and_time_range'] . '
                            </p>';
                    }
                    
                    // if order item is a donation, do not display quantity
                    if ($selection_type == 'donation') {
                        $output_quantity = '';
                    
                    // else order is not a donation, so output quantity
                    } else {
                        $output_quantity = $quantity;
                    }

                    // if order item is a donation, do not display price
                    if ($selection_type == 'donation') {
                        $output_product_price = '';
                        
                    // else the order item is not a donation, so prepare to output price
                    } else {
                        // assume that the order item is not discounted, until we find out otherwise
                        $discounted = FALSE;

                        // if the order item is discounted, then prepare to show that
                        if ($discounted_by_offer == 1) {
                            $discounted = TRUE;
                        }
                        
                        $output_product_price = prepare_price_for_output($original_product_price * 100, $discounted, $product_price * 100, 'html');
                    }

                    $total_price = $product_price * $quantity;
                    $total_tax = $product_tax * $quantity;
                    
                    // assume that we don't need to output a recurring schedule fieldset, until we find out otherwise
                    $output_recurring_schedule_fieldset = '';
                    
                    // if the product is a recurring product and the recurring schedule is editable by the customer, then output recurring schedule fieldset
                    if (($recurring == 1) && ($recurring_schedule_editable_by_customer == 1)) {
                        // if the number of payments is set to 0, then change value to [no limit]
                        if ($recurring_number_of_payments == 0) {
                            $output_recurring_number_of_payments = '[no limit]';
                            
                        // else the number of payments is greater than 0, so show value
                        } else {
                            $output_recurring_number_of_payments = number_format($recurring_number_of_payments);
                        }
                        
                        // determine if start row should be outputted
                        $output_start_date_row = '';
                        
                        // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then output start date row
                        if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                            $output_start_date_row =
                                '<tr>
                                    <td>Start Date:</td>
                                    <td>' . get_absolute_time(array('timestamp' => strtotime($recurring_start_date), 'type' => 'date', 'size' => 'long')) . '</td>
                                </tr>';
                        }
                        
                        $output_recurring_schedule_fieldset =
                            '<fieldset class="software_fieldset">
                                <legend class="software_legend">Payment Schedule</legend>
                                    <table>
                                        <tr>
                                            <td>Frequency:</td>
                                            <td>' . $recurring_payment_period . '</td>
                                        </tr>
                                        <tr>
                                            <td>Number of Payments:</td>
                                            <td>' . $output_recurring_number_of_payments . '</td>
                                        </tr>
                                        ' . $output_start_date_row . '
                                    </table>
                            </fieldset>';
                    }

                    $output_gift_cards = '';
                    
                    // If this order item is a gift card, then output gift card data.
                    if ($gift_card == 1) {
                        // If the quantity is 100 or less, then set the number of gift cards to the quantity.
                        if ($quantity <= 100) {
                            $number_of_gift_cards = $quantity;
                            
                        // Otherwise the quantity is greater than 100, so set the number of gift cards to 100.
                        // We do this in order to prevent a ton of forms from appearing and causing a slowdown.
                        } else {
                            $number_of_gift_cards = 100;
                        }
                        
                        // Loop through all quantities in order to output data for each gift card.
                        for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
                            $output_legend_quantity_number = '';
                            
                            // If number of gift cards is greater than 1, then add quantity number to legend.
                            if ($number_of_gift_cards > 1) {
                                $output_legend_quantity_number .= ' (' . $quantity_number . ' of ' . $number_of_gift_cards . ')';
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
                                    (order_item_id = '" . $order_item_id . "')
                                    AND (quantity_number = '" . $quantity_number . "')");

                            $output_delivery_date = '';

                            if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                                $output_delivery_date = 'Immediate';

                            } else {
                                $output_delivery_date = get_absolute_time(array('timestamp' => strtotime($order_item_gift_card['delivery_date']), 'type' => 'date', 'size' => 'long'));
                            }
                            
                            $output_gift_cards .=
                                '<fieldset class="software_fieldset" style="margin-bottom: 1em">
                                    <legend class="software_legend">Gift Card' . $output_legend_quantity_number . '</legend>
                                        <table>
                                            <tr>
                                                <td>Amount:</td>
                                                <td><strong>' . prepare_price_for_output($product_price * 100, false, $discounted_price = '', 'html') . '</strong></td>
                                            </tr>
                                            <tr>
                                                <td>Recipient Email:</td>
                                                <td>' . h($order_item_gift_card['recipient_email_address']) . '</td>
                                            </tr>
                                            <tr>
                                                <td>Your Name:</td>
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
                    
                    // if there is a form for this product, then prepare to output form
                    if ($form == 1) {
                        // if there should be one form per quantity, then set the number of forms to the quantity of this order item
                        if ($form_quantity_type == 'One Form per Quantity') {
                            // if the quantity is 100 or less, then set the number of forms to the quantity
                            if ($quantity <= 100) {
                                $number_of_forms = $quantity;
                                
                            // else the quantity is greater than 100, so set the number of forms to 100
                            } else {
                                $number_of_forms = 100;
                            }
                            
                        // else there should be one form per product, so set the number of forms to 1
                        } elseif ($form_quantity_type == 'One Form per Product') {
                            $number_of_forms = 1;
                        }
                        
                        // create loop in order to output all forms
                        for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                            $output_legend_content = '';
                            
                            // if there is a form name, then add form name to legend
                            if ($form_name != '') {
                                $output_legend_content .= h($form_name);
                            }
                            
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
                                            ' . get_submitted_product_form_content_with_form_fields($order_item_id, $quantity_number) . '
                                        </table>
                                </fieldset>';
                        }
                    }
                    
                    // if product is not a recurring product or if start date is less than or equal to the order date and payment gateway is not ClearCommerce, then it is in the non-recurring order
                    if (
                        ($recurring == 0)
                        ||
                        (
                            ($recurring_start_date <= date('Y-m-d', $order_date))
                            && ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce'))
                        )
                    ) {
                        $in_nonrecurring = true;
                        
                    } else {
                        $in_nonrecurring = false;
                    }

                    // if product is in non-recurring order
                    if ($in_nonrecurring) {
                        $subtotal = $subtotal + $total_price;
                        $grand_tax = $grand_tax + $total_tax;
                        
                        $output_recurring_schedule_row = '';
                        
                        // if there is a recurring schedule fieldset to output, then prepare row
                        if ($output_recurring_schedule_fieldset != '') {
                            $output_recurring_schedule_row =
                                '<tr class="products data row_' . ($row_count % 2) . '">
                                    <td class="mobile_hide">&nbsp;</td>
                                    <td colspan="4">
                                        ' . $output_recurring_schedule_fieldset . '
                                    </td>
                                </tr>';
                        }

                        $output_gift_card_row = '';
                        
                        // If there is a gift card to output, then output row for it.
                        if ($output_gift_cards != '') {
                            $output_gift_card_row =
                                 '<tr class="products data row_' . ($row_count % 2) . '">
                                    <td class="mobile_hide">&nbsp;</td>
                                    <td colspan="4">
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
                                    <td colspan="4">
                                        ' . $output_forms . '
                                    </td>
                                </tr>';
                        }

                        $output_products .=
                            '<tr class="products data row_' . ($row_count % 2) . '">
                                <td class="mobile_left" style="vertical-align: top">' . h($name) . '</td>
                                <td class="mobile_right mobile_width" style="vertical-align: top">' . $output_description . '</td>';

                        // if donation product, output empty cells for quantity and price and hide any styling from mobile
                        if ($selection_type == 'donation') {
                            $output_products .=
                                '<td class="mobile_hide"></td>
                                <td class="mobile_hide"></td>';
                        } else {
                            $output_products .=
                                '<td class="mobile_left" style="vertical-align: top; margin-right: .5em">' . $output_quantity . '</td>
                                <td class="mobile_left" style="vertical-align: top; text-align: right; white-space: nowrap; margin-right: .5em">' . $output_product_price . '</td>';
                        }

                        $output_products .=
                                '<td class="mobile_right" style="vertical-align: top; text-align: right; white-space: nowrap">' . prepare_price_for_output($total_price * 100, FALSE, $discounted_price = '', 'html') . '</td>
                            </tr>
                            ' . $output_recurring_schedule_row . '
                            ' . $output_gift_card_row . '
                            ' . $output_form_row;
                            
                        // if order item is a donation
                        if ($selection_type == 'donation') {
                            $donations_exist_in_non_recurring = true;
                        } else {
                            $non_donations_exist_in_non_recurring = true;
                        }
                    }

                    // if product is a recurring product
                    if ($recurring) {
                        $recurring_products_exist = true;
                        
                        $output_recurring_schedule_row = '';
                        $output_gift_card_row = '';
                        $output_form_row = '';
                        
                        // if product is not in non-recurring, then prepare recurring schedule and form rows
                        if ($in_nonrecurring == false) {
                            // if there is a recurring schedule fieldset to output, then prepare row
                            if ($output_recurring_schedule_fieldset != '') {
                                $output_recurring_schedule_row =
                                    '<tr class="products data row_' . ($row_count % 2) . '">
                                        <td class="mobile_hide">&nbsp;</td>
                                        <td colspan="5">
                                            ' . $output_recurring_schedule_fieldset . '
                                        </td>
                                    </tr>';
                            }

                            // If there is a gift card to output, then prepare row.
                            if ($output_gift_cards != '') {
                                $output_gift_card_row =
                                    '<tr class="products data row_' . ($row_count % 2) . '">
                                        <td class="mobile_hide">&nbsp;</td>
                                        <td colspan="5">
                                            ' . $output_gift_cards . '
                                        </td>
                                    </tr>';
                            }
                            
                            // if there is a form to output, then prepare row
                            if ($output_forms != '') {
                                $output_form_row =
                                    '<tr class="products data row_' . ($row_count % 2) . '">
                                        <td class="mobile_hide">&nbsp;</td>
                                        <td colspan="5">
                                            ' . $output_forms . '
                                        </td>
                                    </tr>';
                            }
                        }

                        $output_recurring_products .=
                            '<tr class="products data row_' . ($row_count % 2) . '">
                                <td class="mobile_left" style="vertical-align: top">' . h($name) . '</td>
                                <td class="mobile_right mobile_width" style="vertical-align: top">' . $output_description . '</td>
                                <td class="mobile_left" style="vertical-align: top">' . $recurring_payment_period . '</td>
                                <td class="mobile_left" style="vertical-align: top; text-align: center">' . $output_quantity . '</td>
                                <td class="mobile_right" style="vertical-align: top; text-align: right; white-space: nowrap; margin-left: .5em">' . $output_product_price . '</td>
                                <td class="mobile_right" style="vertical-align: top; text-align: right">' . prepare_price_for_output($total_price * 100, FALSE, $discounted_price = '', 'html') . '</td>
                            </tr>
                            ' . $output_recurring_schedule_row . '
                            ' . $output_gift_card_row . '
                            ' . $output_form_row;
                            
                        // if order item is a donation
                        if ($selection_type == 'donation') {
                            $donations_exist_in_recurring = true;
                        } else {
                            $non_donations_exist_in_recurring = true;
                        }
                        
                        // store information for payment period
                        $payment_periods[$recurring_payment_period]['exists'] = true;
                        $payment_periods[$recurring_payment_period]['subtotal'] += $total_price;
                        $payment_periods[$recurring_payment_period]['tax'] += $total_tax;
                    }
                    
                    // if there is an offer applied to this order item and offer has not already been added to applied offers array,
                    // store this offer as an applied offer
                    if ($offer_id && (in_array($offer_id, $applied_offers) == false)) {
                        $applied_offers[] = $offer_id;
                    }

                    $row_count++;
                }

                // if there is at least one product in non-recurring folder for this ship to, then output header and product information
                if ($output_products) {
                    // if shipping is on and this ship to is a real ship to, then add header with ship to label
                    if ((ECOMMERCE_SHIPPING == true) && ($ship_to_id != 0)) {
                        $output_ship_to_name = '';

                        // If multi-recipient shipping is enabled, then output ship to name.
                        // We can't output ship to name for single-recipient because we don't know if it is being sent to "myself" or someone else.
                        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                            $output_ship_to_name = ' <span class="software_highlight">' . h($ship_to_name) . '</span>';
                        }

                        $address = '';
                        
                        // if there is a salutation and a last name, then add salutation to address
                        if (($salutation != '') && ($last_name != '')) {
                            $address .= h($salutation);
                        }
                        
                        // if there is a first name, then add it to address
                        if ($first_name != '') {
                            // if the address is not blank, then add a space
                            if ($address != '') {
                                $address .= ' ';
                            }
                            
                            $address .= h($first_name);
                        }
                        
                        // if there is a last name, then add it to address
                        if ($last_name != '') {
                            // if the address is not blank, then add a space
                            if ($address != '') {
                                $address .= ' ';
                            }
                            
                            $address .= h($last_name);
                        }
                        
                        // if the address is not blank, then add a line break
                        if ($address != '') {
                            $address .= '<br />';
                        }
                        
                        if ($company) {
                            $address .= h($company) . ', ';
                        }
                        
                        $address .= h($address_1);

                        if ($address_2) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($address_2);
                        }

                        if ($city) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($city);
                        }
                        
                        if ($state) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($state);
                        }

                        if ($zip_code) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($zip_code);
                        }

                        if ($zip_code) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($country);
                        }

                        $output_ship_tos .=
                            '<tr class="ship_tos">
                                <td colspan="5">
                                    <div class="heading">Ship to' . $output_ship_to_name . '</div>
                                    <div class="data">
                                        ' . $address . '
                                        ' . get_submitted_form_content_with_form_fields(array('type' => 'custom_shipping_form', 'ship_to_id' => $ship_to_id)) . '
                                    </div>
                                </td>
                            </tr>';
                    }

                    // if shipping is on and this is a real ship to, output shipping row
                    if ((ECOMMERCE_SHIPPING == true) && ($ship_to_id != 0)) {
                        // set shipping to true because we now know that this is a shipping order
                        $shipping = true;

                        // update grand shipping total
                        $grand_shipping += $shipping_cost;

                        $shipping_description = 'Shipping Method: ' . $shipping_method_name;

                        // get arrival date information
                        $query = "SELECT name, custom FROM arrival_dates WHERE id = '$arrival_date_id'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // if an arrival date was selected, then prepare requested arrival date content
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            
                            $shipping_description .= '; Requested Arrival Date: ';
                            
                            // if selected arrival date had a custom field, use actual date in description
                            if ($row['custom'] == 1) {
                                $shipping_description .= get_absolute_time(array('timestamp' => strtotime($arrival_date), 'type' => 'date', 'size' => 'long'));

                            // else selected arrival date did not have a custom field, so use arrival date name
                            } else {
                                $shipping_description .= h($row['name']);
                            }
                        }

                        $shipping_description .= '; ' . $shipping_method_description;
                        
                        // if there is an offer applied to this ship to, then prepare to output shipping cost in a certain way and add offer to applied offers array if necessary
                        if ($ship_to_offer_id != 0) {
                            $original_price = $original_shipping_cost * 100;
                            $discounted = TRUE;
                            $discounted_price = $shipping_cost * 100;
                            
                            // if the offer has not already been added to the applied offers array, then store this offer as an applied offer
                            if (in_array($ship_to_offer_id, $applied_offers) == false) {
                                $applied_offers[] = $ship_to_offer_id;
                            }
                            
                        // else there is not an offer applies to this ship to, so prepare shipping cost in a different way
                        } else {
                            $original_price = $shipping_cost * 100;
                            $discounted = FALSE;
                            $discounted_price = '';
                        }

                        $output_shipping =
                            '<tr class="ship_tos data">
                                <td class="mobile_hide" style="vertical-align: top">&nbsp;</td>
                                <td class="mobile_left" style="vertical-align: top">' . $shipping_description . '</td>
                                <td class="mobile_hide" style="vertical-align: top">&nbsp;</td>
                                <td class="mobile_hide" style="vertical-align: top">&nbsp;</td>
                                <td class="mobile_right" style="vertical-align: top; text-align: right; white-space: nowrap; margin-left: .5em">' . prepare_price_for_output($original_price, $discounted, $discounted_price, 'html') . '</td>
                            </tr>';
                    }

                    if (($non_donations_exist_in_non_recurring == true) || ($donations_exist_in_non_recurring == false)) {
                        $output_quantity_heading = 'Qty';
                        $output_price_heading = 'Price';
                    } else {
                        $output_quantity_heading = '';
                        $output_price_heading = '';
                    }

                    // prepare output for this ship to
                    $output_ship_tos .=
                        '<tr class="product heading mobile_hide" style="border: none">
                            <th class="heading_item" style="text-align: left">Item</th>
                            <th class="heading_description" style="text-align: left">Description</th>
                            <th class="heading_selection" style="text-align: left">' . $output_quantity_heading . '</th>
                            <th class="heading_price" style="text-align: right">' . $output_price_heading . '</th>
                            <th class="heading_amount" style="text-align: right">Amount</th>
                        </tr>
                        ' . $output_products . '
                        ' . $output_shipping . '
                        <tr class="ship_tos data">
                            <td colspan="5">&nbsp;</td>
                        </tr>';
                }

                // if there is at least one product in recurring folder for this ship to, then output header and product information
                if ($output_recurring_products) {
                    // if shipping is on and this ship to is a real ship to, then add header with ship to label
                    if ((ECOMMERCE_SHIPPING == true) && ($ship_to_id != 0)) {
                        $output_ship_to_name = '';

                        // If multi-recipient shipping is enabled, then output ship to name.
                        // We can't output ship to name for single-recipient because we don't know if it is being sent to "myself" or someone else.
                        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                            $output_ship_to_name = ' <span class="software_highlight">' . h($ship_to_name) . '</span>';
                        }

                        $address = '';
                        
                        // if there is a salutation and a last name, then add salutation to address
                        if (($salutation != '') && ($last_name != '')) {
                            $address .= h($salutation);
                        }
                        
                        // if there is a first name, then add it to address
                        if ($first_name != '') {
                            // if the address is not blank, then add a space
                            if ($address != '') {
                                $address .= ' ';
                            }
                            
                            $address .= h($first_name);
                        }
                        
                        // if there is a last name, then add it to address
                        if ($last_name != '') {
                            // if the address is not blank, then add a space
                            if ($address != '') {
                                $address .= ' ';
                            }
                            
                            $address .= h($last_name);
                        }
                        
                        // if the address is not blank, then add a line break
                        if ($address != '') {
                            $address .= '<br />';
                        }
                        
                        if ($company) {
                            $address .= h($company) . ', ';
                        }
                        
                        $address .= h($address_1);

                        if ($address_2) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($address_2);
                        }

                        if ($city) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($city);
                        }
                        
                        if ($state) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($state);
                        }

                        if ($zip_code) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($zip_code);
                        }

                        if ($zip_code) {
                            if ($address) {
                                $address .= ', ';
                            }
                            
                            $address .= h($country);
                        }

                        $output_recurring_ship_tos .=
                            '<tr class="ship_tos">
                                <td colspan="6">
                                    <div class="heading">Ship to' . $output_ship_to_name . '</div>
                                    <div class="data">
                                        ' . $address . '
                                        ' . get_submitted_form_content_with_form_fields(array('type' => 'custom_shipping_form', 'ship_to_id' => $ship_to_id)) . '
                                    </div>
                                </td>
                            </tr>';
                    }

                    if (($non_donations_exist_in_recurring == true) || ($donations_exist_in_recurring == false)) {
                        $output_quantity_heading = 'Qty';
                        $output_price_heading = 'Price';
                    } else {
                        $output_quantity_heading = '';
                        $output_price_heading = '';
                    }

                    // prepare recurring output for this ship to
                    $output_recurring_ship_tos .=
                        '<tr class="products heading mobile_hide" style="border: none">
                            <th class="heading_item" style="text-align: left">Item</th>
                            <th class="heading_description" style="text-align: left">Description</th>
                            <th class="heading_frequency" style="text-align: left">Frequency</th>
                            <th class="heading_selection" style="text-align: left">' . $output_quantity_heading . '</th>
                            <th class="heading_price" style="text-align: right">' . $output_price_heading . '</th>
                            <th class="heading_amount" style="text-align: right">Amount</th>
                        </tr>
                        ' . $output_recurring_products . '
                        <tr class="ship_tos data">
                            <td colspan="6">&nbsp;</td>
                        </tr>';
                }
            }
        }
        
        // get all unique products that are in order, in order to prepare order receipt messages
        $query =
            "SELECT
               DISTINCT(order_items.product_id),
               products.order_receipt_message
            FROM order_items
            LEFT JOIN products ON order_items.product_id = products.id
            WHERE
                (order_items.order_id = '$order_id')
                AND (products.order_receipt_message != '')
                AND (products.order_receipt_message != '<p />')
            ORDER BY ship_to_id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $output_order_receipt_messages = '';
            
        while ($row = mysqli_fetch_assoc($result)) {
            $order_receipt_message = $row['order_receipt_message'];
            
            $output_order_receipt_messages .=
                '<fieldset style="margin-bottom: 15px" class="software_fieldset">
                        ' . $order_receipt_message . '
                </fieldset>';
        }

        // format subtotal, tax, and total
        $subtotal = $subtotal;

        // start grand total off with just the subtotal (we will add tax and shipping later, if necessary)
        $grand_total = $subtotal;

        // if there is an order discount from an offer, prepare order discount
        if ($_SESSION['ecommerce']['order_discount']) {
            $order_discount = $_SESSION['ecommerce']['order_discount'] / 100;
            
            $grand_total = $subtotal - $order_discount;
            
            if ($grand_total < 0) {
                $grand_total = 0;
            }
            
            $output_discount =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right">Discount:</td>
                        <td class="mobile_right" style="text-align: right">-' . prepare_price_for_output($order_discount * 100, FALSE, $discounted_price = '', 'html') . '</td>
                    </tr>';
        }

        // if tax is on, update grand total and prepare tax row
        if (ECOMMERCE_TAX == true) {
            // if there is an order discount, adjust tax
            if ($order_discount > 0) {
                $grand_tax = $grand_tax - ($grand_tax * ($order_discount / $subtotal));
            }

            // If the tax is negative then set it to zero.  The tax might be negative
            // if there is an offer that discounts the order or if there are
            // negative price products.  We don't want to allow a negative tax though.
            if ($grand_tax < 0) {
                $grand_tax = 0;
            }
            
            $grand_total = $grand_total + $grand_tax;

            $output_grand_tax =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right">Tax:</td>
                        <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($grand_tax * 100, FALSE, $discounted_price = '', 'html') . '</td>
                    </tr>';
        }

        // if there was at least one shipping recipient for this order, output grand shipping total
        if ($shipping == true) {
            // update grand total
            $grand_total = $grand_total + $grand_shipping;

            $output_grand_shipping =
                '<tr class="order_totals data">
                    <td class="mobile_left" colspan="4" style="text-align: right">Shipping:</td>
                    <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($grand_shipping * 100, FALSE, $discounted_price = '', 'html') . '</td>
                </tr>';
        }
        
        $output_gift_card_discount = '';
        
        // if there is a gift card discount, then prepare to output it
        if ($gift_card_discount > 0) {
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
            
            // update the grand total
            $grand_total = $grand_total - $gift_card_discount;
            
            // if the grand total is less than 0, then set the grand total to 0
            // this should not happen, however for some reason with gift card discounts, PHP is setting the grand total to -0.00000001 for example instead of 0
            // which results in $-0.00, which we don't want
            if ($grand_total < 0) {
                $grand_total = 0;
            }
            
            $output_gift_card_discount =
                '<tr class="gift_card_discount">
                    <td class="mobile_left" colspan="4" style="text-align: right">Gift Card' . $output_gift_card_label_plural_suffix . ':</td>
                    <td class="mobile_right" style="text-align: right; white-space: nowrap">-' . prepare_price_for_output($gift_card_discount * 100, FALSE, $discounted_price = '', 'html') . '</td>
                </tr>';
        }

        $output_surcharge = '';

        // If there is a credit card surcharge, then output row for it.
        if ($surcharge > 0) {
            $grand_total = $grand_total + $surcharge;

            $output_surcharge =
                '<tr class="order_totals data">
                    <td class="mobile_left" colspan="4" style="text-align: right">Surcharge:</td>
                    <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($surcharge * 100, FALSE, $discounted_price = '', 'html') . '</td>
                </tr>';
        }
        //if there is installment charge and payment installment((1) is no installment)
        $output_number_of_installment = '';
        $output_installment_charges = '';
        if(($installment_charges != 0)&&($payment_installment >= 2)){
            $grand_total = $grand_total + $output_installment_charges;
            
            $output_number_of_installment = 
                '<tr class="order_totals data">
                    <td class="mobile_left" colspan="4" style="text-align: right">Number of Installments:</td>
                    <td class="mobile_right" style="text-align: right">' . $payment_installment . '</td>
                </tr>';

            $output_installment_charges =
                '<tr class="order_totals data">
                    <td class="mobile_left" colspan="4" style="text-align: right">Installment Charge:</td>
                    <td class="mobile_right" style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($installment_charges, 2, '.', ',') . '</td>
                </tr>';
        }

        // if there is a recurring product
        if ($recurring_products_exist == true) {
            $number_of_payment_periods = 0;
            
            // loop through all payment periods to determine how many payment periods exist for the products in the cart
            foreach ($payment_periods as $payment_period) {
                if ($payment_period['exists'] == true) {
                    $number_of_payment_periods++;
                }
            }
            
            $output_payment_periods = '';
            
            $count = 1;
            
            foreach ($payment_periods as $payment_period_name => $payment_period) {
                // if there is a recurring product in the cart for this payment period
                if ($payment_period['exists'] == true) {
                    $output_payment_periods .=
                        '<tr class="order_totals data">
                            <td class="mobile_left" colspan="5" style="text-align: right">' . $payment_period_name . ' Subtotal:</td>
                            <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($payment_period['subtotal'] * 100, FALSE, $discounted_price = '', 'html') . '</td>
                        </tr>';
                        
                    // if tax is on, prepare tax row
                    if (ECOMMERCE_TAX == true) {

                        // If the tax is negative then set it to zero.  The tax
                        // might be negative if there are negative price products.
                        // We don't want to allow a negative tax though.
                        if ($payment_period['tax'] < 0) {
                            $payment_period['tax'] = 0;
                        }

                        $payment_period_total = $payment_period['subtotal'] + $payment_period['tax'];

                        $output_payment_periods .=
                                '<tr class="order_totals data">
                                    <td class="mobile_left" colspan="5" style="text-align: right">' . $payment_period_name . ' Tax:</td>
                                    <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($payment_period['tax'] * 100, FALSE, $discounted_price = '', 'html') . '</td>
                                </tr>';

                    } else {
                        $payment_period_total = $payment_period['subtotal'];
                    }
                    
                    $output_payment_periods .=
                        '<tr class="order_totals data">
                            <td class="mobile_left" colspan="5" style="text-align: right"><strong>' . $payment_period_name . ' Total:</strong></td>
                            <td class="mobile_right" style="text-align: right"><strong>' . prepare_price_for_output($payment_period_total * 100, FALSE, $discounted_price = '', 'html') . '</strong></td>
                        </tr>';
                        
                    // if this is not the last payment period, add a blank line for spacing
                    if ($count < $number_of_payment_periods) {
                        $output_payment_periods .=
                            '<tr class="ship_to data">
                                <td colspan="6">&nbsp;</td>
                            </tr>';
                    }
                    
                    $count++;
                }
            }
            
            $output_recurring_products =
                '<div class="recurring_products">
                <fieldset style="padding: 0px 10px 10px 10px; margin-bottom: 15px" class="software_fieldset">
                    <legend class="software_legend">Recurring Charges</legend>
                    <table class="products" width="100%" cellspacing="2" cellpadding="2" border="0">
                        ' . $output_recurring_ship_tos . '
                        ' . $output_payment_periods . '
                    </table>
                </fieldset>
                </div>';
        }
        
        // if there is an order discount and this offer has not already been added to the applied offers,
        // add order discount offer to applied offers
        if ($discount_offer_id && (in_array($discount_offer_id, $applied_offers) == FALSE)) {
            $applied_offers[] = $discount_offer_id;
        }

        $output_applied_offers = '';

        // if offer(s) have been applied, prepare list of applied offer(s)
        if ($applied_offers) {
            $output_applied_offers =
                '<div class="applied_offers" style="margin-bottom: 1em">
                    <div class="heading">Applied Offers</div>
                    <div class="data">
                    <ul style="margin-top: 0em">';
            
            // loop through each applied offer
            foreach ($applied_offers as $offer_id) {
                // get offer data
                $query = "SELECT description FROM offers WHERE id = '$offer_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $offer_description = $row['description'];
                
                $output_applied_offers .= '<li><em>' . h($offer_description) . '</em></li>';
            }
            
            $output_applied_offers .=
                '   </ul>
                </div></div>';
        }
        
        // if the credit card number is not already protected, then protect it
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
        
        // prepare billing information
        $output_billing_information = '';
        
        // get custom field information
        $query =
            "SELECT
                custom_field_1_label,
                custom_field_2_label
            FROM billing_information_pages
            WHERE page_id = '" . escape($_SESSION['ecommerce']['billing_information_page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);

        $custom_field_1_label = $row['custom_field_1_label'];
        $custom_field_2_label = $row['custom_field_2_label'];

        // if there is a custom field 1 label and custom field 1 is not blank, then prepare to output data
        if (($custom_field_1_label != '') && ($custom_field_1 != '')) {
            $output_billing_information .= h($custom_field_1_label) . ': ' . h($custom_field_1) . '<br />';
        }

        // if there is a custom field 2 label and custom field 2 is not blank, then prepare to output data
        if (($custom_field_2_label != '') && ($custom_field_2 != '')) {
            $output_billing_information .= h($custom_field_2_label) . ': ' . h($custom_field_2) . '<br />';
        }
        
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
        
        if (ECOMMERCE_TAX_EXEMPT == true) {
            // if tax-exempt was checked
            if ($tax_exempt) {
                $output_billing_information .= 'Tax-Exempt<br />';
            }
        }

        $output_unconverted_total = '';
        $output_multicurrency_disclaimer = '';

        // If the visitor's currency is different from the base currency,
        // then show actual base currency amount and disclaimer because the base currency will be charged.
        if (VISITOR_CURRENCY_CODE != BASE_CURRENCY_CODE) {
            $output_unconverted_total = '* <span style="white-space: nowrap">(' . prepare_amount($grand_total) . ' ' . h(BASE_CURRENCY_CODE) . ')</span>';

            $base_currency_name = db_value("SELECT name FROM currencies WHERE id = '" . BASE_CURRENCY_ID . "'");

            // If a base currency name was not found (e.g. no currencies),
            // then set to US dollar.
            if ($base_currency_name == '') {
                $base_currency_name = 'US Dollar';
            }
            
            $output_multicurrency_disclaimer = '<div style="margin-bottom: 15px">*This amount is based on our current currency exchange rate to ' . h($base_currency_name) . ' and may differ from the exact charges (displayed above in ' . h($base_currency_name) . ').</div>';
        }

        $output_applied_gift_cards = '';
        
        // if there is a gift card discount and there is at least one applied gift card, then prepare to output applied gift cards
        // this double check is not redundant, because there can be a situation where there is a discount with no gift cards if there was an error when the gift card transaction was submitted
        if (($gift_card_discount > 0)  && (count($applied_gift_cards) > 0)) {
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
                
                $output_applied_gift_cards .= '<li>' . h($protected_gift_card_code) . ' (Remaining Balance: ' . prepare_price_for_output($applied_gift_card['new_balance'], FALSE, $discounted_price = '', 'html') . ')</li>';
            }
            
            $output_applied_gift_cards .=
                '   </ul>
                </div></div>';
        }
        
        $output_payment_information = '';
        
        // if there was a payment method, then prepare to output payment information
        if ($payment_method != '') {
            // if the payment method is offline and there is an offline payment label in the session, then update payment method name,
            // so that the customer sees the same payment method name on the order receipt as he/she did on the order preview or express order screen
            if (($payment_method == 'Offline Payment') && (isset($_SESSION['ecommerce']['offline_payment_label']) == TRUE)) {
                $output_payment_method = h($_SESSION['ecommerce']['offline_payment_label']);
                
            // else use the default payment method name
            } else {
                $output_payment_method = h($payment_method);
            }
            
            $output_credit_debit_card_information = '';
            
            // if Credit/Debit Card payment method was selected, then prepare to output value for that
            if ($payment_method == 'Credit/Debit Card') {
                $output_credit_debit_card_information =
                    'Card Type: ' . $card_type . '<br />
                    Card Number: ' . $card_number . '<br />';
            }
            
            $output_payment_information =
                '<div class="payment heading">Payment Information</div>
                <div class="payment data">
                Payment Method: ' . $output_payment_method . '<br />' 
                . $output_credit_debit_card_information . '</div>';
        }

        $output_auto_registration = '';

        // If a user account was created via the auto-registration feature,
        // then show user account info.
        if ($_SESSION['software']['auto_registration']['email_address'] != '') {
            $output_auto_registration =
                '<div class="account heading" style="margin-top: 1em">New Account</div>
                <div class="account data">
                    <p>We have created a new account for you so you can view your orders on our site. You can find your login info below.</p>
                    <p>
                        Email: ' . h($_SESSION['software']['auto_registration']['email_address']) . '<br>
                        Password: ' . h($_SESSION['software']['auto_registration']['password']) . '
                    </p>
                </div>';
        }
        
        $output =
            '<div class="product_messages">' . $output_order_receipt_messages . '</div>
            <div class="order data" style="margin-bottom: 15px">Order Number: <b>' . $order_number . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Order Date: ' . get_absolute_time(array('timestamp' => $order_date, 'size' => 'long')) . '</div>
            <table class="order_receipt_totals" style="width: 100%; margin-bottom: 15px">
                ' . $output_ship_tos . '
                <tr class="order_totals data">
                    <td colspan="5">
                        <div class="heading">Order Totals</div>
                    </td>
                </tr>
                <tr class="order_totals data">
                    <td class="mobile_left" colspan="4" style="text-align: right">Subtotal:</td>
                    <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($subtotal * 100, FALSE, $discounted_price = '', 'html') . '</td>
                </tr>
                ' . $output_discount . '
                ' . $output_grand_tax . '
                ' . $output_grand_shipping . '
                ' . $output_gift_card_discount . '
                ' . $output_surcharge . '
                <tr class="order_totals data">
                    <td class="mobile_left" colspan="4" style="text-align: right"><strong>Total:</strong></td>
                    <td class="mobile_right" style="text-align: right"><strong>' . prepare_price_for_output($grand_total * 100, FALSE, $discounted_price = '', 'html') . $output_unconverted_total . '</strong></td>
                </tr>
            </table>
            ' . $output_multicurrency_disclaimer . '
            ' . $output_recurring_products . '
            ' . $output_applied_offers . '
            <div class="billing heading">Billing Information</div>
            <div class="billing data" style="margin-bottom: 15px">
                '. $output_billing_information . '
                ' . get_submitted_form_content_with_form_fields(array('type' => 'custom_billing_form', 'order_id' => $order_id)) . '
            </div>
            ' . $output_applied_gift_cards . '
            ' . $output_payment_information . '
            ' . $output_auto_registration;

        return
            '<div class="software_order_receipt">
                '  . $output . '
            </div>';

    // Otherwise this is a custom layout.
    } else {

        $recipients = array();

        // If there is at least one unshippable item, then add empty recipient.
        if (
            db_value(
                "SELECT COUNT(*)
                FROM order_items
                WHERE
                    (order_id = '" . e($order_id) . "')
                    AND (ship_to_id = '0')")
        ) {
            $recipients[] = array('id' => '0');
        }

        $ship_tos = db_items(
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
                countries.name AS country,
                ship_tos.address_verified,
                ship_tos.arrival_date,
                ship_tos.arrival_date_id,
                arrival_dates.name AS arrival_date_name,
                arrival_dates.custom AS arrival_date_custom,
                ship_tos.shipping_method_id,
                shipping_methods.name AS shipping_method_name,
                shipping_methods.description AS shipping_method_description,
                (ship_tos.shipping_cost / 100) AS shipping_cost,
                (ship_tos.original_shipping_cost / 100) AS original_shipping_cost,
                ship_tos.offer_id
            FROM ship_tos
            LEFT JOIN shipping_methods ON shipping_methods.id = ship_tos.shipping_method_id
            LEFT JOIN countries ON ship_tos.country = countries.code
            LEFT JOIN arrival_dates ON ship_tos.arrival_date_id = arrival_dates.id
            WHERE ship_tos.order_id = '" . e($order_id) . "'
            ORDER BY ship_tos.id");

        $recipients = array_merge($recipients, $ship_tos);

        $order_receipt_messages = array();
        $taxable_items = false;
        $shippable_items = false;
        $nonrecurring_items = false;
        $recurring_items = false;
        $arrival_dates = false;
        $start_date = false;
        $show_subtotal = false;
        $subtotal = 0;
        $subtotal_info = '';
        $discount = 0;
        $discount_info = '';
        $tax = 0;
        $tax_info = '';
        $shipping = 0;
        $shipping_info = '';
        $applied_gift_cards = array();
        $gift_card_discount_info = '';
        $surcharge_info = '';
        $number_of_installment = '';
        $installment_charges_info = '';
        $total = 0;
        $total_info = '';
        $base_currency_total_info = '';
        $base_currency_name = '';
        $total_disclaimer = false;
        $payment_periods = array();
        $applied_offers = array();
        $billing_form = false;
        $billing_form_title = '';
        $fields = array();

        // If shipping is enabled and there is at least one active arrival date,
        // then remember that there are arrival dates, so we can output
        // arrival date info for each recipient.
        if (
            ECOMMERCE_SHIPPING
            and db_value(
                "SELECT id
                FROM arrival_dates
                WHERE
                    (status = 'enabled')
                    AND (start_date <= CURRENT_DATE())
                    AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())
                LIMIT 1")
        ) {
            $arrival_dates = true;
        }

        // intialize payment periods array that we will use to store data
        // about payment periods for recurring products
        $payment_periods = array(
            'Unknown' => '',
            'Monthly' => '',
            'Weekly' => '',
            'Every Two Weeks' => '',
            'Twice every Month' => '',
            'Every Four Weeks' => '',
            'Quarterly' => '',
            'Twice every Year' => '',
            'Yearly' => '');
        
        foreach ($payment_periods as $key => $value) {
            $payment_periods[$key] = array(
                'name' => $key,
                'exists' => false,
                'subtotal' => 0,
                'tax' => 0);
        }

        // If credit/debit card payment method is not enabled or the payment gateway
        // is not ClearCommerce, then remember that we need to deal with the start date.
        if (
            !ECOMMERCE_CREDIT_DEBIT_CARD
            or (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')
        ) {
            $start_date = true;
        }

        foreach ($recipients as $key => $recipient) {

            $recipient['shipping'] = false;

            if ($recipient['id']) {
                $recipient['shipping'] = true;
            }

            $recipient['ship_to_heading'] = false;

            if (ECOMMERCE_SHIPPING and $recipient['shipping']) {

                $recipient['ship_to_heading'] = true;

                // If this shipping address is verified,
                // then convert salutation and country to all uppercase.
                if ($recipient['address_verified']) {
                    $recipient['salutation'] = mb_strtoupper($recipient['salutation']);
                    $recipient['country'] = mb_strtoupper($recipient['country']);
                }

                $recipient['update_url'] = PATH . encode_url_path(get_page_name($_SESSION['ecommerce']['shipping_address_and_arrival_page_id'])) . '?ship_to_id=' . $recipient['id'];

                if ($recipient['salutation'] and $recipient['last_name']) {
                    $recipient['name'] = $recipient['salutation'];
                }

                if ($recipient['first_name']) {
                    if ($recipient['name']) {
                        $recipient['name'] .= ' ';
                    }
                    
                    $recipient['name'] .= $recipient['first_name'];
                }

                if ($recipient['last_name']) {
                    if ($recipient['name']) {
                        $recipient['name'] .= ' ';
                    }
                    
                    $recipient['name'] .= $recipient['last_name'];
                }

                $recipient['address'] = $recipient['company'];

                if ($recipient['address_1']) {
                    if ($recipient['address']) {
                        $recipient['address'] .= ', ';
                    }
                    
                    $recipient['address'] .= $recipient['address_1'];
                }
                
                if ($recipient['address_2']) {
                    if ($recipient['address']) {
                        $recipient['address'] .= ', ';
                    }
                    
                    $recipient['address'] .= $recipient['address_2'];
                }
                
                if ($recipient['city']) {
                    if ($recipient['address']) {
                        $recipient['address'] .= ', ';
                    }
                    
                    $recipient['address'] .= $recipient['city'];
                }
                
                if ($recipient['state']) {
                    if ($recipient['address']) {
                        $recipient['address'] .= ', ';
                    }
                    
                    $recipient['address'] .= $recipient['state'];
                }
                
                if ($recipient['zip_code']) {
                    if ($recipient['address']) {
                        $recipient['address'] .= ', ';
                    }
                    
                    $recipient['address'] .= $recipient['zip_code'];
                }
                
                if ($recipient['country']) {
                    if ($recipient['address']) {
                        if (!$recipient['zip_code']) {
                            $recipient['address'] .= ',';
                        }

                        $recipient['address'] .= ' ';
                    }
                    
                    $recipient['address'] .= $recipient['country'];
                }

                $recipient['form'] = false;

                // Get custom shipping form info, if it exists.
                $form_info = get_form_review_info(array(
                    'type' => 'custom_shipping_form',
                    'ship_to_id' => $recipient['id']));

                if ($form_info) {
                    $recipient['form'] = true;
                    $recipient['form_title'] = $form_info['title'];
                    $recipient['form_data'] = $form_info['data'];
                    $recipient['fields'] = $form_info['fields'];
                }
                    
                // If there is an offer applied to this recipient,
                // then remember that and add offer to applied offers array
                if ($recipient['offer_id']) {
                    $recipient['discounted'] = true;

                    $recipient['shipping_cost_info'] = prepare_price_for_output($recipient['original_shipping_cost'] * 100, true, $recipient['shipping_cost'] * 100, 'html');
                    
                    // if the offer has not already been added to the applied offers array, then store this offer as an applied offer
                    if (!in_array($recipient['offer_id'], $applied_offers)) {
                        $applied_offers[] = $recipient['offer_id'];
                    }

                } else {
                    $recipient['discounted'] = false;

                    $recipient['shipping_cost_info'] = prepare_price_for_output($recipient['shipping_cost'] * 100, false, $discounted_price = '', 'html');

                }

                $shipping += $recipient['shipping_cost'];

            }

            $recipient['items'] = db_items(
                "SELECT
                    order_items.id,
                    order_items.product_id,
                    order_items.product_name AS name,
                    order_items.quantity,
                    order_items.price / 100 AS price,
                    order_items.tax / 100 AS tax,
                    order_items.offer_id,
                    order_items.added_by_offer,
                    order_items.discounted_by_offer,
                    order_items.recurring_payment_period,
                    order_items.recurring_number_of_payments,
                    order_items.recurring_start_date,
                    order_items.calendar_event_id,
                    order_items.recurrence_number,
                    products.image_name,
                    products.short_description,
                    products.full_description,
                    products.inventory,
                    products.inventory_quantity,
                    products.out_of_stock_message,
                    products.price / 100 AS product_price,
                    products.recurring,
                    products.recurring_schedule_editable_by_customer,
                    products.start,
                    products.number_of_payments,
                    products.payment_period,
                    products.selection_type,
                    products.taxable,
                    products.shippable,
                    products.gift_card,
                    products.form,
                    products.form_name AS form_title,
                    products.form_label_column_width,
                    products.form_quantity_type,
                    products.order_receipt_message,
                    products.submit_form,
                    products.submit_form_custom_form_page_id,
                    products.submit_form_update,
                    products.submit_form_update_where_field,
                    products.submit_form_update_where_value
                FROM order_items
                LEFT JOIN products ON order_items.product_id = products.id
                WHERE order_items.order_id = '" . e($order_id) . "' AND order_items.ship_to_id = '" . $recipient['id'] . "'
                ORDER BY order_items.id");

            $recipient['in_nonrecurring'] = false;
            $recipient['in_recurring'] = false;
            $recipient['non_donations_in_nonrecurring'] = false;
            $recipient['non_donations_in_recurring'] = false;
            $recipient['donations_in_nonrecurring'] = false;
            $recipient['donations_in_recurring'] = false;

            foreach ($recipient['items'] as $item_key => $item) {

                // If this item has an order receipt message, and the message has
                // not already been added for this product, then add it.
                if (
                    $item['order_receipt_message']
                    and ($item['order_receipt_message'] != '<p />')
                    and ($item['order_receipt_message'] != '<P />')
                    and ($item['order_receipt_message'] != '<p></p>')
                    and ($item['order_receipt_message'] != '<P></P>')
                    and !isset($order_receipt_messages[$item['product_id']])
                ) {
                    $order_receipt_messages[$item['product_id']] = $item['order_receipt_message'];
                }

                $item['amount'] = $item['price'] * $item['quantity'];

                $item['recurring_schedule'] = false;

                // If this is a recurring item and the schedule is editable
                // by the customer, then remember that.
                if ($item['recurring'] and $item['recurring_schedule_editable_by_customer']) {
                    $item['recurring_schedule'] = true;
                }

                $item['in_nonrecurring'] = false;

                // if product is not a recurring product or if start date is less than or equal to the order date and payment gateway is not ClearCommerce, then it is in the non-recurring order
                if (
                    (!$item['recurring'])
                    or
                    (

                        ($item['recurring_start_date'] <= date('Y-m-d', $order_date))
                        and
                        (
                            !ECOMMERCE_CREDIT_DEBIT_CARD
                            or (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')
                        )
                    )
                ) {
                    // Remember that there are nonrecurring items,
                    // so we know if the nonrecurring item table should be shown.
                    $nonrecurring_items = true;

                    // Remember that the recipient contains a nonrecurring item,
                    // so we know if we need to output nonrecurring content for it.
                    $recipient['in_nonrecurring'] = true;

                    // Remember that the item is a nonrecurring item,
                    // so we know if we need to output nonrecurring content for it.
                    $item['in_nonrecurring'] = true;

                    $subtotal += $item['amount'];
                    $tax += $item['tax'] * $item['quantity'];
                    
                    if ($item['selection_type'] == 'donation') {
                        $recipient['donations_in_nonrecurring'] = true;
                    } else {
                        $recipient['non_donations_in_nonrecurring'] = true;
                    }
                }

                $item['image_url'] = '';

                if ($item['image_name'] != '') {
                    $item['image_url'] = PATH . encode_url_path($item['image_name']);
                }

                // If calendars is enabled and this order item is for a calendar event,
                // then get calendar event info like the name and date & time.
                if (CALENDARS and $item['calendar_event_id']) {
                    $item['calendar_event'] = get_calendar_event($item['calendar_event_id'], $item['recurrence_number']);
                }

                // If this item is a gift card, then prepare info.
                if ($item['gift_card']) {
                    // If the quantity is 100 or less, then set the number
                    // of gift cards to the quantity.
                    if ($item['quantity'] <= 100) {
                        $item['number_of_gift_cards'] = $item['quantity'];
                        
                    // Otherwise the quantity is greater than 100, so set the
                    // number of gift cards to 100. We do this in order to prevent
                    // a ton of forms from appearing and causing a slowdown.
                    } else {
                        $item['number_of_gift_cards'] = 100;
                    }

                    $item['gift_cards'] = array();

                    // Loop through all quantities in order to prepare fields for each quantity.
                    for ($quantity_number = 1; $quantity_number <= $item['number_of_gift_cards']; $quantity_number++) {

                        // Get saved gift card data from database.
                        $item['gift_cards'][] = db_item(
                            "SELECT
                                id,
                                from_name,
                                recipient_email_address,
                                message,
                                delivery_date,
                                quantity_number
                            FROM order_item_gift_cards
                            WHERE
                                (order_item_id = '" . $item['id'] . "')
                                AND (quantity_number = '$quantity_number')");

                    }

                }

                // If this item has a product form, then prepare info.
                if ($item['form']) {

                    // If there should be one form per quantity,
                    // then set the number of forms to the quantity of this order item.
                    if ($item['form_quantity_type'] == 'One Form per Quantity') {
                        // If the quantity is 100 or less,
                        // then set the number of forms to the quantity
                        if ($item['quantity'] <= 100) {
                            $item['number_of_forms'] = $item['quantity'];
                            
                        // Otherwise the quantity is greater than 100,
                        // so set the number of forms to 100.
                        } else {
                            $item['number_of_forms'] = 100;
                        }
                        
                    // Otherwise there should be one form per product,
                    // so set the number of forms to 1.
                    } elseif ($item['form_quantity_type'] == 'One Form per Product') {
                        $item['number_of_forms'] = 1;
                    }

                    $item['forms'] = array();

                    // Loop through all forms in order to get data.
                    for ($quantity_number = 1; $quantity_number <= $item['number_of_forms']; $quantity_number++) {

                        // Get product form info, if it exists.
                        $form_info = get_form_review_info(array(
                            'type' => 'product_form',
                            'order_item_id' => $item['id'],
                            'quantity_number' => $quantity_number,
                            'product_id' => $item['product_id']));

                        if ($form_info) {
                            $form_info['quantity_number'] = $quantity_number;
                            $item['forms'][] = $form_info;
                        }

                    }

                }

                $item['discounted'] = false;

                if ($item['discounted_by_offer']) {
                    $item['discounted'] = true;
                }

                // If this item is not a donation then prepare price.
                if ($item['selection_type'] != 'donation') {
                    $item['price_info'] = prepare_price_for_output($item['product_price'] * 100, $item['discounted'], $item['price'] * 100, 'html');
                }

                $item['amount_info'] = prepare_price_for_output($item['amount'] * 100, false, $discounted_price = '', 'html');

                if (ECOMMERCE_TAX and $item['taxable']) {
                    $taxable_items = true;
                }
                
                if (ECOMMERCE_SHIPPING and $item['shippable']) {
                    $shippable_items = true;
                }

                $item['in_recurring'] = false;

                if ($item['recurring']) {

                    $recurring_items = true;
                    $recipient['in_recurring'] = true;
                    $item['in_recurring'] = true;

                    if ($item['selection_type'] == 'donation') {
                        $recipient['donations_in_recurring'] = true;
                    } else {
                        $recipient['non_donations_in_recurring'] = true;
                    }

                    $payment_periods[$item['recurring_payment_period']]['exists'] = true;
                    $payment_periods[$item['recurring_payment_period']]['subtotal'] += $item['amount'];
                    $payment_periods[$item['recurring_payment_period']]['tax'] += $item['tax'] * $item['quantity'];

                }

                // If there is an offer applied to this order item
                // and offer has not already been added to applied offers array,
                // then store this offer as an applied offer.
                if ($item['offer_id'] and !in_array($item['offer_id'], $applied_offers)) {
                    $applied_offers[] = $item['offer_id'];
                }

                $recipient['items'][$item_key] = $item;

            }

            // If there is an offer applied to this ship to
            // and offer has not already been added to applied offers array,
            // then store this offer as an applied offer
            if ($recipient['offer_id'] and !in_array($recipient['offer_id'], $applied_offers)) {
                $applied_offers[] = $recipient['offer_id'];
            }

            $recipients[$key] = $recipient;
        }

        $total = $subtotal;

        $subtotal_info = prepare_price_for_output($subtotal * 100, false, $discounted_price = '', 'html');

        $discount = $_SESSION['ecommerce']['order_discount'] / 100;

        // If there is a discount, then prepare discount info and total.
        if ($discount) {

            $discount_info = prepare_price_for_output($discount * 100, false, $discounted_price = '', 'html');

            $total -= $discount;

            if ($total < 0) {
                $total = 0;
            }

        }

        // if tax is on, update grand total and prepare tax row
        if (ECOMMERCE_TAX) {
            // if there is an order discount, adjust tax
            if ($subtotal and $discount) {
                $tax -= $tax * ($discount / $subtotal);
            }

            // If the tax is negative then set it to zero.  The tax might be negative
            // if there is an offer that discounts the order or if there are
            // negative price products.  We don't want to allow a negative tax though.
            if ($tax < 0) {
                $tax = 0;
            }

            $total += $tax;

            $tax_info = prepare_price_for_output($tax * 100, false, $discounted_price = '', 'html');
        }

        if ($shippable_items) {
            $total += $shipping;

            $shipping_info = prepare_price_for_output($shipping * 100, false, $discounted_price = '', 'html');
        }

        // If there is a gift card discount then get applied gift cards
        // and prepare gift card info.
        if ($gift_card_discount) {

            $total -= $gift_card_discount;

            // if the grand total is less than 0, then set the grand total to 0
            // this should not happen, however for some reason with gift card discounts, PHP is setting the grand total to -0.00000001 for example instead of 0
            // which results in $-0.00, which we don't want
            if ($total < 0) {
                $total = 0;
            }

            $gift_card_discount_info = prepare_price_for_output($gift_card_discount * 100, false, $discounted_price = '', 'html');

            // get applied gift cards in order to output them
            $applied_gift_cards = db_items(
                "SELECT
                    id,
                    code,
                    (amount / 100) AS amount,
                    (old_balance / 100) AS old_balance,
                    (new_balance / 100) AS new_balance,
                    givex
                FROM applied_gift_cards
                WHERE order_id = '" . e($order_id) . "'
                ORDER BY id ASC");
                
            // Loop through the applied gift cards to prepare info for each one.
            foreach ($applied_gift_cards as $key => $gift_card) {

                $gift_card['amount_info'] = prepare_price_for_output($gift_card['amount'] * 100, false, $discounted_price = '', 'html');

                $gift_card['old_balance_info'] = prepare_price_for_output($gift_card['old_balance'] * 100, false, $discounted_price = '', 'html');

                $gift_card['new_balance_info'] = prepare_price_for_output($gift_card['new_balance'] * 100, false, $discounted_price = '', 'html');

                if ($gift_card['givex'] == 0) {
                    $gift_card['protected_code'] = protect_gift_card_code($gift_card['code']);
                } else {
                    $gift_card['protected_code'] = protect_givex_gift_card_code($gift_card['code']);
                }

                $applied_gift_cards[$key] = $gift_card;

            }

        }
        if(($installment_charges > 0)&&($payment_installment >= 2)){
            $total += $installment_charges;
            $number_of_installment = $payment_installment;
            $installment_charges_info = prepare_price_for_output($installment_charges * 100, false, $discounted_price = '', 'html');
        }

        // If there is a discount, tax, shipping, or gift card discount,
        // then show subtotal row.
        if ($discount_info or $tax_info or $shipping_info or $gift_card_discount_info or $installment_charges_info) {
            $show_subtotal = true;
        }

        // If there is a credit card surcharge, then prepare info for it.
        if ($surcharge) {
            $total += $surcharge;

            $surcharge_info = prepare_price_for_output($surcharge * 100, false, $discounted_price = '', 'html');
        }


        $total_info = prepare_price_for_output($total * 100, false, $discounted_price = '', 'html');
        
        // If the visitor's currency is different from the base currency,
        // then show actual base currency amount and disclaimer
        // because the base currency will be charged.
        if (VISITOR_CURRENCY_CODE != BASE_CURRENCY_CODE) {
            $base_currency_total_info = '<span style="white-space: nowrap">' . prepare_amount($total) . ' ' . h(BASE_CURRENCY_CODE) . '</span>';

            $base_currency_name = db_value("SELECT name FROM currencies WHERE id = '" . BASE_CURRENCY_ID . "'");

            // If a base currency name was not found (e.g. no currencies),
            // then set to US dollar.
            if ($base_currency_name == '') {
                $base_currency_name = 'US Dollar';
            }

            $total_disclaimer = true;
        }

        // If there is at least one recurring item, then prepare payment periods.
        if ($recurring_items) {
            // Loop through payment periods in order to prepare info
            // and remove ones that are not relevant to this order.
            foreach ($payment_periods as $key => $payment_period) {

                if ($payment_period['exists']) {

                    $payment_period['subtotal_info'] = prepare_price_for_output($payment_period['subtotal'] * 100, false, $discounted_price = '', 'html');

                    $payment_period['total'] = $payment_period['subtotal'];

                    if (ECOMMERCE_TAX) {

                        // If the tax is negative then set it to zero.  The tax
                        // might be negative if there are negative price products.
                        // We don't want to allow a negative tax though.
                        if ($payment_period['tax'] < 0) {
                            $payment_period['tax'] = 0;
                        }

                        $payment_period['total'] += $payment_period['tax'];

                        $payment_period['tax_info'] = prepare_price_for_output($payment_period['tax'] * 100, false, $discounted_price = '', 'html');
                        
                    }

                    $payment_period['total_info'] = prepare_price_for_output($payment_period['total'] * 100, false, $discounted_price = '', 'html');

                    $payment_periods[$key] = $payment_period;

                } else {
                    unset($payment_periods[$key]);
                }

            }
        }

        // If there is an order discount and this offer has not already
        // been added to the applied offers, then add it.
        if ($discount_offer_id and !in_array($discount_offer_id, $applied_offers)) {
            $applied_offers[] = $discount_offer_id;
        }

        // Loop through the offers in order to get more info about each one.
        foreach ($applied_offers as $key => $offer_id) {
            $offer = db_item(
                "SELECT id, code, description
                FROM offers WHERE id = '" . e($offer_id) . "'");

            if ($offer) {
                $applied_offers[$key] = $offer;
            } else {
                unset($applied_offers[$key]);
            }
        }

        // If tax is enabled and tax-exempt is allowed, then prepare tax-exempt
        // value from the customer.
        if (ECOMMERCE_TAX and ECOMMERCE_TAX_EXEMPT) {
            settype($tax_exempt, 'bool');

        // Otherwise force tax-exempt to be false.
        } else {
            $tax_exempt = false;
        }

        // get custom field information
        $query =
            "SELECT
                custom_field_1_label,
                custom_field_2_label
            FROM billing_information_pages
            WHERE page_id = '" . e($_SESSION['ecommerce']['billing_information_page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);

        $custom_field_1_label = $row['custom_field_1_label'];
        $custom_field_2_label = $row['custom_field_2_label'];

        $fields = array();

        // Get custom billing form info, if it exists.
        $form_info = get_form_review_info(array(
            'type' => 'custom_billing_form',
            'order_id' => $order_id));

        if ($form_info) {
            $billing_form = true;
            $billing_form_title = $form_info['title'];
            $fields = $form_info['fields'];
        }

        $payment_method_label = '';

        if ($payment_method) {

            // if the payment method is offline and there is an offline payment
            // label in the session, then update payment method name, so that
            // the customer sees the same payment method name on the order receipt
            // as he/she did on the order preview or express order screen.
            if (
                ($payment_method == 'Offline Payment')
                and isset($_SESSION['ecommerce']['offline_payment_label'])
            ) {
                $payment_method_label = $_SESSION['ecommerce']['offline_payment_label'];

            } else {
                $payment_method_label = $payment_method;
            }

            if ($payment_method == 'Credit/Debit Card') {

                // if the credit card number is not already protected, then protect it
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

            }

        }

        $auto_registration = false;

        if ($_SESSION['software']['auto_registration']['email_address'] != '') {
            $auto_registration = true;
            $auto_registration_email_address = $_SESSION['software']['auto_registration']['email_address'];
            $auto_registration_password = $_SESSION['software']['auto_registration']['password'];
        }

        $currency = false;
        $currency_attributes = '';
        $currencies = array();
        $currency_system = '';

        if (ECOMMERCE_MULTICURRENCY) {
            // Get all currencies where the exchange rate is not 0, with base currency first.
            $currencies = db_items(
                "SELECT
                    id,
                    name,
                    base,
                    code,
                    symbol,
                    exchange_rate
                FROM currencies
                WHERE exchange_rate != '0'
                ORDER BY
                    base DESC,
                    name ASC");

            // If there is at least one extra currency, in addition to the base currency,
            // then continue to prepare currency info.
            if (count($currencies) > 1) {

                $currency = true;

                $currency_attributes =
                    'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/update_currency.php" ' .
                    'method="post"';

                $currency_options = array();

                foreach ($currencies as $currency) {
                    $label = h($currency['name'] . ' (' . $currency['code'] . ')');

                    $currency_options[$label] = $currency['id'];
                }

                $form->set('currency_id', 'options', $currency_options);
                $form->set('currency_id', VISITOR_CURRENCY_ID);
                $form->set('send_to', REQUEST_URL);

                $currency_system =
                    get_token_field() . '
                    <input type="hidden" name="send_to">
                    <script>software.init_currency()</script>';

            } else {
                $currencies = array();
            }
        }

        $content = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'order_receipt_messages' => $order_receipt_messages,
            'order_number' => $order_number,
            'order_date' => $order_date,
            'recipients' => $recipients,
            'product_description_type' => $product_description_type,
            'show_subtotal' => $show_subtotal,
            'subtotal' => $subtotal,
            'subtotal_info' => $subtotal_info,
            'discount' => $discount,
            'discount_info' => $discount_info,
            'tax' => $tax,
            'tax_info' => $tax_info,
            'shipping' => $shipping,
            'shipping_info' => $shipping_info,
            'applied_gift_cards' => $applied_gift_cards,
            'number_of_applied_gift_cards' => count($applied_gift_cards),
            'gift_card_discount' => $gift_card_discount,
            'gift_card_discount_info' => $gift_card_discount_info,
            'number_of_installment' => $number_of_installment,
            'installment_charges' => $installment_charges,
            'installment_charges_info' => $installment_charges_info,
            'surcharge' => $surcharge,
            'surcharge_info' => $surcharge_info,
            'total' => $total,
            'total_info' => $total_info,
            'base_currency_total_info' => $base_currency_total_info,
            'base_currency_name' => $base_currency_name,
            'total_disclaimer' => $total_disclaimer,
            'taxable_items' => $taxable_items,
            'shippable_items' => $shippable_items,
            'nonrecurring_items' => $nonrecurring_items,
            'recurring_items' => $recurring_items,
            'arrival_dates' => $arrival_dates,
            'start_date' => $start_date,
            'payment_periods' => $payment_periods,
            'applied_offers' => $applied_offers,
            'number_of_applied_offers' => count($applied_offers),
            'custom_field_1_label' => $custom_field_1_label,
            'custom_field_1' => $custom_field_1,
            'custom_field_2_label' => $custom_field_2_label,
            'custom_field_2' => $custom_field_2,
            'billing_salutation' => $billing_salutation,
            'billing_first_name' => $billing_first_name,
            'billing_last_name' => $billing_last_name,
            'billing_company' => $billing_company,
            'billing_address_1' => $billing_address_1,
            'billing_address_2' => $billing_address_2,
            'billing_city' => $billing_city,
            'billing_state' => $billing_state,
            'billing_zip_code' => $billing_zip_code,
            'billing_country' => $billing_country,
            'billing_phone_number' => $billing_phone_number,
            'billing_fax_number' => $billing_fax_number,
            'billing_email_address' => $billing_email_address,
            'po_number' => $po_number,
            'tax_exempt' => $tax_exempt,
            'discount_offer_id' => $discount_offer_id,
            'billing_form' => $billing_form,
            'billing_form_title' => $billing_form_title,
            'fields' => $fields,
            'payment_method' => $payment_method,
            'payment_method_label' => $payment_method_label,
            'card_type' => $card_type,
            'card_number' => $card_number,
            'cardholder' => $cardholder,
            'auto_registration' => $auto_registration,
            'auto_registration_email_address' => $auto_registration_email_address,
            'auto_registration_password' => $auto_registration_password,
            'currency_symbol' => VISITOR_CURRENCY_SYMBOL,
            'currency_code' => VISITOR_CURRENCY_CODE_FOR_OUTPUT,
            'currency' => $currency,
            'currency_attributes' => $currency_attributes,
            'currencies' => $currencies,
            'currency_system' => $currency_system));

        $content = $form->prepare($content);

        $form->remove();

        return
            '<div class="software_order_receipt">
                '  . $content . '
            </div>';

    }

}