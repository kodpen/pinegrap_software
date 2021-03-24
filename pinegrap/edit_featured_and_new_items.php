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

include_once('liveform.class.php');
$liveform = new liveform('edit_featured_and_new_items');

// if the form has not been submitted, then prepare to output form
if (!$_POST) {
    // get items for table
    $items = get_items_that_can_be_featured_and_new();

    // assume that we should not prefill fields, until we find out otherwise
    $prefill = false;
    
    // if the form has not been submitted already, then prefill fields with data
    if (isset($_SESSION['software']['liveforms']['edit_featured_and_new_items'][0]) == false) {
        $prefill = true;
    }
    
    $output_item_rows = '';
    
    // loop through all items in order to output rows
    foreach ($items as $item) {

        // If this item is enabled, then use green color for name and short description.
        if ($item['enabled'] == 1) {
            $status_class = 'status_enabled';
        
        // Otherwise this item is disabled, so use red color for name and short description.
        } else {
            $status_class = 'status_disabled';
        }

        // if this item is a product group, then prepare data in a certain way
        if ($item['type'] == 'product group') {
            $output_name = '<strong>' . h($item['name']) . '</strong>';
            
            // if this item is not the root product group, then prepare field names
            if ($item['parent_product_group_id'] != 0) {
                $featured_sort_order_field_name = 'featured_sort_order_product_group_' . $item['id'];
                $new_date_field_name = 'new_date_product_group_' . $item['id'];
            }
            
        // else this item is a product, so prepare data in a different way
        } else {
            $output_name = h($item['name']) . ' - ' . h($item['short_description']);
            $featured_sort_order_field_name = 'featured_sort_order_product_group_' . $item['parent_product_group_id'] . '_product_' . $item['id'];
            $new_date_field_name = 'new_date_product_group_' . $item['parent_product_group_id'] . '_product_' . $item['id'];
        }        
        
        $output_padding = '';
        
        // if this is not the root level, then output padding
        if ($item['level'] != 0) {
            $output_padding = ' style="padding-left: ' . $item['level'] * 2 . 'em"';
        }

        // if this is not the root product group and we should prefill the fields, then do that
        if (($item['parent_product_group_id'] != 0) && ($prefill == true)) {
            // if the item is featured, then prefill featured sort order
            if ($item['featured'] == 1) {
                $liveform->assign_field_value($featured_sort_order_field_name, $item['featured_sort_order']);
            }
            
            // if the item has a new date, then prefill new date
            if ($item['new_date'] != '0000-00-00') {
                $liveform->assign_field_value($new_date_field_name, prepare_form_data_for_output($item['new_date'], 'date'));
            }
        }
        
        // assume that we should not output the fields until we find out otherwise
        $output_featured_sort_order_field = '';
        $output_new_date_field = '';
        
        // if this item is not the root product group, then output fields
        if ($item['parent_product_group_id'] != 0) {
            $output_featured_sort_order_field = $liveform->output_field(array('type'=>'text', 'name'=>$featured_sort_order_field_name, 'size'=>'5', 'maxlength'=>'4'));
            $output_new_date_field =
                $liveform->output_field(array('type'=>'text', 'id' => $new_date_field_name, 'name'=>$new_date_field_name, 'size'=>'10', 'maxlength'=>'10')) . '
                <script>
                    $("#' . $new_date_field_name . '").datepicker({
                        dateFormat: date_picker_format
                    });
                </script>';
        }
        
        $output_item_rows .=
            '<tr>
                <td' . $output_padding . ' class="' . $status_class . '">' . $output_name . '</td>
                <td>' . $output_featured_sort_order_field . '</td>
                <td>' . $output_new_date_field . '</td>
            </tr>';
    }

    // output product group tree screen
    echo
        output_header() . '
        ' . get_date_picker_format() . '
        <div id="subnav">
            ' . render(array('template' => 'commerce_subnav.php')) . '
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Featured &amp; New Items</h1>
            <div class="subheading" style="margin-bottom: 1em">Update the featured and new product groups and products that should appear on catalog pages.</div>
            <form name="form" action="edit_featured_and_new_items.php" method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'from', 'value'=>$_GET['from'])) . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>$_GET['send_to'])) . '
                <table class="chart" style="width: auto; margin-bottom: 1em">
                    <tr>
                        <th>Item</th>
                        <th>Featured Order</th>
                        <th>New on Date</th>
                    </tr>
                    ' . $output_item_rows . '
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
                <input type="hidden" name="max_input_vars_test" value="true">
            </form>
        </div>' .
        output_footer();
        
    $liveform->remove_form('edit_featured_and_new_items');
    
