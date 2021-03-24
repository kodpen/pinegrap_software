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

// Get ship to info.
$query =
    "SELECT
        ship_tos.id,
        ship_tos.order_id,
        ship_tos.salutation,
        ship_tos.first_name,
        ship_tos.last_name,
        ship_tos.company,
        ship_tos.address_1,
        ship_tos.address_2,
        ship_tos.city,
        ship_tos.state,
        ship_tos.zip_code,
        ship_tos.country,
        ship_tos.phone_number,
        ship_tos.shipping_method_code,
        shipping_methods.name AS shipping_method_name
    FROM ship_tos
    LEFT JOIN shipping_methods ON ship_tos.shipping_method_id = shipping_methods.id
    WHERE ship_tos.id = '" . escape($_GET['ship_to_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// If the ship to does not exist, then output error.
if (mysqli_num_rows($result) == 0) {
    output_error('The recipient could not be found. <a href="javascript:history.go(-1)">Go back</a>.');

// Otherwise the ship to exists, so store info.
} else {
    $ship_to = mysqli_fetch_assoc($result);
}

// Get order info.
$query =
    "SELECT
        order_number,
        order_date
    FROM orders
    WHERE orders.id = '" . $ship_to['order_id'] . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$order = mysqli_fetch_assoc($result);

// If there is an organization address 2, then output it.
if (ORGANIZATION_ADDRESS_2 != '') {
    $output_organization_address_2 = h(ORGANIZATION_ADDRESS_2) . '<br />';
}

// If there is an organization country, then output it.
if (ORGANIZATION_COUNTRY != '') {
    $output_organization_country = h(ORGANIZATION_COUNTRY);
}

// If there is a ship to salutation, then output it.
if ($ship_to['salutation'] != '') {
    $output_ship_to_salutation = h($ship_to['salutation']) . ' ';
}

// If there is a ship to company, then output it.
if ($ship_to['company'] != '') {
    $output_ship_to_company = h($ship_to['company']) . '<br />';
}

// If there is a ship to address 2, then output it.
if ($ship_to['address_2'] != '') {
    $output_ship_to_address_2 = h($ship_to['address_2']) . '<br />';
}

// If there is a ship to phone number, then output it.
if ($ship_to['phone_number'] != '') {
    if (language_ruler() === 'en'){
        $output_ship_to_phone_number = '<br />Phone: ' . h($ship_to['phone_number']);
    }
    else if (language_ruler() === 'tr'){
        $output_ship_to_phone_number = '<br />Telefon: ' . h($ship_to['phone_number']);
    }
}

$output_shipping_method = '';

if ($ship_to['shipping_method_name'] != '') {
    $output_shipping_method = h($ship_to['shipping_method_name']);

} else {
    $output_shipping_method = h($ship_to['shipping_method_code']);
}

$number_of_columns = 3;

$output_custom_field_1_heading = '';

// If the first custom product field is active, then output heading for it.
if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
    $number_of_columns++;

    $output_custom_field_1_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . '</th>';
}

$output_custom_field_2_heading = '';

// If the second custom product field is active, then output heading for it.
if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
    $number_of_columns++;

    $output_custom_field_2_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . '</th>';
}

$output_custom_field_3_heading = '';

// If the third custom product field is active, then output heading for it.
if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
    $number_of_columns++;

    $output_custom_field_3_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . '</th>';
}

$output_custom_field_4_heading = '';

// If the fourth custom product field is active, then output heading for it.
if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
    $number_of_columns++;

    $output_custom_field_4_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . '</th>';
}

// Get all order items for this ship to.
$query =
    "SELECT
        order_items.id,
        order_items.product_name AS name,
        products.short_description,
        order_items.quantity,
        products.custom_field_1,
        products.custom_field_2,
        products.custom_field_3,
        products.custom_field_4,
        products.form,
        products.form_name,
        products.form_label_column_width,
        products.form_quantity_type
    FROM order_items
    LEFT JOIN products ON order_items.product_id = products.id
    WHERE order_items.ship_to_id = '" . $ship_to['id'] . "'
    ORDER BY order_items.id ASC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$order_items = mysqli_fetch_items($result);

$output_order_item_rows = '';

