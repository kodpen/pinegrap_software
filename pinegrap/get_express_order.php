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

function get_express_order($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];
    $folder_id_for_default_value = $properties['folder_id_for_default_value'];

    $properties = get_page_type_properties($page_id, 'express order');

    $shopping_cart_label = $properties['shopping_cart_label'];
    $quick_add_label = $properties['quick_add_label'];
    $quick_add_product_group_id = $properties['quick_add_product_group_id'];
    $product_description_type = $properties['product_description_type'];
    $custom_shipping_form = $properties['shipping_form'];
    $special_offer_code_label = $properties['special_offer_code_label'];
    $special_offer_code_message = $properties['special_offer_code_message'];
    $custom_field_1_label = $properties['custom_field_1_label'];
    $custom_field_1_required = $properties['custom_field_1_required'];
    $custom_field_2_label = $properties['custom_field_2_label'];
    $custom_field_2_required = $properties['custom_field_2_required'];
    $po_number = $properties['po_number'];
    $custom_billing_form = $properties['form'];
    $custom_billing_form_name = $properties['form_name'];
    $custom_billing_form_label_column_width = $properties['form_label_column_width'];
    $card_verification_number_page_id = $properties['card_verification_number_page_id'];
    $offline_payment_always_allowed = $properties['offline_payment_always_allowed'];
    $offline_payment_label = $properties['offline_payment_label'];
    $terms_page_id = $properties['terms_page_id'];
    $terms_page_name = get_page_name($properties['terms_page_id']);
    $update_button_label = $properties['update_button_label'];
    $purchase_now_button_label = $properties['purchase_now_button_label'];
    
    global $user;

    $layout_type = get_layout_type($page_id);
    
    // store page id for express order page in case we need to direct the user to the page sometime in the future
    $_SESSION['ecommerce']['express_order_page_id'] = $page_id;
    
    // unset shopping cart page id if one exists, so we don't foward user to that page in the future
    unset($_SESSION['ecommerce']['shopping_cart_page_id']);
    
    $form = new liveform('express_order');

    $ghost = $_SESSION['software']['ghost'];
    
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
    
    // if tax is on, apply taxes to order
    if (ECOMMERCE_TAX == true) {
        update_order_item_taxes();
    }
        
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

    $order_id = $_SESSION['ecommerce']['order_id'];

    $system = '';
    $shippable_items = false;
    $arrival_dates = array();
    $custom_arrival_dates = array();
    $recipients = array();
    settype($custom_shipping_form, 'bool');
    $custom_shipping_form_fields = array();
    $edit_custom_shipping_form_start = '';
    $edit_custom_shipping_form_end = '';
    $date_picker_format_added = false;
    $billing_same_as_shipping = false;
    $billing_same_as_shipping_id = 0;
    settype($custom_billing_form, 'bool');
    $custom_billing_form_fields = array();
    $custom_billing_form_title = '';
    $edit_custom_billing_form_start = '';
    $edit_custom_billing_form_end = '';
    $date_fields = array();
    $date_and_time_fields = array();
    $wysiwyg_fields = array();

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

    // If shipping is enabled then get shipping recipients
    if (ECOMMERCE_SHIPPING) {

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
                ship_tos.country AS country_code,
                countries.name AS country,
                ship_tos.address_type,
                ship_tos.address_verified,
                ship_tos.phone_number,
                ship_tos.arrival_date,
                arrival_dates.id AS arrival_date_id,
                arrival_dates.name AS arrival_date_name,
                arrival_dates.custom AS arrival_date_custom,
                ship_tos.shipping_method_id,
                shipping_methods.name AS shipping_method_name,
                shipping_methods.description AS shipping_method_description,
                (ship_tos.shipping_cost / 100) AS shipping_cost,
                (ship_tos.original_shipping_cost / 100) AS original_shipping_cost,
                ship_tos.offer_id,
                ship_tos.complete
            FROM ship_tos
            LEFT JOIN shipping_methods ON shipping_methods.id = ship_tos.shipping_method_id
            LEFT JOIN countries ON ship_tos.country = countries.code
            LEFT JOIN arrival_dates ON ship_tos.arrival_date_id = arrival_dates.id
            WHERE ship_tos.order_id = '" . e($order_id) . "'
            ORDER BY ship_tos.id");

        // If there is at least one ship to, then remember that there are shippable items for later
        if ($ship_tos) {
            $shippable_items = true;
        }

        $recipients = array_merge($recipients, $ship_tos);
    }

    // If there is at least one recipient, then that means there is at least one item in cart,
    // so get various info that we will need later when there is at least one item.
    if ($recipients) {

        // Add a blank option for the first country pick list option
        $country_options[''] = '';

        // Get countries for country selection
        $countries =
            db_items(
                "SELECT name, code, zip_code_required AS zip FROM countries ORDER BY name",
                'code');

        foreach ($countries as $country) {
            $country_options[$country['name']] = $country['code'];
        }

        $states = db_items(
            "SELECT
                states.name,
                states.code,
                countries.code AS country_code
            FROM states
            LEFT JOIN countries ON countries.id = states.country_id
            ORDER BY states.name");

        // Loop through the states in order to add them to countries.
        foreach ($states as $state) {
            $countries[$state['country_code']]['states'][] = array(
                'name' => $state['name'],
                'code' => $state['code']);
        }

        $system .= '<script>software.countries = ' . encode_json($countries) . '</script>';
    }

    // If there is at least one shippable item, then prepare info that we will need for later
    if ($shippable_items) {

        $arrival_dates = db_items(
            "SELECT id, name, description, default_selected, custom
            FROM arrival_dates
            WHERE
                (status = 'enabled')
                AND (start_date <= CURRENT_DATE())
                AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())
            ORDER BY sort_order, arrival_date");

        $custom_arrival_dates = array();

        foreach ($arrival_dates as $arrival_date) {
            if ($arrival_date['custom']) {
                $custom_arrival_dates[] = $arrival_date['id'];
            }
        }

        if ($custom_arrival_dates and !$date_picker_format_added) {
            $system .= get_date_picker_format();
            $date_picker_format_added = true;
        }
    }

    $number_of_recipients = count($recipients);

    // Loop through recipients to prepare shipping info
    foreach ($recipients as $key => $recipient) {

        // If this is the non-shippable recipient, then skip to next recipient
        if (!$recipient['id']) {
            continue;
        }

        // Create a prefix for all shipping fields for this recipient, in order make them unique
        $prefix = 'shipping_' . $recipient['id'] . '_';

        // If the form was not just submitted, then prefill shipping fields
        if (
            !$form->field_in_session('submit_update')
            and !$form->field_in_session('submit_purchase_now')
        ) {

            $address_book = array();

            // If the user is logged in and not ghosting, then check address book for recipient data.
            if (USER_LOGGED_IN and !$ghost) {
                $address_book = db_item(
                    "SELECT
                        salutation,
                        first_name,
                        last_name,
                        company,
                        address_1,
                        address_2,
                        city,
                        state,
                        zip_code,
                        country,
                        address_type,
                        phone_number
                    FROM address_book
                    WHERE
                        user = '" . e(USER_ID) . "'
                        AND ship_to_name = '" . e($recipient['ship_to_name']) . "'");
            }

            // If an address book entry was found, then use that data to prefill
            if ($address_book) {
                $prefill = $address_book;

            // Otherwise an address book entry was not found, so use data from ship to table
            } else {
                $prefill = $recipient;

                // Set country to the country code instead of the name
                $prefill['country'] = $prefill['country_code'];
            }

            $form->set($prefix . 'salutation', $prefill['salutation']);
            $form->set($prefix . 'first_name', $prefill['first_name']);
            $form->set($prefix . 'last_name', $prefill['last_name']);
            $form->set($prefix . 'company', $prefill['company']);
            $form->set($prefix . 'address_1', $prefill['address_1']);
            $form->set($prefix . 'address_2', $prefill['address_2']);
            $form->set($prefix . 'city', $prefill['city']);
            $form->set($prefix . 'state', $prefill['state']);
            $form->set($prefix . 'zip_code', $prefill['zip_code']);

            // If there is a country set, then use it
            if ($prefill['country']) {
                $form->set($prefix . 'country', $prefill['country']);

            // Otherwise there is not a country set, so use default
            } else {
                $form->set($prefix . 'country',
                    db("SELECT code FROM countries WHERE default_selected = '1'"));
            }

            $form->set($prefix . 'address_type', $prefill['address_type']);
            $form->set($prefix . 'phone_number', $prefill['phone_number']);

            // If the visitor has selected an arrival date already, then select it
            if ($recipient['arrival_date_id']) {

                $form->set($prefix . 'arrival_date',
                    $recipient['arrival_date_id']);

                // If the selected arrival date has a custom field, then set that value also
                if ($recipient['arrival_date_custom']) {
                    $form->set(
                        $prefix . 'custom_arrival_date_' . $recipient['arrival_date_id'],
                        prepare_form_data_for_output($recipient['arrival_date'], 'date'));
                }

            // Otherwise the visitor has not selected an arrival date yet, so check if there is an
            // active arrival date that should be selected by default.
            } else {

                $form->set($prefix . 'arrival_date', db(
                    "SELECT id
                    FROM arrival_dates
                    WHERE
                        (status = 'enabled')
                        AND (start_date <= CURRENT_DATE())
                        AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())
                        AND (default_selected = '1')
                    ORDER BY sort_order, arrival_date
                    LIMIT 1"));
            }
        }

        $form->set($prefix . 'salutation', 'options',
                get_salutation_options());

        $form->set($prefix . 'first_name', 'maxlength', 50);
        $form->set($prefix . 'first_name', 'required', true);

        $form->set($prefix . 'last_name', 'maxlength', 50);
        $form->set($prefix . 'last_name', 'required', true);

        $form->set($prefix . 'company', 'maxlength', 50);
        
        $form->set($prefix . 'address_1', 'maxlength', 50);
        $form->set($prefix . 'address_1', 'required', true);

        $form->set($prefix . 'address_2', 'maxlength', 50);

        $form->set($prefix . 'city', 'maxlength', 50);
        $form->set($prefix . 'city', 'required', true);

        $form->set($prefix . 'state', 'maxlength', 50);

        $form->set($prefix . 'zip_code', 'maxlength', 50);

        $form->set($prefix . 'country', 'required', true);

        $form->set($prefix . 'country', 'options', $country_options);

        $system .=
            '<script>
                software.init_country({
                    country_id: "' . $prefix . 'country",
                    state_text_box_id: "' . $prefix . 'state_text_box",
                    state_pick_list_id: "' . $prefix . 'state_pick_list",
                    zip_code_id: "' . $prefix . 'zip_code"})
            </script>';

        // If there is a custom shipping form, then prepare form by prefilling, setting field
        // attributes, and getting fields.
        if ($custom_shipping_form) {

            $response = prepare_custom_form(array(
                'form' => $form,
                'page_id' => $page_id,
                'page_type' => 'express order',
                'form_type' => 'shipping',
                'ship_to_id' => $recipient['id'],
                'prefix' => $prefix,
                'folder_id' => $folder_id_for_default_value,
                'edit' => $editable
            ));

            $recipient['fields'] = $response['fields'];
            $custom_shipping_form_fields = $response['fields'];
            $date_fields = array_merge($date_fields, $response['date_fields']);
            $date_and_time_fields = array_merge($date_and_time_fields, $response['date_and_time_fields']);
            $wysiwyg_fields = array_merge($wysiwyg_fields, $response['wysiwyg_fields']);
            $edit_custom_shipping_form_start = $response['edit_start'];
            $edit_custom_shipping_form_end = $response['edit_end'];
        }

        // If there is at least one active arrival date, then require one to be selected.
        if ($arrival_dates) {
            $form->set($prefix . 'arrival_date', 'required', true);
        }
        
        foreach ($custom_arrival_dates as $custom_arrival_date) {
            $system .=
                '<script>
                    software.init_custom_arrival_date({
                        radio_field_id: "' . $prefix . 'arrival_date_' . $custom_arrival_date . '",
                        date_field_id: "' . $prefix . 'custom_arrival_date_' . $custom_arrival_date . '"})
                </script>';
        }

        $system .=
            '<script>
                software.init_shipping({
                    ship_to_id: ' . $recipient['id'] . ',
                    prefix: "' . $prefix . '",
                    selected_method_id: ' . $recipient['shipping_method_id'] . '})
            </script>';

        // If there is only one recipient or this is the myself recipient, then store ship to id,
        // so we know which recipient to copy info from for billing same as shipping.
        if ($number_of_recipients == 1 or $recipient['ship_to_name'] == 'myself') {
            $billing_same_as_shipping = true;
            $billing_same_as_shipping_id = $recipient['id'];
        }

        $recipients[$key] = $recipient;
    }

    // If there is an item in the cart, then prepare more info.
    if ($recipients) {

        // If there are shippable items, then prepare data that will allow us to dynamically
        // update shipping and total when a method is selected.
        if ($shippable_items) {
            $system .=
                '<script>
                    software.base_currency_symbol = \'' . escape_javascript(BASE_CURRENCY_SYMBOL) . '\';
                    software.base_currency_code = \'' . escape_javascript(BASE_CURRENCY_CODE) . '\';
                    software.visitor_currency_symbol = \'' . escape_javascript(VISITOR_CURRENCY_SYMBOL) . '\';
                    software.visitor_currency_exchange_rate = \'' . escape_javascript(VISITOR_CURRENCY_EXCHANGE_RATE) . '\';
                    software.visitor_currency_code_for_output = \'' . escape_javascript(VISITOR_CURRENCY_CODE_FOR_OUTPUT) . '\';
                </script>';
        }

        // If there is a recipient to copy info from for the billing address, then init js for that
        if ($billing_same_as_shipping_id) {
            $system .=
                '<script>
                    software.init_billing_same_as_shipping({id: ' . $billing_same_as_shipping_id . '})
                </script>';
        }

        $system .=
            '<script>
                software.init_country({
                    country_id: "billing_country",
                    state_text_box_id: "billing_state_text_box",
                    state_pick_list_id: "billing_state_pick_list",
                    zip_code_id: "billing_zip_code"})
            </script>';

        // If there is a custom billing form, then prepare form by prefilling, setting field attributes,
        // and getting fields.
        if ($custom_billing_form) {

            $custom_billing_form_title = $custom_billing_form_name;

            $response = prepare_custom_form(array(
                'form' => $form,
                'page_id' => $page_id,
                'page_type' => 'express order',
                'form_type' => 'billing',
                'folder_id' => $folder_id_for_default_value,
                'edit' => $editable
            ));

            $custom_billing_form_fields = $response['fields'];
            $date_fields = array_merge($date_fields, $response['date_fields']);
            $date_and_time_fields = array_merge($date_and_time_fields, $response['date_and_time_fields']);
            $wysiwyg_fields = array_merge($wysiwyg_fields, $response['wysiwyg_fields']);
            $edit_custom_billing_form_start = $response['edit_start'];
            $edit_custom_billing_form_end = $response['edit_end'];
        }
    }

    if ($layout_type == 'system') {
    
        $grand_shipping = 0;
        
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
                '<form class="special_offers" action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/express_order.php" method="post" style="margin: 0px 0px 15px 0px">
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
                    <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/express_order.php" method="post" style="margin: 0px 0px 15px 0px">
                        ' . get_token_field() . '
                        <input type="hidden" name="page_id" value="' . $page_id . '" />
                        <input type="hidden" name="quick_add" value="true" />
                        <input type="hidden" name="require_cookies" value="true" />
                        <fieldset class="software_fieldset">
                            <legend class="software_legend">' . h($quick_add_label) . '</legend>
                                <table class="quick_add">
                                    <tr>
                                        <td style="font-weight: bold">Item:</td>
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
            
            // initialize variables before we figure out which types of products exist
            $taxable_products_exist = false;
            $shippable_products_exist = false;
            $recurring_products_exist = false;
            $recurring_transaction = FALSE;
            
            // if shipping is on, get arrival dates
            // we need to do this in order to know whether to output request arrival date data for ship tos
            if (ECOMMERCE_SHIPPING == true) {
                $query =
                    "SELECT id
                    FROM arrival_dates
                    WHERE
                        (status = 'enabled')
                        AND (start_date <= CURRENT_DATE())
                        AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if there is at least one active arrival date, then arrival dates exist
                if (mysqli_num_rows($result) > 0) {
                    $arrival_dates_exist = true;
                } else {
                    $arrival_dates_exist = false;
                }
            }
            
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
                    'subtotal' => 0,
                    'tax' => 0);
            }
            
            $applied_offers = array();
            
            // create array for storing inventory quantity for products,
            // so we can keep track of remaining inventory quantity as we loop through order items,
            // so that we can determine if out of stock message should be show for an order item
            $inventory_quantities = array();

            // loop through all ship tos
            foreach ($ship_tos as $ship_to_id) {
                // get ship to name for this ship to id
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
                       countries.name as country,
                       ship_tos.address_verified,
                       ship_tos.arrival_date,
                       ship_tos.arrival_date_id,
                       ship_tos.shipping_method_id,
                       shipping_methods.name as shipping_method_name,
                       shipping_methods.description as shipping_method_description,
                       ship_tos.shipping_cost,
                       ship_tos.original_shipping_cost,
                       ship_tos.offer_id,
                       ship_tos.complete
                    FROM ship_tos
                    LEFT JOIN shipping_methods ON shipping_methods.id = ship_tos.shipping_method_id
                    LEFT JOIN countries ON ship_tos.country = countries.code
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
                $ship_to_complete = $row['complete'];
                
                // if this shipping address is verified, then convert salutation and country to all uppercase
                if ($address_verified == 1) {
                    $salutation = mb_strtoupper($salutation);
                    $country = mb_strtoupper($country);
                }

                // get all order items in cart for this ship to
                $query =
                    "SELECT
                        order_items.id,
                        order_items.product_id,
                        order_items.product_name,
                        order_items.quantity,
                        order_items.price,
                        order_items.tax,
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
                
                // initialize variables to determine which types of products are being displayed
                // these variables will be used later to determine column heading labels
                $non_donations_exist_in_non_recurring = false;
                $non_donations_exist_in_recurring = false;
                $donations_exist_in_non_recurring = false;
                $donations_exist_in_recurring = false;
                $row_count = 1;

                // foreach order item in cart
                foreach ($order_items as $order_item) {
                    $order_item_id = $order_item['id'];
                    $product_id = $order_item['product_id'];
                    $name = $order_item['product_name'];
                    $quantity = $order_item['quantity'];
                    $product_price = $order_item['price'] / 100;
                    $product_tax = $order_item['tax'] / 100;
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
                    $total_tax = $product_tax * $quantity;
                    
                    $output_total_price = prepare_price_for_output($total_price * 100, FALSE, $discounted_price = '', 'html');
                    
                    // if order item is a donation
                    if ($selection_type == 'donation') {
                        $output_donation_amount_text_box = VISITOR_CURRENCY_SYMBOL . '<input type="text" name="donations[' . $order_item_id . ']" value="' . number_format(get_currency_amount($total_price, VISITOR_CURRENCY_EXCHANGE_RATE), 2, '.', ',') . '" size="5" class="software_input_text" style="text-align: right" />' . h(VISITOR_CURRENCY_CODE_FOR_OUTPUT);
                    }
                    
                    $output_remove = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/remove_item_from_cart.php?order_item_id=' . $order_item_id . '&screen=express_order&send_to=' . h(urlencode(REQUEST_URL)) . get_token_query_string_field() . '" class="software_button_small_secondary remove_button">X</a>';
                    
                    // assume that we don't need to output a recurring schedule fieldset, until we find out otherwise
                    $output_recurring_schedule_fieldset = '';
                    
                    // if the product is a recurring product and the recurring schedule is editable by the customer, then output recurring schedule fieldset
                    if (($recurring == 1) && ($recurring_schedule_editable_by_customer == 1)) {
                        // if screen was not just submitted, then prefill recurring schedule fields
                        if (($form->field_in_session('submit_update') == false) && ($form->field_in_session('submit_purchase_now') == false)) {
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
                            if (($form->field_in_session('submit_update') == false) && ($form->field_in_session('submit_purchase_now') == false)) {
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
                            if (($form->field_in_session('submit_update') == false) && ($form->field_in_session('submit_purchase_now') == false)) {
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
                            $form_info = get_form_info(0, $product_id, $order_item_id, $quantity_number, $form_label_column_width, $office_use_only = false, $form, 'frontend', false, $device_type, $folder_id_for_default_value, $reference_code_field_id);
                            
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
                        
                        // update tax
                        $grand_tax = $grand_tax + $total_tax;
                        
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
                        
                        // if the recurring product's price is greater than 0, then a recurring transaction is required
                        if ($product_price > 0) {
                            $recurring_transaction = TRUE;
                        }
                        
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
                                <td class="mobile_right mobile_width" style="vertical-align: top">' . $output_description . '</td>
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
                        $payment_periods[$payment_period]['tax'] += $total_tax;
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
                        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                            $output_ship_to_name = '<span class="software_highlight">' . h($ship_to_name) . '</span>';
                        }
                        
                        $output_ship_tos .=
                            '<tr class="ship_tos">
                                <td colspan="6">
                                    <div class="heading">Ship to ' . $output_ship_to_name . '</div>
                                </td>
                            </tr>';
                    }
                    
                    $output_shipping = '';
                    
                    // If shipping is on and this is a real ship to, then show shipping info
                    if (ECOMMERCE_SHIPPING and $ship_to_id) {

                        $for_ship_to_name = '';

                        // if recipient mode is multi-recipient, then prepare "for [ship to name]"
                        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                            $for_ship_to_name = ' for <span class="software_highlight">' . h($ship_to_name) . '</span>';
                        }

                        // Create a prefix for all shipping fields for this recipient, in order make
                        // them unique
                        $prefix = 'shipping_' . $ship_to_id . '_';

                        $output_custom_shipping_form = '';

                        // If a custom shipping form is enabled, then output it.
                        if ($custom_shipping_form) {
                            
                            // Get form info (form content, wysiwyg fields).
                            $form_info = get_form_info($page_id, 0, 0, 0, 0, $office_use_only = false, $form, 'frontend', false, $device_type, $folder_id_for_default_value, 0, 'shipping', $prefix);
                            
                            $output_custom_shipping_form =
                                '<div class="data">
                                    <table class="custom_shipping_form" style="margin-bottom: 1.5em">
                                        ' . $form_info['content'] . '
                                    </table>
                                </div>';
                            
                            // If edit mode is on, then output grid around custom shipping form.
                            if ($editable) {
                                
                                $output_custom_shipping_form =
                                    '<div class="edit_mode" style="position: relative; border: 1px dashed #4780C5; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . h($page_id) . '&form_type=shipping&from=pages&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Custom Shipping Form">Edit</a>
                                        ' . $output_custom_shipping_form . '
                                    </div>';
                            }
                        }

                        $output_arrival_dates = '';

                        if ($arrival_dates) {

                            $output_arrival_dates .=
                                '<div class="arrival heading">Requested Arrival Date' . $for_ship_to_name . '</div>
                                <div class="data">
                                    <table class="arrival_dates" style="margin-bottom: 15px">
                                        <tr>
                                            <th style="width: 30%;text-align:left;">Select One</th>
                                            <th style="width: 70%;text-align:left;">Description</th>
                                        </tr>';

                            foreach ($arrival_dates as $arrival_date) {

                                $custom_field = '';

                                if ($arrival_date['custom']) {
                                    $custom_field =
                                        $form->output_field(array('type'=>'text', 'id' => $prefix . 'custom_arrival_date_' . $arrival_date['id'], 'name' => $prefix . 'custom_arrival_date_' . $arrival_date['id'], 'size'=>'10', 'class'=>'software_input_text')) . ' ';
                                }

                                $output_arrival_dates .=
                                    '<tr>
                                        <td style="vertical-align: top">' . $form->output_field(array('type' => 'radio', 'name' => $prefix . 'arrival_date', 'id' => $prefix . 'arrival_date_' . $arrival_date['id'], 'value'=>$arrival_date['id'],  'class'=>'software_input_radio', 'required' => 'true')) . '<label for="' . $prefix . 'arrival_date_' . $arrival_date['id'] . '"> ' . h($arrival_date['name']) . '</label></td>
                                        <td style="width: 100%;">' . $custom_field . $arrival_date['description'] . '</td>
                                    </tr>';
                            }

                            $output_arrival_dates .=
                                '    </table>
                                </div>';
                        }

                        $output_shipping =
                            '<tr class="ship_tos data">
                                <td colspan="6">

                                    <div class="heading">Shipping Address' . $for_ship_to_name . '</div>

                                    <div class="data" style="margin-bottom: 1.5em">
                                        <table>
                                            <tr>
                                                <td><label for="' . $prefix . 'salutation">Salutation</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'select',
                                                        'id' => $prefix . 'salutation',
                                                        'name' => $prefix . 'salutation',
                                                        'options' => get_salutation_options(),
                                                        'class' => 'software_select',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping honorific-prefix')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'first_name">First Name*</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'first_name',
                                                        'name' => $prefix . 'first_name',
                                                        'size' => '40',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'required' => 'true',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping given-name',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'last_name">Last Name*</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'last_name',
                                                        'name' => $prefix . 'last_name',
                                                        'size' => '40',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'required' => 'true',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping family-name',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'company">Company</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'company',
                                                        'name' => $prefix . 'company',
                                                        'size' => '40',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping organization',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'address_1">Address 1*</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'address_1',
                                                        'name' => $prefix . 'address_1',
                                                        'size' => '40',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'required' => 'true',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping address-line1',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'address_2">Address 2</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'address_2',
                                                        'name' => $prefix . 'address_2',
                                                        'size' => '40',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping address-line2',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'city">City*</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'city',
                                                        'name' => $prefix . 'city',
                                                        'size' => '40',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'required' => 'true',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping address-level2',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'country">Country*</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'select',
                                                        'id' => $prefix . 'country',
                                                        'name' => $prefix . 'country',
                                                        'options' => $country_options,
                                                        'class' => 'software_select',
                                                        'required' => 'true',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping country')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="' . $prefix . 'state_text_box">
                                                        State / Province
                                                    </label>

                                                    <label for="' . $prefix . 'state_pick_list" style="display: none">
                                                        State / Province*
                                                    </label>
                                                </td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'state_text_box',
                                                        'name' => $prefix . 'state',
                                                        'size' => '40',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping address-level1',
                                                        'spellcheck' => 'false')) .

                                                    $form->output_field(array(
                                                        'type' => 'select',
                                                        'id' => $prefix . 'state_pick_list',
                                                        'name' => $prefix . 'state',
                                                        'class' => 'software_select',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping address-level1')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'zip_code">Zip / Postal Code<span id="' . $prefix . 'zip_code_required" style="display: none">*</span></label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'text',
                                                        'id' => $prefix . 'zip_code',
                                                        'name' => $prefix . 'zip_code',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping postal-code',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Address Type</td>
                                                <td style="padding-top: 0.5em; padding-bottom: 0.5em">' . $form->output_field(array('type'=>'radio', 'name'=>$prefix . 'address_type', 'id'=>$prefix . 'address_type_residential', 'value'=>'residential', 'class'=>'software_input_radio', 'required' => 'true')) . '<label for="' . $prefix . 'address_type_residential"> Residential</label>&nbsp;&nbsp;&nbsp;&nbsp;' . $form->output_field(array('type'=>'radio', 'name'=>$prefix . 'address_type', 'id'=>$prefix . 'address_type_business', 'value'=>'business', 'class'=>'software_input_radio')) . '<label for="' . $prefix . 'address_type_business"> Business</label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="' . $prefix . 'phone_number">Phone</label></td>
                                                <td>' .
                                                    $form->output_field(array(
                                                        'type' => 'tel',
                                                        'id' => $prefix . 'phone_number',
                                                        'name' => $prefix . 'phone_number',
                                                        'maxlength' => '50',
                                                        'class' => 'software_input_text',
                                                        'autocomplete' => 'section-shipping-' . $ship_to_id . ' shipping tel',
                                                        'spellcheck' => 'false')) . '
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    ' . $output_custom_shipping_form . '
                                    ' . $output_arrival_dates . '

                                    <p
                                        id="' . $prefix . 'message"
                                        class="software_error" style="display: none">
                                    </p>

                                    <div
                                        id="' . $prefix . 'method_heading"
                                        class="heading" style="display: none"
                                    >
                                        Shipping Method' . $for_ship_to_name . '
                                    </div>

                                    <table
                                        id="' . $prefix . 'methods"
                                        class="shipping_methods" style="display: none"
                                    >
                                        
                                        <thead>
                                            <tr class="heading" style="border: none">
                                                <th style="text-align: left">Select One</th>
                                                <th style="text-align: right; padding-right:15px">
                                                    Cost
                                                </th>
                                                <th style="text-align:left">Details</th>
                                            </tr>
                                        </thead>
                                        
                                        <tr class="method_row">
                                            <td style="vertical-align: top; padding-right: 15px">
                                                <label>
                                                    <input type="radio" class="software_input_radio">
                                                    <span class="name"></span>
                                                </label>
                                            </td>

                                            <td style="vertical-align: top; text-align: right; padding-right: 15px">
                                                <div class="cost"></div>
                                            </td>

                                            <td style="vertical-align: top">
                                                <div class="delivery_date" style="display: none">
                                                    Estimated Delivery:
                                                    <strong class="date"></strong>
                                                </div>
                                                <div class="description"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>';
                            
                        // update grand shipping total
                        $grand_shipping += $shipping_cost;
                        
                        // if there is an offer applied to this ship to, then remember that and add offer to applied offers array
                        if ($ship_to_offer_id != 0) {
                            
                            // if the offer has not already been added to the applied offers array, then store this offer as an applied offer
                            if (in_array($ship_to_offer_id, $applied_offers) == false) {
                                $applied_offers[] = $ship_to_offer_id;
                            }
                        }
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
                        ' . $output_shipping . '
                        <tr class="ship_tos data">
                            <td colspan="6">&nbsp;</td>
                        </tr>';
                }

                // if there is at least one product in recurring folder for this ship to, then output header and product information
                if ($output_recurring_products) {
                    // if shipping is on and this ship to is a real ship to, then add header with ship to label
                    if ((ECOMMERCE_SHIPPING == true) && ($ship_to_id != 0)) {

                        $output_ship_to_name = '';

                        // If multi-recipient shipping is enabled, then output ship to name.
                        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
                            $output_ship_to_name = '<span class="software_highlight">' . h($ship_to_name) . '</span>';
                        }

                        $output_recurring_ship_tos .=
                            '<tr class="ship_tos">
                                <td colspan="7">
                                    <div class="heading">Ship to ' . $output_ship_to_name . '</div>
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
            }
            
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
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
            } else {
                $output_discount = '';
            }
            
            // if tax is on, update grand total and prepare tax row
            if (ECOMMERCE_TAX == true) {
                // if there is an order discount, adjust tax
                if (($subtotal > 0) && ($order_discount > 0)) {
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
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
            } else {
                $output_grand_tax = '';
            }

            $output_grand_shipping = '';
            
            // if there was at least one shipping recipient for this order, output grand shipping total
            if ($shippable_items) {
                
                // update grand total
                $grand_total = $grand_total + $grand_shipping;
                
                $output_grand_shipping =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right">Shipping:</td>
                        <td class="mobile_right" style="text-align: right"><span class="shipping">' . prepare_price_for_output($grand_shipping * 100, FALSE, $discounted_price = '', 'html') . '</span></td>
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
            }
            
            $output_gift_card_discount = '';
            $gift_card_discount = 0;
            
            // if gift cards are enabled then get applied gift cards and prepare to output gift card discount
            if (ECOMMERCE_GIFT_CARD == TRUE) {
                // get applied gift cards in order to output them
                $query =
                    "SELECT
                        id,
                        code,
                        old_balance,
                        givex
                    FROM applied_gift_cards
                    WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'
                    ORDER BY id ASC";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                $applied_gift_cards = array();
                
                $total_gift_card_balance = 0;

                // loop through applied gift cards in order to add them to array
                while ($row = mysqli_fetch_assoc($result)) {
                    $applied_gift_cards[] = $row;
                    
                    $total_gift_card_balance = $total_gift_card_balance + ($row['old_balance'] / 100);
                }
                
                // if the total is greater than 0 and there is at least 1 applied gift card, then prepare to output gift card discount
                if (($grand_total > 0) && (count($applied_gift_cards) > 0)) {
                    $output_gift_card_label_plural_suffix = '';
                    
                    // if there is more than 1 applied gift card, then prepare to output gift card label plural suffix
                    if (count($applied_gift_cards) > 1) {
                        $output_gift_card_label_plural_suffix = 's';
                    }
                    
                    // if the total gift card balance is less than the grand total, then set the gift card discount to the total gift card balance
                    if ($total_gift_card_balance < $grand_total) {
                        $gift_card_discount = $total_gift_card_balance;
                        
                    // else the total gift card balance is greater than or equal to the grand total, so set the gift card discount to the grand total
                    } else {
                        $gift_card_discount = $grand_total;
                    }
                    
                    // update the grand total
                    $grand_total = $grand_total - $gift_card_discount;
                    
                    $output_gift_card_discount =
                        '<tr class="gift_card_discount">
                            <td class="mobile_left" colspan="4" style="text-align: right">Gift Card' . $output_gift_card_label_plural_suffix . ':</td>
                            <td class="mobile_right" style="text-align: right">-' . prepare_price_for_output($gift_card_discount * 100, FALSE, $discounted_price = '', 'html') . '</td>
                        </tr>';
                }
            }
            
            // if there is a discount, tax, shipping, or gift card discount, then show subtotal row
            if ($output_discount || $output_grand_tax || $output_grand_shipping || $output_gift_card_discount) {
                $output_subtotal =
                    '<tr class="order_totals data">
                        <td class="mobile_left" colspan="4" style="text-align: right">Subtotal:</td>
                        <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($subtotal * 100, FALSE, $discounted_price = '', 'html') . '</td>
                        <td class="mobile_hide">&nbsp;</td>
                    </tr>';
            } else {
                $output_subtotal = '';
            }
            
            $output_unconverted_total = '';
            $output_multicurrency_disclaimer = '';
            
            // If the visitor's currency is different from the base currency,
            // then show actual base currency amount and disclaimer because the base currency will be charged.
            if (VISITOR_CURRENCY_CODE != BASE_CURRENCY_CODE) {
                $base_currency_name = db_value("SELECT name FROM currencies WHERE id = '" . BASE_CURRENCY_ID . "'");

                // If a base currency name was not found (e.g. no currencies),
                // then set to US dollar.
                if ($base_currency_name == '') {
                    $base_currency_name = 'US Dollar';
                }

                $output_unconverted_total = '* <span style="white-space: nowrap">(<span class="base_currency_total">' . prepare_amount($grand_total) . ' ' . h(BASE_CURRENCY_CODE) . '</span>)</span>';
                $output_multicurrency_disclaimer = '<div style="margin-bottom: 15px">*This amount is based on our current currency exchange rate to ' . h($base_currency_name) . ' and may differ from the exact charges (displayed above in ' . h($base_currency_name) . ').</div>';
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
                            '<tr class="payment data">
                                <td class="mobile_left" colspan="5" style="text-align: right">' . $payment_period_name . ' Subtotal:</td>
                                <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($payment_period['subtotal'] * 100, FALSE, $discounted_price = '', 'html') . '</td>
                                <td class="mobile_hide">&nbsp;</td>
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
                                '<tr class="payment data">
                                    <td class="mobile_left" colspan="5" style="text-align: right">' . $payment_period_name . ' Tax:</td>
                                    <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($payment_period['tax'] * 100, FALSE, $discounted_price = '', 'html') . '</td>
                                    <td class="mobile_hide">&nbsp;</td>
                                </tr>';

                        } else {
                            $payment_period_total = $payment_period['subtotal'];
                        }
                        
                        $output_payment_periods .=
                            '<tr class="payment data">
                                <td class="mobile_left" colspan="5" style="text-align: right"><strong>' . $payment_period_name . ' Total:</strong></td>
                                <td class="mobile_right" style="text-align: right"><strong>' . prepare_price_for_output($payment_period_total * 100, FALSE, $discounted_price = '', 'html') . '</strong></td>
                                <td class="mobile_hide">&nbsp;</td>
                            </tr>';
                            
                        // if this is not the last payment period, add a blank line for spacing
                        if ($count < $number_of_payment_periods) {
                            $output_payment_periods .=
                                '<tr class="payment data">
                                    <td colspan="7">&nbsp;</td>
                                </tr>';
                        }
                        
                        $count++;
                    }
                }
                
                $output_recurring_products =
                    '<div class="recurring_products">
                    <fieldset style="margin-bottom: 15px" class="software_fieldset">
                        <legend class="software_legend">Recurring Charges</legend>
                            <table class="products" style="width: 100%">
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
                    '<table style="margin-bottom: 15px">
                        <tr>
                            <td class="offer_code_label mobile_left">' . h($special_offer_code_label) . '</td>
                            <td class="mobile_hide" style="padding-right: .5em;"></td>
                            <td class="offer_code_field mobile_left mobile_width">' . $form->output_field(array('type'=>'text', 'name'=>'special_offer_code', 'size'=>'15', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                            <td class="mobile_hide" style="padding-right: .5em;"></td>
                            <td class="offer_code_message mobile_left mobile_width">' . h($special_offer_code_message) . '</td>
                        </tr>
                    </table>';
            } else {
                $output_special_offer = '';
            }
            
            // if the total is greater than 0, then a non-recurring transaction is required
            if ($grand_total > 0) {
                $nonrecurring_transaction = TRUE;
                
            // else the total is 0, so a non-recurring transaction is not required
            } else {
                $nonrecurring_transaction = FALSE;
            }
            
            // if an update button label was entered for the page, then use that
            if ($update_button_label) {
                $output_update_button_label = h($update_button_label);
                
            // else if a shopping cart label is found, then use that with "Update" in front of the label
            } elseif ($shopping_cart_label) {
                $output_update_button_label = 'Update ' . h($shopping_cart_label);
                
            // else an update button label could not be found, so just use a default label
            } else {
                $output_update_button_label = 'Update Cart';
            }
            
            // If user is logged in and not ghosting, then get contact for user (we will use this
            // contact id in a couple of places)
            if (USER_LOGGED_IN and !$ghost) {
                // get contact for user
                $query = "SELECT contacts.id
                         FROM user
                         LEFT JOIN contacts ON user_contact = contacts.id
                         WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                $row = mysqli_fetch_assoc($result);
                $contact_id = $row['id'];
                
                // if a contact was found, get contact information
                if ($contact_id) {
                    $query = "SELECT
                                salutation,
                                first_name,
                                last_name,
                                company,
                                business_address_1,
                                business_address_2,
                                business_city,
                                business_state,
                                business_zip_code,
                                business_country,
                                business_phone,
                                business_fax,
                                email_address,
                                lead_source,
                                opt_in
                             FROM contacts
                             WHERE id = $contact_id";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $contact = mysqli_fetch_assoc($result);
                }
            }
            
            // if form was not just filled out, get data from order in order to populate fields
            if ($form->field_in_session('billing_first_name') == false) {
                $query = "SELECT * FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $order = mysqli_fetch_assoc($result);

                if ($order['billing_salutation'] != '') {
                    $form->assign_field_value('billing_salutation', $order['billing_salutation']);
                } else if ($contact['salutation'] != '') {
                    $form->assign_field_value('billing_salutation', $contact['salutation']);
                }

                if ($order['billing_first_name'] != '') {
                    $form->assign_field_value('billing_first_name', $order['billing_first_name']);
                } else if ($contact['first_name'] != '') {
                    $form->assign_field_value('billing_first_name', $contact['first_name']);
                }

                if ($order['billing_last_name'] != '') {
                    $form->assign_field_value('billing_last_name', $order['billing_last_name']);
                } else if ($contact['last_name'] != '') {
                    $form->assign_field_value('billing_last_name', $contact['last_name']);
                }

                if ($order['billing_company'] != '') {
                    $form->assign_field_value('billing_company', $order['billing_company']);
                } else if ($contact['company'] != '') {
                    $form->assign_field_value('billing_company', $contact['company']);
                }

                if ($order['billing_address_1'] != '') {
                    $form->assign_field_value('billing_address_1', $order['billing_address_1']);
                } else if ($contact['business_address_1'] != '') {
                    $form->assign_field_value('billing_address_1', $contact['business_address_1']);
                }

                if ($order['billing_address_2'] != '') {
                    $form->assign_field_value('billing_address_2', $order['billing_address_2']);
                } else if ($contact['business_address_2'] != '') {
                    $form->assign_field_value('billing_address_2', $contact['business_address_2']);
                }

                if ($order['billing_city'] != '') {
                    $form->assign_field_value('billing_city', $order['billing_city']);
                } else if ($contact['business_city'] != '') {
                    $form->assign_field_value('billing_city', $contact['business_city']);
                }

                if ($order['billing_state'] != '') {
                    $form->assign_field_value('billing_state', $order['billing_state']);
                } else if ($contact['business_state'] != '') {
                    $form->assign_field_value('billing_state', $contact['business_state']);
                }

                if ($order['billing_zip_code'] != '') {
                    $form->assign_field_value('billing_zip_code', $order['billing_zip_code']);
                } else if ($contact['business_zip_code'] != '') {
                    $form->assign_field_value('billing_zip_code', $contact['business_zip_code']);
                }

                if ($order['billing_country'] != '') {
                    $form->assign_field_value('billing_country', $order['billing_country']);
                } else if ($contact['business_country'] != '') {
                    $form->assign_field_value('billing_country', $contact['business_country']);

                // Otherwise country is blank, so set default country.
                } else {
                    $query = "SELECT code FROM countries WHERE default_selected = 1";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    // if a default country was found, set default country
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $form->assign_field_value('billing_country', $row['code']);
                    }
                }

                if ($order['billing_phone_number'] != '') {
                    $form->assign_field_value('billing_phone_number', $order['billing_phone_number']);
                } else if ($contact['business_phone'] != '') {
                    $form->assign_field_value('billing_phone_number', $contact['business_phone']);
                }

                if ($order['billing_fax_number'] != '') {
                    $form->assign_field_value('billing_fax_number', $order['billing_fax_number']);
                } else if ($contact['business_fax'] != '') {
                    $form->assign_field_value('billing_fax_number', $contact['business_fax']);
                }

                if ($order['billing_email_address'] != '') {
                    $form->assign_field_value('billing_email_address', $order['billing_email_address']);
                } else if ($contact['email_address'] != '') {
                    $form->assign_field_value('billing_email_address', $contact['email_address']);
                }

                if ($order['custom_field_1'] != '') {
                    $form->assign_field_value('custom_field_1', $order['custom_field_1']);
                }

                if ($order['custom_field_2'] != '') {
                    $form->assign_field_value('custom_field_2', $order['custom_field_2']);
                }

                if ($order['po_number'] != '') {
                    $form->assign_field_value('po_number', $order['po_number']);
                }

                if ($order['referral_source_code'] != '') {
                    $form->assign_field_value('referral_source', $order['referral_source_code']);
                } else if ($contact['lead_source'] != '') {
                    $form->assign_field_value('referral_source', $contact['lead_source']);
                }

                if ($order['opt_in'] == 1) {
                    $form->assign_field_value('opt_in', '1');
                } else {
                    $form->assign_field_value('opt_in', '');
                }

                // If the visitor is logged in and not ghosting and the visitor has selected
                // that he/she does not want his/her contact info updated with the billing info
                // previously in this session, then uncheck the update contact check box.
                if (USER_LOGGED_IN and !$ghost and ($_SESSION['software']['update_contact'] === false)) {
                    $form->assign_field_value('update_contact', '');
                } else {
                    $form->assign_field_value('update_contact', '1');
                }
                
                if ($order['tax_exempt'] == 1) {
                    $form->assign_field_value('tax_exempt', '1');
                } else {
                    $form->assign_field_value('tax_exempt', '');
                }
            }

            $output_billing_same_as_shipping = '';

            if ($billing_same_as_shipping) {

                $output_billing_same_as_shipping =
                    '<div class="billing_same_as_shipping" style="margin: 7px 0">
                        <label>
                            <input
                                type="checkbox"
                                id="billing_same_as_shipping"
                                class="software_input_checkbox"
                            >
                            Billing Address Same as Shipping
                        </label>
                    </div>';
            }
            
            $output_custom_field_1 = '';
            
            // if there is a custom field 1 label, then prepare to output custom field 1
            if ($custom_field_1_label) {
                $output_custom_field_1_label = $custom_field_1_label;
                $required = '';
                
                // if custom field 1 is required, then prepare asterisk
                if ($custom_field_1_required == 1) {
                    $output_custom_field_1_label .= '*';
                    $required = 'true';
                }
                
                $output_custom_field_1 =
                    '<tr>
                        <td><label for="custom_field_1">' . h($output_custom_field_1_label) . '</label></td>
                        <td>' .
                            $form->output_field(array(
                                'type' => 'text',
                                'id' => 'custom_field_1',
                                'name' => 'custom_field_1',
                                'maxlength' => '255',
                                'class' => 'software_input_text',
                                'required' => $required)) . '
                        </td>
                    </tr>';
            }
            
            $output_custom_field_2 = '';
            
            // if there is a custom field 2 label, then prepare to output custom field 2
            if ($custom_field_2_label) {
                $output_custom_field_2_label = $custom_field_2_label;
                $required = '';
                
                // if custom field 2 is required, then prepare asterisk
                if ($custom_field_2_required == 1) {
                    $output_custom_field_2_label .= '*';
                    $required = 'true';
                }
                
                $output_custom_field_2 =
                    '<tr>
                        <td><label for="custom_field_2">' . h($output_custom_field_2_label) . '</label></td>
                        <td>' .
                            $form->output_field(array(
                                'type' => 'text',
                                'id' => 'custom_field_2',
                                'name' => 'custom_field_2',
                                'maxlength' => '255',
                                'class' => 'software_input_text',
                                'required' => $required)) . '
                        </td>
                    </tr>';
            }
            
            $output_custom_field_spacing = '';
            
            // if there is a custom field 1 or custom field 2 to output, prepare spacing
            if ($output_custom_field_1 || $output_custom_field_2) {
                $output_custom_field_spacing =
                    '<tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>';
            }
            
            // assume that the opt in field will not be displayed until we find out otherwise
            $output_opt_in = '';
            $output_opt_in_displayed_value = 'false';
            
            // if contact cannot be found or if contact is opted out, we are going to display opt-in field
            if ((!$contact_id) || ($contact['opt_in'] == 0)) {
                $output_opt_in =
                    '<tr>
                        <td>&nbsp;</td>
                        <td>' . $form->output_field(array('type'=>'checkbox', 'name'=>'opt_in', 'id'=>'opt_in', 'value' => '1', 'checked'=>'checked', 'class'=>'software_input_checkbox')) . '<label for="opt_in"> ' . h(OPT_IN_LABEL) . '</label></td>
                    </tr>';
                
                $output_opt_in_displayed_value = 'true';
            }
            
            // if po number is enabled, output po number field
            if ($po_number) {
                $output_po_number =
                    '<tr>
                        <td><label for="po_number">PO Number</label></td>
                        <td>' .
                            $form->output_field(array(
                                'type' => 'text',
                                'id' => 'po_number',
                                'name' => 'po_number',
                                'maxlength' => '50',
                                'class' => 'software_input_text',
                                'spellcheck' => 'false')) . '
                        </td>
                    </tr>';
            }
            
            // check to see if there is at least one referral source
            $query = "SELECT name, code FROM referral_sources";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if there is at least one referral source, prepare referral source drop-down selection field
            if (mysqli_num_rows($result) > 0) {
                $referral_source_options[''] = '';

                // loop through all referral sources to prepare all options for selection drop-down field
                while ($row = mysqli_fetch_assoc($result)) {
                    $referral_source_options[$row['name']] = $row['code'];
                }
                
                $output_referral_source =
                    '<tr>
                        <td><label for="referral_source">How did you hear about us?</label></td>
                        <td>' .
                            $form->output_field(array(
                                'type' => 'select',
                                'id' => 'referral_source',
                                'name' => 'referral_source',
                                'options' => $referral_source_options,
                                'class' => 'software_select')) . '
                        </td>
                    </tr>';
            }

            $output_update_contact = '';

            // If this visitor is logged in and not ghosting, then output check box to allow the visitor
            // to decide if he/she wants his/her contact info to be updated with the billing info.
            if (USER_LOGGED_IN and !$ghost) {
                // If tax exempt area is going to be outputted below this area,
                // then just output a little bottom margin.
                if ((ECOMMERCE_TAX == true) && (ECOMMERCE_TAX_EXEMPT == true)) {
                    $output_margin_bottom = '.7em';

                // Otherwise the tax exempt area is not going to be outputted,
                // so output more margin, so there is a greater space before the next area.
                } else {
                    $output_margin_bottom = '1.5em';
                }

                $output_update_contact =
                    '<div class="update_contact" style="margin-bottom: ' . $output_margin_bottom . '">' .
                        $form->output_field(array(
                            'type' => 'checkbox',
                            'id' => 'update_contact',
                            'name' => 'update_contact',
                            'value' => '1',
                            'class' => 'software_input_checkbox')) .
                        '<label for="update_contact"> Update my contact info with this billing info.</label>
                    </div>';
            }

            $output_tax_exempt = '';

            // if tax is on and tax-exempt is allowed, prepare tax-exempt checkbox
            if ((ECOMMERCE_TAX == true) && (ECOMMERCE_TAX_EXEMPT == true)) {
                if (ECOMMERCE_TAX_EXEMPT_LABEL) {
                    $output_tax_exempt_label = ECOMMERCE_TAX_EXEMPT_LABEL;
                } else {
                    $output_tax_exempt_label = 'Tax-Exempt?';
                }

                $output_tax_exempt =
                    '<div style="margin-bottom: 1.5em; text-align: left">
                        ' . $form->output_field(array('type'=>'checkbox', 'name'=>'tax_exempt', 'value' => '1', 'id'=>'tax_exempt', 'class'=>'software_input_checkbox')) . '<label for="tax_exempt"> ' . h($output_tax_exempt_label) . '</label>
                    </div>';
            }

            $output_custom_billing_form = '';

            // If a custom billing form is enabled, then output it.
            if ($custom_billing_form == 1) {
                $output_form_name = '';
                
                // If there is a form name, then output form name.
                if ($custom_billing_form_name != '') {
                    $output_form_name = '<div class="heading">' . h($custom_billing_form_name) . '</div>';
                }
                
                // Get form info (form content, wysiwyg fields).
                $form_info = get_form_info($page_id, 0, 0, 0, $custom_billing_form_label_column_width, $office_use_only = false, $form, 'frontend', false, $device_type, $folder_id_for_default_value, 0, 'billing');
                
                $output_custom_billing_form =
                    $output_form_name . '
                    <div class="data">
                        <table class="custom_billing_form" style="margin-bottom: 1.5em">
                            ' . $form_info['content'] . '
                        </table>
                    </div>';
                
                // If edit mode is on, then output grid around custom billing form.
                if ($editable == true) {
                    $output_title = 'Custom Billing Form';
                    
                    // if the form name is not blank, then add it to the title
                    if ($custom_billing_form_name != '') {
                        $output_title .= ': ' . h($custom_billing_form_name);
                    }
                    
                    $output_custom_billing_form =
                        '<div class="edit_mode" style="position: relative; border: 1px dashed #4780C5; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&form_type=billing&from=pages&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $output_title . '">Edit</a>
                            ' . $output_custom_billing_form . '
                        </div>';
                }
            }

            $output_applied_gift_cards = '';
            
            // If gift cards are enabled and there is at least one applied gift card, then output them.
            if ((ECOMMERCE_GIFT_CARD == true) && (count($applied_gift_cards) > 0)) {
                $output_applied_gift_cards =
                    '<div class="applied_gift_cards" style="margin-bottom: 1em">
                        <div class="heading">Applied Gift Cards</div>
                        <div class="data">
                        <ul style="margin-top: 0em">';
                
                // set the amount that needs to be redeemed from all gift cards
                $required_redemption_amount = $gift_card_discount * 100;
                
                // initialize a variable for tracking the amount that will be redeemed so far
                $current_redemption_amount = 0;
                
                // loop through applied gift cards
                foreach ($applied_gift_cards as $applied_gift_card) {
                    $remaining_redemption_amount = $required_redemption_amount - $current_redemption_amount;
                    
                    // if the balance of this applied gift card is less than or equal to the remaining redemption amount, then redeem the full balance of the gift card
                    if ($applied_gift_card['old_balance'] <= $remaining_redemption_amount) {
                        $amount = $applied_gift_card['old_balance'];
                        
                    // else the balance of this applied gift card is greater than the remaining redemption amount, so just redeem the remaining redemption amount
                    } else {
                        $amount = $remaining_redemption_amount;
                    }
                    
                    $remaining_balance = $applied_gift_card['old_balance'] - $amount;

                    if ($applied_gift_card['givex'] == 0) {
                        $protected_gift_card_code = protect_gift_card_code($applied_gift_card['code']);
                    } else {
                        $protected_gift_card_code = protect_givex_gift_card_code($applied_gift_card['code']);
                    }
                    
                    $output_applied_gift_cards .= '<li>' . h($protected_gift_card_code) . ' (Remaining Balance: ' . prepare_price_for_output($remaining_balance, FALSE, $discounted_price = '', 'html') . ') <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/remove_gift_card_from_order.php?applied_gift_card_id=' . $applied_gift_card['id'] . '&send_to=' . h(urlencode(REQUEST_URL)) . get_token_query_string_field() . '" class="software_button_small_secondary remove_button" title="Remove">X</a></li>';
                    
                    $current_redemption_amount = $current_redemption_amount + $amount;
                }
                
                $output_applied_gift_cards .=
                    '   </ul>
                    </div></div>';
            }
            
            // if PayPal Express Checkout payment method is active, then prepare PayPal Express Checkout purchase now button label
            if (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == true) {
                $paypal_express_checkout_purchase_now_button_label = 'Continue to PayPal';
            }
            
            // if a purchase now button label was entered for the page, then use that for standard purchase now button label
            if ($purchase_now_button_label) {
                $standard_purchase_now_button_label = $purchase_now_button_label;
                
            // else a purchase now button label could not be found, so use a default label for standard purchase now button label
            } else {
                $standard_purchase_now_button_label = 'Purchase Now';
            }
            
            // assume that we will not output payment information until we find out otherwise
            $output_payment_information = '';

            // assume that the credit/debit card payment method will not be shown until we find out otherwise
            $show_credit_debit_card_payment_method = FALSE;
            
            // assume that the paypal express checkout payment method will not be shown until we find out otherwise
            $show_paypal_express_checkout_payment_method = FALSE;

            $output_surcharge_rows = '';
            
            // if a non-recurring or recurring transaction is required and at least one payment method will be outputted,
            // then the payment information should be outputted, so prepare to output it
            if (
                (
                    ($nonrecurring_transaction == TRUE)
                    || ($recurring_transaction == TRUE)
                )
                &&
                (
                    ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && ((ECOMMERCE_AMERICAN_EXPRESS === true) || (ECOMMERCE_DINERS_CLUB == true) || (ECOMMERCE_DISCOVER_CARD == true) || (ECOMMERCE_MASTERCARD == true) || (ECOMMERCE_VISA == true)))
                    || ((ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == TRUE) && ($recurring_transaction == FALSE))
                    ||
                    (
                        (ECOMMERCE_OFFLINE_PAYMENT == TRUE)
                        &&
                        (
                            ($offline_payment_allowed == '1')
                            || ($offline_payment_always_allowed == 1)
                        )
                    )
                    || ((ECOMMERCE_GIFT_CARD == true) && ($nonrecurring_transaction == true))
                )
            ) {
                // if the credit/debit card payment method is on and there is at least one accepted card, then remember that the payment method should be shown
                if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && ((ECOMMERCE_AMERICAN_EXPRESS === true) || (ECOMMERCE_DINERS_CLUB == true) || (ECOMMERCE_DISCOVER_CARD == true) || (ECOMMERCE_MASTERCARD == true) || (ECOMMERCE_VISA == true))) {
                    $show_credit_debit_card_payment_method = TRUE;
                }
                
                // if the PayPal Express Checkout payment method is on and a recurring transaction is not required, then remember that the payment method should be shown
                if ((ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == TRUE) && ($recurring_transaction == FALSE)) {
                    $show_paypal_express_checkout_payment_method = TRUE;
                }

                $output_payment_methods = '';

                // If gift cards are enabled and if a non-recurring transaction is required,
                // then the customer might want to add a gift card, so output gift card code field.
                if ((ECOMMERCE_GIFT_CARD == true) && ($nonrecurring_transaction == true)) {
                    $output_payment_methods .= '<div style="margin-bottom: 1em">Gift Card Code: ' . $form->output_field(array('type'=>'text', 'name'=>'gift_card_code', 'size'=>'30', 'maxlength'=>'50', 'class'=>'software_input_text mobile_text_width')) . ' <input type="submit" name="submit_apply_gift_card" value="Apply" class="software_input_submit_small_secondary" formnovalidate></div>';
                }
                
                // if the credit/debit card payment method should be shown, then prepare to output it
                if ($show_credit_debit_card_payment_method == TRUE) {
                    // if the Credit/Debit Card payment method is the only payment method, then select radio button by default
                    if (
                        ($show_paypal_express_checkout_payment_method == FALSE)
                        &&
                        (
                            (ECOMMERCE_OFFLINE_PAYMENT == FALSE)
                            ||
                            (
                                ($offline_payment_allowed == '0')
                                && ($offline_payment_always_allowed == 0)
                            )
                        )
                    ) {
                        $credit_debit_card_checked = 'checked';
                        
                    } else {
                        $credit_debit_card_checked = '';
                    }
                    
                    $expiration_month_options = array(
                        '-Select Month-' => '',
                        '01' => '01',
                        '02' => '02',
                        '03' => '03',
                        '04' => '04',
                        '05' => '05',
                        '06' => '06',
                        '07' => '07',
                        '08' => '08',
                        '09' => '09',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12');
                    
                    // prepare expiration year options (use current year through 10 years from now)
                    $expiration_year_options['-Select Year-'] = '';

                    $first_year = date('Y');
                    $last_year = $first_year + 10;
                    for ($i = $first_year; $i <= $last_year; $i++) {
                        $expiration_year_options[$i] = $i;
                    }
                    
                    // if there is a page that explains the card verification number field, prepare link to that page
                    if ($card_verification_number_page_id) {

                        $card_verification_number_page_name = get_page_name($card_verification_number_page_id);

                        $output_card_verification_number_help =
                            '&nbsp; <a href="' . OUTPUT_PATH . h(encode_url_path($card_verification_number_page_name)) . '" target="_blank" style="white-space: nowrap">What is this?</a>';
                    }

                    $output_surcharge_message = '';
                    $surcharge = 0;

                    // If a credit card surcharge is enabled and a non-recurring transaction is required
                    // (i.e. total is greater than 0), then deal with surcharge.
                    if (
                        (ECOMMERCE_SURCHARGE_PERCENTAGE > 0)
                        && ($nonrecurring_transaction)
                        && ($show_credit_debit_card_payment_method)
                    ) {
                        $surcharge = round(ECOMMERCE_SURCHARGE_PERCENTAGE / 100 * $grand_total, 2);

                        $grand_total_with_surcharge = $grand_total + $surcharge;

                        $output_surcharge_unconverted_total = '';

                        // If the visitor's currency is different from the base currency,
                        // then show actual base currency amount because the base currency will be charged.
                        if (VISITOR_CURRENCY_CODE != BASE_CURRENCY_CODE) {
                            $output_surcharge_unconverted_total = '* <span style="white-space: nowrap">(' . prepare_amount($grand_total_with_surcharge) . ' ' . h(BASE_CURRENCY_CODE) . ')</span>';
                        }

                        $output_surcharge_rows =
                            '<tr class="order_totals data surcharge_row" id="software_surcharge_row">
                                <td class="mobile_left" colspan="4" style="text-align: right">Surcharge:</td>
                                <td class="mobile_right" style="text-align: right">' . prepare_price_for_output($surcharge * 100, FALSE, $discounted_price = '', 'html') . '</td>
                            </tr>
                            <tr class="order_totals data surcharge_total_row" id="software_surcharge_total_row">
                                <td class="mobile_left" colspan="4" style="text-align: right"><strong>Total Due:</strong></td>
                                <td class="mobile_right" style="text-align: right">
                                    <strong>' . prepare_price_for_output($grand_total_with_surcharge * 100, FALSE, $discounted_price = '', 'html') . $output_surcharge_unconverted_total . '</strong>
                                    <input type="hidden" name="total_with_surcharge" value="' . h($grand_total_with_surcharge) . '">
                                </td>
                            </tr>';

                        // If there are multiple payment methods, then output surcharge warning,
                        // because the surcharge might not have been included in the totals
                        // above until the customer selected the credit card payment method.
                        if (
                            ($show_paypal_express_checkout_payment_method)
                            ||
                            (
                                (ECOMMERCE_OFFLINE_PAYMENT)
                                &&
                                (
                                    ($offline_payment_allowed == '1')
                                    || ($offline_payment_always_allowed == 1)
                                )
                            )
                        ) {
                            // Output surcharge percentage with unnecessary zeros removed.
                            $output_surcharge_message = '<div class="software_surcharge_message" style="margin-top: .25em">' . h(floatval(ECOMMERCE_SURCHARGE_PERCENTAGE)) . '% surcharge has been added.</div>';
                        }
                    }
                    
                    $output_payment_methods .=
                        '<div class="payment data" style="margin-bottom: 1em">
                            <div style="margin-bottom: 10px">' .
                                $form->output_field(array('type'=>'radio', 'name'=>'payment_method', 'id'=>'payment_method_credit_debit_card', 'value'=>'Credit/Debit Card', 'checked'=>$credit_debit_card_checked, 'class'=>'software_input_radio', 'required' => 'true')) .
                                '<label for="payment_method_credit_debit_card"> Credit/Debit Card</label>
                            </div>
                            <div id="credit_debit_card_fields" class="credit_debit_card" style="margin-top: .25em">
                                <table>
                                    <tr>
                                        <td
                                            class="mobile_left"
                                            style="padding-right: 15px; vertical-align: top"
                                        >
                                            <label for="card_number">Card Number*</label> ' .

                                            $form->field(array(
                                                'type' => 'tel',
                                                'id' => 'card_number',
                                                'name' => 'card_number',
                                                'autocomplete' => 'cc-number',
                                                'spellcheck' => 'false',
                                                'inputmode' => 'numeric',
                                                'size' => '20',
                                                'class' => 'software_input_text')) . '
                                        </td>

                                        <td
                                            class="mobile_left"
                                            style="padding-right: 15px; vertical-align: top"
                                        >
                                            <label for="expiration">Expiration*</label> ' .

                                            $form->field(array(
                                                'type' => 'tel',
                                                'id' => 'expiration',
                                                'name' => 'expiration',
                                                'autocomplete' => 'cc-exp',
                                                'spellcheck' => 'false',
                                                'inputmode' => 'numeric',
                                                'placeholder' => 'MM / YY',
                                                'size' => '9',
                                                'class' => 'software_input_text')) . '
                                        </td>

                                        <td
                                            class="card_verification_number mobile_left"
                                            style="vertical-align: top"
                                        >
                                            <label for="card_verification_number">Security Code*</label> ' .

                                            $form->field(array(
                                                'type'=>'tel',
                                                'id' => 'card_verification_number',
                                                'name' => 'card_verification_number',
                                                'autocomplete' => 'cc-csc',
                                                'spellcheck' => 'false',
                                                'inputmode' => 'numeric',
                                                'placeholder' => 'CSC',
                                                'size' => '4',
                                                'maxlength' => '4',
                                                'class' => 'software_input_text')) .

                                            $output_card_verification_number_help . '
                                        </td>
                                    </tr>
                                </table>
                                <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery.payment.min.js"></script>
                                ' . $output_surcharge_message . '
                            </div>
                        </div>';
                }
                
                // if the paypal express checkout payment method should be shown, then prepare to output it
                if ($show_paypal_express_checkout_payment_method == TRUE) {
                    // if the PayPal Express Checkout payment method is the only payment method, then select radio button by default
                    if (
                        ($show_credit_debit_card_payment_method == FALSE)
                        &&
                        (
                            (ECOMMERCE_OFFLINE_PAYMENT == FALSE)
                            ||
                            (
                                ($offline_payment_allowed == '0')
                                && ($offline_payment_always_allowed == 0)
                            )
                        )
                    ) {
                        $paypal_express_checkout_checked = 'checked';
                        
                    } else {
                        $paypal_express_checkout_checked = '';
                    }
                    
                    $output_payment_methods .= '<div class="payment data" style="margin-bottom: 1em">' . $form->output_field(array('type'=>'radio', 'name'=>'payment_method', 'id'=>'payment_method_paypal_express_checkout', 'value'=>'PayPal Express Checkout', 'checked'=>$paypal_express_checkout_checked, 'class'=>'software_input_radio', 'required' => 'true')) . '<label for="payment_method_paypal_express_checkout"> <img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/paypal.png" width="40" height="26" border="0" alt="PayPal" onclick="document.getElementById(\'payment_method_paypal_express_checkout\').checked = true;"></label></div>';
                }
                
                // if allow offline orders is on, and if this order is allowed to be paid for offline, then output the offline payment method
                if (
                    (ECOMMERCE_OFFLINE_PAYMENT == TRUE)
                    &&
                    (
                        ($offline_payment_allowed == '1')
                        || ($offline_payment_always_allowed == 1)
                    )
                ) {
                    // if the offline payment label is blank, then set it to the default label
                    if ($offline_payment_label == '') {
                        $offline_payment_label = 'Offline Payment';
                    }
                    
                    // set the offline payment label in the session so that it can be used on the order receipt screen
                    $_SESSION['ecommerce']['offline_payment_label'] = $offline_payment_label;
                    
                    // if the offline payment method is the only payment method, then select radio button by default
                    if (($show_credit_debit_card_payment_method == FALSE) && ($show_paypal_express_checkout_payment_method == FALSE)) {
                        $offline_payment_checked = 'checked';
                        
                    } else {
                        $offline_payment_checked = '';
                    }
                    
                    // output the radio button for the payment method
                    $output_payment_methods .= '<div class="payment data" style="margin-bottom: 1em">' . $form->output_field(array('type'=>'radio', 'name'=>'payment_method', 'id'=>'payment_method_offline_payment', 'value'=>'Offline Payment', 'checked'=>$offline_payment_checked, 'class'=>'software_input_radio', 'required' => 'true')) . '<label for="payment_method_offline_payment"> ' . h($offline_payment_label) . '</label></div>';
                }
                
                // output the payment information
                $output_payment_information =
                    '<div class="heading">Payment Information</div>
                    ' . $output_payment_methods;

                $system .= '<script>software.init_payment_method()</script>';
            }
            
            $output_offline_payment_allowed = '';
            
            // If offline payment is on, and this page is not set to always allow offline payment,
            // and the user is logged in, and if the user is at least a manager or if they have access
            // to set offline payment, then output check box to allow user to enable offline payment for this order.
            // We don't want to show this check box if this page is set to always allow offline payment,
            // because it would be confusing for the user, because offline payment will be allowed regardless
            // of whether the user checks this check box or not.
            if (
                (ECOMMERCE_OFFLINE_PAYMENT == TRUE)
                && ($offline_payment_always_allowed == 0)
                && (isset($user) == TRUE)
                &&
                (
                    ($user['role'] < 3)
                    || ($user['set_offline_payment'] == TRUE)
                )
            ) {
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
            
            $output_terms = '';
            
            // if a terms page was selected for this express order page, prepare to output terms and conditions checkbox
            if ($terms_page_name) {
                $output_terms = '<div class="order_terms" style="margin-bottom: 15px">' . $form->output_field(array('type'=>'checkbox', 'id'=>'terms', 'name'=>'terms', 'class'=>'software_input_checkbox', 'required' => 'true')) . '<label for="terms"> I agree to the </label><a href="' . OUTPUT_PATH . h($terms_page_name) . '" target="_blank">terms and conditions</a>.</div>';
            }
            
            // if the total is greater than 0 and there is not an active payment method, then prepare error and do not show purchase now button
            if (
                ($grand_total > 0)
                &&
                (
                    (ECOMMERCE_GIFT_CARD == FALSE)
                    && (ECOMMERCE_CREDIT_DEBIT_CARD == FALSE)
                    &&
                    (
                        (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == FALSE)
                        ||
                        (
                            (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == TRUE)
                            && ($recurring_transaction == TRUE)
                        )
                    )
                    &&
                    (
                        (ECOMMERCE_OFFLINE_PAYMENT == FALSE) 
                        ||
                        (
                            ($offline_payment_allowed == '0')
                            && ($offline_payment_always_allowed == 0)
                        )
                    )
                )
            ) {
                $form->mark_error('payment_method', 'Sorry, this order may not be submitted, because there is not an available payment method.  Please contact the administrator of this website.');
                
                $output_purchase_now_button = '';
                
            // else the total is 0 or there is an active payment method, so prepare to output purchase now button
            } else {

                $paypal_label = '';

                // If PayPal method is shown, then store PayPal label in purchase now button so that,
                // init_payment_method can update button label to that when necessary.
                if ($show_paypal_express_checkout_payment_method) {
                    $paypal_label = ' data-paypal-label="Continue to PayPal"';
                }

                $output_purchase_now_button = '<input type="submit" name="submit_purchase_now" id="submit_purchase_now" value="' .  h($standard_purchase_now_button_label) . '" class="software_input_submit_primary purchase_button" style="margin: 0 0 .5em .5em"' . $paypal_label . '>';
            }

            // If there is at least one rich-text editor field, then output JS for them.
            if ($wysiwyg_fields) {
                $system .= get_wysiwyg_editor_code($wysiwyg_fields);
            }

            // If there are shippable items, then prepare data that will allow us to dynamically
            // update shipping and total when a method is selected.
            if ($shippable_items) {
                $system .=
                    '<script>
                        software.shipping = ' . $grand_shipping . ';
                        software.gift_card_discount = ' . $gift_card_discount . ';
                        software.surcharge = ' . $surcharge . ';
                        software.total_without_shipping = ' . ($grand_total - $grand_shipping) . ';
                    </script>';
            }
            
            $output_express_order =
                '<form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/express_order.php" method="post" style="margin: 0px 0px 15px 0px">
                    ' . get_token_field() . '
                    <input type="hidden" name="page_id" value="' . $page_id . '">
                    <input type="hidden" name="require_cookies" value="true">
                    <input type="hidden" name="opt_in_displayed" value="' . $output_opt_in_displayed_value . '">
                    <input type="hidden" name="folder_id" value="' . $folder_id_for_default_value . '">

                    <table class="products" style="width: 100%; margin-bottom: 15px">
                        ' . $output_ship_tos . '
                        <tr class="order_totals">
                            <td colspan="6">
                                <div class="heading">Order Totals</div>
                            </td>
                        </tr>
                        ' . $output_subtotal . '
                        ' . $output_discount . '
                        ' . $output_grand_tax . '
                        ' . $output_grand_shipping . '
                        ' . $output_gift_card_discount . '
                        ' . $output_surcharge_rows . '
                        <tr class="ship_tos data total_row" id="software_total_row">
                            <td class="mobile_left" colspan="4" style="text-align: right; font-weight: bold; white-space: nowrap">Total Due:</td>
                            <td class="mobile_right" style="text-align: right"><strong><span class="total">' . prepare_price_for_output($grand_total * 100, FALSE, $discounted_price = '', 'html') . '</span>' . $output_unconverted_total . '</strong></td>
                            <td class="mobile_hide">&nbsp;</td>
                        </tr>
                    </table>
                    ' . $output_multicurrency_disclaimer . '
                    ' . $output_recurring_products . '
                    <div class="offer_code data">
                    ' . $output_applied_offers . '
                    ' . $output_special_offer . '
                        <div style="text-align: right; margin-bottom: 15px">
                            <input type="submit" name="submit_update" value="' . $output_update_button_label . '" class="software_input_submit_secondary update_button" formnovalidate>
                        </div>
                    </div>
                    <div class="billing heading">Billing Information</div>
                    <div class="billing data">
                        ' . $output_billing_same_as_shipping . '
                        <table style="margin-bottom: 1.5em">
                            ' . $output_custom_field_1 . '
                            ' . $output_custom_field_2 . '
                            ' . $output_custom_field_spacing . '
                            <tr>
                                <td><label for="billing_salutation">Salutation</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'select',
                                        'id' => 'billing_salutation',
                                        'name' => 'billing_salutation',
                                        'options' => get_salutation_options(),
                                        'class' => 'software_select',
                                        'autocomplete' => 'section-billing billing honorific-prefix')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_first_name">First Name*</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_first_name',
                                        'name' => 'billing_first_name',
                                        'maxlength' => '30',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'section-billing billing given-name',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_last_name">Last Name*</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_last_name',
                                        'name' => 'billing_last_name',
                                        'maxlength' => '30',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'section-billing billing family-name',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_company">Company</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_company',
                                        'name' => 'billing_company',
                                        'maxlength' => '30',
                                        'class' => 'software_input_text',
                                        'autocomplete' => 'section-billing billing organization',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_address_1">Address 1*</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_address_1',
                                        'name' => 'billing_address_1',
                                        'maxlength' => '30',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'section-billing billing address-line1',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_address_2">Address 2</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_address_2',
                                        'name' => 'billing_address_2',
                                        'maxlength' => '30',
                                        'class' => 'software_input_text',
                                        'autocomplete' => 'section-billing billing address-line2',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_city">City*</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_city',
                                        'name' => 'billing_city',
                                        'maxlength' => '30',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'section-billing billing address-level2',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_country">Country*</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'select',
                                        'id' => 'billing_country',
                                        'name' => 'billing_country',
                                        'options' => $country_options,
                                        'class' => 'software_select',
                                        'required' => 'true',
                                        'autocomplete' => 'section-billing billing country')) . '
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="billing_state_text_box">
                                        State / Province
                                    </label>

                                    <label for="billing_state_pick_list" style="display: none">
                                        State / Province*
                                    </label>
                                </td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_state_text_box',
                                        'name' => 'billing_state',
                                        'maxlength' => '50',
                                        'class' => 'software_input_text',
                                        'autocomplete' => 'section-billing billing address-level1',
                                        'spellcheck' => 'false')) .

                                    $form->output_field(array(
                                        'type' => 'select',
                                        'id' => 'billing_state_pick_list',
                                        'name' => 'billing_state',
                                        'class' => 'software_select',
                                        'autocomplete' => 'section-billing billing address-level1')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_zip_code">Zip / Postal Code<span id="billing_zip_code_required" style="display: none">*</span></label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'text',
                                        'id' => 'billing_zip_code',
                                        'name' => 'billing_zip_code',
                                        'maxlength' => '50',
                                        'class' => 'software_input_text',
                                        'autocomplete' => 'section-billing billing postal-code',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_phone_number">Phone*</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'tel',
                                        'id' => 'billing_phone_number',
                                        'name' => 'billing_phone_number',
                                        'maxlength' => '20',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'section-billing billing tel',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="billing_email_address">Email*</label></td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'email',
                                        'id' => 'billing_email_address',
                                        'name' => 'billing_email_address',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'section-billing billing email',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            ' . $output_opt_in . '
                            ' . $output_po_number . '
                            ' . $output_referral_source . '
                        </table>
                        ' . $output_update_contact . '
                        ' . $output_tax_exempt . '
                    </div>
                    ' . $output_custom_billing_form . '
                    ' . $output_applied_gift_cards . '
                    ' . $output_payment_information . '
                    ' . $output_offline_payment_allowed . '
                    ' . $output_terms . '
                    <div class="mobile_margin_top" style="text-align: right">
                        <input type="submit" name="submit_update" value="' . $output_update_button_label . '" class="software_input_submit_secondary update_button" style="margin-bottom: .5em" formnovalidate>
                        ' .  $output_purchase_now_button . 
                    '</div>
                    ' . $system . '
                    <input type="hidden" name="total" value="' . h($grand_total) . '">
                </form>
                <div class="cart_link" style="font-size: 90%">
                    This ' . h($shopping_cart_label) . ' has been saved.  To retrieve this ' . h($shopping_cart_label) . ' at a later time, please use this link:<br />
                    <a href="' . URL_SCHEME . h($_SERVER['HTTP_HOST']) . OUTPUT_PATH . h(get_page_name($page_id)) . '?r=' . $reference_code . '">' . URL_SCHEME . h($_SERVER['HTTP_HOST']) . OUTPUT_PATH . h(get_page_name($page_id)) . '?<wbr />r=' . $reference_code . '</a>
                </div>';
        
        // else the number of ship tos is 0, so there are no products in the cart, so output notice
        } else {
            $output_express_order = '<p style="font-weight: bold">No items have been added.</p>';
        }
        
        $output =
            $form->output_errors() . '
            ' . $form->output_notices() . '
            ' . $output_special_offers . '
            ' . $output_quick_add . '
            ' . $output_express_order . '
            ' . get_update_currency_form();
        
        $form->remove();

        return
            '<div class="software_express_order">
                '  . $output . '
            </div>';

    // Otherwise this is a custom layout.
    } else {

        // Prepare attributes that are used for all forms (e.g. pending, quick add, cart).
        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/express_order.php" ' .
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

        $order_offline_payment_allowed = $offline_payment_allowed;

        $edit = $editable;
        $taxable_items = false;
        $shippable_items = false;
        $nonrecurring_items = false;
        $recurring_items = false;
        $number_of_payments_message = '';
        $number_of_payments_required = false;
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
        $gift_card_discount = 0;
        $gift_card_discount_info = '';
        $show_surcharge = false;
        $surcharge = 0;
        $surcharge_info = '';
        $total_with_surcharge = 0;
        $total_with_surcharge_info = '';
        $base_currency_total_with_surcharge_info = '';
        $surcharge_message = false;
        $surcharge_percentage = 0;
        $total = 0;
        $total_info = '';
        $base_currency_total_info = '';
        $base_currency_name = '';
        $total_disclaimer = false;
        $payment_periods = array();
        $applied_offers = array();
        $show_special_offer_code = false;
        $nonrecurring_transaction = false;
        $recurring_transaction = false;
        $payment = false;
        $gift_card_code = false;
        $number_of_payment_methods = 0;
        $credit_debit_card = false;
        $card_verification_number_url = '';
        $paypal_express_checkout = false;
        $paypal_express_checkout_image_url = '';
        $offline_payment = false;
        $offline_payment_allowed = false;
        $terms_url = '';
        $purchase_now_button = false;
        $retrieve_order_url = '';
        $installment = false;
        $installment_table = '';
        $threedsecure = false;
        $threedsecure_required = '';

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
            
            // create array for storing inventory quantity for products,
            // so we can keep track of remaining inventory quantity as we loop through order items,
            // so that we can determine if out of stock message should be shown for an order item
            $inventory_quantities = array();

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

                    if ($recipient['complete']) {
                        
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
                        if (!$form->field_in_session('submit_update') and !$form->field_in_session('submit_purchase_now')) {

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

                    // if mode is edit, add edit button for images
                    if ($edit) {
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
                            if (!$form->field_in_session('submit_update') and !$form->field_in_session('submit_purchase_now')) {
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
                            if (!$form->field_in_session('submit_update') and !$form->field_in_session('submit_purchase_now')) {

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

                    $item['remove_url'] = PATH . SOFTWARE_DIRECTORY . '/remove_item_from_cart.php?order_item_id=' . $item['id'] . '&screen=express_order&send_to=' . urlencode(REQUEST_URL) . '&token=' . $_SESSION['software']['token'];

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

                        // If the recurring product's price is greater than 0,
                        // then a recurring transaction is required.
                        if ($item['price'] > 0) {
                            $recurring_transaction = true;
                        }

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
                        $payment_periods[$item['payment_period']]['tax'] += $item['tax'] * $item['quantity'];

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

            // If gift cards are enabled then get applied gift cards
            // and prepare to output gift card discount.
            if (ECOMMERCE_GIFT_CARD) {

                // get applied gift cards in order to output them
                $query =
                    "SELECT
                        id,
                        code,
                        (old_balance / 100) AS old_balance,
                        givex
                    FROM applied_gift_cards
                    WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'
                    ORDER BY id ASC";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $total_gift_card_balance = 0;

                // loop through applied gift cards in order to add them to array
                while ($row = mysqli_fetch_assoc($result)) {
                    $applied_gift_cards[] = $row;
                    
                    $total_gift_card_balance += $row['old_balance'];
                }
                
                // If the total is greater than 0 and there is at least 1 applied
                // gift card, then prepare gift card info.
                if (($total > 0) and $applied_gift_cards) {
                    
                    // if the total gift card balance is less than the grand total, then set the gift card discount to the total gift card balance
                    if ($total_gift_card_balance < $total) {
                        $gift_card_discount = $total_gift_card_balance;
                        
                    // else the total gift card balance is greater than or equal to the grand total, so set the gift card discount to the grand total
                    } else {
                        $gift_card_discount = $total;
                    }
                    
                    // update the grand total
                    $total -= $gift_card_discount;

                    $gift_card_discount_info = prepare_price_for_output($gift_card_discount * 100, false, $discounted_price = '', 'html');

                    // set the amount that needs to be redeemed from all gift cards
                    $required_redemption_amount = $gift_card_discount;
                    
                    // initialize a variable for tracking the amount that will be redeemed so far
                    $current_redemption_amount = 0;
                    
                    // Loop through the applied gift cards to prepare info for each one.
                    foreach ($applied_gift_cards as $key => $gift_card) {

                        $remaining_redemption_amount = $required_redemption_amount - $current_redemption_amount;
                        
                        // if the balance of this applied gift card is less than or equal to the remaining redemption amount, then redeem the full balance of the gift card
                        if ($gift_card['old_balance'] <= $remaining_redemption_amount) {
                            $gift_card['amount'] = $gift_card['old_balance'];
                            
                        // else the balance of this applied gift card is greater than the remaining redemption amount, so just redeem the remaining redemption amount
                        } else {
                            $gift_card['amount'] = $remaining_redemption_amount;
                        }

                        $gift_card['amount_info'] = prepare_price_for_output($gift_card['amount'] * 100, false, $discounted_price = '', 'html');
                        
                        $gift_card['remaining_balance'] = $gift_card['old_balance'] - $gift_card['amount'];

                        $gift_card['remaining_balance_info'] = prepare_price_for_output($gift_card['remaining_balance'] * 100, false, $discounted_price = '', 'html');

                        if ($gift_card['givex'] == 0) {
                            $gift_card['protected_code'] = protect_gift_card_code($gift_card['code']);
                        } else {
                            $gift_card['protected_code'] = protect_givex_gift_card_code($gift_card['code']);
                        }

                        $gift_card['remove_url'] = PATH . SOFTWARE_DIRECTORY . '/remove_gift_card_from_order.php?applied_gift_card_id=' . $gift_card['id'] . '&send_to=' . urlencode(REQUEST_URL) . '&token=' . $_SESSION['software']['token'];
                        
                        $current_redemption_amount += $gift_card['amount'];

                        $applied_gift_cards[$key] = $gift_card;

                    }
                    
                }

            }

            // If there is a discount, tax, shipping, or gift card discount,
            // then show subtotal row.
            if ($discount_info or $tax_info or $shipping_info or $gift_card_discount_info) {
                $show_subtotal = true;
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

            // If an update button label was not entered for the page,
            // then set default label.
            if ($update_button_label == '') {
                $update_button_label = 'Update ' . $shopping_cart_label;
            }

            // If a purchase now button label was not entered for the page,
            // then set default label.
            if ($purchase_now_button_label == '') {
                $purchase_now_button_label = 'Purchase Now';
            }

            // If user is logged in and not ghosting, get contact for user.
            if (USER_LOGGED_IN and !$ghost) {
                $contact = db_item(
                    "SELECT
                        id,
                        salutation,
                        first_name,
                        last_name,
                        company,
                        business_address_1,
                        business_address_2,
                        business_city,
                        business_state,
                        business_zip_code,
                        business_country,
                        business_phone,
                        business_fax,
                        email_address,
                        lead_source,
                        opt_in
                    FROM contacts
                    WHERE id = '" . USER_CONTACT_ID . "'");
            }

            // If the billing fields were not just submitted, then prefill them.
            if (!$form->field_in_session('billing_first_name')) {
                $order = db_item(
                    "SELECT
                        billing_salutation,
                        billing_first_name,
                        billing_last_name,
                        billing_company,
                        billing_address_1,
                        billing_address_2,
                        billing_city,
                        billing_state,
                        billing_zip_code,
                        billing_country,
                        billing_phone_number,
                        billing_fax_number,
                        billing_email_address,
                        custom_field_1,
                        custom_field_2,
                        opt_in,
                        po_number,
                        tax_exempt,
                        referral_source_code
                    FROM orders
                    WHERE id = '" . e($order_id) . "'");

                if ($order['billing_salutation'] != '') {
                    $form->set('billing_salutation', $order['billing_salutation']);
                } else if ($contact['salutation'] != '') {
                    $form->set('billing_salutation', $contact['salutation']);
                }

                if ($order['billing_first_name'] != '') {
                    $form->set('billing_first_name', $order['billing_first_name']);
                } else if ($contact['first_name'] != '') {
                    $form->set('billing_first_name', $contact['first_name']);
                }

                if ($order['billing_last_name'] != '') {
                    $form->set('billing_last_name', $order['billing_last_name']);
                } else if ($contact['last_name'] != '') {
                    $form->set('billing_last_name', $contact['last_name']);
                }

                if ($order['billing_company'] != '') {
                    $form->set('billing_company', $order['billing_company']);
                } else if ($contact['company'] != '') {
                    $form->set('billing_company', $contact['company']);
                }

                if ($order['billing_address_1'] != '') {
                    $form->set('billing_address_1', $order['billing_address_1']);
                } else if ($contact['business_address_1'] != '') {
                    $form->set('billing_address_1', $contact['business_address_1']);
                }

                if ($order['billing_address_2'] != '') {
                    $form->set('billing_address_2', $order['billing_address_2']);
                } else if ($contact['business_address_2'] != '') {
                    $form->set('billing_address_2', $contact['business_address_2']);
                }

                if ($order['billing_city'] != '') {
                    $form->set('billing_city', $order['billing_city']);
                } else if ($contact['business_city'] != '') {
                    $form->set('billing_city', $contact['business_city']);
                }

                if ($order['billing_state'] != '') {
                    $form->set('billing_state', $order['billing_state']);
                } else if ($contact['business_state'] != '') {
                    $form->set('billing_state', $contact['business_state']);
                }

                if ($order['billing_zip_code'] != '') {
                    $form->set('billing_zip_code', $order['billing_zip_code']);
                } else if ($contact['business_zip_code'] != '') {
                    $form->set('billing_zip_code', $contact['business_zip_code']);
                }

                if ($order['billing_country'] != '') {
                    $form->set('billing_country', $order['billing_country']);
                } else if ($contact['business_country'] != '') {
                    $form->set('billing_country', $contact['business_country']);

                // Otherwise country is blank, so set default country.
                } else {
                    $query = "SELECT code FROM countries WHERE default_selected = 1";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    // if a default country was found, set default country
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $form->set('billing_country', $row['code']);
                    }
                }

                if ($order['billing_phone_number'] != '') {
                    $form->set('billing_phone_number', $order['billing_phone_number']);
                } else if ($contact['business_phone'] != '') {
                    $form->set('billing_phone_number', $contact['business_phone']);
                }

                if ($order['billing_fax_number'] != '') {
                    $form->set('billing_fax_number', $order['billing_fax_number']);
                } else if ($contact['business_fax'] != '') {
                    $form->set('billing_fax_number', $contact['business_fax']);
                }

                if ($order['billing_email_address'] != '') {
                    $form->set('billing_email_address', $order['billing_email_address']);
                } else if ($contact['email_address'] != '') {
                    $form->set('billing_email_address', $contact['email_address']);
                }

                if ($order['custom_field_1'] != '') {
                    $form->set('custom_field_1', $order['custom_field_1']);
                }

                if ($order['custom_field_2'] != '') {
                    $form->set('custom_field_2', $order['custom_field_2']);
                }

                if ($order['po_number'] != '') {
                    $form->set('po_number', $order['po_number']);
                }

                if ($order['referral_source_code'] != '') {
                    $form->set('referral_source', $order['referral_source_code']);
                } else if ($contact['lead_source'] != '') {
                    $form->set('referral_source', $contact['lead_source']);
                }

                if ($order['opt_in'] == 1) {
                    $form->set('opt_in', '1');
                } else {
                    $form->set('opt_in', '');
                }

                // If the visitor is logged in and the visitor has selected
                // that he/she does not want his/her contact info updated with the billing info
                // previously in this session, then uncheck the update contact check box.
                if (USER_LOGGED_IN and !$ghost and ($_SESSION['software']['update_contact'] === false)) {
                    $form->set('update_contact', '');
                } else {
                    $form->set('update_contact', '1');
                }

                if ($order['tax_exempt'] == 1) {
                    $form->set('tax_exempt', '1');
                } else {
                    $form->set('tax_exempt', '');
                }
            }

            settype($custom_field_1_required, 'bool');
            
            // If there is a custom field 1 label, then prepare to output custom field 1.
            if ($custom_field_1_label != '') {
                $custom_field_1 = true;

                $form->set('custom_field_1', 'maxlength', 255);
                $form->set('custom_field_1', 'required', $custom_field_1_required);

            } else {
                $custom_field_1 = false;
            }

            settype($custom_field_2_required, 'bool');

            // If there is a custom field 2 label, then prepare to output custom field 2.
            if ($custom_field_2_label != '') {
                $custom_field_2 = true;

                $form->set('custom_field_2', 'maxlength', 255);
                $form->set('custom_field_2', 'required', $custom_field_2_required);

            } else {
                $custom_field_2 = false;
            }

            $form->set('billing_salutation', 'options', get_salutation_options());

            $form->set('billing_first_name', 'maxlength', 50);
            $form->set('billing_first_name', 'required', true);

            $form->set('billing_last_name', 'maxlength', 50);
            $form->set('billing_last_name', 'required', true);

            $form->set('billing_company', 'maxlength', 50);
            
            $form->set('billing_address_1', 'maxlength', 50);
            $form->set('billing_address_1', 'required', true);

            $form->set('billing_address_2', 'maxlength', 50);

            $form->set('billing_city', 'maxlength', 50);
            $form->set('billing_city', 'required', true);

            $form->set('billing_state', 'maxlength', 50);

            $form->set('billing_zip_code', 'maxlength', 50);

            $form->set('billing_country', 'required', true);
            $form->set('billing_country', 'options', $country_options);

            $form->set('billing_phone_number', 'maxlength', 50);
            $form->set('billing_phone_number', 'required', true);

            $form->set('billing_fax_number', 'maxlength', 50);

            $form->set('billing_email_address', 'maxlength', 100);
            $form->set('billing_email_address', 'required', true);
            
            // If contact cannot be found or if contact is opted out,
            // we are going to display opt-in field.
            if (!$contact['id'] || !$contact['opt_in']) {
                $opt_in = true;
                $opt_in_label = OPT_IN_LABEL;
                $opt_in_displayed_value = 'true';

            } else {
                $opt_in = false;
                $opt_in_label = '';
                $opt_in_displayed_value = 'false';
            }

            $system .= '<input type="hidden" name="opt_in_displayed" value="' . $opt_in_displayed_value . '">';

            settype($po_number, 'bool');

            if ($po_number) {
                $form->set('po_number', 'maxlength', 50);
            }

            $referral_sources = db_items(
                "SELECT name, code
                FROM referral_sources
                ORDER BY sort_order, name");

            // If there is at least one referral source, prepare referral source pick list.
            if ($referral_sources) {
                $referral_source = true;

                $referral_source_options[''] = '';

                foreach ($referral_sources as $referral_source) {
                    $referral_source_options[$referral_source['name']] = $referral_source['code'];
                }

                $form->set('referral_source', 'options', $referral_source_options);

            } else {
                $referral_source = false;
            }

            // If this visitor is logged in and not ghosting, then output check box to allow the visitor
            // to decide if he/she wants his/her contact info to be updated with the billing info.
            if (USER_LOGGED_IN and !$ghost) {
                $update_contact = true;
            } else {
                $update_contact = false;
            }
            
            // If tax is on and tax-exempt is allowed, prepare tax-exempt checkbox.
            if (ECOMMERCE_TAX && ECOMMERCE_TAX_EXEMPT) {
                $tax_exempt = true;

                if (ECOMMERCE_TAX_EXEMPT_LABEL != '') {
                    $tax_exempt_label = ECOMMERCE_TAX_EXEMPT_LABEL;
                } else {
                    $tax_exempt_label = 'Tax-Exempt?';
                }

            } else {
                $tax_exempt = false;
                $tax_exempt_label = '';
            }

            // If the total is greater than 0, then a non-recurring transaction is required.
            if ($total > 0) {
                $nonrecurring_transaction = true;
            }

            // if a non-recurring or recurring transaction is required and at least one payment method will be outputted,
            // then the payment information should be outputted, so prepare to output it
            if (
                (
                    ($nonrecurring_transaction == TRUE)
                    || ($recurring_transaction == TRUE)
                )
                &&
                (
                    ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && ((ECOMMERCE_AMERICAN_EXPRESS === true) || (ECOMMERCE_DINERS_CLUB == true) || (ECOMMERCE_DISCOVER_CARD == true) || (ECOMMERCE_MASTERCARD == true) || (ECOMMERCE_VISA == true)))
                    || ((ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == TRUE) && ($recurring_transaction == FALSE))
                    ||
                    (
                        (ECOMMERCE_OFFLINE_PAYMENT == TRUE)
                        &&
                        (
                            ($order_offline_payment_allowed)
                            || ($offline_payment_always_allowed)
                        )
                    )
                    || ((ECOMMERCE_GIFT_CARD == true) && ($nonrecurring_transaction == true))
                )
            ) {

                $payment = true;

                // If gift cards are enabled and if a non-recurring transaction is required,
                // then the customer might want to add a gift card, so output gift card code field.
                if (ECOMMERCE_GIFT_CARD and $nonrecurring_transaction) {
                    $gift_card_code = true;
                    $form->set('gift_card_code', 'maxlength', 50);
                }

                $form->set('payment_method', 'required', true);

                // If the PayPal Express Checkout payment method is on
                // and a recurring transaction is not required,
                // then remember that the payment method should be shown
                if (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT and !$recurring_transaction) {
                    $paypal_express_checkout = true;
                    $number_of_payment_methods++;
                }

                // If offline payment method should be available to this order,
                // then remember that.
                if (
                    ECOMMERCE_OFFLINE_PAYMENT
                    and
                    (
                        $order_offline_payment_allowed
                        or $offline_payment_always_allowed
                    )
                ) {
                    $offline_payment = true;
                    $number_of_payment_methods++;
                }

                // if the credit/debit card payment method is on and there is at least one accepted card, then remember that the payment method should be shown
                if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && ((ECOMMERCE_AMERICAN_EXPRESS === true) || (ECOMMERCE_DINERS_CLUB == true) || (ECOMMERCE_DISCOVER_CARD == true) || (ECOMMERCE_MASTERCARD == true) || (ECOMMERCE_VISA == true))) {

                    $credit_debit_card = true;
                    $number_of_payment_methods++;

                    // If credit/debit card is the only payment method, then select that method by
                    // default.
                    if (!$paypal_express_checkout and !$offline_payment) {
                        $form->set('payment_method', 'Credit/Debit Card');
                    }

                    // Even though we don't use a card type field anymore, prepare card type options
                    // so that the field will not be broken for sites that still have the field
                    // in a custom layout (i.e. backwards compatibility reasons).  The field will
                    // still appear with options and the customer can select it, however it just
                    // won't be used for anything.  A designer should eventually remove field from
                    // custom layout.

                    $card_type_options = array('-Select Card-' => '');
                    
                    if (ECOMMERCE_AMERICAN_EXPRESS) {
                        $card_type_options['American Express'] = 'American Express';
                    }
                    
                    if (ECOMMERCE_DINERS_CLUB) {
                        $card_type_options['Diners Club'] = 'Diners Club';
                    }
                    
                    if (ECOMMERCE_DISCOVER_CARD) {
                        $card_type_options['Discover Card'] = 'Discover Card';
                    }
                    
                    if (ECOMMERCE_MASTERCARD) {
                        $card_type_options['MasterCard'] = 'MasterCard';
                    }
                    
                    if (ECOMMERCE_VISA) {
                        $card_type_options['Visa'] = 'Visa';
                    }

                    $form->set('card_type', 'options', $card_type_options);

                    $expiration_month_options = array(
                        '-Select Month-' => '',
                        '01' => '01',
                        '02' => '02',
                        '03' => '03',
                        '04' => '04',
                        '05' => '05',
                        '06' => '06',
                        '07' => '07',
                        '08' => '08',
                        '09' => '09',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12');

                    $form->set('expiration_month', 'options', $expiration_month_options);

                    // prepare expiration year options (use current year through 10 years from now)
                    $expiration_year_options['-Select Year-'] = '';

                    $first_year = date('Y');
                    $last_year = $first_year + 10;

                    for ($i = $first_year; $i <= $last_year; $i++) {
                        $expiration_year_options[$i] = $i;
                    }

                    $form->set('expiration_year', 'options', $expiration_year_options);

                    $form->set('card_verification_number', 'maxlength', 4);

                    // If there is a page that explains the card verification number field,
                    // prepare link to that page.
                    if ($card_verification_number_page_id) {
                        $card_verification_number_url = PATH . encode_url_path(get_page_name($card_verification_number_page_id));
                    }

                    // If a credit card surcharge is enabled and a non-recurring transaction is required
                    // (i.e. total is greater than 0), then deal with surcharge.
                    if (
                        (ECOMMERCE_SURCHARGE_PERCENTAGE > 0)
                        && ($nonrecurring_transaction)
                    ) {

                        $show_surcharge = true;

                        $surcharge = round(ECOMMERCE_SURCHARGE_PERCENTAGE / 100 * $total, 2);

                        $surcharge_info = prepare_price_for_output($surcharge * 100, false, $discounted_price = '', 'html');

                        $total_with_surcharge = $total + $surcharge;

                        $total_with_surcharge_info = prepare_price_for_output($total_with_surcharge * 100, false, $discounted_price = '', 'html');

                        // If the visitor's currency is different from the base currency,
                        // then show actual base currency amount and disclaimer
                        // because the base currency will be charged.
                        if (VISITOR_CURRENCY_CODE != BASE_CURRENCY_CODE) {
                            $base_currency_total_with_surcharge_info = '<span style="white-space: nowrap">' . prepare_amount($total_with_surcharge) . ' ' . h(BASE_CURRENCY_CODE) . '</span>';
                        }

                        // Remember total with surcharge so if the total changes after the customer
                        // submits the order, we can forward the user back to review the new total.
                        $form->set('total_with_surcharge', $total_with_surcharge);
                        $system .= '<input type="hidden" name="total_with_surcharge">';

                        // If there are multiple payment methods, then output surcharge warning,
                        // because the surcharge might not have been included in the totals
                        // above until the customer selected the credit card payment method.
                        if ($paypal_express_checkout or $offline_payment) {
                            $surcharge_message = true;
                        }

                        $surcharge_percentage = floatval(ECOMMERCE_SURCHARGE_PERCENTAGE);
                    }






                    //This options for only for Credit/debit Cart and to get installment table,
                    // if supported installment, we output a table with all supported cards and banks installment prices.
                    switch (ECOMMERCE_PAYMENT_GATEWAY) {
                        case 'Iyzipay':
                            //test credit cards for check cart for installment outputs.
                            // use for get info and output installment table.
                            $card_numbers = array(
                                'Maximum'=>'4543590000000006',
                                'Advantage'=>'5504720000000003',
                                'Cardfinans'=>'9792030000000000',
                                'Paraf'=>'5528790000000008',
                                'World'=>'5451030000000000',
                                'Bonus'=>'374427000000003',
                                'Axess'=>'4355084355084358',
                            );
                        
						    // if test or live mode for iyzipay gateway.
						    if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
						    	$payment_gateway_host = 'https://sandbox-api.iyzipay.com';
						    }else {
						    	$payment_gateway_host = 'https://api.iyzipay.com';
                            }
                            //add total
                             
                            if($total_with_surcharge > 0){
                                //if there is surcharge output total with surcharge
                                $iyzipay_total = $total_with_surcharge;

                            }else{
                                //else output direct total
                                $iyzipay_total = $total;
                            }

						    require_once('iyzipay-php/IyzipayBootstrap.php');
                            IyzipayBootstrap::init();
                            //to get all cart type installment prices foreach them.
                            foreach($card_numbers as $key => $card_number){
						        $card_binNumber = substr($card_number, 0, 6);
						        // Conversation ID Digits amount
						        $digits = 9;
						        // Random Conversation ID
						        $conversationid = rand(pow(10, $digits-1), pow(10, $digits)-1);
						        //config
						        $options = new \Iyzipay\Options();
						        $options->setApiKey(ECOMMERCE_IYZIPAY_API_KEY);
						        $options->setSecretKey(ECOMMERCE_IYZIPAY_SECRET_KEY);
						        $options->setBaseUrl($payment_gateway_host);
						        $request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
						        $request->setLocale(strtoupper(language_ruler()));//get location from sofware language, where set from software settings
						        $request->setConversationId($conversationid);
						        $request->setBinNumber($card_binNumber);
						        $request->setPrice($iyzipay_total);
						        $installmentInfo = \Iyzipay\Model\InstallmentInfo::retrieve($request, $options);
						        $result = $installmentInfo->getRawResult();
						        $oneinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[0]->installmentPrice;
                                $oneinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[0]->totalPrice;
						        $twoinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[1]->installmentPrice;
						        $twoinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[1]->totalPrice;
						        $threeinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[2]->installmentPrice;
						        $threeinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[2]->totalPrice;
						        $sixinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[3]->installmentPrice;
						        $sixinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[3]->totalPrice;	
						        $nineinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[4]->installmentPrice;
						        $nineinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[4]->totalPrice;
						        $twelveinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[5]->installmentPrice;
                                $twelveinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[5]->totalPrice;

                                if($twoinstallment_price){
                                    $outout_installment_table_two_installment = '<td><span class="installment_per_month">' . BASE_CURRENCY_SYMBOL . $twoinstallment_price . ' / month</span><br/><span class="installment_month_total">Total: ' . BASE_CURRENCY_SYMBOL . $twoinstallment_totalprice . '</span></td>';
                                }else{
                                    $outout_installment_table_two_installment = '<td></td>';
                                }
                                if($threeinstallment_price){
                                    $outout_installment_table_three_installment = '<td><span class="installment_per_month">' . BASE_CURRENCY_SYMBOL . $threeinstallment_price  . ' / month</span><br/><span class="installment_month_total">Total: ' . BASE_CURRENCY_SYMBOL . $threeinstallment_totalprice . '</span></td>';
                                }else{
                                    $outout_installment_table_three_installment = '<td></td>';
                                }
                                if($sixinstallment_price){
                                    $outout_installment_table_six_installment = '<td><span class="installment_per_month">' . BASE_CURRENCY_SYMBOL . $sixinstallment_price . ' / month</span><br/><span class="installment_month_total">Total: ' . BASE_CURRENCY_SYMBOL . $sixinstallment_totalprice . '</span></td>';
                                }else{
                                    $outout_installment_table_six_installment = '<td></td>';
                                }
                                if($nineinstallment_price){
                                    $outout_installment_table_nine_installment = '<td><span class="installment_per_month">' . BASE_CURRENCY_SYMBOL . $nineinstallment_price . ' / month</span><br/><span class="installment_month_total">Total: ' . BASE_CURRENCY_SYMBOL . $nineinstallment_totalprice . '</span></td>';
                                }else{
                                    $outout_installment_table_nine_installment = '<td></td>';
                                }
                                if($twelveinstallment_price){
                                    $outout_installment_table_twelve_installment = '<td><span class="installment_per_month">' . BASE_CURRENCY_SYMBOL . $twelveinstallment_price . ' / month</span><br/><span class="installment_month_total">Total: ' . BASE_CURRENCY_SYMBOL . $twelveinstallment_totalprice . '</span></td>';
                                }else{
                                    $outout_installment_table_twelve_installment = '<td></td>';
                                }

                                $cardFamilyName = json_decode($result)->installmentDetails[0]->cardFamilyName;

                                //Check if there is at least 2x installment option activated from site settings.
                                if( ($oneinstallment_price)&&(ECOMMERCE_IYZIPAY_INSTALLMENT >= 2) ){
                                    //print_r($result);
                                    $installment = true;
                                    
                                    $installment_table_content .='<tr>';
                                    $installment_table_content .= '<td  scope="row">' . $cardFamilyName . '</td>';
                                    $installment_table_content .= $outout_installment_table_two_installment;
                                    //Check if there is at least 3x installment option activated from site settings.
                                    if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 3){
                                        $installment_table_content .= $outout_installment_table_three_installment;
                                    }
                                    //Check if there is at least 6x installment option activated from site settings.
                                    if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 6){
                                        $installment_table_content .= $outout_installment_table_six_installment;
                                    }
                                    //Check if there is at least 9x installment option activated from site settings.
                                    if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 9){
                                        $installment_table_content .= $outout_installment_table_nine_installment;
                                    }
                                    //Check if there is at least 12x installment option activated from site settings.
                                    if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 12){
                                        $installment_table_content .= $outout_installment_table_twelve_installment;
                                    }
                                    $installment_table_content .='</tr>';
                                }
                            }
                            //for table headers.
                            if( ($oneinstallment_price)&&(ECOMMERCE_IYZIPAY_INSTALLMENT >= 2) ){
                                //print_r($result);
                                $installment_table_header .= '<tr>';
                                $installment_table_header .= '<th>Cart Type</th>';
                                $installment_table_header .= '<th>2 Installment</th>';
                                //Check if there is at least 3x installment option activated from site settings.
                                if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 3){
                                    $installment_table_header .= '<th>3 Installment</th>';
                                }
                                //Check if there is at least 6x installment option activated from site settings.
                                if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 6){
                                    $installment_table_header .= '<th>6 Installment</th>';
                                }
                                //Check if there is at least 9x installment option activated from site settings.
                                if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 9){
                                    $installment_table_header .= '<th>9 Installment</th>';
                                }
                                //Check if there is at least 12x installment option activated from site settings.
                                if(ECOMMERCE_IYZIPAY_INSTALLMENT >= 12){
                                    $installment_table_header .= '<th>12 Installment</th>';
                                }
                                $installment_table_header .= '</tr>';
                            }	       

                            if( ECOMMERCE_IYZIPAY_INSTALLMENT >= 2 ){
                                if(language_ruler() === 'tr'){
                                    $installment_table_content = str_replace('Total:', 'Top.:', $installment_table_content);
                                    $installment_table_header = str_replace('Installment', "Taksit", $installment_table_header);
                                    $installment_table_header = str_replace('Cart Type', "Kart Türü", $installment_table_header);
                                    $installment_table_content = str_replace('/ month', " / ay", $installment_table_content);
                                }
                                //we output installment table 
                                $installment_table .= '<style>td[scope=row] {font-weight: 700;}span.installment_per_month {font-size: initial;}</style><div class="table-responsive"><table id="software_installment_table"  class="table responsive-table  table-striped" style="width:100%;" >';
                                $installment_table .= '<thead class="thead-dark">' . $installment_table_header . '</thead>';
                                $installment_table .= '<tbody>' . $installment_table_content .'</tbody></table></div>';
                            }

                            //if this is iyzipay payment method than output 3Dsecure.
                            $threedsecure = true;
                            //if this is iyzico payment than prepare to output 3Dsecure payment checkbox to disabled and checked.
                            if(ECOMMERCE_IYZIPAY_THREEDS){
                                $threedsecure_required = ' checked disabled style="cursor:help;" title="3DSecure is required" ';
                            }
                                                        
                        break;
                    }





                    // Use jQuery.payment library to enhance payment card fields
                    $system .= '<script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery.payment.min.js"></script>';
                }

                // If PayPal Express Checkout payment method should be shown, then prepare.
                if ($paypal_express_checkout) {

                    // If PayPal Express Checkout is the only payment method,
                    // then select it by default.
                    if (
                        !$credit_debit_card
                        and !$offline_payment
                    ) {
                        $form->set('payment_method', 'PayPal Express Checkout');
                    }

                    $paypal_express_checkout_image_url = PATH . SOFTWARE_DIRECTORY . '/images/paypal.png';
                }

                // If offline payment method should be shown, then prepare.
                if ($offline_payment) {

                    // If offline payment is the only payment method,
                    // then select it by default.
                    if (
                        !$credit_debit_card
                        and !$paypal_express_checkout
                    ) {
                        $form->set('payment_method', 'Offline Payment');
                    }

                    // if the offline payment label is blank, then set it to the default label
                    if ($offline_payment_label == '') {
                        $offline_payment_label = 'Offline Payment';
                    }

                    // set the offline payment label in the session so that it can be used on the order receipt screen
                    $_SESSION['ecommerce']['offline_payment_label'] = $offline_payment_label;
                }

                // If there is only one payment method, then add a hidden field for it so that
                // the designer does not have to include a radio button in the custom layout.
                if ($number_of_payment_methods == 1) {
                    $system .= '<input type="hidden" name="payment_method">';
                }
                // If payment gateway is Iyzipay and installment selection is at least 2 installment and above than output it bause we need it for installment options
                // the designer does not have to include a radio button in the custom layout.
                if ( (ECOMMERCE_PAYMENT_GATEWAY == 'Iyzipay') && (ECOMMERCE_IYZIPAY_INSTALLMENT >= 2) ) {
                    $system .= '<input type="hidden" name="payment_gateway_installment_option" value="true" >';
                }

                $system .= '<script>software.init_payment_method()</script>';
            }

            if (
                ECOMMERCE_OFFLINE_PAYMENT
                and !$offline_payment_always_allowed
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

            // If a terms page was selected for this express order page,
            // prepare to output terms and conditions checkbox.
            if ($terms_page_name) {
                $terms_url = PATH . encode_url_path($terms_page_name);
                $form->set('terms', 'required', true);
            }

            // if the total is greater than 0 and there is not an active payment method, then prepare error and do not show purchase now button
            if (
                ($total > 0)
                &&
                (
                    (ECOMMERCE_GIFT_CARD == FALSE)
                    && (ECOMMERCE_CREDIT_DEBIT_CARD == FALSE)
                    &&
                    (
                        (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == FALSE)
                        ||
                        (
                            (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == TRUE)
                            && ($recurring_transaction == TRUE)
                        )
                    )
                    &&
                    (
                        (ECOMMERCE_OFFLINE_PAYMENT == FALSE) 
                        ||
                        (
                            ($order_offline_payment_allowed == '0')
                            && ($offline_payment_always_allowed == 0)
                        )
                    )
                )
            ) {
                $form->mark_error('payment_method', 'Sorry, this order may not be submitted, because there is not an available payment method.  Please contact the administrator of this website.');
                
            // else the total is 0 or there is an active payment method, so prepare to output purchase now button
            } else {
                $purchase_now_button = true;
            }

            // If there is at least one rich-text editor field, then output JS for them.
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

            $retrieve_order_url = URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id) . '?r=' . $reference_code;

            // Remember total so if the total changes after the customer submits the order, we can
            // forward the user back to review the new total.
            $form->set('total', $total);
            $system .= '<input type="hidden" name="total">';

            // If there are shippable items, then prepare data that will allow us to dynamically
            // update shipping and total when a method is selected.
            if ($shippable_items) {
                $system .=
                    '<script>
                        software.shipping = ' . $shipping . ';
                        software.gift_card_discount = ' . $gift_card_discount . ';
                        software.surcharge = ' . $surcharge . ';
                        software.total_without_shipping = ' . ($total - $shipping) . ';
                    </script>';
            }
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
            'edit' => $edit,
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
            'custom_shipping_form' => $custom_shipping_form,
            'shipping_field' => $custom_shipping_form_fields,
            'edit_custom_shipping_form_start' => $edit_custom_shipping_form_start,
            'edit_custom_shipping_form_end' => $edit_custom_shipping_form_end,
            'arrival_dates' => $arrival_dates,
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
            'show_surcharge' => $show_surcharge,
            'surcharge' => $surcharge,
            'surcharge_info' => $surcharge_info,
            'total_with_surcharge' => $total_with_surcharge,
            'total_with_surcharge_info' => $total_with_surcharge_info,
            'base_currency_total_with_surcharge_info' => $base_currency_total_with_surcharge_info,
            'surcharge_message' => $surcharge_message,
            'surcharge_percentage' => $surcharge_percentage,
            'total' => $total,
            'total_info' => $total_info,
            'base_currency_total_info' => $base_currency_total_info,
            'base_currency_name' => $base_currency_name,
            'total_disclaimer' => $total_disclaimer,
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
            'update_button_label' => $update_button_label,
            'purchase_now_button_label' => $purchase_now_button_label,
            'billing_same_as_shipping' => $billing_same_as_shipping,
            'custom_field_1' => $custom_field_1,
            'custom_field_1_label' => $custom_field_1_label,
            'custom_field_1_required' => $custom_field_1_required,
            'custom_field_2' => $custom_field_2,
            'custom_field_2_label' => $custom_field_2_label,
            'custom_field_2_required' => $custom_field_2_required,
            'opt_in' => $opt_in,
            'opt_in_label' => $opt_in_label,
            'po_number' => $po_number,
            'referral_source' => $referral_source,
            'update_contact' => $update_contact,
            'tax_exempt' => $tax_exempt,
            'tax_exempt_label' => $tax_exempt_label,
            'custom_billing_form' => $custom_billing_form,
            'custom_billing_form_title' => $custom_billing_form_title,
            'field' => $custom_billing_form_fields,
            'edit_custom_billing_form_start' => $edit_custom_billing_form_start,
            'edit_custom_billing_form_end' => $edit_custom_billing_form_end,
            'nonrecurring_transaction' => $nonrecurring_transaction,
            'recurring_transaction' => $recurring_transaction,
            'payment' => $payment,
            'gift_card_code' => $gift_card_code,
            'number_of_payment_methods' => $number_of_payment_methods,
            'credit_debit_card' => $credit_debit_card,
            'card_verification_number_url' => $card_verification_number_url,
            'paypal_express_checkout' => $paypal_express_checkout,
            'paypal_express_checkout_image_url' => $paypal_express_checkout_image_url,
            'offline_payment' => $offline_payment,
            'offline_payment_label' => $offline_payment_label,
            'offline_payment_allowed' => $offline_payment_allowed,
            'terms_url' => $terms_url,
            'purchase_now_button' => $purchase_now_button,
            'system' => $system,
            'retrieve_order_url' => $retrieve_order_url,
            'currency_symbol' => VISITOR_CURRENCY_SYMBOL,
            'currency_code' => VISITOR_CURRENCY_CODE_FOR_OUTPUT,
            'currency' => $currency,
            'currency_attributes' => $currency_attributes,
            'currencies' => $currencies,
            'currency_system' => $currency_system,
            'installment' => $installment,
            'installment_table' => $installment_table,
            'threedsecure' => $threedsecure,
            'threedsecure_required' => $threedsecure_required));
            

        $content = $form->prepare($content);

        $form->remove();

        return
            '<div class="software_express_order">
                '  . $content . '
            </div>';

    }

}