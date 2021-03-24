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

// This script is used by various features to update the session
// for later use and then redirect the visitor to a URL.

include('init.php');

// Do different things depending on the action.
switch ($_GET['action']) {

    // This action deals with storing a watcher in the session so that it is added
    // when an order is submitted where a product submit form feature is used.
    // Example: do.php?action=add_watcher_for_product_submit_form&watcher=username&url=/example
    case 'add_watcher_for_product_submit_form':
        $_SESSION['software']['product_submit_form']['add_watcher'] = $_GET['watcher'];
        break;

    // This action deals with storing a field name and value in the session,
    // so once a product is added to the cart, a specific field can be prefilled,
    // with a certain value, based on where the visitor came from.
    // Example: do.php?action=prefill_product_form&field_name=conversation_number&field_value=ABC123&url=/example
    case 'prefill_product_form':
        unset($_SESSION['software']['prefill_product_form']);
        
        $_SESSION['software']['prefill_product_form'] = array();

        $_SESSION['software']['prefill_product_form'][] = array(
            'name' => trim($_GET['field_name']),
            'value' => trim($_GET['field_value'])
        );

        break;

    // This action removes the current cart from the visitor's session,
    // so that if the visitor adds more products to his/her cart,
    // they will be added to a new order.  This is often used
    // by people who are creating quotes for different customers,
    // and want to keep the orders separate.
    // Example: do.php?action=reset_order&url=/example
    case 'reset_order':
        unset($_SESSION['ecommerce']['order_id']);
        break;

    // This action sets a start page in the session that will
    // be set for a user if the user registers via registration entrance
    // or membership entrance.
    // Example: do.php?action=set_start_page&page_id=123&url=/example
    case 'set_start_page':
        $_SESSION['software']['start_page_id'] = $_GET['page_id'];
        break;

}

go($_GET['url']);
?>