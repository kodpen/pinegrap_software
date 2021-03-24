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

$liveform = new liveform('view_products');

$user = validate_user();
validate_ecommerce_access($user);

$output_clear_button = '';

// If there is a query then store it in a session
if (isset($_REQUEST['query']) == true) {
    $_SESSION['software']['view_products']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['view_products']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['view_products']['query']) == true) && ($_SESSION['software']['view_products']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

// If there is a filter then store it in a session
if ($_GET['filter']) {
    $_SESSION['software']['view_products']['filter'] = $_GET['filter'];
}

// if filter session is blank then set it to the default all products
if ($_SESSION['software']['view_products']['filter'] == '') {
    $_SESSION['software']['view_products']['filter'] = 'all_products';
}

$filter = $_SESSION['software']['view_products']['filter'];

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['ecommerce']['view_products']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['ecommerce']['view_products']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    $_SESSION['software']['ecommerce']['view_products']['order'] = $_REQUEST['order'];
}

// If the sort is not set, then set to default.
if ($_SESSION['software']['ecommerce']['view_products']['sort'] == '') {
    $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
    $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
}

// If a screen was passed and it is a positive integer, then use it.
// These checks are necessary in order to avoid SQL errors below for a bogus screen value.
if (
    $_REQUEST['screen']
    and is_numeric($_REQUEST['screen'])
    and $_REQUEST['screen'] > 0
    and $_REQUEST['screen'] == round($_REQUEST['screen'])
) {
    $screen = (int) $_REQUEST['screen'];

// Otherwise, use the default, which is the first screen.
} else {
    $screen = 1;
}

// Set where statement and page headers for the filter
switch ($filter) {
    case 'all_product_actions':
        // if the sort session does not apply to this screen then reset it to the default
        if(($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Price')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Set Start Page')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Grant Private Access')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Membership Renewal')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Order Receipt Message')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Order Receipt BCC')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'E-mail Page')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'E-mail Page BCC')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Contact Group')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SEO')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Inventory Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')) {
            
            $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
            $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
        }
        
        $sql_join = "LEFT JOIN contact_groups ON contact_groups.id = products.contact_group_id ";
        
        // Change the heading and subheading.
        $heading = 'All Product Actions';
        $subheading = 'Actions triggered by each product when it is ordered.';
        
        // select the filter option
        $all_product_actions_filter_selected = ' selected="selected"';
        break;
        
    case 'shippable_products':
        // if the sort session does not apply to this screen then reset it to the default
        if(($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Price')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Taxable')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Product Form')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Weight')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'PWP')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SWP')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Dim')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Cont Req')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Prep')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Free Ship')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Extra Ship')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SEO')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Inventory Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')) {
            
            $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
            $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
        }
        
        // If where is blank
        if ($where == '') {
            $where .= "WHERE ";
        
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        // set where statement
        $where .= "shippable = '1'";
        
        // Change the heading and subheading.
        $heading = 'Shippable Products';
        $subheading = 'All products that can be shipped to a recipient.';
        
        // select the filter option
        $shippable_product_filter_selected = ' selected="selected"';
        break;
    case 'recurring_products':
        // if the sort session does not apply to this screen then reset it to the default
        if(($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Price')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Taxable')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Product Form')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Start')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Number of Payments')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Payment Period')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SEO')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Inventory Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')) {
            
            $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
            $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
        }
        
        // If where is blank
        if ($where == '') {
            $where .= "WHERE ";
        
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        // set where statement
        $where .= "recurring = '1'";
        
        // Change the heading and subheading.
        $heading = 'Recurring Products';
        $subheading = 'All products that require a recurring payment.';
        
        // select the filter option
        $recurring_product_filter_selected = ' selected="selected"';
        break;
    case 'donation_products':
        // if the sort session does not apply to this screen then reset it to the default
        if(($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Default Amount (Price)')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Taxable')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Product Form')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Recurring Payment')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Start')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Number of Payments')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Payment Period')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Allow to Schedule')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SEO')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Inventory Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')) {
            
            $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
            $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
        }
        
        // If where is blank
        if ($where == '') {
            $where .= "WHERE ";
        
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        // set where statement
        $where .= "selection_type = 'donation'";
        
        // Change the heading and subheading.
        $heading = 'Donation Products';
        $subheading = 'All products that allow donors to enter their own amount and optionally set their donation schedule.';
        
        // select the filter option
        $donation_product_filter_selected = ' selected="selected"';
        break;
    case 'grant_access_products':
        // if the sort session does not apply to this screen then reset it to the default
        if(($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Price')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Taxable')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Product Form')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Set Start Page')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Grant Private Access')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SEO')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Inventory Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')) {
            
            $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
            $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
        }
        
        // If where is blank
        if ($where == '') {
            $where .= "WHERE ";
        
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        // set where statement
        $where .= "grant_private_access = '1' AND (private_folder != '0' OR send_to_page != '0')";
        
        // Change the heading and subheading.
        $heading = 'Grant Access Products';
        $subheading = 'All products that grant access a private folder\'s pages and files.';
        
        // select the filter option
        $grant_access_product_filter_selected = ' selected="selected"';
        break;
    case 'membership_products':
        // if the sort session does not apply to this screen then reset it to the default
        if(($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Price')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Taxable')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Product Form')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Recurring Payment')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Set Start Page')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Membership Renewal')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SEO')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Inventory Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')) {
            
            $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
            $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
        }
        
        // If where is blank
        if ($where == '') {
            $where .= "WHERE ";
        
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        // set where statement
        $where .= "membership_renewal != '0'";
        
        // Change the heading and subheading.
        $heading = 'Membership Products';
        $subheading = 'All products that grant access to all member folders, and set or extend their membership days.';
        
        // select the filter option
        $membership_product_filter_selected = ' selected="selected"';
        break;
        
        case 'out_of_stock_products':
          
                
            // if the sort session does not apply to this screen then reset it to the default
            if(
                ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Price')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Taxable')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Product Form')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Selection Type')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Default Quantity')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Shippable')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL)
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL)
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL)
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL)
                && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Out of Stock Date')
            ) {
                $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Out of Stock Date';
                $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
            }
            // If where is blank
            if ($where == '') {
                $where .= "WHERE ";
            
            // else where is not blank, so add and
            } else {
                $where .= "AND ";
            }
            // set where statement
            $where .= "out_of_stock = '1'";
            // Change the heading and subheading.
            $heading = 'All Out of Stock Products';
            $subheading = 'All out of stock products, Products purchased by customers and out of stock.';
            
            // select the filter option
            $out_of_stock_products_filter_selected = ' selected="selected"';
            break;
    case 'all_products':
    default:
        
        // if the sort session does not apply to this screen then reset it to the default
        if(
            ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'ID')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Short Description')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Enabled')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Price')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Taxable')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Product Form')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Selection Type')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Default Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Shippable')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Recurring Payment')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'SEO')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Inventory Quantity')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != 'Last Modified')
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL)
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL)
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL)
            && ($_SESSION['software']['ecommerce']['view_products']['sort'] != ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL)
        ) {
            $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
            $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
        }
        
        // Change the heading and subheading.
        $heading = 'All Products';
        $subheading = 'Merchandise, downloads, donations, recurring fees, memberships, and simple payments.';
        
        // select the filter option
        $all_products_filter_selected = ' selected="selected"';
        break;
}

