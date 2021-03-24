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

// Prepares various info for a product or product group.

function get_catalog_item($request) {

    $item = $request['item'];

    // If this is a product group then get info for group.
    if ($item['type'] == 'product group') {

        $item_properties = db_item(
            "SELECT
                name,
                short_description,
                image_name,
                address_name,
                display_type
            FROM product_groups
            WHERE id = '" . e($item['id']) . "'");

    // Otherwise this is a product, so get info for product.
    } else {

        $item_properties = db_item(
            "SELECT
                name,
                short_description,
                image_name,
                price,
                selection_type,
                address_name
            FROM products
            WHERE id = '" . e($item['id']) . "'");
    }

    if (!$item_properties) {
        return error_response('Sorry, that item could not be found.');
    }

    $item = array_merge($item, $item_properties);

    if ($item['type'] == 'product group' and $item['display_type'] == 'browse') {
        $item['url'] = $request['catalog_url'] . encode_url_path($item['address_name']);

    } else {
        $item['url'] = $request['catalog_detail_url'] . encode_url_path($item['address_name']);
    }

    $item['image_url'] = '';

    if ($item['image_name'] != '') {
        $item['image_url'] = PATH . encode_url_path($item['image_name']);
    }

    if (isset($request['discounted_product_prices'])) {
        $discounted_product_prices = $request['discounted_product_prices'];
    } else {
        $discounted_product_prices = get_discounted_product_prices();
    }

    if ($item['type'] == 'product group') {
        $item['price_range'] = get_price_range($item['id'], $discounted_product_prices);
    }

    // Get the price or price range for output.
    $item['price_info'] = get_price_info(array(
        'item' => $item,
        'discounted_product_prices' => $discounted_product_prices));

    if ($item['type'] == 'product group') {
        $item['price_range']['smallest_price'] = $item['price_range']['smallest_price'] / 100;
        $item['price_range']['largest_price'] = $item['price_range']['largest_price'] / 100;
        $item['price_range']['original_price'] = $item['price_range']['original_price'] / 100;

    // Otherwise this is a product, so prepare price in dollars.
    } else {
        $item['price'] = $item['price'] / 100;
    }

    return array(
        'status' => 'success',
        'item' => $item);
}