// else the form has been submitted, so process form
} else {
    validate_token_field();
    
    // If the max_input_vars_test hidden field is not in the post data then that means the post data was truncated,
    // so output error. This can happen because of max_input_vars (i.e. php.ini
    // setting added in PHP v5.3.9 and often backported to earlier versions).
    // This can happen when there is a large number of products (e.g. 1,000+).
    // The default value for max_input_vars is 1,000.
    if (isset($_POST['max_input_vars_test']) == FALSE) {
        output_error('Sorry, the server did not accept the form that you submitted. We recommend that you ask the server administrator to check the max_input_vars PHP setting in the php.ini file.  We recommend that it be set to a number that is at least double the number of Products that the site will contain. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    $liveform->add_fields_to_session();
    
    // get all items that can be featured and new
    $items = get_items_that_can_be_featured_and_new();
    
    // loop through all items in order to validate new date field for each item
    foreach ($items as $item) {
        // if this item is not the root product group, then continue
        if ($item['parent_product_group_id'] != 0) {
            // if this item is a product group, then prepare new date field name in a certain way
            if ($item['type'] == 'product group') {
                $new_date_field_name = 'new_date_product_group_' . $item['id'];
                
            // else this item is a product, so prepare new date field name in a different way
            } else {
                $new_date_field_name = 'new_date_product_group_' . $item['parent_product_group_id'] . '_product_' . $item['id'];
            }
            
            // if a new date was entered for this item and the date is invalid, prepare error
            if (($liveform->get_field_value($new_date_field_name) != '') && (validate_date($liveform->get_field_value($new_date_field_name)) == false)) {
                // if this item is a product group, then prepare name for error message in a certain way
                if ($item['type'] == 'product group') {
                    $output_name = h($item['name']);
                    
                // else this item is a product, so prepare name for error message in a different way
                } else {
                    $output_name = h($item['name']) . ' - ' . h($item['short_description']);
                }
                
                $liveform->mark_error($new_date_field_name, 'Please enter a valid new date for ' . $output_name . '.');
            }
        }
    }
    
    // if there is an error, forward user back to the form
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_featured_and_new_items.php');
        exit();
    }
    
    // loop through all items in order to update featured and new data for items
    foreach ($items as $item) {
        // if this item is not the root product group, then continue
        if ($item['parent_product_group_id'] != 0) {
            // if this item is a product group, then prepare field names in a certain way
            if ($item['type'] == 'product group') {
                $featured_sort_order_field_name = 'featured_sort_order_product_group_' . $item['id'];
                $new_date_field_name = 'new_date_product_group_' . $item['id'];
                
            // else this item is a product, so prepare field names in a different way
            } else {
                $featured_sort_order_field_name = 'featured_sort_order_product_group_' . $item['parent_product_group_id'] . '_product_' . $item['id'];
                $new_date_field_name = 'new_date_product_group_' . $item['parent_product_group_id'] . '_product_' . $item['id'];
            }
            
            // if the data for the item has changed (see details below),
            // if the item was not featured and is now featured,
            // or if the item was featured and is now not featured,
            // or if the item is now featured and the featured sort order is different,
            // or if the item did not have a new date and it has a new date now,
            // or if the item did have a new date and it does not have a new date now,
            // or if the item now has a new date and the new date is different,
            // then update the item
            if (
                (($item['featured'] == 0) && ($liveform->get_field_value($featured_sort_order_field_name) != ''))
                || (($item['featured'] == 1) && ($liveform->get_field_value($featured_sort_order_field_name) == ''))
                || (($liveform->get_field_value($featured_sort_order_field_name) != '') && ($item['featured_sort_order'] != $liveform->get_field_value($featured_sort_order_field_name)))
                || (($item['new_date'] == '0000-00-00') && ($liveform->get_field_value($new_date_field_name) != ''))
                || (($item['new_date'] != '0000-00-00') && ($liveform->get_field_value($new_date_field_name) == ''))
                || (($liveform->get_field_value($new_date_field_name) != '') && ($item['new_date'] != prepare_form_data_for_input($liveform->get_field_value($new_date_field_name), 'date')))
            ) {
                // if this item is a product group, then prepare sql in a certain way
                if ($item['type'] == 'product group') {
                    $table_name = 'product_groups';
                    $sql_where = "id = '" . $item['id'] . "'";
                    
                // else this item is a product, so prepare sql in a different way
                } else {
                    $table_name = 'products_groups_xref';
                    $sql_where = "(product = '" . $item['id'] . "') AND (product_group = '" . $item['parent_product_group_id'] . "')";
                }
                
                // assume that the item should not be featured until we find out otherwise
                $featured = '0';
                $featured_sort_order = '0';
                
                // if a featured sort order was entered for this item, then prepare to turn featured on
                if ($liveform->get_field_value($featured_sort_order_field_name) != '') {
                    $featured = 1;
                    $featured_sort_order = $liveform->get_field_value($featured_sort_order_field_name);
                }
                
                // update featured and new data for item
                $query =
                    "UPDATE $table_name
                    SET
                        featured = '" . $featured . "',
                        featured_sort_order = '" . escape($featured_sort_order) . "',
                        new_date = '" . escape(prepare_form_data_for_input($liveform->get_field_value($new_date_field_name), 'date')) . "'
                    WHERE $sql_where";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
    }
    
    log_activity('featured and new items were modified', $_SESSION['sessionusername']);
    
    $confirmation_message = 'The featured and new items have been saved.';
    
    // if the user came from the view product groups screen, then prepare notice for that screen
    if ($liveform->get_field_value('from') == 'view_product_groups') {
        $liveform_view_product_groups = new liveform('view_product_groups');
        $liveform_view_product_groups->add_notice($confirmation_message);
        
    // else the user came from the view products screen, so prepare notice for that screen
    } else {
        $liveform_view_products = new liveform('view_products');
        $liveform_view_products->add_notice($confirmation_message);
    }
    
    // forward user to send to
    header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
    
    $liveform->remove_form();
}

function get_items_that_can_be_featured_and_new($parent_product_group_id = 0, $level = 0)
{
    // initialize arrays for storing items and sorting
    $items = array();
    $item_sort_orders = array();
    $item_names = array();
    
    // get product groups under this parent product group
    $query =
        "SELECT
            id,
            name,
            enabled,
            parent_id,
            sort_order,
            display_type,
            featured,
            featured_sort_order,
            new_date
        FROM product_groups
        WHERE parent_id = '" . escape($parent_product_group_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        // add product_group to items array
        $items[] = 
            array (
                'type' => 'product group',
                'id' => $row['id'],
                'name' => $row['name'],
                'enabled' => $row['enabled'],
                'parent_product_group_id' => $row['parent_id'],
                'sort_order' => $row['sort_order'],
                'display_type' => $row['display_type'],
                'featured' => $row['featured'],
                'featured_sort_order' => $row['featured_sort_order'],
                'new_date' => $row['new_date'],
                'level' => $level
            );
        
        // add data to sorting arrays
        $item_sort_orders[] = $row['sort_order'];
        $item_names[] = mb_strtolower($row['name']);
    }
    
    // get products under this parent id
    $query =
        "SELECT
            products.id,
            products.name,
            products.enabled,
            products.short_description,
            products_groups_xref.product_group,
            products_groups_xref.sort_order,
            products_groups_xref.featured,
            products_groups_xref.featured_sort_order,
            products_groups_xref.new_date
        FROM products_groups_xref
        LEFT JOIN products ON products_groups_xref.product = products.id
        WHERE products_groups_xref.product_group = '" . escape($parent_product_group_id) . "'
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        // add product to items array
        $items[] = 
            array (
                'type' => 'product',
                'id' => $row['id'],
                'name' => $row['name'],
                'enabled' => $row['enabled'],
                'short_description' => $row['short_description'],
                'parent_product_group_id' => $row['product_group'],
                'sort_order' => $row['sort_order'],
                'featured' => $row['featured'],
                'featured_sort_order' => $row['featured_sort_order'],
                'new_date' => $row['new_date'],
                'level' => $level
            );
        
        // add data to sorting arrays
        $item_sort_orders[] = $row['sort_order'];
        $item_names[] = mb_strtolower($row['name']);
    }
    
    // sort the items by sort order and then by name
    array_multisort($item_sort_orders, $item_names, $items);
    
    $items_for_returning = array();
    
    // loop through the items in order to return them
    foreach ($items as $item) {
        $items_for_returning[] = $item;
        
        // if this item is a product group and the product group is a browse product group, then get items under this product group
        if (($item['type'] == 'product group') && ($item['display_type'] == 'browse')) {
            $items_for_returning = array_merge($items_for_returning, get_items_that_can_be_featured_and_new($item['id'], $level + 1));
        }
    }
    
    return $items_for_returning;
}
?>