switch ($_SESSION['software']['ecommerce']['view_products']['sort']) {
    case 'ID':
        $sort_column = 'products.name';
        break;

    case 'Enabled':
        $sort_column = 'products.enabled';
        break;

    case 'Short Description':
        $sort_column = 'products.short_description';
        break;
    case 'Price':
    case 'Default Amount (Price)':
        $sort_column = 'products.price';
        break;
    case 'Taxable':
        $sort_column = 'products.taxable';
        break;
    case 'Product Form':
        $sort_column = 'products.form_name';
        break;
    case 'Selection Type':
        $sort_column = 'products.selection_type';
        break;
    case 'Default Quantity':
        $sort_column = 'products.default_quantity';
        break;
    case 'Shippable':
        $sort_column = 'products.shippable';
        break;
    case 'Weight':
        $sort_column = 'products.weight';
        break;
    case 'PWP':
        $sort_column = 'products.primary_weight_points';
        break;
    case 'SWP':
        $sort_column = 'products.secondary_weight_points';
        break;
    case 'Dim':
        $sort_column = 'products.length';
        break;
    case 'Cont Req':
        $sort_column = 'products.container_required';
        break;
    case 'Prep':
        $sort_column = 'products.preparation_time';
        break;
    case 'Free Ship':
        $sort_column = 'products.free_shipping';
        break;
    case 'Extra Ship':
        $sort_column = 'products.extra_shipping_cost';
        break;
    case 'Recurring Payment':
        $sort_column = 'products.recurring';
        break;
    case 'SEO':
        $sort_column = 'products.seo_score';
        break;
    case 'Allow to Schedule':
        $sort_column = 'products.recurring_schedule_editable_by_customer';
        break;
    case 'Order Receipt Message':
        $sort_column = 'products.order_receipt_message';
        break;
    case 'Order Receipt BCC':
        $sort_column = 'products.order_receipt_bcc_email_address';
        break;
    case 'E-mail Page':
        $sort_column = 'products.email_page';
        break;
    case 'E-mail Page BCC':
        $sort_column = 'products.email_bcc';
        break;
    case 'Contact Group':
        $sort_column = 'contact_group_name';
        break;
    case 'Start':
        $sort_column = 'products.start';
        break;
    case 'Number of Payments':
        $sort_column = 'products.number_of_payments';
        break;
    case 'Payment Period':
        $sort_column = 'products.payment_period';
        break;
    case 'Set Start Page':
        $sort_column = 'start_page';
        break;
    case 'Grant Private Access':
        $sort_column = 'private_folder';
        break;
    case 'Membership Renewal':
        $sort_column = 'products.membership_renewal';
        break;

    case ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL:
        $sort_column = 'products.custom_field_1';
        break;

    case ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL:
        $sort_column = 'products.custom_field_2';
        break;

    case ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL:
        $sort_column = 'products.custom_field_3';
        break;

    case ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL:
        $sort_column = 'products.custom_field_4';
        break;
    case 'Inventory Quantity':
            $sort_column = 'products.inventory_quantity';
            break;
    case 'Last Modified':
        $sort_column = 'products.timestamp';
        break;
    case 'Out of Stock Date':
        $sort_column = 'products.out_of_stock_timestamp';
        break;
    default:
        $sort_column = 'products.timestamp';
        $_SESSION['software']['ecommerce']['view_products']['sort'] = 'Last Modified';
}

if ($_SESSION['software']['ecommerce']['view_products']['order']) {
    $asc_desc = $_SESSION['software']['ecommerce']['view_products']['order'];
} elseif ($sort_column == 'products.timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['ecommerce']['view_products']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['ecommerce']['view_products']['order'] = 'asc';
}

$search_query = mb_strtolower($_SESSION['software']['view_products']['query']);

// create where clause for sql
$sql_search =
    "((products.name LIKE '%" . escape($search_query) . "%')
    OR (products.short_description LIKE '%" . escape($search_query) . "%'))";

if (isset($_SESSION['software']['view_products']['query'])) {
    // If where is blank
    if ($where == '') {
        $where .= 'WHERE ';

    // else where is not blank, so add and
    } else {
        $where .= 'AND ';
    }
    
    $where .= "$sql_search ";
}

