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

// Couple of functions used by myself upsell.

// Get products for myself recipient, so myself upsell knows not to show upsell for those products.

function get_myself_products() {

    $products = array();

    // If there is no order then return empty array.
    if (!$_SESSION['ecommerce']['order_id']) {
        return $products;
    }

    // Get myself recipient, if one exists.
    $recipient = db_item(
        "SELECT id FROM ship_tos
        WHERE
            order_id = '" . e($_SESSION['ecommerce']['order_id']) . "'
            AND ship_to_name = 'myself'");

    // If there is no myself recipient, then return empty array.
    if (!$recipient) {
        return $products;
    }

    // Get myself products.
    $products = db_values(
        "SELECT DISTINCT(product_id) FROM order_items
        WHERE ship_to_id = '" . e($recipient['id']) . "'");

    return $products;
}

// Check if a myself upsell should be shown for an item in the cart.

function check_myself_upsell($request) {

    // If this item is in the myself recipient or it was added by an offer or it is not shippable,
    // then we don't want to show myself upsell.
    if (
        $request['recipient']['ship_to_name'] == 'myself'
        or $request['item']['added_by_offer']
        or !$request['item']['shippable']
    ) {
        return false;
    }

    $myself_products = $request['myself_products'];

    // If the myself products were not passed in the request, then get them.
    if (!is_array($myself_products)) {
        $myself_products = get_myself_products();
    }

    // If this item is already in the myself recipient, then we don't want to show myself upsell.
    if (in_array($request['item']['product_id'], $myself_products)) {
        return false;
    }

    // If we got here then we want to show myself upsell.
    return true;
}