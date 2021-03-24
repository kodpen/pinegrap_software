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
$user = validate_user();
validate_ecommerce_access($user);

switch($_GET['sort']) {
    case 'Name':
        $sort_column = 'name';
        break;

    case 'Type':
        $sort_column = 'type';
        break;

    case 'Last Modified':
        $sort_column = 'timestamp';
        break;

    default:
        $sort_column = 'timestamp';
        $asc_desc = 'DESC';
}

if ($_GET['sort']) {
    $asc_desc = $_GET['order'];
}

$query = "SELECT
            offer_actions.id,
            offer_actions.name,
            offer_actions.type,
            offer_actions.discount_order_amount,
            offer_actions.discount_order_percentage,
            offer_actions.discount_product_product_id,
            offer_actions.discount_product_amount,
            offer_actions.discount_product_percentage,
            offer_actions.add_product_product_id,
            offer_actions.add_product_quantity,
            offer_actions.add_product_discount_amount,
            offer_actions.add_product_discount_percentage,
            offer_actions.discount_shipping_percentage,
            user.user_username as user,
            offer_actions.timestamp as timestamp
        FROM offer_actions
        LEFT JOIN user ON offer_actions.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$number_of_results = 0;

while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    $name = $row['name'];
    $type = $row['type'];
    $discount_order_amount = $row['discount_order_amount'];
    $discount_order_percentage = $row['discount_order_percentage'];
    $discount_product_product_id = $row['discount_product_product_id'];
    $discount_product_amount = $row['discount_product_amount'];
    $discount_product_percentage = $row['discount_product_percentage'];
    $add_product_product_id = $row['add_product_product_id'];
    $add_product_quantity = $row['add_product_quantity'];
    $add_product_discount_amount = $row['add_product_discount_amount'];
    $add_product_discount_percentage = $row['add_product_discount_percentage'];
    $discount_shipping_percentage = $row['discount_shipping_percentage'];
    $username = $row['user'];
    $timestamp = $row['timestamp'];

    switch ($type) {
        case 'discount order':
            $discount_amount = $discount_order_amount;
            $discount_percentage = $discount_order_percentage;
            $product_name = '';
            $quantity = '';
            break;

        case 'discount product':
            $discount_amount = $discount_product_amount;
            $discount_percentage = $discount_product_percentage;

            // get product name
            $query = "SELECT name FROM products WHERE id = '$discount_product_product_id'";
            $result_2 = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row_2 = mysqli_fetch_assoc($result_2);
            $product_name = $row_2['name'];

            $quantity = '';
            break;

        case 'add product':
            $discount_amount = $add_product_discount_amount;
            $discount_percentage = $add_product_discount_percentage;

            // get product name
            $query = "SELECT name FROM products WHERE id = '$add_product_product_id'";
            $result_2 = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row_2 = mysqli_fetch_assoc($result_2);
            $product_name = $row_2['name'];

            $quantity = $add_product_quantity;
            break;
            
        case 'discount shipping':
            $discount_amount = '';
            $discount_percentage = $discount_shipping_percentage;
            $product_name = '';
            $quantity = '';
            break;
    }

    // if discount amount is set, then use amount for discount
    if ($discount_amount) {
        $discount = BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $discount_amount / 100);

    // else discount amount is not set, so use percentage for discount
    } elseif ($discount_percentage) {
        $discount = $discount_percentage . '%';

    // else set discount to emtpy
    } else {
        $discount = '';
    }

    $type = ucwords($type);

    $number_of_results++;

    $output_link_url = 'edit_offer_action.php?id=' . $id;
    $output_rows .=
        '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label">' . h($name) . '</td>
            <td>' . $type . '</td>
            <td style="text-align: right; padding-right: 1em;">' . $discount . '</td>
            <td>' . $product_name . '</td>
            <td>' . $quantity . '</td>
            <td>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

print
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar">
        <a href="add_offer_action.php">Create Offer Action</a>
    </div>
    <div id="content">
        
        <a href="#" id="help_link">Help</a>
        <h1>All Offer Actions</h1>
        <div class="subheading">All actions available to any offer.</div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . asc_or_desc('Name','view_offer_actions') . '</th>
                <th>' . asc_or_desc('Type','view_offer_actions') . '</th>
                <th style="text-align: right; padding-right: 1em;">Discount</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>' . asc_or_desc('Last Modified','view_offer_actions') . '</th>
            </tr>
            ' . $output_rows . '
        </table>
    </div>' .
    output_footer();
?>