// if user requested to export products, then export them
if ($_GET['submit_data'] == 'Export Products') {
    // force download dialog
    header("Content-type: text/csv; charset=utf-8");
    header("Content-disposition: attachment; filename=products.csv");

    $output_custom_field_1_heading = '';

    // If the first custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
        $output_custom_field_1_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . '",';
    }

    $output_custom_field_2_heading = '';

    // If the second custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
        $output_custom_field_2_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . '",';
    }

    $output_custom_field_3_heading = '';

    // If the third custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
        $output_custom_field_3_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . '",';
    }

    $output_custom_field_4_heading = '';

    // If the fourth custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
        $output_custom_field_4_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . '",';
    }

    // Get all of the submit form fields, so we can figure out all of the necessary columns.
    $submit_form_fields = db_items(
        "SELECT
            product_submit_form_fields.product_id,
            product_submit_form_fields.action,
            product_submit_form_fields.value,
            form_fields.name
        FROM product_submit_form_fields
        LEFT JOIN form_fields ON product_submit_form_fields.form_field_id = form_fields.id
        ORDER BY
            product_submit_form_fields.product_id,
            product_submit_form_fields.action,
            product_submit_form_fields.id");

    $submit_form_create_fields = array();
    $submit_form_update_fields = array();
    $product_submit_form_fields = array();

    foreach ($submit_form_fields as $submit_form_field) {
        switch ($submit_form_field['action']) {
            case 'create':
                if (in_array($submit_form_field['name'], $submit_form_create_fields) == false) {
                    $submit_form_create_fields[] = $submit_form_field['name'];
                }

                break;
            
            case 'update':
                if (in_array($submit_form_field['name'], $submit_form_update_fields) == false) {
                    $submit_form_update_fields[] = $submit_form_field['name'];
                }

                break;
        }

        $product_submit_form_fields[$submit_form_field['product_id']][$submit_form_field['action']][$submit_form_field['name']] = $submit_form_field['value'];
    }

    // output column headings for CSV data
    echo
        '"name",' .
        '"enabled",' .
        '"short_description",' .
        '"full_description",' .
        '"details",' .
        '"code",' .
        '"keywords",' .
        '"image_name",' .
        '"price",' .
        '"taxable",' .
        '"selection_type",' .
        '"default_quantity",' .
        '"address_name",' .
        '"title",' .
        '"meta_description",' .
        '"meta_keywords",' .
        '"inventory",' .
        '"inventory_quantity",' .
        '"backorder",' .
        '"out_of_stock_message",' .
        '"required_product_id",' .
        '"form",' .
        '"form_name",' .
        '"form_label_column_width",' .
        '"form_quantity_type",' .
        '"shippable",' .
        '"weight",' .
        '"primary_weight_points",' .
        '"secondary_weight_points",' .
        '"length",' .
        '"width",' .
        '"height",' .
        '"container_required",' .
        '"preparation_time",' .
        '"free_shipping",' .
        '"extra_shipping_cost",' .
        '"commissionable",' .
        '"commission_rate_limit",' .
        '"order_receipt_message",' .
        '"order_receipt_bcc_email_address",' .
        '"email_page_id",' .
        '"email_bcc_email_address",' .
        '"recurring",' .
        '"recurring_schedule_editable_by_customer",' .
        '"recurring_days_before_start",' .
        '"recurring_number_of_payments",' .
        '"recurring_payment_period",' .
        '"recurring_profile_disabled_perform_actions",' .
        '"recurring_profile_disabled_expire_membership",' .
        '"recurring_profile_disabled_revoke_private_access",' .
        '"recurring_profile_disabled_email",' .
        '"recurring_profile_disabled_email_subject",' .
        '"recurring_profile_disabled_email_page_id",' .
        '"recurring_sage_group_id",' .
        '"contact_group_id",' .
        '"membership_renewal",' .
        '"grant_private_access",' .
        '"private_folder_id",' .
        '"private_days",' .
        '"start_page_id",' .
        '"reward_points",' .
        '"gift_card",' .
        '"gift_card_email_subject",' .
        '"gift_card_email_format",' .
        '"gift_card_email_body",' .
        '"gift_card_email_page_id",' .
        '"submit_form",' .
        '"submit_form_custom_form_page_id",' .
        '"submit_form_create",' .
        '"submit_form_update",' .
        '"submit_form_update_where_field",' .
        '"submit_form_update_where_value",' .
        '"submit_form_quantity_type",' .
        '"add_comment",' .
        '"add_comment_page_id",' .
        '"add_comment_message",' .
        '"add_comment_name",' .
        '"add_comment_only_for_submit_form_update",' .
        $output_custom_field_1_heading .
        $output_custom_field_2_heading .
        $output_custom_field_3_heading .
        $output_custom_field_4_heading .
        '"notes",' .
        '"google_product_category",' .
        '"gtin",' .
        '"brand",' .
        '"mpn"';

    foreach ($submit_form_create_fields as $field) {
        echo ',"sfc_' . escape_csv($field) . '"';
    }

    foreach ($submit_form_update_fields as $field) {
        echo ',"sfu_' . escape_csv($field) . '"';
    }

    echo "\n";

    // get all products in order to export them
    $query =
        'SELECT
            id,
            name,
            enabled,
            short_description,
            full_description,
            details,
            code,
            keywords,
            image_name,
            price,
            taxable,
            selection_type,
            default_quantity,
            address_name,
            title,
            meta_description,
            meta_keywords,
            inventory,
            inventory_quantity,
            backorder,
            out_of_stock_message,
            required_product,
            form,
            form_name,
            form_label_column_width,
            form_quantity_type,
            shippable,
            weight,
            primary_weight_points,
            secondary_weight_points,
            length,
            width,
            height,
            container_required,
            preparation_time,
            free_shipping,
            extra_shipping_cost,
            commissionable,
            commission_rate_limit,
            order_receipt_message,
            order_receipt_bcc_email_address,
            email_page,
            email_bcc,
            recurring,
            recurring_schedule_editable_by_customer,
            start,
            number_of_payments,
            payment_period,
            recurring_profile_disabled_perform_actions,
            recurring_profile_disabled_expire_membership,
            recurring_profile_disabled_revoke_private_access,
            recurring_profile_disabled_email,
            recurring_profile_disabled_email_subject,
            recurring_profile_disabled_email_page_id,
            sage_group_id,
            contact_group_id,
            membership_renewal,
            grant_private_access,
            private_folder,
            private_days,
            send_to_page,
            reward_points,
            gift_card,
            gift_card_email_subject,
            gift_card_email_format,
            gift_card_email_body,
            gift_card_email_page_id,
            submit_form,
            submit_form_custom_form_page_id,
            submit_form_create,
            submit_form_update,
            submit_form_update_where_field,
            submit_form_update_where_value,
            submit_form_quantity_type,
            add_comment,
            add_comment_page_id,
            add_comment_message,
            add_comment_name,
            add_comment_only_for_submit_form_update,
            custom_field_1,
            custom_field_2,
            custom_field_3,
            custom_field_4,
            notes,
            google_product_category,
            gtin,
            brand,
            mpn
        FROM products
        ' . $where . '
        ORDER BY ' . $sort_column . ' ' . $asc_desc;
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $products = mysqli_fetch_items($result);

    // loop through the products in order to output CSV data
    foreach ($products as $product) {
        $output_custom_field_1 = '';

        // If the first custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
            $output_custom_field_1 = '"' . escape_csv($product['custom_field_1']) . '",';
        }

        $output_custom_field_2 = '';

        // If the second custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
            $output_custom_field_2 = '"' . escape_csv($product['custom_field_2']) . '",';
        }

        $output_custom_field_3 = '';

        // If the third custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
            $output_custom_field_3 = '"' . escape_csv($product['custom_field_3']) . '",';
        }

        $output_custom_field_4 = '';

        // If the fourth custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
            $output_custom_field_4 = '"' . escape_csv($product['custom_field_4']) . '",';
        }

        echo
            '"' . escape_csv($product['name']) . '",' .
            '"' . $product['enabled'] . '",' .
            '"' . escape_csv($product['short_description']) . '",' .
            '"' . escape_csv($product['full_description']) . '",' .
            '"' . escape_csv($product['details']) . '",' .
            '"' . escape_csv($product['code']) . '",' .
            '"' . escape_csv($product['keywords']) . '",' .
            '"' . escape_csv($product['image_name']) . '",' .
            '"' . sprintf('%01.2lf', $product['price'] / 100) . '",' .
            '"' . $product['taxable'] . '",' .
            '"' . $product['selection_type'] . '",' .
            '"' . $product['default_quantity'] . '",' .
            '"' . escape_csv($product['address_name']) . '",' .
            '"' . escape_csv($product['title']) . '",' .
            '"' . escape_csv($product['meta_description']) . '",' .
            '"' . escape_csv($product['meta_keywords']) . '",' .
            '"' . $product['inventory'] . '",' .
            '"' . $product['inventory_quantity'] . '",' .
            '"' . $product['backorder'] . '",' .
            '"' . escape_csv($product['out_of_stock_message']) . '",' .
            '"' . $product['required_product'] . '",' .
            '"' . $product['form'] . '",' .
            '"' . escape_csv($product['form_name']) . '",' .
            '"' . escape_csv($product['form_label_column_width']) . '",' .
            '"' . $product['form_quantity_type'] . '",' .
            '"' . $product['shippable'] . '",' .
            '"' . $product['weight'] . '",' .
            '"' . $product['primary_weight_points'] . '",' .
            '"' . $product['secondary_weight_points'] . '",' .
            '"' . $product['length'] . '",' .
            '"' . $product['width'] . '",' .
            '"' . $product['height'] . '",' .
            '"' . $product['container_required'] . '",' .
            '"' . $product['preparation_time'] . '",' .
            '"' . $product['free_shipping'] . '",' .
            '"' . sprintf('%01.2lf', $product['extra_shipping_cost'] / 100) . '",' .
            '"' . $product['commissionable'] . '",' .
            '"' . $product['commission_rate_limit'] . '",' .
            '"' . escape_csv($product['order_receipt_message']) . '",' .
            '"' . escape_csv($product['order_receipt_bcc_email_address']) . '",' .
            '"' . $product['email_page'] . '",' .
            '"' . escape_csv($product['email_bcc']) . '",' .
            '"' . $product['recurring'] . '",' .
            '"' . $product['recurring_schedule_editable_by_customer'] . '",' .
            '"' . $product['start'] . '",' .
            '"' . $product['number_of_payments'] . '",' .
            '"' . $product['payment_period'] . '",' .
            '"' . $product['recurring_profile_disabled_perform_actions'] . '",' .
            '"' . $product['recurring_profile_disabled_expire_membership'] . '",' .
            '"' . $product['recurring_profile_disabled_revoke_private_access'] . '",' .
            '"' . $product['recurring_profile_disabled_email'] . '",' .
            '"' . escape_csv($product['recurring_profile_disabled_email_subject']) . '",' .
            '"' . $product['recurring_profile_disabled_email_page_id'] . '",' .
            '"' . $product['sage_group_id'] . '",' .
            '"' . $product['contact_group_id'] . '",' .
            '"' . $product['membership_renewal'] . '",' .
            '"' . $product['grant_private_access'] . '",' .
            '"' . $product['private_folder'] . '",' .
            '"' . $product['private_days'] . '",' .
            '"' . $product['send_to_page'] . '",' .
            '"' . $product['reward_points'] . '",' .
            '"' . $product['gift_card'] . '",' .
            '"' . escape_csv($product['gift_card_email_subject']) . '",' .
            '"' . $product['gift_card_email_format'] . '",' .
            '"' . escape_csv($product['gift_card_email_body']) . '",' .
            '"' . $product['gift_card_email_page_id'] . '",' .
            '"' . $product['submit_form'] . '",' .
            '"' . $product['submit_form_custom_form_page_id'] . '",' .
            '"' . $product['submit_form_create'] . '",' .
            '"' . $product['submit_form_update'] . '",' .
            '"' . $product['submit_form_update_where_field'] . '",' .
            '"' . $product['submit_form_update_where_value'] . '",' .
            '"' . $product['submit_form_quantity_type'] . '",' .
            '"' . $product['add_comment'] . '",' .
            '"' . $product['add_comment_page_id'] . '",' .
            '"' . escape_csv($product['add_comment_message']) . '",' .
            '"' . escape_csv($product['add_comment_name']) . '",' .
            '"' . $product['add_comment_only_for_submit_form_update'] . '",' .
            $output_custom_field_1 .
            $output_custom_field_2 .
            $output_custom_field_3 .
            $output_custom_field_4 .
            '"' . escape_csv($product['notes']) . '",' .
            '"' . escape_csv($product['google_product_category']) . '",' .
            '"' . escape_csv($product['gtin']) . '",' .
            '"' . escape_csv($product['brand']) . '",' .
            '"' . escape_csv($product['mpn']) . '"';

        foreach ($submit_form_create_fields as $field) {
            echo ',"' . escape_csv($product_submit_form_fields[$product['id']]['create'][$field]) . '"';
        }

        foreach ($submit_form_update_fields as $field) {
            echo ',"' . escape_csv($product_submit_form_fields[$product['id']]['update'][$field]) . '"';
        }

        echo "\n";
    }

    // if at least 1 product was exported, then log activity
    if (count($products) > 0) {
        // if only 1 product was exported, then prepare message phrasing in a certain way
        if (count($products) == 1) {
            $plural_suffix = '';
            $was_or_were = 'was';

        // else more than 1 product was exported, so prepare message phrasing in a different way
        } else {
            $plural_suffix = 's';
            $was_or_were = 'were';
        }

        // add log message about products being exported
        log_activity(count($products) . ' product' . $plural_suffix . ' ' . $was_or_were . ' exported', $_SESSION['sessionusername']);
    }

