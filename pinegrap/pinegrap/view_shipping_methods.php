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

    case 'Message':
        $sort_column = 'description';
        break;

    case 'Code':
        $sort_column = 'code';
        break;

    case 'Service':
        $sort_column = 'service';
        break;

    case 'Real-Time Rate':
        $sort_column = 'realtime_rate';
        break;

    case 'Base Rate':
        $sort_column = 'base_rate';
        break;

    case 'Primary W. Rate':
        $sort_column = 'primary_weight_rate';
        break;

    case 'Secondary W. Rate':
        $sort_column = 'secondary_weight_rate';
        break;

    case 'Item Rate':
        $sort_column = 'item_rate';
        break;

    case 'Handling':
        $sort_column = 'handle_days';
        break;

    case 'Transit':
        $sort_column = 'base_transit_days';
        break;

    case 'Street':
        $sort_column = 'street_address';
        break;

    case 'PO Box':
        $sort_column = 'po_box';
        break;

    case 'Protected':
        $sort_column = 'protected';
        break;

    case 'Last Modified':
        $sort_column = 'timestamp';
        break;
    default:
        $sort_column = 'timestamp';
}

if ($_GET['sort']) {
    $asc_desc = $_GET['order'];
} else {
    $asc_desc = 'asc';
}

if (($sort_column == 'timestamp') && (!$_GET['order'])) {
    $asc_desc = 'desc';
}

$query = "SELECT
            shipping_methods.id,
            shipping_methods.name,
            shipping_methods.description,
            shipping_methods.code,
            shipping_methods.status,
            shipping_methods.start_time,
            shipping_methods.end_time,
            shipping_methods.service,
            shipping_methods.realtime_rate,
            shipping_methods.base_rate,
            shipping_methods.variable_base_rate,
            shipping_methods.base_rate_2,
            shipping_methods.base_rate_2_subtotal,
            shipping_methods.base_rate_3,
            shipping_methods.base_rate_3_subtotal,
            shipping_methods.base_rate_4,
            shipping_methods.base_rate_4_subtotal,
            shipping_methods.primary_weight_rate,
            shipping_methods.secondary_weight_rate,
            shipping_methods.item_rate,
            shipping_methods.handle_days,
            shipping_methods.base_transit_days,
            shipping_methods.street_address,
            shipping_methods.po_box,
            shipping_methods.protected,
            user.user_username as user,
            shipping_methods.timestamp
        FROM shipping_methods
        LEFT JOIN user ON shipping_methods.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$number_of_results = 0;

