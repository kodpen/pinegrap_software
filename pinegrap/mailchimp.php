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

// Various functions related to MailChimp.

// Get MailChimp settings from the DB and create global constants that various functions will use.

function mailchimp_init() {

    // Get MailChimp settings.
    $config = db_item(
        "SELECT
            mailchimp_key,
            mailchimp_list_id,
            mailchimp_store_id,
            mailchimp_sync_running,
            mailchimp_sync_days,
            mailchimp_sync_limit
        FROM config");

    define('MAILCHIMP_KEY', $config['mailchimp_key']);
    define('MAILCHIMP_LIST_ID', $config['mailchimp_list_id']);
    define('MAILCHIMP_STORE_ID', $config['mailchimp_store_id']);
    define('MAILCHIMP_SYNC_DAYS', $config['mailchimp_sync_days']);
    define('MAILCHIMP_SYNC_LIMIT', $config['mailchimp_sync_limit']);

    // Get the datacenter which is after the dash in the key.
    define('MAILCHIMP_DC', substr(MAILCHIMP_KEY, strpos(MAILCHIMP_KEY, '-') + 1));
}

mailchimp_init();

// Sync products and then orders.

function mailchimp_sync() {

    // If the sync is already running from a different process, then log and exit, because we
    // don't want multiple processes trampling on each other (trying to sync the same orders).

    if (db("SELECT mailchimp_sync_running FROM config")) {

        $message = 'A MailChimp sync is already running, so we will not run an extra sync. This might be normal if there are a lot of orders to sync. A cron job might have attempted to run a second sync before the first sync was complete. If you would like to avoid this error, then you might try lowering Limit Sync in the MailChimp Settings, and/or setting the cron job to run less frequently. If you feel that a sync is not actually running then you may run the following SQL command to fix the issue: UPDATE config SET mailchimp_sync_running = 0;';

        log_activity($message);

        // We don't email this error, because it might happen a lot.

        return error_response($message);
    }

    db("UPDATE config SET mailchimp_sync_running = '1'");

    $response = mailchimp_sync_products();

    if ($response['status'] == 'error') {

        db("UPDATE config SET mailchimp_sync_running = '0'");

        return $response;
    }

    $response = mailchimp_sync_orders();

    if ($response['status'] == 'error') {

        db("UPDATE config SET mailchimp_sync_running = '0'");

        return $response;
    }

    db("UPDATE config SET mailchimp_sync_running = '0'");

    return array('status' => 'success');
}

// Sync product groups and products with MailChimp.

function mailchimp_sync_products() {

    // Prepare a timestamp that we will use to mark all products/groups that are synced.
    $timestamp = time();

    // Get all select products groups that have been modified since the last sync.
    $product_groups = db_items("
        SELECT
            id,
            name,
            parent_id,
            short_description,
            image_name,
            address_name
        FROM product_groups
        WHERE
            display_type = 'select'
            AND timestamp >= mailchimp_sync_timestamp");

    // Get all products that have been modified since the last sync.
    $products = db_items("
        SELECT
            id,
            name,
            enabled,
            short_description,
            price,
            image_name,
            address_name,
            inventory,
            inventory_quantity,
            backorder
        FROM products
        WHERE timestamp >= mailchimp_sync_timestamp");

    // If no product groups and no products have been modified, then we are done.
    if (!$product_groups and !$products) {
        return array('status' => 'success');
    }

    // Get current products in MailChimp, so we can determine if we need to create or update.
    // The count query string parameter is necessary so MailChimp will respond with all products.

    $response = mailchimp_request(array(
        'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products?count=999999'));

    if ($response['status'] == 'error') {
        return $response;
    }

    $mailchimp_products = $response['mailchimp_response']['products'];

    // Get the primary catalog detail page name, so we can prepare a URL for each product. We don't
    // have a good way of determining which catalog detail page is the primary one, so for now, we
    // are just going to get the one that is included in the site map and most recently modified.
    // We might be changing the way URLs work for products/groups soon, so we don't want to spend
    // time on a better solution for now.

    $catalog_detail_page_name = db("
        SELECT page_name FROM page
        WHERE
            page_type = 'catalog detail'
            AND page_search = '1'
            AND sitemap = '1'
        ORDER BY page_timestamp DESC
        LIMIT 1");

    $url = URL_SCHEME . HOSTNAME_SETTING . PATH . encode_url_path($catalog_detail_page_name) . '/';

    // Loop through the product groups in order to create or update them.

    foreach ($product_groups as $product_group) {

        $mailchimp_product = array();
        $mailchimp_product['id'] = 'pg-' . $product_group['id'];
        $mailchimp_product['title'] = $product_group['name'];
        $mailchimp_product['handle'] = $product_group['address_name'];
        $mailchimp_product['url'] = $url . encode_url_path($product_group['address_name']);
        $mailchimp_product['description'] = $product_group['short_description'];

        // Set the type to the name of the parent product group.
        $mailchimp_product['type'] =
            db("SELECT name FROM product_groups WHERE id = '" . e($product_group['parent_id']) . "'");

        $mailchimp_product['image_url'] = '';

        if ($product_group['image_name']) {
            $mailchimp_product['image_url'] =
                URL_SCHEME . HOSTNAME_SETTING . PATH . encode_url_path($product_group['image_name']);
        }

        // Get the products in this product group, in order to set MailChimp variants.
        $child_products = db_items("
            SELECT
                products.id,
                products.name,
                products.enabled,
                products.short_description,
                products.image_name,
                products.price,
                products.inventory,
                products.inventory_quantity,
                products.backorder
            FROM products_groups_xref
            LEFT JOIN products ON products.id = products_groups_xref.product
            WHERE products_groups_xref.product_group = '" . e($product_group['id']) . "'
            ORDER BY products_groups_xref.sort_order, products.name");

        // If there are no products in the product group, then mark as synced and skip to the next
        // product group, because MailChimp will return error if there are no variants.
        if (!$child_products) {
            db("UPDATE product_groups SET mailchimp_sync_timestamp = '" . e($timestamp) . "'
                WHERE id = '" . e($product_group['id']) . "'");
            continue;
        }

        $mailchimp_product['variants'] = array();

        foreach ($child_products as $product) {

            $variant = array();
            $variant['id'] = $product['id'];
            $variant['title'] = $product['short_description'];
            $variant['url'] = $mailchimp_product['url'];
            $variant['sku'] = $product['name'];
            $variant['price'] = sprintf("%01.2lf", $product['price'] / 100);

            // If the product is enabled, then determine how inventory should be set.
            if ($product['enabled']) {

                // If inventory tracking is enabled, then determine how we should set inventory.
                if ($product['inventory']) {

                    // If backorder is enabled, then assume product is available.
                    if ($product['backorder']) {
                        $variant['inventory_quantity'] = 999;

                    // Otherwise backorder is disabled, so set the actual inventory.
                    } else {
                        $variant['inventory_quantity'] = (int) $product['inventory_quantity'];
                    }

                // Otherwise, inventory tracking is disabled, so just assume product is available.
                } else {
                    $variant['inventory_quantity'] = 999;
                }

            // Otherwise, the product is disabled, so set inventory to zero, so MailChimp does not
            // recommend product.
            } else {
                $variant['inventory_quantity'] = 0;
            }

            $variant['image_url'] = '';

            if ($product['image_name']) {
                $variant['image_url'] =
                    URL_SCHEME . HOSTNAME_SETTING . PATH . encode_url_path($product['image_name']);
            }

            $mailchimp_product['variants'][] = $variant;
        }

        // Check if the product group already exists in MailChimp
        $existing_product = array_find($mailchimp_products, 'id', 'pg-' . $product_group['id']);

        // If the product group does not already exist in MailChimp, then add it.
        if (!$existing_product) {

            $response = mailchimp_request(array(
                'method' => 'post',
                'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products',
                'data' => $mailchimp_product));

        // Otherwise the product group already exists, so update it.
        } else {

            $response = mailchimp_request(array(
                'method' => 'patch',
                'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products/pg-' .
                    $product_group['id'],
                'data' => $mailchimp_product));
        }

        if ($response['status'] == 'error') {
            return $response;
        }

        db("UPDATE product_groups SET mailchimp_sync_timestamp = '" . e($timestamp) . "'
            WHERE id = '" . e($product_group['id']) . "'");

        log_activity('Product Group (' . $product_group['name'] . ') was synced with MailChimp.');
    }

    require_once(dirname(__FILE__) . '/get_product_group_for_product.php');

    // Loop through the modified products to create or update them.

    foreach ($products as $product) {

        // Prepare a variant for this product, which might use in a couple of areas below.

        $variant = array();
        $variant['id'] = $product['id'];
        $variant['title'] = $product['short_description'];
        $variant['url'] = $url . encode_url_path($product['address_name']);
        $variant['sku'] = $product['name'];
        $variant['price'] = sprintf("%01.2lf", $product['price'] / 100);

        // If the product is enabled, then determine how inventory should be set.
        if ($product['enabled']) {

            // If inventory tracking is enabled, then determine how we should set inventory.
            if ($product['inventory']) {

                // If backorder is enabled, then assume product is available.
                if ($product['backorder']) {
                    $variant['inventory_quantity'] = 999;

                // Otherwise backorder is disabled, so set the actual inventory.
                } else {
                    $variant['inventory_quantity'] = (int) $product['inventory_quantity'];
                }

            // Otherwise, inventory tracking is disabled, so just assume product is available.
            } else {
                $variant['inventory_quantity'] = 999;
            }

        // Otherwise, the product is disabled, so set inventory to zero, so MailChimp does not
        // recommend product.
        } else {
            $variant['inventory_quantity'] = 0;
        }

        $variant['image_url'] = '';

        if ($product['image_name']) {
            $variant['image_url'] =
                URL_SCHEME . HOSTNAME_SETTING . PATH . encode_url_path($product['image_name']);
        }

        // Get the first/primary product group that this product is in, in order to determine if
        // this is a standalone product.
        $product_group = get_product_group_for_product(array('product' => $product));

        // If the primary product group for this product is a browse group then create/update a
        // standalone product in MailChimp.  We don't want to create a standalone product, if the
        // product is in a select product group, because there will already be a variant for this
        // product.

        if ($product_group and $product_group['display_type'] == 'browse') {

            $mailchimp_product = array();
            $mailchimp_product['id'] = 'p-' . $product['id'];
            $mailchimp_product['title'] = $product['short_description'];
            $mailchimp_product['handle'] = $product['address_name'];
            $mailchimp_product['url'] = $url . encode_url_path($product['address_name']);
            $mailchimp_product['description'] = $product['short_description'];

            // If a parent product group was found for this product, then use name of product
            // group for the type.
            if ($product_group) {
                $mailchimp_product['type'] = db("
                    SELECT name FROM product_groups WHERE id = '" . e($product_group['id']) . "'");
            }

            $mailchimp_product['image_url'] = '';

            if ($product['image_name']) {
                $mailchimp_product['image_url'] =
                    URL_SCHEME . HOSTNAME_SETTING . PATH . encode_url_path($product['image_name']);
            }

            // MailChimp requires that we add one variant for standalone products.
            $mailchimp_product['variants'] = array();
            $mailchimp_product['variants'][] = $variant;

            // Check if the product already exists in MailChimp
            $existing_product = array_find($mailchimp_products, 'id', 'p-' . $product['id']);

            // If the product does not already exist in MailChimp, then create it.
            if (!$existing_product) {

                $response = mailchimp_request(array(
                    'method' => 'post',
                    'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products',
                    'data' => $mailchimp_product));

            // Otherwise the product already exists, so update it.
            } else {

                $response = mailchimp_request(array(
                    'method' => 'patch',
                    'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products/p-' .
                        $product['id'],
                    'data' => $mailchimp_product));
            }

            if ($response['status'] == 'error') {
                return $response;
            }
        }

        // Loop through all existing products in MailChimp in order to update all existing variants
        // for this product.

        foreach ($mailchimp_products as $mailchimp_product) {

            // Look for an existing variant.
            $existing_variant = array_find($mailchimp_product['variants'], 'id', $product['id']);

            // If an existing variant for this product was found, then update it.
            if ($existing_variant) {

                $response = mailchimp_request(array(
                    'method' => 'patch',
                    'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products/' .
                        $mailchimp_product['id'] . '/variants/' . $existing_variant['id'],
                    'data' => $variant));

                if ($response['status'] == 'error') {
                    return $response;
                }
            }
        }

        db("UPDATE products SET mailchimp_sync_timestamp = '" . e($timestamp) . "'
            WHERE id = '" . e($product['id']) . "'");

        log_activity('Product (' . $product['name'] . ' - ' . $product['short_description'] . ') was synced with MailChimp.');
    }

    $message = 'Product Groups (' . number_format(count($product_groups)) . ') and Products (' . number_format(count($products)) . ') were synced with MailChimp.';

    log_activity($message);

    return array(
        'status' => 'success',
        'message' => $message);
}

// Sync completed orders with MailChimp.

function mailchimp_sync_orders() {

    // Prepare a timestamp that we will use to mark all orders that are synced.
    $timestamp = time();

    // If there is a limit on the number of days in the past that we should sync orders, then
    // prepare SQL filter.

    $sql_date_filter = '';

    if (MAILCHIMP_SYNC_DAYS) {
        $sql_date_filter =
            "AND order_date >= '" . e($timestamp - (86400 * MAILCHIMP_SYNC_DAYS)) . "'";
    }

    // If there is a limit for the number of orders that should be synced each time this process
    // runs, then prepare SQL filter.

    $sql_limit = '';

    if (MAILCHIMP_SYNC_LIMIT) {
        $sql_limit = "LIMIT " . MAILCHIMP_SYNC_LIMIT;
    }

    // Get completed orders that have not been synced with MailChimp yet and have not generated a
    // MailChimp error in the past.

    $orders = db_items("
        SELECT
            id,
            billing_first_name,
            billing_last_name,
            billing_email_address,
            billing_city,
            billing_state,
            billing_zip_code,
            billing_country,
            total,
            order_number,
            discount,
            tax,
            shipping,
            order_date,
            user_id,
            opt_in,
            special_offer_code,
            tracking_code
        FROM orders
        WHERE
            mailchimp_sync_timestamp = '0'
            AND mailchimp_sync_error = '0'
            AND status != 'incomplete'
            $sql_date_filter
        ORDER BY order_date, order_number
        $sql_limit");

    if (!$orders) {
        return array('status' => 'success');
    }

    // Get current products in MailChimp, so for each order item, we can check if a product
    // already exists or if we need to create one, because MailChimp requires that a product exist
    // before adding a line item.  The count query string parameter is necessary so MailChimp will
    // respond with all products.

    $response = mailchimp_request(array(
        'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products?count=999999'));

    if ($response['status'] == 'error') {
        return $response;
    }

    $mailchimp_products = $response['mailchimp_response']['products'];

    // Check if there is a SELF_GIFT merge field, so we know if we need to deal with updating
    // that field for the member for each order.  The merge field is used in order to remember
    // if a customer has ordered for him/herself (S), a gift recipient (G), or both (B).

    $response = mailchimp_request(array(
        'path' => '/lists/' . MAILCHIMP_LIST_ID . '/merge-fields?fields=merge_fields.tag&count=999999'));

    $merge_fields = $response['mailchimp_response']['merge_fields'];
    
    $self_gift_field = array_find($merge_fields, 'tag', 'SELF_GIFT');

    require_once(dirname(__FILE__) . '/get_product_group_for_product.php');

    $number_of_synced_orders = 0;

    // Loop through the orders in order to add them to MailChimp.

    foreach ($orders as $order) {

        $mailchimp_order = array();

        $mailchimp_order['id'] = $order['id'];

        $mailchimp_order['customer'] = array();

        $user = array();

        // If there is a user id for the order, then check if user still exists and get user info.
        if ($order['user_id']) {
            $user = db_item("
                SELECT user_id AS id, user_email AS email_address FROM user
                WHERE user_id = '" . e($order['user_id']) . "'");
        }

        // If a user was found, then use user info.
        // We will use a hash of the email address as the customer ID, so that multiple orders
        // can be connected to the same customer, if he/she used the same email address.

        if ($user) {

            // Originally, we used the user ID for the customer ID, however we ran into a MailChimp
            // issue with that solution if a user changed their email address in liveSite. MailChimp
            // does not support changing the email address for a customer so it responded with the
            // following error: "An email address may not be changed once a customer is created."
            // https://github.com/mailchimp/mc-magento/issues/17
            // Now, we use the hash of the user's email address for the customer ID.

            $mailchimp_order['customer']['id'] = md5(mb_strtolower($user['email_address']));
            $mailchimp_order['customer']['email_address'] = $user['email_address'];

        // Otherwise a user was not found, so use info from order.
        } else {
            $mailchimp_order['customer']['id'] = md5(mb_strtolower($order['billing_email_address']));
            $mailchimp_order['customer']['email_address'] = $order['billing_email_address'];
        }

        // Look for a contact for the email address, in order to determine opt-in status, because
        // that should be the most recent opt-in status that we have. We don't want to simply look
        // at the order opt-in value because this might be an old order.
        
        $contact = db_item("
            SELECT
                id,
                opt_in
            FROM contacts
            WHERE email_address = '" . e($mailchimp_order['customer']['email_address']) . "'
            ORDER BY timestamp DESC
            LIMIT 1");

        // If a contact was found, then use opt-in status from that.
        if ($contact) {
            $opt_in = $contact['opt_in'];

        // Otherwise a contact was not found, so use opt-in status from order.
        } else {
            $opt_in = $order['opt_in'];
        }

        if ($opt_in) {
            $mailchimp_order['customer']['opt_in_status'] = true;
        } else {
            $mailchimp_order['customer']['opt_in_status'] = false;
        }

        $mailchimp_order['customer']['first_name'] = $order['billing_first_name'];
        $mailchimp_order['customer']['last_name'] = $order['billing_last_name'];

        // Get all-time orders and total for this customer.

        // If a user was found, then look for orders by user.
        if ($user) {
            $sql_filter = "user_id = '" . e($user['id']) . "'";

        // Otherwise a user was not found, so look for orders by email address.
        } else {
            $sql_filter = "billing_email_address = '" . e($order['billing_email_address']) . "'";
        }

        $all_time = db_item("
            SELECT COUNT(*) AS number_of_orders, SUM(total) AS total FROM orders
            WHERE status != 'incomplete' AND $sql_filter");

        $mailchimp_order['customer']['orders_count'] = (int) $all_time['number_of_orders'];
        $mailchimp_order['customer']['total_spent'] = sprintf("%01.2lf", $all_time['total'] / 100);

        $mailchimp_order['customer']['address'] = array();

        // Even though MailChimp does not technically require a street address, we have noticed
        // errors that eventually happen in other features of MailChimp if an address is passed
        // without a street address. So, we are going to just set a placeholder.  We don't want
        // to pass the actual street address, for privacy reasons, and because it should not
        // be necessary to have that info in MailChimp.

        $mailchimp_order['customer']['address']['address1'] = '[omitted]';

        $mailchimp_order['customer']['address']['city'] = $order['billing_city'];

        // Get country info.
        $country = db("
            SELECT id, name, code FROM countries
            WHERE code = '" . e($order['billing_country']) . "'
            LIMIT 1");

        $state = array();

        // If we found a country, then also get state info.
        if ($country) {
            $state = db("
                SELECT id, name, code FROM states
                WHERE
                    country_id = '" . e($country['id']) . "'
                    AND code = '" . e($order['billing_state']) . "'
                LIMIT 1");
        }

        // If we found a state in the db, then send province name and code.
        if ($state) {
            $mailchimp_order['customer']['address']['province'] = $state['name'];
            $mailchimp_order['customer']['address']['province_code'] = $state['code'];

        // Otherwise we did not find a state in the db, so send the state that the customer entered
        // as the province name.
        } else {
            $mailchimp_order['customer']['address']['province'] = $order['billing_state'];
        }

        // If there is a postal code, then set it.
        if ($order['billing_zip_code']) {
            $mailchimp_order['customer']['address']['postal_code'] = $order['billing_zip_code'];

        // Otherwise there is no postal code, so set placeholder. We have to set a placeholder,
        // because MailChimp requires a postal code when it updates the member's address field, even
        // if a country does not have postal codes.  We don't know why MailChimp requires a postal
        // code for countries without postal codes.  If we don't set a placeholder then MailChimp
        // will respond with error. 
        } else {
            $mailchimp_order['customer']['address']['postal_code'] = '[none]';
        }

        // If the country was found in the db, then send both the country name and code.
        if ($country) {
            $mailchimp_order['customer']['address']['country'] = $country['name'];
            $mailchimp_order['customer']['address']['country_code'] = $country['code'];

        // Otherwise the country was not found, so just send the code.
        } else {
            $mailchimp_order['customer']['address']['country_code'] = $order['billing_country'];
        }

        $mailchimp_order['currency_code'] = BASE_CURRENCY_CODE;
        $mailchimp_order['order_total'] = sprintf("%01.2lf", $order['total'] / 100);
        $mailchimp_order['discount_total'] = sprintf("%01.2lf", $order['discount'] / 100);
        $mailchimp_order['tax_total'] = sprintf("%01.2lf", $order['tax'] / 100);
        $mailchimp_order['shipping_total'] = sprintf("%01.2lf", $order['shipping'] / 100);

        $mailchimp_order['promos'] = array();

        // If there is an offer code for the order, then pass that as a promo.  We aren't going to
        // bother to include the amount discounted, because we don't have an easy way of figuring
        // that out.

        if ($order['special_offer_code']) {

            $promo = array(
                'code' => 'o:' . $order['special_offer_code'],
                'amount_discounted' => 0,
                'type' => 'fixed');

            $mailchimp_order['promos'][] = $promo;
        }

        // If there is a tracking code for the order, then pass that as a promo.  Even though a
        // tracking code might not be considered a promo, we are going to include it here, because
        // we don't see a better location to include the tracking code.  MailChimp might eventually
        // add a reporting/filtering feature for promos, so this might be useful.

        if ($order['tracking_code']) {

            $promo = array(
                'code' => 't:' . $order['tracking_code'],
                'amount_discounted' => 0,
                'type' => 'fixed');

            $mailchimp_order['promos'][] = $promo;
        }

        // Date & time order was completed.
        $mailchimp_order['processed_at_foreign'] = gmdate('Y-m-d\TH:i:s\Z', $order['order_date']);

        // We are purposely not sending the shipping address and billing street address, because
        // we want to minimize the amount of private info we share with MailChimp, and because
        // that data will probably not be useful in MailChimp.

        $mailchimp_order['billing_address'] = array();

        $mailchimp_order['billing_address']['name'] =
            $order['billing_first_name'] . ' ' . $order['billing_last_name'];

        // Even though MailChimp does not technically require a street address, we have noticed
        // errors that eventually happen in other features of MailChimp if an address is passed
        // without a street address. So, we are going to just set a placeholder.  We don't want
        // to pass the actual street address, for privacy reasons, and because it should not
        // be necessary to have that info in MailChimp.
        
        $mailchimp_order['billing_address']['address1'] = '[omitted]';

        $mailchimp_order['billing_address']['city'] = $order['billing_city'];

        // If we found a state in the db, then send province name and code.
        if ($state) {
            $mailchimp_order['billing_address']['province'] = $state['name'];
            $mailchimp_order['billing_address']['province_code'] = $state['code'];

        // Otherwise we did not find a state in the db, so send the state that the customer entered
        // as the province name.
        } else {
            $mailchimp_order['billing_address']['province'] = $order['billing_state'];
        }

        // If there is a postal code, then set it.
        if ($order['billing_zip_code']) {
            $mailchimp_order['billing_address']['postal_code'] = $order['billing_zip_code'];

        // Otherwise there is no postal code, so set placeholder. We have to set a placeholder,
        // because MailChimp requires a postal code when it updates the member's address field, even
        // if a country does not have postal codes.  We don't know why MailChimp requires a postal
        // code for countries without postal codes.  If we don't set a placeholder then MailChimp
        // will respond with error. 
        } else {
            $mailchimp_order['billing_address']['postal_code'] = '[none]';
        }

        // If the country was found in the db, then send both the country name and code.
        if ($country) {
            $mailchimp_order['billing_address']['country'] = $country['name'];
            $mailchimp_order['billing_address']['country_code'] = $country['code'];

        // Otherwise the country was not found, so just send the code.
        } else {
            $mailchimp_order['billing_address']['country_code'] = $order['billing_country'];
        }

        $sql_recurring_start_date = "";

        // If the payment service is not ClearCommerce, then prepare to get recurring order items
        // where start date is less than or equal to order date.
        if (
            (ECOMMERCE_CREDIT_DEBIT_CARD == FALSE)
            || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')
        ) {
            $sql_recurring_start_date = "OR (order_items.recurring_start_date <= '" . e(date('Y-m-d', $order['order_date'])) . "')";
        }

        $items = db_items("
            SELECT
                order_items.id,
                order_items.product_id,
                order_items.product_name,
                products.short_description,
                order_items.price,
                SUM(order_items.quantity) AS quantity
            FROM order_items
            LEFT JOIN products ON order_items.product_id = products.id
            WHERE
                (order_items.order_id = '" . e($order['id']) . "')
                AND
                (
                    (order_items.recurring_payment_period = '')
                    " . $sql_recurring_start_date . "
                )
            GROUP BY order_items.product_id
            ORDER BY order_items.id ASC");

        // If there are no order items for this order, then skip to the next order.  This should not
        // happen unless the db is corrupted.  This just prevents an error from MailChimp.
        if (!$items) {
            continue;
        }

        $mailchimp_order['lines'] = array();

        foreach ($items as $item) {

            $product = array();
            $product['id'] = $item['product_id'];
            $product['name'] = $item['product_name'];
            $product['short_description'] = $item['short_description'];

            // Get the primary product group for this product, so we can determine if we should
            // attempt to use a variant for a select product group, or use a standalone product.
            $product_group = get_product_group_for_product(array('product' => $product));

            $mailchimp_product = array();

            // If the primary product group is a select group, then we want to check for a product
            // group and variant for this product, instead of using a standalone product.
            if ($product_group['display_type'] == 'select') {

                // Check if the product group exists in MailChimp.
                $mailchimp_product = array_find($mailchimp_products, 'id', 'pg-' . $product_group['id']);

                // If a product was found in MailChimp, then look for a variant under that product,
                // that matches this order item's product.
                if ($mailchimp_product) {

                    $mailchimp_variant =
                        array_find($mailchimp_product['variants'], 'id', $product['id']);

                    // If a variant was not found, then don't use this product.
                    if (!$mailchimp_variant) {
                        $mailchimp_product = array();
                    }
                }
            }

            // If a MailChimp product has not been found yet, then look for an existing standalone
            // product in MailChimp.
            if (!$mailchimp_product) {

                $mailchimp_product = array_find($mailchimp_products, 'id', 'p-' . $product['id']);

                // If an existing standalone product was not found, then we need to create one.
                // This might happen, for example, if a product has been deleted in
                // liveSite since the order was completed, so the product might not exist in
                // MailChimp.  MailChimp requires that a product exist for all order items in an
                // order, so we have to create a product, in order to sync the order.

                if (!$mailchimp_product) {

                    $mailchimp_product = array();

                    $mailchimp_product['id'] = 'p-' . $product['id'];

                    if ($product['short_description']) {
                        $mailchimp_product['title'] = $product['short_description'];
                    } else {
                        $mailchimp_product['title'] = $product['name'];
                    }

                    $mailchimp_product['description'] = '';

                    // The short description might be null if product has been deleted, so we need
                    // to check for this to avoid MailChimp error.
                    if ($product['short_description']) {
                        $mailchimp_product['description'] = $product['short_description'];
                    }

                    // MailChimp requires that we add one variant for standalone products.

                    $variant = array();
                    $variant['id'] = $product['id'];

                    if ($product['short_description']) {
                        $variant['title'] = $product['short_description'];
                    } else {
                        $variant['title'] = $product['name'];
                    }

                    $variant['sku'] = $product['name'];

                    $mailchimp_product['variants'] = array();
                    $mailchimp_product['variants'][] = $variant;

                    $response = mailchimp_request(array(
                        'method' => 'post',
                        'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/products',
                        'data' => $mailchimp_product));

                    if ($response['status'] == 'error') {
                        return $response;
                    }

                    // Add the new product to the $mailchimp_products array, so that we will know
                    // that the product now exists in MailChimp for other orders that might have
                    // that same product.
                    $mailchimp_products[] = $mailchimp_product;
                }
            }

            $line = array();

            $line['id'] = $item['id'];
            $line['product_id'] = $mailchimp_product['id'];
            $line['product_variant_id'] = $product['id'];
            $line['quantity'] = (int) $item['quantity'];
            $line['price'] = sprintf("%01.2lf", $item['price'] / 100);

            $mailchimp_order['lines'][] = $line;
        }

        // Send a request to MailChimp to create the order.
        $response = mailchimp_request(array(
            'method' => 'post',
            'path' => '/ecommerce/stores/' . MAILCHIMP_STORE_ID . '/orders',
            'data' => $mailchimp_order));

        if ($response['status'] == 'error') {

            // If MailChimp responded with a 400 Invalid Resource error, then there is probably
            // something about the email address that MailChimp does not like, so mark order as
            // causing error, so we don't attempt to sync it again, so it won't error again. This is
            // necessary because MailChimp has various rules about email addresses.  For example,
            // MailChimp won't allow addresses like "example.@example.com" and "spam@example.com".

            if (
                $response['mailchimp_response']['status'] == 400
                and $response['mailchimp_response']['title'] == 'Invalid Resource'
            ) {

                // Mark error for order.
                db("UPDATE orders SET mailchimp_sync_error = '1' WHERE id = '" . e($order['id']) . "'");

                // Skip to the next order.
                continue;

            // Otherwise, there is some other error, which might not even be related to the order
            // (e.g. invalid key), so abort sync by returning response.

            } else {
                return $response;
            }
        }

        // Remember that we have synced this order, so we don't try to do it again.
        db("UPDATE orders SET mailchimp_sync_timestamp = '" . e($timestamp) . "'
            WHERE id = '" . e($order['id']) . "'");

        log_activity('Order (' . $order['order_number'] . ') was synced with MailChimp.');

        $number_of_synced_orders++;

        // If there is not a SELF_GIFT merge field, then we are done with this order, so continue
        // to the next order.
        if (!$self_gift_field) {
            continue;
        }

        // Otherwise, there is a SELF_GIFT merge field, so deal with that. The merge field is used
        // in order to remember if a customer has ordered for him/herself (S), a gift recipient (G),
        // or both (B).

        // Get the current SELF_GIFT value for the member in MailChimp.

        $member_hash = md5(mb_strtolower($mailchimp_order['customer']['email_address']));

        $response = mailchimp_request(array(
            'path' => '/lists/' . MAILCHIMP_LIST_ID . '/members/' . $member_hash .
                '?fields=merge_fields',
            'quiet' => true));

        // If there was an error, then just skip to the next order.  Sometimes the member won't
        // exist for various reasons.  For example, if the customer misspells the email address
        // (e.g. hotmil.com instead of hotmail.com), then MailChimp won't create a member.
        if ($response['status'] == 'error') {
            continue;
        }

        $old_self_gift = mb_strtoupper($response['mailchimp_response']['merge_fields']['SELF_GIFT']);

        // If SELF_GIFT is already "B" (both), then we don't need to do anything.
        if ($old_self_gift == 'B') {
            continue;
        }

        $self = false;
        $gift = false;

        // If there is no recipient for this order, then let's consider that a myself order, because
        // that means there were no shippable items in the order.  Only non-shippable items.
        if (!db("SELECT id FROM ship_tos WHERE order_id = '" . e($order['id']) . "' LIMIT 1")) {

            $self = true;

        // Otherwise there is at least one recipient for this order, so determine recipient types.
        } else {

            // If there is a myself recipient for this order, then remember that.
            if (db("
                SELECT id FROM ship_tos
                WHERE
                    order_id = '" . e($order['id']) . "'
                    AND ship_to_name = 'myself'
                LIMIT 1")
            ) {
                $self = true;
            }

            // If there is a gift recipient for this order, then remember that.
            if (db("
                SELECT id FROM ship_tos
                WHERE
                    order_id = '" . e($order['id']) . "'
                    AND ship_to_name != 'myself'
                LIMIT 1")
            ) {
                $gift = true;
            }
        }

        // Get the SELF_GIFT value for just this order.

        if ($self and $gift) {
            $order_self_gift = 'B';

        } else if ($self) {
            $order_self_gift = 'S';

        } else if ($gift) {
            $order_self_gift = 'G';
        }

        // Now determine the new SELF_GIFT value, when taking into account the old value.

        $new_self_gift = '';

        if (!$old_self_gift) {
            $new_self_gift = $order_self_gift;

        } else if ($old_self_gift == 'S' and $order_self_gift == 'S') {
            $new_self_gift = 'S';

        } else if ($old_self_gift == 'G' and $order_self_gift == 'G') {
            $new_self_gift = 'G';

        } else if ($old_self_gift == 'S' and $order_self_gift == 'G') {
            $new_self_gift = 'B';

        } else if ($old_self_gift == 'G' and $order_self_gift == 'S') {
            $new_self_gift = 'B';

        } else {
            $new_self_gift = $order_self_gift;
        }

        // If the SELF_GIFT value has changed, then update value for member in MailChimp.

        if ($new_self_gift != $old_self_gift) {

            $member = array();

            $member['merge_fields'] = array('SELF_GIFT' => $new_self_gift);

            $response = mailchimp_request(array(
                'method' => 'patch',
                'path' => '/lists/' . MAILCHIMP_LIST_ID . '/members/' . $member_hash,
                'data' => $member));

            if ($response['status'] == 'error') {
                return $response;
            }
        }
    }

    if ($number_of_synced_orders == 1) {
        $message = '1 Order was synced with MailChimp.';
    } else {
        $message = number_format($number_of_synced_orders) . ' Orders were synced with MailChimp.';
    }

    // We only need to log if there is more than one order because we have already logged above
    // for the individual order.  No need to log a redundant message for just one order.
    if ($number_of_synced_orders > 1) {
        log_activity($message);
    }

    return array(
        'status' => 'success',
        'message' => $message);
}

// Send any type of request to the MailChimp API.

function mailchimp_request($request) {

    $method = $request['method'];
    $path = $request['path'];
    $data = $request['data'];

    // If the key was passed in the request, then use it.
    if (isset($request['key'])) {

        $key = $request['key'];

        // Get the datacenter which is after the dash in the key.
        $dc = substr($key, strpos($key, '-') + 1);

    // Otherwise the key was not passed in the request, so use values from settings.
    } else {

        $key = MAILCHIMP_KEY;
        $dc = MAILCHIMP_DC;
    }

    // Sometimes you might want to check if something exists, however you don't want to log and
    // send an email alert, if the item does not exist (404).  Set quiet to true to silence 404
    // errors.
    if (isset($request['quiet'])) {
        $quiet = $request['quiet'];
    } else {
        $quiet = false;
    }

    $ch = curl_init();

    $url = 'https://' . $dc . '.api.mailchimp.com/3.0' . $path;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD,  'key:' . $key);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    $data_json = '';

    switch ($method) {

        case 'post':
            curl_setopt($ch, CURLOPT_POST, true);
            $data_json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            break;

        case 'delete':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;

        case 'patch':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            $data_json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            break;

        case 'put':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            $data_json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            break;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // We added the following in order to resolve issue where MailChimp returned an empty response
    // sometimes for a patch request to update a product. cURL Error: Empty reply from server (52).
    // We don't know why.
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

    $response = curl_exec($ch);

    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);

    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    // If there was a cURL problem, then log and return error.
    if ($curl_errno) {

        $message = 
            'MailChimp Error' . "\n" .
            'cURL Error Number: ' . $curl_errno . '.' . "\n" .
            'cURL Error Message: ' . $curl_error . '.' . "\n" .
            'Request: ' . print_r($request, true);

        log_activity($message);

        mailchimp_email_error($message);

        return error_response($message);
    }

    $response = json_decode($response, true);

    if ($response_code < 200 or $response_code > 299) {
        
        $message = 
            'MailChimp Error: ' . $response['title'] . '. ' . $response['detail'] . "\n" .
            'Response: ' . print_r($response, true) . "\n" .
            'Request: ' . print_r($request, true);

        // If quiet is disabled (default) or the response code is not 404, then log and email error.
        if (!$quiet or $response_code != 404) {
            log_activity($message);
            mailchimp_email_error($message);
        }

        return array(
            'status' => 'error',
            'message' => $message,
            'mailchimp_response' => $response);
    }

    return array(
        'status' => 'success',
        'mailchimp_response' => $response);
}

// Used to send an email to a technical contact when a MailChimp error happens.

function mailchimp_email_error($message) {

    // If there is an email for a technical admin defined in config.php, then email that person.
    // The contact for the support or commerce email set in the site settings, may not be technical,
    // so it may not be appropriate to email them.
    if (defined('ADMIN_EMAIL_ADDRESS')) {
        $to = ADMIN_EMAIL_ADDRESS;

    } else if (ECOMMERCE_EMAIL_ADDRESS) {
        $to = ECOMMERCE_EMAIL_ADDRESS;

    }  else {
        $to = EMAIL_ADDRESS;
    }

    email(array(
        'to' => $to,
        'from_name' => ORGANIZATION_NAME,
        'from_email_address' => EMAIL_ADDRESS,
        'subject' => HOSTNAME_SETTING . ': MailChimp Error',
        'body' => $message));
}