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
require_cookies();
initialize_order();

include_once('liveform.class.php');
$liveform = new liveform('catalog_detail');
$liveform->add_fields_to_session();

// get information about product
$query =
    "SELECT
        name,
        enabled,
        short_description,
        selection_type,
        price,
        default_quantity,
        inventory,
        inventory_quantity,
        backorder,
        shippable
    FROM products
    WHERE id = '" . escape($liveform->get_field_value('product_id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed');

// If a product could not be found, then output error.
if (mysqli_num_rows($result) == 0) {
    $liveform->mark_error('product_id', 'Sorry, we could not determine which item you wanted to add.  Please make sure you complete the selections.');
    go($liveform->get_field_value('current_url'));
}

$row = mysqli_fetch_assoc($result);

$name = $row['name'];
$enabled = $row['enabled'];
$short_description = $row['short_description'];
$selection_type = $row['selection_type'];
$price = $row['price'];
$default_quantity = $row['default_quantity'];
$inventory = $row['inventory'];
$inventory_quantity = $row['inventory_quantity'];
$backorder = $row['backorder'];
$shippable = $row['shippable'];

// if the product is not available because it is disabled or because of inventory issues,
// then add error and forward user back to previous page
if (
    ($enabled == 0)
    ||
    (
        ($inventory == 1)
        && ($inventory_quantity == 0)
        && ($backorder == 0)
    )
) {
    // prepare product description for error
    $product_description = '';
    
    // if there is a name, then add it to the description
    if ($name != '') {
        $product_description .= $name;
    }
    
    // if there is a short description, then add it to the description
    if ($short_description != '') {
        // if the description is not blank, then add separator
        if ($product_description != '') {
            $product_description .= ' - ';
        }
        
        $product_description .= $short_description;
    }
    
    $liveform->mark_error('product_id', 'Sorry, ' . h($product_description) . ' is not currently available.');
    header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('current_url'));
    exit();
}

// If multi-recipient shipping is enabled
// and this product is shippable,
// and a recipient was not selected,
// and the add name field is blank,
// then add error to ship to field and add name field,
// and send visitor back to previous screen.
if (
    (ECOMMERCE_SHIPPING == true)
    && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient')
    && ($shippable == 1)
    &&
    (
        ($liveform->get_field_value('ship_to') == '')
        || ($liveform->get_field_value('ship_to') == '- add name below -')
    )
    && ($liveform->get_field_value('add_name') == '')
) {
    $liveform->mark_error('ship_to', 'Please select or enter a recipient.');
    $liveform->mark_error('add_name');
    go($liveform->get_field_value('current_url'));
}

// If a quantity was entered, then remove commas.
if ($liveform->get_field_value('quantity') != '') {
    $liveform->assign_field_value('quantity', str_replace(',', '', $liveform->get_field_value('quantity')));
}

// if product is a donation
if ($selection_type == 'donation') {
    $quantity = 1;
    
    // If a quantity field was displayed to the user, and the quantity is valid,
    // then use that for the quantity.
    if (
        ($liveform->get_field_value('quantity') != '')
        && (preg_match('/^\d+$/', $liveform->get_field_value('quantity')) == 1)
    ) {
        $actual_quantity = $liveform->get_field_value('quantity');
        
    // Otherwise a quantity field was not displayed to the user, or the quantity is invalid,
    // so use default quantity.
    } else {
        $actual_quantity = $default_quantity;
    }
    
    $donation_amount = $price * $actual_quantity;
    
// else product is not a donation, so just prepare quantity
} else {
    // If the quantity is not valid, then output error.
    if (preg_match('/^\d+$/', $liveform->get_field_value('quantity')) == 0) {
        $liveform->mark_error('quantity', 'Please enter a valid quantity.');
        header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('current_url'));
        exit();
    }

    $quantity = $liveform->get_field_value('quantity');
    $donation_amount = 0;
}

// add order item to order
add_order_item($liveform->get_field_value('product_id'), $quantity, $donation_amount, $liveform->get_field_value('ship_to'), $liveform->get_field_value('add_name'));

// An item has been added to the order, so offers for this order
// need to be refreshed, so that the subtotal in a cart region is accurate.
update_order_item_prices();
apply_offers_to_cart();

// get next page id
$query = "SELECT next_page_id FROM catalog_detail_pages WHERE page_id = '" . escape($liveform->get_field_value('page_id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$next_page_id = $row['next_page_id'];

// send user to next page
header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($next_page_id)));

$liveform->remove_form();
?>