// Loop through all order items in order to output them.
foreach ($order_items as $order_item) {
    $output_custom_field_1_cell = '';

    // If the first custom product field is active, then output cell for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
        $output_custom_field_1_cell = '<td>' . h($order_item['custom_field_1']) . '</td>';
    }

    $output_custom_field_2_cell = '';

    // If the second custom product field is active, then output cell for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
        $output_custom_field_2_cell = '<td>' . h($order_item['custom_field_2']) . '</td>';
    }

    $output_custom_field_3_cell = '';

    // If the third custom product field is active, then output cell for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
        $output_custom_field_3_cell = '<td>' . h($order_item['custom_field_3']) . '</td>';
    }

    $output_custom_field_4_cell = '';

    // If the fourth custom product field is active, then output cell for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
        $output_custom_field_4_cell = '<td>' . h($order_item['custom_field_4']) . '</td>';
    }

    $output_order_item_rows .=
        '<tr>
            <td>' . h($order_item['name']) . '</td>
            <td>' . h($order_item['short_description']) . '</td>
            <td>' . number_format($order_item['quantity']) . '</td>
            ' . $output_custom_field_1_cell . '
            ' . $output_custom_field_2_cell . '
            ' . $output_custom_field_3_cell . '
            ' . $output_custom_field_4_cell . '
        </tr>';

    // assume that there is not a form to output until we find out otherwse
    $output_forms = '';
    
    // if there is a form for this product, then prepare to output form
    if ($order_item['form'] == 1) {
        // if there should be one form per quantity, then set the number of forms to the quantity of this order item
        if ($order_item['form_quantity_type'] == 'One Form per Quantity') {
            // if the quantity is 100 or less, then set the number of forms to the quantity
            if ($order_item['quantity'] <= 100) {
                $number_of_forms = $order_item['quantity'];
                
            // else the quantity is greater than 100, so set the number of forms to 100
            } else {
                $number_of_forms = 100;
            }
            
        // else there should be one form per product, so set the number of forms to 1
        } elseif ($order_item['form_quantity_type'] == 'One Form per Product') {
            $number_of_forms = 1;
        }
        
        // create loop in order to output all forms
        for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
            $output_legend_content = '';
            
            // if there is a form name, then add form name to legend
            if ($order_item['form_name'] != '') {
                $output_legend_content .= h($order_item['form_name']);
            }
            
            // if number of forms is greater than 1, then add quantity number to legend
            if ($number_of_forms > 1) {
                $output_legend_content .= ' (' . $quantity_number . ' of ' . $number_of_forms . ')';
            }
            
            $output_legend = '';
            
            // if the legend content is not blank, then output a legend
            if ($output_legend_content != '') {
                $output_legend = '<legend>' . $output_legend_content . '</legend>';
            }
            
            $output_forms .=
                '<fieldset class="product_form" style="margin-top: .5em; margin-bottom: .5em">
                    ' . $output_legend . '
                    <table>
                        ' . get_submitted_product_form_content_with_form_fields($order_item['id'], $quantity_number) . '
                    </table>
                </fieldset>';
        }
    }
    
    // if there is a form to output, then prepare form row
    if ($output_forms != '') {
        $output_order_item_rows .=
            '<tr>
                <td>&nbsp;</td>
                <td colspan="' . ($number_of_columns - 1) . '">
                    ' . $output_forms . '
                </td>
            </tr>';
    }
}
if(language_ruler() == 'en'){
    $title = '<title>Packing Slip</title>';
}
elseif(language_ruler() == 'tr'){
    $title = '<title>Sevk İrsaliyesi</title>';
}

    echo
    '<!DOCTYPE html>
    
    <html lang="'.language_ruler().'">
        <head>
            <meta charset="utf-8">
            '.$title.'
            ' . get_generator_meta_tag() . '
            <script type="text/javascript">
                window.onload = function() {
                    window.print();
                };
                
                ' . output_control_panel_header_includes() . '
            <style type="text/css">
                body
                {
                    background-color: white;
                    color: black;
                    font-family: "Lucida Grande","Lucida Sans Unicode","Lucida Sans",Lucida,Arial,Verdana,sans-serif;
                    font-size: 85%;
                    margin: 0em;
                    padding: 1em;
                }

                h1,
                h2
                {
                    margin: 0em 0em .3em 0em;
                }

                table
                {
                    border-collapse: collapse;
                    width: 100%;
                }

                td
                {
                    text-align: left;
                    vertical-align: top;
                }

                th
                {
                    font-weight: bold;
                    text-align: left;
                }

                .order_items td,
                .order_items th
                {
                    border: #aaaaaa 1px solid;
                    padding: .3em;
                }

                fieldset
                {
                    border: #aaaaaa 1px solid;
                }

                .product_form table
                {
                    width: auto;
                }

                .product_form td
                {
                    border: none;
                }
            </style>
        </head>
        <body>
            <div id="content">
            <table style="margin-bottom: 1em">
                <tr>
                    <td style="padding-right: 1em">
                        <h1>Packing Slip</h1>
                      <div class="text" style="display:inline">Order</div> #: ' . $order['order_number'] . '<br/>
                       <div class="text" style="display:inline">Order Date:</div> <time>' . get_absolute_time(array('timestamp' => $order['order_date'])) . '</time>
                    </td>
                    <td>
                        <h2>' . h(ORGANIZATION_NAME) . '</h2>
                        <div>
                            ' . h(ORGANIZATION_ADDRESS_1) . '<br />
                            ' . $output_organization_address_2 . '
                            ' . h(ORGANIZATION_CITY) . ', ' . h(ORGANIZATION_STATE) . ' ' . h(ORGANIZATION_ZIP_CODE) . '<br />
                            ' . $output_organization_country . '
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding-right: 1em">
                        <h2 style="margin-top: .5em">Ship to:</h2>
                        <div style="margin-bottom: 1em">
                            ' . $output_ship_to_salutation . h($ship_to['first_name']) . ' ' . h($ship_to['last_name']) . '<br />
                            ' . $output_ship_to_company . '
                            ' . h($ship_to['address_1']) . '<br />
                            ' . $output_ship_to_address_2 . '
                            ' . h($ship_to['city']) . ', ' . h($ship_to['state']) . ' ' . h($ship_to['zip_code']) . '<br />
                            ' . h($ship_to['country']) . '
                            ' . $output_ship_to_phone_number . '
                        </div>
                    </td>
                    <td>
                        <p style="margin-top: 3.5em"><div class="text" style="display:inline">Shipping Method: </div>' . $output_shipping_method . '</p>
                    </td>
                </tr>
            </table>
            <table class="order_items">
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Qty</th>
                    ' . $output_custom_field_1_heading . '
                    ' . $output_custom_field_2_heading . '
                    ' . $output_custom_field_3_heading . '
                    ' . $output_custom_field_4_heading . '
                </tr>
                ' . $output_order_item_rows . '
            </table>
        </div>
        </body>
    </html>';


            