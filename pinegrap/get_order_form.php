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

function get_order_form($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];

    $properties = get_page_type_properties($page_id, 'order form');

    $product_group_id = $properties['product_group_id'];
    $product_layout = $properties['product_layout'];
    $add_button_label = $properties['add_button_label'];
    $skip_button_label = $properties['skip_button_label'];
    $skip_button_next_page_id = $properties['skip_button_next_page_id'];

    $layout_type = get_layout_type($page_id);
    
    $form = new liveform('order_form');

    $products = array();

    // If the product group is enabled, then get products in this product.
    if (db_value("SELECT enabled FROM product_groups WHERE id = '" . e($product_group_id) . "'")) {
        $products = db_items(
            "SELECT
                products.id,
                products.name,
                products.image_name,
                products.short_description,
                products.full_description,
                products.price,
                products.selection_type,
                products.default_quantity,
                products.shippable,
                products.recurring,
                products.recurring_schedule_editable_by_customer,
                products.start,
                products.number_of_payments,
                products.payment_period,
                products.inventory,
                products.inventory_quantity,
                products.backorder,
                products.out_of_stock_message
            FROM products_groups_xref
            LEFT JOIN products ON products.id = products_groups_xref.product
            WHERE
                (products_groups_xref.product_group = '$product_group_id')
                AND (products.enabled = '1')
            ORDER BY products_groups_xref.sort_order, products.name");
    }
    
    // If there is at least one product to output, then get discounted product prices,
    // so we can show the discounted price.
    if (count($products) > 0) {
        $discounted_product_prices = get_discounted_product_prices();
    }

    if ($layout_type == 'system') {
        
        // set $shippable_products to false until we determine that there is at least one shippable product
        $shippable_products = false;
        
        // initialize variables to determine which types of products are being displayed
        $available_products_exist = FALSE;
        $non_donations_exist = false;
        $donations_exist = false;
        
        // display the products differently depending on the product layout
        switch ($product_layout) {
            // if product layout is not selected or is list
            case '':
            case 'list':
                // initialize variables to determine which type of selections are being displayed
                $checkbox_selections_exist = false;
                $quantity_selections_exist = false;
                
                $output_products = '';
                $row_count = 1;
                
                // loop through products
                foreach ($products as $product) {
                    // assume that the product is not available until we find out otheriwse
                    $available = FALSE;
                    
                    // if inventory is disabled for the product,
                    // or the inventory quantity is greater than 0,
                    // or the product is allowed to be backordered,
                    // then the product is available so remember that
                    if (
                        ($product['inventory'] == 0)
                        || ($product['inventory_quantity'] > 0)
                        || ($product['backorder'] == 1)
                    ) {
                        $available = TRUE;
                        $available_products_exist = TRUE;
                        
                        // if the product is shippable take note of it
                        if ($product['shippable'] == 1) {
                            $shippable_products = true;
                        }
                    }
                    
                    // if inventory is enabled for the product,
                    // and the product is out of stock,
                    // and there is an out of stock message
                    // then append out of stock message to full description
                    if (
                        ($product['inventory'] == 1)
                        && ($product['inventory_quantity'] == 0)
                        && ($product['out_of_stock_message'] != '')
                        && ($product['out_of_stock_message'] != '<p></p>')
                    ) {
                        $product['full_description'] .= ' ' . $product['out_of_stock_message'];
                    }
                    
                    // if mode is edit, then add edit button to images
                    if ($editable == TRUE) {
                        $product['full_description'] = add_edit_button_for_images('product', $product['id'], $product['full_description']);
                    }
                    
                    $output_price = '&nbsp;';
                    $output_order_field = '&nbsp;';

                    switch ($product['selection_type']) {
                        case 'checkbox':
                            // assume that the product is not discounted, until we find out otherwise
                            $discounted = FALSE;
                            $discounted_price = '';
                            
                            // if the product is discounted, then prepare to show that
                            if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                                $discounted = TRUE;
                                $discounted_price = $discounted_product_prices[$product['id']];
                            }
                            
                            $output_price = prepare_price_for_output($product['price'], $discounted, $discounted_price, 'html');
                            
                            // if the product is available then prepare various info
                            if ($available == TRUE) {
                                $checkbox_selections_exist = true;
                                $output_order_field = $form->output_field(array('type'=>'checkbox', 'name'=>'product_' . $product['id'], 'value' => '1', 'class'=>'software_input_checkbox'));
                                $non_donations_exist = true;
                            }

                            break;
                            
                        case 'quantity';
                            // assume that the product is not discounted, until we find out otherwise
                            $discounted = FALSE;
                            $discounted_price = '';
                            
                            // if the product is discounted, then prepare to show that
                            if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                                $discounted = TRUE;
                                $discounted_price = $discounted_product_prices[$product['id']];
                            }
                            
                            $output_price = prepare_price_for_output($product['price'], $discounted, $discounted_price, 'html');
                            
                            // if the product is available then prepare various info
                            if ($available == TRUE) {
                                $quantity_selections_exist = true;
                                $output_order_field = $form->output_field(array('type'=>'text', 'name'=>'product_' . $product['id'], 'value'=>$product['default_quantity'], 'size'=>'2', 'maxlength'=>'9', 'class'=>'software_input_text'));
                                $non_donations_exist = true;
                            }
                            
                            break;
                            
                        case 'donation';
                            // if the product is available then prepare various info
                            if ($available == TRUE) {
                                $output_price = VISITOR_CURRENCY_SYMBOL . $form->output_field(array('type'=>'text', 'name'=>'donation_' . $product['id'], 'size'=>'5', 'class'=>'software_input_text', 'style'=>'text-align: right')) . h(VISITOR_CURRENCY_CODE_FOR_OUTPUT);
                                $output_order_field = '&nbsp;';
                                $donations_exist = true;
                            }
                            
                            break;
                            
                        case 'autoselect':
                            // assume that the product is not discounted, until we find out otherwise
                            $discounted = FALSE;
                            $discounted_price = '';
                            
                            // if the product is discounted, then prepare to show that
                            if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                                $discounted = TRUE;
                                $discounted_price = $discounted_product_prices[$product['id']];
                            }
                            
                            $output_price = prepare_price_for_output($product['price'], $discounted, $discounted_price, 'html');
                            
                            // if the product is available then prepare various info
                            if ($available == TRUE) {
                                $non_donations_exist = true;
                            }
                            
                            break;
                    }
                    
                    // if page is being viewed in an editable mode, output edit button to allow user to edit product
                    if ($editable) {
                        $edit_button = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_product.php?id=' . $product['id'] . '&from=pages&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Product: ' . h($product['short_description']) . '">Edit</a>';
                    }
                    
                    // assume that we don't need to output a recurring schedule fieldset, until we find out otherwise
                    $output_recurring_schedule_row = '';
                    
                    // if the product is available,
                    // and the product is a recurring product and the recurring schedule is editable by the customer,
                    // then output recurring schedule fieldset
                    if (($available == TRUE) && ($product['recurring'] == 1) && ($product['recurring_schedule_editable_by_customer'] == 1)) {
                        // if screen was not just submitted, then prefill recurring schedule fields
                        if (($form->field_in_session('submit_add') == false) && ($form->field_in_session('submit_skip') == false)) {
                            $form->assign_field_value('recurring_payment_period_' . $product['id'], $product['payment_period']);
                            $form->assign_field_value('recurring_number_of_payments_' . $product['id'], $product['number_of_payments']);
                            
                            // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then prepare start date
                            if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                                // If the date format is month and then day, then use that format.
                                if (DATE_FORMAT == 'month_day') {
                                    $month_and_day_format = 'n/j';

                                // Otherwise the date format is day and then month, so use that format.
                                } else {
                                    $month_and_day_format = 'j/n';
                                }

                                // get the default start date based on the number of days that are set for the product
                                $recurring_start_date = date($month_and_day_format . '/Y', time() + (86400 * $product['start']));
                                
                                $form->assign_field_value('recurring_start_date_' . $product['id'], $recurring_start_date);
                            }
                        }
                        
                        $output_recurring_number_of_payments_required_asterisk = '';
                        
                        // if credit/debit card is enabled as a payment method and the payment gateway is set to ClearCommerce or First Data Global Gateway
                        // then output asterisk to show that number of payments is required
                        if (
                            (ECOMMERCE_CREDIT_DEBIT_CARD == true)
                            &&
                            (
                                (ECOMMERCE_PAYMENT_GATEWAY == 'ClearCommerce')
                                || (ECOMMERCE_PAYMENT_GATEWAY == 'First Data Global Gateway')
                            )
                        ) {
                            $output_recurring_number_of_payments_required_asterisk = '*';
                        }
                        
                        // if number of payments is 0, then set to empty string
                        if ($form->get_field_value('recurring_number_of_payments_' . $product['id']) == 0) {
                            $form->assign_field_value('recurring_number_of_payments_' . $product['id'], '');
                        }
                        
                        // determine if start row should be outputted
                        $output_start_date_row = '';
                        
                        // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then output start date row
                        if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                            $output_start_date_row =
                                '<tr>
                                    <td>Start Date*:</td>
                                    <td>
                                        ' . $form->output_field(array('type'=>'text', 'id'=>'recurring_start_date_' . $product['id'], 'name'=>'recurring_start_date_' . $product['id'], 'size'=>'10', 'maxlength'=>'10', 'class'=>'software_input_text')) . '
                                        ' . get_date_picker_format() . '
                                        <script>
                                            software_$("#recurring_start_date_' . $product['id'] . '").datepicker({
                                                dateFormat: date_picker_format
                                            });
                                        </script>
                                    </td>
                                </tr>';
                        }
                        
                        $output_recurring_schedule_row =
                            '<tr class="products data row_' . ($row_count % 2) . '">';
                        
                        if ($device_type == 'mobile') {
                            $output_recurring_schedule_row .=
                                '<td colspan="4">';
                        } else {
                            $output_recurring_schedule_row .=
                                '<td class="mobile_hide">&nbsp;</td>
                                <td colspan="3">';
                        }
                        $output_recurring_schedule_row .=
                                    '<fieldset class="software_fieldset" style="margin-bottom: 1em">
                                        <legend class="software_legend">Payment Schedule</legend>
                                            <table>
                                                <tr>
                                                    <td>Frequency*:</td>
                                                    <td>' . $form->output_field(array('type'=>'select', 'name'=>'recurring_payment_period_' . $product['id'], 'options'=>get_payment_period_options(), 'class'=>'software_select')) . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Number of Payments' . $output_recurring_number_of_payments_required_asterisk . ':</td>
                                                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'recurring_number_of_payments_' . $product['id'], 'size'=>'3', 'maxlength'=>'9', 'class'=>'software_input_text')) . get_number_of_payments_message() . '</td>
                                                </tr>
                                                ' . $output_start_date_row . '
                                            </table>
                                    </fieldset>
                                </td>
                            </tr>';
                    }

                    $output_products .= 
                        '<tr class="products data row_' . ($row_count % 2) . '">
                            <td class="mobile_left" style="vertical-align: top">' . $edit_button . h($product['name']) . '</td>
                            <td class="mobile_right mobile_width" style="vertical-align: top">' . $product['full_description'] . '</td> 
                            <td class="mobile_left" style="vertical-align: top; text-align: right; white-space: nowrap">' . $output_price . '</td>';

                    if ($device_type == 'mobile') {
                        $output_products .= '<td class="mobile_left" style="vertical-align: top">' . $output_selection_heading . '</td>';
                    }

                    $output_products .=
                            '<td class="mobile_right" style="vertical-align: top; text-align: center; margin-bottom: 2em">' . $output_order_field . '</td>
                        </tr>
                        ' . $output_recurring_schedule_row;

                    $row_count++;
                }
                
                if (($non_donations_exist == true) || ($donations_exist == false)) {
                    $output_price_heading = 'Price';
                } else {
                    $output_price_heading = 'Amount';
                }
                
                // prepare selection column heading
                $output_selection_heading = '';
                
                // if checkbox and quantity selections exist
                if (($checkbox_selections_exist == true) && ($quantity_selections_exist == true)) {
                    $output_selection_heading = 'Select/Qty';
                    
                // else if checkbox selections exist
                } elseif ($checkbox_selections_exist == true) {
                    $output_selection_heading = 'Select';
                    
                // else if quantity selections exist
                } elseif ($quantity_selections_exist == true) {
                    $output_selection_heading = 'Qty';
                    
                // else checkbox and quantity selections do not exist
                } else {
                    $output_selection_heading = '&nbsp;';
                }

                $output_product_table =
                    '<table class="products" style="width: 100%; margin-bottom: 1em">
                        <tr class="heading mobile_hide" style="border: none">
                            <th class="heading_item" style="text-align: left">Item</th>
                            <th class="heading_description" style="text-align: left">Description</th>
                            <th class="heading_price" style="text-align: right">' . $output_price_heading . '</th>
                            <th class="heading_selection">' . $output_selection_heading . '</th>
                        </tr>
                        <tr>
                            ' . $output_products . '
                        </tr>
                    </table>';
                
                // if shipping is on and recipient mode is multi-recipient and there are shippable products, then allow customer to select a recipient
                if ((ECOMMERCE_SHIPPING == TRUE) && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') && ($shippable_products == TRUE)) {
                    $output_product_table .=
                        '<table class="select_ship_to" style="margin-bottom: 1em">
                            <tr id="ship_to_row">
                                <td><strong>Ship to:</strong></td>
                                <td>' . $form->output_field(array('type'=>'select', 'name'=>'ship_to', 'options'=>get_recipient_options(), 'class'=>'software_select')) . '</td>
                            </tr>
                            <tr id="add_name_row">
                                <td>or add name:</td>
                                <td>' . $form->output_field(array('type'=>'text', 'name'=>'add_name', 'maxlength'=>'50', 'size'=>'12', 'class'=>'software_input_text  mobile_text_width')) . ' &nbsp;(e.g. "Tom")</td>
                            </tr>
                        </table>';
                }

                break;
                
            // if product layout is drop-down selection
            case 'drop-down selection':
                $product_options = array();
                
                // loop through all products
                foreach ($products as $product) {
                    // assume that the product is not available until we find out otheriwse
                    $available = FALSE;
                    
                    // if inventory is disabled for the product,
                    // or the inventory quantity is greater than 0,
                    // or the product is allowed to be backordered,
                    // then the product is available so remember that
                    if (
                        ($product['inventory'] == 0)
                        || ($product['inventory_quantity'] > 0)
                        || ($product['backorder'] == 1)
                    ) {
                        $available = TRUE;
                        $available_products_exist = TRUE;
                        
                        // if the the product is shippable take note of it
                        if ($product['shippable'] == 1) {
                            $shippable_products = true;
                        }
                        
                        // if the product is a donation, then take note of it
                        if ($product['selection_type'] == 'donation') {
                            $donations_exist = true;
                        } else {
                            $non_donations_exist = true;
                        }
                    }
                    
                    // assume that the product is not discounted, until we find out otherwise
                    $discounted = FALSE;
                    $discounted_price = '';
                    
                    // if the product is discounted, then prepare to show that
                    if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                        $discounted = TRUE;
                        $discounted_price = $discounted_product_prices[$product['id']];
                    }
                    
                    // set label for option for product in pick list
                    $label = h($product['short_description']) . ' (' . prepare_price_for_output($product['price'], $discounted, $discounted_price, 'plain_text') . ')';
                    
                    // if inventory is enabled for the product,
                    // and the product is out of stock,
                    // and it cannot be backordered,
                    // and there is an out of stock message
                    // then append out of stock message to label
                    if (
                        ($product['inventory'] == 1)
                        && ($product['inventory_quantity'] == 0)
                        && ($product['backorder'] != 1)
                        && ($product['out_of_stock_message'] != '')
                        && ($product['out_of_stock_message'] != '<p></p>')
                    ) {
                        $message = trim(convert_html_to_text($product['out_of_stock_message']));
                        
                        // if the message is longer than 50 characters, then truncate it
                        if (mb_strlen($message) > 50) {
                            $message = mb_substr($message, 0, 50) . '...';
                        }
                        
                        $label .= ' - ' . h($message);
                    }

                    // If the device type is mobile then prepare radio button for product,
                    // because we will output a set of radio buttons for mobile instead of a pick list.
                    if ($device_type == 'mobile') {
                        $checked = '';

                        // If this is the first product that will be listed, then select radio button by default.
                        if ($output_products_for_mobile == '') {
                            $checked = 'checked';
                        }

                        $output_products_for_mobile .= $form->output_field(array('type'=>'radio', 'name'=>'product_id', 'id'=>$product['id'], 'value'=>$product['id'], 'checked'=>$checked, 'class'=>'software_input_radio')) . '<label for="'. $product['id'] . '"> ' . $label . '</label><br />';

                    // Otherwise the device type is desktop so prepare option for pick list.
                    } else {
                        $product_options[$label] = $product['id'];
                    }
                }

                if ($device_type == 'mobile') {
                    $output_products = $output_products_for_mobile;
                } else {
                    $output_products = $form->output_field(array('type'=>'select', 'name'=>'product_id', 'options'=>$product_options, 'class'=>'software_select'));
                }

                $output_recipient_rows = '';
                
                // if shipping is on and recipient mode is multi-recipient and there are shippable products, then allow customer to select a recipient
                if ((ECOMMERCE_SHIPPING == TRUE) && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') && ($shippable_products == TRUE)) {
                    $output_recipient_rows =
                        '<tr id="ship_to_row">
                            <td><strong>Ship to:</strong></td>
                            <td>' . $form->output_field(array('type'=>'select', 'name'=>'ship_to', 'options'=>get_recipient_options(), 'class'=>'software_select')) . '</td>
                        </tr>
                        <tr id="add_name_row">
                            <td>or add name:</td>
                            <td>' . $form->output_field(array('type'=>'text', 'name'=>'add_name', 'maxlength'=>'50', 'size'=>'12', 'class'=>'software_input_text  mobile_text_width')) . ' &nbsp;(e.g. "Tom")</td>
                        </tr>';
                }
                
                $output_quantity_row = '';
                
                // if available products exist and non donation products exist or no donation products exist, then output quantity row
                if (
                    ($available_products_exist == TRUE)
                    &&
                    (
                        ($non_donations_exist == true)
                        || ($donations_exist == false)
                    )
                ) {
                    $output_quantity_row =
                        '<tr id="quantity_row">
                            <td><strong>Qty:</strong></td>
                            <td>' . $form->output_field(array('type'=>'number', 'name'=>'quantity', 'size'=>'3', 'maxlength'=>'9', 'min'=>'1', 'max'=>'999999999', 'value'=>'1', 'class'=>'software_input_text')) . '</td>
                        </tr>';
                }

                $output_product_table =
                    '<table style="margin-bottom: 1em">
                        <tr id="product_row">
                            <td style="vertical-align: top"><strong>Item:</strong></td>
                            <td>' . $output_products . '</td>
                        </tr>
                        ' . $output_recipient_rows . '
                        ' . $output_quantity_row . '
                    </table>';
                
                break;
        }
        
        $output_add_button = '';
        
        // if available products exist, then output add button
        if ($available_products_exist == TRUE) {
            // if a submit button label was entered for the page, then use that
            if ($add_button_label) {
                $output_add_button_label = h($add_button_label);
            
            // else a submit button label could not be found, so use a default label
            } else {
                $output_add_button_label = 'Continue';
            }
            
            $output_add_button = '<input type="submit" name="submit_add" value="' . $output_add_button_label . '" class="software_input_submit_primary add_button" />';
        }
        
        $output_skip_button = '';
        
        // if there is a skip button label, then prepare skip button
        if ($skip_button_label) {
            // if there is an add button, then add separation
            if ($output_add_button != '') {
                $output_skip_button .= '&nbsp;&nbsp;&nbsp;';
            }
            
            $output_skip_button .= '<input type="submit" name="submit_skip" value="' . h($skip_button_label) . '" class="software_input_submit_secondary skip_button" />';
        }
        
        $output =
            $form->output_errors() .
            $form->output_notices() .
            '<form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/order_form.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="page_id" value="' . h($page_id) . '" />
                <input type="hidden" name="require_cookies" value="true" />
                ' . $output_product_table . '
                <br />
                <div style="text-align: right">
                    ' . $output_add_button . $output_skip_button . '
                </div>
            </form>
            ' . get_update_currency_form();
        
        $form->remove();

        return $output;

    // Otherwise the layout is custom.
    } else {

        // Remember if the form was empty so we know whether to prefill fields or not.
        $form_is_empty = $form->is_empty();

        $shippable_products = false;
        $available_products = false;
        $available_non_donations = false;
        $available_donations = false;
        $recipient = false;
        $number_of_payments_message = '';
        $start_date = false;
        $quantity = false;
        $system = '';

        if ($product_layout == 'list') {

            $checkbox_selections = false;
            $quantity_selections = false;

            // If credit/debit card payment method is not enabled or the payment gateway
            // is not ClearCommerce, then remember that we need to deal with the start date.
            if (
                !ECOMMERCE_CREDIT_DEBIT_CARD
                or (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')
            ) {
                $start_date = true;
            }
            
            // loop through products
            foreach ($products as $key => $product) {

                // assume that the product is not available until we find out otheriwse
                $available = false;
                $product['available'] = false;
                
                // if inventory is disabled for the product,
                // or the inventory quantity is greater than 0,
                // or the product is allowed to be backordered,
                // then the product is available so remember that
                if (
                    ($product['inventory'] == 0)
                    || ($product['inventory_quantity'] > 0)
                    || ($product['backorder'] == 1)
                ) {
                    $available = TRUE;
                    $product['available'] = true;
                    $available_products = TRUE;
                    
                    // if the product is shippable take note of it
                    if ($product['shippable'] == 1) {
                        $shippable_products = true;
                    }
                }
                
                // if inventory is enabled for the product,
                // and the product is out of stock,
                // and there is an out of stock message
                // then append out of stock message to full description
                if (
                    ($product['inventory'] == 1)
                    && ($product['inventory_quantity'] == 0)
                    && ($product['out_of_stock_message'] != '')
                    && ($product['out_of_stock_message'] != '<p></p>')
                ) {
                    $product['full_description'] .= ' ' . $product['out_of_stock_message'];
                }
                
                // if mode is edit, then add edit button to images
                if ($editable == TRUE) {
                    $product['full_description'] = add_edit_button_for_images('product', $product['id'], $product['full_description']);
                }
                
                $product['price_info'] = '';

                switch ($product['selection_type']) {
                    case 'checkbox':
                        // assume that the product is not discounted, until we find out otherwise
                        $discounted = FALSE;
                        $discounted_price = '';
                        
                        // if the product is discounted, then prepare to show that
                        if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                            $discounted = TRUE;
                            $discounted_price = $discounted_product_prices[$product['id']];
                        }
                        
                        $product['price_info'] = prepare_price_for_output($product['price'], $discounted, $discounted_price, 'html');
                        
                        // if the product is available then prepare various info
                        if ($available == TRUE) {
                            $checkbox_selections = true;
                            $available_non_donations = true;
                        }

                        break;
                        
                    case 'quantity';
                        // assume that the product is not discounted, until we find out otherwise
                        $discounted = FALSE;
                        $discounted_price = '';
                        
                        // if the product is discounted, then prepare to show that
                        if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                            $discounted = TRUE;
                            $discounted_price = $discounted_product_prices[$product['id']];
                        }
                        
                        $product['price_info'] = prepare_price_for_output($product['price'], $discounted, $discounted_price, 'html');
                        
                        // if the product is available then prepare various info
                        if ($available == TRUE) {
                            $quantity_selections = true;

                            if ($form_is_empty and $product['default_quantity']) {
                                $form->set('product_' . $product['id'], $product['default_quantity']);
                            }
                            
                            $form->set('product_' . $product['id'], 'maxlength', 9);

                            $available_non_donations = true;
                        }
                        
                        break;
                        
                    case 'donation';
                        // if the product is available then prepare various info
                        if ($available == TRUE) {
                            $available_donations = true;
                        }
                        
                        break;
                        
                    case 'autoselect':
                        // assume that the product is not discounted, until we find out otherwise
                        $discounted = FALSE;
                        $discounted_price = '';
                        
                        // if the product is discounted, then prepare to show that
                        if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                            $discounted = TRUE;
                            $discounted_price = $discounted_product_prices[$product['id']];
                        }
                        
                        $product['price_info'] = prepare_price_for_output($product['price'], $discounted, $discounted_price, 'html');
                        
                        // if the product is available then prepare various info
                        if ($available == TRUE) {
                            $available_non_donations = true;
                        }
                        
                        break;
                }
                
                $product['edit'] = '';

                // if page is being viewed in an editable mode, output edit button to allow user to edit product
                if ($editable) {
                    $product['edit'] = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_product.php?id=' . $product['id'] . '&from=pages&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Product: ' . h($product['short_description']) . '">Edit</a>';
                }
                
                $product['recurring_schedule'] = false;
                
                // if the product is available,
                // and the product is a recurring product and the recurring schedule is editable by the customer,
                // then output recurring schedule fieldset
                if (
                    $available and $product['recurring']
                    and $product['recurring_schedule_editable_by_customer']
                ) {

                    $product['recurring_schedule'] = true;

                    // if screen was not just submitted, then prefill recurring schedule fields
                    if ($form_is_empty) {
                        $form->set('recurring_payment_period_' . $product['id'], $product['payment_period']);
                        $form->set('recurring_number_of_payments_' . $product['id'], $product['number_of_payments']);
                        
                        // If we need to deal with the start date, then do that.
                        if ($start_date) {
                            // If the date format is month and then day, then use that format.
                            if (DATE_FORMAT == 'month_day') {
                                $month_and_day_format = 'n/j';

                            // Otherwise the date format is day and then month, so use that format.
                            } else {
                                $month_and_day_format = 'j/n';
                            }

                            // get the default start date based on the number of days that are set for the product
                            $recurring_start_date = date($month_and_day_format . '/Y', time() + (86400 * $product['start']));
                            
                            $form->set('recurring_start_date_' . $product['id'], $recurring_start_date);
                        }
                    }

                    $form->set('recurring_payment_period_' . $product['id'], 'options', get_payment_period_options());
                    
                    // if number of payments is 0, then set to empty string
                    if ($form->get('recurring_number_of_payments_' . $product['id']) == 0) {
                        $form->set('recurring_number_of_payments_' . $product['id'], '');
                    }

                    $form->set('recurring_number_of_payments_' . $product['id'], 'maxlength', 9);

                    // If we have not gotten the number of payments message while processing
                    // a different product, then do that now.
                    if (!$number_of_payments_message) {
                        $number_of_payments_message = trim(get_number_of_payments_message());
                    }
                    
                    // If we need to deal with the start date, then do that.
                    if ($start_date) {
                        $form->set('recurring_start_date_' . $product['id'], 'maxlength', 10);

                        // If this is the first start date that we are dealing with,
                        // then prepare date picker format.
                        if ($system == '') {
                            $system .= get_date_picker_format();
                        }

                        $system .=
                            '<script>
                                software_$("#recurring_start_date_' . $product['id'] . '").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>';
                    }
                    
                }

                $product['image_url'] = '';

                if ($product['image_name'] != '') {
                    $product['image_url'] = PATH . encode_url_path($product['image_name']);
                }

                $product['price'] = $product['price'] / 100;

                if (isset($discounted_product_prices[$product['id']])) {
                    $product['discounted'] = true;
                    $product['discounted_price'] = $discounted_product_prices[$product['id']] / 100;
                } else {
                    $product['discounted'] = false;
                }

                $products[$key] = $product;

            }
        
        // Otherwise the product layout is drop-down selection.
        } else {

            $product_options = array();
            
            // loop through all products
            foreach ($products as $key => $product) {
                // assume that the product is not available until we find out otheriwse
                $available = false;
                $product['available'] = false;
                
                // if inventory is disabled for the product,
                // or the inventory quantity is greater than 0,
                // or the product is allowed to be backordered,
                // then the product is available so remember that
                if (
                    ($product['inventory'] == 0)
                    || ($product['inventory_quantity'] > 0)
                    || ($product['backorder'] == 1)
                ) {
                    $available = TRUE;
                    $product['available'] = true;
                    $available_products = TRUE;
                    
                    // if the the product is shippable take note of it
                    if ($product['shippable'] == 1) {
                        $shippable_products = true;
                    }
                    
                    // if the product is a donation, then take note of it
                    if ($product['selection_type'] == 'donation') {
                        $available_donations = true;
                    } else {
                        $available_non_donations = true;
                    }
                }
                
                // assume that the product is not discounted, until we find out otherwise
                $discounted = FALSE;
                $discounted_price = '';
                
                // if the product is discounted, then prepare to show that
                if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                    $discounted = TRUE;
                    $discounted_price = $discounted_product_prices[$product['id']];
                }
                
                // set label for option for product in pick list
                $label = h($product['short_description']) . ' (' . prepare_price_for_output($product['price'], $discounted, $discounted_price, 'plain_text') . ')';
                
                // if inventory is enabled for the product,
                // and the product is out of stock,
                // and it cannot be backordered,
                // and there is an out of stock message
                // then append out of stock message to label
                if (
                    ($product['inventory'] == 1)
                    && ($product['inventory_quantity'] == 0)
                    && ($product['backorder'] != 1)
                    && ($product['out_of_stock_message'] != '')
                    && ($product['out_of_stock_message'] != '<p></p>')
                ) {
                    $message = trim(convert_html_to_text($product['out_of_stock_message']));
                    
                    // if the message is longer than 50 characters, then truncate it
                    if (mb_strlen($message) > 50) {
                        $message = mb_substr($message, 0, 50) . '...';
                    }
                    
                    $label .= ' - ' . h($message);
                }

                $product_options[$label] = $product['id'];

                $product['image_url'] = '';

                if ($product['image_name'] != '') {
                    $product['image_url'] = PATH . encode_url_path($product['image_name']);
                }

                $product['price'] = $product['price'] / 100;

                if (isset($discounted_product_prices[$product['id']])) {
                    $product['discounted'] = true;
                    $product['discounted_price'] = $discounted_product_prices[$product['id']] / 100;
                } else {
                    $product['discounted'] = false;
                }

                $products[$key] = $product;

            }

            $form->set('product_id', 'options', $product_options);

            // If there are available products that are not donations,
            // then show quantity row.
            if ($available_non_donations) {
                $quantity = true;
            }

        }

        $attributes = '';
        $number_of_payments_required = false;

        if ($available_products) {

            $attributes =
                'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/order_form.php" ' .
                'method="post"';
            
            // if credit/debit card is enabled as a payment method and the payment gateway is set to ClearCommerce or First Data Global Gateway
            // then output asterisk to show that number of payments is required
            if (
                (ECOMMERCE_CREDIT_DEBIT_CARD == true)
                &&
                (
                    (ECOMMERCE_PAYMENT_GATEWAY == 'ClearCommerce')
                    || (ECOMMERCE_PAYMENT_GATEWAY == 'First Data Global Gateway')
                )
            ) {
                $number_of_payments_required = true;
            }

            // If multi-recipient shipping is enabled and there are shippable products
            // that are available, then allow customer to select a recipient.
            if (
                ECOMMERCE_SHIPPING
                and (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient')
                and $shippable_products
            ) {
                $recipient = true;

                $form->set('ship_to', 'options', get_recipient_options());
                $form->set('add_name', 'maxlength', 50);
            }

            // If an add button label was not entered for the page, then set default label.
            if ($add_button_label == '') {
                $add_button_label = 'Continue';
            }

            $system .=
                get_token_field() . '
                <input type="hidden" name="page_id" value="' . h($page_id) . '">
                <input type="hidden" name="require_cookies" value="true">';
        }

        $skip_button_url = '';

        if (($skip_button_label != '') and $skip_button_next_page_id) {
            $skip_button_url = PATH . encode_url_path(get_page_name($skip_button_next_page_id));
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
            'edit' => $editable,
            'available_products' => $available_products,
            'available_non_donations' => $available_non_donations,
            'available_donations' => $available_donations,
            'form' => $form,
            'attributes' => $attributes,
            'product_layout' => $product_layout,
            'checkbox_selections' => $checkbox_selections,
            'quantity_selections' => $quantity_selections,
            'products' => $products,
            'number_of_payments_message' => $number_of_payments_message,
            'number_of_payments_required' => $number_of_payments_required,
            'start_date' => $start_date,
            'recipient' => $recipient,
            'quantity' => $quantity,
            'add_button_label' => $add_button_label,
            'skip_button_url' => $skip_button_url,
            'skip_button_label' => $skip_button_label,
            'system' => $system,
            'currency_symbol' => VISITOR_CURRENCY_SYMBOL,
            'currency_code' => VISITOR_CURRENCY_CODE_FOR_OUTPUT,
            'currency' => $currency,
            'currency_attributes' => $currency_attributes,
            'currencies' => $currencies,
            'currency_system' => $currency_system));

        $content = $form->prepare($content);

        $form->remove();

        return $content;

    }

}