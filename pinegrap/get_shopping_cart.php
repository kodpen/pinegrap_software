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

function get_shopping_cart($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];
    $folder_id_for_default_value = $properties['folder_id_for_default_value'];

    $properties = get_page_type_properties($page_id, 'shopping cart');

    $shopping_cart_label = $properties['shopping_cart_label'];
    $quick_add_label = $properties['quick_add_label'];
    $quick_add_product_group_id = $properties['quick_add_product_group_id'];
    $product_description_type = $properties['product_description_type'];
    $special_offer_code_label = $properties['special_offer_code_label'];
    $special_offer_code_message = $properties['special_offer_code_message'];
    $update_button_label = $properties['update_button_label'];
    $checkout_button_label = $properties['checkout_button_label'];
    $next_page_id_with_shipping = $properties['next_page_id_with_shipping'];
    $next_page_id_without_shipping = $properties['next_page_id_without_shipping'];

    global $user;

    $layout_type = get_layout_type($page_id);
    
    // store page id for shopping cart page in case we need to direct the user to the page sometime in the future
    $_SESSION['ecommerce']['shopping_cart_page_id'] = $page_id;
    
    // unset express order page id if one exists, so we don't forward user to that page in the future
    unset($_SESSION['ecommerce']['express_order_page_id']);
    
    // store page id for shipping address and arrival page in case we need to direct the user to the page sometime in the future
    $_SESSION['ecommerce']['shipping_address_and_arrival_page_id'] = $next_page_id_with_shipping;
    
    // store page id for billing information page in case we need to direct the user to the page sometime in the future
    $_SESSION['ecommerce']['billing_information_page_id'] = $next_page_id_without_shipping;
    
    $form = new liveform('shopping_cart');
    
    // If a reference code was passed in the query string, then retrieve order.

    if ($_GET['r'] || $_GET['reference_code']) {

        require_once(dirname(__FILE__) . '/retrieve_order.php');

        $response = retrieve_order(array('order_label' => mb_strtolower($shopping_cart_label)));

        if ($response['status'] == 'success' and $response['message']) {
            $form->add_notice(h($response['message']));
        } else if ($response['status'] == 'error') {
            $form->mark_error('retrieve_order', h($response['message']));
        }
    }
    
    // if there is a special offer code in the session and an order has been created, prepare to add special offer code to order
    if ($_SESSION['ecommerce']['special_offer_code'] && $_SESSION['ecommerce']['order_id']) {
        // if an offer exists for special offer code, then add special offer code to order
        if (get_offer_code_for_special_offer_code($_SESSION['ecommerce']['special_offer_code'])) {
            $query =
                "UPDATE orders
                SET special_offer_code = '" . escape($_SESSION['ecommerce']['special_offer_code']) . "'
                WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        // clear special offer code from session
        unset($_SESSION['ecommerce']['special_offer_code']);
    }

    // Check min and max quantity requirements for products.
    check_quantity($form);
    
    // check inventory for order items in order to make sure they are still all in stock
    check_inventory($form);
    
    // check reservations for calendar events in order to make sure they are still valid and available
    check_reservations($form);
    
    update_order_item_prices();
    
    $result = apply_offers_to_cart();
    
    $pending_offers = $result['pending_offers'];
    $upsell_offers = $result['upsell_offers'];

    // get various order data that we will use later
    $query = "SELECT
                special_offer_code,
                reference_code,
                discount_offer_id,
                offline_payment_allowed
             FROM orders
             WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $special_offer_code = $row['special_offer_code'];
    $reference_code = $row['reference_code'];
    $discount_offer_id = $row['discount_offer_id'];
    $offline_payment_allowed = $row['offline_payment_allowed'];

    if ($layout_type == 'system') {
    
        // get all ship tos
        $query = "SELECT DISTINCT ship_to_id FROM order_items WHERE order_id = '" . escape($_SESSION['ecommerce']['order_id']) . "' ORDER BY ship_to_id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $ship_tos = array();
        
        // foreach ship to, add ship to to array
        while ($row = mysqli_fetch_assoc($result)) {
            $ship_tos[] = $row['ship_to_id'];
        }
        
        $output_pending_offers = '';

        $count_offers = 1;
        
        // loop through pending offers in order to output them
        foreach ($pending_offers as $pending_offer) {
            $count_offer_actions = 1;
            
            // check if this offer has more than 1 offer action that adds a product
            // if there is more than 1 offer action for this pending offer,
            // then that means more than 1 product is being added for this offer
            // so we need to add extra info to the offer description with the action name,
            // so the customer understands which product will be added
            $query =
                "SELECT
                    COUNT(offers_offer_actions_xref.offer_action_id)
                FROM offers_offer_actions_xref
                LEFT JOIN offer_actions ON offers_offer_actions_xref.offer_action_id = offer_actions.id
                WHERE 
                    (offers_offer_actions_xref.offer_id = '" . $pending_offer['id'] . "')
                    AND (offer_actions.type = 'add product')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            
            $multiple_actions = FALSE;
            
            // if this offer has multiple offer actions, then remember that
            if ($row[0] > 1) {
                $multiple_actions = TRUE;
            }
            
            // loop through the offers actions for this pending offer, in order to output pending offer actions
            foreach ($pending_offer['offer_actions'] as $offer_action) {
                // if product is not shippable
                if (
                    ($offer_action['add_product_shippable'] == 0)
                    || (ECOMMERCE_SHIPPING == false)
                    || ((ECOMMERCE_SHIPPING == true) && (ECOMMERCE_RECIPIENT_MODE == 'single recipient'))
                ) {
                    $output_select_recipient = '&nbsp;';
                
                // else product is shippable
                } else {
                    // if only certain recipients are allowed for this offer action
                    if ($offer_action['allowed_recipients']) {
                        $recipient_options = array();
                        $recipient_options[''] = '';
                        
                        foreach ($offer_action['allowed_recipients'] as $ship_to_id) {
                            $query = "SELECT ship_to_name FROM ship_tos WHERE id = '$ship_to_id'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);

                            $recipient_options[$row['ship_to_name']] = $row['ship_to_name'];
                        }
                        
                        $output_select_recipient = '<strong>Ship to</strong> ' . $form->output_field(array('type'=>'select', 'name'=>'pending_offer_' . $pending_offer['id'] . '_' . $offer_action['id'] . '_ship_to', 'options'=>$recipient_options, 'class'=>'software_select'));
                        
                    // else all recipients are allowed for this offer action
                    } else {
                        initialize_recipients();
                        
                        $recipient_options = array();
                        $recipient_options[''] = '';
                        $recipient_options['myself'] = 'myself';

                        // if there is at least one recipient stored in session
                        if ($_SESSION['ecommerce']['recipients']) {
                            // loop through all recipients to build recipient options
                            foreach ($_SESSION['ecommerce']['recipients'] as $recipient) {
                                $recipient_options[$recipient] = $recipient;
                            }
                        }
                        
                        $output_select_recipient = '<strong>Ship to</strong> ' . $form->output_field(array('type'=>'select', 'name'=>'pending_offer_' . $pending_offer['id'] . '_' . $offer_action['id'] . '_ship_to', 'options'=>$recipient_options, 'class'=>'software_select')) . ' ' . $form->output_field(array('type'=>'text', 'name'=>'pending_offer_' . $pending_offer['id'] . '_' . $offer_action['id'] . '_add_name', 'value'=>'or add name', 'size'=>'15', 'maxlength'=>'50', 'class'=>'software_input_text', 'onfocus'=>'if (this.value == \'or add name\') {this.value = \'\'}'));
                    }
                }
                
                $bottom_border = '';
                
                // if there are upsell offers or this is not the last pending offer action
                if (
                    ($upsell_offers)
                    || ($count_offers < count($pending_offers))
                    || ($count_offer_actions < count($pending_offer['offer_actions']))
                ) {
                    $bottom_border = '; border-bottom: #dddddd 1px solid';
                }
                
                $output_offer_action_name = '';
                
                // if this offer has multiple actions, then output offer action name so the customer will know which product he/she is adding
                if ($multiple_actions == TRUE) {
                    $output_offer_action_name = ' (' . h($offer_action['name']) . ')';
                }
                
                $output_pending_offers .=
                    '<tr>
                        <td style="' . $bottom_border . '"><span class="software_highlight"><strong>' . h($pending_offer['description']) . $output_offer_action_name . '</strong></span></td>
                        <td style="white-space: nowrap' . $bottom_border . '">' . $output_select_recipient . '</td>
                        <td style="text-align: center' . $bottom_border . '"><input type="submit" name="add_pending_offer_' . $pending_offer['id'] . '_' . $offer_action['id'] . '" value="Add" class="software_input_submit_small_primary" /><br /></td>
                    </tr>';
                
                $count_offer_actions++;
            }
            
            $count_offers++;
        }
        
        $output_upsell_offers = '';
        
        $count = 1;
        
        foreach ($upsell_offers as $upsell_offer) {
            // if an upsell message was entered, use that
            if ($upsell_offer['upsell_message']) {
                $output_upsell_message = h($upsell_offer['upsell_message']);
                
            // else an upsell message was not entered, so use offer description
            } else {
                $output_upsell_message = h($upsell_offer['description']);
            }
            
            // if an upsell action page was selected for this upsell offer
            if ($upsell_offer['upsell_action_page_id']) {
                if ($upsell_offer['upsell_action_button_label']) {
                    $output_upsell_action_button_label = h($upsell_offer['upsell_action_button_label']);
                } else {
                    $output_upsell_action_button_label = 'More Info';
                }
                
                $output_upsell_action_link = '<a href="' . OUTPUT_PATH . get_page_name($upsell_offer['upsell_action_page_id']) . '" class="software_button_small_secondary">' . $output_upsell_action_button_label . '</a>';
            } else {
                $output_upsell_action_link = '&nbsp;';
            }
            
            $bottom_border = '';
            
            if ($count < count($upsell_offers)) {
                $bottom_border = '; border-bottom: #dddddd 1px solid';
            }
            
            $output_upsell_offers .=
                '<tr>
                    <td colspan="2" style="' . $bottom_border . '">' . $output_upsell_message . '</td>
                    <td style="text-align: center; white-space: nowrap' . $bottom_border . '">' . $output_upsell_action_link . '</td>
                </tr>';
                
            $count++;
        }

        if ($output_pending_offers || $output_upsell_offers) {
            $output_special_offers =
                '<form class="special_offers" action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shopping_cart.php" method="post" style="margin: 0px 0px 15px 0px">
                    ' . get_token_field() . '
                    <input type="hidden" name="page_id" value="' . $page_id . '" />
                    <input type="hidden" name="pending_offers" value="true" />
                    <input type="hidden" name="require_cookies" value="true" />
                    <fieldset class="software_fieldset">
                        <legend class="software_legend">Special Offers</legend>
                            <table cellspacing="0" cellpadding="7" border="0" style="width: 100%">
                                ' . $output_pending_offers . '
                                ' . $output_upsell_offers . '
                            </table>
                    </fieldset>
                </form>';
        }
        
        /* begin: prepare quick add */
        
        $output_quick_add = '';
        
        // If there is a quick add product group and it is enabled,
        // then prepare quick add.
        if (
            $quick_add_product_group_id
            and db_value("SELECT enabled FROM product_groups WHERE id = '" . e($quick_add_product_group_id) . "'")
        ) {
            // get all products that are in quick add product group
            $query =
                "SELECT
                    products.id,
                    products.name,
                    products.short_description,
                    products.price,
                    products.shippable,
                    products.selection_type,
                    products.default_quantity,
                    products.inventory,
                    products.inventory_quantity,
                    products.backorder,
                    products.out_of_stock_message
                FROM products_groups_xref
                LEFT JOIN products ON products.id = products_groups_xref.product
                WHERE
                    (products_groups_xref.product_group = '$quick_add_product_group_id')
                    AND (products.enabled = '1')
                ORDER BY products_groups_xref.sort_order, products.name";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if there is at least one product in quick add product group, prepare quick add area
            if (mysqli_num_rows($result) > 0) {
                // get prices for products that have been discounted by offers, so we can show the discounted price
                $discounted_product_prices = get_discounted_product_prices();
                
                // initialize variables for determining which type of products are in quick add pick list
                $quantity_products = false;
                $donation_products = false;
                $recipient_required_products = false;
                
                $output_quick_add_products_array = '';
                
                // hide all quick add rows until we determine which need to be displayed
                $quick_add_quantity_row_style = 'display: none';
                $quick_add_amount_row_style = 'display: none';
                $quick_add_ship_to_row_style = 'display: none';
                $quick_add_add_name_row_style = 'display: none';
                
                $quick_add_product_id_options = array();
                $quick_add_product_id_options[''] = '';
                
                // assume that available products do not exist until we find out otherwise
                $available_products_exist = FALSE;
                
                // loop through all products in quick add product group to build options for selection drop-down
                while ($row = mysqli_fetch_assoc($result)) {
                    $id = $row['id'];
                    $name = $row['name'];
                    $short_description = $row['short_description'];
                    $price = $row['price'];
                    $shippable = $row['shippable'];
                    $selection_type = $row['selection_type'];
                    $default_quantity = $row['default_quantity'];
                    $inventory = $row['inventory'];
                    $inventory_quantity = $row['inventory_quantity'];
                    $backorder = $row['backorder'];
                    $out_of_stock_message = $row['out_of_stock_message'];
                    
                    // assume that a recipient is not required until we find out otherwise
                    $recipient_required = 'false';
                    
                    // if the product is available by looking at inventory
                    if (
                        ($inventory == 0)
                        || ($inventory_quantity > 0)
                        || ($backorder == 1)
                    ) {
                        $available_products_exist = TRUE;
                        
                        // if product has a quantity selection type, then remember that
                        if ($selection_type == 'quantity') {
                            $quantity_products = true;
                        }
                        
                        // if product has a donation selection type, then remember that
                        if ($selection_type == 'donation') {
                            $donation_products = true;
                        }
                        
                        // if recipient is required to be selected for this product, prepare that value for JavaScript array
                        if ((ECOMMERCE_SHIPPING == true) && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') && ($shippable == 1)) {
                            $recipient_required = 'true';
                            $recipient_required_products = true;
                        }
                    }
                    
                    $output_quick_add_products_array .= 'quick_add_products[' . $id . '] = new Array("' . $selection_type . '", ' . $default_quantity . ', ' . $recipient_required . ');' . "\n";
                    
                    // if this product is currently selected, figure out which quick add rows need to be displayed for this product
                    if ($id == $form->get_field_value('quick_add_product_id')) {                    
                        // if product has a quantity selection type, then show quantity row
                        if ($selection_type == 'quantity') {
                            $quick_add_quantity_row_style = '';
                        }
                        
                        // if product has a donation selection type, then show amount row
                        if ($selection_type == 'donation') {
                            $quick_add_amount_row_style = '';
                        }
                        
                        // if a recipient is required to be selected, then show ship to and add name rows
                        if ($recipient_required == 'true') {
                            $quick_add_ship_to_row_style = '';
                            $quick_add_add_name_row_style = '';
                        }
                    }
                    
                    if ($name) {
                        $output_name = h($name) . ' - ';
                    } else {
                        $output_name = '';
                    }
                    
                    // if product's selection type is not donation, then prepare to output price
                    if ($selection_type != 'donation') {
                        // assume that the product is not discounted, until we find out otherwise
                        $discounted = FALSE;
                        $discounted_price = '';

                        // if the product is discounted, then prepare to show that
                        if (isset($discounted_product_prices[$id]) == TRUE) {
                            $discounted = TRUE;
                            $discounted_price = $discounted_product_prices[$id];
                        }
                        
                        $output_price = ' (' . prepare_price_for_output($price, $discounted, $discounted_price, 'plain_text') . ')';
                        
                    } else {
                        $output_price = '';
                    }
                    
                    $output_label = $output_name . h($short_description) . $output_price;
                    
                    // if the product is out of stock then add message to label
                    if (
                        ($inventory == 1)
                        && ($inventory_quantity == 0)
                        && ($out_of_stock_message != '')
                        && ($out_of_stock_message != '<p></p>')
                    ) {
                        $out_of_stock_message = trim(convert_html_to_text($out_of_stock_message));
                        
                        // if the message is longer than 50 characters, then truncate it
                        if (mb_strlen($out_of_stock_message) > 50) {
                            $out_of_stock_message = mb_substr($out_of_stock_message, 0, 50) . '...';
                        }
                        
                        $output_label .= ' - ' . h($out_of_stock_message);
                    }

                    // If the device type is mobile then prepare radio button for product,
                    // because we will output a set of radio buttons for mobile instead of a pick list.
                    if ($device_type == 'mobile') {
                        $output_products_for_mobile .= $form->output_field(array('type'=>'radio', 'name'=>'quick_add_product_id', 'id'=>'quick_add_product_id_' . $id, 'value'=>$id, 'class'=>'software_input_radio', 'required' => 'true', 'onclick'=>'change_quick_add_product_id(' . $id . ')')) . '<label for="quick_add_product_id_'. $id . '"> ' . $output_label . '</label><br />';

                    // Otherwise the device type is desktop so prepare option for pick list.
                    } else {
                        $quick_add_product_id_options[$output_label] = $id;
                    }
                }

                // if there are products that require a recipient to be selected, prepare ship to and add name rows
                if ($recipient_required_products == true) {
                    initialize_recipients();
                    
                    $quick_add_ship_to_options = array();
                    $quick_add_ship_to_options[''] = '';
                    $quick_add_ship_to_options['myself'] = 'myself';

                    // if there is at least one recipient stored in session
                    if ($_SESSION['ecommerce']['recipients']) {
                        // loop through all recipients to build recipient options
                        foreach ($_SESSION['ecommerce']['recipients'] as $recipient) {
                            $quick_add_ship_to_options[$recipient] = $recipient;
                        }
                    }
                    
                    $output_ship_to_and_add_name_rows =
                        '<tr id="quick_add_ship_to_row" style="' . $quick_add_ship_to_row_style . '">
                            <td><strong>Ship to:</strong></td>
                            <td>' . $form->output_field(array('type'=>'select', 'name'=>'quick_add_ship_to', 'options'=>$quick_add_ship_to_options, 'class'=>'software_select')) . '</td>
                        </tr>
                        <tr id="quick_add_add_name_row" style="' . $quick_add_add_name_row_style . '">
                            <td>or add name:</td>
                            <td>' . $form->output_field(array('type'=>'text', 'name'=>'quick_add_add_name', 'maxlength'=>'50', 'size'=>'12', 'class'=>'software_input_text  mobile_text_width')) . ' &nbsp;(e.g. "Tom")</td>
                        </tr>';
                }
                
                // if there are products that require a quantity to be selected, prepare quantity row
                if ($quantity_products == true) {
                    $output_quantity_row =
                        '<tr id="quick_add_quantity_row" style="' . $quick_add_quantity_row_style . '">
                            <td><strong>Qty:</strong></td>
                            <td>' . $form->output_field(array('type'=>'number', 'id'=>'quick_add_quantity', 'name'=>'quick_add_quantity', 'value'=>'1', 'size'=>'3', 'maxlength'=>'9', 'min'=>'1', 'max'=>'999999999', 'class'=>'software_input_text')) . '</td>
                        </tr>';
                }
                
                // if there are donation products, prepare amount row for output
                if ($donation_products == true) {
                    $output_amount_row =
                        '<tr id="quick_add_amount_row" style="' . $quick_add_amount_row_style . '">
                            <td><strong>Amount:</strong></td>
                            <td>' . VISITOR_CURRENCY_SYMBOL . $form->output_field(array('type'=>'text', 'name'=>'quick_add_amount', 'size'=>'5', 'class'=>'software_input_text')) . h(VISITOR_CURRENCY_CODE_FOR_OUTPUT) . '</td>
                        </tr>';
                }
                
                $output_add_button_row = '';
                
                // if there are available products, then output add button
                if ($available_products_exist == TRUE) {
                    $output_add_button_row =
                        '<tr>
                            <td>&nbsp;</td>
                            <td><input type="submit" name="submit" value="Add" class="software_input_submit_small_secondary mobile_right" /></td>
                        </tr>';
                }

                // output pick list (or radio buttons if mobile)
                if ($device_type == 'mobile') {
                    $output_products = $output_products_for_mobile;
                } else {
                    $output_products = $form->output_field(array('type'=>'select', 'name'=>'quick_add_product_id', 'options'=>$quick_add_product_id_options, 'class'=>'software_select', 'required' => 'true', 'onchange'=>'change_quick_add_product_id(this.options[this.selectedIndex].value)'));
                }
                
                $output_quick_add =
                    '<script type="text/javascript" language="JavaScript 1.2">
                        var quick_add_products = new Array();
                        ' . $output_quick_add_products_array . '
                    </script>
                    <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shopping_cart.php" method="post" style="margin: 0px 0px 15px 0px">
                        ' . get_token_field() . '
                        <input type="hidden" name="page_id" value="' . $page_id . '" />
                        <input type="hidden" name="quick_add" value="true" />
                        <input type="hidden" name="require_cookies" value="true" />
                        <fieldset class="software_fieldset">
                            <legend class="software_legend">' . h($quick_add_label) . '</legend>
                                <table class="quick_add">
                                    <tr>
                                        <td style="font-weight: bold; vertical-align: top">Item:</td>
                                        <td>' . $output_products . '</td>
                                    </tr>
                                    ' . $output_ship_to_and_add_name_rows . '
                                    ' . $output_quantity_row . '
                                    ' . $output_amount_row . '
                                    ' . $output_add_button_row . '
                                </table>
                        </fieldset>
                    </form>';
            }
        }
        
        /* end: prepare quick add */
        
        // if there are ship tos, then there are products in the cart, so output cart
        if ($ship_tos) {
            // initialize array to store wysiwyg fields for product forms
            $wysiwyg_fields = array();
            
            // initialize variables before we figure out which types of products exist
            $taxable_products_exist = false;
            $shippable_products_exist = false;
            $recurring_products_exist = false;
            
            // intialize payment periods array that we will use to store data about payment periods for recurring products
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
                    'exists' => false,
                    'subtotal' => 0);
            }
            
            $applied_offers = array();
            
            // create array for storing inventory quantity for products,
            // so we can keep track of remaining inventory quantity as we loop through order items,
            // so that we can determine if out of stock message should be show for an order item
            $inventory_quantities = array();
            
            // loop through all ship tos
            foreach ($ship_tos as $ship_to_id) {
                // get ship to info for this ship to id
                $query =
                    "SELECT
                        ship_to_name,
                        offer_id
                    FROM ship_tos
                    WHERE id = '$ship_to_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $ship_to_name = $row['ship_to_name'];
                $ship_to_offer_id = $row['offer_id'];

                // get all order items in cart for this ship to
                $query =
                    "SELECT
                        order_items.id,
                        order_items.product_id,
                        order_items.product_name,
                        order_items.quantity,
                        order_items.price,
                        order_items.offer_id,
                        order_items.added_by_offer,
                        order_items.discounted_by_offer,
                        order_items.recurring_payment_period,
                        order_items.recurring_number_of_payments,
                        order_items.recurring_start_date,
                        order_items.calendar_event_id,
                        order_items.recurrence_number,
                        products.short_description,
                        products.full_description,
                        products.details,
                        products.inventory,
                        products.inventory_quantity,
                        products.out_of_stock_message,
                        products.price as product_price,
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
                        products.form_name,
                        products.form_label_column_width,
                        products.form_quantity_type,
                        products.submit_form,
                        products.submit_form_custom_form_page_id,
                        products.submit_form_update,
                        products.submit_form_update_where_field,
                        products.submit_form_update_where_value,
                        products.submit_form_quantity_type
                    FROM order_items
                    LEFT JOIN products ON order_items.product_id = products.id
                    WHERE order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "' AND order_items.ship_to_id = '$ship_to_id'
                    ORDER BY order_items.id";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $order_items = array();
                
                // foreach order item in cart, add order item to array
                while ($row = mysqli_fetch_assoc($result)) {
                    $order_items[] = $row;
                }
                
                $output_products = '';
                $output_recurring_products = '';
                $row_count = 1;
                
                // initialize variables to determine which types of products are being displayed
                // these variables will be used later to determine column heading labels
                $non_donations_exist_in_non_recurring = false;
                $non_donations_exist_in_recurring = false;
                $donations_exist_in_non_recurring = false;
                $donations_exist_in_recurring = false;
                
                // foreach order item in cart
                foreach ($order_items as $order_item) {
                    $order_item_id = $order_item['id'];
                    $product_id = $order_item['product_id'];
                    $name = $order_item['product_name'];
                    $quantity = $order_item['quantity'];
                    $product_price = $order_item['price'] / 100;
                    $offer_id = $order_item['offer_id'];
                    $added_by_offer = $order_item['added_by_offer'];
                    $discounted_by_offer = $order_item['discounted_by_offer'];
                    $recurring_payment_period = $order_item['recurring_payment_period'];
                    $recurring_number_of_payments = $order_item['recurring_number_of_payments'];
                    $recurring_start_date = $order_item['recurring_start_date'];
                    $calendar_event_id = $order_item['calendar_event_id'];
                    $recurrence_number = $order_item['recurrence_number'];
                    $short_description = $order_item['short_description'];
                    $full_description = $order_item['full_description'];
                    $details = $order_item['details'];
                    $inventory = $order_item['inventory'];
                    $inventory_quantity = $order_item['inventory_quantity'];
                    $out_of_stock_message = $order_item['out_of_stock_message'];
                    $original_product_price = $order_item['product_price'] / 100;
                    $recurring = $order_item['recurring'];
                    $recurring_schedule_editable_by_customer = $order_item['recurring_schedule_editable_by_customer'];
                    $start = $order_item['start'];
                    $number_of_payments = $order_item['number_of_payments'];
                    $payment_period = $order_item['payment_period'];
                    $selection_type = $order_item['selection_type'];
                    $taxable = $order_item['taxable'];
                    $shippable = $order_item['shippable'];
                    $gift_card = $order_item['gift_card'];
                    $form_enabled = $order_item['form'];
                    $form_name = $order_item['form_name'];
                    $form_label_column_width = $order_item['form_label_column_width'];
                    $form_quantity_type = $order_item['form_quantity_type'];

                    // If the product description type for this page is full description, then use the full description.
                    if ($product_description_type == 'full_description') {
                        $output_description = $full_description;

                    // Otherwise the product description type is short description, so use the short description.
                    } else {
                        $output_description = h($short_description);
                    }
                    
                    // if inventory is enabled for the product and there is an out of stock message, then determine if we should show out of stock message
                    if (
                        ($inventory == 1)
                        && ($out_of_stock_message != '')
                        && ($out_of_stock_message != '<p></p>')
                    ) {
                        // if the initial inventory quantity for this product has not already been set, then set it
                        if (isset($inventory_quantities[$product_id]) == FALSE) {
                            $inventory_quantities[$product_id] = $inventory_quantity;
                        }
                        
                        // if the quantity of this order item is greater than the inventory quantity, then show out of stock message and set inventory quantity to 0
                        if ($quantity > $inventory_quantities[$product_id]) {
                            $output_description .= ' ' . $out_of_stock_message;
                            $inventory_quantities[$product_id] = 0;
                            
                        // else the quantity of this order items is less than the inventory quantity, so decrement inventory quantity,
                        // so when we look at more products we have an accurate inventory quantity for what has been used so far
                        } else {
                            $inventory_quantities[$product_id] = $inventory_quantities[$product_id] - $quantity;
                        }
                    }
                    
                    // if mode is edit and full description is being shown, add edit button for images
                    if (($editable == TRUE) && ($product_description_type == 'full_description')) {
                        $output_description = add_edit_button_for_images('product', $product_id, $output_description);
                    }
                    
                    // if calendars is enabled and this order item is for a calendar event reservation, then add calendar event name and date and time range to description
                    if ((CALENDARS == TRUE) && ($calendar_event_id != 0)) {
                        $calendar_event = get_calendar_event($calendar_event_id, $recurrence_number);
                        
                        $output_description .=
                            '<p>
                                ' . h($calendar_event['name']) . '<br />
                                ' . $calendar_event['date_and_time_range'] . '
                            </p>';
                    }
                    
                    if ($taxable == 1) {
                        $taxable_products_exist = true;
                    }
                    
                    if ($shippable == 1) {
                        $shippable_products_exist = true;
                    }
                    
                    // if order item is a donation
                    if ($selection_type == 'donation') {
                        // output an empty quantity, because a donation does not really have a quantity
                        $output_quantity = '';
                    
                    // else if order item was added by offer
                    } elseif ($added_by_offer == 1)    {
                        // output only the quantity value, not quantity field
                        $output_quantity = $quantity;
                    
                    // else order item is a normal order item
                    } else {
                        // output quantity field
                        $output_quantity = '<input name="quantity[' . $order_item_id . ']" type="text" size="2" maxlength="9" value="' . $quantity . '" />';
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
                    
                    $output_total_price = prepare_price_for_output($total_price * 100, FALSE, $discounted_price = '', 'html');
                    
                    // if order item is a donation
                    if ($selection_type == 'donation') {
                        $output_donation_amount_text_box = VISITOR_CURRENCY_SYMBOL . '<input type="text" name="donations[' . $order_item_id . ']" value="' . number_format(get_currency_amount($total_price, VISITOR_CURRENCY_EXCHANGE_RATE), 2, '.', ',') . '" size="5" class="software_input_text" style="text-align: right" />' . h(VISITOR_CURRENCY_CODE_FOR_OUTPUT);
                    }
                    
                    $output_remove = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/remove_item_from_cart.php?order_item_id=' . $order_item_id . '&screen=shopping_cart&send_to=' . h(urlencode(get_request_uri())) . get_token_query_string_field() . '" class="software_button_small_secondary remove_button">X</a>';
                    
                    // assume that we don't need to output a recurring schedule fieldset, until we find out otherwise
                    $output_recurring_schedule_fieldset = '';
                    
                    // if the product is a recurring product and the recurring schedule is editable by the customer, then output recurring schedule fieldset
                    if (($recurring == 1) && ($recurring_schedule_editable_by_customer == 1)) {
                        // if screen was not just submitted, then prefill recurring schedule fields
                        if (($form->field_in_session('submit_update') == false) && ($form->field_in_session('submit_checkout') == false)) {
                            // if the payment period has not been set for this order item yet, then use the default scheduling values from the product
                            if ($recurring_payment_period == '') {
                                $form->assign_field_value('recurring_payment_period_' . $order_item_id, $payment_period);
                                $form->assign_field_value('recurring_number_of_payments_' . $order_item_id, $number_of_payments);
                                
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
                                    $recurring_start_date = date($month_and_day_format . '/Y', time() + (86400 * $start));
                                    
                                    $form->assign_field_value('recurring_start_date_' . $order_item_id, $recurring_start_date);
                                }
                                
                            // else the payment period has been set for this order item, so use the values that the customer has set
                            } else {
                                $form->assign_field_value('recurring_payment_period_' . $order_item_id, $recurring_payment_period);
                                $form->assign_field_value('recurring_number_of_payments_' . $order_item_id, $recurring_number_of_payments);
                                
                                // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then prepare start date
                                if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                                    $form->assign_field_value('recurring_start_date_' . $order_item_id, prepare_form_data_for_output($recurring_start_date, 'date'));
                                }
                            }
                        }
                        
                        $output_recurring_number_of_payments_required_asterisk = '';
                        $number_of_payments_required = '';
                        
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
                            $number_of_payments_required = 'true';
                        }
                        
                        // if number of payments is 0, then set to empty string
                        if ($form->get_field_value('recurring_number_of_payments_' . $order_item_id) == 0) {
                            $form->assign_field_value('recurring_number_of_payments_' . $order_item_id, '');
                        }
                        
                        // determine if start row should be outputted
                        $output_start_date_row = '';
                        
                        // if credit/debit card payment method is not enabled or the payment gateway is not ClearCommerce, then output start date row
                        if ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')) {
                            $output_start_date_row =
                                '<tr>
                                    <td>Start Date*:</td>
                                    <td>
                                        ' . $form->output_field(array('type'=>'text', 'id' => 'recurring_start_date_' . $order_item_id, 'name'=>'recurring_start_date_' . $order_item_id, 'size'=>'10', 'maxlength'=>'10', 'class'=>'software_input_text', 'required' => 'true')) . '
                                        ' . get_date_picker_format() . '
                                        <script>
                                            software_$("#recurring_start_date_' . $order_item_id . '").datepicker({
                                                dateFormat: date_picker_format
                                            });
                                        </script>
                                    </td>
                                </tr>';
                        }
                        
                        $output_recurring_schedule_fieldset =
                            '<fieldset class="software_fieldset">
                                <legend class="software_legend">Payment Schedule</legend>
                                <table>
                                    <tr>
                                        <td>Frequency*:</td>
                                        <td>' . $form->output_field(array('type'=>'select', 'name'=>'recurring_payment_period_' . $order_item_id, 'options'=>get_payment_period_options(), 'class'=>'software_select', 'required' => 'true')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Number of Payments' . $output_recurring_number_of_payments_required_asterisk . ':</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'recurring_number_of_payments_' . $order_item_id, 'size'=>'3', 'maxlength'=>'9', 'class'=>'software_input_text', 'required' => $number_of_payments_required)) . get_number_of_payments_message() . '</td>
                                    </tr>
                                    ' . $output_start_date_row . '
                                </table>
                            </fieldset>';
                    }

                    $output_gift_cards = '';
                    
                    // If this product is a gift card, then output gift card form.
                    if ($gift_card == 1) {
                        // If the quantity is 100 or less, then set the number of gift cards to the quantity.
                        if ($quantity <= 100) {
                            $number_of_gift_cards = $quantity;
                            
                        // Otherwise the quantity is greater than 100, so set the number of gift cards to 100.
                        // We do this in order to prevent a ton of forms from appearing and causing a slowdown.
                        } else {
                            $number_of_gift_cards = 100;
                        }
                        
                        // Loop through all quantities in order to output a form for each quantity.
                        for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
                            // If screen was not just submitted, then prefill fields for this gift card.
                            if (($form->field_in_session('submit_update') == false) && ($form->field_in_session('submit_checkout') == false)) {
                                // Get saved gift card data from database.
                                $order_item_gift_card = db_item(
                                    "SELECT
                                        id,
                                        from_name,
                                        recipient_email_address,
                                        message,
                                        delivery_date
                                    FROM order_item_gift_cards
                                    WHERE
                                        (order_item_id = '$order_item_id')
                                        AND (quantity_number = '$quantity_number')");

                                // If gift card data was found in database, then prefill fields with data.
                                if ($order_item_gift_card['id']) {
                                    $form->assign_field_value('order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_from_name', $order_item_gift_card['from_name']);
                                    $form->assign_field_value('order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address', $order_item_gift_card['recipient_email_address']);
                                    $form->assign_field_value('order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_message', $order_item_gift_card['message']);

                                    if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                                        $delivery_date = '';
                                    } else {
                                        $delivery_date = prepare_form_data_for_output($order_item_gift_card['delivery_date'], 'date');
                                    }

                                    $form->assign_field_value('order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', $delivery_date);
                                }
                            }

                            // If the delivery date is blank, then set it to today's date.
                            if ($form->get_field_value('order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date') == '') {
                                // If the date format is month and then day, then use that format.
                                if (DATE_FORMAT == 'month_day') {
                                    $month_and_day_format = 'n/j';

                                // Otherwise the date format is day and then month, so use that format.
                                } else {
                                    $month_and_day_format = 'j/n';
                                }

                                $form->assign_field_value('order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', date($month_and_day_format . '/Y'));
                            }

                            $output_legend_quantity_number = '';
                            
                            // If number of gift cards is greater than 1, then add quantity number to legend.
                            if ($number_of_gift_cards > 1) {
                                $output_legend_quantity_number .= ' (' . $quantity_number . ' of ' . $number_of_gift_cards . ')';
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
                                            <td>Recipient Email*:</td>
                                            <td>' . $form->output_field(array('type' => 'email', 'name' => 'order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address', 'placeholder' => 'recipient@example.com', 'size' => '40', 'maxlength' => '100', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Your Name:</td>
                                            <td>' . $form->output_field(array('type' => 'text', 'name' => 'order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_from_name', 'placeholder' => 'Your name that will appear in the email.', 'size' => '40', 'maxlength' => '100', 'class'=>'software_input_text')) . ' (leave blank if you want to be anonymous)</td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align: top">Message:</td>
                                            <td>' . $form->output_field(array('type' => 'textarea', 'name' => 'order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_message', 'placeholder' => 'The message that will appear in the email.', 'rows' => '3', 'cols' => '60', 'class' => 'software_textarea')) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Date:</td>
                                            <td>
                                                ' . $form->output_field(array('type' => 'text', 'id' => 'order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', 'name' => 'order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', 'size' => '10', 'maxlength' => '10', 'class' => 'software_input_text')) . '
                                                ' . get_date_picker_format() . '
                                                <script>
                                                    software_$("#order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date").datepicker({
                                                        dateFormat: date_picker_format
                                                    });
                                                </script>
                                            </td>
                                        </tr>
                                    </table>
                                </fieldset>';
                        }
                    }
                    
                    // assume that there is not a form to output until we find out otherwse
                    $output_forms = '';
                    
                    // if there is a form for this product, then prepare to output form
                    if ($form_enabled == 1) {
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
                            // if screen was not just submitted, then prefill fields for this product form
                            if (($form->field_in_session('submit_update') == false) && ($form->field_in_session('submit_checkout') == false)) {
                                $query =
                                    "SELECT
                                        form_data.form_field_id,
                                        form_data.data,
                                        count(*) as number_of_values,
                                        form_fields.type
                                    FROM form_data
                                    LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                                    WHERE
                                        (form_data.order_item_id = '$order_item_id')
                                        AND (form_data.quantity_number = '$quantity_number')
                                    GROUP BY form_data.form_field_id";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                $fields = array();
                                
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $fields[] = $row;
                                }
                                
                                // loop through all field data in order to prefill fields
                                foreach ($fields as $field) {
                                    // if there is more than one value, get all values
                                    if ($field['number_of_values'] > 1) {
                                        $query =
                                            "SELECT data
                                            FROM form_data
                                            WHERE
                                                (order_item_id = '$order_item_id')
                                                AND (quantity_number = '$quantity_number')
                                                AND (form_field_id = '" . $field['form_field_id'] . "')
                                            ORDER BY id";
                                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                        
                                        $field['data'] = array();
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $field['data'][] = $row['data'];
                                        }
                                    }
                                    
                                    $html_field_name = 'order_item_' . $order_item_id . '_quantity_number_' . $quantity_number . '_form_field_' . $field['form_field_id'];
                                    
                                    $form->assign_field_value($html_field_name, prepare_form_data_for_output($field['data'], $field['type'], $prepare_for_html = false));
                                }
                            }
                            
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

                            // If there is a product submit form reference code field on this form,
                            // then pass that info to the get form info function so that
                            // we can output the title for that form next to the reference code field.

                            $reference_code_field_id = 0;

                            if (
                                ($order_item['submit_form'])
                                && ($order_item['submit_form_custom_form_page_id'])
                                && ($order_item['submit_form_update'])
                                && ($order_item['submit_form_update_where_field'] == 'reference_code')
                                && (($quantity_number == 1) or ($order_item['submit_form_quantity_type'] == 'One Form per Quantity'))
                            ) {
                                // Remove carets from where value, in order to determine
                                // if a product form field exists for the where value.
                                $field_name = str_replace('^^', '', $order_item['submit_form_update_where_value']);

                                if ($field_name != '') {
                                    $reference_code_field_id = db_value(
                                        "SELECT id
                                        FROM form_fields
                                        WHERE
                                            (product_id = '" . e($product_id) . "')
                                            AND (name = '" . e($field_name) . "')");
                                }
                            }
                            
                            // get form info (form content, wysiwyg fields, file upload)
                            $form_info = get_form_info(0, $product_id, $order_item_id, $quantity_number, $form_label_column_width, $office_use_only = false, $form, 'frontend', $editable = FALSE, $device_type, $folder_id_for_default_value, $reference_code_field_id);
                            
                            // store wysiwyg fields in array
                            $wysiwyg_fields = array_merge($wysiwyg_fields, $form_info['wysiwyg_fields']);
                            
                            $output_forms .=
                                '<fieldset class="software_fieldset" style="margin-bottom: 1em">
                                    ' . $output_legend . '
                                        <table>
                                            ' . $form_info['content'] . '
                                        </table>
                                </fieldset>';
                        }
                    }
                    
                    // if product is not a recurring product or if start date is today and payment gateway is not ClearCommerce, then it is in the non-recurring order
                    if (
                        ($recurring == 0)
                        ||
                        (
                            (
                                (($recurring_schedule_editable_by_customer == 0) && ($start == 0))
                                || (($recurring_schedule_editable_by_customer == 1) && (prepare_form_data_for_input($form->get_field_value('recurring_start_date_' . $order_item_id), 'date') == date('Y-m-d')))
                            )
                            && ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce'))
                        )
                    ) {
                        $in_nonrecurring = true;
                        
                    } else {
                        $in_nonrecurring = false;
                    }

                    // if product is in non-recurring order
                    if ($in_nonrecurring) {
                        // update subtotal
                        $subtotal = $subtotal + $total_price;
                        
                        // if order item is a donation, then output donation amount text box
                        if ($selection_type == 'donation') {
                            $donations_exist_in_non_recurring = true;
                            $output_total_price_or_donation_amount_text_box = $output_donation_amount_text_box;
                            
                        // else order item is not a donation, so just output total price
                        } else {
                            $non_donations_exist_in_non_recurring = true;
                            $output_total_price_or_donation_amount_text_box = $output_total_price;
                        }
                        
                        $output_recurring_schedule_row = '';
                        
                        // if there is a recurring schedule fieldset to output, then prepare row
                        if ($output_recurring_schedule_fieldset != '') {
                            $output_recurring_schedule_row =
                                '<tr class="products data row_' . ($row_count % 2) . '">';
                            if ($device_type == 'mobile') {
                                $output_recurring_schedule_row .=
                                    '<td colspan="6">';
                            } else {
                                $output_recurring_schedule_row .=
                                    '<td>&nbsp;</td>
                                    <td colspan="5">';
                            }
                            $output_recurring_schedule_row .=
                                    $output_recurring_schedule_fieldset . '
                                    </td>
                                </tr>';
                        }

                        $output_gift_card_row = '';
                        
                        // If there is a gift card form to output, then output row.
                        if ($output_gift_cards != '') {
                            $output_gift_card_row =
                                '<tr class="products data row_' . ($row_count % 2) . '">
                                    <td class="mobile_hide">&nbsp;</td>
                                    <td colspan="5">
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
                                    <td colspan="5">
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
                                '<td class="mobile_left" style="vertical-align: top; text-align: right; white-space: nowrap">' . $output_total_price_or_donation_amount_text_box . '</td>
                                <td class="mobile_right" style="vertical-align: top; text-align: center; padding-top: 0px; padding-bottom: 5px">' . $output_remove . '</td>
                            </tr>
                            ' . $output_recurring_schedule_row . '
                            ' . $output_gift_card_row . '
                            ' . $output_form_row;
                    }

                    // if product is a recurring product
                    if ($recurring) {
                        $recurring_products_exist = true;
                        
                        // if order item is a donation and it is not already listed in non-recurring area, then output donation amount text box
                        if (($selection_type == 'donation') && ($in_nonrecurring == false)) {
                            $output_total_price_or_donation_amount_text_box = $output_donation_amount_text_box;
                            
                        // else order item is not a donation or it is already listed in non-recurring area, so just output the total price
                        } else {
                            $output_total_price_or_donation_amount_text_box = $output_total_price;
                        }
                        
                        // if the recurring schedule is editable by the customer, then update payment period in order to show correct payment period for product
                        if ($recurring_schedule_editable_by_customer == 1) {
                            $payment_period = $form->get_field_value('recurring_payment_period_' . $order_item_id);
                            
                        // else the recurring schedule is not editable by the customer and if the payment period is blank, then default to Monthly
                        } elseif ($payment_period == '') {
                            $payment_period = 'Monthly';
                        }
                        
                        // if the payment period is blank, then set to Unknown
                        if ($payment_period == '') {
                            $payment_period = 'Unknown';
                        }
                        
                        $output_recurring_schedule_row = '';
                        $output_gift_card_row = '';
                        $output_form_row = '';

                        // if product is in non-recurring order, then we do not want to output quantity and remove fields that could conflict with other fields
                        if ($in_nonrecurring) {
                            // check that quantity has not already been cleared because it is a donation
                            if ($output_quantity) {
                                $output_quantity = $quantity;
                            }
                            $output_remove = '';
                            
                        // else product is not in non-recurring order, so prepare recurring schedule and form rows
                        } else {
                            // if there is a recurring schedule fieldset to output, then prepare row
                            if ($output_recurring_schedule_fieldset != '') {
                                $output_recurring_schedule_row =
                                    '<tr class="products data row_' . ($row_count % 2) . '">
                                        <td>&nbsp;</td>
                                        <td colspan="6">
                                            ' . $output_recurring_schedule_fieldset . '
                                        </td>
                                    </tr>';
                            }

                            // If there is a gift card form to output, then output gift card row.
                            if ($output_gift_cards != '') {
                                $output_gift_card_row =
                                    '<tr class="products data row_' . ($row_count % 2) . '">
                                        <td>&nbsp;</td>
                                        <td colspan="6">
                                            ' . $output_gift_cards . '
                                        </td>
                                    </tr>';
                            }
                            
                            // if there is a form to output, then prepare form row
                            if ($output_forms != '') {
                                $output_form_row =
                                    '<tr class="products data row_' . ($row_count % 2) . '">
                                        <td>&nbsp;</td>
                                        <td colspan="6">
                                            ' . $output_forms . '
                                        </td>
                                    </tr>';
                            }
                        }
                        
                        $output_recurring_products .=
                            '<tr class="products data row_' . ($row_count % 2) . '">
                                <td class="mobile_left" style="vertical-align: top">' . h($name) . '</td>
                                <td class="mobile_right mobile_width" style="vertical-align: top; width: 100%">' . $output_description . '</td>
                                <td class="mobile_left" style="vertical-align: top">' . $payment_period . '</td>
                                <td class="mobile_left" style="vertical-align: top; margin-left: .5em">' . $output_quantity . '</td>
                                <td class="mobile_left" style="vertical-align: top; text-align: right; margin-left: .5em">' . $output_product_price . '</td>
                                <td class="mobile_right" style="vertical-align: top; text-align: right; white-space: nowrap; margin-left: .5em">' . $output_total_price_or_donation_amount_text_box . '</td>
                                <td class="mobile_right" style="vertical-align: top; text-align: center; padding-top: 0px; padding-bottom: 5px">' . $output_remove . '</td>
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
                        $payment_periods[$payment_period]['exists'] = true;
                        $payment_periods[$payment_period]['subtotal'] += $total_price;
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
                    // if shipping is on and recipient mode is multi-recipient and this ship to is a real ship to, then add header with ship to label
                    if ((ECOMMERCE_SHIPPING == true) && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') && ($ship_to_id != 0)) {
                        $output_ship_tos .=
                            '<tr class="ship_tos">
                                <td colspan="6">
                                    <div class="heading">Ship to <span class="software_highlight">' . h($ship_to_name) . '</span></div>
                                </td>
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
                        '<tr class="products heading mobile_hide" style="border: none">
                            <th class="heading_item" style="text-align: left">Item</th>
                            <th class="heading_description" style="text-align: left">Description</th>
                            <th class="heading_selection" style="text-align: left">' . $output_quantity_heading . '</th>
                            <th class="heading_price" style="text-align: right">' . $output_price_heading . '</th>
                            <th class="heading_amount" style="text-align: right">Amount</th>
                            <th>&nbsp;</th>
                        </tr>
                        ' . $output_products . '
                        <tr class="ship_tos data">
                            <td colspan="6">&nbsp;</td>
                        </tr>';
                }

                // if there is at least one product in recurring folder for this ship to, then output header and product information
                if ($output_recurring_products) {
                    // if shipping is on and recipient mode is multi-recipient and this ship to is a real ship to, then add header with ship to label
                    if ((ECOMMERCE_SHIPPING == true) && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') && ($ship_to_id != 0)) {
                        $output_recurring_ship_tos .=
                            '<tr class="ship_tos">
                                <td colspan="7">
                                    <div class="heading">Ship to <span class="software_highlight">' . h($ship_to_name) . '</span></div>
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
                            <th>&nbsp;</th>
                        </tr>
                        ' . $output_recurring_products . '
                        <tr>
                            <td colspan="7">&nbsp;</td>
                        </tr>';
                }
                
                // if there is an offer applied to this ship to and offer has not already been added to applied offers array,
                // store this offer as an applied offer
                if (($ship_to_offer_id != 0) && (in_array($ship_to_offer_id, $applied_offers) == false)) {
                    $applied_offers[] = $ship_to_offer_id;
                }
            }
            
            // assume that we don't need to output wywiwyg javascript until we find out otherwise
            $output_wysiwyg_javascript = '';
            
            // if there is at least one wysiwyg field, prepare wysiwyg fields
            if ($wysiwyg_fields) {
                $output_wysiwyg_javascript = get_wysiwyg_editor_code($wysiwyg_fields);
            }
            
            // if there is an order discount from an offer
            if ($_SESSION['ecommerce']['order_discount']) {
                $output_subtotal =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right">Subtotal:</td>
                        <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($subtotal * 100, FALSE, $discounted_price = '', 'html') . '</td>
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
                
                $order_discount = $_SESSION['ecommerce']['order_discount'] / 100;
                
                $output_discount =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right">Discount:</td>
                        <td class="mobile_right" style="text-align: right">-' . prepare_price_for_output($order_discount * 100, FALSE, $discounted_price = '', 'html') . '</td>
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
                
                $total = $subtotal - $order_discount;
                
                $output_total =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right"><strong>Total:</strong></td>
                        <td class="mobile_right" style="text-align: right"><strong>' . prepare_price_for_output($total * 100, FALSE, $discounted_price = '', 'html') . '</strong></td>
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
                
                $total_label = 'Total';
                    
            // else there is not an order discount from an offer
            } else {
                $output_subtotal =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right"><strong>Subtotal:</strong></td>
                        <td class="mobile_right" style="text-align: right"><strong>' . prepare_price_for_output($subtotal * 100, FALSE, $discounted_price = '', 'html') . '</strong></td>
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
                
                $output_discount = '';
                $output_total = '';
                
                $total_label = 'Subtotal';
            }
            
            // if tax and shipping are active
            if ((ECOMMERCE_TAX == true) && ($taxable_products_exist == true) && (ECOMMERCE_SHIPPING == TRUE) && ($shippable_products_exist == true)) {
                $output_total_warning = '<div class="order_totals data" style="margin-bottom: 15px; text-align: center">' . $total_label . ' does not include any applicable taxes or shipping charges which will be calculated during checkout.</div>';
                
            // else if just tax is active
            } elseif ((ECOMMERCE_TAX == true) && ($taxable_products_exist == true)) {
                $output_total_warning = '<div class="order_totals data" style="margin-bottom: 15px; text-align: center">' . $total_label . ' does not include any applicable taxes which will be calculated during checkout.</div>';
                
            // else if just shipping is active
            } elseif ((ECOMMERCE_SHIPPING == true) && ($shippable_products_exist == true)) {
                $output_total_warning = '<div class="order_totals data" style="margin-bottom: 15px; text-align: center">' . $total_label . ' does not include any applicable shipping charges which will be calculated during checkout.</div>';
                
            // else nothing is active so do not prepare any message
            } else {
                $output_total_warning = '';
            }

            // if there is a recurring product
            if ($recurring_products_exist == true) {
                $output_payment_periods = '';
                
                foreach ($payment_periods as $payment_period_name => $payment_period) {
                    // if there is a recurring product in the cart for this payment period
                    if ($payment_period['exists'] == true) {
                        $output_payment_periods .=
                            '<tr>
                                <td class="mobile_left" colspan="5" style="vertical-align: top; text-align: right"><strong>' . $payment_period_name . ' Subtotal:</strong></td>
                                <td class="mobile_right" style="text-align: right"><strong>' . prepare_price_for_output($payment_period['subtotal'] * 100, FALSE, $discounted_price = '', 'html') . '</strong></td>
                                <td class="mobile_hide">&nbsp;</td>
                            </tr>';
                    }
                }
                
                $output_recurring_products =
                    '<div class="recurring_products">
                    <fieldset style="margin-bottom: 15px" class="software_fieldset">
                        <legend class="software_legend">Recurring Charges</legend>
                            <table class="products" width="100%" cellspacing="2" cellpadding="2" border="0" style="margin-bottom: 15px">
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
                    
                    $output_applied_offers .= '<li class="software_highlight"><em>' . h($offer_description) . '</em></li>';
                }
                
                $output_applied_offers .=
                    '   </ul>
                    </div></div>';
            }
            
            // if special offer code was not just submitted, get special offer code from order record,
            // so we can prefill special offer code field
            if ($form->field_in_session('special_offer_code') == false) {
                    $form->assign_field_value('special_offer_code', $special_offer_code);
            }

            // output special offer field if a special offer label or message exists, or an offer is already applied to the order
            if ($special_offer_code_label != '' || $special_offer_code_message != '' || $special_offer_code != '') {
                $output_special_offer =
                    '<table class="offer_code" style="margin-bottom: 15px">
                        <tr>
                            <td class="offer_code_label mobile_left">' . h($special_offer_code_label) . '</td>
                            <td class="mobile_hide" style="padding-right: 5px;"></td>
                            <td class="offer_code_field mobile_left mobile_width">' . $form->output_field(array('type'=>'text', 'name'=>'special_offer_code', 'size'=>'15', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                            <td class="mobile_hide" style="padding-right: .5em;"></td>
                            <td class="offer_code_message mobile_left mobile_width">' . h($special_offer_code_message) . '</td>
                        </tr>
                    </table>';
            } else {
                $output_special_offer = '';
            }
            
            $output_offline_payment_allowed = '';
            
            // if offline payment is on, and the user is logged in, and if the user is at least a manager or if they have access to set offline payment, then output the offline payment option
            if ((ECOMMERCE_OFFLINE_PAYMENT == TRUE) && (isset($user) == TRUE) && (($user['role'] < 3) || ($user['set_offline_payment'] == TRUE))) {
                // if offline payment is allowed for this order, then prepare to check the check box
                if ($offline_payment_allowed == 1) {
                    $form->assign_field_value('offline_payment_allowed', 1);
                }
                
                $output_shopping_cart_label = $shopping_cart_label;
                
                // if the shopping cart label is blank, then set it to cart
                if ($output_shopping_cart_label == '') {
                    $output_shopping_cart_label = 'Cart';
                }
                
                // output the offline payment option
                $output_offline_payment_allowed = 
                    '<div class="software_notice">
                        ' . $form->output_field(array('type'=>'checkbox', 'name'=>'offline_payment_allowed', 'id'=>'offline_payment_allowed', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="offline_payment_allowed"> Allow offline payment option for this ' . $output_shopping_cart_label . ' (and click update to apply)</label>.
                    </div>';
            }
            
            // if a update button label was entered for the page, then use that
            if ($update_button_label) {
                $output_update_button_label = h($update_button_label);
                
            // else if a shopping cart label is found, then use that with "Update" in front of the label
            } elseif ($shopping_cart_label) {
                $output_update_button_label = 'Update ' . h($shopping_cart_label);
                
            // else a update button label could not be found, so just use a default label
            } else {
                $output_update_button_label = 'Update Cart';
            }
            
            // if a checkout button label was entered for the page, then use that
            if ($checkout_button_label) {
                $output_checkout_button_label = h($checkout_button_label);
                
            // else a checkout button label could not be found, so just use a default label
            } else {
                $output_checkout_button_label = 'Checkout';
            }
            
            $output_shopping_cart =
                $output_wysiwyg_javascript . '
                <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shopping_cart.php" method="post" style="margin: 0px 0px 15px 0px">
                    ' . get_token_field() . '
                    <input type="hidden" name="page_id" value="' . $page_id . '">
                    <input type="hidden" name="require_cookies" value="true">
                    <input type="hidden" name="folder_id" value="' . $folder_id_for_default_value . '">
                    <table class="products" width="100%" cellspacing="2" cellpadding="2" border="0" style="margin-bottom: 15px">
                        ' . $output_ship_tos . '
                        ' . $output_subtotal . '
                        ' . $output_discount . '
                        ' . $output_total . '
                    </table>
                    ' . $output_total_warning . '
                    ' . $output_recurring_products . '
                    ' . $output_applied_offers . '
                    ' . $output_special_offer . '
                    ' . $output_offline_payment_allowed . '
                    <table class="cart_update" style="width: 100%">
                        <tr>
                            <td style="float: left">Click the ' . $output_update_button_label . ' button to update totals, or click the ' . $output_checkout_button_label . ' button to update totals and complete your order on our secure server.</td>
                            <td class="mobile_left mobile_width mobile_margin_top" style="text-align: right; white-space: nowrap">
                                <input type="submit" name="submit_update" value="' . $output_update_button_label . '" class="software_input_submit_secondary update_button" style="margin: 0 0 .5em 0" formnovalidate>
                                <input type="submit" name="submit_checkout" value="' . $output_checkout_button_label . '" class="software_input_submit_primary checkout_button" style="margin: 0 0 .5em .5em"/>
                            </td>
                        </tr>
                    </table>
                </form>
                <div class="cart_link" style="font-size: 90%">
                    This ' . h($shopping_cart_label) . ' has been saved.  To retrieve this ' . h($shopping_cart_label) . ' at a later time, please use this link:<br />
                    <a href="' . URL_SCHEME . h($_SERVER['HTTP_HOST']) . OUTPUT_PATH . h(get_page_name($page_id)) . '?r=' . $reference_code . '">' . URL_SCHEME . h($_SERVER['HTTP_HOST']) . OUTPUT_PATH . h(get_page_name($page_id)) . '?<wbr />r=' . $reference_code . '</a>
                </div>';

        // else the number of ship tos is 0, so there are no products in the cart, so output notice
        } else {
            $output_shopping_cart = '<p style="font-weight:bold">No items have been added.</p>';
        }
        
        $output =
            $form->output_errors() .
            $form->output_notices() . '
            ' . $output_special_offers . '
            ' . $output_quick_add . '
            ' . $output_shopping_cart . '
            ' . get_update_currency_form();
        
        $form->remove();

        return
            '<div class="software_shopping_cart">
                '  . $output . '
            </div>';

    // Otherwise the layout is custom.
    } else {

        $order_id = $_SESSION['ecommerce']['order_id'];

        // Prepare attributes that are used for all forms (e.g. pending, quick add, cart).
        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shopping_cart.php" ' .
            'method="post"';

        $number_of_pending_offer_actions = 0;
        $pending_system = '';

        if ($pending_offers) {

            foreach ($pending_offers as $key => $offer) {

                // check if this offer has more than 1 offer action that adds a product
                // if there is more than 1 offer action for this pending offer,
                // then that means more than 1 product is being added for this offer
                // so we need to add extra info to the offer description with the action name,
                // so the customer understands which product will be added
                $query =
                    "SELECT
                        COUNT(offers_offer_actions_xref.offer_action_id)
                    FROM offers_offer_actions_xref
                    LEFT JOIN offer_actions ON offers_offer_actions_xref.offer_action_id = offer_actions.id
                    WHERE 
                        (offers_offer_actions_xref.offer_id = '" . $offer['id'] . "')
                        AND (offer_actions.type = 'add product')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_row($result);
                
                $offer['multiple_actions'] = false;
                
                // if this offer has multiple offer actions, then remember that
                if ($row[0] > 1) {
                    $offer['multiple_actions'] = true;
                }

                foreach ($offer['offer_actions'] as $action_key => $action) {

                    $number_of_pending_offer_actions++;

                    $action['recipient'] = false;

                    // If a recipient needs to be selected, then prepare that.
                    if (
                        $action['add_product_shippable']
                        and ECOMMERCE_SHIPPING
                        and (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient')
                    ) {
                        
                        $action['recipient'] = true;

                        // if only certain recipients are allowed for this offer action
                        if ($action['allowed_recipients']) {

                            $recipient_options = array();
                            $recipient_options[''] = '';
                            
                            foreach ($action['allowed_recipients'] as $ship_to_id) {
                                $query = "SELECT ship_to_name FROM ship_tos WHERE id = '$ship_to_id'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                $row = mysqli_fetch_assoc($result);

                                $recipient_options[$row['ship_to_name']] = $row['ship_to_name'];
                            }

                            $action['add_name'] = false;

                        // else all recipients are allowed for this offer action
                        } else {

                            initialize_recipients();
                            
                            $recipient_options = array();
                            $recipient_options[''] = '';
                            $recipient_options['myself'] = 'myself';

                            // if there is at least one recipient stored in session
                            if ($_SESSION['ecommerce']['recipients']) {
                                // loop through all recipients to build recipient options
                                foreach ($_SESSION['ecommerce']['recipients'] as $recipient) {
                                    $recipient_options[$recipient] = $recipient;
                                }
                            }

                            $action['add_name'] = true;

                            $form->set(
                                'pending_offer_' . $offer['id'] . '_' . $action['id'] . '_add_name',
                                'maxlength',
                                50);

                        }

                        $form->set(
                            'pending_offer_' . $offer['id'] . '_' . $action['id'] . '_ship_to',
                            'options',
                            $recipient_options);

                    }

                    $offer['offer_actions'][$action_key] = $action;
                
                }

                $pending_offers[$key] = $offer;

            }

            $pending_system .=
                get_token_field() . '
                <input type="hidden" name="page_id" value="' . h($page_id) . '">
                <input type="hidden" name="pending_offers" value="true">
                <input type="hidden" name="require_cookies" value="true">';

        }

        // Loop through the upsell offers to prepare info.
        foreach ($upsell_offers as $key => $offer) {
            $offer['upsell_action_url'] = '';

            if ($offer['upsell_action_page_id']) {
                $offer['upsell_action_url'] = PATH . encode_url_path(get_page_name($offer['upsell_action_page_id']));

                if ($offer['upsell_action_button_label'] == '') {
                    $offer['upsell_action_button_label'] = 'More Info';
                }
            }

            $upsell_offers[$key] = $offer;
        }

        $quick_add = array();

        // If there is a quick add product group and it is enabled,
        // then prepare quick add.
        if (
            $quick_add_product_group_id
            and db_value("SELECT enabled FROM product_groups WHERE id = '" . e($quick_add_product_group_id) . "'")
        ) {

            // Get all products that are in quick add product group.
            $products = db_items(
                "SELECT
                    products.id,
                    products.name,
                    products.short_description,
                    (products.price / 100) as price,
                    products.shippable,
                    products.selection_type,
                    products.default_quantity,
                    products.inventory,
                    products.inventory_quantity,
                    products.backorder,
                    products.out_of_stock_message
                FROM products_groups_xref
                LEFT JOIN products ON products.id = products_groups_xref.product
                WHERE
                    (products_groups_xref.product_group = '" . e($quick_add_product_group_id) . "')
                    AND (products.enabled = '1')
                ORDER BY products_groups_xref.sort_order, products.name");

            // If there is at least one product, then prepare quick add.
            if ($products) {

                $quick_add['label'] = $quick_add_label;
                $quick_add['product_group_id'] = $quick_add_product_group_id;
                $quick_add['products'] = $products;
                $quick_add['available_products'] = false;
                $quick_add['quantity'] = false;
                $quick_add['amount'] = false;
                $quick_add['recipient'] = false;
                $output_quick_add_products_array = '';
                $quick_add['product_id_options'] = array();
                $quick_add['product_id_options'][''] = '';

                // Get prices for products that have been discounted by offers,
                // so we can show the discounted price.
                $discounted_product_prices = get_discounted_product_prices();

                foreach ($quick_add['products'] as $key => $product) {

                    // assume that a recipient is not required until we find out otherwise
                    $recipient_required = 'false';
                    
                    // if the product is available by looking at inventory
                    if (
                        !$product['inventory']
                        or $product['inventory_quantity']
                        or $product['backorder']
                    ) {
                        $quick_add['available_products'] = true;
                        
                        // if product has a quantity selection type, then remember that
                        if ($product['selection_type'] == 'quantity') {
                            $quick_add['quantity'] = true;
                        }
                        
                        // if product has a donation selection type, then remember that
                        if ($product['selection_type'] == 'donation') {
                            $quick_add['amount'] = true;
                        }
                        
                        // if recipient is required to be selected for this product,
                        // prepare that value for JavaScript array
                        if (
                            ECOMMERCE_SHIPPING
                            and (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient')
                            and $product['shippable']
                        ) {
                            $recipient_required = 'true';
                            $quick_add['recipient'] = true;
                        }
                    }
                    
                    $output_quick_add_products_array .= 'quick_add_products[' . $product['id'] . '] = new Array("' . $product['selection_type'] . '", ' . $product['default_quantity'] . ', ' . $recipient_required . ');' . "\n";

                    $output_name = '';

                    if ($product['name']) {
                        $output_name = h($product['name']) . ' - ';
                    }

                    $output_price = '';
                    
                    // if product's selection type is not donation, then prepare to output price
                    if ($product['selection_type'] != 'donation') {
                        $product['discounted'] = false;
                        $product['discounted_price'] = 0;

                        // if the product is discounted, then prepare to show that
                        if (isset($discounted_product_prices[$product['id']]) == TRUE) {
                            $product['discounted'] = true;
                            $product['discounted_price'] = $discounted_product_prices[$product['id']] / 100;
                        }
                        
                        $output_price = ' (' . prepare_price_for_output($product['price'] * 100, $product['discounted'], $product['discounted_price'] * 100, 'plain_text') . ')';
                    }
                    
                    $output_label = $output_name . h($product['short_description']) . $output_price;
                    
                    // if the product is out of stock then add message to label
                    if (
                        $product['inventory']
                        and !$product['inventory_quantity']
                        and ($product['out_of_stock_message'] != '')
                        and ($product['out_of_stock_message'] != '<p></p>')
                    ) {
                        $out_of_stock_message = trim(convert_html_to_text($product['out_of_stock_message']));
                        
                        // if the message is longer than 50 characters, then truncate it
                        if (mb_strlen($out_of_stock_message) > 50) {
                            $out_of_stock_message = mb_substr($out_of_stock_message, 0, 50) . '...';
                        }
                        
                        $output_label .= ' - ' . h($out_of_stock_message);
                    }

                    $quick_add['product_id_options'][$output_label] = $product['id'];

                    $quick_add['products'][$key] = $product;

                }

                $form->set('quick_add_product_id', 'options', $quick_add['product_id_options']);
                $form->set('quick_add_product_id', 'required', true);
                
                // If there are products that require a recipient to be selected,
                // prepare ship to and add name rows.
                if ($quick_add['recipient']) {

                    initialize_recipients();

                    $quick_add['ship_to_options'] = array();
                    $quick_add['ship_to_options'][''] = '';
                    $quick_add['ship_to_options']['myself'] = 'myself';

                    // if there is at least one recipient stored in session
                    if ($_SESSION['ecommerce']['recipients']) {
                        // loop through all recipients to build recipient options
                        foreach ($_SESSION['ecommerce']['recipients'] as $recipient) {
                            $quick_add['ship_to_options'][$recipient] = $recipient;
                        }
                    }

                    $form->set('quick_add_ship_to', 'options', $quick_add['ship_to_options']);
                    $form->set('quick_add_add_name', 'maxlength', 50);

                }

                // If there are products that require a quantity to be selected,
                // prepare quantity row.
                if ($quick_add['quantity']) {
                    $form->set('quick_add_quantity', 'maxlength', 9);
                }

                $quick_add['system'] .=
                    get_token_field() . '
                    <input type="hidden" name="page_id" value="' . h($page_id) . '">
                    <input type="hidden" name="quick_add" value="true">
                    <input type="hidden" name="require_cookies" value="true">
                    <script>
                        var quick_add_products = new Array();
                        ' . $output_quick_add_products_array . '
                        software.init_quick_add();
                    </script>';

            }

        }

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
                countries.name as country,
                ship_tos.address_verified,
                ship_tos.arrival_date,
                ship_tos.shipping_cost,
                ship_tos.offer_id
            FROM ship_tos
            LEFT JOIN countries ON ship_tos.country = countries.code
            WHERE ship_tos.order_id = '" . e($order_id) . "'
            ORDER BY ship_tos.id");

        $recipients = array_merge($recipients, $ship_tos);

        $system = '';
        $taxable_items = false;
        $shippable_items = false;
        $nonrecurring_items = false;
        $recurring_items = false;
        $number_of_payments_message = '';
        $number_of_payments_required = false;
        $start_date = false;
        $date_picker_format_added = false;
        $subtotal = 0;
        $subtotal_info = '';
        $discount = 0;
        $discount_info = '';
        $total = 0;
        $total_info = '';
        $payment_periods = array();
        $applied_offers = array();
        $show_special_offer_code = false;
        $retrieve_order_url = '';

        // If there are recipients, then there are products in the cart, so output cart.
        if ($recipients) {

            $system .=
                get_token_field() . '
                <input type="hidden" name="page_id" value="' . h($page_id) . '">
                <input type="hidden" name="require_cookies" value="true">
                <input type="hidden" name="folder_id" value="' . h($folder_id_for_default_value) . '">';

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
                    'subtotal' => 0);
            }

            // If credit/debit card payment method is not enabled or the payment gateway
            // is not ClearCommerce, then remember that we need to deal with the start date.
            if (
                !ECOMMERCE_CREDIT_DEBIT_CARD
                or (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')
            ) {
                $start_date = true;
            }
            
            // create array for storing inventory quantity for products,
            // so we can keep track of remaining inventory quantity as we loop through order items,
            // so that we can determine if out of stock message should be shown for an order item
            $inventory_quantities = array();

            $wysiwyg_fields = array();
            $date_fields = array();
            $date_and_time_fields = array();

            foreach ($recipients as $key => $recipient) {

                $recipient['shipping'] = false;

                if ($recipient['id']) {
                    $recipient['shipping'] = true;
                }

                $recipient['ship_to_heading'] = false;

                if (
                    ECOMMERCE_SHIPPING
                    and (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient')
                    and $recipient['shipping']
                ) {
                    $recipient['ship_to_heading'] = true;
                }

                $recipient['items'] = db_items(
                    "SELECT
                        order_items.id,
                        order_items.product_id,
                        order_items.product_name AS name,
                        order_items.quantity,
                        order_items.price / 100 AS price,
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
                        products.details,
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
                        products.submit_form,
                        products.submit_form_custom_form_page_id,
                        products.submit_form_update,
                        products.submit_form_update_where_field,
                        products.submit_form_update_where_value,
                        products.submit_form_quantity_type
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

                    $item['amount'] = $item['price'] * $item['quantity'];

                    $item['recurring_schedule'] = false;

                    // If this is a recurring item and the schedule is editable
                    // by the customer, then prepare schedule.  We have to do this
                    // up here at the top, because we check the start date that is set
                    // further below to determine whether item is in nonrecurring area.
                    if ($item['recurring'] and $item['recurring_schedule_editable_by_customer']) {

                        $item['recurring_schedule'] = true;

                        // If screen was not just submitted,
                        // then prefill recurring schedule fields.
                        if (!$form->field_in_session('submit_update') and !$form->field_in_session('submit_checkout')) {

                            // If the payment period has not been set for
                            // this order item yet, then use the default
                            // scheduling values from the product
                            if ($item['recurring_payment_period'] == '') {

                                $form->set('recurring_payment_period_' . $item['id'], $item['payment_period']);
                                $form->set('recurring_number_of_payments_' . $item['id'], $item['number_of_payments']);
                                
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
                                    $recurring_start_date = date($month_and_day_format . '/Y', time() + (86400 * $item['start']));
                                    
                                    $form->set('recurring_start_date_' . $item['id'], $recurring_start_date);
                                }

                            // Otherwise the payment period has been set for
                            // this order item, so use the values that the customer has set.
                            } else {
                                $form->set('recurring_payment_period_' . $item['id'], $item['recurring_payment_period']);
                                $form->set('recurring_number_of_payments_' . $item['id'], $item['recurring_number_of_payments']);

                                // If we need to deal with the start date, then do that.
                                if ($start_date) {
                                    $form->set('recurring_start_date_' . $item['id'], prepare_form_data_for_output($item['recurring_start_date'], 'date'));
                                }
                            }

                        }

                        $form->set('recurring_payment_period_' . $item['id'], 'options', get_payment_period_options());
                        
                        // if number of payments is 0, then set to empty string
                        if ($form->get('recurring_number_of_payments_' . $item['id']) == 0) {
                            $form->set('recurring_number_of_payments_' . $item['id'], '');
                        }

                        $form->set('recurring_number_of_payments_' . $item['id'], 'maxlength', 9);

                        // If we have not gotten the number of payments message while processing
                        // a different product, then do that now.
                        if (!$number_of_payments_message) {
                            $number_of_payments_message = trim(get_number_of_payments_message());
                        }
                        
                        // If we need to deal with the start date, then do that.
                        if ($start_date) {
                            $form->set('recurring_start_date_' . $item['id'], 'maxlength', 10);

                            // If we have not already added the date picker format code,
                            // then add it.
                            if (!$date_picker_format_added) {
                                $system .= get_date_picker_format();
                                $date_picker_format_added = true;
                            }

                            $system .=
                                '<script>
                                    software_$("#recurring_start_date_' . $item['id'] . '").datepicker({
                                        dateFormat: date_picker_format
                                    });
                                </script>';
                        }

                    }

                    $item['in_nonrecurring'] = false;

                    // if product is not a recurring product or if start date is today and payment gateway is not ClearCommerce, then it is in the non-recurring order
                    if (
                        (!$item['recurring'])
                        or
                        (
                            (
                                (
                                    !$item['recurring_schedule_editable_by_customer']
                                    and !$item['start']
                                )
                                or
                                (
                                    ($item['recurring_schedule_editable_by_customer'])
                                    and
                                    (prepare_form_data_for_input($form->get_field_value('recurring_start_date_' . $item['id']), 'date') == date('Y-m-d'))
                                )
                            )
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

                    // if mode is edit, add edit button for images
                    if ($editable) {
                        $item['full_description'] = add_edit_button_for_images('product', $item['product_id'], $item['full_description']);
                    }

                    $item['show_out_of_stock_message'] = false;

                    // if inventory is enabled for the product and there is an out of stock message, then determine if we should show out of stock message
                    if (
                        $item['inventory']
                        and ($item['out_of_stock_message'] != '')
                    ) {
                        // if the initial inventory quantity for this product has not already been set, then set it
                        if (!isset($inventory_quantities[$item['product_id']])) {
                            $inventory_quantities[$item['product_id']] = $item['inventory_quantity'];
                        }
                        
                        // if the quantity of this order item is greater than the inventory quantity, then show out of stock message and set inventory quantity to 0
                        if ($item['quantity'] > $inventory_quantities[$item['product_id']]) {
                            $item['show_out_of_stock_message'] = true;
                            $inventory_quantities[$item['product_id']] = 0;
                            
                        // else the quantity of this order items is less than the inventory quantity, so decrement inventory quantity,
                        // so when we look at more products we have an accurate inventory quantity for what has been used so far
                        } else {
                            $inventory_quantities[$item['product_id']] = $inventory_quantities[$item['product_id']] - $item['quantity'];
                        }
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

                        // Loop through all quantities in order to prepare fields for each quantity.
                        for ($quantity_number = 1; $quantity_number <= $item['number_of_gift_cards']; $quantity_number++) {
                            // If screen was not just submitted, then prefill
                            // fields for this gift card.
                            if (!$form->field_in_session('submit_update') and !$form->field_in_session('submit_checkout')) {
                                // Get saved gift card data from database.
                                $order_item_gift_card = db_item(
                                    "SELECT
                                        id,
                                        from_name,
                                        recipient_email_address,
                                        message,
                                        delivery_date
                                    FROM order_item_gift_cards
                                    WHERE
                                        (order_item_id = '" . $item['id'] . "')
                                        AND (quantity_number = '$quantity_number')");

                                // If gift card data was found in database, then prefill fields with data.
                                if ($order_item_gift_card['id']) {
                                    $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address', $order_item_gift_card['recipient_email_address']);

                                    $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_from_name', $order_item_gift_card['from_name']);

                                    $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_message', $order_item_gift_card['message']);

                                    if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                                        $delivery_date = '';
                                    } else {
                                        $delivery_date = prepare_form_data_for_output($order_item_gift_card['delivery_date'], 'date');
                                    }

                                    $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', $delivery_date);
                                }
                            }

                            $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_recipient_email_address', 'maxlength', 100);

                            $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_from_name', 'maxlength', 100);

                            $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', 'maxlength', 100);

                            // If the delivery date is blank, then set it to today's date.
                            if ($form->get('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date') == '') {
                                // If the date format is month and then day, then use that format.
                                if (DATE_FORMAT == 'month_day') {
                                    $month_and_day_format = 'n/j';

                                // Otherwise the date format is day and then month, so use that format.
                                } else {
                                    $month_and_day_format = 'j/n';
                                }

                                $form->set('order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date', date($month_and_day_format . '/Y'));
                            }

                            // If we have not already added the date picker format code,
                            // then add it.
                            if (!$date_picker_format_added) {
                                $system .= get_date_picker_format();
                                $date_picker_format_added = true;
                            }

                            $system .=
                                '<script>
                                    software_$("#order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_gift_card_delivery_date").datepicker({
                                        dateFormat: date_picker_format
                                    });
                                </script>';

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

                        $item['fields'] = db_items(
                            "SELECT
                                id,
                                name,
                                label,
                                type,
                                default_value,
                                use_folder_name_for_default_value,
                                multiple,
                                required,
                                size,
                                maxlength,
                                wysiwyg,
                                `rows`, # Backticks for reserved word.
                                cols,
                                information
                            FROM form_fields
                            WHERE product_id = '" . e($item['product_id']) . "'
                            ORDER BY sort_order");

                        // Loop through all fields to get options.
                        foreach ($item['fields'] as $field_key => $field) {
                            
                            // If this is a radio, check box group, or pick list,
                            // then get options for the field, because we will need them below.
                            if (
                                ($field['type'] == 'radio button')
                                || ($field['type'] == 'check box')
                                || ($field['type'] == 'pick list')
                            ) {

                                $field['options'] = db_items(
                                    "SELECT
                                        label,
                                        value,
                                        default_selected
                                    FROM form_field_options
                                    WHERE form_field_id = '" . $field['id'] . "'
                                    ORDER BY sort_order");

                                if ($field['type'] == 'pick list') {

                                    $field['pick_list_options'] = array();
                                    
                                    foreach ($field['options'] as $option) {
                                        $field['pick_list_options'][$option['label']] =
                                            array(
                                                'value' => $option['value'],
                                                'default_selected' => $option['default_selected']
                                            );
                                    }

                                }

                            }

                            $item['fields'][$field_key] = $field;

                        }

                        // If there is a product submit form reference code field on this form,
                        // then figure out which field that is, so we can prepare for the title
                        // of the submitted form to be displayed below the field.

                        $reference_code_field_id = 0;

                        if (
                            ($item['submit_form'])
                            && ($item['submit_form_custom_form_page_id'])
                            && ($item['submit_form_update'])
                            && ($item['submit_form_update_where_field'] == 'reference_code')
                        ) {
                            // Remove carets from where value, in order to determine
                            // if a product form field exists for the where value.
                            $field_name = str_replace('^^', '', $item['submit_form_update_where_value']);

                            if ($field_name != '') {
                                $reference_code_field_id = db_value(
                                    "SELECT id
                                    FROM form_fields
                                    WHERE
                                        (product_id = '" . e($item['product_id']) . "')
                                        AND (name = '" . e($field_name) . "')");
                            }
                        }

                        // Loop through all quantity product forms to prepare fields.
                        for ($quantity_number = 1; $quantity_number <= $item['number_of_forms']; $quantity_number++) {

                            // If the form was not just submitted, then prefill fields from db.
                            if (!$form->field_in_session('submit_update') and !$form->field_in_session('submit_checkout')) {

                                $fields = db_items(
                                    "SELECT
                                        form_data.form_field_id,
                                        form_data.data,
                                        count(*) as number_of_values,
                                        form_fields.type
                                    FROM form_data
                                    LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                                    WHERE
                                        (form_data.order_item_id = '" . e($item['id']) . "')
                                        AND (form_data.quantity_number = '$quantity_number')
                                    GROUP BY form_data.form_field_id");
                                
                                // Loop through all field data in order to prefill fields.
                                foreach ($fields as $field) {

                                    // If there is more than one value, get all values.
                                    if ($field['number_of_values'] > 1) {
                                        $field['data'] = db_values(
                                            "SELECT data
                                            FROM form_data
                                            WHERE
                                                (order_item_id = '" . e($item['id']) . "')
                                                AND (quantity_number = '$quantity_number')
                                                AND (form_field_id = '" . $field['form_field_id'] . "')
                                            ORDER BY id");
                                    }

                                    $html_name = 'order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_form_field_' . $field['form_field_id'];

                                    $form->set($html_name, prepare_form_data_for_output($field['data'], $field['type'], $prepare_for_html = false));
                                    
                                }

                            }

                            foreach ($item['fields'] as $field_key => $field) {

                                $html_name = 'order_item_' . $item['id'] . '_quantity_number_' . $quantity_number . '_form_field_' . $field['id'];

                                // If the value for this field has not been set yet,
                                // (e.g. the form has not been submitted by the customer),
                                // then set default value.
                                if (!$form->field_in_session($html_name)) {
                                    $value_from_query_string = trim($_GET['value_' . $field['id']]);

                                    // If a default value was passed in the query string,
                                    // then use that.
                                    if ($value_from_query_string != '') {
                                        $form->set($html_name, $value_from_query_string);
                                        
                                    // Otherwise, if field is set to use folder name for default value,
                                    // then use it.
                                    } else if ($field['use_folder_name_for_default_value']) {
                                        $default_value = db_value(
                                            "SELECT folder_name
                                            FROM folder
                                            WHERE folder_id = '" . e($folder_id_for_default_value) . "'");

                                        $form->set($html_name, $default_value);

                                    // Otherwise if there is a default value, then use that.
                                    } else if ($field['default_value'] != '') {
                                        $form->set($html_name, $field['default_value']);

                                    // Otherwise if this is a check box group or pick list,
                                    // then use on/off values (default_selected) from choices.
                                    } else if (
                                        ($field['type'] == 'check box')
                                        || ($field['type'] == 'pick list')
                                    ) {
                                        $values = array();

                                        foreach ($field['options'] as $option) {
                                            if ($option['default_selected']) {
                                                $values[] = $option['value'];
                                            }
                                        }

                                        // If there is at least one default-selected value, then continue to set it.
                                        if ($values) {
                                            // If there is only one value, then set it.
                                            if (count($values) == 1) {
                                                $form->set($html_name, $values[0]);

                                            // Otherwise, there is more than one value, so set all of them.
                                            } else {
                                                $form->set($html_name, $values);
                                            }
                                        }
                                    }
                                }

                                switch ($field['type']) {

                                    case 'text box':

                                        // If this field is a product submit form reference code field,
                                        // and this is the first quantity, or a submitted form is updated
                                        // for every quantity, and the field does not have an error and the 
                                        // and the field has a value in it, then check if we can find a
                                        // submitted form for the reference code, and then get title for that form.
                                        // This will help the user understand which submitted form the reference code is related to.
                                        if (
                                            ($field['id'] == $reference_code_field_id)
                                            && (($quantity_number == 1) or ($item['submit_form_quantity_type'] == 'One Form per Quantity'))
                                            && ($form->check_field_error($html_name) == false)
                                            && ($form->get($html_name) != '')
                                            && ($submitted_form = db_item("SELECT id, page_id FROM forms WHERE reference_code = '" . e($form->get($html_name)) . "'"))
                                        ) {
                                            
                                            $title_label = db_value(
                                                "SELECT label
                                                FROM form_fields
                                                WHERE
                                                    page_id = '" . $submitted_form['page_id'] . "'
                                                    AND (rss_field = 'title')
                                                ORDER BY sort_order");

                                            $title = get_submitted_form_title($submitted_form['id']);

                                            // If there is a title, then store title data in the fields array.
                                            if ($title != '') {
                                                $item['fields'][$field_key]['titles'][$quantity_number]['title'] = $title;
                                                $item['fields'][$field_key]['titles'][$quantity_number]['title_label'] = $title_label;
                                            }
                                            
                                        }

                                        break;

                                    case 'date':
                                        $form->set($html_name, 'maxlength', 10);
                                        $date_fields[] = $html_name;

                                        break;

                                    case 'date and time':
                                        $form->set($html_name, 'maxlength', 22);
                                        $date_and_time_fields[] = $html_name;

                                        break;

                                    case 'pick list':

                                        $form->set($html_name, 'options', $field['pick_list_options']);

                                        break;

                                    case 'text area':
                                        // If field is a rich-text editor, then remember that,
                                        // so we can prepare JS later.
                                        if ($field['wysiwyg']) {
                                            $wysiwyg_fields[] = $html_name;
                                        }

                                        break;

                                    case 'time':
                                        $form->set($html_name, 'maxlength', 11);

                                        break;

                                }

                            }
                            
                        }
                    }

                    if (
                        ($item['selection_type'] != 'donation')
                        and (!$item['added_by_offer'])
                    ) {
                        $form->set('quantity[' . $item['id'] . ']', $item['quantity']);
                        $form->set('quantity[' . $item['id'] . ']', 'maxlength', 9);
                    }

                    $item['discounted'] = false;

                    if ($item['discounted_by_offer']) {
                        $item['discounted'] = true;
                    }

                    // If this item is not a donation then prepare price.
                    if ($item['selection_type'] != 'donation') {
                        $item['price_info'] = prepare_price_for_output($item['product_price'] * 100, $item['discounted'], $item['price'] * 100, 'html');
                    }

                    // If this item is a donation, then prepare donation amount field.
                    if ($item['selection_type'] == 'donation') {
                        // We can't add a comma to the amount, because value will
                        // appear blank if html 5 number type field is used.
                        $form->set(
                            'donations[' . $item['id'] . ']',
                            number_format(get_currency_amount($item['amount'], VISITOR_CURRENCY_EXCHANGE_RATE), 2, '.', ''));
                    }

                    $item['amount_info'] = prepare_price_for_output($item['amount'] * 100, false, $discounted_price = '', 'html');

                    $item['remove_url'] = PATH . SOFTWARE_DIRECTORY . '/remove_item_from_cart.php?order_item_id=' . $item['id'] . '&screen=shopping_cart&send_to=' . urlencode(REQUEST_URL) . '&token=' . $_SESSION['software']['token'];

                    // Remember if product is taxable and shippable,
                    // so that we can customize total disclaimer.

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

                        // If the recurring schedule is editable by the customer,
                        // then set payment period to period that customer selected.
                        if ($item['recurring_schedule_editable_by_customer']) {
                            $item['payment_period'] = $form->get('recurring_payment_period_' . $item['id']);
                            
                        // Otherwise the recurring schedule is not editable by the
                        // customer and if the payment period is blank,
                        // then default to Monthly
                        } elseif ($item['payment_period'] == '') {
                            $item['payment_period'] = 'Monthly';
                        }
                        
                        // if the payment period is blank, then set to Unknown
                        if ($item['payment_period'] == '') {
                            $item['payment_period'] = 'Unknown';
                        }

                        $payment_periods[$item['payment_period']]['exists'] = true;
                        $payment_periods[$item['payment_period']]['subtotal'] += $item['amount'];

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

            // If there is at least one rich-text editor field,
            // then output JS for them.
            if ($wysiwyg_fields) {
                $system .= get_wysiwyg_editor_code($wysiwyg_fields);
            }

            // If there is a date or date and time field, then prepare system content,
            // for the date/time picker.
            if ($date_fields || $date_and_time_fields) {

                // If we have not already added the date picker format code,
                // then add it.
                if (!$date_picker_format_added) {
                    $system .= get_date_picker_format();
                    $date_picker_format_added = true;
                }

                foreach ($date_fields as $date_field) {
                    $system .=
                        '<script>
                            software_$("#' . escape_javascript($date_field) . '").datepicker({
                                dateFormat: date_picker_format
                            });
                        </script>';
                }

                // If there is a date and time field, then prepare JS for those.
                if ($date_and_time_fields) {
                    // Include JS file for timepicker.
                    $system .=
                        '<script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>';

                    foreach ($date_and_time_fields as $date_and_time_field) {
                        $system .=
                            '<script>
                                software_$("#' . escape_javascript($date_and_time_field) . '").datetimepicker({
                                    dateFormat: date_picker_format,
                                    timeFormat: "h:mm TT"
                                });
                            </script>';
                    }
                }

            }

            $subtotal_info = prepare_price_for_output($subtotal * 100, false, $discounted_price = '', 'html');

            $discount = $_SESSION['ecommerce']['order_discount'] / 100;

            // If there is a discount, then prepare discount info and total.
            if ($discount) {
                $discount_info = prepare_price_for_output($discount * 100, false, $discounted_price = '', 'html');

                $total = $subtotal - $discount;

                $total_info = prepare_price_for_output($total * 100, false, $discounted_price = '', 'html');
            }

            // Loop through payment periods in order to prepare info
            // and remove ones that are not relevant to this order.
            foreach ($payment_periods as $key => $payment_period) {
                if ($payment_period['exists']) {
                    $payment_period['subtotal_info'] = prepare_price_for_output($payment_period['subtotal'] * 100, false, $discounted_price = '', 'html');

                    $payment_periods[$key] = $payment_period;

                } else {
                    unset($payment_periods[$key]);
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

            // output special offer field if a special offer label or message exists, or an offer is already applied to the order
            if (
                ($special_offer_code_label != '')
                or ($special_offer_code_message != '')
                or ($special_offer_code != '')
            ) {
                $show_special_offer_code = true;

                if (!$form->field_in_session('special_offer_code')) {
                    $form->set('special_offer_code', $special_offer_code);
                }

                $form->set('special_offer_code', 'maxlength', 50);
            }

            if ($shopping_cart_label == '') {
                $shopping_cart_label = 'Cart';
            }

            $order_offline_payment_allowed = $offline_payment_allowed;

            $offline_payment_allowed = false;

            if (
                ECOMMERCE_OFFLINE_PAYMENT
                and USER_LOGGED_IN
                and
                (
                    (USER_ROLE < 3)
                    or $user['set_offline_payment']
                )
            ) {
                $offline_payment_allowed = true;

                if ($order_offline_payment_allowed) {
                    $form->set('offline_payment_allowed', 1);
                }
            }

            // If an update button label was not entered for the page, then set default label.
            if ($update_button_label == '') {
                $update_button_label = 'Update ' . $shopping_cart_label;
            }

            // If a checkout button label was not entered for the page, then set default label.
            if ($checkout_button_label == '') {
                $checkout_button_label = 'Checkout';
            }

            $retrieve_order_url = URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id) . '?r=' . $reference_code;

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
            'form' => $form,
            'attributes' => $attributes,
            'number_of_special_offers' => $number_of_pending_offer_actions + count($upsell_offers),
            'number_of_pending_offer_actions' => $number_of_pending_offer_actions,
            'pending_offers' => $pending_offers,
            'pending_system' => $pending_system,
            'number_of_upsell_offers' => count($upsell_offers),
            'upsell_offers' => $upsell_offers,
            'quick_add' => $quick_add,
            'recipients' => $recipients,
            'product_description_type' => $product_description_type,
            'subtotal' => $subtotal,
            'subtotal_info' => $subtotal_info,
            'discount' => $discount,
            'discount_info' => $discount_info,
            'total' => $total,
            'total_info' => $total_info,
            'taxable_items' => $taxable_items,
            'shippable_items' => $shippable_items,
            'nonrecurring_items' => $nonrecurring_items,
            'recurring_items' => $recurring_items,
            'number_of_payments_message' => $number_of_payments_message,
            'number_of_payments_required' => $number_of_payments_required,
            'start_date' => $start_date,
            'payment_periods' => $payment_periods,
            'applied_offers' => $applied_offers,
            'number_of_applied_offers' => count($applied_offers),
            'show_special_offer_code' => $show_special_offer_code,
            'special_offer_code' => $special_offer_code,
            'special_offer_code_label' => $special_offer_code_label,
            'special_offer_code_message' => $special_offer_code_message,
            'shopping_cart_label' => $shopping_cart_label,
            'offline_payment_allowed' => $offline_payment_allowed,
            'update_button_label' => $update_button_label,
            'checkout_button_label' => $checkout_button_label,
            'system' => $system,
            'retrieve_order_url' => $retrieve_order_url,
            'currency_symbol' => VISITOR_CURRENCY_SYMBOL,
            'currency_code' => VISITOR_CURRENCY_CODE_FOR_OUTPUT,
            'currency' => $currency,
            'currency_attributes' => $currency_attributes,
            'currencies' => $currencies,
            'currency_system' => $currency_system));

        $content = $form->prepare($content);

        $form->remove();

        return
            '<div class="software_shopping_cart">
                '  . $content . '
            </div>';
    }
}