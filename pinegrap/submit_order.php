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


function submit_order($type) {
    


    switch($type) {
        case 'order preview':
            $type_value = 'order_preview';
            break;
        
        case 'express order':
            $type_value = 'express_order';
            break;
    }
    
   
    $page_id = $_REQUEST['page_id'];
    

    $liveform = new liveform($type_value);

    $ghost = $_SESSION['software']['ghost'];
    
    // if the mode is paypal_express_checkout_return, then set payment method value, because it might no longer exist in the session
    // for example, if the customer was taken to PayPal, clicked the back button to go back to the website, and then clicked the forward button to go to PayPal again
    // if we don't do this, then the order will be allowed to be submitted without a payment method
    if ($_GET['mode'] == 'paypal_express_checkout_return') {
        $liveform->assign_field_value('payment_method', 'PayPal Express Checkout');
    }
    if($_GET['mode'] == 'iyzipay_threedsecure_return'){
        $liveform->assign_field_value('payment_method', 'Credit/Debit Card');
    }

    check_banned_ip_addresses('complete order');
    
    // If the customer came from the order preview screen or PayPal, then verify that shipping &
    // billing info is complete.  We don't have to do this if the customer just submitted the
    // express order page because it already does that by validating all the fields on the screen.
    // Normally, the billing and shipping info should be complete at this point, however there are
    // edge cases where a customer might do something unusual, which requires that we check.  For
    // example, a customer might open an additional browser window and submit blank shipping
    // or billing info and then come back to the original window and try to complete the order.

    if ( (($type == 'order preview') and ($_GET['mode'] != 'iyzipay_threedsecure_return'))  and ($_GET['mode'] == 'paypal_express_checkout_return')) {

        // Check for an incomplete recipient.
        $incomplete_recipient_id = db("
            SELECT id FROM ship_tos
            WHERE
                (order_id = '" . e($_SESSION['ecommerce']['order_id']) . "')
                AND (complete = '0')
            ORDER BY id LIMIT 1");

        // If there is an incomplete recipient, show error.
        if ($incomplete_recipient_id) {

            $message = 'Sorry, your order could not be completed because the shipping info is incomplete. Please update the shipping info below.';

            log_activity($message);

            // If the customer came from order preview, then send customer to shipping address page.
            if ($type == 'order preview') {

                $liveform_shipping_address_and_arrival = new liveform('shipping_address_and_arrival');
                $liveform_shipping_address_and_arrival->mark_error('incomplete_ship_tos_error',
                    $message);

                go(PATH . encode_url_path(get_page_name($_SESSION['ecommerce']['shipping_address_and_arrival_page_id'])) .
                    '?ship_to_id=' . $incomplete_recipient_id);

            // Otherwise the customer came from PayPal, by way of express order, so send customer
            // to express order page.
            } else {

                $liveform->mark_error('incomplete_ship_tos_error', $message);
                go(PATH . encode_url_path(get_page_name($page_id)));
            }
        }

        // Check if billing info is complete.
        $billing_complete = db("
            SELECT billing_complete FROM orders
            WHERE id = '" . e($_SESSION['ecommerce']['order_id']) . "'");
        
        // If billing info is not complete, show error.
        if (!$billing_complete) {

            $message = 'Sorry, your order could not be completed because the billing info is incomplete. Please update the billing info below.';

            log_activity($message);

            // If the customer came from order preview, then send customer to billing info page.
            if ($type == 'order preview') {

                $liveform_billing_information = new liveform('billing_information');
                $liveform_billing_information->mark_error('incomplete_billing_error',
                    $message);

                go(PATH . encode_url_path(get_page_name($_SESSION['ecommerce']['billing_information_page_id'])));

            // Otherwise the customer came from PayPal, by way of express order, so send customer
            // to express order page.
            } else {

                $liveform->mark_error('incomplete_billing_error', $message);
                go(PATH . encode_url_path(get_page_name($page_id)));
            }
        }
    }

    $user_id = '';
    $contact_id = '';

 
    if($_GET['contact_id'] and $_GET['mode'] == 'iyzipay_threedsecure_return'){
        $contact_id = $_GET['contact_id'];
    }else{
        // If the user is logged in and not ghosting, then use this user and contact.
        if (USER_LOGGED_IN and !$ghost) {
            $user_id = USER_ID;
            $contact_id = USER_CONTACT_ID;
        }
    }

    // If a contact has not been found, then look for one in order.
    if (!$contact_id) {

        // get contact id for this order
        $query = "SELECT contact_id FROM orders WHERE id = '" . e($_SESSION['ecommerce']['order_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $contact_id = $row['contact_id'];
        
        // check to see if this contact is connected to a user
        $query = "SELECT user_id FROM user WHERE user_contact = '$contact_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the contact is connected to a user, then the contact should not be used
        if (mysqli_num_rows($result) > 0) {
            $contact_id = 0;
        }
    }

    // If a contact id has been found, then check to make sure contact still exists.
    if ($contact_id) {
        $contact_id = db("SELECT id FROM contacts WHERE id = '" . e($contact_id) . "'");
    }

    // If we could not find a contact, then create one.
    if (!$contact_id) {
        $query =
            "INSERT INTO contacts (
                user,
                timestamp)
            VALUES (
                '$user_id',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $contact_id = mysqli_insert_id(db::$con);

        // if user is logged in, connect new contact record to user record
        if ($user_id) {
            $query = "UPDATE user SET user_contact = '$contact_id' WHERE user_id = '$user_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // check if there is already a contact group record for Orders
    $query = "SELECT id FROM contact_groups WHERE name = 'Orders'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if there is a contact group record for Orders contact group, get id
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $contact_group_id = $row['id'];
        
    // else there is not a contact group record for the Orders contact group, so create contact group
    } else {
        $query =
            "INSERT INTO contact_groups (
                name,
                created_timestamp,
                last_modified_timestamp)
            VALUES (
                'Orders',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $contact_group_id = mysqli_insert_id(db::$con);
    }
    
    // check if contact is already in Orders contact group
    $query =
        "SELECT contact_id
        FROM contacts_contact_groups_xref
        WHERE
            (contact_id = '$contact_id')
            AND (contact_group_id = '$contact_group_id')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if contact is not already in contact group, then add contact to Orders contact group
    if (mysqli_num_rows($result) == 0) {
        $query =
            "INSERT INTO contacts_contact_groups_xref (
                contact_id,
                contact_group_id)
            VALUES (
                '$contact_id',
                '$contact_group_id')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // add user id and contact id to order record in database
    $query = "UPDATE orders
             SET
                user_id = '$user_id',
                contact_id = '$contact_id'
             WHERE id = " . $_SESSION['ecommerce']['order_id'];
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // initialize a variable for storing the total balance of all gift cards
    $gift_card_balance = 0;
    
    // if gift cards are enabled, then check if applied gift cards are still valid and refresh balances
    if (ECOMMERCE_GIFT_CARD == TRUE) {
        // get applied gift cards
        $query =
            "SELECT
                id,
                gift_card_id,
                code,
                old_balance,
                givex
            FROM applied_gift_cards
            WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'
            ORDER BY id ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        $applied_gift_cards = array();

        // loop through applied gift cards in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $applied_gift_cards[] = $row;
        }
        
        // if there is at least one applied gift card, then continue to check gift card balances
        if (count($applied_gift_cards) > 0) {
            // If there are only gift card products in the order,
            // then remove all applied gift cards and output error.
            if (
                db_value(
                    "SELECT COUNT(*)
                    FROM order_items
                    LEFT JOIN products ON order_items.product_id = products.id
                    WHERE
                        (order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                        AND products.gift_card = '0'") == 0
            ) {
                db("DELETE FROM applied_gift_cards WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'");
                $liveform->mark_error('gift_card', 'Sorry, we do not allow gift cards to be applied to an order that only contains gift cards.  We have removed the previously applied gift card(s).  Please add a different type of item to your order in order to apply a gift card.');
                go(PATH . encode_url_path(get_page_name($page_id)));
            }

            $gift_card_plural_suffix = '';
            
            // if the number of applied gift cards is greater than 1, then prepare plural suffix
            if (count($applied_gift_cards) > 1) {
                $gift_card_plural_suffix = 's';
            }
            
            // loop through the applied gift cards in order to check if they are valid and refresh balances
            foreach ($applied_gift_cards as $key => $applied_gift_card) {
                // If this is a gift card in this system (i.e. not givex),
                // then check that gift card still exists and balance is good.
                if ($applied_gift_card['givex'] == 0) {
                    // Check for a gift card in this system.
                    $gift_card = db_item(
                        "SELECT
                            id,
                            balance,
                            expiration_date
                        FROM gift_cards
                        WHERE id = '" . $applied_gift_card['gift_card_id']  . "'");

                    // If a gift card no longer exists, then output error.
                    if ($gift_card['id'] == '') {
                        $liveform->mark_error('gift_card_' . $applied_gift_card['id'], 'Sorry, we could not find a gift card for that code (' . h(protect_gift_card_code($applied_gift_card['code'])) . ').  Therefore, the gift card has been removed from your order. You may try using a different gift card.');
                        db("DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'");
                        unset($applied_gift_cards[$key]);

                    // Otherwise, if the card has expired, then output error.
                    } else if (
                        ($gift_card['expiration_date'] != '0000-00-00')
                        && ($gift_card['expiration_date'] < date('Y-m-d'))
                    ) {
                        $liveform->mark_error('gift_card_' . $applied_gift_card['id'], 'Sorry, we cannot accept the gift card (' . h(protect_gift_card_code($applied_gift_card['code'])) . ') because it has expired. Therefore, the gift card has been removed from your order. You may try using a different gift card.');
                        db("DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'");
                        unset($applied_gift_cards[$key]);

                    // Otherwise, if the balance is 0, then output error.
                    } else if ($gift_card['balance'] == 0) {
                        $liveform->mark_error('gift_card_' . $applied_gift_card['id'], 'Sorry, we cannot accept the gift card (' . h(protect_gift_card_code($applied_gift_card['code'])) . ') because there is no remaining balance. Therefore, the gift card has been removed from your order. You may try using a different gift card.');
                        db("DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'");
                        unset($applied_gift_cards[$key]);

                    // Otherwise the card is valid, so update data for card in database and array.
                    } else {
                        $balance = $gift_card['balance'];
                        
                        // update old balance in database
                        $query = "UPDATE applied_gift_cards SET old_balance = '" . escape($balance) . "' WHERE id = '" . $applied_gift_card['id'] . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // update old balance in array
                        $applied_gift_cards[$key]['old_balance'] = $balance;
                        
                        // update total gift card balance
                        $gift_card_balance = $gift_card_balance + $balance;
                    }

                // Otherwise this is a givex gift card, so check if gift card still exists
                // and if balance is good.
                } else {
                    // if cURL does not exist, then add error and send user back to previous screen
                    if (function_exists('curl_init') == FALSE) {
                        $liveform->mark_error('gift_card', 'Sorry, we cannot communicate with the gift card service, because cURL is not installed, so we cannot accept the gift card' . $gift_card_plural_suffix . '. The administrator of this website should install cURL. If you would like to proceed without using the gift card' . $gift_card_plural_suffix . ', then you may remove the gift card' . $gift_card_plural_suffix . ' and continue.');
                        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($page_id)));
                        exit();
                    }

                    // send a balance request to Givex in order to check gift card balance
                    $result = send_givex_request('balance', $applied_gift_card['code']);
                    
                    // if there was an error or the balance is 0 then add error and possibly remove applied gift card
                    if ((isset($result['curl_errno']) == TRUE) || (isset($result['error_message']) == TRUE) || ($result['balance'] == 0)) {
                        // if there was a curl error, then prepare error message
                        if (isset($result['curl_errno']) == TRUE) {
                            $error_message = 'Sorry, we cannot communicate with the gift card service at the moment, so we cannot accept the gift card (' . h(protect_givex_gift_card_code($applied_gift_card['code'])) . '). Please try again later. If the problem continues, then please contact the administrator of this website. If you would like to proceed without using the gift card, then you may remove the card and continue. cURL Error Number: ' . h($result['curl_errno']) . '. cURL Error Message: ' . h($result['curl_error']) . '.';
                            
                        // else if there was a Givex error, then prepare error message and remove gift card
                        } else if (isset($result['error_message']) == TRUE) {
                            $error_message = 'Sorry, we cannot accept the gift card (' . h(protect_givex_gift_card_code($applied_gift_card['code'])) . ') because there was an error from the gift card service: ' . h($result['error_message']) . '. Therefore, the gift card has been removed from your order. You may try using a different gift card.';
                            
                            // remove applied gift card from database
                            $query = "DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                            // remove applied gift card from array
                            unset($applied_gift_cards[$key]);
                            
                        // else if the balance is 0, then prepare error message and remove gift card
                        } else if ($result['balance'] == 0) {
                            $error_message = 'Sorry, we cannot accept the gift card (' . h(protect_givex_gift_card_code($applied_gift_card['code'])) . ') because there is no remaining balance. Therefore, the gift card has been removed from your order. You may try using a different gift card.';
                            
                            // remove applied gift card from database
                            $query = "DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                            // remove applied gift card from array
                            unset($applied_gift_cards[$key]);
                        }
                        
                        // add error
                        $liveform->mark_error('gift_card_' . $applied_gift_card['id'], $error_message);
                        
                    // else the card is valid, so update data for card in database and array
                    } else {
                        $balance = $result['balance'] * 100;
                        
                        // update old balance in database
                        $query = "UPDATE applied_gift_cards SET old_balance = '" . escape($balance) . "' WHERE id = '" . $applied_gift_card['id'] . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // update old balance in array
                        $applied_gift_cards[$key]['old_balance'] = $balance;
                        
                        // update total gift card balance
                        $gift_card_balance = $gift_card_balance + $balance;
                    }
                }
            }
        }
    
    // else gift cards are disabled, so delete any applied gift cards that might exist for this order
    // this is used to delete any gift cards that might have been applied right before gift cards were disabled
    } else {
        $query = "DELETE FROM applied_gift_cards WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // if the mode is not paypal_express_checkout_return, then add field values to session and validate data
    if ($_GET['mode'] != 'paypal_express_checkout_return' and $_GET['mode'] != 'iyzipay_threedsecure_return') {
        $liveform->add_fields_to_session();
        
        // if order was submitted from an express order screen, then validate billing information fields
        if ($type == 'express order') {
            // Get page info.
            $query =
                "SELECT
                    custom_field_1_label,
                    custom_field_1_required,
                    custom_field_2_label,
                    custom_field_2_required,
                    form
                FROM express_order_pages
                WHERE page_id = '" . escape($page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);

            $custom_field_1_label = $row['custom_field_1_label'];
            $custom_field_1_required = $row['custom_field_1_required'];
            $custom_field_2_label = $row['custom_field_2_label'];
            $custom_field_2_required = $row['custom_field_2_required'];
            $form = $row['form'];

            // if there is a custom field 1 label and custom field 1 is required, then validate required field
            if (($custom_field_1_label != '') && ($custom_field_1_required == 1)) {
                $liveform->validate_required_field('custom_field_1', $custom_field_1_label . ' is required.');
            }

            // if there is a custom field 2 label and custom field 2 is required, then validate required field
            if (($custom_field_2_label != '') && ($custom_field_2_required == 1)) {
                $liveform->validate_required_field('custom_field_2', $custom_field_2_label . ' is required.');
            }
            
            $liveform->validate_required_field('billing_first_name', 'First Name is required.');
            $liveform->validate_required_field('billing_last_name', 'Last Name is required.');
            $liveform->validate_required_field('billing_address_1', 'Address 1 is required.');
            $liveform->validate_required_field('billing_city', 'City is required.');
            $liveform->validate_required_field('billing_country', 'Country is required.');

            // If a country has been selected and then determine if state and zip code are required.
            if ($liveform->get('billing_country')) {

                // If there is a state in this system for the selected country, then require state.
                if (db(
                    "SELECT states.id FROM states
                    LEFT JOIN countries ON countries.id = states.country_id
                    WHERE countries.code = '" . e($liveform->get('billing_country')) . "'
                    LIMIT 1")
                ) {
                    $liveform->validate_required_field('billing_state', 'State/Province is required.');
                }

                // If this country requires a zip code, then require it.
                if (
                    db(
                        "SELECT zip_code_required FROM countries
                        WHERE code = '" . e($liveform->get('billing_country')) . "'")
                ) {
                    $liveform->validate_required_field('billing_zip_code', 'Zip/Postal Code is required.');
                }
            }            
            
            $liveform->validate_required_field('billing_phone_number', 'Phone is required.');
            $liveform->validate_required_field('billing_email_address', 'Email is required.');

            // if there is not already an error for the e-mail address field, validate e-mail address
            if ($liveform->check_field_error('billing_email_address') == false) {
                if (validate_email_address($liveform->get_field_value('billing_email_address')) == false) {
                    $liveform->mark_error('billing_email_address', 'Email is invalid.');
                }
            }

            // If a custom billing form is enabled, then submit form.
            if ($form == 1) {

                submit_custom_form(array(
                    'form' => $liveform,
                    'page_id' => $page_id,
                    'page_type' => 'express order',
                    'form_type' => 'billing',
                    'require' => true));
            }
            
            // if there was no opt-in field on previous screen or if the opt-in checkbox was checked
            if (($liveform->get_field_value('opt_in_displayed') == 'false') || ($liveform->get_field_value('opt_in') == '1')) {
                $opt_in = 1;
                
            // else this order is not opted in
            } else {
                $opt_in = 0;
            }
            
            // if tax-exempt was checked
            if ($liveform->get_field_value('tax_exempt') == '1') {
                $tax_exempt = 1;
                
            // else tax-exempt was not checked
            } else {
                $tax_exempt = 0;
            }
            
            // get old address verified value from the database
            $query = "SELECT billing_address_verified FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $address_verified = $row['address_verified'];

            // Verify address if address verification is enabled and it is a US address

            require_once(dirname(__FILE__) . '/shipping.php');

            $address_verified = verify_address(array(
                'liveform' => $liveform,
                'address_type' => 'billing',
                'old_address_verified' => $address_verified));

            // If there are errors, then set billing incomplete.
            if ($liveform->check_form_errors()) {
                $billing_complete = 0;

            // Otherwise there are no errors, so set billing complete.
            } else {
                $billing_complete = 1;
            }
            
            // add data to orders table
            $query =
                "UPDATE orders
                 SET
                    billing_salutation = '" . escape($liveform->get_field_value('billing_salutation')) . "',
                    billing_first_name = '" . escape($liveform->get_field_value('billing_first_name')) . "',
                    billing_last_name = '" . escape($liveform->get_field_value('billing_last_name')) . "',
                    billing_company = '" . escape($liveform->get_field_value('billing_company')) . "',
                    billing_address_1 = '" . escape($liveform->get_field_value('billing_address_1')) . "',
                    billing_address_2 = '" . escape($liveform->get_field_value('billing_address_2')) . "',
                    billing_city = '" . escape($liveform->get_field_value('billing_city')) . "',
                    billing_state = '" . escape($liveform->get_field_value('billing_state')) . "',
                    billing_zip_code = '" . escape($liveform->get_field_value('billing_zip_code')) . "',
                    billing_country = '" . escape($liveform->get_field_value('billing_country')) . "',
                    billing_address_verified = '" . escape($address_verified) . "',
                    billing_phone_number = '" . escape($liveform->get_field_value('billing_phone_number')) . "',
                    billing_fax_number = '" . escape($liveform->get_field_value('billing_fax_number')) . "',
                    billing_email_address = '" . escape($liveform->get_field_value('billing_email_address')) . "',
                    custom_field_1 = '" . escape($liveform->get_field_value('custom_field_1')) . "',
                    custom_field_2 = '" . escape($liveform->get_field_value('custom_field_2')) . "',
                    opt_in = '$opt_in',
                    tax_exempt = '$tax_exempt',
                    po_number = '" . escape($liveform->get_field_value('po_number')) . "',
                    referral_source_code = '" . escape($liveform->get_field_value('referral_source')) . "',
                    billing_complete = '$billing_complete',
                    ip_address = IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0)
                WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // If the user is logged in and not ghosting and the user selected that he/she
            // does not want his/her contact info updated with billing info,
            // then remember that in the session, so if the user comes back
            // to the billing information screen the check box will be unchecked.
            if (USER_LOGGED_IN and !$ghost and !$liveform->get_field_value('update_contact')) {
                $_SESSION['software']['update_contact'] = false;
            } else {
                unset($_SESSION['software']['update_contact']);
            }

            // If the user is not logged in or is ghosting or if the user is logged in and wants
            // his/her contact info updated with billing info, then update contact.
            if (!USER_LOGGED_IN or $ghost or ($_SESSION['software']['update_contact'] !== false)) {

                $sql_fax = '';

                // If a fax field appeared on the form, then update fax number for contact.
                // We don't want to clear the contact's fax number if there was no fax field.
                if ($liveform->field_in_session('billing_fax_number')) {
                    $sql_fax = "business_fax = '" . e($liveform->get('billing_fax_number')) . "',";
                }
                
                $query =
                    "UPDATE contacts
                    SET
                        salutation = '" . escape($liveform->get_field_value('billing_salutation')) . "',
                        first_name = '" . escape($liveform->get_field_value('billing_first_name')) . "',
                        last_name = '" . escape($liveform->get_field_value('billing_last_name')) . "',
                        company = '" . escape($liveform->get_field_value('billing_company')) . "',
                        business_address_1 = '" . escape($liveform->get_field_value('billing_address_1')) . "',
                        business_address_2 = '" . escape($liveform->get_field_value('billing_address_2')) . "',
                        business_city = '" . escape($liveform->get_field_value('billing_city')) . "',
                        business_state = '" . escape($liveform->get_field_value('billing_state')) . "',
                        business_zip_code = '" . escape($liveform->get_field_value('billing_zip_code')) . "',
                        business_country = '" . escape($liveform->get_field_value('billing_country')) . "',
                        business_phone = '" . escape($liveform->get_field_value('billing_phone_number')) . "',
                        $sql_fax
                        email_address = '" . escape($liveform->get_field_value('billing_email_address')) . "',
                        lead_source = '" . escape($liveform->get_field_value('referral_source')) . "',
                        opt_in = '$opt_in',
                        user = '$user_id',
                        timestamp = UNIX_TIMESTAMP()
                    WHERE id = '" . $contact_id . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
        
        // even though we are not done with validating all fields, we have to close out of this condition,
        // because we have to get data in order to figure out if the payment method is required and the data is also used by a paypal return
    }
    
    // get billing information for order (we will use this in various areas below)
    $query =
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
            po_number,
            tax_exempt,
            offline_payment_allowed,
            reference_code,
            special_offer_code
        FROM orders
        WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
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
    $custom_field_1 = $row['custom_field_1'];
    $custom_field_2 = $row['custom_field_2'];
    $po_number = $row['po_number'];
    $tax_exempt = $row['tax_exempt'];
    $offline_payment_allowed = $row['offline_payment_allowed'];
    $reference_code = $row['reference_code'];
    $special_offer_code = $row['special_offer_code'];
    
    // if tax is on, apply taxes to order
    if (ECOMMERCE_TAX == true) {
        update_order_item_taxes();
    }
    
    // get all products in cart
    $query =
        "SELECT
            order_items.id as order_item_id,
            order_items.ship_to_id,
            order_items.product_id,
            order_items.product_name,
            order_items.quantity,
            order_items.price,
            order_items.tax,
            order_items.recurring_payment_period,
            order_items.recurring_number_of_payments,
            order_items.recurring_start_date,
            order_items.recurrence_number,
            order_items.add_watcher,
            products.id,
            products.enabled,
            products.short_description,
            products.price AS product_price,
            products.recurring,
            products.sage_group_id,
            products.required_product,
            products.commissionable,
            products.commission_rate_limit,
            products.order_receipt_bcc_email_address,
            products.contact_group_id,
            products.membership_renewal,
            products.grant_private_access,
            products.private_folder,
            products.private_days,
            products.send_to_page,
            products.email_page,
            products.email_bcc,
            products.inventory,
            products.inventory_quantity,
            products.out_of_stock,
            products.reward_points,
            products.gift_card,
            products.gift_card_email_subject,
            products.gift_card_email_format,
            products.gift_card_email_body,
            products.gift_card_email_page_id,
            products.submit_form,
            products.submit_form_custom_form_page_id,
            products.submit_form_quantity_type,
            products.submit_form_create,
            products.submit_form_update,
            products.submit_form_update_where_field,
            products.submit_form_update_where_value,
            products.add_comment,
            products.add_comment_page_id,
            products.add_comment_message,
            products.add_comment_name,
            products.add_comment_only_for_submit_form_update,
            calendar_events.id as calendar_event_id,
            calendar_events.reservations,
            calendar_events.limit_reservations,
            calendar_events.number_of_initial_spots
        FROM order_items
        LEFT JOIN products ON order_items.product_id = products.id
        LEFT JOIN calendar_events ON order_items.calendar_event_id = calendar_events.id
        WHERE order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "'
        ORDER BY order_items.id ASC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $products = array();

    // foreach product in cart, add product to array
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    // if there are no items in the cart, output error
    if (count($products) == 0) {
        output_error('There are no items. The order cannot be submitted. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    $nonrecurring_products = array();
    $recurring_products = array();

    $recurring_transaction = false;

    // Loop through all ordered products to determine if they are non-recurring
    // or recurring.
    foreach ($products as $product) {
        // if product is not a recurring product or if start date is less than or equal to today's date and payment gateway is not ClearCommerce, then it will go in nonrecurring transaction
        // note: there can be recurring products in the nonrecurring transaction if the products have a start date of 0
        if (
            ($product['recurring'] == 0)
            ||
            (
                ($product['recurring_start_date'] <= date('Y-m-d'))
                && ((ECOMMERCE_CREDIT_DEBIT_CARD == false) || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce'))
            )
        ) {
            $nonrecurring_products[] = $product;
        }

        // if product is a recurring product, then add it to array
        if ($product['recurring']) {
            
            $recurring_products[] = $product;

            if ($product['price'] > 0) {
                $recurring_transaction = true;
            }

        }
    }

    // loop through all nonrecurring products in order to get totals for order
    foreach ($nonrecurring_products as $product) {

        $quantity = $product['quantity'];

        $total_price = $product['price'] * $product['quantity'];
        $total_tax =  $product['tax'] * $product['quantity'];

        // update subtotal
        $subtotal = $subtotal + $total_price;

        // update tax
        $tax = $tax + $total_tax;
    }

    // If the tax is negative then set it to zero.  The tax might be negative
    // if there are negative price products.  We don't want to allow a negative tax though.
    if ($tax < 0) {
        $tax = 0;
    }
    
    // set the discount for the order if there is one
    $discount = $_SESSION['ecommerce']['order_discount'];
    
    // store the orginal tax in case there is a discount
    // and we need to add a discount line item for PayPal Express Checkout
    $original_tax = $tax;

    // if there is an order discount, adjust tax according to discount
    if ($discount) {

        $tax = $tax - round(($tax * ($discount / $subtotal)));

        // If the tax is negative then set it to zero.
        if ($tax < 0) {
            $tax = 0;
        }

    }
    
    // get shipping total for all ship tos
    $query = "SELECT SUM(shipping_cost) as shipping FROM ship_tos WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $shipping = $row['shipping'];
    
    $total = $subtotal - $discount + $tax + $shipping;

    $gift_card_discount = 0;

    // If the total is greater than 0 then set gift card discount
    if ($total > 0) {
        // if the gift card balance is greater than the total, then set the gift card discount to the total
        if ($gift_card_balance > $total) {
            $gift_card_discount = $total;
            
        // else the gift card balance is less than or equal to the total, so set the gift card discount to the gift card balance
        } else {
            $gift_card_discount = $gift_card_balance;
        }
    }
    
    // update the total to include the gift card discount
    $total = $total - $gift_card_discount;

    // Store the total without the surcharge that we might add below.
    // We use this further below in the affiliate area,
    // when determining the percent of the gift card discount to the total.
    $total_without_surcharge = $total;

    $surcharge = 0;
    
    // if the total is greater than 0, then a non-recurring transaction is required
    if ($total > 0) {
        $nonrecurring_transaction = TRUE;

        // If a credit card surcharge is enabled and the credit card payment method
        // was selected, then calculate surcharge and add it to total.
        if (
            (ECOMMERCE_SURCHARGE_PERCENTAGE > 0)
            && ($liveform->get_field_value('payment_method') == 'Credit/Debit Card')
        ) {
            $surcharge = round(ECOMMERCE_SURCHARGE_PERCENTAGE / 100 * $total);
            $total = $total + $surcharge;
        }
        
    // else the total is not greater than 0, so a non-recurring transaction is not required
    } else {
        $nonrecurring_transaction = FALSE;
    }

    // get various properties for previous express order or order preview page
    // we just need the terms page name in the block below, however we will go ahead and get other values
    // that we will use later in the code, in order to minimize database queries
    $query =
        "SELECT
            page.page_name,
            " . $type_value . "_pages.offline_payment_always_allowed,
            " . $type_value . "_pages.auto_registration,
            " . $type_value . "_pages.pre_save_hook_code,
            " . $type_value . "_pages.post_save_hook_code,
            " . $type_value . "_pages.order_receipt_email,
            " . $type_value . "_pages.order_receipt_email_subject,
            " . $type_value . "_pages.order_receipt_email_format,
            " . $type_value . "_pages.order_receipt_email_header,
            " . $type_value . "_pages.order_receipt_email_footer,
            " . $type_value . "_pages.order_receipt_email_page_id,
            " . $type_value . "_pages.next_page_id
        FROM " . $type_value . "_pages
        LEFT JOIN page ON " . $type_value . "_pages.terms_page_id = page.page_id
        WHERE " . $type_value . "_pages.page_id = '" . escape($page_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $terms_page_name = $row['page_name'];
    $offline_payment_always_allowed = $row['offline_payment_always_allowed'];
    $auto_registration = $row['auto_registration'];
    $pre_save_hook_code = $row['pre_save_hook_code'];
    $post_save_hook_code = $row['post_save_hook_code'];
    $order_receipt_email = $row['order_receipt_email'];
    $order_receipt_email_subject = $row['order_receipt_email_subject'];
    $order_receipt_email_format = $row['order_receipt_email_format'];
    $order_receipt_email_header = $row['order_receipt_email_header'];
    $order_receipt_email_footer = $row['order_receipt_email_footer'];
    $order_receipt_email_page_id = $row['order_receipt_email_page_id'];
    $next_page_id = $row['next_page_id'];
    
    // if the mode is not paypal_express_checkout_return, then validate the rest of the fields
    if ($_GET['mode'] != 'paypal_express_checkout_return' and $_GET['mode'] != 'iyzipay_threedsecure_return') {
        // if a nonrecurring transaction or recurring transaction is required, then require a payment method
        if (($nonrecurring_transaction == TRUE) || ($recurring_transaction == TRUE)) {
            $liveform->validate_required_field('payment_method', 'A payment method is required.');
            
            // if there is not already an error for the payment method field and the selected payment method is not valid, then prepare error
            if (
                ($liveform->check_field_error('payment_method') == FALSE)
                &&
                (
                    (($liveform->get_field_value('payment_method') == 'Credit/Debit Card') && (ECOMMERCE_CREDIT_DEBIT_CARD == FALSE))
                    || (($liveform->get_field_value('payment_method') == 'PayPal Express Checkout') && (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT == FALSE))
                    || (($liveform->get_field_value('payment_method') == 'PayPal Express Checkout') && ($recurring_transaction == TRUE))
                    ||
                    (
                        ($liveform->get_field_value('payment_method') == 'Offline Payment')
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
                )
            ) {
                $liveform->mark_error('payment_method', 'The selected payment method is not available.');
            }
            
            // if there is not already an error for the payment method field and the credit/debit card payment method was selected, then validate credit/debit card fields
            if (
                ($liveform->check_field_error('payment_method') == FALSE)
                && ($liveform->get_field_value('payment_method') == 'Credit/Debit Card')
            ) {
                
                $liveform->validate_required_field('card_number', 'Card Number is required.');

                // If there was a single expiration field (new way) then require that field.
                if ($liveform->field_in_session('expiration')) {

                    $expiration_single = true;
                    $liveform->validate_required_field('expiration', 'Expiration is required.');

                // Otherwise there must be two expiration fields (old way), so require both fields.
                } else {

                    $expiration_single = false;
                    $liveform->validate_required_field('expiration_month', 'Expiration Month is required.');
                    $liveform->validate_required_field('expiration_year', 'Expiration Year is required.');
                }

                // If there was a cardholder field then require it.
                if ($liveform->field_in_session('cardholder')) {
                    $liveform->validate_required_field('cardholder', 'Cardholder is required.');
                }

                // If there was a security code field then require it.
                if ($liveform->field_in_session('card_verification_number')) {
                    $liveform->validate_required_field('card_verification_number', 'Security Code is required.');
                }
                
                // If there is not already an error for the card number field validate card number
                if (!$liveform->check_field_error('card_number')) {

                    // remove non-numeric characters from credit card number
                    $card_number = preg_replace('/[^0-9]/', '', $liveform->get_field_value('card_number'));
                    
                    $card_number_prefix  = mb_substr($card_number, 0, 4);
                    $card_number_length = mb_strlen($card_number);

                    $card_type = '';

                    if (
                        ECOMMERCE_AMERICAN_EXPRESS and ($card_number_length == 15) && ((($card_number_prefix >= 3400) && ($card_number_prefix <= 3499)) || (($card_number_prefix >= 3700) && ($card_number_prefix <= 3799)))
                    ) {
                        $card_type = 'American Express';

                    } else if (
                        ECOMMERCE_DINERS_CLUB and ($card_number_length == 14) && ((($card_number_prefix >= 3000) && ($card_number_prefix <= 3059)) || (($card_number_prefix >= 3600) && ($card_number_prefix <= 3699)) || (($card_number_prefix >= 3800) && ($card_number_prefix <= 3889)))
                    ) {
                        $card_type = 'Diners Club';

                    } else if (
                        ECOMMERCE_DISCOVER_CARD and ($card_number_length == 16) && ($card_number_prefix == 6011)
                    ) {
                        $card_type = 'Discover Card';

                    } else if (
                        ECOMMERCE_MASTERCARD and ($card_number_length == 16) && ($card_number_prefix >= 5100) && ($card_number_prefix <= 5599)
                    ) {
                        $card_type = 'MasterCard';

                    } else if (
                        ECOMMERCE_VISA and (($card_number_length == 16) || ($card_number_length == 13)) && ($card_number_prefix >= 4000) && ($card_number_prefix <= 4999)
                    ) {
                        $card_type = 'Visa';
                    }

                    // Assume that card number is not valid, until we find out otherwise.
                    $valid_credit_card = false;
                    
                    // If the credit card number is valid so far, perform mod10 check.
                    if ($card_type) {

                        $checksum = 0;
                        
                        // add even digits in even length strings or odd digits in odd length strings.
                        for ($location = 1 - ($card_number_length % 2); $location < $card_number_length; $location += 2) {
                            $checksum += mb_substr($card_number, $location, 1);
                        }

                        // analyze odd digits in even length strings or even digits in odd length strings.
                        for ($location = ($card_number_length % 2); $location < $card_number_length; $location += 2) {
                            $digit = mb_substr($card_number, $location, 1) * 2;
                            if ($digit < 10) {
                                $checksum += $digit;
                            } else {
                                $checksum += $digit - 9;
                            }
                        }

                        // if checksum is divisible by 10 then credit card number is valid
                        if ($checksum % 10 == 0) {
                            $valid_credit_card = true;
                        }
                    }
                    
                    // If the credit card number is not valid, mark error.
                    if (!$valid_credit_card) {
                        $liveform->mark_error('card_number', 'The credit/debit card number is invalid. Please check that you have entered the number correctly. If the number is correct, then we might not support that type of card, so please try a different card.');
                    }
                }

                // If there is not already an error for the expiration, then get values.
                if (
                    !$liveform->check_field_error('expiration')
                    and !$liveform->check_field_error('expiration_month')
                    and !$liveform->check_field_error('expiration_year')
                ) {

                    // If there was a single expiration field (new way) then get month and year.
                    if ($expiration_single) {

                        $expiration_parts = explode('/', $liveform->get('expiration'));
                        $expiration_month = trim($expiration_parts[0]);
                        $expiration_year = trim($expiration_parts[1]);

                        if (!$expiration_month or !$expiration_year) {

                            $liveform->mark_error('expiration', 'The expiration is not valid.');

                        } else {

                            // If the month has 1 digit, then convert to 2.
                            if (mb_strlen($expiration_month) == 1) {
                                $expiration_month = '0' . $expiration_month;
                            }

                            // If the year has 2 digits, then convert to 4.
                            if (mb_strlen($expiration_year) == 2) {
                                $expiration_year = '20' . $expiration_year;
                            }
                        }

                    // Otherwise there must be two expiration fields (old way).
                    } else {
                        $expiration_month = $liveform->get('expiration_month');
                        $expiration_year = $liveform->get('expiration_year');
                    }
                }

                // If there is not already an error for the expiration, then check if expiration is
                // in the past, or too far in the future.
                if (
                    !$liveform->check_field_error('expiration')
                    and !$liveform->check_field_error('expiration_month')
                    and !$liveform->check_field_error('expiration_year')
                ) {

                    $current_year = date('Y');
                    $current_month = date('m');

                    // If the expiration is in the past, then add error.
                    if (
                        $expiration_year < $current_year
                        or ($expiration_year == $current_year and $expiration_month < $current_month)
                    ) {

                        $message = 'The expiration date has passed.';

                        // If there was a single expiration field (new way).
                        if ($expiration_single) {
                            $liveform->mark_error('expiration', $message);

                        // Otherwise there must be two expiration fields (old way).
                        } else {
                            $liveform->mark_error('expiration_month', $message);
                            $liveform->mark_error('expiration_year', '');
                        }

                    // Otherwise if the expiration is too far in the future, then add error.
                    } else if ($expiration_year > ($current_year + 20)) {

                        $message = 'Sorry, the expiration year (' . h($expiration_year) . ') is too far in the future. Please check that you have entered the correct year. We allow a maximum of 20 years in the future.';

                        // If there was a single expiration field (new way).
                        if ($expiration_single) {
                            $liveform->mark_error('expiration', $message);

                        // Otherwise there must be two expiration fields (old way).
                        } else {
                            $liveform->mark_error('expiration_year', $message);
                        }
                    }
                }
            }
        }

        // if a terms page name was found, then require terms checkbox to be checked
        if ($terms_page_name) {
            $liveform->validate_required_field('terms', 'Agreement to the terms and conditions is required.');
        }
        
        // we are done with the validation of fields now
    }

    // Loop through the order items in order to determine if we need to remove order items
    // for products that have been deleted or disabled.  We have to have a special area to
    // do this here because this function does not run the update_order_item_prices() function,
    // which takes care of removing order items in other areas of the system.  In the future,
    // we might need to decide if we should just run update_order_item_prices() here also.
    foreach ($products as $product) {
        // If the product for this order item has been deleted or is disabled
        // then remove order item and add error.
        if (
            ($product['id'] == '')
            || ($product['enabled'] == 0)
        ) {
            remove_order_item($product['order_item_id']);

            // prepare product description for notice
            $product_description = '';
            
            // if there is a name, then add it to the description
            if ($product['product_name'] != '') {
                $product_description .= $product['product_name'];
            }
            
            // if there is a short description, then add it to the description
            if ($product['short_description'] != '') {
                // if the description is not blank, then add separator
                if ($product_description != '') {
                    $product_description .= ' - ';
                }
                
                $product_description .= $product['short_description'];
            }
            
            // if the description is blank, then set default description
            if ($product_description == '') {
                $product_description = 'an item';
            }

            $liveform->add_notice('We\'re sorry, ' . h($product_description) . ' has been removed from your order because it is not currently available.');
        }
    }

    // Check min and max quantity requirements for products.
    check_quantity($liveform);
    
    // check inventory for order items in order to make sure they are still all in stock
    check_inventory($liveform);
    
    // check reservations for calendar events in order to make sure they are still valid and available
    check_reservations($liveform);
    
    // loop through all order items in order to determine if all required products are in order
    foreach ($products as $product) {
        // if product has a required product, then make sure required product is in cart
        if ($product['required_product']) {
            // find out if required product is in cart
            $query =
                "SELECT id
                FROM order_items
                WHERE
                    (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                    AND (product_id = '" . $product['required_product'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if required product is not in cart, add product to cart and add notice, so user knows that required product was added to cart
            if (mysqli_num_rows($result) == 0) {
                // get information about required product
                $query = "SELECT name FROM products WHERE id = '" . $product['required_product'] . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $required_product_name = $row['name'];

                // add required product to cart
                add_order_item($product['required_product'], 1, 0, '', '');

                $liveform->add_notice(h($product['product_name']) . ' requires ' .  h($required_product_name) . ', so ' . h($required_product_name) . ' has been added to your order.');
            }
        }
    }
    
    /* begin: check that there are no free order items alone in a ship to */
    
    $query =
        "SELECT
            ship_to_id,
            product_name
        FROM order_items
        WHERE
            (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
            AND (ship_to_id > 0)
            AND (price <= 0)";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $free_order_items = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $free_order_items[] = $row;
    }
    
    // loop through all free order items to see if they are alone in ship to
    foreach ($free_order_items as $free_order_item) {
        // try to get a non-free order item in the same ship to as the free order item
        $query =
            "SELECT id
            FROM order_items
            WHERE
                (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                AND (ship_to_id = '" . $free_order_item['ship_to_id'] . "')
                AND (price > 0)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if a non-free order item could not be found, add error
        if (mysqli_num_rows($result) == 0) {
            $liveform->mark_error('free_order_item_error', h($free_order_item['product_name']) . ' is a free item that you have requested to ship with no non-free items.  Free items must be shipped with at least one non-free item. You may update your order so that the item is shipped with at least one non-free item or you may remove the item.');
        }
    }
    
    /* end: check that there are no free order items alone in a ship to */
    
    // If this is not a PayPal return and there is not already an error or notice and the total is
    // greater than the total that was last displayed to the user, then we need to show the correct
    // total to the user before the order is submitted, so add error.  We don't want to charge the
    // customer a greater amount than they saw.  We have to do this in case gift card balances have
    // changed or tax has changed (e.g. after billing info is submitted on express order screen).
    // If the new total is less than the old total, then we just allow the order to be submitted,
    // because the customer won't care that they are being charged less.  We use round() to resolve
    // any floating point issues with the total.  We use @ to suppress any warnings in PHP 7.1+.
    
    if( ($_GET['mode'] != 'paypal_express_checkout_return' and $_GET['mode'] != 'iyzipay_threedsecure_return') 
        and ($liveform->check_form_errors() == FALSE)
        and ($liveform->check_form_notices() == FALSE)
        and
        (
            (
                ($surcharge == 0)
                and (round($total) > round(@($liveform->get('total') * 100)))
            )
            or
            (
                ($surcharge > 0)
                and (round($total) > round(@($liveform->get('total_with_surcharge') * 100)))
            )
        )
    ) {

        $liveform->mark_error('total', 'Sorry, the order was not accepted, because the total has changed. Can you please review the new total and submit the order again? This might happen if a cost has changed since you last reviewed the order.');

        if ($surcharge == 0) {
            $old_total = $liveform->get('total');
        } else {
            $old_total = $liveform->get('total_with_surcharge');
        }

        log_activity('Order (id: ' . $_SESSION['ecommerce']['order_id'] . ', reference code: ' . $reference_code . ') was not accepted because the new total (' . prepare_amount($total / 100) . ') was greater than the old total (' . prepare_amount($old_total) . ') that the customer saw. The customer was asked to review the new total and submit the order again. This might be normal, if, for example, the customer stayed on the page for a long time before submitting the order, because a cost might have changed during that time period. Or, it might be normal if you are using an express order page with tax enabled, because the customer\'s tax is not known until an address is supplied. However, if this issue happens often, then it might indicate an issue that needs to be addressed.');
    }

    // If hooks are enabled and there is pre-save hook code, then run it.
    if ((defined('PHP_REGIONS') and PHP_REGIONS) && ($pre_save_hook_code != '')) {
        eval(prepare_for_eval($pre_save_hook_code));
    }
    
    // if an error or notice exists, then send user back to previous screen
    if (($liveform->check_form_errors() == TRUE) || ($liveform->check_form_notices() == TRUE)) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
        exit();
    }
    
    // if a nonrecurring transaction or recurring transaction is required, then prepare for communication with payment service differently based on selected payment method
    if (($nonrecurring_transaction == TRUE) || ($recurring_transaction == TRUE)) {
        switch ($liveform->get_field_value('payment_method')) {
            // is credit/debit card payment method was selected, process transaction through a payment gateway if one is selected
            case 'Credit/Debit Card':
                switch (ECOMMERCE_PAYMENT_GATEWAY) {
                    case 'Authorize.Net':
                        // if cURL is not installed, then prepare error and send user back to previous screen
                        if (function_exists('curl_init') == false) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.');

                            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        break;
                        
                    case 'ClearCommerce':
                        // if cURL is not installed, then prepare error and send user back to previous screen
                        if (function_exists('curl_init') == false) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.');

                            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
                            $clearcommerce_mode = 'Y';
                            $payment_gateway_host = 'https://test5x.clearcommerce.com:11500';
                        } else {
                            $clearcommerce_mode = 'P';
                            $payment_gateway_host = 'https://xmlic.payfuse.com';
                        }
                        
                        // if transaction type is set to Authorize, then prepare type to send
                        if (ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE == 'Authorize') {
                            $clearcommerce_type = 'PreAuth';
                            
                        // else transaction type is Authorize & Capture, so prepare type to send
                        } else {
                            $clearcommerce_type = 'Auth';
                        }

                        // Prepare ClearCommerce currency code.
                        switch (BASE_CURRENCY_CODE) {
                            default:
                            case 'USD':
                                $clearcommerce_currency_code = '840';
                                break;

                            case 'AUD':
                                $clearcommerce_currency_code = '036';
                                break;

                            case 'CAD':
                                $clearcommerce_currency_code = '124';
                                break;

                            case 'EUR':
                                $clearcommerce_currency_code = '978';
                                break;

                            case 'GBP':
                                $clearcommerce_currency_code = '826';
                                break;
                        }
                        
                        break;
                        
                    case 'First Data Global Gateway':
                        // if cURL is not installed, then prepare error and send user back to previous screen
                        if (function_exists('curl_init') == false) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.');

                            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
                            $first_data_global_gateway_mode = 'GOOD';
                        } else {
                            $first_data_global_gateway_mode = 'LIVE';
                        }
                        
                        // if transaction type is set to Authorize, then prepare type to send
                        if (ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE == 'Authorize') {
                            $first_data_global_gateway_type = 'PREAUTH';
                            
                        // else transaction type is Authorize & Capture, so prepare type to send
                        } else {
                            $first_data_global_gateway_type = 'SALE';
                        }
                        
                        break;
                    
                    case 'PayPal Payflow Pro':
                        $payment_gateway_communication_method = '';
                        
                        // if cURL is installed, then use it
                        if (function_exists('curl_init') == true) {
                            $payment_gateway_communication_method = 'curl';
                            
                        // else cURL is not installed, so check for PayPal Payflow Pro Client
                        } else {
                            // if this server is on Windows, use COM object to connect to PayPal Payflow Pro
                            if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) == 'WIN') {
                                $payflowpro = new COM("PFProCOMControl.PFProCOMControl.1");
                                
                                // if connection was successful, set communication method
                                if ($payflowpro) {
                                    $payment_gateway_communication_method = 'payflow pro com object';
                                }
                                
                            // else this server is not on Windows, so check for PayPal Payflow Pro Client for Unix (i.e. PHP pfpro extension) is installed
                            } else {
                                // if connection was successful, set communication method
                                if (function_exists('pfpro_process') == true) {
                                    $payment_gateway_communication_method = 'pfpro extension';
                                }
                            }
                        }
                        
                        // set different payment gateway settings based on communication method
                        switch ($payment_gateway_communication_method) {
                            case 'curl':
                                // set payment gateway host
                                if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
                                    $payment_gateway_host = 'https://pilot-payflowpro.paypal.com';
                                } else {
                                    $payment_gateway_host = 'https://payflowpro.paypal.com';
                                }
                                
                                break;
                                
                            case 'payflow pro com object':
                            case 'pfpro extension':
                                // set the Pay Flow Pro certificate path so that PHP can find the certificate file (f73e89fd.0)
                                putenv('PFPRO_CERT_PATH=./certs/');
                                
                                // set payment gateway host
                                if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
                                    $payment_gateway_host = 'test-payflow.verisign.com';
                                } else {
                                    $payment_gateway_host = 'payflow.verisign.com';
                                }
                                
                                break;
                        }
                        
                        // if there is a communication error with PayPal Payflow Pro, then prepare error and send user back to previous screen
                        if (!$payment_gateway_communication_method) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL or the Payflow Pro Client (i.e. PHP pfpro extension for Unix, Payflow Pro COM Object for Windows).');

                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        // if a user was entered for payment gateway, then use user
                        if (ECOMMERCE_PAYPAL_PAYFLOW_PRO_USER) {
                            $payment_gateway_user = ECOMMERCE_PAYPAL_PAYFLOW_PRO_USER;
                            
                        // else a user was not entered for payment gateway, so use merchant login
                        } else {
                            $payment_gateway_user = ECOMMERCE_PAYPAL_PAYFLOW_PRO_MERCHANT_LOGIN;
                        }
                        
                        break;
                        
                    // if PayPal Payments Pro payment method was selected
                    case 'PayPal Payments Pro':
                        // if cURL is not installed, then prepare error and send user back to previous screen
                        if (function_exists('curl_init') == false) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.');

                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        // if the payment gateway mode is set to test, set the payment gatway host to the sandbox posting url.
                        if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
                            $payment_gateway_host = 'https://api-3t.sandbox.paypal.com/nvp';
                            
                        // else, the payment gameway mode is set to live, so use the live payment gateway host.
                        } else {
                            $payment_gateway_host = 'https://api-3t.paypal.com/nvp';
                        }
                        
                        // Setup the credit card type required for the payment gateway.
                        switch ($card_type) {
                            case 'American Express':
                                $credit_card_type = 'Amex';
                                break;
                            
                            case 'Diners Club':
                                $credit_card_type = 'Diners Club';
                                break;
                            
                            case 'Discover Card':
                                $credit_card_type = 'Discover';
                                break;
                            
                            case 'MasterCard':
                                $credit_card_type = 'MasterCard';
                                break;
                            
                            case 'Visa':
                                $credit_card_type = 'Visa';
                                break;
                        }
                        
                        break;
                        
                    case 'Sage':
                        // if cURL is not installed, then prepare error and send user back to previous screen
                        if (function_exists('curl_init') == false) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.');

                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        // if transaction type is set to Authorize, then prepare type to send
                        if (ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE == 'Authorize') {
                            $sage_transaction_type = '02';
                            
                        // else transaction type is Authorize & Capture, so prepare type to send
                        } else {
                            $sage_transaction_type = '01';
                        }
                        
                        break;

                    case 'Stripe':
                        // if cURL is not installed, then prepare error and send user back to previous screen
                        if (function_exists('curl_init') == false) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.');

                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        break;

                    case 'Iyzipay':
                        // if cURL is not installed, then prepare error and send user back to previous screen
                        if (function_exists('curl_init') == false) {
                            $liveform->mark_error('payment_gateway', 'This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.');

                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        break;
                }
                
                break;
                
            // if PayPal payment method was selected
            case 'PayPal Express Checkout':
                // if cURL is not installed, then prepare error and send user back to previous screen
                if (function_exists('curl_init') == false) {
                    $liveform->mark_error('paypal_express_checkout', 'This website cannot communicate with PayPal.  The administrator of this website should install cURL.');

                    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                    exit();
                }
                
                break;
        }
    }
    
    // if a non-recurring transaction is required, then process transaction through payment gateway, if there is one
    if ($nonrecurring_transaction == TRUE) {
        $payment_gateway_error_message_prefix = 'Error message from payment gateway: ';
        
        // process transaction differently based on selected payment method
        switch ($liveform->get_field_value('payment_method')) {
            // is credit/debit card payment method was selected, process transaction through a payment gateway if one is selected
            case 'Credit/Debit Card':
                switch (ECOMMERCE_PAYMENT_GATEWAY) {
                    case 'Authorize.Net':
                        // If the Authorize.Net URL has been set in the config.php file
                        // (for an Authorize.Net emulator service), then use it.
                        if ((defined('AUTHORIZENET_URL') == true) && (AUTHORIZENET_URL != '')) {
                            $payment_gateway_host = AUTHORIZENET_URL;
                            
                        // Otherwise use the standard Authorize.Net url.
                        } else {
                            $payment_gateway_host = 'https://secure2.authorize.net/gateway/transact.dll';
                        }

                        if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
                            $x_test_request = 'TRUE';
                        } else {
                            $x_test_request = 'FALSE';
                        }
                        
                        // if transaction type is set to Authorize, then prepare x_type to send
                        if (ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE == 'Authorize') {
                            $x_type = 'AUTH_ONLY';
                            
                        // else transaction type is Authorize & Capture, so prepare x_type to send
                        } else {
                            $x_type = 'AUTH_CAPTURE';
                        }
                        
                        if ($tax_exempt == 1) {
                            $x_tax_exempt = 'TRUE';
                        } else {
                            $x_tax_exempt = 'FALSE';
                        }
                        
                        $transaction_values =
                            array(
                                'x_version' => '3.1',
                                'x_login' => ECOMMERCE_AUTHORIZENET_API_LOGIN_ID,
                                'x_tran_key' => ECOMMERCE_AUTHORIZENET_TRANSACTION_KEY,
                                'x_test_request' => $x_test_request,
                                'x_delim_data' => 'TRUE',
                                'x_delim_char' => ',',
                                'x_relay_response' => 'FALSE',
                                'x_type' => $x_type,
                                'x_first_name' => $billing_first_name,
                                'x_last_name' => $billing_last_name,
                                'x_company' => $billing_company,
                                'x_email' => $billing_email_address,
                                'x_address' => $billing_address_1 . ' ' . $billing_address_2,
                                'x_city' => $billing_city,
                                'x_state' => $billing_state,
                                'x_zip' => $billing_zip_code,
                                'x_phone' => $billing_phone_number,
                                'x_fax' => $billing_fax_number,
                                'x_po_num' => $po_number,
                                'x_tax_exempt' => $x_tax_exempt,
                                'x_customer_ip' => $_SERVER['REMOTE_ADDR'],
                                'x_amount' => sprintf("%01.2lf", $total / 100),
                                'x_currency_code' => BASE_CURRENCY_CODE,
                                'x_tax' => sprintf("%01.2lf", $tax / 100),
                                'x_card_num' => $card_number,
                                'x_exp_date' => $expiration_month . '/' . $expiration_year,
                                'x_card_code' => $liveform->get_field_value('card_verification_number'),
                                'x_solution_id' => 'A1000013');
                        
                        $post_data = '';
                        
                        foreach ($transaction_values as $name => $value) {
                            if ($post_data) {
                                $post_data .= '&';
                            }
                            
                            $post_data .= $name . '=' . urlencode($value);
                        }
                        
                        $ch = curl_init();
                        
                        curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        
                        // if there is a proxy address, then send cURL request through proxy
                        if (PROXY_ADDRESS != '') {
                            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                            curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                        }
                        
                        $response_data = curl_exec($ch);
                        
                        $curl_errno = curl_errno($ch);
                        $curl_error = curl_error($ch);
                        
                        curl_close($ch);
                        
                        $response = explode(',', $response_data);
                        
                        // increment the keys of the array by one, so that the keys match up with Authorize.net numbers
                        array_unshift($response, '');

                        // If transaction failed, prepare error.
                        // We had to update the test below to change "trim($response[1]) != 1",
                        // "to trim($response[1]) !== '1'", because Authorize.Net started returning
                        // "1.0" for the response code when there was an error.  That is strange,
                        // because "1" means success, which was causing our previous PHP comparison to match.
                        // Other codes (e.g. "2", "3", etc.)  mean failure, so we have no idea
                        // why they decided to start returning "1.0" for a failure.  "1.0" is
                        // not mentioned in their documentation as being a valid code.
                        if ((count($response) == 1) || (trim($response[1]) !== '1')) {
                            // if there was a response from the payment gateway, then use response text in error
                            if ($response[4]) {
                                $payment_gateway_error_message = $payment_gateway_error_message_prefix . $response[4];
                                
                            // else there was no response, so there was a communication problem
                            } else {
                                $payment_gateway_error_message = 'An error occurred while trying to communicate with the payment gateway.';
                                
                                // if there was a cURL error, add error number and error message to payment gateway error message
                                if ($curl_errno) {
                                    $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                }
                            }

                            log_activity($payment_gateway_error_message);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            // send user back to previous screen
                            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        $transaction_id = $response[7];
                        $authorization_code = $response[5];
                        
                        break;

                    // We originally added the round function to the total below
                    // in order to solve a problem where there might be a decimal value
                    // for the total, which contains cents (e.g. 1099.5 cents), when an
                    // order discount was applied.  ClearCommerce would return an error then.
                    // We have since fixed the issue so that the discount in cents should
                    // never contain a decimal. This means that the round function is probably
                    // not needed anymore however we have decided to leave it for now, in case it is.
                    
                    case 'ClearCommerce':
                        $transaction_xml =
                            '<EngineDocList>
                                <DocVersion>1.0</DocVersion>
                                <EngineDoc>
                                    <ContentType>OrderFormDoc</ContentType>
                                    <User>
                                        <Name>' . h(ECOMMERCE_CLEARCOMMERCE_USER_ID) . '</Name>
                                        <Password>' . h(ECOMMERCE_CLEARCOMMERCE_PASSWORD) . '</Password>
                                        <Alias>' . h(ECOMMERCE_CLEARCOMMERCE_CLIENT_ID) . '</Alias>
                                    </User>
                                    <Instructions>
                                        <Pipeline>Payment</Pipeline>
                                    </Instructions>
                                    <OrderFormDoc>
                                        <Mode>' . $clearcommerce_mode . '</Mode>
                                        <Consumer>
                                            <PaymentMech>
                                                <CreditCard>
                                                    <Number>' . $card_number . '</Number>
                                                    <Expires DataType="ExpirationDate" Locale="840">' . h($expiration_month) . '/' . h($expiration_year) . '</Expires>
                                                    <Cvv2Val>' . h($liveform->get_field_value('card_verification_number')) . '</Cvv2Val>
                                                    <Cvv2Indicator>1</Cvv2Indicator>
                                                </CreditCard>
                                            </PaymentMech>
                                            <BillTo>
                                                <Location>
                                                    <Address>
                                                        <Name>' . h($billing_first_name) . ' ' . h($billing_last_name) . '</Name>
                                                        <Company>' . h($billing_company) . '</Company>
                                                        <Street1>' . h($billing_address_1) . '</Street1>
                                                        <Street2>' . h($billing_address_2) . '</Street2>
                                                        <City>' . h($billing_city) . '</City>
                                                        <StateProv>' . h($billing_state) . '</StateProv>
                                                        <PostalCode>' . h($billing_zip_code) . '</PostalCode>
                                                        <Country>' . get_country_number($billing_country) . '</Country>
                                                    </Address>
                                                </Location>
                                            </BillTo>
                                        </Consumer>
                                        <Transaction>
                                            <Type>' . $clearcommerce_type . '</Type>
                                            <CurrentTotals>
                                                <Totals>
                                                    <Total DataType="Money" Currency="' . $clearcommerce_currency_code . '">' . round($total) . '</Total>
                                                </Totals>
                                            </CurrentTotals>
                                        </Transaction>
                                    </OrderFormDoc>
                                </EngineDoc>
                            </EngineDocList>';
                        
                        $ch = curl_init();
                        
                        curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, 'CLRCMRC_XML=' . urlencode($transaction_xml));
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        
                        // if there is a proxy address, then send cURL request through proxy
                        if (PROXY_ADDRESS != '') {
                            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                            curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                        }
                        
                        $response_data = curl_exec($ch);
                        
                        $curl_errno = curl_errno($ch);
                        $curl_error = curl_error($ch);
                        
                        curl_close($ch);
                        
                        // get CcErrCode
                        preg_match('/<CcErrCode DataType="S32">(.*?)<\/CcErrCode>/', $response_data, $matches);
                        $clearcommerce_CcErrCode = $matches[1];
                        
                        // get CcReturnMsg
                        preg_match('/<CcReturnMsg DataType="String">(.*?)<\/CcReturnMsg>/', $response_data, $matches);
                        $clearcommerce_CcReturnMsg = $matches[1];
                        
                        // get Text
                        preg_match('/<Text DataType="String">(.*?)<\/Text>/', $response_data, $matches);
                        $clearcommerce_Text = $matches[1];
                        
                        // if transaction failed, prepare error
                        if ($clearcommerce_CcErrCode != '1') {
                            // if there was a cErrCode value, then use CcReturnMsg for error message
                            if ($clearcommerce_CcErrCode != '') {
                                $payment_gateway_error_message = $payment_gateway_error_message_prefix . $clearcommerce_CcReturnMsg;
                                
                            // else if there was a Text value, then use value for error message
                            } elseif ($clearcommerce_Text != '') {
                                $payment_gateway_error_message = $payment_gateway_error_message_prefix . $clearcommerce_Text;
                                
                            // else there was no response, so there was a communication problem
                            } else {
                                $payment_gateway_error_message = 'An error occurred while trying to communicate with the payment gateway.';
                                
                                // if there was a cURL error, add error number and error message to payment gateway error message
                                if ($curl_errno) {
                                    $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                }
                            }

                            log_activity($payment_gateway_error_message);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            // send user back to previous screen
                            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        // get transaction id
                        preg_match('/<TransactionId DataType="String">(.*?)<\/TransactionId>/', $response_data, $matches);
                        $transaction_id = $matches[1];
                        
                        // get authorization code
                        preg_match('/<AuthCode DataType="String">(.*?)<\/AuthCode>/', $response_data, $matches);
                        $authorization_code = $matches[1];
                        
                        break;
                        
                    case 'First Data Global Gateway':
                        // Get the numerical portion of the 1st billing address.
                        preg_match('/[0-9]+/', $billing_address_1, $matches);
                        $avs_address_number = $matches[0];
                        
                        // Build the First Data Global Gateway XML Packet
                        // They do not appear to support foreign currencies (at least at the API level),
                        // so we are not passing currency info.
                        $transaction_xml =
                            '<order>
                                <billing>
                                    <name>' . h($billing_first_name) . ' ' . h($billing_last_name) . '</name>
                                    <company>' . h($billing_company) . '</company>
                                    <address1>' . h($billing_address_1) . '</address1>
                                    <address2>' . h($billing_address_2) . '</address2>
                                    <city>' . h($billing_city) . '</city>
                                    <state>' . h($billing_state) . '</state>
                                    <zip>' . h($billing_zip_code) . '</zip>
                                    <country>' . h($billing_country) . '</country>
                                    <phone>' . h($billing_phone_number) . '</phone>
                                    <fax>' . h($billing_fax_number) . '</fax>
                                    <email>' . h($billing_email_address) . '</email>
                                    <addrnum>' . $avs_address_number . '</addrnum>
                                </billing>
                                <transactiondetails>
                                    <ip>' . h($_SERVER['REMOTE_ADDR']) . '</ip>
                                </transactiondetails>
                                <orderoptions>
                                    <ordertype>' . $first_data_global_gateway_type . '</ordertype>
                                    <result>' . $first_data_global_gateway_mode . '</result>
                                </orderoptions>
                                <payment>
                                    <subtotal>' . $subtotal / 100 . '</subtotal>
                                    <tax>' . $tax / 100 . '</tax>
                                    <shipping>' . $shipping / 100 . '</shipping>
                                    <chargetotal>' . sprintf("%01.2lf", $total / 100) . '</chargetotal>
                                </payment>
                                <creditcard>
                                    <cardnumber>' . $card_number . '</cardnumber>
                                    <cardexpmonth>' . h($expiration_month) . '</cardexpmonth>
                                    <cardexpyear>' .  h(mb_substr($expiration_year, -2)) . '</cardexpyear>
                                    <cvmvalue>' .  h($liveform->get_field_value('card_verification_number')) . '</cvmvalue>
                                    <cvmindicator>provided</cvmindicator>
                                </creditcard>
                                <merchantinfo>
                                    <configfile>' . h(ECOMMERCE_FIRST_DATA_GLOBAL_GATEWAY_STORE_NUMBER) . '</configfile>
                                </merchantinfo>
                            </order>';
                        
                        $ch = curl_init();
                        
                        curl_setopt($ch, CURLOPT_URL, 'https://secure.linkpt.net:1129');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                        // Setup an SSL connection using the supplied .pem file from First Data Global Gateway
                        curl_setopt($ch, CURLOPT_SSLCERT, FILE_DIRECTORY_PATH . '/' . ECOMMERCE_FIRST_DATA_GLOBAL_GATEWAY_PEM_FILE_NAME);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $transaction_xml);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  0);
                        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        
                        // if there is a proxy address, then send cURL request through proxy
                        if (PROXY_ADDRESS != '') {
                            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                            curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                        }
                        
                        $response_data = curl_exec($ch);

                        $curl_errno = curl_errno($ch);
                        $curl_error = curl_error($ch);
                        
                        curl_close($ch);

                        // get r_approved
                        preg_match('/<r_approved>(.*?)<\/r_approved>/', $response_data, $matches);
                        $first_data_global_gateway_approved = $matches[1];
                        
                        // get r_error
                        preg_match('/<r_error>(.*?)<\/r_error>/', $response_data, $matches);
                        $first_data_global_gateway_error = $matches[1];
                        
                        // get r_message
                        preg_match('/<r_message>(.*?)<\/r_message>/', $response_data, $matches);
                        $first_data_global_gateway_message = $matches[1];
                        
                        // if transaction failed, prepare error
                        if ($first_data_global_gateway_approved != 'APPROVED') {
                            // if there was a cErrCode value, then use CcReturnMsg for error message
                            if ($first_data_global_gateway_error != '') {
                                $payment_gateway_error_message = $payment_gateway_error_message_prefix . $first_data_global_gateway_error;
                                
                            // else if there was a Text value, then use value for error message
                            } elseif ($first_data_global_gateway_message != '') {
                                $payment_gateway_error_message = $payment_gateway_error_message_prefix . $first_data_global_gateway_message;
                                
                            // else there was no response, so there was a communication problem
                            } else {
                                $payment_gateway_error_message = 'An error occurred while trying to communicate with the payment gateway.';
                                
                                // if there was a cURL error, add error number and error message to payment gateway error message
                                if ($curl_errno) {
                                    $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                }
                            }

                            log_activity($payment_gateway_error_message);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            // send user back to previous screen
                            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        // get transaction id
                        preg_match('/<r_ordernum>(.*?)<\/r_ordernum>/', $response_data, $matches);
                        $transaction_id = $matches[1];
                        
                        // We dont need to get the authorization code
                        $authorization_code = '';
                        
                        break;
                    
                    case 'PayPal Payflow Pro':
                        // if transaction type is set to Authorize, then prepare TRXTYPE to send
                        if (ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE == 'Authorize') {
                            $payflow_pro_TRXTYPE = 'A';
                            
                        // else transaction type is Authorize & Capture, so prepare TRXTYPE to send
                        } else {
                            $payflow_pro_TRXTYPE = 'S';
                        }
                        
                        $transaction_values = array(
                            'PARTNER' => ECOMMERCE_PAYPAL_PAYFLOW_PRO_PARTNER,
                            'VENDOR'  => ECOMMERCE_PAYPAL_PAYFLOW_PRO_MERCHANT_LOGIN,
                            'USER'    => $payment_gateway_user,
                            'PWD'     => ECOMMERCE_PAYPAL_PAYFLOW_PRO_PASSWORD,
                            'TRXTYPE' => $payflow_pro_TRXTYPE,
                            'TENDER'  => 'C',
                            'AMT'     => sprintf("%01.2lf", $total / 100),
                            'CURRENCY' => BASE_CURRENCY_CODE,
                            'TAXAMT'  => sprintf("%01.2lf", $tax / 100),
                            'ACCT'    => $card_number,
                            'EXPDATE' => $expiration_month . mb_substr($expiration_year, -2),
                            'CVV2'    => $liveform->get_field_value('card_verification_number'),
                            'FIRSTNAME' => $billing_first_name,
                            'LASTNAME' => $billing_last_name,
                            'COMPANYNAME' => $billing_company,
                            'EMAIL' =>   $billing_email_address,
                            'STREET'  => $billing_address_1 . ' ' . $billing_address_2,
                            'CITY'    => $billing_city,
                            'STATE'    => $billing_state,
                            'ZIP'     => $billing_zip_code,
                            'BILLTOCOUNTRY' => $billing_country);
                        
                        // process transaction differently based on communication method
                        switch ($payment_gateway_communication_method) {
                            case 'curl':
                                $current_time_parts = gettimeofday();
                                $request_id = md5($current_time_parts['sec'] . '.' . $current_time_parts['usec']);
                                
                                $headers =
                                    array(
                                        'Content-Type: text/namevalue',
                                        'X-VPS-VIT-Client-Certification-Id: 978cd78328cf44d58863f98e863f64dd',
                                        'X-VPS-Request-ID: ' . $request_id
                                    );
                                
                                $post_data = '';
                                
                                foreach ($transaction_values as $name => $value) {
                                    if ($post_data) {
                                        $post_data .= '&';
                                    }
                                    
                                    $value = str_replace('"', '', $value);
                                    
                                    $post_data .= $name . '[' . strlen($value) . ']' . '=' . $value;
                                }
                                
                                $ch = curl_init();
                                
                                curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                
                                // if there is a proxy address, then send cURL request through proxy
                                if (PROXY_ADDRESS != '') {
                                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                    curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                                }
                                
                                $response_data = curl_exec($ch);
                                
                                $curl_errno = curl_errno($ch);
                                $curl_error = curl_error($ch);
                                
                                curl_close($ch);
                                
                                parse_str($response_data, $response);
                                    
                                break;
                                
                            case 'payflow pro com object':
                                $post_data = '';
                                
                                foreach ($transaction_values as $name => $value) {
                                    if ($post_data) {
                                        $post_data .= '&';
                                    }
                                    
                                    $value = str_replace('"', '', $value);
                                    
                                    $post_data .= $name . '[' . strlen($value) . ']' . '=' . $value;
                                }
                                
                                $payflowpro = new COM("PFProCOMControl.PFProCOMControl.1");

                                $context = $payflowpro->CreateContext($payment_gateway_host, 443, 30, '', 0, '', '');
                                $response_data = $payflowpro->SubmitTransaction($context, $post_data, strlen($post_data));

                                $payflowpro->DestroyContext($context);
                                
                                parse_str($response_data, $response);
                                
                                break;
                                
                            case 'pfpro extension':
                                $response = pfpro_process($transaction_values, $payment_gateway_host, 443, 30);
                                
                                break;
                        }

                        // if transaction was unsuccessful, prepare error
                        if (!$response || ($response['RESULT'] != 0)) {
                            // if there was a response from the payment gateway
                            if ($response) {
                                switch ($response['RESULT']) {
                                    case 112:
                                        $payment_gateway_error_message = $payment_gateway_error_message_prefix . 'The billing address that you entered does not match the billing address for this bank card.  Please correct the billing address and try again.  If you continue to receive this message, please try another bank card, or contact us for help.';
                                        break;
                                        
                                    case 114:
                                        $payment_gateway_error_message = $payment_gateway_error_message_prefix . 'The verification number that you entered does not match the verification number for this bank card.  Please correct the verification number and try again.  If you continue to receive this message, please try another bank card, or contact us for help.';
                                        break;
                                        
                                    default:
                                        $payment_gateway_error_message = $payment_gateway_error_message_prefix . $response['RESPMSG'];
                                        break;
                                }
                                
                            // else there was no response, so there was a communication problem
                            } else {
                                $payment_gateway_error_message = 'An error occurred while trying to communicate with the payment gateway.';
                                
                                // if there was a cURL error, add error number and error message to payment gateway error message
                                if ($curl_errno) {
                                    $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                }
                            }

                            log_activity($payment_gateway_error_message);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            // send user back to previous screen
                            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        $transaction_id = $response['PNREF'];
                        $authorization_code = $response['AUTHCODE'];
                        
                        break;
                        
                    case 'PayPal Payments Pro':
                        // if transaction type is set to Authorize, prepare to authorize transaction.
                        if (ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE == 'Authorize') {
                            $paypal_payments_pro_transaction_type = 'Authorization';
                            
                        // else transaction type is Authorize & Capture, prepare to authorize and capture payment.
                        } else {
                            $paypal_payments_pro_transaction_type = 'Sale';
                        }
                        
                        
                        // check if there are shippable items in order
                        $paypal_payments_pro_shipping = false;
                        $query =
                            "SELECT COUNT(*)
                            FROM order_items
                            LEFT JOIN products ON products.id = order_items.product_id
                            WHERE
                                (order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                                AND (products.shippable = '1')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);
                        
                        // if there are shippable items in order, then prepare to send information to PayPal
                        if ($row[0] > 0) {
                            // check how many recipients there are for order
                            $query = "SELECT COUNT(*) FROM ship_tos WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_row($result);
                            
                            // if there is just one recipient, then prepare to send shipping information to PayPal
                            if ($row[0] == 1) {
                                
                                // getting shipping information for recipient
                                $query =
                                    "SELECT
                                        first_name,
                                        last_name,
                                        address_1,
                                        address_2,
                                        city,
                                        state,
                                        country,
                                        zip_code,
                                        phone_number
                                    FROM ship_tos
                                    WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                $row = mysqli_fetch_assoc($result);
                                
                                $first_name = $row['first_name'];
                                $last_name = $row['last_name'];
                                $address_1 = $row['address_1'];
                                $address_2 = $row['address_2'];
                                $city = $row['city'];
                                $state = $row['state'];
                                $country = $row['country'];
                                $zip_code = $row['zip_code'];
                                $phone_number = $row['phone_number'];
                                
                                $shipping_values = array(
                                    'SHIPTONAME' => $first_name . " " . $last_name,
                                    'SHIPTOSTREET' => $address_1,
                                    'SHIPTOCITY' => $city,
                                    'SHIPTOSTATE' => $state,
                                    'SHIPTOCOUNTRYCODE' => $country,
                                    'SHIPTOZIP' => $zip_code,
                                    'SHIPTOSTREET2' => $address_2,
                                    'PHONENUM' => $phone_number);
                                
                                $paypal_payments_pro_shipping = true;
                            }
                        }
                        
                        // Assign values to their names
                        $transaction_values = array(
                            'METHOD' => 'doDirectPayment',
                            'VERSION' => '3.0',
                            'PWD' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_PASSWORD,
                            'USER' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_USERNAME,
                            'SIGNATURE' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_SIGNATURE,
                            'PAYMENTACTION' => $paypal_payments_pro_transaction_type,
                            'BUTTONSOURCE' => 'Camelback_Cart_DP',
                            'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
                            'AMT' => sprintf("%01.2lf", $total / 100),
                            'CREDITCARDTYPE' => $credit_card_type,
                            'ACCT' => $card_number,
                            'EXPDATE' => $expiration_month . $expiration_year,
                            'CVV2' => $liveform->get_field_value('card_verification_number'),
                            'EMAIL' => $billing_email_address,
                            'FIRSTNAME' => $billing_first_name,
                            'LASTNAME' => $billing_last_name,
                            'STREET' => $billing_address_1 . ' ' . $billing_address_2,
                            'CITY' => $billing_city,
                            'STATE' => $billing_state,
                            'ZIP' => $billing_zip_code,
                            'COUNTRYCODE' => $billing_country,
                            'CURRENCYCODE' => BASE_CURRENCY_CODE);
                        
                        // If there is not a gift card discount and not a surcharge, then prepare to send optional details.
                        // We can't send this data if there is a gift card discount or surcharge, because PayPal returns an error:
                        // "The totals of the cart item amounts do not match order amounts."
                        if (($gift_card_discount == 0) && ($surcharge == 0)) {
                            $transaction_values['ITEMAMT'] = sprintf("%01.2lf", ($subtotal - $discount) / 100);
                            $transaction_values['SHIPPINGAMT'] = sprintf("%01.2lf", $shipping / 100);
                            $transaction_values['TAXAMT'] = sprintf("%01.2lf", $tax / 100);
                        }
                        
                        $post_data = '';
                        
                        // Create name value pairs.
                        foreach ($transaction_values as $name => $value) {
                            if ($post_data) {
                                $post_data .= '&';
                            }
                            
                            $value = str_replace('"', '', $value);
                            
                            $post_data .= $name . '=' . urlencode($value);
                        }
                        
                        // if there are shippable items and there are not multiple recipients, create name value pairs for the shipping address.
                        if ($paypal_payments_pro_shipping === true) {
                            foreach ($shipping_values as $name => $value) {
                                if ($post_data) {
                                    $post_data .= '&';
                                }
                                
                                $value = str_replace('"', '', $value);
                                
                                $post_data .= $name . '=' . urlencode($value);
                            }
                        }
                        
                        // Setup cURL options.
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
                        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        
                        // if there is a proxy address, then send cURL request through proxy
                        if (PROXY_ADDRESS != '') {
                            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                            curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                        }
                        
                        $response_data = curl_exec($ch);
                        
                        $curl_errno = curl_errno($ch);
                        $curl_error = curl_error($ch);
                        
                        curl_close($ch);
                        
                        parse_str($response_data, $response);
                        
                        // if transaction was unsuccessful, prepare error
                        if (!$response || (isset($response['L_ERRORCODE0']) == true)) {
                            // if there was a response from the payment gateway
                            if ($response) {
                                $payment_gateway_error_message = $payment_gateway_error_message_prefix . $response['L_LONGMESSAGE0'];
                            
                            // else there was no response, so there was a communication problem
                            } else {
                                $payment_gateway_error_message = 'An error occurred while trying to communicate with the payment gateway.';
                                
                                // if there was a cURL error, add error number and error message to payment gateway error message
                                if ($curl_errno) {
                                    $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                }
                            }

                            log_activity($payment_gateway_error_message);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            // send user back to previous screen
                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        $transaction_id = $response['TRANSACTIONID'];
                        $authorization_code = '';
                        
                        break;
                        
                    case 'Sage':
                        $sage_t_shipping = '';
                        $sage_c_ship_name = '';
                        $sage_c_ship_address = '';
                        $sage_c_ship_city = '';
                        $sage_c_ship_state = '';
                        $sage_c_ship_zip = '';
                        $sage_c_ship_country = '';
                        
                        // determine if there are shippable items
                        $query =
                            "SELECT COUNT(*)
                            FROM order_items
                            LEFT JOIN products ON products.id = order_items.product_id
                            WHERE
                                (order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                                AND (products.shippable = '1')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);
                        
                        // if there are shippable items in order, then determine if there is only one recipient
                        if ($row[0] > 0) {
                            // check how many recipients there are for order
                            $query = "SELECT COUNT(*) FROM ship_tos WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_row($result);
                            
                            // if there is just one recipient, then prepare to send shipping information to Sage
                            if ($row[0] == 1) {
                                $sage_t_shipping = sprintf("%01.2lf", $shipping / 100);
                                
                                // getting shipping information for recipient
                                $query =
                                    "SELECT
                                        first_name,
                                        last_name,
                                        address_1,
                                        address_2,
                                        city,
                                        state,
                                        zip_code,
                                        country
                                    FROM ship_tos
                                    WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                $row = mysqli_fetch_assoc($result);
                                
                                $sage_c_ship_name = $row['first_name'] . ' ' . $row['last_name'];
                                $sage_c_ship_address = $row['address_1'] . ' ' . $row['address_2'];
                                $sage_c_ship_city = $row['city'];
                                $sage_c_ship_state = $row['state'];
                                $sage_c_ship_zip = $row['zip_code'];
                                $sage_c_ship_country = $row['country'];
                            }
                        }

                        // They do not appear to support foreign currencies (at least at the API level),
                        // so we are not passing currency info.
                        
                        $transaction_values = array(
                            'M_id' => ECOMMERCE_SAGE_MERCHANT_ID,
                            'M_key' => ECOMMERCE_SAGE_MERCHANT_KEY,
                            'C_name' => $billing_first_name . ' ' . $billing_last_name,
                            'C_address' => $billing_address_1 . ' ' . $billing_address_2,
                            'C_city' => $billing_city,
                            'C_state' => $billing_state,
                            'C_zip' => $billing_zip_code,
                            'C_country' => $billing_country,
                            'C_email' => $billing_email_address,
                            'C_telephone' => $billing_phone_number,
                            'C_fax' => $billing_fax_number,
                            'C_cardnumber' => $card_number,
                            'C_exp' => $expiration_month . mb_substr($expiration_year, -2), // mmyy
                            'C_cvv' => $liveform->get_field_value('card_verification_number'),
                            'T_amt' => sprintf("%01.2lf", $total / 100),
                            'T_code' => $sage_transaction_type,
                            'T_ordernum' => $reference_code, // we use our reference code instead of order number because we do not know order number yet
                            'T_tax' => sprintf("%01.2lf", $tax / 100),
                            'T_shipping' => $sage_t_shipping,
                            'C_ship_name' => $sage_c_ship_name,
                            'C_ship_address' => $sage_c_ship_address,
                            'C_ship_city' => $sage_c_ship_city,
                            'C_ship_state' => $sage_c_ship_state,
                            'C_ship_zip' => $sage_c_ship_zip,
                            'C_ship_country' => $sage_c_ship_country
                        );
                        
                        $post_data = '';
                        
                        // Create name value pairs.
                        foreach ($transaction_values as $name => $value) {
                            if ($post_data) {
                                $post_data .= '&';
                            }
                            
                            $post_data .= $name . '=' . urlencode($value);
                        }
                        
                        // Setup cURL options.
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'https://gateway.sagepayments.net/cgi-bin/eftBankcard.dll?transaction');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        
                        // if there is a proxy address, then send cURL request through proxy
                        if (PROXY_ADDRESS != '') {
                            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                            curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                        }
                        
                        $response_data = curl_exec($ch);
                        
                        $curl_errno = curl_errno($ch);
                        $curl_error = curl_error($ch);
                        
                        curl_close($ch);
                        
                        // if there was not a response, then there was a communication problem, so prepare error and send user back to previous screen
                        if ($response_data === FALSE) {
                            $payment_gateway_error_message = 'An error occurred while trying to communicate with the payment gateway.';
                            
                            // if there was a cURL error, add error number and error message to payment gateway error message
                            if ($curl_errno) {
                                $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                            }

                            log_activity($payment_gateway_error_message);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            // send user back to previous screen
                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        // the approval indicator is the 2nd character in the response data string
                        $sage_approval_indicator = trim($response_data[1]);
                        
                        // if the transaction was rejected, then prepare error and forward user back to previous screen
                        if ($sage_approval_indicator != 'A') {
                            $sage_code = trim(mb_substr($response_data, 2, 6));
                            $sage_message = trim(mb_substr($response_data, 8, 32));
                        
                            $payment_gateway_error_message = $payment_gateway_error_message_prefix . $sage_message . ' (' . $sage_code . ')';

                            log_activity($payment_gateway_error_message);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            // send user back to previous screen
                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }
                        
                        // set the transaction id to Sage's reference code
                        $transaction_id = trim(mb_substr($response_data, 46, 10));
                        
                        // Sage does not return an authorization code
                        $authorization_code = trim(mb_substr($response_data, 2, 6));
                        
                        break;


                    case 'Stripe':
                        require_once(dirname(__FILE__) . '/stripe/lib/Stripe.php');

                        Stripe::setApiKey(ECOMMERCE_STRIPE_API_KEY);

                        // If transaction type is set to Authorize, then set capture to false.
                        if (ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE == 'Authorize') {
                            $capture = false;
                            
                        // Otherwise transaction type is Authorize & Capture, so set capture to true.
                        } else {
                            $capture = true;
                        }

                        try {
                            $response = Stripe_Charge::create(array(
                                'amount' => $total,
                                'currency' => strtolower(BASE_CURRENCY_CODE),
                                'card' => array(
                                    'number' => $card_number,
                                    'exp_month' => $expiration_month,
                                    'exp_year' => $expiration_year,
                                    'cvc' => $liveform->get_field_value('card_verification_number'),
                                    'name' => $liveform->get_field_value('cardholder'),
                                    'address_line1' => $billing_address_1,
                                    'address_line2' => $billing_address_2,
                                    'address_city' => $billing_city,
                                    'address_zip' => $billing_zip_code,
                                    'address_state' => $billing_state,
                                    'address_country' => $billing_country),
                                'description' => $billing_email_address,
                                'capture' => $capture
                            ));

                            $transaction_id = $response->id;
                            $authorization_code = '';

                        } catch (Exception $e) {
                            $payment_gateway_error_message = $payment_gateway_error_message_prefix . $e->getMessage();

                            log_activity($payment_gateway_error_message, $_SESSION['sessionusername']);
                            
                            $liveform->mark_error('payment_gateway', h($payment_gateway_error_message));

                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
                            exit();
                        }

                        break; 


                    case 'Iyzipay':
                        
						require_once(dirname(__FILE__) . '/iyzipay-php/IyzipayBootstrap.php');
						IyzipayBootstrap::init();

						// if test or live mode for iyzipay gateway. 
						if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
							$payment_gateway_host = 'https://sandbox-api.iyzipay.com';
						}else {
							$payment_gateway_host = 'https://api.iyzipay.com';
						}

						//config
						$options = new \Iyzipay\Options();
						$options->setApiKey(ECOMMERCE_IYZIPAY_API_KEY);
						$options->setSecretKey(ECOMMERCE_IYZIPAY_SECRET_KEY);
						$options->setBaseUrl($payment_gateway_host);
                        
                        if($_GET['mode'] != 'iyzipay_threedsecure_return'){

                            $threedsEnabled = 0;
                            if( (isset($_POST['threedsecure'])) || (ECOMMERCE_IYZIPAY_THREEDS) ) {
                                //Payment 3dsecure
                                $threedsEnabled = 1;
                            } 

						    // if user logged in use user id if not than use guest
						    if(!$user_id){
                                $user_id_output='Guest';
						    }else{
						        $user_id_output = $user_id;
						    }
                        
						    // Conversation ID Digits amount
						    $digits = 9;
						    // Random Conversation ID
						    $conversationid = rand(pow(10, $digits-1), pow(10, $digits)-1);
                        
						    // Cart Security Code
						    $cvc =  $liveform->get_field_value('card_verification_number');
                        
						    // Buyer IP Address
						    $ip = h($_SERVER['REMOTE_ADDR']);
                        
						    if(($custom_field_1) && (is_numeric($custom_field_1))) {
						    	$identitynumber = substr($custom_field_1, 0, 11);// I do set $custom_field_1 to identity number if is number from software billing pages(for no frontend and backend modification)
						    }else{
						    	$identitynumber ='11111111111';//not merged yet.
                            }
                            //card bin number first six digit
                            $card_binNumber = substr($card_number, 0, 6);


						    //Payment Installment
						    //get max accepted installments number
						    $accepted_payment_installments = ECOMMERCE_IYZIPAY_INSTALLMENT;

						    //default payment installment
						    $payment_installment ='1';
						    // get Buyer installment selection
						    $get_buyer_payment_installment_selection = $liveform->get_field_value('installment');
						    if($get_buyer_payment_installment_selection >= 1){
						    	// if buyer send installment value bigger than software accepted installment value, stop action and return error.
						    	if($get_buyer_payment_installment_selection > $accepted_payment_installments){
						    		$liveform->mark_error('payment_gateway', ' Payment Installment is cannot be bigger than software accepted Installment!');
						    		header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
						    		exit();
						    	// so here we use buyer installment selection
						    	}else{
						    		$payment_installment = $get_buyer_payment_installment_selection;
                                
						    		$request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
						    		$request->setLocale(strtoupper(language_ruler()));//get location from sofware language, where set from software settings
						    		$request->setConversationId($conversationid);
						    		$request->setBinNumber($card_binNumber);
						    		$request->setPrice(sprintf("%01.2lf", $total / 100));
						    		$installmentInfo = \Iyzipay\Model\InstallmentInfo::retrieve($request, $options);
						    		$result = $installmentInfo->getRawResult();
                                
						    		if($payment_installment == 1){
						    			$installment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[0]->totalPrice;//1
						    		}else if($payment_installment == 2){
						    			$installment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[1]->totalPrice;//2
						    		}else if($payment_installment == 3){
						    			$installment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[2]->totalPrice;//3
						    		}else if($payment_installment == 6){
						    			$installment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[3]->totalPrice;//6
						    		}else if($payment_installment == 9){
						    			$installment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[4]->totalPrice;//9
						    		}else if($payment_installment == 12){
						    			$installment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[5]->totalPrice;//12
                                    }else{
                                        $payment_installment = 1;
                                    }
						    		$installmentPriceTotal = number_format((float)$installment_totalprice, 2, '.', '');//number change if 103 to 103.00
						    		$installmentPrice_replaced = str_replace(".", "", $installmentPriceTotal);//remove pointer 103.00 to 10300

						    		if( ($installmentPrice_replaced != NULL)&&($installmentPrice_replaced > $total)) {
                                        $total_witout_installment_charge = $total;
						    			$installment_charge = $installmentPrice_replaced - $total;//eg. 10300(total with installment price added) - 10000(total) = 300(installment price )
                                        $total = $total + $installment_charge;
                                    }
                                }
                            }

                            // determine if there are shippable items
                            $query = "SELECT COUNT(*) FROM order_items LEFT JOIN products ON products.id = order_items.product_id WHERE (order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (products.shippable = '1')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_row($result);
						    $Iyzipay_ship_name = '';
                            $Iyzipay_ship_address = '';
                            $Iyzipay_ship_state = '';
                            $Iyzipay_ship_zip = '';
                            $Iyzipay_ship_country = '';
                            // if there are shippable items in order, then determine if there is only one recipient
                            if ($row[0] > 0) {
                                // check how many recipients there are for order
                                $query = "SELECT COUNT(*) FROM ship_tos WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                $row = mysqli_fetch_row($result);

                                // if there is just one recipient, then prepare to send shipping information to iyzipay
                                if ($row[0] == 1) {
                                    // getting shipping information for recipient
                                    $query ="SELECT first_name,last_name,address_1,address_2,city,state,zip_code,country FROM ship_tos WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                    $row = mysqli_fetch_assoc($result);
                                    $Iyzipay_ship_name = $row['first_name'] . ' ' . $row['last_name'];
                                    $Iyzipay_ship_address = $row['address_1'] . ' ' . $row['address_2'] . ' ' . $row['city'];
                                    $Iyzipay_ship_state = $row['state'];
                                    $Iyzipay_ship_zip = $row['zip_code'];
                                    $Iyzipay_ship_country = $row['country'];
                                }
                            }


						    # create request class
						    $request = new \Iyzipay\Request\CreatePaymentRequest();
						    //requests
						    $request->setConversationId($conversationid);
						    $request->setLocale(strtoupper(language_ruler()));//get location from sofware language, where set from software settings
						    $request->setPrice(sprintf("%01.2lf", $subtotal / 100));//$subtotal: total of item prices
						    $request->setPaidPrice(sprintf("%01.2lf", $total / 100));//$total: subtotal - discount + shipping + tax
						    $request->setCurrency(BASE_CURRENCY_CODE);//
						    $request->setInstallment($payment_installment);
						    $request->setBasketId($page_id);//Not Set 
						    $request->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
                            $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
                            $order_id = $_SESSION['ecommerce']['order_id'];
                            $software_token = $_SESSION['software']['token'];
                            $sessionusername = $_SESSION['sessionusername'];
                            $callbackurl =  URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/' . $type_value . '.php?mode=iyzipay_threedsecure_return&page_id=' . $page_id . '&order_id=' . $order_id . '&token=' . $software_token .'&installment_charge=' . $installment_charge . '&type_value=' . $type_value .'&sessionusername=' . $sessionusername . '&contact_id=' . $contact_id . '&surcharge=' . $surcharge . '&discount=' . $discount . '&subtotal=' . $subtotal;
						    if($threedsEnabled == 1){
						    	$request->setCallbackUrl($callbackurl);
                            }

						    //card informations 
						    $paymentCard = new \Iyzipay\Model\PaymentCard();
						    $paymentCard->setCardHolderName($billing_first_name . ' ' . $billing_last_name);
						    $paymentCard->setCardNumber($card_number);
						    $paymentCard->setExpireMonth($expiration_month);
						    $paymentCard->setExpireYear($expiration_year);
						    $paymentCard->setCvc($cvc);
						    $paymentCard->setRegisterCard(0);
						    $request->setPaymentCard($paymentCard);

						    //buyer informations
						    $buyer = new \Iyzipay\Model\Buyer();
						    $buyer->setId($user_id_output);
						    $buyer->setName($billing_first_name);
						    $buyer->setSurname($billing_last_name);
						    $buyer->setGsmNumber($billing_phone_number);
						    $buyer->setEmail($billing_email_address);
						    $buyer->setIdentityNumber($identitynumber);
						    $buyer->setRegistrationAddress($billing_address_1 . ' ' . $billing_address_2 . ' ' . $billing_city);
						    $buyer->setIp($ip);
						    $buyer->setCity($billing_state);
						    $buyer->setCountry($billing_country);
						    $buyer->setZipCode($billing_zip_code);
						    $request->setBuyer($buyer);

						    //shipping informations
						    $shippingAddress = new \Iyzipay\Model\Address();
						    $shippingAddress->setContactName($Iyzipay_ship_name);
						    $shippingAddress->setCity($Iyzipay_ship_state);
						    $shippingAddress->setCountry($Iyzipay_ship_country);
						    $shippingAddress->setAddress($Iyzipay_ship_address);
						    $shippingAddress->setZipCode($Iyzipay_ship_zip);
						    $request->setShippingAddress($shippingAddress);

						    //billing informations
						    $billingAddress = new \Iyzipay\Model\Address();
						    $billingAddress->setContactName($billing_first_name . ' ' . $billing_last_name);
						    $billingAddress->setCity($billing_state);
						    $billingAddress->setCountry($billing_country);
						    $billingAddress->setAddress($billing_address_1 . ' ' . $billing_address_2 . ' ' . $billing_city);
						    $billingAddress->setZipCode($billing_zip_code);
						    $request->setBillingAddress($billingAddress);
                        
						    //Shopping Basket Items
						    $basketItems = array();//Items
						    $i = 0;
						    // loop through non-recurring order items in order to send order item details to Iyzipay
						    foreach ($products as $product) {
                                // fix basketItemPrice  cant be zero error from Iyzipay
                                //we dont add this item to send iyzipay
                                if($product['price'] > 0){
						    	    //Proparing Product short description as product name. because product name is ID.
						    	    //product quantity is added to name because iyzico do not accept basket item quantity number.
						    	    if($product['quantity'] > 1){
						    	    	$product_name =	$product['quantity'] . ' Qty/Adet - ' . $product['short_description'];
						    	    }else{
						    	    	$product_name =	$product['short_description'];
						    	    }
						    	    $item = BasketItem . $i;
						    	    $item = new \Iyzipay\Model\BasketItem();//Item
						    	    $item->setId($product['product_name']);
						    	    $item->setName($product_name);
						    	    $item->setPrice(sprintf("%01.2lf", $product['price'] / 100 * $product['quantity']));
						    	    //if this order item is shipable than this is PHYSICAL and else VIRTUAL.
						    	    if($product['shippable']){
						    	    	$item->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
						    	    }else{
						    	    	$item->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
						    	    }
						    	    $item->setCategory1($product['short_description']);//Not Set
						    	    $basketItems[$i] = $item;//Item End
						    	    $i++;
                                }
						    }
						    $request->setBasketItems($basketItems);//Items End

						    if($threedsEnabled == 1){

						    	# make request with 3ds
						    	$threedsInitialize = \Iyzipay\Model\ThreedsInitialize::create($request, $options);

						    	//Get responses
						    	$status = ($threedsInitialize->getStatus()); 
						    	$error_message = ($threedsInitialize->getErrorMessage());
                                $threeDSHtmlContent = ($threedsInitialize->getHtmlContent());
						    	//if payment is not success
						    	if($status != 'success'){
						    		$liveform->mark_error('payment_gateway',$error_message . '!');
						    	    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
						    	    exit();
						    	}else{
                                    // there is no problem, so we can start 3dsecure authentication.
                                    echo $threeDSHtmlContent;
                                    log_activity('3D Secure Payment started by (' . $billing_first_name . ' ' . $billing_last_name . ', ' . $billing_email_address . ').');
						    		exit();
						    	}

						    }else{
						    	# make request with payment
						    	//return success or failure if success submit order complete and if not go back page.
						    	$payment = \Iyzipay\Model\Payment::create($request, $options);

						    	//Get responses
						    	$status = ($payment->getStatus()); 
						    	$error_code = ($payment->getErrorCode());
						    	$error_message = ($payment->getErrorMessage());
						    	$Payment_ID = ($payment->getPaymentId());
						    	$Installment= ($payment->getInstallment());
                            
						    	$card_type= ($payment->getCardAssociation());
						    	$binNumber= ($payment->getbinNumber());
						    	$lastFourDigits= ($payment->getlastFourDigits());
						    	$cardFamily= ($payment->getcardFamily());
						    	$card_number = protect_credit_card_number($card_number);


						    	$transaction_id = $Payment_ID;  

						    	// if value is 0 payment must be checked by iyzico before accepted . 
						    	//if value is -1 risky not accepted payment. 
						    	//if value is 1 this is no risk so accept directly.
						    	$FraudStatus= ($payment->getFraudStatus());
                            
						    	//if payment is not success
						    	if($status != 'success'){
						    		//Get back to order preview page and show an error
						    		$liveform->mark_error('payment_gateway',$error_message . '!');
						    	    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
						    	    exit();
						    	}
                            }
                        
                        }else{

		                    # create request class
		                    $request = new \Iyzipay\Request\CreateThreedsPaymentRequest();
		                    $request->setLocale(strtoupper(language_ruler()));//get location from sofware language, where set from software settings
		                    $request->setConversationId($_POST["conversationId"]);
		                    $request->setPaymentId($_POST["paymentId"]);
		                    $request->setConversationData($_POST["conversationData"]);
		                    # make request
		                    $threedsPayment = \Iyzipay\Model\ThreedsPayment::create($request, $options);
		                    //Get responses
		                    $status = ($threedsPayment->getStatus()); 
		                    $error_code = ($threedsPayment->getErrorCode());
		                    $error_message = ($threedsPayment->getErrorMessage());
		                    $Payment_ID = ($threedsPayment->getPaymentId());
		                    $Installment= ($threedsPayment->getInstallment());
		                    $BasketId= ($threedsPayment->getbasketId());
		                    $card_type = ($threedsPayment->getCardAssociation());
		                    $binNumber= ($threedsPayment->getbinNumber());
		                    $lastFourDigits= ($threedsPayment->getlastFourDigits());
		                    $cardFamily= ($threedsPayment->getcardFamily());
		                    $card_number = $binNumber . '******' . $lastFourDigits;
		                    $paidPrice = ($threedsPayment->getPaidPrice());
		                    // if value is 0 payment must be checked by iyzico before accepted . 
		                    //if value is -1 risky not accepted payment. 
		                    //if value is 1 this is no risk so accept directly.
		                    $FraudStatus= ($threedsPayment->getFraudStatus());
                            $payment_installment = $Installment;
                            $installment_charge = $_GET['installment_charge'];
                            $transaction_id = $_POST["paymentId"];
                            $discount = $_GET['discount'];
                            $subtotal = $_GET['subtotal'];

                            if($discount > 0){
                                $total = $subtotal - $discount;
                            }
                            if($installment_charge > 0){
                                $total_witout_installment_charge = $total;
                                $total = $total + $installment_charge;
                            }
                            
                           
                            

		                    if($error_code != '5002'){
		                    	//if payment is not success
		                    	if($status != 'success'){
                                    $liveform->mark_error('payment_gateway', $error_message);
                                    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
		                    		exit();
		                    	}
                            
		                    }else{
		                    	$liveform->mark_error('payment_gateway', $error_message);
                                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id));
		                    	exit();
		                    }
                            log_activity('3D Secure Payment ended by (' . $billing_first_name . ' ' . $billing_last_name . ', ' . $billing_email_address . ').');
                       
                        }
                        break;
                }
                
                break;
            
            // if PayPal Express Checkout payment method was selected, process transaction through PayPal
            case 'PayPal Express Checkout':
                if (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_MODE == 'sandbox') {
                    $paypal_express_checkout_host = 'https://api-3t.sandbox.paypal.com/nvp';
                } else {
                    $paypal_express_checkout_host = 'https://api-3t.paypal.com/nvp';
                }
                
                // if transaction type is set to Authorize, then prepare paymentaction to send
                if (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_TRANSACTION_TYPE == 'Authorize') {
                    $paypal_express_checkout_paymentaction = 'Authorization';
                    
                // else transaction type is Authorize & Capture, so prepare paymentaction to send
                } else {
                    $paypal_express_checkout_paymentaction = 'Sale';
                }

                $paypal_express_checkout_shipping = '';

                // check if there are shippable order items in order
                $query =
                    "SELECT COUNT(*)
                    FROM order_items
                    LEFT JOIN products ON products.id = order_items.product_id
                    WHERE
                        (order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                        AND (products.shippable = '1')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_row($result);
                
                // if there are shippable order items in order, then prepare to send information to PayPal
                if ($row[0] > 0) {
                    // check how many recipients there are for order
                    $query = "SELECT COUNT(*) FROM ship_tos WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_row($result);
                    
                    // if there is just one recipient, then prepare to send shipping information to PayPal
                    if ($row[0] == 1) {
                        // getting shipping information for recipient
                        $query =
                            "SELECT
                                ship_to_name,
                                first_name,
                                last_name,
                                address_1,
                                address_2,
                                city,
                                state,
                                country,
                                zip_code,
                                phone_number
                            FROM ship_tos
                            WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        
                        $ship_to_name = $row['ship_to_name'];
                        
                        // if recipient is myself (i.e. single-recipient or myself under multi-recipient), then send shipping information to PayPal
                        if ($ship_to_name == 'myself') {
                            $first_name = $row['first_name'];
                            $last_name = $row['last_name'];
                            $address_1 = $row['address_1'];
                            $address_2 = $row['address_2'];
                            $city = $row['city'];
                            $state = $row['state'];
                            $country = $row['country'];
                            $zip_code = $row['zip_code'];
                            $phone_number = $row['phone_number'];
                            
                            $paypal_express_checkout_noshipping = '0';
                            $paypal_express_checkout_addroverride = '1';
                            
                            $paypal_express_checkout_shipping =
                                'PAYMENTREQUEST_0_SHIPTONAME=' . urlencode($first_name) . ' ' . urlencode($last_name) . '&' .
                                'PAYMENTREQUEST_0_SHIPTOSTREET=' . urlencode($address_1) . '&' .
                                'PAYMENTREQUEST_0_SHIPTOCITY=' . urlencode($city) . '&' .
                                'PAYMENTREQUEST_0_SHIPTOSTATE=' . urlencode($state) . '&' .
                                'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=' . urlencode($country) . '&' .
                                'PAYMENTREQUEST_0_SHIPTOZIP=' . urlencode($zip_code) . '&' .
                                'PAYMENTREQUEST_0_SHIPTOSTREET2=' . urlencode($address_2) . '&' .
                                'PAYMENTREQUEST_0_PHONENUM=' . urlencode($phone_number) . '&';
                        
                        // else the recipient is not myself, so don't send shipping information to PayPal
                        // we don't want to send shipping information to PayPal under this condition,
                        // because PayPal has a bug where it will prefill the PayPal sign up form with this shipping information,
                        // which will cause confusion if the recipient is not myself
                        } else {
                            $paypal_express_checkout_noshipping = '1';
                            $paypal_express_checkout_addroverride = '0';
                            $paypal_express_checkout_shipping = '';
                        }
                        
                    // else there is more than one recipient, so suppress shipping at PayPal and prepare to not send shipping information to PayPal
                    } else {
                        $paypal_express_checkout_noshipping = '1';
                        $paypal_express_checkout_addroverride = '0';
                        $paypal_express_checkout_shipping = '';
                    }
                    
                // else there are no shippable order items in order, so suppress shipping at PayPal
                } else {
                    $paypal_express_checkout_noshipping = '1';
                    $paypal_express_checkout_addroverride = '0';
                    $paypal_express_checkout_shipping = '';
                }

                $paypal_express_checkout_order_details = '';
                
                // if there is not a gift card discount and not a surcharge,
                // then prepare order details so additional information about order totals and line items appears at PayPal
                // we can't send this data if there is a gift card discount or surcharge,
                // because PayPal returns an error: The totals of the cart item amounts do not match order amounts.
                // there is a chance we could add the gift card discount as a line item with a negative amount (i.e. similar to the order discount),
                // however we have decided not to do that for now because we don't know if that would work and it has not been tested
                // For example, what if the gift card discounts more than the whole subtotal
                if (($gift_card_discount == 0) && ($surcharge == 0)) {
                    $paypal_express_checkout_order_details .=
                        'PAYMENTREQUEST_0_ITEMAMT=' . sprintf("%01.2lf", ($subtotal - $discount) / 100) . '&' .
                        'PAYMENTREQUEST_0_SHIPPINGAMT=' . sprintf("%01.2lf", $shipping / 100) . '&' .
                        'PAYMENTREQUEST_0_TAXAMT=' . sprintf("%01.2lf", $tax / 100) . '&';

                    // loop through non-recurring order items in order to send order item details to PayPal Express Checkout
                    foreach ($nonrecurring_products as $line_item_number => $product) {
                        $paypal_express_checkout_order_details .=
                            'L_PAYMENTREQUEST_0_NAME' . $line_item_number . '=' . urlencode($product['short_description']) . '&' .
                            'L_PAYMENTREQUEST_0_NUMBER' . $line_item_number . '=' . urlencode($product['product_name']) . '&' .
                            'L_PAYMENTREQUEST_0_AMT' . $line_item_number . '=' . sprintf("%01.2lf", $product['price'] / 100) . '&' .
                            'L_PAYMENTREQUEST_0_QTY' . $line_item_number . '=' . $product['quantity'] . '&' .
                            'L_PAYMENTREQUEST_0_TAXAMT' . $line_item_number . '=' . sprintf("%01.2lf", $product['tax'] / 100) . '&';
                    }

                    // if there is an order discount, then add line item for discount
                    if ($discount) {
                        $line_item_number += 1;

                        $tax_discount = $original_tax - $tax;

                        $paypal_express_checkout_order_details .=
                            'L_PAYMENTREQUEST_0_NAME' . $line_item_number . '=' . urlencode('Discount') . '&' .
                            'L_PAYMENTREQUEST_0_AMT' . $line_item_number . '=-' . sprintf("%01.2lf", $discount / 100) . '&' .
                            'L_PAYMENTREQUEST_0_QTY' . $line_item_number . '=1&' .
                            'L_PAYMENTREQUEST_0_TAXAMT' . $line_item_number . '=-' . sprintf("%01.2lf", $tax_discount / 100) . '&';
                    }
                }
                
                // if mode is not paypal_express_checkout_return, then prepare to send SetExpressCheckout request to PayPal
                if ($_GET['mode'] != 'paypal_express_checkout_return') {
                    $paypal_express_checkout_returnurl = URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/' . $type_value . '.php?mode=paypal_express_checkout_return&page_id=' . $page_id;
                    $paypal_express_checkout_cancelurl = URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id);
                    
                    $post_data =
                        'METHOD=SetExpressCheckout&' .
                        'VERSION=85.0&' .
                        'USER=' . urlencode(ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_USERNAME) . '&' .
                        'PWD=' . urlencode(ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_PASSWORD) . '&' .
                        'SIGNATURE=' . urlencode(ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_SIGNATURE) . '&' .
                        'SOLUTIONTYPE=Sole&' . // this parameter and the one below tell PayPal to allow the customer to pay by credit/debit card without having a PayPal account
                        'LANDINGPAGE=Billing&' .
                        'PAYMENTREQUEST_0_PAYMENTACTION=' . $paypal_express_checkout_paymentaction . '&' .
                        'PAYMENTREQUEST_0_AMT=' . sprintf("%01.2lf", $total / 100) . '&' .
                        'PAYMENTREQUEST_0_CURRENCYCODE=' . BASE_CURRENCY_CODE . '&' .
                        'RETURNURL=' . urlencode($paypal_express_checkout_returnurl) . '&' .
                        'CANCELURL=' . urlencode($paypal_express_checkout_cancelurl) . '&' .
                        'NOSHIPPING=' . $paypal_express_checkout_noshipping . '&' .
                        'ADDROVERRIDE=' . $paypal_express_checkout_addroverride . '&' .
                        $paypal_express_checkout_shipping .
                        $paypal_express_checkout_order_details .
                        'EMAIL=' . urlencode($billing_email_address);
                    
                // else mode is paypal_express_checkout_return, so prepare to send DoExpressCheckoutPayment request to PayPal
                } else {

                    $post_data =
                        'METHOD=DoExpressCheckoutPayment&' .
                        'VERSION=85.0&' .
                        'USER=' . urlencode(ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_USERNAME) . '&' .
                        'PWD=' . urlencode(ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_PASSWORD) . '&' .
                        'SIGNATURE=' . urlencode(ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_SIGNATURE) . '&' .
                        'TOKEN=' . urlencode($_GET['token']) . '&' .
                        'PAYERID=' . urlencode($_GET['PayerID']) . '&' .
                        'PAYMENTREQUEST_0_PAYMENTACTION=' . $paypal_express_checkout_paymentaction . '&' .
                        'BUTTONSOURCE=Camelback_Cart_EC&' .
                        $paypal_express_checkout_shipping .
                        $paypal_express_checkout_order_details .
                        'PAYMENTREQUEST_0_AMT=' . sprintf("%01.2lf", $total / 100) . '&' .
                        'PAYMENTREQUEST_0_CURRENCYCODE=' . BASE_CURRENCY_CODE;
                }
                
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, $paypal_express_checkout_host);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                
                // if there is a proxy address, then send cURL request through proxy
                if (PROXY_ADDRESS != '') {
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                }
                
                $response_data = curl_exec($ch);
                
                $curl_errno = curl_errno($ch);
                $curl_error = curl_error($ch);
                
                curl_close($ch);
                
                parse_str($response_data, $response);
                
                // if transaction was unsuccessful, prepare error
                if (!$response || (isset($response['L_ERRORCODE0']) == true)) {
                    // if there was a response from PayPal
                    if ($response) {
                        $paypal_express_checkout_error_message = 'Error message from PayPal: ' . $response['L_LONGMESSAGE0'];
                        
                    // else there was no response, so there was a communication problem
                    } else {
                        $paypal_express_checkout_error_message = 'An error occurred while trying to communicate with PayPal.';
                        
                        // if there was a cURL error, add error number and error message to PayPal error message
                        if ($curl_errno) {
                            $paypal_express_checkout_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                        }
                    }

                    log_activity($paypal_express_checkout_error_message);
                    
                    $liveform->mark_error('paypal_express_checkout', h($paypal_express_checkout_error_message));

                    // send user back to previous screen
                    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($page_id));
                    exit();
                }
                
                // if mode is not paypal_express_checkout_return, then send user to PayPal
                if ($_GET['mode'] != 'paypal_express_checkout_return') {
                    if (ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_MODE == 'sandbox') {
                        $paypal_express_checkout_url = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&useraction=commit&token=';
                    } else {
                        $paypal_express_checkout_url = 'https://www.paypal.com/webscr&cmd=_express-checkout&useraction=commit&token=';
                    }
                    
                    // send user to PayPal
                    header('Location: ' . $paypal_express_checkout_url . $response['TOKEN']);
                    exit();
                    
                // else mode is paypal_express_checkout_return, so get transaction id
                } else {
                    $transaction_id = $response['PAYMENTINFO_0_TRANSACTIONID'];
                }
                
                break;
        }
    }

    $sql_special_offer_code = '';

    // If a special offer code was added to this order, then check if code is a key code that might
    // require various updates.

    if ($special_offer_code != '') {

        $key_code = db_item(
            "SELECT
                id,
                offer_code,
                single_use,
                report
            FROM key_codes
            WHERE code = '" . e($special_offer_code) . "'");

        // If a key code was found then continue to check.
        if ($key_code) {

            // If key code is single-use, then disable key code.
            if ($key_code['single_use']) {
                db(
                    "UPDATE key_codes
                    SET
                        enabled = '0',
                        user = '" . USER_ID . "',
                        timestamp = UNIX_TIMESTAMP()
                    WHERE id = '" . $key_code['id'] . "'");
            }

            // If key code report is set to offer code, then store offer code in order.
            if ($key_code['report'] == 'offer_code') {
                $sql_special_offer_code = "special_offer_code = '" . e($key_code['offer_code']) . "',";
            }
        }
    }
    
    // if gift cards are enabled, then check if applied gift cards are still valid and refresh balances
    if (ECOMMERCE_GIFT_CARD == TRUE) {
        // assume that there is not a gift card error until we find out otherwise
        $gift_card_error = FALSE;
        
        // set the amount that needs to be redeemed from all gift cards
        $required_redemption_amount = $gift_card_discount;
        
        // initialize a variable for tracking the amount that has been redeemed so far
        $current_redemption_amount = 0;
        
        // create array for storing errors so that we can e-mail an administrator about them if necessary
        // errors normally won't occur, because we have already checked balances earlier in this script, but a network issue or etc. is possible
        // we will accept the order even if there is an error, because it is too difficult to backtrack (e.g. a payment gateway transaction might have already been submitted)
        $gift_card_errors = array();
        
        // loop through the applied gift cards in order to redeem them or remove them if they are not necessary
        foreach ($applied_gift_cards as $applied_gift_card) {
            // if the current redemption amount is less than the required redemption amount, then we have not redeemed enough yet, so redeem this gift card
            if ($current_redemption_amount < $required_redemption_amount) {
                $remaining_redemption_amount = $required_redemption_amount - $current_redemption_amount;
                
                // if the balance of this applied gift card is less than or equal to the remaining redemption amount, then redeem the full balance of the gift card
                if ($applied_gift_card['old_balance'] <= $remaining_redemption_amount) {
                    $amount = $applied_gift_card['old_balance'];
                    
                // else the balance of this applied gift card is greater than the remaining redemption amount, so just redeem the remaining redemption amount
                } else {
                    $amount = $remaining_redemption_amount;
                }

                // If this is a gift card in this system (i.e. not givex), then reduce balance of gift card.
                if ($applied_gift_card['givex'] == 0) {
                    // Check for a gift card in this system.
                    $gift_card = db_item(
                        "SELECT
                            id,
                            balance
                        FROM gift_cards
                        WHERE id = '" . $applied_gift_card['gift_card_id']  . "'");

                    // If a gift card could not be found or if there is not enough balance, then add error
                    // and remove applied gift card from order.
                    // This just protects against a race condition where someone might try to submit multiple
                    // orders at the same moment to get fraudulent value from the gift card.
                    if (($gift_card['id'] == '') || ($gift_card['balance'] < $amount)) {
                        $gift_card_errors[] = array(
                            'gift_card_code' => $applied_gift_card['code'],
                            'amount' => $amount,
                            'error_message' => 'Gift card was deleted or balance of card changed right when order was submitted.',
                            'givex' => false
                        );

                        db("DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'");

                    // Otherwise we should be able to redeem the gift card successfully,
                    // so reduce balance, update applied gift card, and update the current redemption amount.
                    } else {
                        db(
                            "UPDATE gift_cards
                            SET
                                balance = balance - " . $amount . ",
                                last_modified_user_id = '" . USER_ID . "',
                                last_modified_timestamp = UNIX_TIMESTAMP()
                            WHERE id = '" . $gift_card['id'] . "'");

                        $new_balance = db_value("SELECT balance FROM gift_cards WHERE id = '" . $gift_card['id'] . "'");

                        db(
                            "UPDATE applied_gift_cards
                            SET
                                amount = '" . $amount . "',
                                new_balance = '" . $new_balance . "'
                            WHERE id = '" . $applied_gift_card['id'] . "'");
                        
                        $current_redemption_amount = $current_redemption_amount + $amount;
                    }

                // Otherwise this is a givex gift card, so check if gift card still exists
                // and if balance is good.
                } else {
                    // send a redemption request to Givex
                    $result = send_givex_request('redemption', $applied_gift_card['code'], $amount / 100);
                    
                    // if there was an error then add error to errors array and remove gift card from order
                    if ((isset($result['curl_errno']) == TRUE) || (isset($result['error_message']) == TRUE)) {
                        // if there was a curl error, then store certain values in array
                        if (isset($result['curl_errno']) == TRUE) {
                            $gift_card_errors[] = array(
                                'gift_card_code' => $applied_gift_card['code'],
                                'amount' => $amount,
                                'curl_errno' => $result['curl_errno'],
                                'curl_error' => $result['curl_error'],
                                'givex' => true
                            );
                            
                        // else there was a Givex error, so store different values in array
                        } else {
                            $gift_card_errors[] = array(
                                'gift_card_code' => $applied_gift_card['code'],
                                'amount' => $amount,
                                'error_message' => $result['error_message'],
                                'givex' => true
                            );
                        }
                        
                        // remove applied gift card from database
                        $query = "DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                    // else the redemption was successful, so update applied gift card and update the current redemption amount
                    } else {
                        $query =
                            "UPDATE applied_gift_cards
                            SET
                                amount = '" . $amount . "',
                                new_balance = '" . escape($result['balance'] * 100) . "',
                                authorization_number = '" . escape($result['authorization_number']) . "'
                            WHERE id = '" . $applied_gift_card['id'] . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        $current_redemption_amount = $current_redemption_amount + $amount;
                    }
                }
                
            // else the current redemption amount is greater than or equal to the required redemption amount, so remove this gift card from the order,
            // because no more gift cards need to be redeemed
            } else {
                // remove applied gift card from database
                $query = "DELETE FROM applied_gift_cards WHERE id = '" . escape($applied_gift_card['id']) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
        
        // if the total amount that was redeemed is less than the required redemption amount, then not enough was redeemed because of an error,
        // so remember that so that we can later e-mail an administrator and log activity
        // we can't e-mail the administrator or log activity yet because we don't know the order number yet
        if ($current_redemption_amount < $required_redemption_amount) {
            $gift_card_error = TRUE;
        }
    }
    
    // set order date
    $order_date = time();

    // Unset any existing auto-registration info, from a previous order.  This is important because
    // a user might be submitting multiple orders on behalf of multiple customers, and we don't
    // want the auto-registration info from a previous order to appear on the receipt for a
    // different order/customer.
    unset($_SESSION['software']['auto_registration']);

    // Prepare to remember whether a new user was created or an existing user was found for
    // auto-registration, so later we can create address book items for the user.  We don't create
    // address book items now, because it is more efficient to do it later, when we get recipient
    // data to update ship date, delivery date, and etc.
    $auto_registration_type = '';

    // If auto-registration is enabled and the user is not logged in or is ghosting, then deal with
    // auto-registration.
    if ($auto_registration and (!USER_LOGGED_IN or $ghost)) {

        // Check if user exists for billing email address.
        // If the user is found, then this order will be connected to the user further below.
        $user_id = db_value("SELECT user_id FROM user WHERE user_email = '" . e($billing_email_address) . "'");

        // If a user does not exist, then create user.
        if (!$user_id) {

            // Create a username by using everything before "@" in the email address,
            // and, if necessary, add numbers to the end to make it unique.
            $username = strtok($billing_email_address, '@');
            $username = get_unique_username($username);

            $random_password = get_random_string(array(
                'type' => 'lowercase_letters',
                'length' => 10));

            db(
                "INSERT INTO user (
                    user_username,
                    user_email,
                    user_password,
                    user_role,
                    user_contact,
                    user_timestamp)
                VALUES (
                    '" . e($username) . "',
                    '" . e($billing_email_address) . "',
                    '" . md5($random_password) . "',
                    '3',
                    '" . $contact_id . "',
                    UNIX_TIMESTAMP())");

            // Get the new user id which we will use to connect this order
            // to the new user further below.
            $user_id = mysqli_insert_id(db::$con);

            // Remember email and password in the session, so we can show it on the order receipt.
            $_SESSION['software']['auto_registration']['email_address'] = $billing_email_address;
            $_SESSION['software']['auto_registration']['password'] = $random_password;

            // If there is a contact for this user, and there is a registration contact group,
            // then add contact to registration contact group.  There should probably always
            // be a contact, but we just add that check to make sure, in case there is some
            // situation where there is not one.
            if (
                ($contact_id)
                && (REGISTRATION_CONTACT_GROUP_ID)
                && (db_value("SELECT id FROM contact_groups WHERE id = '" . REGISTRATION_CONTACT_GROUP_ID  . "'"))
            ) {
                db(
                    "INSERT INTO contacts_contact_groups_xref (
                        contact_id,
                        contact_group_id)
                    VALUES (
                        '" . $contact_id . "',
                        '" . REGISTRATION_CONTACT_GROUP_ID . "')");
            }

            // If this user is not ghosting, then auto-login user.  We don't want to auto-login the
            // user if they are ghosting, because they probably want to remain logged in to their
            // main account.
            if (!$ghost) {

                $_SESSION['sessionusername'] = $username;
                $_SESSION['sessionpassword'] = md5($random_password);

                log_activity(
                    'user was auto-logged in by order auto-registration',
                    $_SESSION['sessionusername']);
            }

            // Remember that a new user was created, so later we can create address book items for
            // the new user.
            $auto_registration_type = 'new';

        // Otherwise a user was found, so just take note, so later we can create address book items
        // for the existing user.
        } else {
            $auto_registration_type = 'existing';
        }
    }

    /* begin: calculate commission for affiliate program */
    
    $affiliate_code = get_affiliate_code();
    
    // if affiliate program is on and there is an affiliate code, then determine if commission record and recurring commission profiles should be created
    if ((AFFILIATE_PROGRAM == true) && ($affiliate_code != '')) {
        // get affiliate information from contact
        $query = "SELECT affiliate_commission_rate FROM contacts WHERE (affiliate_code = '" . escape($affiliate_code) . "') AND (affiliate_approved = 1)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if a valid affiliate contact was found, continue with calculating commission
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $affiliate_commission_rate = $row['affiliate_commission_rate'];
            
            // if affiliate commission rate is 0, then use default commission rate
            if ($affiliate_commission_rate == 0) {
                $affiliate_commission_rate = AFFILIATE_DEFAULT_COMMISSION_RATE;
            }
            
            // if there is an order discount, calculate discount percentage
            if ($discount) {
                $discount_percentage = $discount / $subtotal;
            }
            
            // if there is a gift card discount, calculate gift card discount percentage
            if ($gift_card_discount != 0) {
                $gift_card_discount_percentage = $gift_card_discount / ($total_without_surcharge + $gift_card_discount);
            }
            
            // loop through all nonrecurring products in order
            foreach ($nonrecurring_products as $product) {
                if ($product['commissionable'] == 1) {
                    // if there is a commission rate limit set for the product
                    // and the commission rate limit is less than the affiliate commission rate
                    // use commission rate limit for product
                    if (($product['commission_rate_limit'] != 0) && ($product['commission_rate_limit'] < $affiliate_commission_rate)) {
                        $commission_rate = $product['commission_rate_limit'];
                        
                    // else use affiliate commission rate
                    } else {
                        $commission_rate = $affiliate_commission_rate;
                    }
                    
                    $product_commission = $product['quantity'] * $product['price'] * ($commission_rate / 100);
                    
                    // if there is an order discount, apply discount to product commission
                    if ($discount) {
                        $product_commission = $product_commission - ($product_commission * $discount_percentage);
                    }
                    
                    // if there is a gift card discount, apply gift card discount to product commission
                    if ($gift_card_discount != 0) {
                        $product_commission = $product_commission - ($product_commission * $gift_card_discount_percentage);
                    }
                    
                    // add product commission to total commission
                    $commission += $product_commission;
                }
            }
            
            $commission = round($commission);
            
            // if there is a commission, then add commission record
            if ($commission > 0) {
                $query =
                    "INSERT INTO commissions (
                        reference_code,
                        affiliate_code,
                        order_id,
                        amount,
                        status,
                        created_user_id,
                        created_timestamp,
                        last_modified_user_id,
                        last_modified_timestamp)
                    VALUES (
                        '" . generate_commission_reference_code() . "',
                        '" . escape($affiliate_code) . "',
                        '" . $_SESSION['ecommerce']['order_id'] . "',
                        '" . $commission . "',
                        'pending',
                        '" . $user_id . "',
                        UNIX_TIMESTAMP(),
                        '" . $user_id . "',
                        UNIX_TIMESTAMP())";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
            
            // loop through recurring products in order to create recurring commission profiles
            foreach ($recurring_products as $product) {
                // if the product is commissionable and the payment period is monthly or yearly then continue to check if recurring commission profile should be created
                if (
                    ($product['commissionable'] == 1)
                    &&
                    (
                        ($product['recurring_payment_period'] == 'Monthly')
                        || ($product['recurring_payment_period'] == 'Yearly')
                    )
                ) {
                    // if there is a commission rate limit set for the product
                    // and the commission rate limit is less than the affiliate commission rate
                    // use commission rate limit for product
                    if (($product['commission_rate_limit'] != 0) && ($product['commission_rate_limit'] < $affiliate_commission_rate)) {
                        $commission_rate = $product['commission_rate_limit'];
                        
                    // else use affiliate commission rate
                    } else {
                        $commission_rate = $affiliate_commission_rate;
                    }
                    
                    $product_commission = $product['quantity'] * $product['price'] * ($commission_rate / 100);
                    
                    $product_commission = round($product_commission);
                    
                    // if the recurring start date for the order item is less than or equal to today,
                    // then the order item was included in non-recurring transaction, so determine actual start date for profile
                    if ($product['recurring_start_date'] <= date('Y-m-d')) {
                        switch ($product['recurring_payment_period']) {
                            case 'Monthly':
                                $start_date = date('Y-m-d', time() + 86400 * 30);
                                break;
                            
                            case 'Yearly':
                                $start_date = date('Y-m-d', time() + 86400 * 365);
                                break;
                        }
                        
                    // else the recurring start date is greater than today, so figure out start date from start value
                    } else {
                        $start_date = date('Y-m-d', strtotime($product['recurring_start_date']));
                    }
                    
                    // create recurring commission profile
                    $query =
                        "INSERT INTO recurring_commission_profiles (
                            affiliate_code,
                            order_id,
                            order_item_id,
                            amount,
                            enabled,
                            start_date,
                            period,
                            number_of_commissions,
                            product_name,
                            product_short_description,
                            created_user_id,
                            created_timestamp,
                            last_modified_user_id,
                            last_modified_timestamp)
                        VALUES (
                            '" . escape($affiliate_code) . "',
                            '" . $_SESSION['ecommerce']['order_id'] . "',
                            '" . $product['order_item_id'] . "',
                            '" . $product_commission . "',
                            '1',
                            '" . $start_date . "',
                            '" . mb_strtolower($product['recurring_payment_period']) . "',
                            '" . $product['recurring_number_of_payments'] . "',
                            '" . escape($product['product_name']) . "',
                            '" . escape($product['short_description']) . "',
                            '" . $user_id . "',
                            UNIX_TIMESTAMP(),
                            '" . $user_id . "',
                            UNIX_TIMESTAMP())";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
        }
    }

    /* end: calculate commission for affiliate program */
    
    // if Credit/Debit Card payment method was selected, then prepare to update fields in order table for Credit/Debit Card
    if ($liveform->get_field_value('payment_method') == 'Credit/Debit Card') {
        // if a transaction was processed through a payment gateway, then protect credit card number and card verification number
        if ($transaction_id != '') {
            $processed_card_number = protect_credit_card_number($card_number);
            $card_verification_number = protect_card_verification_number($liveform->get_field_value('card_verification_number'));
            
        // else a transaction was not processed through a payment gateway, so determine what to do with the credit card number and card verification number
        } else {
            // if encryption is enabled then encrypt credit card number
            if (
                (defined('ENCRYPTION_KEY') == TRUE)
                && (extension_loaded('mcrypt') == TRUE)
                && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
            ) {
                $processed_card_number = encrypt_credit_card_number($card_number, ENCRYPTION_KEY);
                
            // else encryption is not enabled so store credit card number in plain text
            } else {
                $processed_card_number = $card_number;
            }
            
            // store card verification number
            $card_verification_number = $liveform->get_field_value('card_verification_number');
        }
        
        $sql_credit_debit_card_values =
            "card_type = '" . e($card_type) . "',
            card_number = '" . e($processed_card_number) . "',
            expiration_month = '" . e($expiration_month) . "',
            expiration_year = '" . e($expiration_year) . "',
            cardholder = '" . e($liveform->get('cardholder')) . "',
            card_verification_number = '" . e($card_verification_number) . "',";
    } else {
        $sql_credit_debit_card_values = "";
    }

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
    
    // add data to orders table
    // We update the user id again below, in case we now have new user info
    // from the auto-registration feature that ran above.
    $query = "UPDATE orders
             SET
                payment_method = '" . escape($liveform->get_field_value('payment_method')) . "',
                $sql_credit_debit_card_values
                subtotal = '$subtotal',
                discount = '$discount',
                tax = '$tax',
                shipping = '$shipping',
                gift_card_discount = '$gift_card_discount',
                surcharge = '$surcharge',
                total = '$total',
                payment_installment = '$payment_installment',
                installment_charges = '$installment_charge',
                commission = '$commission',
                order_date = '$order_date',
                user_id = '$user_id',
                $sql_special_offer_code
                last_modified_timestamp = '$order_date',
                transaction_id = '" . escape($transaction_id) . "',
                authorization_code = '" . escape($authorization_code) . "',
                status = 'complete',
                $sql_tracking_code
                $sql_utm
                affiliate_code = '" . escape($affiliate_code) . "',
                ip_address = IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0)
            WHERE id = " . $_SESSION['ecommerce']['order_id'];
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // lock table, so no one can read table, so we can get the order number
    $query = "LOCK TABLES next_order_number WRITE";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // get order number
    $query = "SELECT next_order_number FROM next_order_number";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if a next order number was found, then use it
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $order_number = $row['next_order_number'];
        
    // else a next order number was not found, so use 1 and create next order number record
    } else {
        $query = "INSERT INTO next_order_number VALUES (1)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $order_number = 1;
    }
    
    // increment next order number
    $query = "UPDATE next_order_number SET next_order_number = next_order_number + 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // release lock on table
    $query = "UNLOCK TABLES";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // update order number for order
    $query = "UPDATE orders
             SET order_number = '$order_number'
             WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // we know the order number now, so if gift cards are enabled and there was a gift card error, then e-mail administrator and log activity
    if ((ECOMMERCE_GIFT_CARD == TRUE) && ($gift_card_error == TRUE)) {
        $error_message_detail = '';
        
        // loop through the gift card errors, in order to prepare error message detail
        foreach ($gift_card_errors as $gift_card_error) {
            if ($gift_card_error['givex'] == false) {
                $protected_gift_card_code = protect_gift_card_code($gift_card_error['gift_card_code']);
            } else {
                $protected_gift_card_code = protect_givex_gift_card_code($gift_card_error['gift_card_code']);
            }

            $error_message_detail .= ' Gift card: ' . $protected_gift_card_code . '. Attempted redemption amount: ' . BASE_CURRENCY_SYMBOL . number_format($gift_card_error['amount'] / 100, 2, '.', ',') . '.';
            
            // if there is a cURL error, then add error info to error message detail
            if (isset($gift_card_error['curl_errno']) == TRUE) {
                $error_message_detail .= ' cURL Error Number: ' . $gift_card_error['curl_errno'] . '. cURL Error Message: ' . $gift_card_error['curl_error'] . '.';
                
            // else if there is an error message from Givex, then add error info to error message detail
            } else if (isset($gift_card_error['error_message']) == TRUE) {
                if ($gift_card_error['givex'] == true) {
                    $error_message_detail .= ' Givex error:';
                }

                $error_message_detail .= ' ' . $gift_card_error['error_message'] . '.';
            }
        }
        
        $error_message = 'Order (' . $order_number . ') was accepted however one or more gift cards could not be redeemed properly because there was an error with at least one gift card. The order should probably not be fulfilled until the payment issue is resolved. Total attempted redemption amount: ' . BASE_CURRENCY_SYMBOL . number_format($required_redemption_amount / 100, 2, '.', ',') . '. Total successfull redemption amount: ' . BASE_CURRENCY_SYMBOL . number_format($current_redemption_amount / 100, 2, '.', ',') . '.' . $error_message_detail;
        
        log_activity($error_message, $_SESSION['sessionusername']);

        email(array(
            'to' => ECOMMERCE_EMAIL_ADDRESS,
            'from_name' => ORGANIZATION_NAME,
            'from_email_address' => EMAIL_ADDRESS,
            'subject' => 'Gift card error',
            'body' => $error_message));

    }

    // if a recurring transaction is required
    // and Credit/Debit Card was the selected payment method
    // and there is an active payment gateway, process recurring transactions
    if (
        ($recurring_transaction == TRUE)
        && ($liveform->get_field_value('payment_method') == 'Credit/Debit Card')
        && (ECOMMERCE_PAYMENT_GATEWAY != '')
    ) {
        // prepare for communicating with payment gateway in different ways depending on the selected payment gateway
        switch (ECOMMERCE_PAYMENT_GATEWAY) {
            case 'Authorize.Net':
                $start_date_format = 'Y-m-d';

                // If the Authorize.Net recurring URL has been set in the config.php file
                // (for an Authorize.Net emulator service), then use it.
                if ((defined('AUTHORIZENET_RECURRING_URL') == true) && (AUTHORIZENET_RECURRING_URL != '')) {
                    $payment_gateway_host = AUTHORIZENET_RECURRING_URL;
                    
                // Otherwise if test mode is enabled, use standard Authorize.net URL for test mode.
                } else if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
                    $payment_gateway_host = 'https://apitest.authorize.net/xml/v1/request.api';

                // Otherwise use standard production Authorize.Net URL.
                } else {
                    $payment_gateway_host = 'https://api2.authorize.net/xml/v1/request.api';
                }
                
                break;
                
            case 'First Data Global Gateway':
                $start_date_format = 'Ymd';
                
                break;
            
            case 'PayPal Payflow Pro':
                $start_date_format = 'mdY';
                
                break;
                
            case 'PayPal Payments Pro':
                $start_date_format = 'Y-m-d\TH:i:s.00\Z';
                
                break;
                
            case 'Sage':
                $start_date_format = 'm/d/Y';
                
                break;
        }
        
        // Loop through the recurring products in order to create recurring
        // transaction if necessary.
        foreach ($recurring_products as $product) {

            // If this product's price is less than or equal to 0,
            // then we don't need to create a transaction for it,
            // so skip to the next product.
            if ($product['price'] <= 0) {
                continue;
            }

            $price = $product['price'] * $product['quantity'];
            $tax = $product['tax'] * $product['quantity'];
            $amount = $price + $tax;
            
            // prepare values in a certain way for several payment gateways
            if (
                (ECOMMERCE_PAYMENT_GATEWAY == 'Authorize.Net')
                || (ECOMMERCE_PAYMENT_GATEWAY == 'First Data Global Gateway')
                || (ECOMMERCE_PAYMENT_GATEWAY == 'PayPal Payflow Pro')
                || (ECOMMERCE_PAYMENT_GATEWAY == 'PayPal Payments Pro')
                || (ECOMMERCE_PAYMENT_GATEWAY == 'Sage')
            ) {
                // convert amount from cents to dollars
                $amount = sprintf("%01.2lf", $amount / 100);
                
                if ($billing_company) {
                    $profile_name = $billing_company;
                } else {
                    $profile_name = $billing_first_name . ' ' . $billing_last_name;
                }
                
                $profile_name .= ' - ' . $order_number;
                
                // if the recurring start date for the order item is less than or equal to today,
                // then the order item was included in non-recurring transaction, so determine actual start date for profile
                if ($product['recurring_start_date'] <= date('Y-m-d')) {
                    switch ($product['recurring_payment_period']) {
                        case 'Monthly':
                            $start_date = date($start_date_format, time() + 86400 * 30);
                            break;

                        case 'Weekly':
                            $start_date = date($start_date_format, time() + 86400 * 7);
                            break;

                        case 'Every Two Weeks':
                            $start_date = date($start_date_format, time() + 86400 * 14);
                            break;

                        case 'Twice every Month':
                            $start_date = date($start_date_format, time() + 86400 * 15);
                            break;

                        case 'Every Four Weeks':
                            $start_date = date($start_date_format, time() + 86400 * 28);
                            break;

                        case 'Quarterly':
                            $start_date = date($start_date_format, time() + 86400 * 90);
                            break;

                        case 'Twice every Year':
                            $start_date = date($start_date_format, time() + 86400 * 180);
                            break;

                        case 'Yearly':
                            $start_date = date($start_date_format, time() + 86400 * 365);
                            break;
                    }
                    
                // else the recurring start date is greater than today, so figure out start date from start value
                } else {
                    $start_date = date($start_date_format, strtotime($product['recurring_start_date']));
                }
            }
            
            // process the recurring transaction differently based on the payment gateway
            switch (ECOMMERCE_PAYMENT_GATEWAY) {
                case 'Authorize.Net':
                    // get the payment period for the payment period
                    switch ($product['recurring_payment_period']) {
                        case 'Monthly':  // 1 month
                            $payment_period_length = '1';
                            $payment_period_units = 'months';
                            break;
                        
                        case 'Weekly':  // 7 days
                            $payment_period_length = '7';
                            $payment_period_units = 'days';
                            break;
                        
                        case 'Every Two Weeks':  // 14 days
                            $payment_period_length = '14';
                            $payment_period_units = 'days';
                            break;
                        
                        case 'Twice every Month':  // 15 days
                            $payment_period_length = '15';
                            $payment_period_units = 'days';
                            break;
                        
                        case 'Every Four Weeks':  // 28 days
                            $payment_period_length = '28';
                            $payment_period_units = 'days';
                            break;
                        
                        case 'Quarterly':  //  3 months
                            $payment_period_length = '3';
                            $payment_period_units = 'months';
                            break;
                        
                        case 'Twice every Year':  //  6 months
                            $payment_period_length = '6';
                            $payment_period_units = 'months';
                            break;
                        
                        case 'Yearly':  // 12 months
                            $payment_period_length = '12';
                            $payment_period_units = 'months';
                            break;
                    }
                    
                    // if the number of payments is 0, then set number of payments to 9999, because Authorize.Net requires that for unlimited
                    if ($product['recurring_number_of_payments'] == 0) {
                        $number_of_payments = 9999;
                        
                    // else the number of payments is not 0, so set it to the number of payments
                    } else {
                        $number_of_payments = $product['recurring_number_of_payments'];
                    }
                    
                    // prepare the transaction XML
                    // Authorize.Net does not appear to support foreign currencies at the transaction level
                    // so we have not included anything for that.  Maybe they support changing that at the
                    // account/processor level.
                    $transaction_xml =
                        "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                        "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                        "<merchantAuthentication>".
                        "<name>" . h(ECOMMERCE_AUTHORIZENET_API_LOGIN_ID) . "</name>".
                        "<transactionKey>" . h(ECOMMERCE_AUTHORIZENET_TRANSACTION_KEY) . "</transactionKey>".
                        "</merchantAuthentication>".
                            "<subscription>".
                            "<name>" . h($profile_name) . "</name>".
                                "<paymentSchedule>".
                                    "<interval>".
                                        "<length>". $payment_period_length . "</length>".
                                        "<unit>". $payment_period_units . "</unit>".
                                    "</interval>".
                                    "<startDate>" . $start_date . "</startDate>".
                                    "<totalOccurrences>". $number_of_payments . "</totalOccurrences>".
                                "</paymentSchedule>".
                                "<amount>". $amount . "</amount>".
                                "<payment>".
                                    "<creditCard>".
                                        "<cardNumber>" . h($card_number) . "</cardNumber>".
                                        "<expirationDate>" . h($expiration_year) . "-" . h($expiration_month) . "</expirationDate>".
                                    "</creditCard>".
                                "</payment>".
                                "<order>".
                                    "<invoiceNumber>" . $order_number . "</invoiceNumber>".
                                    "<description>" . h($profile_name) . "</description>".
                                "</order>".
                                "<customer>".
                                    "<email>" . h($billing_email_address) . "</email>".
                                "</customer>".
                                "<billTo>".
                                    "<firstName>". h($billing_first_name) . "</firstName>".
                                    "<lastName>" . h($billing_last_name) . "</lastName>".
                                    "<company>" . h($billing_company) . "</company>".
                                    "<address>" . h($billing_address_1) . ' ' . h($billing_address_2) . "</address>".
                                    "<city>" . h($billing_city) . "</city>".
                                    "<state>" . h($billing_state) . "</state>".
                                    "<zip>" . h($billing_zip_code) . "</zip>".
                                    "<country>" . h($billing_country) . "</country>".
                                "</billTo>".
                            "</subscription>".
                        "</ARBCreateSubscriptionRequest>";
                    
                    $ch = curl_init();
                    
                    curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $transaction_xml);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    
                    // if there is a proxy address, then send cURL request through proxy
                    if (PROXY_ADDRESS != '') {
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                    }
                    
                    $response_data = curl_exec($ch);
                    curl_close($ch);
                    
                    break;
                    
                case 'ClearCommerce':
                    // set frequency
                    switch ($product['recurring_payment_period']) {
                        case 'Monthly':
                            $clearcommerce_OrderFrequencyCycle = 'M';
                            $clearcommerce_OrderFrequencyInterval = '1';
                            break;
                        
                        case 'Weekly':
                            $clearcommerce_OrderFrequencyCycle = 'W';
                            $clearcommerce_OrderFrequencyInterval = '1';
                            break;
                        
                        case 'Every Two Weeks':
                            $clearcommerce_OrderFrequencyCycle = 'W';
                            $clearcommerce_OrderFrequencyInterval = '2';
                            break;
                        
                        case 'Twice every Month':
                            $clearcommerce_OrderFrequencyCycle = 'D';
                            $clearcommerce_OrderFrequencyInterval = '15';
                            break;
                        
                        case 'Every Four Weeks':
                            $clearcommerce_OrderFrequencyCycle = 'W';
                            $clearcommerce_OrderFrequencyInterval = '4';
                            break;
                        
                        case 'Quarterly':
                            $clearcommerce_OrderFrequencyCycle = 'M';
                            $clearcommerce_OrderFrequencyInterval = '3';
                            break;
                        
                        case 'Twice every Year':
                            $clearcommerce_OrderFrequencyCycle = 'M';
                            $clearcommerce_OrderFrequencyInterval = '6';
                            break;
                        
                        case 'Yearly':
                            $clearcommerce_OrderFrequencyCycle = 'M';
                            $clearcommerce_OrderFrequencyInterval = '12';
                            break;
                    }
                    
                    $transaction_xml =
                        '<EngineDocList>
                            <DocVersion>1.0</DocVersion>
                            <EngineDoc>
                                <ContentType>OrderFormDoc</ContentType>
                                <User>
                                    <Name>' . h(ECOMMERCE_CLEARCOMMERCE_USER_ID) . '</Name>
                                    <Password>' . h(ECOMMERCE_CLEARCOMMERCE_PASSWORD) . '</Password>
                                    <Alias>' . h(ECOMMERCE_CLEARCOMMERCE_CLIENT_ID) . '</Alias>
                                </User>
                                <Instructions>
                                    <Pipeline>Payment</Pipeline>
                                </Instructions>
                                <OrderFormDoc>
                                    <Mode>' . $clearcommerce_mode . '</Mode>
                                    <Consumer>
                                        <PaymentMech>
                                            <CreditCard>
                                                <Number>' . $card_number . '</Number>
                                                <Expires DataType="ExpirationDate" Locale="840">' . h($expiration_month) . '/' . h($expiration_year) . '</Expires>
                                                <Cvv2Val>' . h($liveform->get_field_value('card_verification_number')) . '</Cvv2Val>
                                                <Cvv2Indicator>1</Cvv2Indicator>
                                            </CreditCard>
                                        </PaymentMech>
                                        <BillTo>
                                            <Location>
                                                <Address>
                                                    <Name>' . h($billing_first_name) . ' ' . h($billing_last_name) . '</Name>
                                                    <Company>' . h($billing_company) . '</Company>
                                                    <Street1>' . h($billing_address_1) . '</Street1>
                                                    <Street2>' . h($billing_address_2) . '</Street2>
                                                    <City>' . h($billing_city) . '</City>
                                                    <StateProv>' . h($billing_state) . '</StateProv>
                                                    <PostalCode>' . h($billing_zip_code) . '</PostalCode>
                                                    <Country>' . get_country_number($billing_country) . '</Country>
                                                </Address>
                                            </Location>
                                        </BillTo>
                                    </Consumer>
                                    <Transaction>
                                        <Type>' . $clearcommerce_type . '</Type>
                                        <CurrentTotals>
                                            <Totals>
                                                <Total DataType="Money" Currency="' . $clearcommerce_currency_code . '">' . $amount . '</Total>
                                            </Totals>
                                        </CurrentTotals>
                                    </Transaction>
                                    <PbOrder>
                                        <OrderType DataType="S32">0</OrderType>
                                        <TotalNumberPayments DataType="S32">' . $product['recurring_number_of_payments'] . '</TotalNumberPayments>
                                        <OrderFrequencyCycle>' . $clearcommerce_OrderFrequencyCycle . '</OrderFrequencyCycle>
                                        <OrderFrequencyInterval DataType="S32">' . $clearcommerce_OrderFrequencyInterval . '</OrderFrequencyInterval>
                                    </PbOrder>
                                </OrderFormDoc>
                            </EngineDoc>
                        </EngineDocList>';
                    
                    $ch = curl_init();
                    
                    curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, 'CLRCMRC_XML=' . urlencode($transaction_xml));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    
                    // if there is a proxy address, then send cURL request through proxy
                    if (PROXY_ADDRESS != '') {
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                    }
                    
                    $response_data = curl_exec($ch);
                    curl_close($ch);
                    
                    break;
                    
                case 'First Data Global Gateway':
                    // Get the numerical portion of the 1st billing address.
                    preg_match('/[0-9]+/', $billing_address_1, $matches);
                    $avs_address_number = $matches[0];
                    
                    // set frequency
                    switch ($product['recurring_payment_period']) {
                        case 'Monthly':
                            $first_data_global_gateway_periodicity = 'monthly';
                            break;
                        
                        case 'Weekly':
                            $first_data_global_gateway_periodicity = 'weekly';
                            break;
                        
                        case 'Every Two Weeks':
                            $first_data_global_gateway_periodicity = 'biweekly';
                            break;
                        
                        case 'Twice every Month':
                            $first_data_global_gateway_periodicity = 'd15';
                            break;
                        
                        case 'Every Four Weeks':
                            $first_data_global_gateway_periodicity = 'w4';
                            break;
                        
                        case 'Quarterly':
                            $first_data_global_gateway_periodicity = 'm3';
                            break;
                        
                        case 'Twice every Year':
                            $first_data_global_gateway_periodicity = 'm6';
                            break;
                        
                        case 'Yearly':
                            $first_data_global_gateway_periodicity = 'yearly';
                            break;
                    }
                    
                    // Build the transaction XML
                    $transaction_xml =
                        '<order>
                            <billing>
                                <name>' . h($billing_first_name) . ' ' . h($billing_last_name) . '</name>
                                <company>' . h($billing_company) . '</company>
                                <address1>' . h($billing_address_1) . '</address1>
                                <address2>' . h($billing_address_2) . '</address2>
                                <city>' . h($billing_city) . '</city>
                                <state>' . h($billing_state) . '</state>
                                <zip>' . h($billing_zip_code) . '</zip>
                                <country>' . h($billing_country) . '</country>
                                <phone>' . h($billing_phone_number) . '</phone>
                                <fax>' . h($billing_fax_number) . '</fax>
                                <email>' . h($billing_email_address) . '</email>
                                <addrnum>' . $avs_address_number . '</addrnum>
                            </billing>
                            <transactiondetails>
                                <ip>' . h($_SERVER['REMOTE_ADDR']) . '</ip>
                            </transactiondetails>
                            <orderoptions>
                                <ordertype>' . $first_data_global_gateway_type . '</ordertype>
                                <result>' . $first_data_global_gateway_mode . '</result>
                            </orderoptions>
                            <payment>
                                <tax>' . $tax / 100 . '</tax>
                                <chargetotal>' . $amount . '</chargetotal>
                            </payment>
                            <periodic>
                                <action>SUBMIT</action>
                                <installments>' . $product['recurring_number_of_payments'] . '</installments>
                                <threshold>3</threshold>
                                <startdate>' . $start_date . '</startdate>
                                <periodicity>' . $first_data_global_gateway_periodicity . '</periodicity>
                            </periodic>
                            <creditcard>
                                <cardnumber>' . $card_number . '</cardnumber>
                                <cardexpmonth>' . h($expiration_month) . '</cardexpmonth>
                                <cardexpyear>' .  h(mb_substr($expiration_year, -2)) . '</cardexpyear>
                                <cvmvalue>' .  h($liveform->get_field_value('card_verification_number')) . '</cvmvalue>
                                <cvmindicator>provided</cvmindicator>
                            </creditcard>
                            <merchantinfo>
                                <configfile>' . h(ECOMMERCE_FIRST_DATA_GLOBAL_GATEWAY_STORE_NUMBER) . '</configfile>
                            </merchantinfo>
                        </order>';

                    $ch = curl_init();
                    
                    curl_setopt($ch, CURLOPT_URL, 'https://secure.linkpt.net:1129');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    // Setup an SSL connection using the supplied .pem file from First Data Global Gateway
                    curl_setopt($ch, CURLOPT_SSLCERT, FILE_DIRECTORY_PATH . '/' . ECOMMERCE_FIRST_DATA_GLOBAL_GATEWAY_PEM_FILE_NAME);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $transaction_xml);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  0);
                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    
                    // if there is a proxy address, then send cURL request through proxy
                    if (PROXY_ADDRESS != '') {
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                    }
                    
                    $response_data = curl_exec($ch);
                    curl_close($ch);

                    break;
                    
                case 'PayPal Payflow Pro':
                    // get the Verisign payment period code for the payment period
                    switch ($product['recurring_payment_period']) {
                        case 'Monthly':  // 1 month
                            $payment_period = 'MONT';
                            break;

                        case 'Weekly':  // 7 days
                            $payment_period = 'WEEK';
                            break;

                        case 'Every Two Weeks':  // 14 days
                            $payment_period = 'BIWK';
                            break;

                        case 'Twice every Month':  // 15 days
                            $payment_period = 'SMMO';
                            break;

                        case 'Every Four Weeks':  // 28 days
                            $payment_period = 'FRWK';
                            break;

                        case 'Quarterly':  //  3 months
                            $payment_period = 'QTER';
                            break;

                        case 'Twice every Year':  //  6 months
                            $payment_period = 'SMYR';
                            break;

                        case 'Yearly':  // 12 months
                            $payment_period = 'YEAR';
                            break;
                    }
                    
                    $transaction_values = array(
                        'PARTNER' => ECOMMERCE_PAYPAL_PAYFLOW_PRO_PARTNER,
                        'VENDOR'  => ECOMMERCE_PAYPAL_PAYFLOW_PRO_MERCHANT_LOGIN,
                        'USER'    => $payment_gateway_user,
                        'PWD'     => ECOMMERCE_PAYPAL_PAYFLOW_PRO_PASSWORD,
                        'TRXTYPE' => 'R',
                        'TENDER'  => 'C',
                        'ACTION'  => 'A',
                        'PROFILENAME' => $profile_name,
                        'AMT'     => $amount,
                        'CURRENCY' => BASE_CURRENCY_CODE,
                        'TAXAMT'  => sprintf("%01.2lf", $tax / 100),
                        'ACCT'    => $card_number,
                        'EXPDATE' => $expiration_month . mb_substr($expiration_year, -2),
                        'CVV2'    => $liveform->get_field_value('card_verification_number'),
                        'START'   => $start_date,
                        'TERM'    => $product['recurring_number_of_payments'],
                        'PAYPERIOD' => $payment_period,
                        'FIRSTNAME' => $billing_first_name,
                        'LASTNAME' => $billing_last_name,
                        'COMPANYNAME' => $billing_company,
                        'EMAIL' =>   $billing_email_address,
                        'STREET'  => $billing_address_1 . ' ' . $billing_address_2,
                        'CITY'    => $billing_city,
                        'STATE'    => $billing_state,
                        'ZIP'     => $billing_zip_code,
                        'COUNTRY' => $billing_country,
                        'COMMENT1' => $profile_name);
                    
                    // process the PayPal Payflow Pro transaction differently based on communication method
                    switch ($payment_gateway_communication_method) {
                        case 'curl':
                            $current_time_parts = gettimeofday();
                            $request_id = md5($current_time_parts['sec'] . '.' . $current_time_parts['usec']);
                            
                            $headers =
                                array(
                                    'Content-Type: text/namevalue',
                                    'X-VPS-VIT-Client-Certification-Id: 978cd78328cf44d58863f98e863f64dd',
                                    'X-VPS-Request-ID: ' . $request_id
                                );
                            
                            $post_data = '';
                            
                            foreach ($transaction_values as $name => $value) {
                                if ($post_data) {
                                    $post_data .= '&';
                                }
                                
                                $value = str_replace('"', '', $value);
                                
                                $post_data .= $name . '[' . strlen($value) . ']' . '=' . $value;
                            }
                            
                            $ch = curl_init();
                            
                            curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                            curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            
                            // if there is a proxy address, then send cURL request through proxy
                            if (PROXY_ADDRESS != '') {
                                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                            }
                            
                            $response_data = curl_exec($ch);
                            curl_close($ch);
                            
                            break;
                            
                        case 'payflow pro com object':
                            $post_data = '';
                            
                            foreach ($transaction_values as $name => $value) {
                                if ($post_data) {
                                    $post_data .= '&';
                                }
                                
                                $value = str_replace('"', '', $value);
                                
                                $post_data .= $name . '[' . strlen($value) . ']' . '=' . $value;
                            }
                            
                            $payflowpro = new COM("PFProCOMControl.PFProCOMControl.1");

                            $context = $payflowpro->CreateContext($payment_gateway_host, 443, 30, '', 0, '', '');
                            $response_data = $payflowpro->SubmitTransaction($context, $post_data, strlen($post_data));
                            $payflowpro->DestroyContext($context);
                            
                            break;
                            
                        case 'pfpro extension':
                            $response = pfpro_process($transaction_values, $payment_gateway_host, 443, 30);
                            
                            break;
                    }
                    
                    break;
                    
                case 'PayPal Payments Pro':
                    // get the payment period and frequency for the payment profile
                    switch ($product['recurring_payment_period']) {
                        case 'Monthly':  // 1 month
                            $payment_period = 'Month';
                            $payment_frequency = '1';
                            break;

                        case 'Weekly':  // 1 week
                            $payment_period = 'Week';
                            $payment_frequency = '1';
                            break;

                        case 'Every Two Weeks':  // 2 weeks
                            $payment_period = 'Week';
                            $payment_frequency = '2';
                            break;

                        case 'Twice every Month':  // 2 weeks
                            $payment_period = 'Week';
                            $payment_frequency = '2';
                            break;

                        case 'Every Four Weeks':  // 4 weeks
                            $payment_period = 'Week';
                            $payment_frequency = '4';
                            break;

                        case 'Quarterly':  //  3 months
                            $payment_period = 'Month';
                            $payment_frequency = '3';
                            break;

                        case 'Twice every Year':  //  6 months
                            $payment_period = 'Month';
                            $payment_frequency = '6';
                            break;

                        case 'Yearly':  // 1 year
                            $payment_period = 'Year';
                            $payment_frequency = '1';
                            break;
                    }
                    
                    // Setup the transaction values
                    $transaction_values = array(
                        'METHOD' => 'CreateRecurringPaymentsProfile',
                        'VERSION' => '50.0',
                        'PWD' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_PASSWORD,
                        'USER' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_USERNAME,
                        'SIGNATURE' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_SIGNATURE,
                        'SUBSCRIBERNAME' => $billing_first_name . ' ' . $billing_last_name,
                        'EMAIL' =>   $billing_email_address,
                        'PROFILESTARTDATE' => $start_date,
                        'PROFILEREFERENCE' => $profile_name,
                        'DESC' => $product['short_description'],
                        'BILLINGPERIOD' => $payment_period,
                        'BILLINGFREQUENCY' => $payment_frequency,
                        'TOTALBILLINGCYCLES' => $product['recurring_number_of_payments'],
                        'AMT' => sprintf("%01.2lf", $price / 100),
                        'TAXAMT'  => sprintf("%01.2lf", $tax / 100),
                        'CREDITCARDTYPE' => $credit_card_type,
                        'ACCT' => $card_number,
                        'EXPDATE' => $expiration_month . $expiration_year,
                        'CVV2' => $liveform->get_field_value('card_verification_number'),
                        'FIRSTNAME' => $billing_first_name,
                        'LASTNAME' => $billing_last_name,
                        'STREET' => $billing_address_1 . ' ' . $billing_address_2, // using STREET instead of STREET1 and STREET2 because they do not appear to work correctly, even though API says they should
                        'CITY' => $billing_city,
                        'STATE' => $billing_state,
                        'ZIP' => $billing_zip_code,
                        'COUNTRYCODE' => $billing_country,
                        'CURRENCYCODE' => BASE_CURRENCY_CODE);
                    
                    $post_data = '';
                    
                    // Place the transaction values into the correct format
                    foreach ($transaction_values as $name => $value) {
                        if ($post_data) {
                            $post_data .= '&';
                        }
                        
                        $value = str_replace('"', '', $value);
                        
                        $post_data .= $name . '=' . urlencode($value);
                    }
                    
                    $ch = curl_init();
                    
                    curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    
                    // if there is a proxy address, then send cURL request through proxy
                    if (PROXY_ADDRESS != '') {
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                    }
                    
                    $response_data = curl_exec($ch);
                    curl_close($ch);
                    
                    // parse response
                    parse_str($response_data, $response);
                    
                    // if a recurring profile id was returned, then set recurring profile id for order item
                    if (isset($response['PROFILEID']) == true) {
                        $query =
                            "UPDATE order_items
                            SET
                                recurring_profile_id = '" . escape($response['PROFILEID']) . "',
                                recurring_profile_enabled = '1'
                            WHERE id = '" . $product['order_item_id'] . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                    
                    break;
                    
                case 'Sage':
                    $payment_gateway_error_message = '';
                
                    $sage_shipping_recipient = '';
                    $sage_shipping_address = '';
                    $sage_shipping_city = '';
                    $sage_shipping_state = '';
                    $sage_shipping_zip = '';
                    $sage_shipping_country = '';
                    
                    // if this order item is part of a ship to, then get shipping information
                    if ($product['ship_to_id'] != 0) {
                        $query =
                            "SELECT
                                first_name,
                                last_name,
                                address_1,
                                address_2,
                                city,
                                state,
                                zip_code,
                                country
                            FROM ship_tos
                            WHERE id = '" . $product['ship_to_id'] . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        
                        $sage_shipping_recipient = $row['first_name'] . ' ' . $row['last_name'];
                        $sage_shipping_address = $row['address_1'] . ' ' . $row['address_2'];
                        $sage_shipping_city = $row['city'];
                        $sage_shipping_state = $row['state'];
                        $sage_shipping_zip = $row['zip_code'];
                        $sage_shipping_country = $row['country'];
                    }
                    
                    // create recurring customer
                    
                    // all of these fields are required to be sent even though some do not have values
                    $transaction_values = array(
                        'M_ID' => ECOMMERCE_SAGE_MERCHANT_ID,
                        'M_KEY' => ECOMMERCE_SAGE_MERCHANT_KEY,
                        'CUSTOMER_TYPE' => '1', // "1" is Active and "0" is Inactive.
                        'FIRST_NAME' => $billing_first_name,
                        'LAST_NAME' => $billing_last_name,
                        'ADDRESS' => $billing_address_1 . ' ' . $billing_address_2,
                        'CITY' => $billing_city,
                        'STATE' => $billing_state,
                        'ZIP' => $billing_zip_code,
                        'COUNTRY' => $billing_country,
                        'TELEPHONE' => $billing_phone_number,
                        'FAX' => $billing_fax_number,
                        'EMAIL_ADDRESS' => $billing_email_address,
                        'SHIPPING_RECIPIENT' => $sage_shipping_recipient,
                        'SHIPPING_ADDRESS' => $sage_shipping_address,
                        'SHIPPING_CITY' => $sage_shipping_city,
                        'SHIPPING_STATE' => $sage_shipping_state,
                        'SHIPPING_ZIP' => $sage_shipping_zip,
                        'SHIPPING_COUNTRY' => $sage_shipping_country,
                        'HOLD' => '0', // we don't know what this is
                        'HOLD_MESSAGE' => '', // we don't know what this is
                        'REFERENCE' => $order_number
                    );
                    
                    $post_data = '';
                    
                    foreach ($transaction_values as $name => $value) {
                        if ($post_data) {
                            $post_data .= '&';
                        }
                        
                        $post_data .= $name . '=' . urlencode($value);
                    }
                    
                    // Setup cURL options.
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://www.sagepayments.net/web_services/vterm_extensions/recurring.asmx/CREATE_RECURRING_CUSTOMER');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    
                    // if there is a proxy address, then send cURL request through proxy
                    if (PROXY_ADDRESS != '') {
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                    }
                    
                    $response_data = curl_exec($ch);
                    
                    $curl_errno = curl_errno($ch);
                    $curl_error = curl_error($ch);
                    
                    curl_close($ch);
                    
                    // if there was an error then prepare error message
                    if (($response_data === FALSE) || (mb_strpos($response_data, '<CUSTOMER_ID>') === FALSE)) {
                        $payment_gateway_error_message = 'A recurring profile could not be created for order number ' . $order_number . ' due to a communication problem with the payment gateway. The CREATE_RECURRING_CUSTOMER transaction failed. The recurring profile will need to be setup manually in your payment gateway\'s control panel. The order was still accepted.';
                        
                        // if there was a cURL error, add error number and error message to payment gateway error message
                        if ($curl_errno) {
                            $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                        }
                        
                        // if the response was not empty, then add response to the end of the error message
                        if (trim($response_data) != '') {
                            $payment_gateway_error_message .= ' ' . trim($response_data);
                        }
                        
                    // else there was not an error, so get customer ID and continue with the rest of the recurring transactions
                    } else {
                        // get customer ID
                        preg_match('/<CUSTOMER_ID>(.*?)<\/CUSTOMER_ID>/', $response_data, $matches);
                        $sage_customer_id = $matches[1];
                        
                        $sage_group_id = $product['sage_group_id'];
                        
                        // if this order item's product does not have a Sage group ID, then create recurring group
                        if ($sage_group_id == 0) {
                            // create recurring group
                            
                            $transaction_values = array(
                                'M_ID' => ECOMMERCE_SAGE_MERCHANT_ID,
                                'M_KEY' => ECOMMERCE_SAGE_MERCHANT_KEY,
                                'GROUP_DESCRIPTION' => $profile_name
                            );
                            
                            $post_data = '';
                            
                            foreach ($transaction_values as $name => $value) {
                                if ($post_data) {
                                    $post_data .= '&';
                                }
                                
                                $post_data .= $name . '=' . urlencode($value);
                            }
                            
                            // Setup cURL options.
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://www.sagepayments.net/web_services/vterm_extensions/recurring.asmx/CREATE_RECURRING_GROUP');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                            curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            
                            // if there is a proxy address, then send cURL request through proxy
                            if (PROXY_ADDRESS != '') {
                                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                            }
                            
                            $response_data = curl_exec($ch);
                            
                            $curl_errno = curl_errno($ch);
                            $curl_error = curl_error($ch);
                            
                            curl_close($ch);
                            
                            // if there was an error then prepare error message
                            if (($response_data === FALSE) || (mb_strpos($response_data, '<GROUP_ID>') === FALSE)) {
                                $payment_gateway_error_message = 'A recurring profile could not be created for order number ' . $order_number . ' due to a communication problem with the payment gateway. The CREATE_RECURRING_GROUP transaction failed. The recurring profile will need to be setup manually in your payment gateway\'s control panel. The order was still accepted.';
                                
                                // if there was a cURL error, add error number and error message to payment gateway error message
                                if ($curl_errno) {
                                    $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                }
                                
                                // if the response was not empty, then add response to the end of the error message
                                if (trim($response_data) != '') {
                                    $payment_gateway_error_message .= ' ' . trim($response_data);
                                }
                                
                            // else there was not an error, so get group ID
                            } else {
                                // get customer ID
                                preg_match('/<GROUP_ID>(.*?)<\/GROUP_ID>/', $response_data, $matches);
                                $sage_group_id = $matches[1];
                            }
                        }
                        
                        // if there was not an error getting/creating the group, then continue with the rest of the recurring transactions
                        if ($payment_gateway_error_message == '') {
                            // create either a monthly or daily schedule based on the payment period
                            switch ($product['recurring_payment_period']) {
                                case 'Monthly':
                                case 'Quarterly':
                                case 'Twice every Year':
                                case 'Yearly':
                                    // create recurring monthly schedule
                                    
                                    $sage_monthly_interval = '';
                                    
                                    // set monthly interval differently based on the payment period
                                    switch ($product['recurring_payment_period']) {
                                        case 'Monthly':
                                            $sage_monthly_interval = '1';
                                            break;
                                            
                                        case 'Quarterly':
                                            $sage_monthly_interval = '3';
                                            break;
                                        
                                        case 'Twice every Year':
                                            $sage_monthly_interval = '6';
                                            break;
                                        
                                        case 'Yearly':
                                            $sage_monthly_interval = '12';
                                            break;
                                    }
                                    
                                    $start_date_parts = explode('/', $start_date);
                                    $sage_day_of_month = $start_date_parts[1];
                                    
                                    // all of these fields are required to be sent even though some do not have values
                                    $transaction_values = array(
                                        'M_ID' => ECOMMERCE_SAGE_MERCHANT_ID,
                                        'M_KEY' => ECOMMERCE_SAGE_MERCHANT_KEY,
                                        'SCHEDULE_DESCRIPTION' => $profile_name . ' - ' . $product['recurring_payment_period'],
                                        'MONTHLY_INTERVAL' => $sage_monthly_interval,
                                        'DAY_OF_MONTH' => $sage_day_of_month,
                                        'NON_BUSINESS_DAYS' => '2', // that day (not before or after)
                                        'START_OFFSET' => '0' // we don't know what this is
                                    );
                                    
                                    $post_data = '';
                                    
                                    foreach ($transaction_values as $name => $value) {
                                        if ($post_data) {
                                            $post_data .= '&';
                                        }
                                        
                                        $post_data .= $name . '=' . urlencode($value);
                                    }
                                    
                                    // Setup cURL options.
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, 'https://www.sagepayments.net/web_services/vterm_extensions/recurring.asmx/CREATE_RECURRING_MONTHLY_SCHEDULE');
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    
                                    // if there is a proxy address, then send cURL request through proxy
                                    if (PROXY_ADDRESS != '') {
                                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                                    }
                                    
                                    $response_data = curl_exec($ch);
                                    
                                    $curl_errno = curl_errno($ch);
                                    $curl_error = curl_error($ch);
                                    
                                    curl_close($ch);
                                    
                                    // if there was an error then prepare error message
                                    if (($response_data === FALSE) || (mb_strpos($response_data, '<SCHEDULE_ID>') === FALSE)) {
                                        $payment_gateway_error_message = 'A recurring profile could not be created for order number ' . $order_number . ' due to a communication problem with the payment gateway. The CREATE_RECURRING_MONTHLY_SCHEDULE transaction failed. The recurring profile will need to be setup manually in your payment gateway\'s control panel. The order was still accepted.';
                                        
                                        // if there was a cURL error, add error number and error message to payment gateway error message
                                        if ($curl_errno) {
                                            $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                        }
                                        
                                        // if the response was not empty, then add response to the end of the error message
                                        if (trim($response_data) != '') {
                                            $payment_gateway_error_message .= ' ' . trim($response_data);
                                        }
                                        
                                    // else there was not an error, so get schedule ID
                                    } else {
                                        preg_match('/<SCHEDULE_ID>(.*?)<\/SCHEDULE_ID>/', $response_data, $matches);
                                        $sage_schedule_id = $matches[1];
                                    }
                                    
                                    break;
                                    
                                case 'Weekly':
                                case 'Every Two Weeks':
                                case 'Twice every Month':
                                case 'Every Four Weeks':
                                    // create recurring daily schedule
                                    
                                    $sage_daily_interval = '';
                                    
                                    // set daily interval differently based on the payment period
                                    switch ($product['recurring_payment_period']) {
                                        case 'Weekly':
                                            $sage_daily_interval = '7';
                                            break;
                                            
                                        case 'Every Two Weeks':
                                            $sage_daily_interval = '14';
                                            break;
                                        
                                        case 'Twice every Month':
                                            $sage_daily_interval = '15';
                                            break;
                                        
                                        case 'Every Four Weeks':
                                            $sage_daily_interval = '28';
                                            break;
                                    }
                                    
                                    $transaction_values = array(
                                        'M_ID' => ECOMMERCE_SAGE_MERCHANT_ID,
                                        'M_KEY' => ECOMMERCE_SAGE_MERCHANT_KEY,
                                        'SCHEDULE_DESCRIPTION' => $profile_name . ' - ' . $product['recurring_payment_period'],
                                        'DAILY_INTERVAL' => $sage_daily_interval,
                                        'START_DATE' => $start_date,
                                        'NON_BUSINESS_DAYS' => '2' // that day (not before or after)
                                    );
                                    
                                    $post_data = '';
                                    
                                    foreach ($transaction_values as $name => $value) {
                                        if ($post_data) {
                                            $post_data .= '&';
                                        }
                                        
                                        $post_data .= $name . '=' . urlencode($value);
                                    }
                                    
                                    // Setup cURL options.
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, 'https://www.sagepayments.net/web_services/vterm_extensions/recurring.asmx/CREATE_RECURRING_DAILY_SCHEDULE');
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    
                                    // if there is a proxy address, then send cURL request through proxy
                                    if (PROXY_ADDRESS != '') {
                                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                                    }
                                    
                                    $response_data = curl_exec($ch);
                                    
                                    $curl_errno = curl_errno($ch);
                                    $curl_error = curl_error($ch);
                                    
                                    curl_close($ch);
                                    
                                    // if there was an error then prepare error message
                                    if (($response_data === FALSE) || (mb_strpos($response_data, '<SCHEDULE_ID>') === FALSE)) {
                                        $payment_gateway_error_message = 'A recurring profile could not be created for order number ' . $order_number . ' due to a communication problem with the payment gateway. The CREATE_RECURRING_DAILY_SCHEDULE transaction failed. The recurring profile will need to be setup manually in your payment gateway\'s control panel. The order was still accepted.';
                                        
                                        // if there was a cURL error, add error number and error message to payment gateway error message
                                        if ($curl_errno) {
                                            $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                        }
                                        
                                        // if the response was not empty, then add response to the end of the error message
                                        if (trim($response_data) != '') {
                                            $payment_gateway_error_message .= ' ' . trim($response_data);
                                        }
                                        
                                    // else there was not an error, so get schedule ID
                                    } else {
                                        preg_match('/<SCHEDULE_ID>(.*?)<\/SCHEDULE_ID>/', $response_data, $matches);
                                        $sage_schedule_id = $matches[1];
                                    }
                                    
                                    break;
                            }
                            
                            // if there was not an error creating schedule, then continue to process the final recurring transaction
                            if ($payment_gateway_error_message == '') {
                                // create recurring bankcard payer and transaction
                                
                                // if the number of payments is 0, then set values so recurring profile does not expire
                                if ($product['recurring_number_of_payments'] == 0) {
                                    $sage_times_to_process = '0';
                                    $sage_indefinite = '1';
                                    
                                // else the number of payments is not 0, so set values so recurring profile will expire
                                } else {
                                    $sage_times_to_process = $product['recurring_number_of_payments'];
                                    $sage_indefinite = '0';
                                }
                                
                                $transaction_values = array(
                                    'M_ID' => ECOMMERCE_SAGE_MERCHANT_ID,
                                    'M_KEY' => ECOMMERCE_SAGE_MERCHANT_KEY,
                                    'ACTIVE' => '1',
                                    'CUSTOMER_ID' => $sage_customer_id,
                                    'FIRST_NAME' => $billing_first_name,
                                    'LAST_NAME' => $billing_last_name,
                                    'ADDRESS' => $billing_address_1 . ' ' . $billing_address_2,
                                    'CITY' => $billing_city,
                                    'STATE' => $billing_state,
                                    'ZIP' => $billing_zip_code,
                                    'COUNTRY' => $billing_country,
                                    'EMAIL_ADDRESS' => $billing_email_address,
                                    'SHIPPING_RECIPIENT' => $sage_shipping_recipient,
                                    'SHIPPING_ADDRESS' => $sage_shipping_address,
                                    'SHIPPING_CITY' => $sage_shipping_city,
                                    'SHIPPING_STATE' => $sage_shipping_state,
                                    'SHIPPING_ZIP' => $sage_shipping_zip,
                                    'SHIPPING_COUNTRY' => $sage_shipping_country,
                                    'CARDNUMBER' => $card_number,
                                    'CARD_EXP_MONTH' => $expiration_month,
                                    'CARD_EXP_YEAR' => mb_substr($expiration_year, -2),
                                    'GROUP_ID' => $sage_group_id,
                                    'SCHEDULE_ID' => $sage_schedule_id,
                                    'AMOUNT' => $amount,
                                    'T_CODE' => $sage_transaction_type,
                                    'TIMES_TO_PROCESS' => $sage_times_to_process,
                                    'INDEFINITE' => $sage_indefinite,
                                    'REFERENCE' => $order_number,
                                    'START_DATE' => $start_date
                                );
                                
                                $post_data = '';
                                
                                foreach ($transaction_values as $name => $value) {
                                    if ($post_data) {
                                        $post_data .= '&';
                                    }
                                    
                                    $post_data .= $name . '=' . urlencode($value);
                                }
                                
                                // Setup cURL options.
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, 'https://www.sagepayments.net/web_services/vterm_extensions/recurring.asmx/CREATE_RECURRING_BANKCARD_PAYER_AND_TRANSACTION');
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                
                                // if there is a proxy address, then send cURL request through proxy
                                if (PROXY_ADDRESS != '') {
                                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                    curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                                }
                                
                                $response_data = curl_exec($ch);
                                
                                $curl_errno = curl_errno($ch);
                                $curl_error = curl_error($ch);
                                
                                curl_close($ch);
                                
                                // if there was an error then prepare error message
                                if (($response_data === FALSE) || (mb_strpos($response_data, '<RECURRING_ID>') === FALSE)) {
                                    $payment_gateway_error_message = 'A recurring profile could not be created for order number ' . $order_number . ' due to a communication problem with the payment gateway. The CREATE_RECURRING_BANKCARD_PAYER_AND_TRANSACTION transaction failed. The recurring profile will need to be setup manually in your payment gateway\'s control panel. The order was still accepted.';
                                    
                                    // if there was a cURL error, add error number and error message to payment gateway error message
                                    if ($curl_errno) {
                                        $payment_gateway_error_message .= ' cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.';
                                    }
                                    
                                    // if the response was not empty, then add response to the end of the error message
                                    if (trim($response_data) != '') {
                                        $payment_gateway_error_message .= ' ' . trim($response_data);
                                    }
                                }
                            }
                        }
                    }
                    
                    // if there was an error creating recurring profile with Sage, then log activity and e-mail administrator
                    if ($payment_gateway_error_message != '') {

                        log_activity($payment_gateway_error_message, $_SESSION['sessionusername']);

                        email(array(
                            'to' => ECOMMERCE_EMAIL_ADDRESS,
                            'from_name' => ORGANIZATION_NAME,
                            'from_email_address' => EMAIL_ADDRESS,
                            'subject' => 'Recurring profile error',
                            'body' => $payment_gateway_error_message));

                    }
                    
                    break;
            }
        }
    }

    // store order id as completed order id so it can be found by order receipt screen
    $_SESSION['ecommerce']['completed_order_id'] = $_SESSION['ecommerce']['order_id'];

    // Add values to session for order number and total so they can be easily accessed
    // for outputting to conversion tracking JavaScript services and etc.
    $_SESSION['software']['order_number'] = $order_number;
    $_SESSION['software']['order_total'] = sprintf("%01.2lf", $total / 100);
    
    // if an order receipt is set to be e-mailed, then do that
    if ($order_receipt_email == 1) {
        
        // initialize array for storing all e-mail addresses that the order receipt should be BCC'd to
        $bcc_email_addresses = array();
        
        // if there is a ECOMMERCE_EMAIL_ADDRESS then add it to the array
        if (ECOMMERCE_EMAIL_ADDRESS != '') {
            $bcc_email_addresses[] = ECOMMERCE_EMAIL_ADDRESS;
        }
        
        // loop through all products to get order receipt bcc e-mail addresses
        foreach ($products as $product) {
            // if there is an order receipt bcc e-mail address
            // and if it is not already in the array,
            // and if it is valid, then add e-mail address to array
            if (
                ($product['order_receipt_bcc_email_address'] != '')
                && (in_array($product['order_receipt_bcc_email_address'], $bcc_email_addresses) == false)
                && (validate_email_address($product['order_receipt_bcc_email_address']) == TRUE)
            ) {
                $bcc_email_addresses[] = $product['order_receipt_bcc_email_address'];
            }
        }        

        // if the format of the e-mail should be plain text, then prepare that
        if ($order_receipt_email_format == 'plain_text') {
            $body = '';

            // if there is a header, then start body with header and a blank line for spacing
            if ($order_receipt_email_header != '') {
                $body .=
                    $order_receipt_email_header . "\n" .
                    "\n";
            }
            
            include_once('get_order_receipt_in_plain_text.php');

            $body .= get_order_receipt_in_plain_text($_SESSION['ecommerce']['order_id']);

            // if there is a footer, then end body with a blank line for spacing and then the footer
            if ($order_receipt_email_footer != '') {
                $body .=
                    "\n" .
                    "\n" .
                    $order_receipt_email_footer;
            }

        // else the format of the e-mail should be HTML, so prepare that
        } else {

            require_once(dirname(__FILE__) . '/get_page_content.php');

            // get order receipt page HTML for e-mail
            $body = get_page_content($order_receipt_email_page_id, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true);
            
        }
        
        email(array(
            'to' => $billing_email_address,
            'bcc' => $bcc_email_addresses,
            'from_name' => ORGANIZATION_NAME,
            'from_email_address' => EMAIL_ADDRESS,
            'subject' => $order_receipt_email_subject . $order_number,
            'format' => $order_receipt_email_format,
            'body' => $body));
    }

    // If shipping is enabled, then update ship date & delivery date, and user's address book (for
    // auto-registration) for recipients.

    if (ECOMMERCE_SHIPPING) {

        // Get excluded transit dates before we loop through the ship tos
        // so we only have to run one query.
        $excluded_transit_date_items = db_items(
            "SELECT
                shipping_method_id,
                date
            FROM excluded_transit_dates");

        $excluded_transit_dates = array();

        // Loop through the raw excluded transit date data in order to prepare
        // an array that we can use to easily look up transit dates for a shipping method.
        foreach ($excluded_transit_date_items as $excluded_transit_date_item) {
            // If an item has not been added for this shipping method yet, then create array for it.
            if (isset($excluded_transit_dates[$excluded_transit_date_item['shipping_method_id']]) == false) {
                $excluded_transit_dates[$excluded_transit_date_item['shipping_method_id']] = array();
            }

            $excluded_transit_dates[$excluded_transit_date_item['shipping_method_id']][] = $excluded_transit_date_item['date'];
        }

        // Get all ship tos in order to update dates.
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
                ship_tos.country,
                ship_tos.address_type,
                ship_tos.phone_number,
                ship_tos.arrival_date,
                shipping_methods.id AS shipping_method_id,
                shipping_methods.base_transit_days,
                shipping_methods.adjust_transit,
                shipping_methods.transit_on_sunday,
                shipping_methods.transit_on_saturday,
                countries.id AS country_id,
                countries.transit_adjustment_days
            FROM ship_tos
            LEFT JOIN shipping_methods ON ship_tos.shipping_method_id = shipping_methods.id
            LEFT JOIN countries ON ship_tos.country = countries.code
            WHERE ship_tos.order_id = '" . e($_SESSION['ecommerce']['order_id']) . "'");

        // Loop through the ship tos in order to update address book and dates for each recipient
        foreach ($ship_tos as $ship_to) {

            // If auto-registration was used in order to create a new user or connect order to
            // an existing user and the existing user does not already have this same recipient,
            // then add recipient to user's address book, so when the user logs in, in the future,
            // his/her address book will contain this recipient. We don't update existing address
            // book recipients for an existing user, because this customer was not authenticated via
            // auto-registration, so we don't know if he/she should be allowed to update the address
            // book.  We want to prevent one customer from clobbering the address book of a
            // different customer.
            //
            // It is important that we update the address book before updating dates below, because
            // the date logic below uses a continue call which skips to the next recipient.

            if (
                $auto_registration_type == 'new'
                or (
                    $auto_registration_type == 'existing'
                    and !db(
                        "SELECT id FROM address_book
                        WHERE
                            user = '" . e($user_id) . "'
                            AND ship_to_name = '" . e($ship_to['ship_to_name']) . "'")
                )
            ) {
                db(
                    "INSERT INTO address_book (
                        user,
                        ship_to_name,
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
                        phone_number)
                    VALUES (
                        '" . e($user_id) . "',
                        '" . e($ship_to['ship_to_name']) . "',
                        '" . e($ship_to['salutation']) . "',
                        '" . e($ship_to['first_name']) . "',
                        '" . e($ship_to['last_name']) . "',
                        '" . e($ship_to['company']) . "',
                        '" . e($ship_to['address_1']) . "',
                        '" . e($ship_to['address_2']) . "',
                        '" . e($ship_to['city']) . "',
                        '" . e($ship_to['state']) . "',
                        '" . e($ship_to['zip_code']) . "',
                        '" . e($ship_to['country']) . "',
                        '" . e($ship_to['address_type']) . "',
                        '" . e($ship_to['phone_number']) . "')");
            }

            $ship_date = '';

            // If the arrival date is at once, then determine the ship date
            // by looking at the order date and preparation time for the products.
            if ($ship_to['arrival_date'] == '0000-00-00') {

                // Get the maximum preparation time by looking for the product in this ship to
                // that has the greatest preparation time.
                $preparation_time = db(
                    "SELECT MAX(products.preparation_time)
                    FROM order_items
                    LEFT JOIN products ON order_items.product_id = products.id
                    WHERE order_items.ship_to_id = '" . e($ship_to['id']) . "'");

                require_once(dirname(__FILE__) . '/shipping.php');

                $response = get_ship_date(array(
                    'preparation_time' => $preparation_time,
                    'shipping_method' => array('id' => $ship_to['shipping_method_id'])));

                // If there was an error getting the ship date, then skip to the next recipient.
                if ($response['status'] == 'error') {
                    continue;
                }

                $ship_date = $response['ship_date'];

            // Otherwise the arrival date is not at once, so if a shipping method was found,
            // then work backwards from arrival date, using the transit time, in order to calculate the ship date.
            } else if ($ship_to['shipping_method_id'] != '') {

                // Start the total shipping days off with the base transit days for the shipping method.
                $total_shipping_days = $ship_to['base_transit_days'];

                // If adjust transit is enabled for the shipping method,
                // and a country was found, then also add transit adjustment days
                // for the recipient's country.
                if (($ship_to['adjust_transit'] == 1) && ($ship_to['country_id'] != '')) {
                    $total_shipping_days += $ship_to['transit_adjustment_days'];
                }

                $date = '';
                $count = 0;

                while ($count <= $total_shipping_days) {
                    // If date is blank, then set date to arrival date.
                    if ($date == '') {
                        $date = $ship_to['arrival_date'];
                        
                    // Otherwise date is not blank, so set date to previous day.
                    } else {
                        $date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
                    }
                    
                    $day_name = date('l', strtotime($date));
                    
                    // If date is not an excluded date, then increase count
                    if (
                        (($day_name != 'Sunday') || ($ship_to['transit_on_sunday'] == 1))
                        && (($day_name != 'Saturday') || ($ship_to['transit_on_saturday'] == 1))
                        &&
                        (
                            (isset($excluded_transit_dates[$ship_to['shipping_method_id']]) == false)
                            || (in_array($date, $excluded_transit_dates[$ship_to['shipping_method_id']]) == false)
                        )
                    ) {
                        $count++;
                    }
                }

                $ship_date = $date;

                // If the arrival date that the customer requested is equal to
                // an active arrival date in the system, then continue to check if
                // the ship date should be adjusted.
                if (
                    db_value(
                        "SELECT COUNT(*)
                        FROM arrival_dates
                        WHERE
                            (status = 'enabled')
                            AND (start_date <= CURRENT_DATE())
                            AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())
                            AND (arrival_date = '" . $ship_to['arrival_date'] . "')")
                    > 0
                ) {
                    $zip_code_prefix = mb_substr($ship_to['zip_code'], 0, 3);

                    // Check if there is a ship date adjustment for the zip code prefix
                    // and shipping method.
                    $adjustment = db_value(
                        "SELECT adjustment
                        FROM ship_date_adjustments
                        WHERE
                            (zip_code_prefix = '" . escape($zip_code_prefix) . "')
                            AND (shipping_method_id = '" . $ship_to['shipping_method_id'] . "')");

                    // If an adjustment was found then adjust the ship date.
                    if ($adjustment) {
                        // If the adjustment is negative then adjust the ship date into the past.
                        if ($adjustment < 0) {
                            $ship_date = date('Y-m-d', strtotime($adjustment . ' day', strtotime($ship_date)));

                        // Otherwise the adjustment is positive,
                        // so adjust the ship date into the future.
                        } else {
                            $ship_date = date('Y-m-d', strtotime('+' . $adjustment . ' day', strtotime($ship_date)));
                        }

                        $order_date_for_comparison = date('Y-m-d', $order_date);

                        // If the adjusted ship date is before the order date,
                        // then set it to the order date.
                        if ($ship_date < $order_date_for_comparison) {
                            $ship_date = $order_date_for_comparison;
                        }
                    }
                }
            }

            // Now that we know the ship date, let's figure out the estimated delivery date.

            $delivery_date = '';

            // If the requested arrival date is at once, and we found the shipping method, then
            // determine the estimated delivery date by looking at the ship date and transit time.
            if ($ship_to['arrival_date'] == '0000-00-00' and $ship_to['shipping_method_id']) {

                // Start the total shipping days off with the base transit days for the shipping
                // method.
                $total_shipping_days = $ship_to['base_transit_days'];

                // If adjust transit is enabled for the shipping method, and a country was found,
                // then also add transit adjustment days for the recipient's country.
                if ($ship_to['adjust_transit'] and $ship_to['country_id']) {
                    $total_shipping_days += $ship_to['transit_adjustment_days'];
                }

                $date = '';
                $count = 0;

                while ($count <= $total_shipping_days) {

                    // If date is blank, then set starting date to ship date.
                    if ($date == '') {
                        $date = $ship_date;
                        
                    // Otherwise date is not blank, so set date to next day.
                    } else {
                        $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                    }
                    
                    $day_name = date('l', strtotime($date));
                    
                    // If date is not an excluded date, then increase count
                    if (
                        (($day_name != 'Sunday') || ($ship_to['transit_on_sunday'] == 1))
                        && (($day_name != 'Saturday') || ($ship_to['transit_on_saturday'] == 1))
                        &&
                        (
                            (isset($excluded_transit_dates[$ship_to['shipping_method_id']]) == false)
                            || (in_array($date, $excluded_transit_dates[$ship_to['shipping_method_id']]) == false)
                        )
                    ) {
                        $count++;
                    }
                }

                $delivery_date = $date;

            // Otherwise the requested arrival date is not at once, so set the estimated delivery
            // date to the requested arrival date (e.g. Christmas).
            } else {
                $delivery_date = $ship_to['arrival_date'];
            }

            // Update the dates for the ship to.
            db(
                "UPDATE ship_tos SET
                    ship_date = '" . e($ship_date) . "',
                    delivery_date = '" . e($delivery_date) . "'
                WHERE id = '" . $ship_to['id'] . "'");
        }
    }

    // Check if there are auto e-mail campaigns that should be created based on an order being completed.
    create_auto_email_campaigns(array(
        'action' => 'order_completed',
        'contact_id' => $contact_id));
    
    // initialize array that will be used to store all of the contact groups that the contact should be added to
    $contact_groups = array();
    
    // intialize variable to keep track of the total reward points that products should give for this order
    $reward_points = 0;
    
    // loop through all ordered products in order to determine if things need to be done for each product
    foreach ($products as $product) {
        $product_id = $product['product_id'];
        $name = $product['product_name'];
        $quantity = $product['quantity'];
        $product_price = sprintf("%01.2lf", $product['price'] / 100);
        $product_tax = sprintf("%01.2lf", $product['tax'] / 100);
        $contact_group_id = $product['contact_group_id'];
        $membership_renewal = $product['membership_renewal'];
        $grant_private_access = $product['grant_private_access'];
        $private_folder = $product['private_folder'];
        $private_days = $product['private_days'];
        $send_to_page = $product['send_to_page'];
        $inventory = $product['inventory'];
        $inventory_quantity = $product['inventory_quantity'];
        $out_of_stock = $product['out_of_stock'];
        $calendar_event_id = $product['calendar_event_id'];
        $recurrence_number = $product['recurrence_number'];
        $reservations = $product['reservations'];
        $limit_reservations = $product['limit_reservations'];
        $number_of_initial_spots = $product['number_of_initial_spots'];
        
        // if inventory is enabled for this product and the inventory quantity is not already zero then decrement inventory quantity
        if (($inventory == 1) && ($inventory_quantity != 0)) {
            // the single quotes around the quantity are necessary in order to prevent MySQL from doing strange things
            $query = "UPDATE products SET inventory_quantity = (inventory_quantity - '" . $quantity . "') WHERE id = '" . $product_id . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            //if quantity is equal to product inventory quantity than add product to out of stock. so we can find product where out of stock page and welcome page.
            if($quantity == $inventory_quantity){
                $query = "UPDATE products SET 
                            out_of_stock = '1', 
                            out_of_stock_timestamp = UNIX_TIMESTAMP() 
                            WHERE id = '" . $product_id . "'";
                            
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
        
        
        // if calendars is enabled
        // and this order item is for a calendar event reservation,
        // and reservations is still enabled for the calendar event,
        // then determine if we should decrement number of remaining spots,
        // and also create auto e-mail campaigns if necessary.
        if (
            (CALENDARS == TRUE)
            && ($calendar_event_id != '')
            && ($reservations == 1)
        ) {
            // If limit reservations is still enabled,
            // then decrement number of remaining spots by the quantity.
            if ($limit_reservations == 1) {
                // check if there is an existing remaining spots record
                $query =
                    "SELECT calendar_event_id
                    FROM remaining_reservation_spots
                    WHERE
                        (calendar_event_id = '" . $calendar_event_id . "')
                        AND (recurrence_number = '" . $recurrence_number . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if a remaining spots record was found then decrement it by the quantity
                if (mysqli_num_rows($result) > 0) {
                    $query =
                        "UPDATE remaining_reservation_spots
                        SET number_of_remaining_spots = (number_of_remaining_spots - $quantity)
                        WHERE
                            (calendar_event_id = '" . $calendar_event_id . "')
                            AND (recurrence_number = '" . $recurrence_number . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                // else a remaining spots record was not found, so create new record using number of initial spots
                } else {
                    $number_of_remaining_spots = $number_of_initial_spots - 1;
                    
                    $query =
                        "INSERT INTO remaining_reservation_spots (
                            calendar_event_id,
                            recurrence_number,
                            number_of_remaining_spots)
                        VALUES (
                            '" . $calendar_event_id . "',
                            '" . $recurrence_number . "',
                            '" . $number_of_remaining_spots . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }

            // Check if there are auto e-mail campaigns that should be created based on this calendar event being reserved.
            create_auto_email_campaigns(array(
                'action' => 'calendar_event_reserved',
                'action_item_id' => $calendar_event_id,
                'calendar_event_recurrence_number' => $recurrence_number,
                'contact_id' => $contact_id));
        }
        
        // if contact group has not already been added to array, add it to array
        if (in_array($contact_group_id, $contact_groups) == false) {
            $contact_groups[] = $contact_group_id;
        }

        // if product is a membership renewal product
        if ($membership_renewal) {
            // get member id for user
            $query = "SELECT member_id, expiration_date " .
                     "FROM contacts " .
                     "WHERE id = '$contact_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $row = mysqli_fetch_assoc($result);
            $member_id = $row['member_id'];
            $expiration_date = $row['expiration_date'];

            // if user does not have a member id, set member id to the order's reference code and set new member id for user
            if (!$member_id) {
                $member_id = $reference_code;
                $query = "UPDATE contacts SET member_id = '" . escape($member_id) . "' WHERE id = '$contact_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            }

            // get current date
            $current_date = date('Y-m-d');

            // if expiration date is in the past or is today, then add the number of membership days to current date
            if ($expiration_date <= $current_date) {
                $new_expiration_date = date('Y-m-d', strtotime($current_date) + ($membership_renewal * $quantity * 86400));

            // else expiration date is in the future, so add the number of membership days to expiration date
            } else {
                $new_expiration_date = date('Y-m-d', strtotime($expiration_date) + ($membership_renewal * $quantity * 86400));
            }

            // update expiration date for member
            $query = "UPDATE contacts SET expiration_date = '$new_expiration_date' WHERE member_id = '" . escape($member_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            
            // if a membership contact group is selected and membership contact group has not already been added to the array,
            // then add membership contact group to array, so that contact is added to contact group
            if ((MEMBERSHIP_CONTACT_GROUP_ID != 0) && (in_array(MEMBERSHIP_CONTACT_GROUP_ID, $contact_groups) == false)) {
                $contact_groups[] = MEMBERSHIP_CONTACT_GROUP_ID;
            }
        }

        // if product grants private access, give user access to private folder and assign send_to_page for user
        if ($grant_private_access) {
            // Get access control values for this user and folder.
            $row = db_item(
                "SELECT 
                    aclfolder_rights AS rights,
                    expiration_date
                FROM aclfolder
                WHERE
                    (aclfolder_user = '$user_id')
                    AND (aclfolder_folder = '$private_folder')");

            $rights = $row['rights'];
            $expiration_date = $row['expiration_date'];

            // If the user does not have edit rights and does not have infinite private rights already,
            // then continue to give user private access or extend it.
            if (
                ($rights != 2)
                && 
                (
                    ($rights != 1)
                    || ($expiration_date != '0000-00-00')
                )
            ) {
                // Delete existing access control record if one exists, so we can add a new one.
                db(
                    "DELETE FROM aclfolder
                    WHERE
                        (aclfolder_user = '$user_id')
                        AND (aclfolder_folder = '$private_folder')");

                $new_expiration_date = '';

                // If the private access is limited in length, then prepare new expiration date.
                if ($private_days) {
                    // Get current date.
                    $current_date = date('Y-m-d');

                    // If no existing access control record was found or the existing expiration date is in the past or is today,
                    // then add the number of private days to current date.
                    if (
                        ($expiration_date == '')
                        || ($expiration_date <= $current_date)
                    ) {
                        $new_expiration_date = date('Y-m-d', strtotime($current_date) + ($private_days * $quantity * 86400));

                    // Otherwise the expiration date is in the future, so add the number of private days to the expiration date.
                    } else {
                        $new_expiration_date = date('Y-m-d', strtotime($expiration_date) + ($private_days * $quantity * 86400));
                    }
                }

                // Add access control record in order to give user private access.
                db(
                    "INSERT INTO aclfolder (
                        aclfolder_user,
                        aclfolder_folder,
                        aclfolder_rights,
                        expiration_date)
                    VALUES (
                        '$user_id',
                        '$private_folder',
                        '1',
                        '$new_expiration_date')");
            }

            // if a send to page is set for this product, set send to page for user
            if ($send_to_page) {
                $query = "UPDATE user SET user_home = '$send_to_page' WHERE user_id = '$user_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            }
        }
        
        // if the reward program is enabled,
        // and this product gives reward points,
        // and the visitor is logged in,
        // then increment reward points (multiply by quantity)
        if (
            (ECOMMERCE_REWARD_PROGRAM == TRUE)
            && ($product['reward_points'] != 0)
        ) {
            $reward_points = $reward_points + ($product['reward_points'] * $quantity);
        }

        // If this is a gift card product, then create gift card and email recipient.
        if ($product['gift_card']) {
            // If the quantity is 100 or less, then set the number of gift cards to the quantity.
            if ($quantity <= 100) {
                $number_of_gift_cards = $quantity;
                
            // Otherwise the quantity is greater than 100, so set the number of gift cards to 100.
            // We do this in order to prevent a ton of forms from appearing and causing a slowdown.
            } else {
                $number_of_gift_cards = 100;
            }
            
            // Loop through all quantities in order to create gift card and email recipients.
            for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
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
                        (order_item_id = '" . $product['order_item_id'] . "')
                        AND (quantity_number = '" . $quantity_number . "')");

                $code = generate_gift_card_code();

                $expiration_date = '';

                // If a number of validity days has been entered in the settings,
                // then calculate expiration date.
                if (ECOMMERCE_GIFT_CARD_VALIDITY_DAYS) {
                    // If the delivery date is immediate, then set expiration date,
                    // based on today as the start date.
                    if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                        $expiration_date = date('Y-m-d', strtotime('+' . ECOMMERCE_GIFT_CARD_VALIDITY_DAYS . ' day'));

                    // Otherwise the delivery date is in the future, so set expiration date,
                    // based on the delivery date as the start date.
                    } else {
                        $expiration_date = date('Y-m-d', strtotime('+' . ECOMMERCE_GIFT_CARD_VALIDITY_DAYS . ' day', strtotime($order_item_gift_card['delivery_date'])));
                    }
                }

                db(
                    "INSERT INTO gift_cards (
                        code,
                        amount,
                        balance,
                        expiration_date,
                        order_id,
                        order_item_id,
                        quantity_number,
                        from_name,
                        recipient_email_address,
                        message,
                        delivery_date,
                        created_user_id,
                        created_timestamp,
                        last_modified_user_id,
                        last_modified_timestamp)
                    VALUES (
                        '" . $code . "',
                        '" . $product['price'] . "',
                        '" . $product['price'] . "',
                        '" . $expiration_date . "',
                        '" . $_SESSION['ecommerce']['order_id'] . "',
                        '" . $product['order_item_id'] . "',
                        '" . $quantity_number . "',
                        '" . escape($order_item_gift_card['from_name']) . "',
                        '" . escape($order_item_gift_card['recipient_email_address']) . "',
                        '" . escape($order_item_gift_card['message']) . "',
                        '" . $order_item_gift_card['delivery_date'] . "',
                        '" . USER_ID . "',
                        UNIX_TIMESTAMP(),
                        '" . USER_ID . "',
                        UNIX_TIMESTAMP())");

                log_activity('gift card (' . output_gift_card_code($code) . ') was created because gift card product was ordered', $_SESSION['sessionusername']);

                // Create an array that will store the fields for variables that need to be replaced.
                $fields = array();

                $fields[] = array(
                    'name' => 'code',
                    'data' => output_gift_card_code($code),
                    'type' => '');

                // We remove ".00" from the end of the value if it exists.
                
                $fields[] = array(
                    'name' => 'amount',
                    'data' => preg_replace('~\.0+$~', '', number_format($product['price'] / 100, 2)),
                    'type' => '');

                $fields[] = array(
                    'name' => 'from_name',
                    'data' => $order_item_gift_card['from_name'],
                    'type' => '');

                $fields[] = array(
                    'name' => 'recipient_email_address',
                    'data' => $order_item_gift_card['recipient_email_address'],
                    'type' => '');

                $fields[] = array(
                    'name' => 'message',
                    'data' => $order_item_gift_card['message'],
                    'type' => '');

                $delivery_date = $order_item_gift_card['delivery_date'];

                // If there is no delivery date, then change it from 0000-00-00 to blank,
                // so that the admin can use a conditional structure to output "Immediate".
                // Example: [[^^delivery_date^^||Immediate]] 
                if ($delivery_date == '0000-00-00') {
                    $delivery_date = '';
                }

                $fields[] = array(
                    'name' => 'delivery_date',
                    'data' => $delivery_date,
                    'type' => 'date');

                // Replace variables in subject (e.g. ^^code^^).
                $subject = replace_variables(array(
                    'content' => $product['gift_card_email_subject'],
                    'fields' => $fields,
                    'format' => 'plain_text'));

                // If plain text was selected for the format, then store body in variable
                // and clear page id so that we don't store it with the e-mail campaign.
                if ($product['gift_card_email_format'] == 'plain_text') {
                    $body = $product['gift_card_email_body'];
                    $product['gift_card_email_page_id'] = '';

                // Otherwise HTML was selected for the format, so prepare body for that format.
                } else {
                    require_once(dirname(__FILE__) . '/get_page_content.php');

                    // Get html for page.
                    $body = get_page_content($product['gift_card_email_page_id'], $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true);
                    
                    // Find if there is a base tag in the HTML.
                    $base_in_html = preg_match('/<\s*base\s+[^>]*href\s*=\s*["\'](?:http:\/\/|https:\/\/|ftp:\/\/).*?["\']/is', $body);

                    // If there is not a base tag in the HTML, add base tag and convert relative links to absolute links.
                    if (!$base_in_html) {
                        $base = '<head>' . "\n" . '<base href="' . URL_SCHEME . HOSTNAME_SETTING . '/" />';
                        $body = preg_replace('/<head>/i', $base, $body);

                        // Change relative URLs to absolute URLs for links.
                        $body = preg_replace('/(<\s*a\s+[^>]*href\s*=\s*["\'])(?!ftp:\/\/|https:\/\/|mailto:|http:\/\/)(?:\/|\.\.\/|\.\/|)(.*?["\'].*?>)/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);

                        // Change relative URLs to absolute URLs for images.
                        $body = preg_replace('/(<\s*img\s+[^>]*src\s*=\s*["\'])(?!http:\/\/|https:\/\/)(?:\/|\.\.\/|\.\/|)(.*?["\'].*?>)/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);

                        // Change relative URLs to absolute URLs for CSS background images.
                        $body = preg_replace('/(background-image\s*:\s*url\s*\(\s*(?:"|\'|))(?!http:\/\/|https:\/\/)(?:\/|\.\.\/|\.\/|)(.*?(?:"|\'|).*?\))/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);

                        // Change relative URLs to absolute URLs for HTML background images.
                        $body = preg_replace('/(background\s*=\s*["\'])(?!http:\/\/|https:\/\/)(?:\/|\.\.\/|\.\/|)(.*?["\'])/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);
                    }
                    
                    // get URL for page
                    $page_url = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_email_campaign.php?r=<reference_code></reference_code>';
                    
                    $footer =
                        '<div class="software_email_footer" style="font-family: arial; font-size: 11px; color: #666666; text-align: center; background-color: #ffffff; padding: 5px; margin-top: 15px">
                            <a href="' . $page_url . '" style="color: #666666">View this email at our site</a><br>
                        </div>
                        </body>';
                    $body = preg_replace('/<\/body>/i', $footer, $body);
                }

                // Wrap long lines (RFC 821).
                $body = wordwrap($body, 900, "\n", 1);

                // Replace variables in body (e.g. ^^code^^).
                $body = replace_variables(array(
                    'content' => $body,
                    'fields' => $fields,
                    'format' => $product['gift_card_email_format']));

                $start_date_and_time = '';

                // If the customer set a delivery date, then prepare email campaign start date and time
                // to 12 PM on that date.  If the customer did not set a delivery date, then we setup
                // the campaign to be sent immediately.
                if ($order_item_gift_card['delivery_date'] != '0000-00-00') {
                    $start_date_and_time = $order_item_gift_card['delivery_date'] . ' 12:00:00';
                }
                
                // Create e-mail campaign.
                db(
                    "INSERT INTO email_campaigns (
                        type,
                        action,
                        from_name,
                        from_email_address,
                        subject,
                        format,
                        body,
                        page_id,
                        start_time,
                        status,
                        purpose,
                        created_timestamp,
                        last_modified_timestamp)
                    VALUES (
                        'automatic',
                        'gift_card_ordered',
                        '" . escape(ORGANIZATION_NAME) . "',
                        '" . escape(EMAIL_ADDRESS) . "',
                        '" . escape($subject) . "',
                        '" . $product['gift_card_email_format'] . "',
                        '" . escape($body) . "',
                        '" . $product['gift_card_email_page_id'] . "',
                        '" . $start_date_and_time . "',
                        'ready',
                        'transactional',
                        UNIX_TIMESTAMP(),
                        UNIX_TIMESTAMP())");
                
                $email_campaign_id = mysqli_insert_id(db::$con);

                // Create record for e-mail recipient.
                db(
                    "INSERT INTO email_recipients (
                        email_campaign_id,
                        type,
                        email_address)
                    VALUES (
                        '$email_campaign_id',
                        'automatic',
                        '" . escape($order_item_gift_card['recipient_email_address']) . "')");
                
                log_activity('auto campaign was created because gift card was ordered (' . output_gift_card_code($code) . ')', $_SESSION['sessionusername']);
            }
        }

        // If this product creates/updates a submitted a form, adds a comment,
        // or emails a page, then prepare variable fields where variables can
        // be replaced with dynamic data from the order.
        if (
            ($product['submit_form'])
            || ($product['add_comment'])
            || ($product['email_page'])
        ) {
            $variable_fields = array();

            $variable_fields[] = array('name' => 'billing_salutation', 'data' => $billing_salutation);
            $variable_fields[] = array('name' => 'billing_first_name', 'data' => $billing_first_name);
            $variable_fields[] = array('name' => 'billing_last_name', 'data' => $billing_last_name);
            $variable_fields[] = array('name' => 'billing_company', 'data' => $billing_company);
            $variable_fields[] = array('name' => 'billing_address_1', 'data' => $billing_address_1);
            $variable_fields[] = array('name' => 'billing_address_2', 'data' => $billing_address_2);
            $variable_fields[] = array('name' => 'billing_city', 'data' => $billing_city);
            $variable_fields[] = array('name' => 'billing_state', 'data' => $billing_state);
            $variable_fields[] = array('name' => 'billing_zip_code', 'data' => $billing_zip_code);
            $variable_fields[] = array('name' => 'billing_country', 'data' => $billing_country);
            $variable_fields[] = array('name' => 'billing_phone_number', 'data' => $billing_phone_number);
            $variable_fields[] = array('name' => 'billing_fax_number', 'data' => $billing_fax_number);
            $variable_fields[] = array('name' => 'billing_email_address', 'data' => $billing_email_address);
            $variable_fields[] = array('name' => 'custom_field_1', 'data' => $custom_field_1);
            $variable_fields[] = array('name' => 'custom_field_2', 'data' => $custom_field_2);
            $variable_fields[] = array('name' => 'po_number', 'data' => $po_number);
            $variable_fields[] = array('name' => 'total', 'data' => sprintf("%01.2lf", $total / 100));
            $variable_fields[] = array('name' => 'order_number', 'data' => $order_number);
            $variable_fields[] = array('name' => 'order_date_and_time', 'data' => date('Y-m-d H:i:s', $order_date), 'type' => 'date and time');
            $variable_fields[] = array('name' => 'quantity', 'data' => $quantity);

            // If just one quantity was ordered, then set plural suffix to blank.
            if ($quantity == 1) {
                $variable_fields[] = array('name' => 'quantity_plural_suffix', 'data' => '');
                $variable_fields[] = array('name' => 'quantity_plural_suffix_es', 'data' => '');

            // Otherwise more than one quantity was ordered, so set plural suffixes.
            } else {
                $variable_fields[] = array('name' => 'quantity_plural_suffix', 'data' => 's');
                $variable_fields[] = array('name' => 'quantity_plural_suffix_es', 'data' => 'es');
            }

            // Get form data items for product form in order to add variable fields for them.
            $product_form_data_items = db_items(
                "SELECT
                    quantity_number,
                    form_field_id,
                    data,
                    name,
                    type
                FROM form_data
                WHERE
                    (order_item_id = '" . $product['order_item_id'] . "')
                    AND (name != '')
                ORDER BY id ASC");

            $product_forms = array();

            // Loop through form data items in order to prepare data for each one,
            // because some fields might have multiple values/records (e.g. check box groups).
            foreach ($product_form_data_items as $product_form_data_item) {
                $quantity_number = $product_form_data_item['quantity_number'];

                // If there is already a value for this field, then that means,
                // this field has multiple values, so add comma and this additional value.
                if (isset($product_forms[$quantity_number][$product_form_data_item['form_field_id']]) == true) {
                    $product_forms[$quantity_number][$product_form_data_item['form_field_id']]['data'] .= ', ' . $product_form_data_item['data'];

                // Otherwise, there is not already a value for this field, so add field to array.
                } else {
                    $product_forms[$quantity_number][$product_form_data_item['form_field_id']] = array();
                    $product_forms[$quantity_number][$product_form_data_item['form_field_id']]['name'] = $product_form_data_item['name'];
                    $product_forms[$quantity_number][$product_form_data_item['form_field_id']]['data'] = $product_form_data_item['data'];
                    $product_forms[$quantity_number][$product_form_data_item['form_field_id']]['type'] = $product_form_data_item['type'];
                }
            }

            $product_form_variable_fields = array();

            // Loop through each product form to prepare variables.
            foreach ($product_forms as $quantity_number => $product_form) {
                // Loop through the product form fields, in order to add variable fields for them.
                foreach ($product_form as $product_form_field) {
                    $product_form_variable_fields[$quantity_number][] = array('name' => $product_form_field['name'], 'data' => $product_form_field['data'], 'type' => $product_form_field['type']);
                }
            }
        }

        // Create an array that will be used to keep track of whether a submitted form
        // was created or updated for each quantity number, and which submitted form
        // was created/updated.  We will need this info later when we add a comment.
        $forms = array();

        // If this product submits a form, then deal with that.
        if (
            ($product['submit_form'])
            && ($product['submit_form_custom_form_page_id'])
            &&
            (
                ($product['submit_form_create'])
                || ($product['submit_form_update'])
            )
        ) {
            $add_watcher_user = array();

            // If the add watcher feature was used where a watcher is stored in the session
            // and then in the order item once the order item is added to the cart,
            // then get watcher info so watcher can be added to form and page,
            // and so it can be emailed.
            if ($product['add_watcher'] != '') {
                $add_watcher_user = db_item(
                    "SELECT
                        user_id AS id,
                        user_email AS email_address
                    FROM user
                    WHERE
                        (user_username = '" . escape($product['add_watcher']) . "')
                        OR (user_email = '" . escape($product['add_watcher']) . "')");
            }

            $pretty_urls = check_if_pretty_urls_are_enabled($product['submit_form_custom_form_page_id']);

            // If the quantity type is "one form per quantity",
            // then set the number of forms to the quantity.
            if ($product['submit_form_quantity_type'] == 'One Form per Quantity') {
                $number_of_forms = $quantity;
                
            // Otherwise the quantity type is "one form per product",
            // so set the number of forms to one.
            } else  {
                $number_of_forms = 1;
            }

            for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {

                // Assume that a submitted form was not updated until we determine that we need to do that.
                // We use this in order to determine if we need to create a form.
                $submitted_form_updated = false;

                $product_form_quantity_number = 0;

                // If this is like the 2nd, 3rd or etc. form being submitted,
                // and a product form exists for this quantity number,
                // then remember that so that later we know we can replace variables for it.
                if (
                    ($quantity_number != 1)
                    and isset($product_form_variable_fields[$quantity_number])
                ) {
                    $product_form_quantity_number = $quantity_number;

                // Otherwise if a product form exists for the first quantity,
                // then remember that so that later we know we can replace variables for it.
                } else if (isset($product_form_variable_fields[1])) {
                    $product_form_quantity_number = 1;
                }

                // If submit form update is enabled, and the where field is set,
                // then determine if we should update a submitted form.
                if (
                    ($product['submit_form_update'])
                    && ($product['submit_form_update_where_field'])
                ) {
                    $where_field = $product['submit_form_update_where_field'];
                    $where_value = $product['submit_form_update_where_value'];

                    // If there is a where value, then replace mail-merge fields
                    // in it so things like the product form reference code will be replaced
                    // with the value that the customer entered.
                    if ($where_value != '') {

                        $where_value = replace_variables(array(
                            'content' => $where_value,
                            'fields' => $variable_fields,
                            'format' => 'plain_text'));

                        // If a product form was found, then replace variables for it.
                        if ($product_form_quantity_number) {
                            $where_value = replace_variables(array(
                                'content' => $where_value,
                                'fields' => $product_form_variable_fields[$product_form_quantity_number],
                                'format' => 'plain_text'));
                        }

                    }

                    // If the where field is the reference code field,
                    // then prepare the where field for that system field.
                    if ($where_field == 'reference_code') {
                        $sql_where = "(reference_code = '" . e($where_value) . "')";

                    // Otherwise the where field is a custom field,
                    // so prepare the where field for that custom field.
                    } else {
                        // If the value is blank, then prepare extra null check along with blank check.
                        if ($where_value == '') {
                            $sql_where =
                                "(
                                    ((SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . e($where_field) . "') LIMIT 1) = '" . e($where_value) . "')
                                    OR ((SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . e($where_field) . "') LIMIT 1) IS NULL)
                                )";

                        // Otherwise the value is not blank, so just do straight comparison.
                        } else {
                            $sql_where = "((SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . e($where_field) . "') LIMIT 1) = '" . e($where_value) . "')";
                        }
                    }

                    // Multiple submitted forms might match, so we just get the oldest one,
                    // so in case someone is using this feature with submitted form inventories,
                    // we use the oldest submitted form in the inventory.
                    $submitted_form_id = db_value(
                        "SELECT id
                        FROM forms
                        WHERE
                            (page_id = '" . $product['submit_form_custom_form_page_id'] . "')
                            AND $sql_where
                        ORDER BY submitted_timestamp
                        LIMIT 1");

                    // If a submitted form was found, then update it.
                    if ($submitted_form_id) {
                        // Remember that a submitted form was updated, so we know further below to not create a form.
                        $submitted_form_updated = true;

                        // Remember that a form was updated for this quantity number,
                        // for use later when we add a comment.
                        $forms[$quantity_number] = array(
                            'id' => $submitted_form_id,
                            'type' => 'updated');

                        // Update last modified properties for submitted form.
                        db(
                            "UPDATE forms
                            SET
                                last_modified_user_id = '" . USER_ID . "',
                                last_modified_timestamp = UNIX_TIMESTAMP()
                            WHERE id = '$submitted_form_id'");

                        // Get valid submit form fields for this product so we can determine
                        // which fields need to be updated for submitted form.
                        $submit_form_fields = db_items(
                            "SELECT
                                product_submit_form_fields.form_field_id,
                                product_submit_form_fields.value,
                                form_fields.name,
                                form_fields.type,
                                form_fields.wysiwyg
                            FROM product_submit_form_fields
                            LEFT JOIN form_fields ON product_submit_form_fields.form_field_id = form_fields.id
                            WHERE
                                (form_fields.id IS NOT NULL)
                                AND (product_submit_form_fields.product_id = '$product_id')
                                AND (product_submit_form_fields.action = 'update')
                                AND (form_fields.type != 'information')
                                AND (form_fields.type != 'file upload')");

                        // Loop through the submit form fields, in order to update them.
                        foreach ($submit_form_fields as $submit_form_field) {
                            $value = '';

                            // If the value for this field, is just the quantity
                            // then this is a special type of field where we want to
                            // always increment the quantity and not just replace the current quantity,
                            // so get old quantity.
                            if ($submit_form_field['value'] == '^^quantity^^') {
                                $old_quantity = db_value(
                                    "SELECT data
                                    FROM form_data
                                    WHERE
                                        (form_id = '$submitted_form_id')
                                        AND (form_field_id = '" . $submit_form_field['form_field_id'] . "')");

                                // If the old quantity is numeric, then add the new quantity to it.
                                if (is_numeric($old_quantity) == true) {
                                    $value = $old_quantity + $quantity;
                                }
                            }

                            // If the value has not already been determine above (for quantity fields),
                            // then prepare value.
                            if ($value == '') {

                                $value = replace_variables(array(
                                    'content' => $submit_form_field['value'],
                                    'fields' => $variable_fields,
                                    'format' => 'plain_text'));

                                // If a product form was found, then replace variables for it.
                                if ($product_form_quantity_number) {
                                    $value = replace_variables(array(
                                        'content' => $value,
                                        'fields' => $product_form_variable_fields[$product_form_quantity_number],
                                        'format' => 'plain_text'));
                                }

                            }

                            // assume that the form data type is standard until we find out otherwise
                            $form_data_type = 'standard';
                            
                            // if the form field's type is date, date and time, or time, then set form data type to the form field type
                            if (
                                ($submit_form_field['type'] == 'date')
                                || ($submit_form_field['type'] == 'date and time')
                                || ($submit_form_field['type'] == 'time')
                            ) {
                                $form_data_type = $submit_form_field['type'];
                                
                            // else if the form field is a wysiwyg text area, then set type to html.
                            } elseif (($submit_form_field['type'] == 'text area') && ($submit_form_field['wysiwyg'] == 1)) {
                                $form_data_type = 'html';
                            }

                            // Delete existing form_data record for this field, if one exists.
                            db(
                                "DELETE FROM form_data
                                WHERE
                                    (form_id = '$submitted_form_id')
                                    AND (form_id != '0')
                                    AND (form_field_id = '" . $submit_form_field['form_field_id'] . "')");

                            // Insert form_data record for this field.
                            db(
                                "INSERT INTO form_data (
                                    form_id,
                                    form_field_id,
                                    data,
                                    name,
                                    type)
                                VALUES (
                                    '$submitted_form_id',
                                    '" . $submit_form_field['form_field_id'] . "',
                                    '" . escape($value) . "',
                                    '" . escape($submit_form_field['name']) . "',
                                    '$form_data_type')");
                        }

                        // If pretty URLs are enabled, then update address name.
                        if ($pretty_urls == true) {
                            update_submitted_form_address_name($submitted_form_id);
                        }
                    }
                }

                // If submit form create is enabled, and a submitted form was not updated up above,
                // then create a submitted form.
                if (
                    ($product['submit_form_create'])
                    && ($submitted_form_updated == false)
                ) {
                    // Get custom form info.
                    $custom_form_page = db_item(
                        "SELECT
                            page.page_id AS id,
                            custom_form_pages.submitter_email,
                            custom_form_pages.submitter_email_from_email_address,
                            custom_form_pages.submitter_email_subject,
                            custom_form_pages.submitter_email_format,
                            custom_form_pages.submitter_email_body,
                            custom_form_pages.submitter_email_page_id,
                            custom_form_pages.administrator_email,
                            custom_form_pages.administrator_email_to_email_address,
                            custom_form_pages.administrator_email_bcc_email_address,
                            custom_form_pages.administrator_email_subject,
                            custom_form_pages.administrator_email_format,
                            custom_form_pages.administrator_email_body,
                            custom_form_pages.administrator_email_page_id
                        FROM page
                        LEFT JOIN custom_form_pages ON page.page_id = custom_form_pages.page_id
                        WHERE
                            (page.page_id = '" . $product['submit_form_custom_form_page_id'] . "')
                            AND (page.page_type = 'custom form')");

                    // If the custom form page was found, then continue to create submitted form.
                    if ($custom_form_page['id']) {
                        $submitter_email = $custom_form_page['submitter_email'];
                        $submitter_email_from_email_address = $custom_form_page['submitter_email_from_email_address'];
                        $submitter_email_subject = $custom_form_page['submitter_email_subject'];
                        $submitter_email_format = $custom_form_page['submitter_email_format'];
                        $submitter_email_body = $custom_form_page['submitter_email_body'];
                        $submitter_email_page_id = $custom_form_page['submitter_email_page_id'];
                        $administrator_email = $custom_form_page['administrator_email'];
                        $administrator_email_to_email_address = $custom_form_page['administrator_email_to_email_address'];
                        $administrator_email_bcc_email_address = $custom_form_page['administrator_email_bcc_email_address'];
                        $administrator_email_subject = $custom_form_page['administrator_email_subject'];
                        $administrator_email_format = $custom_form_page['administrator_email_format'];
                        $administrator_email_body = $custom_form_page['administrator_email_body'];
                        $administrator_email_page_id = $custom_form_page['administrator_email_page_id'];

                        // Create record in database for submitted form.
                        db(
                            "INSERT INTO forms (
                                page_id,
                                complete,
                                user_id,
                                contact_id,
                                reference_code,
                                tracking_code,
                                affiliate_code,
                                http_referer,
                                ip_address,
                                submitted_timestamp,
                                last_modified_user_id,
                                last_modified_timestamp)
                            VALUES (
                                '" . $product['submit_form_custom_form_page_id'] . "',
                                '1',
                                '" . $user_id . "',
                                '" . $contact_id . "',
                                '" . generate_form_reference_code() . "',
                                '" . escape(get_tracking_code()) . "',
                                '" . escape(get_affiliate_code()) . "',
                                '" . escape($_SESSION['software']['http_referer']) . "',
                                IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0),
                                UNIX_TIMESTAMP(),
                                '" . $user_id . "',
                                UNIX_TIMESTAMP())");

                        $submitted_form_id = mysqli_insert_id(db::$con);
                        
                        // Get valid submit form fields for this product so we can determine
                        // which fields need to be set for submitted form.
                        $submit_form_fields = db_items(
                            "SELECT
                                product_submit_form_fields.form_field_id,
                                product_submit_form_fields.value,
                                form_fields.name,
                                form_fields.type,
                                form_fields.wysiwyg
                            FROM product_submit_form_fields
                            LEFT JOIN form_fields ON product_submit_form_fields.form_field_id = form_fields.id
                            WHERE
                                (form_fields.id IS NOT NULL)
                                AND (product_submit_form_fields.product_id = '$product_id')
                                AND (product_submit_form_fields.action = 'create')
                                AND (form_fields.type != 'information')
                                AND (form_fields.type != 'file upload')");

                        $administrator_email_addresses = array();

                        // Loop through the submit form fields, in order to set them.
                        foreach ($submit_form_fields as $submit_form_field) {

                            // Replace variables (e.g. ^^order_number^^) in the value.
                            $value = replace_variables(array(
                                'content' => $submit_form_field['value'],
                                'fields' => $variable_fields,
                                'format' => 'plain_text'));

                            // If a product form was found, then replace variables for it.
                            if ($product_form_quantity_number) {
                                $value = replace_variables(array(
                                    'content' => $value,
                                    'fields' => $product_form_variable_fields[$product_form_quantity_number],
                                    'format' => 'plain_text'));
                            }

                            // assume that the form data type is standard until we find out otherwise
                            $form_data_type = 'standard';
                            
                            // if the form field's type is date, date and time, or time, then set form data type to the form field type
                            if (
                                ($submit_form_field['type'] == 'date')
                                || ($submit_form_field['type'] == 'date and time')
                                || ($submit_form_field['type'] == 'time')
                            ) {
                                $form_data_type = $submit_form_field['type'];
                                
                            // else if the form field is a wysiwyg text area, then set type to html.
                            } elseif (($submit_form_field['type'] == 'text area') && ($submit_form_field['wysiwyg'] == 1)) {
                                $form_data_type = 'html';
                            }

                            // Insert form_data record for this field.
                            db(
                                "INSERT INTO form_data (
                                    form_id,
                                    form_field_id,
                                    data,
                                    name,
                                    type)
                                VALUES (
                                    '$submitted_form_id',
                                    '" . $submit_form_field['form_field_id'] . "',
                                    '" . escape($value) . "',
                                    '" . escape($submit_form_field['name']) . "',
                                    '$form_data_type')");

                            // if field is a pick list, checkbox or radio button, then add e-mail addressess
                            // for form field options to array if there are any,
                            // so that we can later send e-mails to those administrators
                            if (($submit_form_field['type'] == 'pick list') || ($submit_form_field['type'] == 'radio button') || ($submit_form_field['type'] == 'check box')) {
                                $email_addresses = db_values(
                                    "SELECT email_address
                                    FROM form_field_options
                                    WHERE
                                        (form_field_id = '" . $submit_form_field['form_field_id'] . "')
                                        AND (email_address != '')
                                        AND (value = '" . escape($value) . "')");
                                
                                foreach ($email_addresses as $email_address) {
                                    if (in_array($email_address, $administrator_email_addresses) == false) {
                                        $administrator_email_addresses[] = $email_address;
                                    }
                                }
                            }
                        }

                        // If pretty URLs are enabled, then update address name.
                        if ($pretty_urls == true) {
                            update_submitted_form_address_name($submitted_form_id);
                        }

                        // If the submitter email is enabled, then send it to the submitter.
                        if ($submitter_email == 1) {
                            // Prepare array to store email address for submitter and
                            // any watcher that was added through the hidden add watcher feature.
                            $submitter_and_watcher_email_addresses = array();

                            $submitter_and_watcher_email_addresses[] = $billing_email_address;

                            // If the add watcher feature was used and a user was found for the watcher,
                            // then add watcher's email address to array.
                            if ($add_watcher_user['id'] != '') {
                                $submitter_and_watcher_email_addresses[] = $add_watcher_user['email_address'];
                            }

                            // Loop through the submitter and watcher email addresses,
                            // in order to send a separate email to each one.  We don't just
                            // want to send one email with multiple tos because they can see eachother's
                            // email address that way.  We don't need to re-prepare the subject
                            // and body for every round of this loop, because they will be the same each time,
                            // however we are just doing it like that for now to save development time.
                            foreach ($submitter_and_watcher_email_addresses as $to_email_address) {
                                
                                if ($submitter_email_from_email_address != '') {
                                    $from_email_address = $submitter_email_from_email_address;
                                } else {
                                    $from_email_address = EMAIL_ADDRESS;
                                }
                                
                                // check the subject line for variable data and replace any variables with content from the submitted form
                                $submitter_email_subject = get_variable_submitted_form_data_for_content($product['submit_form_custom_form_page_id'], $submitted_form_id, $submitter_email_subject, $prepare_for_html = FALSE);

                                // if the format of the e-mail should be plain text, then prepare that
                                if ($submitter_email_format == 'plain_text') {

                                    // check the body for variable data and replace any variables with content from the submitted form
                                    $body = get_variable_submitted_form_data_for_content($product['submit_form_custom_form_page_id'], $submitted_form_id, $submitter_email_body, $prepare_for_html = FALSE);

                                // else the format of the e-mail should be HTML, so prepare that
                                } else {

                                    require_once(dirname(__FILE__) . '/get_page_content.php');

                                    $body = get_page_content($submitter_email_page_id, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true, array('form_id' => $submitted_form_id));
                                }
                                
                                email(array(
                                    'to' => $to_email_address,
                                    'from_name' => ORGANIZATION_NAME,
                                    'from_email_address' => $from_email_address,
                                    'subject' => $submitter_email_subject,
                                    'format' => $submitter_email_format,
                                    'body' => $body));

                            }
                        }
                        
                        // if an administrator e-mail is enabled and there is an e-mail address to e-mail, then send e-mail
                        if (($administrator_email == 1) && (($administrator_email_to_email_address != '') || ($administrator_email_bcc_email_address != '') || (count($administrator_email_addresses) > 0))) {
                            
                            // if there is a to e-mail address and it has not already been added to the array, then add it
                            if (($administrator_email_to_email_address != '') && (in_array($administrator_email_to_email_address, $administrator_email_addresses) == false)) {
                                $administrator_email_addresses[] = $administrator_email_to_email_address;
                            }
                            
                            // check the subject line for variable data and replace any variables with content from the submitted form
                            $administrator_email_subject = get_variable_submitted_form_data_for_content($product['submit_form_custom_form_page_id'], $submitted_form_id, $administrator_email_subject, $prepare_for_html = FALSE);

                            // if the format of the e-mail should be plain text, then prepare that
                            if ($administrator_email_format == 'plain_text') {

                                // check the body for variable data and replace any variables with content from the submitted form
                                $body = get_variable_submitted_form_data_for_content($product['submit_form_custom_form_page_id'], $submitted_form_id, $administrator_email_body, $prepare_for_html = FALSE);

                            // else the format of the e-mail should be HTML, so prepare that
                            } else {

                                require_once(dirname(__FILE__) . '/get_page_content.php');

                                $body = get_page_content($administrator_email_page_id, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true, array('form_id' => $submitted_form_id));

                            }

                            // In the past we would set the from info to the submitter's address (if one existed),
                            // however this caused issues with mail providers using DMARC (e.g. Yahoo, AOL),
                            // so we now send it from this site's info and add a reply to for the submitter (if exists).
                            
                            email(array(
                                'to' => $administrator_email_addresses,
                                'bcc' => $administrator_email_bcc_email_address,
                                'from_name' => ORGANIZATION_NAME,
                                'from_email_address' => EMAIL_ADDRESS,
                                'reply_to' => $billing_email_address,
                                'subject' => $administrator_email_subject,
                                'format' => $administrator_email_format,
                                'body' => $body));

                        }

                        // Check if there are auto e-mail campaigns that should be created based on this custom form being submitted.
                        create_auto_email_campaigns(array(
                            'action' => 'custom_form_submitted',
                            'action_item_id' => $product['submit_form_custom_form_page_id'],
                            'contact_id' => $contact_id));

                        // Remember that a form was created for this quantity number,
                        // for use later when we add a comment.
                        $forms[$quantity_number] = array(
                            'id' => $submitted_form_id,
                            'type' => 'created');

                    }
                }

                // If the add watcher feature has been used, and there is a page selected
                // for the add comment feature, and the page exists, then add watcher for that page.
                if (
                    ($add_watcher_user['id'])
                    && ($product['add_comment_page_id'])
                ) {
                    // Check if form item view page exists for the add comment page.
                    $add_watcher_page = db_item(
                        "SELECT page_id AS id
                        FROM page
                        WHERE
                            (page_id = '" . $product['add_comment_page_id'] . "')
                            AND (page_type = 'form item view')
                            AND (comments = '1')
                            AND (comments_watcher_email_page_id != '0')");

                    // If a page was found then continue to determine if we should add watcher.
                    if ($add_watcher_page['id']) {
                        // If the user is not already a watcher, then add watcher.
                        if (db_value(
                            "SELECT COUNT(*)
                            FROM watchers
                            WHERE
                                (user_id = '" . $add_watcher_user['id'] . "')
                                AND (page_id = '" . $add_watcher_page['id'] . "')
                                AND (item_id = '$submitted_form_id')
                                AND (item_type = 'submitted_form')") == 0
                        ) {
                            db(
                                "INSERT INTO watchers (
                                    user_id,
                                    page_id,
                                    item_id,
                                    item_type)
                                 VALUES (
                                    '" . $add_watcher_user['id'] . "',
                                    '" . $add_watcher_page['id'] . "',
                                    '$submitted_form_id',
                                    'submitted_form')");
                        }
                    }
                }

            }
            
        }

        // If add comment feature is enabled, then add comment.
        if ($product['add_comment'] and $product['add_comment_page_id']) {

            // Check if page exists by getting info for page that comment will be added to.
            $add_comment_page = db_item(
                "SELECT
                    page_id AS id,
                    page_type AS type,
                    comments_administrator_email_to_email_address,
                    comments_administrator_email_subject,
                    comments_administrator_email_conditional_administrators,
                    comments_submitter_email_page_id,
                    comments_watcher_email_page_id
                FROM page
                WHERE page_id = '" . $product['add_comment_page_id'] . "'");

            // If page was found, the continue to add comment.
            if ($add_comment_page['id']) {

                $comments_administrator_email_to_email_address = $add_comment_page['comments_administrator_email_to_email_address'];
                $comments_administrator_email_subject = $add_comment_page['comments_administrator_email_subject'];
                $comments_administrator_email_conditional_administrators = $add_comment_page['comments_administrator_email_conditional_administrators'];
                $comments_submitter_email_page_id = $add_comment_page['comments_submitter_email_page_id'];
                $comments_watcher_email_page_id = $add_comment_page['comments_watcher_email_page_id'];

                // If the quantity type is "one form per quantity",
                // then set the number of comments to the quantity.
                // Even though the "submit_form_quantity_type" field appears
                // to apply to the submit form feature, it also applies to the add
                // comment feature.
                if ($product['submit_form_quantity_type'] == 'One Form per Quantity') {
                    $number_of_comments = $quantity;
                    
                // Otherwise the quantity type is "one form per product",
                // so set the number of forms to one.
                } else  {
                    $number_of_comments = 1;
                }

                for ($quantity_number = 1; $quantity_number <= $number_of_comments; $quantity_number++) {

                    $submitted_form_id = $forms[$quantity_number]['id'];

                    if ($forms[$quantity_number]['type'] == 'updated') {
                        $submitted_form_updated = true;
                    } else {
                        $submitted_form_updated = false;
                    }

                    // If a comment should only be added when a submitted form is updated,
                    // and a submitted form was not updated for this quantity number,
                    // then don't add a comment for this quantity number,
                    // and skip to the next quantity number.
                    if (
                        $product['add_comment_only_for_submit_form_update']
                        and !$submitted_form_updated
                    ) {
                        continue;
                    }

                    // If the page is a form item view and a submitted form was updated above,
                    // then delete any records that might disallow new comments,
                    // because it is likely the customer has ordered something that should re-enable comments.
                    if (
                        ($add_comment_page['type'] == 'form item view')
                        && ($submitted_form_updated)
                    ) {
                        db(
                            "DELETE FROM allow_new_comments_for_items
                            WHERE
                                (page_id = '" . $add_comment_page['id'] . "')
                                AND (item_id = '$submitted_form_id')
                                AND (item_type = 'submitted_form')");
                    }

                    // Assume that there is no item or item type for this comment
                    // until we find out otherwise.
                    $item_id = 0;
                    $item_type = '';

                    // If the page is a form item view and a submitted form was created
                    // or updated above, then prepare item id and item type so that
                    // the comment is connected to the submitted form.
                    if (
                        ($add_comment_page['type'] == 'form item view')
                        && ($submitted_form_id)
                    ) {
                        $item_id = $submitted_form_id;
                        $item_type = 'submitted_form';
                    }

                    // Replace variables in name and message values.

                    $product_form_quantity_number = 0;

                    // If this is like the 2nd, 3rd or etc. comment being added,
                    // and a product form exists for this quantity number,
                    // then remember that so that later we know we can replace variables for it.
                    if (
                        ($quantity_number != 1)
                        and isset($product_form_variable_fields[$quantity_number])
                    ) {
                        $product_form_quantity_number = $quantity_number;

                    // Otherwise if a product form exists for the first quantity,
                    // then remember that so that later we know we can replace variables for it.
                    } else if (isset($product_form_variable_fields[1])) {
                        $product_form_quantity_number = 1;
                    }

                    $add_comment_name = replace_variables(array(
                        'content' => $product['add_comment_name'],
                        'fields' => $variable_fields,
                        'format' => 'plain_text'));

                    // If a product form was found, then replace variables for it.
                    if ($product_form_quantity_number) {
                        $add_comment_name = replace_variables(array(
                            'content' => $add_comment_name,
                            'fields' => $product_form_variable_fields[$product_form_quantity_number],
                            'format' => 'plain_text'));
                    }

                    $add_comment_message = replace_variables(array(
                        'content' => $product['add_comment_message'],
                        'fields' => $variable_fields,
                        'format' => 'plain_text'));

                    // If a product form was found, then replace variables for it.
                    if ($product_form_quantity_number) {
                        $add_comment_message = replace_variables(array(
                            'content' => $add_comment_message,
                            'fields' => $product_form_variable_fields[$product_form_quantity_number],
                            'format' => 'plain_text'));
                    }

                    // Add comment to database.
                    db(
                        "INSERT INTO comments (
                            page_id,
                            item_id,
                            item_type,
                            name,
                            message,
                            published,
                            created_timestamp,
                            last_modified_timestamp)
                         VALUES (
                            '" . $add_comment_page['id'] . "',
                            '$item_id',
                            '$item_type',
                            '" . e($add_comment_name) . "',
                            '" . e($add_comment_message) . "',
                            '1',
                            UNIX_TIMESTAMP(),
                            UNIX_TIMESTAMP())");

                    $comment_id = mysqli_insert_id(db::$con);

                    // Cancel any necessary scheduled comments,
                    // that are set to be cancelled if new comments are added.
                    db(
                        "UPDATE comments
                        SET
                            publish_date_and_time = '',
                            publish_cancel = ''
                        WHERE
                            (publish_cancel = '1')
                            AND (page_id = '" . $add_comment_page['id'] . "')
                            AND (item_id = '$item_id')
                            AND (item_type = '$item_type')");

                    // If the page is a form item view and a submitted form was created or updated.
                    // then update submitted form info.
                    if (
                        ($add_comment_page['type'] == 'form item view')
                        && ($submitted_form_id)
                    ) {
                        // Get the number of views so we do not lose that data when we delete record below.
                        $number_of_views = db_value("SELECT number_of_views FROM submitted_form_info WHERE (submitted_form_id = '$submitted_form_id') AND (page_id = '" . $add_comment_page['id'] . "')");

                        // get the number of published comments for this submitted form and page
                        $query =
                            "SELECT COUNT(*)
                            FROM comments
                            WHERE
                                (page_id = '" . $add_comment_page['id'] . "')
                                AND (item_id = '$submitted_form_id')
                                AND (item_type = 'submitted_form')
                                AND (published = '1')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);
                        $number_of_comments = $row[0];
                        
                        // delete the current record if one exists
                        $query = "DELETE FROM submitted_form_info WHERE (submitted_form_id = '$submitted_form_id') AND (page_id = '" . $add_comment_page['id'] . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // insert a new record
                        $query = 
                            "INSERT INTO submitted_form_info (
                                submitted_form_id,
                                page_id,
                                number_of_views,
                                number_of_comments,
                                newest_comment_id)
                             VALUES (
                                '$submitted_form_id',
                                '" . $add_comment_page['id'] . "',
                                '$number_of_views',
                                '$number_of_comments',
                                '$comment_id')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }

                    send_comment_email_to_administrators($comment_id);

                    // if this is a form item view, and if there is a page to send
                    // then send e-mail to submitter letting them know a comment has been added
                    if (($add_comment_page['type'] == 'form item view') && ($comments_submitter_email_page_id != '0')) {
                        send_comment_email_to_custom_form_submitter($comment_id);
                    }

                    // if there is a watcher page to send, then send e-mail to watchers
                    // letting them know a comment has been added
                    if ($comments_watcher_email_page_id != '0') {
                        send_comment_email_to_watchers($comment_id);
                    }

                }

            }
        }

        // if a page for e-mail page is selected, then e-mail page for product
        if ($product['email_page']) {
            // If bcc e-mail address was found for the product then use that.
            if ($product['email_bcc'] != '') {
                $bcc = $product['email_bcc'];

            // Otherwise a bcc e-mail address was not found for product so use global value.
            } else {
                $bcc = ECOMMERCE_EMAIL_ADDRESS;
            }

            $subject = db_value("SELECT page_title FROM page WHERE page_id = '" . $product['email_page'] . "'");

            // If the page did not have a title, then use default title.
            if ($subject == '') {
                $subject = 'Product Information';

            // Otherwise, the page had a title, so replace variables in subject.
            } else {
                $subject = replace_variables(array(
                    'content' => $subject,
                    'fields' => $variable_fields,
                    'format' => 'plain_text'));

                // If there is a product form for the first quantity, then
                // replace variables with product form values.
                if (isset($product_form_variable_fields[1])) {
                    $subject = replace_variables(array(
                        'content' => $subject,
                        'fields' => $product_form_variable_fields[1],
                        'format' => 'plain_text'));
                }

                // If a submitted form was created or updated for the first quantity,
                // then replace submitted form variables also.
                if (isset($forms[1])) {
                    $subject = get_variable_submitted_form_data_for_content($product['submit_form_custom_form_page_id'], $forms[1]['id'], $subject, $prepare_for_html = false);
                }
            }

            require_once(dirname(__FILE__) . '/get_page_content.php');

            // Get HTML body for email page.
            $body = get_page_content($product['email_page'], $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true);

            // Replace variables in body (e.g. ^^billing_email_address^^).
            // This will replace order-level fields outside and inside
            // any quantity row tags that might exist.
            $body = replace_variables(array(
                'content' => $body,
                'fields' => $variable_fields,
                'format' => 'html'));

            // If there are quantity row comments, that allows a row of content,
            // to be outputted for every quantity, then replace those areas of content.
            // This allows different product form or submitted form content to
            // appear for each quantity in the email body.

            preg_match_all('/<!-- Start Quantity Row -->(.*?)<!-- End Quantity Row -->/si', $body, $matches, PREG_SET_ORDER);

            // Loop through all the matches in order to prepare content and replace them.
            foreach ($matches as $match) {

                $tags = $match[0];
                $layout = $match[1];

                // Will contain content for all quantities.
                $content = '';

                // Loop through each quantity in order to prepare content for each quantity.
                for ($quantity_number = 1; $quantity_number <= $quantity; $quantity_number++) {

                    // Will contain content for just this quantity.
                    $quantity_content = $layout;

                    $product_form_quantity_number = 0;

                    // If this is the 2nd, 3rd or etc. quantity being prepared,
                    // and a product form exists for this quantity number,
                    // then remember that so that later we know we can replace variables for it.
                    if (
                        ($quantity_number != 1)
                        and isset($product_form_variable_fields[$quantity_number])
                    ) {
                        $product_form_quantity_number = $quantity_number;

                    // Otherwise if a product form exists for the first quantity,
                    // then remember that so that later we know we can replace variables for it.
                    } else if (isset($product_form_variable_fields[1])) {
                        $product_form_quantity_number = 1;
                    }

                    // If a product form was found for this quantity number,
                    // then replace variables for it.
                    if ($product_form_quantity_number) {
                        $quantity_content = replace_variables(array(
                            'content' => $quantity_content,
                            'fields' => $product_form_variable_fields[$product_form_quantity_number],
                            'format' => 'html'));
                    }

                    $submitted_form_id = 0;

                    // If this is the 2nd, 3rd or etc. quantity being prepared,
                    // and a submitted form was created or updated for this quantity number,
                    // then use that submitted form.
                    if (
                        ($quantity_number != 1)
                        and isset($forms[$quantity_number])
                    ) {
                        $submitted_form_id = $forms[$quantity_number]['id'];

                    // Otherwise if a submitted form was created or updated for the first quantity,
                    // then use that submitted form.
                    } else if (isset($forms[1])) {
                        $submitted_form_id = $forms[1]['id'];
                    }

                    // If there is a submitted form to use for this quantity number,
                    // then replace submitted form variables also.
                    if ($submitted_form_id) {
                        $quantity_content = get_variable_submitted_form_data_for_content($product['submit_form_custom_form_page_id'], $submitted_form_id, $quantity_content);
                    }

                    // Add the content for this quantity to the total content.
                    $content .= $quantity_content;

                }

                // Replace comment tags and layout content in the body
                // with the content for all quantities.
                $body = str_replace($tags, $content, $body);

            }

            // If there is a product form for the first quantity, then
            // replace variables with product form values.  Even though we have
            // done this above for quantity row tags, we have to do again,
            // because quantity row tags might not have been used,
            // or product form variables might appear in the content outside of those tags.
            if (isset($product_form_variable_fields[1])) {
                $body = replace_variables(array(
                    'content' => $body,
                    'fields' => $product_form_variable_fields[1],
                    'format' => 'html'));
            }

            // If a submitted form was created or updated for the first quantity,
            // then replace submitted form variables also.  Even though we have
            // done this above for quantity row tags, we have to do again,
            // because quantity row tags might not have been used,
            // or product form variables might appear in the content outside of those tags.
            if (isset($forms[1])) {
                $body = get_variable_submitted_form_data_for_content($product['submit_form_custom_form_page_id'], $forms[1]['id'], $body);
            }

            email(array(
                'to' => $billing_email_address,
                'bcc' => $bcc,
                'from_name' => ORGANIZATION_NAME,
                'from_email_address' => EMAIL_ADDRESS,
                'subject' => $subject,
                'format' => 'html',
                'body' => $body
            ));
        }

        // Check if there are auto e-mail campaigns that should be created based on this product being ordered.
        create_auto_email_campaigns(array(
            'action' => 'product_ordered',
            'action_item_id' => $product_id,
            'contact_id' => $contact_id));
    }

    // If the customer has a user account and all customers are granted private access,
    // then grant access to this user.
    if ($user_id and ECOMMERCE_PRIVATE_FOLDER_ID) {

        // Get access control values for this user and folder.
        $row = db_item(
            "SELECT 
                aclfolder_rights AS rights,
                expiration_date
            FROM aclfolder
            WHERE
                (aclfolder_user = '$user_id')
                AND (aclfolder_folder = '" . ECOMMERCE_PRIVATE_FOLDER_ID . "')");

        $rights = $row['rights'];
        $expiration_date = $row['expiration_date'];

        // If the user does not have edit rights and does not have infinite private rights already,
        // then continue to give user infinite private access.
        if (
            ($rights != 2)
            && 
            (
                ($rights != 1)
                || ($expiration_date != '0000-00-00')
            )
        ) {
            // Delete existing access control record if one exists, so we can add a new one.
            db(
                "DELETE FROM aclfolder
                WHERE
                    (aclfolder_user = '$user_id')
                    AND (aclfolder_folder = '" . ECOMMERCE_PRIVATE_FOLDER_ID . "')");

            // Add access control record in order to give user infinite private access.
            db(
                "INSERT INTO aclfolder (
                    aclfolder_user,
                    aclfolder_folder,
                    aclfolder_rights)
                VALUES (
                    '$user_id',
                    '" . ECOMMERCE_PRIVATE_FOLDER_ID . "',
                    '1')");
        }

    }
    
    // if the reward program is enabled,
    // and products gave reward points for this order,
    // and the visitor is logged in,
    // then add rewards points to user
    if (
        (ECOMMERCE_REWARD_PROGRAM == TRUE)
        && ($reward_points != 0)
        && ($user_id != '')
    ) {
        // get old reward points in order to get new reward points for user
        $query = "SELECT user_reward_points FROM user WHERE user_id = '" . $user_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $old_reward_points = $row['user_reward_points'];
        
        $new_reward_points = $old_reward_points + $reward_points;
        
        // update reward points for user
        $query = "UPDATE user SET user_reward_points = '" . $new_reward_points . "' WHERE user_id = '" . $user_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
        // if the user has met the goal,
        // and the user did not already meet the goal before this order,
        // then complete actions
        if (
            ($new_reward_points >= ECOMMERCE_REWARD_PROGRAM_POINTS)
            && ($old_reward_points < ECOMMERCE_REWARD_PROGRAM_POINTS)
        ) {
            // if the membership action is enabled, then grant or extend membership
            if (ECOMMERCE_REWARD_PROGRAM_MEMBERSHIP == TRUE) {
                // get membership information for user
                $query =
                    "SELECT
                        member_id,
                        expiration_date
                    FROM contacts
                    WHERE id = '" . $contact_id . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $member_id = $row['member_id'];
                $expiration_date = $row['expiration_date'];

                // if user does not have a member id, set member id to order number and set new member id for user
                if ($member_id == '') {
                    $member_id = $order_number;
                    $query = "UPDATE contacts SET member_id = '" . escape($member_id) . "' WHERE id = '" . $contact_id . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
                
                // if membership days is 0, then give lifetime membership (i.e. set expiration date to 0000-00-00)
                if (ECOMMERCE_REWARD_PROGRAM_MEMBERSHIP_DAYS == 0) {
                    $new_expiration_date = '0000-00-00';
                    
                // else membership days is greater than 0, so prepare expiration date
                } else {
                    // get current date
                    $current_date = date('Y-m-d');
                    
                    // if expiration date is in the past or is today, then add the number of membership days to current date
                    if ($expiration_date <= $current_date) {
                        $new_expiration_date = date('Y-m-d', strtotime($current_date) + (ECOMMERCE_REWARD_PROGRAM_MEMBERSHIP_DAYS * 86400));

                    // else expiration date is in the future, so add the number of membership days to expiration date
                    } else {
                        $new_expiration_date = date('Y-m-d', strtotime($expiration_date) + (ECOMMERCE_REWARD_PROGRAM_MEMBERSHIP_DAYS * 86400));
                    }
                }

                // update expiration date for member
                $query = "UPDATE contacts SET expiration_date = '" . $new_expiration_date . "' WHERE id = '" . $contact_id . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if a membership contact group is selected and membership contact group has not already been added to the array,
                // then add membership contact group to array, so that contact is added to contact group
                if ((MEMBERSHIP_CONTACT_GROUP_ID != 0) && (in_array(MEMBERSHIP_CONTACT_GROUP_ID, $contact_groups) == false)) {
                    $contact_groups[] = MEMBERSHIP_CONTACT_GROUP_ID;
                }
            }
            
            // if the e-mail action is enabled, then send e-mail
            if (
                (ECOMMERCE_REWARD_PROGRAM_EMAIL == TRUE)
                && (ECOMMERCE_REWARD_PROGRAM_EMAIL_PAGE_ID != 0)
            ) {

                require_once(dirname(__FILE__) . '/get_page_content.php');
                
                $body = get_page_content(ECOMMERCE_REWARD_PROGRAM_EMAIL_PAGE_ID, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = TRUE);
                
                email(array(
                    'to' => $billing_email_address,
                    'bcc' => ECOMMERCE_REWARD_PROGRAM_EMAIL_BCC_EMAIL_ADDRESS,
                    'from_name' => ORGANIZATION_NAME,
                    'from_email_address' => EMAIL_ADDRESS,
                    'subject' => ECOMMERCE_REWARD_PROGRAM_EMAIL_SUBJECT,
                    'format' => 'html',
                    'body' => $body));

            }
        }
    }
    
    // loop through all contact groups that contact should be added to
    foreach ($contact_groups as $contact_group_id) {
        // check if contact group exists
        $query = "SELECT id FROM contact_groups WHERE id = '$contact_group_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if contact group exists
        if (mysqli_num_rows($result) > 0) {
            // check if contact is already in contact group
            $query =
                "SELECT contact_id
                FROM contacts_contact_groups_xref
                WHERE
                    (contact_id = '$contact_id')
                    AND (contact_group_id = '$contact_group_id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if contact is not already in contact group, then add contact to contact group
            if (mysqli_num_rows($result) == 0) {
                $query =
                    "INSERT INTO contacts_contact_groups_xref (
                        contact_id,
                        contact_group_id)
                    VALUES (
                        '$contact_id',
                        '$contact_group_id')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
    }

    // Cancel all order abandoned auto campaigns that might exist for this customer,
    // because the customer has now completed an order so we don't want
    // to annoy the customer.  This includes the auto campaign for this order,
    // and any other auto campaigns for different orders for this same customer.

    db(
        "UPDATE email_campaigns
        LEFT JOIN email_recipients ON email_campaigns.id = email_recipients.email_campaign_id
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
            AND
            (
                (email_campaigns.order_id = '" . $_SESSION['ecommerce']['order_id'] . "')
                OR
                (
                    (email_recipients.contact_id = '$contact_id')
                    OR (email_recipients.email_address = '" . e($billing_email_address) . "')
                )
            )");
    
    // if visitor tracking is on, update visitor record with order completed information
    if (VISITOR_TRACKING == true) {
        $query = "UPDATE visitors
                 SET
                    order_completed = '1',
                    order_total = order_total + $total,
                    city = '" . escape($billing_city) . "',
                    state = '" . escape($billing_state) . "',
                    zip_code = '" . escape($billing_zip_code) . "',
                    country = '" . escape($billing_country) . "',
                    stop_timestamp = UNIX_TIMESTAMP()
                 WHERE id = '" . $_SESSION['software']['visitor_id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    log_activity('Order was completed (' . $order_number . ', ' . prepare_amount($total / 100) . ', ' . $billing_first_name . ' ' . $billing_last_name . ', ' . $billing_email_address . ').');

    // If hooks are enabled and there is post-save hook code, then run it.
    if ((defined('PHP_REGIONS') and PHP_REGIONS) && ($post_save_hook_code != '')) {
        eval(prepare_for_eval($post_save_hook_code));
    }
    
    // remove order id from session so shopping cart is emptied, in case user tries to shop again
    unset($_SESSION['ecommerce']['order_id']);
    
    // remove liveform because software does not need it anymore
    $liveform->remove_form($type_value);


    // send user to next page
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($next_page_id));
}