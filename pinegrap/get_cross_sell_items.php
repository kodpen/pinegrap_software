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

function get_cross_sell_items($request) {

    $product_group = $request['product_group'];
    $recipient = $request['recipient'];

    $products = array();

    // If products were passed, then use those.
    if ($request['products']) {

        $products = $request['products'];

    // Otherwise if a product group was passed, then use products in that group.
    } else if ($product_group['id']) {

        $products = db_items(
            "SELECT products.id
            FROM products_groups_xref
            LEFT JOIN products ON products.id = products_groups_xref.product
            WHERE
                products_groups_xref.product_group = '" . e($product_group['id']) . "'
                AND products.enabled = '1'");

    // Otherwise if a recipient was passed, then use products from recipient.
    } else if ($recipient['id']) {

        if (!$_SESSION['ecommerce']['order_id']) {
            return error_response('Sorry, we could not find cross-sell items for that recipient because the customer does not have an active order.');
        }

        $recipient = db_item(
            "SELECT id, order_id FROM ship_tos WHERE id = '" . e($recipient['id']) . "'");

        if (!$recipient) {
            return error_response('Sorry, we could not find cross-sell items because the recipient could not be found.');
        }

        if ($recipient['order_id'] != $_SESSION['ecommerce']['order_id']) {
            return error_response('Sorry, we could not find cross-sell items because that recipient is not part of this customer\'s order.');
        }

        $products = db_items(
            "SELECT product_id AS id FROM order_items
            WHERE ship_to_id = '" . e($recipient['id']) . "'");

    // Otherwise if the customer has an order, then use products from order.
    } else if ($_SESSION['ecommerce']['order_id']) {
        $products = db_items(
            "SELECT DISTINCT(product_id) AS id FROM order_items
            WHERE order_id = '" . e($_SESSION['ecommerce']['order_id']) . "'");
    }

    if (!$products) {
        return error_response('Sorry, we could not find cross-sell items because no source products could be found.');
    }

    // Get products that have been ordered together.

    $inclusion = '';
    $exclusion = '';

    // Loop through the products in order to prepare SQL to get ordered-together products.
    foreach ($products as $product) {

        if ($inclusion) {
            $inclusion .= " OR ";
        }

        $inclusion .= "order_items_1.product_id = '" . e($product['id']) . "'";

        $exclusion .= "AND order_items_2.product_id != '" . e($product['id']) . "' ";
    }

    // Limit the amount of data we look at for performance reasons. $request['limit'] should be
    // set to the number of order items in the past that you want to analyze data for, or you can
    // not set a limit in order to use default limit.  We tested various solutions
    // and the solution below (limiting by order items) had the best performance. We tested
    // with a "FROM (subquery with limit clause)", instead of "FROM order_items", and we also tested
    // by limiting by number of orders, however those solutions were slower.

    // If there is a limit in the request, then use that.
    if ($request['limit']) {
        $limit = (int) $request['limit'];

    // Otherwise there is not a limit in the request, so set 50,000 default.
    } else {
        $limit = 50000;
    }

    $sql_limit = '';

    // Get the order item id that is on the edge of the limit, so we can limit data to only data
    // after that order item.
    $limit_order_item_id = db("SELECT id FROM order_items ORDER BY id DESC LIMIT $limit, 1");

    // If an order item id was found for the limit, then limit the query.  Otherwise, if an order
    // item was not found, then this site does not have that many order items, so we don't need to
    // limit.
    if ($limit_order_item_id) {
        $sql_limit = "AND order_items_1.id > '$limit_order_item_id'";
    }

    // If a number of days has been passed in the request, then limit the look-back time period.

    $sql_timestamp = '';
    
    if ($request['days']) {

        $timestamp = time() - ($request['days'] * 86400);

        $sql_timestamp = "AND orders.order_date > '" . e($timestamp) . "'";
    }

    $ordered_together_products = db_items(
        "SELECT
            order_items_2.product_id AS id,
            count(order_items_2.product_id) AS number
        FROM order_items AS order_items_1
        LEFT JOIN orders ON order_items_1.order_id = orders.id
        JOIN order_items AS order_items_2 ON (
            order_items_2.order_id = order_items_1.order_id
            AND order_items_2.ship_to_id = order_items_1.ship_to_id)
        LEFT JOIN products ON order_items_2.product_id = products.id
        WHERE
            ($inclusion)
            $sql_limit
            AND orders.status != 'incomplete'
            $sql_timestamp
            $exclusion
            AND products.enabled = '1'
        GROUP BY order_items_2.product_id
        ORDER BY number DESC");

    if (!$ordered_together_products) {
        return error_response('Sorry, we could not find any cross-sell items. This might happen if no items have been ordered with the source products yet, or if the number of days is too low.');
    }

    if ($request['discounted'] === false) {
        $discounted = false;
    } else {
        $discounted = true;
    }

    // If we should give priority to discounted products, then do that.

    if ($discounted) {

        $discounted_product_prices = get_discounted_product_prices();

        // If discounted products were found, then move any ordered together products, that are
        // discounted, to the top of the array for the ordered together products.
        if ($discounted_product_prices) {

            $ordered_together_discounted_products = array();

            foreach ($ordered_together_products as $key => $product) {

                if (isset($discounted_product_prices[$product['id']])) {
                    $ordered_together_discounted_products[] = $product;
                    unset($ordered_together_products[$key]);
                }
            }

            if ($ordered_together_discounted_products) {
                $ordered_together_products = array_merge($ordered_together_discounted_products, $ordered_together_products);
            }
        }
    }

    require_once(dirname(__FILE__) . '/get_product_group_for_product.php');

    // Figure out excluded product groups which are any groups with select display type for the
    // source products.  This is necessary in order to prevent showing the same product group
    // as a product group for a source product.  For example, if the visitor has a office
    // chair in the cart, then we don't want to show the office chair product group as a cross-sell,
    // because the customer has already added a product from that group.

    $excluded_product_groups = array();

    foreach ($products as $product) {

        $product_group = get_product_group_for_product(array(
            'product_group' => $request['in_product_group'],
            'product' => $product));

        // If the group is a select group and has not already been added, then add it to excluded
        // array.
        if (
            $product_group['display_type'] == 'select'
            and !in_array($product_group['id'], $excluded_product_groups)
        ) {
            $excluded_product_groups[] = $product_group['id'];
        }
    }

    $items = array();

    $added_product_groups = array();

    $url = PATH . encode_url_path(get_page_name($request['catalog_detail_page']['id'])) . '/';

    require_once(dirname(__FILE__) . '/get_catalog_item.php');

    if (!isset($discounted_product_prices)) {
        $discounted_product_prices = get_discounted_product_prices();
    }

    // If the number of items was passed in the request, then use that.
    if ($request['number_of_items']) {
        $number_of_items = $request['number_of_items'];

    // Otherwise, use the default.
    } else {
        $number_of_items = 3;
    }

    $count = 0;

    // Loop through ordered-together products in order to determine which items are valid.
    foreach ($ordered_together_products as $product) {

        // Check if product is located in the valid area of the product group tree, and also get
        // the first product group that the product is in, in case we need to include the product
        // group instead of the product.
        $product_group = get_product_group_for_product(array(
            'product_group' => $request['in_product_group'],
            'product' => $product));

        // If a product group could not be found or it is an excluded group, then the product is
        // not valid, so skip to the next product.
        if (!$product_group or in_array($product_group['id'], $excluded_product_groups)) {
            continue;
        }

        $item = array();

        // If the product's parent product group is a select group, then we need to include the
        // product group instead of the product, because that is how the product is accessed.
        if ($product_group['display_type'] == 'select') {

            // If an item has already been added for this product group, because of a different
            // product in the same group, then we don't need to do anything, so skip to next product.
            if (in_array($product_group['id'], $added_product_groups)) {
                continue;
            }

            $item['id'] = $product_group['id'];
            $item['type'] = 'product group';

            // Remember that an item for this product group has been added so we don't add a
            // duplicate again.
            $added_product_groups[] = $product_group['id'];

        // Otherwise just include the product itself.
        } else {

            $item['id'] = $product['id'];
            $item['type'] = 'product';
        }

        // Get info for item, like price and etc.
        $response = get_catalog_item(array(
            'item' => $item,
            'catalog_detail_url' => $url,
            'discounted_product_prices' => $discounted_product_prices));

        // If we could not find info for item, then skip to next product.
        if (!$response['item']) {
            continue;
        }

        $items[] = $response['item'];

        $count++;

        // If we have added enough items, then we are done.
        if ($count == $number_of_items) {
            break;
        }
    }

    // If no items could be found, then return error.
    if (!$items) {
        return error_response('Sorry, we could not find any cross-sell items. Try selecting a product group for "in_product_group" that has a greater scope (i.e. includes more products), or remove property to include all products.');
    }

    return array(
        'status' => 'success',
        'items' => $items);
}