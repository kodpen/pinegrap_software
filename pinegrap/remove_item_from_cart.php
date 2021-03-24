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

include('init.php');

validate_token_field();
initialize_order();

if (!$_SESSION['ecommerce']['order_id']) {
    output_error('Sorry, we can\'t remove an item from your cart, because you do not have an active cart.');
}

$order_item_id = $_GET['order_item_id'];

if (!$order_item_id) {
    output_error('Sorry, we don\'t know what item you want to remove from the cart, because "order_item_id" in the request is empty.');
}

$order_item = db_item(
    "SELECT id, order_id, ship_to_id FROM order_items WHERE id = '" . e($order_item_id) . "'");

if (!$order_item) {
    output_error('Sorry, we can\'t remove the item from your cart, because it does not exist. It might have already been removed.');
}

// If the order item is for a different order, then log and output error.
if ($order_item['order_id'] != $_SESSION['ecommerce']['order_id']) {
    log_activity('Access denied to remove item from different cart.');
    output_error('Sorry, we can\'t allow you to remove that item, because it is not in your cart.');
}

$ship_to_id = $order_item['ship_to_id'];

// delete order item
$query = "DELETE FROM order_items WHERE (id = '" . escape($order_item_id) . "') AND (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

db("DELETE FROM order_item_gift_cards WHERE order_item_id = '" . escape($order_item_id) . "'");

// delete product form data for this order item
$query = "DELETE FROM form_data WHERE (order_item_id = '" . escape($order_item_id) . "') AND (order_item_id != '0') AND (order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (order_id != '0')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// the cart has changed, so offers might have changed, so we need to refresh prices
update_order_item_prices();

// the cart has changed, so we need to refresh the cart with offers
apply_offers_to_cart();

// get all order items for ship to id, so we can figure out if we may delete ship to record
$query = "SELECT id FROM order_items WHERE ship_to_id = '$ship_to_id'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if there are no order items left for ship to, then delete ship to and determine where user should be sent
if (mysqli_num_rows($result) == 0) {
    $query = "DELETE FROM ship_tos WHERE id = '$ship_to_id'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // delete custom shipping form data for ship to
    $query = "DELETE FROM form_data WHERE (ship_to_id = '$ship_to_id') AND (ship_to_id != '0') AND (order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (order_id != '0')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if user removed item from a shipping method screen, check to see if there is another recipient to send user to
    if ($_GET['screen'] == 'shipping_method') {        
        // check to see if there is another recipient in this order that the user needs to fill out information for
        $query = "SELECT id FROM ship_tos WHERE (order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (complete = 0) AND (id > '$ship_to_id')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        // if there is another recipient, send user to shipping address & arrival screen for that recipient
        if (mysqli_num_rows($result) > 0) {
            // get next recipient ship to id
            $row = mysqli_fetch_assoc($result);
            $next_ship_to_id = $row['id'];

            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['shipping_address_and_arrival_page_id']) . '?ship_to_id=' . $next_ship_to_id);
            exit();
        // else there is not another recipient, so find out where we should send the user
        } else {
            // if user came from an express order page, then forward user to express order page
            if ($_SESSION['ecommerce']['express_order_page_id']) {
                header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['express_order_page_id']));
                exit();
                
            // else user did not come from an express order page, so figure out where user should be forwarded
            } else {
                // check to see if there is at least one item in cart
                $query = "SELECT id FROM order_items WHERE order_id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if there is at least one item in the cart
                if (mysqli_num_rows($result) > 0) {
                    // find out if billing information is complete
                    $query = "SELECT billing_complete FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $billing_complete = $row['billing_complete'];
                    
                    // if billing information is not complete, send user to billing information screen
                    if ($billing_complete == 0) {
                        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['billing_information_page_id']));
                        exit();
                        
                    // else billing information is complete, so send user to order preview screen
                    } else {
                        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['order_preview_page_id']));
                        exit();
                    }
                
                // else there is not at least one item in the cart, so send user to shopping cart
                } else {
                    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['shopping_cart_page_id']));
                    exit();
                }
            }
        }
    }
// else there are order items left for ship to
} else {
    // if there is a ship to for this order item
    if ($ship_to_id) {
        // check if ship to's shipping is complete
        $query = "SELECT complete FROM ship_tos WHERE id = '" . escape($ship_to_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $complete = $row['complete'];
        
        // if ship to is complete, then update shipping cost for ship to
        if ($complete == 1) {
            require_once(dirname(__FILE__) . '/shipping.php');
            update_shipping_cost_for_ship_to($ship_to_id);
        }
    }
}

// send user back to where he/she came from
header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $_GET['send_to']);