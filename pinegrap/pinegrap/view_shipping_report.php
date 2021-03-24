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

validate_ecommerce_report_access();

include_once('liveform.class.php');
$liveform = new liveform('view_shipping_report');

// Store all values collected in request to session.
foreach ($_REQUEST as $key => $value) {
    // If the value is a string then add it to the session.  We have to do this
    // check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings.
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_shipping_report'][$key] = trim($value);
    }
}

// If filters are not set yet, then set defaults.
if (isset($_SESSION['software']['ecommerce']['view_shipping_report']['start_date']) == false) {
    $_SESSION['software']['ecommerce']['view_shipping_report']['start_date'] = date('Y-m-d');
    $_SESSION['software']['ecommerce']['view_shipping_report']['end_date'] = date('Y-m-d', strtotime('+13 day'));
    $_SESSION['software']['ecommerce']['view_shipping_report']['status'] = 'unshipped';
}

$start_date = $_SESSION['software']['ecommerce']['view_shipping_report']['start_date'];
$end_date = $_SESSION['software']['ecommerce']['view_shipping_report']['end_date'];

if (validate_date(prepare_form_data_for_output($start_date, 'date')) == false) {
    output_error('The start date is invalid. <a href="javascript:history.go(-1)">Go back</a>.');
}

if (validate_date(prepare_form_data_for_output($end_date, 'date')) == false) {
    output_error('The end date is invalid. <a href="javascript:history.go(-1)">Go back</a>.');
}

// If the date format is month and then day, then use that format.
if (DATE_FORMAT == 'month_day') {
    $month_and_day_format = 'F j';

// Otherwise the date format is day and then month, so use that format.
} else {
    $month_and_day_format = 'j F';
}

$output_date_range = date($month_and_day_format . ', Y', strtotime($start_date)) . ' - ' . date($month_and_day_format . ', Y', strtotime($end_date));

$number_of_days_in_date_range = round((strtotime($end_date) - strtotime($start_date)) / 86400);

$output_previous_start_date = date('Y-m-d', strtotime('-' . ($number_of_days_in_date_range + 1) . ' day', strtotime($start_date)));
$output_previous_end_date = date('Y-m-d', strtotime('-' . ($number_of_days_in_date_range + 1) . ' day', strtotime($end_date)));

$output_next_start_date = date('Y-m-d', strtotime('+' . ($number_of_days_in_date_range + 1) . ' day', strtotime($start_date)));
$output_next_end_date = date('Y-m-d', strtotime('+' . ($number_of_days_in_date_range + 1) . ' day', strtotime($end_date)));

// Create array to store all dates in the date range.
$dates = array();

// The first date is the start date.
$date = $start_date;

while ($date <= $end_date) {
    $dates[] = $date;

    // Prepare next date.
    $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
}

// Select option for status pick list and prepare SQL filter
// so that we only get order items for the selected status.

$status_any_selected = '';
$status_unshipped_selected = '';
$status_shipped_selected = '';
$sql_status_filter = "";

switch ($_SESSION['software']['ecommerce']['view_shipping_report']['status']) {
    case 'any':
        $status_any_selected = ' selected="selected"';
        break;

    case 'unshipped':
        $status_unshipped_selected = ' selected="selected"';
        $sql_status_filter = "AND (order_items.quantity > order_items.shipped_quantity)";
        break;
    
    case 'shipped':
        $status_shipped_selected = ' selected="selected"';
        $sql_status_filter = "AND (order_items.shipped_quantity > 0)";
        break;
}

