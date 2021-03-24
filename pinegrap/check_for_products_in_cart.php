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

// Checks if at least one of many products is in cart and that there is a certain quantity.
// Just one of the passed products need to be cart for this function to return true. All of the
// passed products don't have to be in the cart.

function check_for_products_in_cart($request) {

    $products = $request['products'];

    // If no required products were passed, then return false.
    if (!$products or !is_array($products)) {
        return false;
    }

    if (isset($request['quantity'])) {
        $quantity = $request['quantity'];
    } else {
        $quantity = 1;
    }

    $recipient = $request['recipient'];

    // If there is no active order then return false.
    if (!$_SESSION['ecommerce']['order_id']) {
        return false;
    }

    $sql_recipient = '';
    
    if ($recipient['id']) {
        $sql_recipient = "AND ship_to_id = '" . e($recipient['id']) . "'";
    }

    // Get all items in the cart.
    $items = db_items("
        SELECT product_id, SUM(quantity) AS quantity FROM order_items
        WHERE
            order_id = '" . e($_SESSION['ecommerce']['order_id']) . "'
            $sql_recipient
        GROUP BY product_id");

    $total_quantity = 0;

    // Loop through the items in the cart in order to determine if the required products and
    // quantity are in the cart.

    foreach ($items as $item) {

        // If this item is one of the required products, then increase the total quantity.
        if (array_find($products, 'id', $item['product_id'])) {

            $total_quantity += $item['quantity'];

            // If we have found a total quantity that is greater than or equal to the required
            // quantity, then the required products and quantity are in the cart.
            if ($total_quantity >= $quantity) {
                return true;
            }
        }
    }

    // If we have gotten here then the required products and quantity were not found in cart.
    return false;
}