// else the user did not select to export products, so just list products
} else {
    // get total number of results for all screens, so that we can output links to different screens
    $query = "SELECT count(id) " .
             "FROM products";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_results = $row[0];
    $all_products = $number_of_results;

    // get number of results for this screen only
    $query = "SELECT count(products.id) " .
             "FROM products
             $sql_join
             $where";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $screen_results = $row[0];

    /* build product filter options */

    // set all products option
    $output_filter_options = '<option value="all_products"' . $all_products_filter_selected . '>All Products (' . number_format($all_products) . ')</option>';

    // set all product actions option
    $output_filter_options .= '<option value="all_product_actions"' . $all_product_actions_filter_selected . '>All Product Actions (' . number_format($all_products) . ')</option>';

    // get the amount of shippable products
    $query = "SELECT count(id) FROM products WHERE shippable = '1'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);

    // set shippable product option
    $output_filter_options .= '<option value="shippable_products"' . $shippable_product_filter_selected . '>Shippable Products (' . number_format($row[0]) . ')</option>';

    // get the amount of recurring products
    $query = "SELECT count(id) FROM products WHERE recurring = '1'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);

    // set recurring product option
    $output_filter_options .= '<option value="recurring_products"' . $recurring_product_filter_selected . '>Recurring Products (' . number_format($row[0]) . ')</option>';

    // get the amount of donation products
    $query = "SELECT count(id) FROM products WHERE selection_type = 'donation'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);

    // set donation product option
    $output_filter_options .= '<option value="donation_products"' . $donation_product_filter_selected . '>Donation Products (' . number_format($row[0]) . ')</option>';

    // get the amount of grant access products
    $query = "SELECT count(id) FROM products WHERE grant_private_access = '1' AND (private_folder != '0' OR send_to_page != '0')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);

    // set grant access product option
    $output_filter_options .= '<option value="grant_access_products"' . $grant_access_product_filter_selected . '>Grant Access Products (' . number_format($row[0]) . ')</option>';

    // get the amount of membership products
    $query = "SELECT count(id) FROM products WHERE membership_renewal != '0'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);

    // set membership product option
    $output_filter_options .= '<option value="membership_products"' . $membership_product_filter_selected . '>Membership Products (' . number_format($row[0]) . ')</option>';
    
    // get the amount of out of stock products
    $query = "SELECT count(id) FROM products WHERE out_of_stock = '1'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    
    // set out of stock product option
    $output_filter_options .= '<option value="out_of_stock_products"' . $out_of_stock_products_filter_selected . '>Out of Stock Products (' . number_format($row[0]) . ')</option>';
   


    /* define range depending on screen value by using a limit clause in the SQL statement */
    // define the maximum number of results
    $max = 100;
    // determine where result set should start
    $start = $screen * $max - $max;
    $limit = "LIMIT $start, $max";

    // get total number of results for all screens, so that we can output links to different screens
    $query = "SELECT count(products.id) " .
             "FROM products " .
             $sql_join .
             $where;
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_results = $row[0];

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_products.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_products.php?screen=\' + this.options[this.selectedIndex].value)">';

        // build HTML output for links to screens
        for ($i = 1; $i <= $number_of_screens; $i++) {
            // if this number is the current screen, then select option
            if ($i == $screen) {
                $selected = ' selected="selected"';
            // else this number is not the current screen, so do not select option
            } else {
                $selected = '';
            }

            $output_screen_links .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
        }

        $output_screen_links .= '</select>';
    }

    // build Next button if necessary
    $next = $screen + 1;
    // if next screen is less than or equal to the total number of screens, output next link
    if ($next <= $number_of_screens) {
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_products.php?screen=' . $next . '">&gt;</a>';
    }

    $filter_specific_columns .= '';
    $filter_specific_join .= '';

    /* Build filter specific table headers, sql joins and columns */
    if ((ECOMMERCE_TAX == true) && ($filter != 'all_product_actions')) {
        $output_tax_header =
            '<th style="text-align: center">' . get_column_heading('Taxable', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }

    if (($filter == 'all_products') || ($filter == 'out_of_stock_products')) {
        
        // Set filter specific sql columns
        $filter_specific_columns .= 
            "products.shippable,
            products.selection_type as selection_type,
            products.default_quantity as default_quantity,
            products.custom_field_1,
            products.custom_field_2,
            products.custom_field_3,
            products.custom_field_4,";

        $output_custom_field_1_heading = '';

        // If the first custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
            $output_custom_field_1_heading .= '<th>' . get_column_heading(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL, $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
        }

        $output_custom_field_2_heading = '';

        // If the second custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
            $output_custom_field_2_heading .= '<th>' . get_column_heading(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL, $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
        }

        $output_custom_field_3_heading = '';

        // If the third custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
            $output_custom_field_3_heading .= '<th>' . get_column_heading(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL, $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
        }

        $output_custom_field_4_heading = '';

        // If the fourth custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
            $output_custom_field_4_heading .= '<th>' . get_column_heading(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL, $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
        }
        
        $output_all_products_headers = 
            '<th>' . get_column_heading('Selection Type', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('Default Quantity', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('Shippable', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            ' . $output_custom_field_1_heading . '
            ' . $output_custom_field_2_heading . '
            ' . $output_custom_field_3_heading . '
            ' . $output_custom_field_4_heading;
    }

    if (
        ($filter == 'grant_access_products')
        || ($filter == 'membership_products')
        || ($filter == 'all_product_actions')
    ) {
        // Set filter specific sql columns
        $filter_specific_columns .= "products.grant_private_access as grant_private_access,";
    }

    if (($filter == 'membership_products') || ($filter == 'all_product_actions')) {
        // Set filter specific sql columns
        $filter_specific_columns .= "products.membership_renewal as membership_renewal,";
    }

    if (($filter == 'all_products') ||
        ($filter == 'donation_products') ||
        ($filter == 'membership_products')) {
        
        // Set filter specific sql columns
        $filter_specific_columns .= 
        "products.recurring as recurring,";
        
        $output_recurring_header = '<th style="text-align: center">' . get_column_heading('Recurring Payment', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';   
    }

    if ($filter == 'all_product_actions') {
        
        // Set filter specific sql columns
        $filter_specific_columns .= 
            "email_page.page_name as email_page,
            contact_groups.name as contact_group_name,
            products.order_receipt_message as order_receipt_message,
            products.order_receipt_bcc_email_address as order_receipt_bcc_email_address,
            products.email_bcc as email_bcc,";

        // Set filter specific sql joins
        $filter_specific_join .= "LEFT JOIN page AS email_page ON email_page.page_id = products.email_page ";
        
        $output_all_product_actions_headers = 
            '<th style="text-align: center">' . get_column_heading('Order Receipt Message', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th>' . get_column_heading('Order Receipt BCC', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th>' . get_column_heading('E-mail Page', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th>' . get_column_heading('E-mail Page BCC', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th>' . get_column_heading('Contact Group', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
        
    // else output product form header
    } else {
        $output_product_form_header = '<th>' . get_column_heading('Product Form', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }

    if ($filter == 'shippable_products') {
        // Set filter specific sql columns
        $filter_specific_columns .= 
            "products.shippable,
            products.weight,
            products.primary_weight_points,
            products.secondary_weight_points,
            products.length,
            products.width,
            products.height,
            products.container_required,
            products.preparation_time,
            products.free_shipping,
            products.extra_shipping_cost,";
            
        $output_shipping_headers =
            '<th style="text-align: center">' . get_column_heading('Weight', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('PWP', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('SWP', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th>' . get_column_heading('Dim', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('Cont Req', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('Prep', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('Free Ship', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('Extra Ship', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th>Allowed Zones</th>
            <th>Disallowed Zones</th>';
    }

    if (($filter == 'recurring_products') || ($filter == 'donation_products')) {
        
        // Set filter specific sql columns
        $filter_specific_columns .= 
            "products.start as recurring_start,
            products.number_of_payments as number_of_payments,
            products.payment_period as payment_period,
            products.recurring_schedule_editable_by_customer as recurring_schedule_editable_by_customer,";
        
        $output_recurring_option_headers = 
            '<th>' . get_column_heading('Start', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th style="text-align: center">' . get_column_heading('Number of Payments', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
            <th>' . get_column_heading('Payment Period', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }

    if ($filter == 'donation_products') {
        
        // Output dontaine price header
        $output_price_header = '<th style="text-align: right; padding-right: 1em;">' . get_column_heading('Default Amount (Price)', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
        
        $output_recurring_set_schedule_header = '<th style="text-align: center">' . get_column_heading('Allow to Schedule', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';

    // else output the default price header
    } else {
        $output_price_header = '<th style="text-align: right; padding-right: 1em;">' . get_column_heading('Price', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }

    if (($filter == 'grant_access_products') || 
        ($filter == 'membership_products') ||
        ($filter == 'all_product_actions')) {

        // Set filter specific sql columns
        $filter_specific_columns .= "page.page_name as start_page,";
        
        // Set filter specific sql joins
        $filter_specific_join .= "LEFT JOIN page ON page.page_id = products.send_to_page ";

        $output_start_page_header = '<th>' . get_column_heading('Set Start Page', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }

    if (($filter == 'grant_access_products') || ($filter == 'all_product_actions')) {
        
        // Set filter specific sql columns
        $filter_specific_columns .= "folder.folder_name as private_folder,";

        // Set filter specific sql joins
        $filter_specific_join .= "LEFT JOIN folder ON folder.folder_id = products.private_folder ";
        
        $output_private_folder_access_headers = '<th>' . get_column_heading('Grant Private Access', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }

    if (($filter == 'membership_products') || ($filter == 'all_product_actions')) {
        
        $output_add_membership_header = '<th>' . get_column_heading('Membership Renewal', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }
    if ($filter == 'out_of_stock_products') {
        
        $output_out_of_stock_timestamp_header = '<th>' . get_column_heading('Out of Stock Date', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
    }
    /* get results for just this screen*/
    $query = "SELECT
                products.id as id,
                products.name as name,
                products.enabled,
				products.image_name  as image_name,
                products.inventory as inventory,
                products.inventory_quantity as inventory_quantity,
                products.short_description as short_description,
                products.price as price,
                products.taxable as taxable,
                products.form_name as form_name,
                products.seo_score as seo_score,
                $filter_specific_columns
                user.user_username as user,
                products.out_of_stock as out_of_stock,
                products.out_of_stock_timestamp as out_of_stock_timestamp,
                products.timestamp as timestamp
             FROM products
             LEFT JOIN user ON products.user = user.user_id
             $sql_join
             $filter_specific_join
             $where
             ORDER BY $sort_column $asc_desc
             $limit";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $product_id = $row['id'];
        $name = h($row['name']);
        $enabled = $row['enabled'];
        $short_description = $row['short_description'];
        $price = $row['price'] / 100;
        $form_name = $row['form_name'];
        $seo_score = $row['seo_score'];
        $selection_type = $row['selection_type'];
        $default_quantity = $row['default_quantity'];
        $required_product = $row['required_product_name'];
        $shippable = $row['shippable'];
        $commissionable = $row['commissionable'];
        $recurring = $row['recurring'];
        $recurring_start = $row['recurring_start'];
        $number_of_payments = $row['number_of_payments'];
        $payment_period = $row['payment_period'];
        $recurring_schedule_editable_by_customer = $row['recurring_schedule_editable_by_customer'];
        $start_page = $row['start_page'];
        $grant_private_access = $row['grant_private_access'];
        $private_folder = $row['private_folder'];
        $membership_renewal = $row['membership_renewal'];
        $order_receipt_message = $row['order_receipt_message'];
        $order_receipt_bcc_email_address = $row['order_receipt_bcc_email_address'];
        $email_page = $row['email_page'];
        $email_bcc = $row['email_bcc'];
        $contact_group_name = $row['contact_group_name'];
        $custom_field_1 = $row['custom_field_1'];
        $custom_field_2 = $row['custom_field_2'];
        $custom_field_3 = $row['custom_field_3'];
        $custom_field_4 = $row['custom_field_4'];
        $inventory = $row['inventory'];
		$inventory_quantity = $row['inventory_quantity'];
		$image_name = $row['image_name'];
        // set checkmark image for columns to use
        $output_checkmark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        
        // set link url
        $output_link_url = 'edit_product.php?id=' . $row['id'];

        $output_name_and_short_description_color = '';
        $output_enabled_check_mark = '';

        // If this product is enabled, then use green color for name and short description,
        // and output check mark for enabled column.
        if ($enabled == 1) {
            $output_name_and_short_description_color = '#009900';
            $output_enabled_check_mark = $output_checkmark;
        
        // Otherwise this product is disabled, so use red color for name and short description,
        // and do not output check mark for enabled column.
        } else {
            $output_name_and_short_description_color = '#ff0000';
        }
        
        // if tax is on, prepare tax data
        if ((ECOMMERCE_TAX == true) && ($filter != 'all_product_actions')) {
            $taxable = $row['taxable'];

            if ($taxable == 1) {
                $taxable = $output_checkmark;
            } else {
                $taxable = '';
            }
            
            // output column
            $output_tax_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $taxable . '</td>';
        }
		
		$output_image_header ='';
        $output_image_column ='';
		//default show image
		$show_image = true;
		if($show_image == true){
			$output_image_header ='<th>Image</th>';
			if(!$image_name){
				$output_image_column ='<td title="no image" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'"></td>';
			}else{
				$output_image_column ='<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'"><img style="width:50px;height:30px;" class="lazy" src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/loading.gif" data-src="' .  PATH . $image_name . '"/></td>';
			}
		}
        
        // output filter specific table cells
        if (($filter == 'all_products') || ($filter == 'out_of_stock_products')) {
            $output_selection_type = '';
            
            switch ($selection_type) {
                case 'checkbox':
                    $output_selection_type = 'Checkbox';
                    break;
                    
                case 'quantity':
                    $output_selection_type = 'Quantity';
                    break;
                    
                case 'donation':
                    $output_selection_type = 'Donation';
                    break;
                    
                case 'autoselect':
                    $output_selection_type = 'Auto-Select';
                    break;
            }
            
            // if shippable is on then output checkmark
            if ($shippable == 1) {
                $output_shippable = $output_checkmark;
            } else {
                $output_shippable = '';
            }

            $output_custom_field_1_cell = '';

            // If the first custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                $output_custom_field_1_cell .= '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($custom_field_1) . '</td>';
            }

            $output_custom_field_2_cell = '';

            // If the second custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                $output_custom_field_2_cell .= '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($custom_field_2) . '</td>';
            }

            $output_custom_field_3_cell = '';

            // If the third custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                $output_custom_field_3_cell .= '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($custom_field_3) . '</td>';
            }

            $output_custom_field_4_cell = '';

            // If the fourth custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                $output_custom_field_4_cell .= '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($custom_field_4) . '</td>';
            }
            
            // output columns
            $output_all_products_columns = 
                '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_selection_type . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $default_quantity . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_shippable . '</td>
                ' . $output_custom_field_1_cell . '
                ' . $output_custom_field_2_cell . '
                ' . $output_custom_field_3_cell . '
                ' . $output_custom_field_4_cell;
        }
        
        // output filter specific table cells
        if (($filter == 'all_products') ||
            ($filter == 'donation_products') ||
            ($filter == 'membership_products')) {
            
            // if recurring is on then output checkmark
            if ($recurring == 1) {
                $output_recurring = $output_checkmark;
            } else {
                $output_recurring = '';
            }
            
            // output column
            $output_recurring_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_recurring . '</td>';
        }
        
        // output filter specific table cells
        if ($filter == 'all_product_actions') {
            // if there is an order receipt message then output checkmark
            if ($order_receipt_message != '') {
                $output_order_receipt_message = $output_checkmark;
            } else {
                $output_order_receipt_message = '';
            }
            
            // output columns
            $output_all_product_actions_columns = 
                '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_order_receipt_message . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($order_receipt_bcc_email_address) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($email_page) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($email_bcc) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact_group_name) . '</td>';
        
        // else output product form column
        } else {
            $output_product_form_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($form_name) . '</td>';
        }
        
        // output filter specific table cells
        if ($filter == 'shippable_products') {

            $weight = '';

            if ($row['weight'] > 0) {
                $weight = ($row['weight']+0) . ' lb';
            }

            $primary_weight_points = $row['primary_weight_points'];
            $secondary_weight_points = $row['secondary_weight_points'];

            $dimensions = '';

            if (($row['length'] > 0) or ($row['width'] > 0) or ($row['height'] > 0)) {
                $dimensions = ($row['length']+0) . '&Prime; x ' . ($row['width']+0) . '&Prime; x ' . ($row['height']+0) . '&Prime;';
            }

            $container_required = '';

            if ($row['container_required']) {
                $container_required = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">';
            }

            $preparation_time = $row['preparation_time'];
            $free_shipping = $row['free_shipping'];
            
            if ($free_shipping == 1) {
                $free_shipping = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
            } else {
                $free_shipping = '';
            }
            
            $extra_shipping_cost = BASE_CURRENCY_SYMBOL . number_format($row['extra_shipping_cost'] / 100, 2, '.', ',');
                
            
            $output_allowed_zones = '';
            $output_disallowed_zones = '';
            $disallowed_zones_sql_where = '';
            
            // Get allowed zones
            $query2 = "SELECT 
                         zones.id,
                         zones.name 
                      FROM zones 
                      LEFT JOIN products_zones_xref ON products_zones_xref.zone_id = zones.id 
                      WHERE products_zones_xref.product_id = '" . escape($product_id) . "'
                      ORDER BY zones.name ASC";
            $result2 = mysqli_query(db::$con, $query2) or output_error('Query failed.');
            
            // loop through and prepare allowed zones
            while($row2 = mysqli_fetch_assoc($result2)){
                
                // if there are already allowed zones then output a comma and a break tag
                if ($output_allowed_zones) {
                    $output_allowed_zones .= ',<br />';
                }
                
                // output allowed zone
                $output_allowed_zones .= h($row2['name']);
                
                // If disallowed zones where is blank
                if ($disallowed_zones_sql_where == '') {
                    $disallowed_zones_sql_where .= 'WHERE ';

                // else where is not blank, so add and
                } else {
                    $disallowed_zones_sql_where .= 'AND ';
                }
                
                // add zone id to where statement to exclude this zone from disallowed zones
                $disallowed_zones_sql_where .= "id != '" . escape($row2['id']) . "'";
            }
            
            // Get disallowed zones
            $query2 = "SELECT name FROM zones
                      $disallowed_zones_sql_where
                      ORDER BY zones.name ASC";
            $result2 = mysqli_query(db::$con, $query2) or output_error('Query failed.');
            
            // loop through and prepare disallowed zones
            while($row2 = mysqli_fetch_assoc($result2)){
                
                if ($output_disallowed_zones) {
                    $output_disallowed_zones .= ',<br />';
                }
                
                // output disallowed zones
                $output_disallowed_zones .= h($row2['name']);
            }
            
            // output columns
            $output_shipping_columns =
                '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $weight . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $primary_weight_points . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $secondary_weight_points . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $dimensions . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $container_required . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $preparation_time . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $free_shipping . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: right">' . $extra_shipping_cost . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_allowed_zones . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_disallowed_zones . '</td>';
        }
        
        // output filter specific table cells
        if (($filter == 'recurring_products') || ($filter == 'donation_products')) {
            // If there is a start date
            if ($recurring_start != '0') {
                // output amount of days
                $output_recurring_start = $recurring_start;
                
                // If the start is set to one day then output day, else output days.
                if ($recurring_start == '1') {
                    $output_recurring_start .= ' Day';
                } else {
                    $output_recurring_start .= ' Days';
                }
                
            // else output default
            } else {
                $output_recurring_start = 'Immediately';
            }
            
            // if number of payments is not 0 then output the number of payments
            if ($number_of_payments != '0') {
                $output_number_of_payments = $number_of_payments;
                
            // else output default
            } else {
                $output_number_of_payments = 'Unlimited';
            }
            
            // output columns
            $output_recurring_option_columns = 
                '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($output_recurring_start) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . h($output_number_of_payments) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($payment_period) . '</td>';
        }
        
        // output filter specific table cells
        if ($filter == 'donation_products') {
            // if customer is able to set the schedule is on then output checkmark
            if ($recurring_schedule_editable_by_customer == 1) {
                $output_recurring_schedule_editable_by_customer = $output_checkmark;
            } else {
                $output_recurring_schedule_editable_by_customer = '';
            }
            
            $output_recurring_set_schedule_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_recurring_schedule_editable_by_customer . '</td>';
        }
        
        // output filter specific table cells
        if (($filter == 'grant_access_products') || 
            ($filter == 'membership_products') ||
            ($filter == 'all_product_actions')) {
            
            // if grant private access is on then output start page
            if ($grant_private_access == '1') {
                $output_start_page = $start_page;
                
            // else output nothing
            } else {
                $output_start_page = '';
            }
            
            // output column
            $output_start_page_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($output_start_page) . '</td>';
        }
        
        // output filter specific table cells
        if (($filter == 'grant_access_products') || ($filter == 'all_product_actions')) {
            // if grant private access is on then output private folder
            if ($grant_private_access == '1') {
                $output_grant_private_folder_access = $private_folder;
                
            // else output nothing
            } else {
                $output_grant_private_folder_access = '';
            }
            
            // output columns
            $output_private_folder_access_columns = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($output_grant_private_folder_access) . '</td>';
        }
        
        // output filter specific table cells
        if (($filter == 'membership_products') || ($filter == 'all_product_actions')) {
            // if there is a membership renewal value then output it
            if ($membership_renewal != '0') {
                $output_membership_renewal = $membership_renewal;
                
                // If the membership renewal field is set to one day then output day, else output days.
                if ($membership_renewal == '1') {
                    $output_membership_renewal .= ' Day';
                } else {
                    $output_membership_renewal .= ' Days';
                }
                
            // else do not output anything
            } else {
                $output_membership_renewal = '';
            }
            
            // output column
            $output_add_membership_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($output_membership_renewal) . '</td>';
        }     

        // output filter specific table cells
        if ($filter == 'out_of_stock_products') {
        
            $output_out_of_stock_timestamp_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $row['out_of_stock_timestamp'])) . '</td>';
        }else{

            $output_inventory_headers ='<th>' . get_column_heading('Inventory Quantity', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>';
            $output_inventory_columns ='<td style="color:grey;text-align: center;">-</td>';
            if ($inventory == 1){
                $output_inventory_columns ='<td style="text-align: center;color:green;">'.$inventory_quantity.'</td>';
                if ($inventory_quantity == '0'){
                    $output_inventory_columns ='<td style="text-align: center;color:red;">'.$inventory_quantity.'</td>';
                }
            }

        }



        
        $output_rows .=
        '<tr>
            <td class="selectall"><input type="checkbox" name="products[]" value="' . $row['id'] . '" class="checkbox" /></td>
			' . $output_image_column . '
            <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer" style="color: ' . $output_name_and_short_description_color . '">' . $name . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="color: ' . $output_name_and_short_description_color . '">' . $short_description . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_enabled_check_mark . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: right; padding-right: 1em;">' . prepare_amount($price) . '</td>
            ' . $output_tax_column . '
            ' . $output_product_form_column . '
            ' . $output_all_products_columns . ' 
            ' . $output_shipping_columns . '
            ' . $output_recurring_column . '
            ' . $output_recurring_option_columns . '
            ' . $output_recurring_set_schedule_column . '
            ' . $output_start_page_column . '
            ' . $output_private_folder_access_columns . '
            ' . $output_add_membership_column . '
            ' . $output_all_product_actions_columns . '
            ' . $output_inventory_columns . '
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $row['timestamp'])) . ' by ' . h($row['user']) . '</td>
            ' . $output_out_of_stock_timestamp_column . '
        </tr>';
    }

    $output .=
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar">
        <a href="add_product.php">Create Product</a>
        <a href="import_products.php">Import Products</a>
        <a href="edit_featured_and_new_items.php?from=view_products&send_to=' . h(urlencode(get_request_uri())) . '">Edit Featured &amp; New Items</a>
    </div>
    <div id="content">
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>' . $heading . '</h1>
        <div class="subheading">' . $subheading . '</div>
        <div style="text-align: right; margin: 5px 0px 1em 0px;">
            <form id="search_form" action="view_products.php" method="get" class="search_form" style="display: inline;">
                Show: <select name="filter" onchange="submit_form(\'search_form\')">' . $output_filter_options . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['view_products']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
            </form>
        </div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($screen_results) . ' Total&nbsp;&nbsp;&nbsp;&nbsp;<form method="get" style="margin: 0; display: inline"><input type="submit" name="submit_data" value="Export Products" class="submit_small_secondary" /></form>
        </div>
        <form name="form" action="edit_products.php" method="post" style="margin: 0">
            ' . get_token_field() . '
            <input type="hidden" name="action">
            <input type="hidden" name="edit_enabled">
            <input type="hidden" name="edit_allowed_zones">
            <input type="hidden" name="edit_disallowed_zones">
			<input type="hidden" name="edit_change_price_method">
			<input type="hidden" name="edit_price_value">

			
            <table class="chart">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
					' . $output_image_header . '
                    <th>' . get_column_heading('ID', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
                    <th>' . get_column_heading('Short Description', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
                    <th style="text-align: center">' . get_column_heading('Enabled', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
                    ' . $output_price_header . '
                    ' . $output_tax_header . '
                    ' . $output_product_form_header . '
                    ' . $output_all_products_headers . '
                    ' . $output_shipping_headers . '
                    ' . $output_recurring_header . '
                    ' . $output_recurring_option_headers . '
                    ' . $output_recurring_set_schedule_header . '
                    ' . $output_start_page_header . '
                    ' . $output_private_folder_access_headers . '
                    ' . $output_add_membership_header . '
                    ' . $output_all_product_actions_headers . '
                    ' . $output_inventory_headers . '
                    <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_products']['sort'], $_SESSION['software']['ecommerce']['view_products']['order']) . '</th>
                    ' . $output_out_of_stock_timestamp_header . '
                </tr>
                ' . $output_rows . '
            </table>
            <div class="pagination">
                ' . $output_screen_links . '
            </div>
            <div class="buttons">
                <input type="button" value="Modify Selected" class="submit-secondary" onclick="window.open(\'edit_products.php\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=500,height=500\'); edit_products(\'edit\')" />&nbsp;&nbsp;&nbsp;<input type="button" value="Delete Selected" class="delete" onclick="edit_products(\'delete\')" />
            </div>
        </form>
    </div>
    <script>
        $(function () {
            $("img.lazy").Lazy();
        });
    </script>
    
    ' .
    output_footer();

    print $output;
    $liveform->remove_form('view_products');
}