while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    $name = $row['name'];
    $description = $row['description'];
    $code = $row['code'];
    $status = $row['status'];
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];
    $service = $row['service'];
    $base_rate = $row['base_rate'];
    $variable_base_rate = $row['variable_base_rate'];
    $base_rate_2 = $row['base_rate_2'];
    $base_rate_2_subtotal = $row['base_rate_2_subtotal'];
    $base_rate_3 = $row['base_rate_3'];
    $base_rate_3_subtotal = $row['base_rate_3_subtotal'];
    $base_rate_4 = $row['base_rate_4'];
    $base_rate_4_subtotal = $row['base_rate_4_subtotal'];
    $primary_weight_rate = sprintf("%01.2lf", $row['primary_weight_rate'] / 100);
    $secondary_weight_rate = sprintf("%01.2lf", $row['secondary_weight_rate'] / 100);
    $item_rate = sprintf("%01.2lf", $row['item_rate'] / 100);
    $handle_days = $row['handle_days'];
    $base_transit_days = $row['base_transit_days'];
    $username = $row['user'];
    $timestamp = $row['timestamp'];
    
    $current_time = date('Y-m-d H:i:s');
    
    // if the shipping method is active, use green status color
    if (($status == 'enabled') && ($start_time <= $current_time) && ($end_time >= $current_time)) {
        $status_color = '#009900';
    
    // else shipping method is inactive, so use red status color
    } else {
        $status_color = '#ff0000';
    }

    $realtime_rate = '';

    if ($row['realtime_rate']) {
        $realtime_rate = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">';
    }

    $output_base_rate = '';

    // If variable base rate is enabled, then show all base rates.
    if ($variable_base_rate) {
        $output_base_rate .= BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate / 100) . ' (@' . BASE_CURRENCY_SYMBOL . '0.00)';

        if ($base_rate_2_subtotal) {
            $output_base_rate .= '<br>' . BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate_2 / 100) . ' (@' . BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate_2_subtotal / 100) . ')';
        }

        if ($base_rate_3_subtotal) {
            $output_base_rate .= '<br>' . BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate_3 / 100) . ' (@' . BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate_3_subtotal / 100) . ')';
        }

        if ($base_rate_4_subtotal) {
            $output_base_rate .= '<br>' . BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate_4 / 100) . ' (@' . BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate_4_subtotal / 100) . ')';
        }

    // Otherwise variable base rate is disabled, so just show the base rate.
    } else {
        $output_base_rate = BASE_CURRENCY_SYMBOL . sprintf("%01.2lf", $base_rate / 100);
    }

    // For handle days, show blank instead of zero.
    if (!$handle_days) {
        $handle_days = '';
    }

    if ($row['street_address']) {
        $street_address = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    } else {
        $street_address = '';
    }

    if ($row['po_box']) {
        $po_box = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    } else {
        $po_box = '';
    }

    $zones = db_values(
        "SELECT zones.name
        FROM shipping_methods_zones_xref
        LEFT JOIN zones ON shipping_methods_zones_xref.zone_id = zones.id
        WHERE shipping_methods_zones_xref.shipping_method_id = '" . e($id) . "'
        ORDER BY zones.name");

    $output_zones = '';

    foreach ($zones as $zone) {

        // If this is not the first zone then output a comma and a break tag.
        if ($output_zones) {
            $output_zones .= ',<br>';
        }
        
        $output_zones .= h($zone);
    }

    if ($row['protected']) {
        $protected = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    } else {
        $protected = '';
    }

    $output_link_url ='edit_shipping_method.php?id=' . $id;
    
    $number_of_results++;
    
    $output_rows .=
        '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label" style="color: ' . $status_color . '">' . h($name) . '</td>
            <td>' . h($description) . '</td>
            <td>' . h($code) . '</td>
            <td>' . h(get_shipping_service_name($service)) . '</td>
            <td style="text-align: center">' . $realtime_rate . '</td>
            <td style="text-align: right; white-space: nowrap">' . $output_base_rate . '</td>
            <td style="text-align: right;">' . BASE_CURRENCY_SYMBOL . $primary_weight_rate . '</td>
            <td style="text-align: right;">' . BASE_CURRENCY_SYMBOL . $secondary_weight_rate . '</td>
            <td style="text-align: right;">' . BASE_CURRENCY_SYMBOL . $item_rate . '</td>
            <td style="text-align: right;">' . h($handle_days) . '</td>
            <td style="text-align: right;">' . $base_transit_days . '</td>
            <td style="text-align: center;">' . $street_address . '</td>
            <td style="text-align: center;">' . $po_box . '</td>
            <td>' . $output_zones . '</td>
            <td style="text-align: center;">' . $protected . '</td>
            <td>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

echo
output_header() . '
<div id="subnav">
    ' . render(array('template' => 'commerce_subnav.php')) . '
</div>
<div id="button_bar">
    <a href="add_shipping_method.php">Create Shipping Method</a>    
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All Shipping Methods</h1>
    <div class="subheading">All shipping options and fees, based on the carrier, product, destination, and arrival date.</div>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
    </div>
    <table class="chart">
        <tr>
            <th>' . asc_or_desc('Name','view_shipping_methods') . '</th>
            <th>' . asc_or_desc('Message','view_shipping_methods') . '</th>
            <th>' . asc_or_desc('Code','view_shipping_methods') . '</th>
            <th>' . asc_or_desc('Service','view_shipping_methods') . '</th>
            <th style="text-align: center">' . asc_or_desc('Real-Time Rate','view_shipping_methods') . '</th>
            <th style="text-align: right;">' . asc_or_desc('Base Rate','view_shipping_methods') . '</th>
            <th style="text-align: right;">' . asc_or_desc('Primary W. Rate','view_shipping_methods') . '</th>
            <th style="text-align: right;">' . asc_or_desc('Secondary W. Rate','view_shipping_methods') . '</th>
            <th style="text-align: right;">' . asc_or_desc('Item Rate','view_shipping_methods') . '</th>
            <th style="text-align: right;">' . asc_or_desc('Handling','view_shipping_methods') . '</th>
            <th style="text-align: right;">' . asc_or_desc('Transit','view_shipping_methods') . '</th>
            <th style="text-align: center;">' . asc_or_desc('Street','view_shipping_methods') . '</th>
            <th style="text-align: center;">' . asc_or_desc('PO Box','view_shipping_methods') . '</th>
            <th>Zones</th>
            <th style="text-align: center;">' . asc_or_desc('Protected','view_shipping_methods') . '</th>
            <th>' . asc_or_desc('Last Modified','view_shipping_methods') . '</th>
        </tr>
        ' . $output_rows . '
    </table>
</div>' .
output_footer();