$order_items = db_items(
    "SELECT
        order_items.quantity,
        order_items.shipped_quantity,
        products.id AS product_id,
        products.name,
        products.short_description,
        ship_tos.ship_date
    FROM order_items
    LEFT JOIN orders ON order_items.order_id = orders.id
    LEFT JOIN products ON order_items.product_id = products.id
    LEFT JOIN ship_tos ON order_items.ship_to_id = ship_tos.id
    WHERE
        (order_items.ship_to_id != '0')
        $sql_status_filter
        AND (orders.status != 'incomplete')
        AND (products.id IS NOT NULL)
        AND (ship_tos.ship_date != '0000-00-00')
    ORDER BY products.name ASC");

// Create array for product data in report.
$products = array();

// If there is at least one order item, then continue to prepare data for report.
if (count($order_items) > 0) {
    // Create an array that will be used to store total data for last row in table.
    $totals = array();
    $totals['before'] = 0;

    // Loop through the dates in the date range, in order to initialize values for each date.
    foreach ($dates as $date) {
        $totals[$date] = 0;
    }

    $totals['after'] = 0;
    $totals['total'] = 0;

    // Loop through the order items in order to prepare data for report.
    foreach ($order_items as $order_item) {
        // If this is the first order item for this product, then initialize array for product.
        if (isset($products[$order_item['product_id']]) == false) {
            $products[$order_item['product_id']] = array();
            $products[$order_item['product_id']]['total'] = 0;
            $products[$order_item['product_id']]['name'] = $order_item['name'];
            $products[$order_item['product_id']]['id'] = $order_item['product_id'];
            $products[$order_item['product_id']]['short_description'] = $order_item['short_description'];
            $products[$order_item['product_id']]['before'] = 0;

            // Loop through the dates in the date range, in order to initialize values for each date.
            foreach ($dates as $date) {
                $products[$order_item['product_id']][$date] = 0;
            }

            $products[$order_item['product_id']]['after'] = 0;
        }

        // Get the quantity differently based on the status.
        switch ($_SESSION['software']['ecommerce']['view_shipping_report']['status']) {
            case 'any':
                // If the ordered quantity is greater than the shipped quantity,
                // then use ordered quantity for the quantity.
                if ($order_item['quantity'] > $order_item['shipped_quantity']) {
                    $quantity = $order_item['quantity'];

                // Otherwise use the shipped quantity.
                } else {
                    $quantity = $order_item['shipped_quantity'];
                }

                break;

            case 'unshipped':
                $quantity = $order_item['quantity'] - $order_item['shipped_quantity'];
                break;
            
            case 'shipped':
                $quantity = $order_item['shipped_quantity'];
                break;
        }

        // If the ship date is in the before category, then add quantity to that category.
        if ($order_item['ship_date'] < $start_date) {
            $products[$order_item['product_id']]['before'] += $quantity;
            $totals['before'] += $quantity;

        // Otherwise if the ship date is in the date range, then increment value for the ship date.
        } else if (($order_item['ship_date'] >= $start_date) && ($order_item['ship_date'] <= $end_date)) {
            $products[$order_item['product_id']][$order_item['ship_date']] += $quantity;
            $totals[$order_item['ship_date']] += $quantity;

        // Otherwise if the ship date is in the after category, then add quantity to that category.
        } else if ($order_item['ship_date'] > $end_date) {
            $products[$order_item['product_id']]['after'] += $quantity;
            $totals['after'] += $quantity;
        }

        // Add quantity to total for this product.
        $products[$order_item['product_id']]['total'] += $quantity;

        $totals['total'] += $quantity;
    }
}

// If there is at least data for one product, then output report.
if (count($products) > 0) {
    $output_view_orders_shipping_status = '';

    // If the status is unshipped, then update shipping status on view orders screen,
    // for all links to view orders screen.
    if ($_SESSION['software']['ecommerce']['view_shipping_report']['status'] == 'unshipped') {
        $output_view_orders_shipping_status = '&amp;shipping_status=unshipped';
    }

    // If the date format is month and then day, then use that format.
    if (DATE_FORMAT == 'month_day') {
        $month_and_day_format = 'M j';

    // Otherwise the date format is day and then month, so use that format.
    } else {
        $month_and_day_format = 'j M';
    }

    $output_date_heading_cells = '';

    // Loop through the dates in order to get the date headings.
    foreach ($dates as $date) {
        $output_date_heading_cells .= '<th style="text-align: center"><a href="view_orders.php?reset=true&amp;status=complete_or_exported&amp;advanced_filters=true' . $output_view_orders_shipping_status . '&amp;date_type=ship_date&amp;start_month=' . date('m', strtotime($date)) . '&amp;start_day=' . date('d', strtotime($date)) . '&amp;start_year=' . date('Y', strtotime($date)) . '&amp;stop_month=' . date('m', strtotime($date)) . '&amp;stop_day=' . date('d', strtotime($date)) . '&amp;stop_year=' . date('Y', strtotime($date)) . '">' . date('D', strtotime($date)) . '<br />' . date($month_and_day_format, strtotime($date)) . '</a></th>';
    }

    rsort($products);

    $output_product_rows = '';

    // Loop through the products, in order to output product rows.
    foreach ($products as $product) {
        $output_description = '';
        
        // If there is a name, then add it to the description.
        if ($product['name'] != '') {
            $output_description .= h($product['name']);
        }
        
        // If there is a short description, then add it to the description.
        if ($product['short_description'] != '') {
            // If the description is not blank, then add separator.
            if ($output_description != '') {
                $output_description .= ' - ';
            }
            
            $output_description .= h($product['short_description']);
        }

        $output_before = '';

        // If the before value is not 0, then prepare to output value.
        if ($product['before'] > 0) {
            $output_before = number_format($product['before']);
        }

        $output_date_cells = '';

        // Loop through the dates in order to get the date cells for this product.
        foreach ($dates as $date) {
            $output_date_value = '';

            // If the value is not 0, then prepare to output value.
            if ($product[$date] > 0) {
                $output_date_value = number_format($product[$date]);
            }

            $output_date_cells .= '<td style="text-align: center">' . $output_date_value . '</td>';
        }

        $output_after = '';

        // If the after value is not 0, then prepare to output value.
        if ($product['after'] > 0) {
            $output_after = number_format($product['after']);
        }

        $output_total = '';

        // If the total value is not 0, then prepare to output value.
        if ($product['total'] > 0) {
            $output_total = number_format($product['total']);
        }

        $output_product_rows .=
            '<tr>
                <td class="chart_label pointer" onclick="window.location.href=\'edit_product.php?id=' . $product['id'] . '\'">' . $output_description . '</td>
                <td style="text-align: center">' . $output_before . '</td>
                ' . $output_date_cells . '
                <td style="text-align: center">' . $output_after . '</td>
                <td style="text-align: center">' . $output_total . '</td>
            </tr>';
    }

    $output_total_before = '';

    // If the before value is not 0, then prepare to output value.
    if ($totals['before'] > 0) {
        $output_total_before = number_format($totals['before']);
    }

    $output_total_date_cells = '';

    // Loop through the dates in order to get the date cells for the total row.
    foreach ($dates as $date) {
        $output_total_date_value = '';

        // If the value is not 0, then prepare to output value.
        if ($totals[$date] > 0) {
            $output_total_date_value = number_format($totals[$date]);
        }

        $output_total_date_cells .= '<td style="font-weight:bold; text-align: center">' . $output_total_date_value . '</td>';
    }

    $output_total_after = '';

    // If the after value is not 0, then prepare to output value.
    if ($totals['after'] > 0) {
        $output_total_after = number_format($totals['after']);
    }

    $output_grand_total = '';

    // If the grand total value is not 0, then prepare to output value.
    if ($totals['total'] > 0) {
        $output_grand_total = number_format($totals['total']);
    }

    $output_report =
        '<table class="chart shipping_report" style="margin-bottom: 1em">
            <tr>
                <th style="width: 20%">Product</th>
                <th style="text-align: center"><a href="view_orders.php?reset=true&amp;status=complete_or_exported&amp;advanced_filters=true' . $output_view_orders_shipping_status . '&amp;date_type=ship_date&amp;start_month=01&amp;start_day=01&amp;start_year=2000&amp;stop_month=' . date('m', strtotime('-1 day', strtotime($start_date))) . '&amp;stop_day=' . date('d', strtotime('-1 day', strtotime($start_date))) . '&amp;stop_year=' . date('Y', strtotime('-1 day', strtotime($start_date))) . '">Before</a></th>
                ' . $output_date_heading_cells . '
                <th style="text-align: center"><a href="view_orders.php?reset=true&amp;status=complete_or_exported&amp;advanced_filters=true' . $output_view_orders_shipping_status . '&amp;date_type=ship_date&amp;start_month=' . date('m', strtotime('+1 day', strtotime($end_date))) . '&amp;start_day=' . date('d', strtotime('+1 day', strtotime($end_date))) . '&amp;start_year=' . date('Y', strtotime('+1 day', strtotime($end_date))) . '&amp;stop_month=12&amp;stop_day=31&amp;stop_year=2030">After</a></th>
                <th style="text-align: center"><a href="view_orders.php?reset=true&amp;status=complete_or_exported&amp;advanced_filters=true' . $output_view_orders_shipping_status . '&amp;date_type=ship_date&amp;start_month=01&amp;start_day=01&amp;start_year=2000&amp;stop_month=12&amp;stop_day=31&amp;stop_year=2030">Total</a></th>
            </tr>
            ' . $output_product_rows . '
            <tr style="background-color: white; color: #c49700; font-weight: bold">
                <td style="font-weight: bold">Grand Total</td>
                <td style="font-weight: bold; text-align: center">' . $output_total_before . '</td>
                ' . $output_total_date_cells . '
                <td style="font-weight: bold; text-align: center">' . $output_total_after . '</td>
                <td style="font-weight: bold; text-align: center">' . $output_grand_total . '</td>
            </tr>
        </table>';

// Otherwise there is not any data for any products, so output notice.
} else {
    $output_report = 'Sorry, there does not appear to be any shipping info for that date range.  Shipping info will appear for this report once shippable Products are purchased.';
}

print
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar"><a href="view_ship_date_adjustments.php">Ship Date Adjustments</a></div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>Shipping Report</h1>
        <div class="subheading">View the number of Products that need to be shipped or have been shipped on different days.</div>
        <table style="border-collapse: collapse; margin-top: 1em; margin-bottom: 1em; width: 100%">
            <tr>
                <td style="padding: 0">
                    <span style="display: block; font-size: 150%; font-weight: bold; margin-bottom: .5em;">' . $output_date_range . '</span>
                    <a href="view_shipping_report.php?start_date=' . $output_previous_start_date . '&end_date=' . $output_previous_end_date . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_shipping_report.php?start_date=' . $output_next_start_date . '&end_date=' . $output_next_end_date . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>
                </td>
                <td style="padding: 0; text-align: right; vertical-align: bottom">
                    <form id="update_status" method="get">
                        Status: <select name="status" onchange="document.getElementById(\'update_status\').submit()"><option value="any"' . $status_any_selected . '>[Any]</option><option value="unshipped"' . $status_unshipped_selected . '>Unshipped</option><option value="shipped"' . $status_shipped_selected . '>Shipped</option></select>
                    </form>
                </td>
            </tr>
        </table>
        ' . $output_report . '
    </div>' .
    output_footer();
?>