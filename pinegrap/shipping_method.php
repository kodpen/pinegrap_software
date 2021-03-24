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

// get order id for ship to
$query =
    "SELECT
        order_id,
        state,
        country
    FROM ship_tos
    WHERE id = '" . escape($_POST['ship_to_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if ship to is not found, output error
if (mysqli_num_rows($result) == 0) {
    output_error('The recipient could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);

// if order id for ship to is not equal to order id in session, output error
if ($row['order_id'] != $_SESSION['ecommerce']['order_id']) {
    output_error('You do not have access to this recipient. <a href="javascript:history.go(-1)">Go back</a>.');
}

// set state and country for ship to for later when we check to see if there are any invalid products
$state_code = $row['state'];
$country_code = $row['country'];

include_once('liveform.class.php');
$liveform = new liveform('shipping_method');

$liveform->add_fields_to_session();

$liveform->validate_required_field('shipping_method', 'Please select a shipping method.');

// if an error does not exist
if ($liveform->check_form_errors() == false) {
    /* begin: calculate shipping cost for selected shipping method */

    // get shipping method info
    $query =
        "SELECT
            name,
            code,
            service,
            realtime_rate,
            base_rate,
            variable_base_rate,
            base_rate_2,
            base_rate_2_subtotal,
            base_rate_3,
            base_rate_3_subtotal,
            base_rate_4,
            base_rate_4_subtotal,
            primary_weight_rate,
            primary_weight_rate_first_item_excluded,
            secondary_weight_rate,
            secondary_weight_rate_first_item_excluded,
            item_rate,
            item_rate_first_item_excluded
        FROM shipping_methods
        WHERE id = '" . escape($liveform->get_field_value('shipping_method')) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if shipping method cannot be found, then shipping method was recently deleted or someone is trying to hack, so output error
    if (mysqli_num_rows($result) == 0) {
        output_error('The selected shipping method could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    $row = mysqli_fetch_assoc($result);
    $shipping_method_name = $row['name'];
    $shipping_method_code = $row['code'];
    $shipping_method_service = $row['service'];
    $shipping_method_realtime_rate = $row['realtime_rate'];
    $shipping_method_base_rate = $row['base_rate'];
    $shipping_method_variable_base_rate = $row['variable_base_rate'];
    $shipping_method_base_rate_2 = $row['base_rate_2'];
    $shipping_method_base_rate_2_subtotal = $row['base_rate_2_subtotal'];
    $shipping_method_base_rate_3 = $row['base_rate_3'];
    $shipping_method_base_rate_3_subtotal = $row['base_rate_3_subtotal'];
    $shipping_method_base_rate_4 = $row['base_rate_4'];
    $shipping_method_base_rate_4_subtotal = $row['base_rate_4_subtotal'];
    $shipping_method_primary_weight_rate = $row['primary_weight_rate'];
    $shipping_method_primary_weight_rate_first_item_excluded = $row['primary_weight_rate_first_item_excluded'];
    $shipping_method_secondary_weight_rate = $row['secondary_weight_rate'];
    $shipping_method_secondary_weight_rate_first_item_excluded = $row['secondary_weight_rate_first_item_excluded'];
    $shipping_method_item_rate = $row['item_rate'];
    $shipping_method_item_rate_first_item_excluded = $row['item_rate_first_item_excluded'];

    $zones = get_valid_zones($_POST['ship_to_id'], $country_code, $state_code);

    // loop through all valid zones in order to find a zone that is allowed for this shipping method
    foreach ($zones as $zone_id) {
        $query = "SELECT shipping_method_id
                 FROM shipping_methods_zones_xref
                 WHERE (shipping_method_id = '" . escape($liveform->get_field_value('shipping_method')) . "') AND (zone_id = '$zone_id')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if this zone is allowed for selected shipping method, get zone info
        if (mysqli_num_rows($result) > 0) {
            // get zone info
            $query = "SELECT base_rate, primary_weight_rate, secondary_weight_rate, item_rate FROM zones WHERE id = '$zone_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);

            $zone_base_rate = $row['base_rate'];
            $zone_primary_weight_rate = $row['primary_weight_rate'];
            $zone_secondary_weight_rate = $row['secondary_weight_rate'];
            $zone_item_rate = $row['item_rate'];

            // we have found a zone, so we need to break out of this loop
            break;
        }
    }

    // get all products in cart for this ship to
    $order_items = db_items(
        "SELECT
            order_items.id,
            order_items.quantity,
            products.name,
            products.weight,
            products.primary_weight_points,
            products.secondary_weight_points,
            products.length,
            products.width,
            products.height,
            products.container_required,
            products.free_shipping,
            products.extra_shipping_cost
        FROM order_items
        LEFT JOIN products ON products.id = order_items.product_id
        WHERE order_items.order_id = '" . $_SESSION['ecommerce']['order_id'] . "' AND order_items.ship_to_id = '" . escape($_POST['ship_to_id']) . "'
        ORDER BY order_items.id ASC");

    $shipping_cost = 0;

    require_once(dirname(__FILE__) . '/shipping.php');

    $realtime_rate = get_shipping_realtime_rate(array(
        'ship_to_id' => $_POST['ship_to_id'],
        'service' => $shipping_method_service,
        'realtime_rate' => $shipping_method_realtime_rate,
        'items' => $order_items));

    // If there was an error getting the realtime rate, then output error.
    if ($realtime_rate === false) {
        output_error('Sorry, that shipping method is not currently available, because we could not find a real-time rate.');
    }

    $shipping_cost += $realtime_rate;

    $shipping_cost += get_shipping_method_base_rate(array(
        'base_rate' => $shipping_method_base_rate,
        'variable_base_rate' => $shipping_method_variable_base_rate,
        'base_rate_2' => $shipping_method_base_rate_2,
        'base_rate_2_subtotal' => $shipping_method_base_rate_2_subtotal,
        'base_rate_3' => $shipping_method_base_rate_3,
        'base_rate_3_subtotal' => $shipping_method_base_rate_3_subtotal,
        'base_rate_4' => $shipping_method_base_rate_4,
        'base_rate_4_subtotal' => $shipping_method_base_rate_4_subtotal,
        'ship_to_id' => $_POST['ship_to_id']));

    $shipping_cost += $zone_base_rate;

    // Create a counter so that later we can determine if the first item
    // should be excluded from shipping charges based on shipping method settings.
    $count = 0;

    // loop through all items in cart for this recipient in order to calculate shipping cost
    foreach ($order_items as $key => $value) {
        $quantity = $order_items[$key]['quantity'];
        $primary_weight_points = $order_items[$key]['primary_weight_points'];
        $secondary_weight_points = $order_items[$key]['secondary_weight_points'];
        $free_shipping = $order_items[$key]['free_shipping'];
        $extra_shipping_cost = $order_items[$key]['extra_shipping_cost'];

        // if this is not a free shipping product, calculate shipping cost for product
        if (($free_shipping == 0)) {
            $count++;

            $shipping_method_primary_weight_rate_quantity = $quantity;
            $shipping_method_secondary_weight_rate_quantity = $quantity;
            $shipping_method_item_rate_quantity = $quantity;

            // If this is the first order item that has a shipping charge,
            // then check if we need to reduce the quantity in order to exclude the first item
            // from shipping charges.
            if ($count == 1) {
                // If the first item should be excluded for the primary weight calculation
                // for the shipping method, then reduce the quantity by one.
                if ($shipping_method_primary_weight_rate_first_item_excluded == 1) {
                    $shipping_method_primary_weight_rate_quantity--;
                }

                // If the first item should be excluded for the secondary weight calculation
                // for the shipping method, then reduce the quantity by one.
                if ($shipping_method_secondary_weight_rate_first_item_excluded == 1) {
                    $shipping_method_secondary_weight_rate_quantity--;
                }

                // If the first item should be excluded for the item calculation
                // for the shipping method, then reduce the quantity by one.
                if ($shipping_method_item_rate_first_item_excluded == 1) {
                    $shipping_method_item_rate_quantity--;
                }
            }

            $shipping_method_cost = ($primary_weight_points * $shipping_method_primary_weight_rate * $shipping_method_primary_weight_rate_quantity) + ($secondary_weight_points * $shipping_method_secondary_weight_rate * $shipping_method_secondary_weight_rate_quantity) + ($shipping_method_item_rate * $shipping_method_item_rate_quantity);
            $zone_cost = ($primary_weight_points * $zone_primary_weight_rate * $quantity) + ($secondary_weight_points * $zone_secondary_weight_rate * $quantity) + ($zone_item_rate * $quantity);
            $extra_shipping_cost = $extra_shipping_cost * $quantity;
            $shipping_cost_for_item = $shipping_method_cost + $zone_cost + $extra_shipping_cost;
            $shipping_cost = $shipping_cost + $shipping_cost_for_item;
            
            // update shipping cost column for order item record
            $shipping_cost_for_item_per_unit = round($shipping_cost_for_item / $quantity);
            $query = "UPDATE order_items SET shipping = '$shipping_cost_for_item_per_unit' WHERE id = '" . $order_items[$key]['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
    
    // prepare for checking for shipping discounts
    // we must do this here, because the order preview screen does not apply offers to cart
    // we don't use the apply_offers_to_cart function here for performance reasons
    $original_shipping_cost = 0;
    $offer = array();
    
    // if there is at least one active shipping discount offer, then continue with check
    if (check_if_active_shipping_discount_offer_exists() == TRUE) {
        $offer = get_best_shipping_discount_offer($_POST['ship_to_id'], $liveform->get_field_value('shipping_method'));
        
        // if a shipping discount offer was found, then discount shipping
        if ($offer != FALSE) {
            // remember the original shipping cost, before it is updated
            $original_shipping_cost = $shipping_cost;
            
            // update shipping cost to contain discount
            $shipping_cost = $shipping_cost - ($shipping_cost * ($offer['discount_shipping_percentage'] / 100));
        }
    }
    
    /* end: calculate shipping cost for selected shipping method */

    if ($shipping_method_code) {
        $shipping_method = $shipping_method_code;
    } else {
        $shipping_method = $shipping_method_name;
    }
    
    // update ship to
    $query =
        "UPDATE ship_tos
        SET
            shipping_method_id = '" . escape($liveform->get_field_value('shipping_method')) . "',
            shipping_method_code = '" . escape($shipping_method) . "',
            zone_id = '$zone_id',
            shipping_cost = '$shipping_cost',
            original_shipping_cost = '$original_shipping_cost',
            offer_id = '" . $offer['id'] . "',
            complete = '1'
        WHERE id = '" . escape($_POST['ship_to_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // remove liveform because software does not need it anymore
    $liveform->remove_form('shipping_method');

    // check to see if there is another recipient that is not complete in this order that the user needs to fill out information for
    $query = "SELECT id FROM ship_tos WHERE (order_id = '" . $_SESSION['ecommerce']['order_id'] . "') AND (complete = 0) AND (id > '" . escape($_POST['ship_to_id']) . "') ORDER BY id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');

    // if there is another recipient, send user to shipping address & arrival screen for that recipient
    if (mysqli_num_rows($result) > 0) {
        // get next recipient ship to id
        $row = mysqli_fetch_assoc($result);
        $next_ship_to_id = $row['id'];

        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['shipping_address_and_arrival_page_id']) . '?ship_to_id=' . $next_ship_to_id);
        
    // else there is not another recipient, so find out where we should send the user
    } else {
        // if user came from an express order page, then forward user to express order page
        if ($_SESSION['ecommerce']['express_order_page_id']) {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['express_order_page_id']));
            exit();
            
        // else user did not come from an express order page, so figure out where user should be forwarded
        } else {
            // find out if billing information is complete
            $query = "SELECT billing_complete FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $billing_complete = $row['billing_complete'];
            
            // if billing information is not complete, send user to billing information screen
            if ($billing_complete == 0) {
                header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_POST['next_page_id']));
                exit();
                
            // else billing information is complete, so send user to order preview screen
            } else {
                header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_SESSION['ecommerce']['order_preview_page_id']));
                exit();
            }
        }
    }

// else an error does exist
} else {
    // send user back to previous form
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($_POST['page_id']) . '?ship_to_id=' . $_POST['ship_to_id']);
}