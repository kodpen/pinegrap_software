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

// Used by things like a myself upsell to add a product to the cart.  Only supports adding to myself
// recipient for now.

function add_to_cart($request) {

    $quantity = $request['quantity'];

    if (!$quantity) {
        $quantity = 1;
    }

    initialize_order();

    $item = array();

    $item['id'] = add_order_item($request['product']['id'], $quantity, $donation_amount = 0, 'myself', '');

    $form = '';

    if ($request['form']) {
        $form = new liveform($request['form']);
    }

    if (!$item['id']) {

        $message = 'Sorry, that product could not be added. The product might no longer exist or might have recently been disabled.';

        if ($form) {
            $form->mark_error('', $message);
        }

        return error_response($message);
    }

    if ($form and $request['notice']) {
        $form->add_notice($request['notice']);
    }

    return array(
        'status' => 'success',
        'item' => $item);
}