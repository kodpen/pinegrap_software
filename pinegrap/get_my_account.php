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

function get_my_account($properties) {

    $page_id = $properties['page_id'];

    $form = new liveform('my_account');

    // If the user has a start page then prepare info for that.
    if (USER_START_PAGE_ID) {
        $start_page_name = get_page_name(USER_START_PAGE_ID);
        $start_page_url = PATH . encode_url_path($start_page_name);

    } else {
        $start_page_name = '';
        $start_page_url = '';
    }

    // If the reward program is enabled then get reward points for the user.
    if (ECOMMERCE_REWARD_PROGRAM) {
        $reward_points = db_value("SELECT user_reward_points FROM user WHERE user_id = '" . USER_ID . "'");
    } else {
        $reward_points = 0;
    }

    $contact = db_item(
        "SELECT
            first_name,
            last_name,
            company,
            title,
            business_address_1,
            business_address_2,
            business_city,
            business_state,
            business_zip_code,
            business_country,
            business_phone,
            mobile_phone,
            home_phone,
            business_fax,
            affiliate_approved,
            affiliate_name,
            affiliate_code,
            affiliate_commission_rate
         FROM contacts
         WHERE id = '" . USER_CONTACT_ID . "'");

    $first_name = $contact['first_name'];
    $last_name = $contact['last_name'];
    $company = $contact['company'];
    $title = $contact['title'];
    $business_address_1 = $contact['business_address_1'];
    $business_address_2 = $contact['business_address_2'];
    $business_city = $contact['business_city'];
    $business_state = $contact['business_state'];
    $business_zip_code = $contact['business_zip_code'];
    $business_country = $contact['business_country'];
    $business_phone = $contact['business_phone'];
    $mobile_phone = $contact['mobile_phone'];
    $home_phone = $contact['home_phone'];
    $business_fax = $contact['business_fax'];
    $affiliate_approved = $contact['affiliate_approved'];
    $affiliate_name = $contact['affiliate_name'];
    $affiliate_code = $contact['affiliate_code'];
    $affiliate_commission_rate = $contact['affiliate_commission_rate'];

    if ($business_country) {
        $business_country = db_value("SELECT name FROM countries WHERE code = '" . e($business_country) . "'");
    }

    $timezone = '';

    // If this PHP version supports user timezones then output timezone.
    if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
        if (USER_TIMEZONE != '') {
            $timezone = USER_TIMEZONE;

        } else if (TIMEZONE != '') {
            $timezone = TIMEZONE;

        } else {
            $timezone = SERVER_TIMEZONE;
        }

        // Check to see if the user's timezone is one of the timezones in our pick list
        // and use our label for the timezone instead of the standard timezone name if so.
        $timezone_label = array_search($timezone, get_timezones());

        if ($timezone_label) {
            $timezone = $timezone_label;
        }
    }

    $affiliate = false;
    $affiliate_url = '';
    $affiliate_pending_total = 0;
    $affiliate_payable_total = 0;
    $affiliate_paid_total = 0;
    $commissions = array();
    $complete_orders = array();
    $incomplete_orders = array();
    $address_book = false;
    $recipients = array();

    // If ecommerce is enabled, then prepare info for that.
    if (ECOMMERCE) {
        // If affiliate program is enabled and affiliate is approved, prepare affiliate info.
        if (AFFILIATE_PROGRAM and $affiliate_approved) {
            $affiliate = true;

            // Create commission instances from recurring profiles, if necessary.
            update_recurring_commissions();

            $affiliate_url = URL_SCHEME . HOSTNAME_SETTING . PATH . '?a=' . urlencode($affiliate_code);

            // If the affiliate commission rate is blank, then the affiliate commission rate
            // is the default commission rate from the site settings.
            if ($affiliate_commission_rate == 0) {
                $affiliate_commission_rate = AFFILIATE_DEFAULT_COMMISSION_RATE;
            }

            $affiliate_pending_total = db_value(
                "SELECT SUM(amount) as pending_total
                FROM commissions
                WHERE
                    (status = 'pending')
                    AND (affiliate_code = '" . e($affiliate_code) . "')") / 100;

            $affiliate_payable_total = db_value(
                "SELECT SUM(amount) as payable_total
                FROM commissions
                WHERE
                    (status = 'payable')
                    AND (affiliate_code = '" . e($affiliate_code) . "')") / 100;

            $affiliate_paid_total = db_value(
                "SELECT SUM(amount) as paid_total
                FROM commissions
                WHERE
                    (status = 'paid')
                    AND (affiliate_code = '" . e($affiliate_code) . "')") / 100;

            // Get all commissions for this affiliate.
            $commissions = db_items(
                "SELECT
                    reference_code,
                    (amount / 100) AS amount,
                    status,
                    created_timestamp
                FROM commissions
                WHERE affiliate_code = '" . e($affiliate_code) . "'
                ORDER BY created_timestamp DESC");

            // Loop through the commissions in order to prepare a status label,
            // which is a propercased version of the status.
            foreach ($commissions as $key => $commission) {
                $commission['status_label'] = ucwords($commission['status']);

                $commissions[$key] = $commission;
            }
        }

        $complete_orders = db_items(
            "SELECT
                id,
                order_number,
                order_date,
                (total / 100) AS total
            FROM orders
            WHERE
                (user_id = '" . USER_ID . "')
                AND (status != 'incomplete')
            ORDER BY order_date DESC");

        $incomplete_orders = db_items(
            "SELECT
                id,
                reference_code,
                order_date
            FROM orders
            WHERE
                (user_id = '" . USER_ID . "')
                AND (status = 'incomplete')
            ORDER BY order_date DESC");

        // If there is at least one complete or incomplete order, then continue to prepare them.
        if ($complete_orders or $incomplete_orders) {

            $view_url = get_page_type_url('view order');

            // Loop through the complete orders in order to prepare info.
            foreach ($complete_orders as $key => $order) {

                $order['total_info'] = prepare_amount($order['total']);

                if ($view_url) {
                    $order['view_url'] = $view_url . '?id=' . $order['id'];
                } else {
                    $order['view_url'] = '';
                }

                $order['reorder_url'] = PATH . SOFTWARE_DIRECTORY . '/order_history_reorder.php?id=' . $order['id'] . '&token=' . $_SESSION['software']['token'];

                $complete_orders[$key] = $order;

            }

            // Loop through the incomplete orders in order to prepare info.
            foreach ($incomplete_orders as $key => $order) {

                if ($view_url) {
                    $order['view_url'] = $view_url . '?id=' . $order['id'];
                } else {
                    $order['view_url'] = '';
                }

                // If this is the current active order, then prepare info for it.
                if ($order['id'] == $_SESSION['ecommerce']['order_id']) {

                    $order['active'] = true;

                    $order['order_url'] = '';

                    $cart_page_id = 0;
                
                    // if the visitor has visited a shopping cart page last, then use that page
                    if ($_SESSION['ecommerce']['shopping_cart_page_id']) {
                        $cart_page_id = $_SESSION['ecommerce']['shopping_cart_page_id'];
                    
                    // else if the visitor has visited an express order page last, then use that page
                    } else if ($_SESSION['ecommerce']['express_order_page_id']) {
                        $cart_page_id = $_SESSION['ecommerce']['express_order_page_id'];
                    }

                    if ($cart_page_id) {
                        $cart_page_name = get_page_name($cart_page_id);

                        if ($cart_page_name != '') {
                            $order['order_url'] = PATH . encode_url_path($cart_page_name);
                        }
                    }

                // Otherwise this is not the active order.
                } else {
                    $order['active'] = false;

                    $order['retrieve_url'] = PATH . SOFTWARE_DIRECTORY . '/order_history_retrieve_order.php?id=' . $order['id'] . '&token=' . $_SESSION['software']['token'];
                }

                $order['delete_url'] = PATH . SOFTWARE_DIRECTORY . '/order_history_delete_order.php?id=' . $order['id'] . '&token=' . $_SESSION['software']['token'];

                $incomplete_orders[$key] = $order;
            }

        }

        // If shipping is enabled, then prepare address book.
        if (ECOMMERCE_SHIPPING) {
            $address_book = true;

            $recipients = db_items(
                "SELECT
                    address_book.id,
                    address_book.ship_to_name,
                    address_book.salutation,
                    address_book.first_name,
                    address_book.last_name,
                    address_book.company,
                    address_book.address_1,
                    address_book.address_2,
                    address_book.city,
                    address_book.state,
                    address_book.zip_code,
                    countries.name AS country
                FROM address_book
                LEFT JOIN countries ON address_book.country = countries.code
                WHERE address_book.user = '" . USER_ID. "'
                ORDER BY address_book.last_name");

            // If there is at least one recipient, then continue to prepare them.
            if ($recipients) {

                $update_url = get_page_type_url('update address book');

                // Loop through the recipients in order to prepare info.
                foreach ($recipients as $key => $recipient) {

                    if ($update_url) {
                        $recipient['update_url'] = $update_url . '?id=' . $recipient['id'];
                    } else {
                        $recipient['update_url'] = '';
                    }

                    $recipient['remove_url'] = PATH . SOFTWARE_DIRECTORY . '/remove_recipient.php?id=' . $recipient['id'] . '&token=' . $_SESSION['software']['token'];

                    $recipients[$key] = $recipient;

                }
            }
        }
    }

    $output = render_layout(array(
        'page_id' => $page_id,
        'messages' => $form->get_messages(),
        'form' => $form,
        'logout_url' => get_page_type_url('logout'),
        'change_password_url' => get_page_type_url('change password'),
        'email_preferences_url' => get_page_type_url('email preferences'),
        'start_page_name' => $start_page_name,
        'start_page_url' => $start_page_url,
        'reward_points' => $reward_points,
        'my_account_profile_url' => get_page_type_url('my account profile'),
        'first_name' => $first_name,
        'last_name' => $last_name,
        'company' => $company,
        'title' => $title,
        'business_address_1' => $business_address_1,
        'business_address_2' => $business_address_2,
        'business_city' => $business_city,
        'business_state' => $business_state,
        'business_zip_code' => $business_zip_code,
        'business_country' => $business_country,
        'business_phone' => $business_phone,
        'mobile_phone' => $mobile_phone,
        'home_phone' => $home_phone,
        'business_fax' => $business_fax,
        'timezone' => $timezone,
        'affiliate' => $affiliate,
        'affiliate_approved' => $affiliate_approved,
        'affiliate_name' => $affiliate_name,
        'affiliate_code' => $affiliate_code,
        'affiliate_url' => $affiliate_url,
        'affiliate_commission_rate' => $affiliate_commission_rate,
        'affiliate_pending_total' => $affiliate_pending_total,
        'affiliate_payable_total' => $affiliate_payable_total,
        'affiliate_paid_total' => $affiliate_paid_total,
        'commissions' => $commissions,
        'complete_orders' => $complete_orders,
        'incomplete_orders' => $incomplete_orders,
        'address_book' => $address_book,
        'update_address_book_url' => get_page_type_url('update address book'),
        'recipients' => $recipients));

    $form->remove();

    return
        '<div class="software_my_account">
            ' . $output . '
        </div>';

}
