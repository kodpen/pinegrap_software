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

// get all shipping methods for shipping discount feature
$query =
    "SELECT
        id,
        name,
        code
    FROM shipping_methods
    ORDER BY name ASC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$shipping_methods = array();

// loop through all shipping methods in order to add them to array
while ($row = mysqli_fetch_assoc($result)) {
    $shipping_methods[] = $row;
}

if (!$_POST) {
    // get offer action data
    $query = "SELECT * FROM offer_actions WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $name = $row['name'];
    $type = $row['type'];
    $discount_order_amount = sprintf("%01.2lf", $row['discount_order_amount'] / 100);
    $discount_order_percentage = $row['discount_order_percentage'];
    $discount_product_product_id = $row['discount_product_product_id'];
    $discount_product_amount = sprintf("%01.2lf", $row['discount_product_amount'] / 100);
    $discount_product_percentage = $row['discount_product_percentage'];
    $add_product_product_id = $row['add_product_product_id'];
    $add_product_quantity = $row['add_product_quantity'];
    $add_product_discount_amount = sprintf("%01.2lf", $row['add_product_discount_amount'] / 100);
    $add_product_discount_percentage = $row['add_product_discount_percentage'];
    $discount_shipping_percentage = $row['discount_shipping_percentage'];

    $discount_order_style = 'display: none';
    $discount_product_style = 'display: none';
    $add_product_style = 'display: none';
    $discount_shipping_style = 'display: none';

    switch ($type) {
        case 'discount order':
            $discount_order_style = '';
            break;

        case 'discount product':
            $discount_product_style = '';
            break;

        case 'add product':
            $add_product_style = '';
            break;
            
        case 'discount shipping':
            $discount_shipping_style = '';
            break;
    }
    
    $output_shipping_methods = '';
    
    // loop through all shipping methods in order to prepare output
    foreach ($shipping_methods as $shipping_method) {
        // check if shipping method is included in this offer action
        $query =
            "SELECT COUNT(*)
            FROM offer_actions_shipping_methods_xref
            WHERE
                (offer_action_id = '" . escape($_GET['id']) . "')
                AND (shipping_method_id = '" . $shipping_method['id'] . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        
        // assume that this shipping method should not be checked until we find out otherwise
        $checked = '';
        
        // if this shipping method is included in this offer action,
        // then prepare to check shipping method check box
        if ($row[0] > 0) {
            $checked = ' checked="checked"';
        }
        
        $output_shipping_methods .= '<input type="checkbox" name="shipping_method_' . $shipping_method['id'] . '" id="shipping_method_' . $shipping_method['id'] . '" value="1" class="checkbox"' . $checked . ' /><label for="shipping_method_' . $shipping_method['id'] . '"> ' . h($shipping_method['name']) . ' (' . h($shipping_method['code']) . ')</label><br />';
    }

    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($name) . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Offer Action</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit an offer action that can be assigned to any offer.</div>
            <form name="form" action="edit_offer_action.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Offer Action Name</h2></th>
                    </tr>
                    <tr>
                        <td>Offer Action Name:</td>
                        <td><input type="text" name="name" maxlength="50" value="' . h($name) . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Offer Action Name</h2></th>
                    </tr>
                    <tr>
                        <td>Type:</td>
                        <td><select name="type" onchange="change_offer_action_type(this.options[this.selectedIndex].value)"><option value="">-Select-</option>' . select_offer_action_type($type) . '</select></td>
                    </tr>
                </table>
                <div style="margin-bottom: 15px">
                    <fieldset id="discount_order" style="padding: 0px 10px 10px 10px; ' . $discount_order_style . '">
                        <legend style="margin-bottom: 10px"><strong>Discount Order</strong></legend>
                        <table>
                            <tr>
                                <td>Amount (' . BASE_CURRENCY_SYMBOL . '):</td>
                                <td><input type="text" name="discount_order_amount" value="' . $discount_order_amount . '" size="5" /></td>
                            </tr>
                            <tr>
                                <td colspan="2">or</td>
                            </tr>
                            <tr>
                                <td>Percentage (%):</td>
                                <td><input type="text" name="discount_order_percentage" value="' . $discount_order_percentage . '" size="3" maxlength="3" /></td>
                            </tr>
                        </table>
                    </fieldset>
                    <fieldset id="discount_product" style="padding: 0px 10px 10px 10px; ' . $discount_product_style . '">
                        <legend style="margin-bottom: 10px"><strong>Discount Product</strong></legend>
                        <table>
                            <tr>
                                <td>Product:</td>
                                <td><select name="discount_product_product_id"><option value="">-Select-</option>' . select_product($discount_product_product_id) . '</select></td>
                            </tr>
                            <tr>
                                <td>Amount (' . BASE_CURRENCY_SYMBOL . '):</td>
                                <td><input type="text" name="discount_product_amount" value="' . $discount_product_amount . '" size="5" /></td>
                            </tr>
                            <tr>
                                <td colspan="2">or</td>
                            </tr>
                            <tr>
                                <td>Percentage (%):</td>
                                <td><input type="text" name="discount_product_percentage" value="' . $discount_product_percentage . '" size="3" maxlength="3" /></td>
                            </tr>
                        </table>
                    </fieldset>
                    <fieldset id="add_product" style="padding: 0px 10px 10px 10px; ' . $add_product_style . '">
                        <legend style="margin-bottom: 10px"><strong>Add Product</strong></legend>
                        <table>
                            <tr>
                                <td>Product:</td>
                                <td><select name="add_product_product_id"><option value="">-Select-</option>' . select_product($add_product_product_id) . '</select></td>
                            </tr>
                            <tr>
                                <td>Quantity:</td>
                                <td><input type="text" name="add_product_quantity" value="' . $add_product_quantity . '" size="3" maxlength="10" /></td>
                            </tr>
                            <tr>
                                <td>Discount Amount (' . BASE_CURRENCY_SYMBOL . '):</td>
                                <td><input type="text" name="add_product_discount_amount" value="' . $add_product_discount_amount . '" size="5" /></td>
                            </tr>
                            <tr>
                                <td colspan="2">or</td>
                            </tr>
                            <tr>
                                <td>Discount Percentage (%):</td>
                                <td><input type="text" name="add_product_discount_percentage" value="' . $add_product_discount_percentage . '" size="3" maxlength="3" /></td>
                            </tr>
                        </table>
                    </fieldset>
                    <fieldset id="discount_shipping" style="padding: 0px 10px 10px 10px; ' . $discount_shipping_style . '">
                        <legend style="margin-bottom: 10px"><strong>Discount Shipping</strong></legend>
                        <table>
                            <tr>
                                <td>Percentage (%):</td>
                                <td><input type="text" name="discount_shipping_percentage" value="' . $discount_shipping_percentage . '" size="3" maxlength="3" /></td>
                            </tr>
                            <tr>
                                <td>Allowed Shipping Methods:</td>
                                <td style="white-space: nowrap">
                                    ' . $output_shipping_methods . '
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </div>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This offer action will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

} else {
    validate_token_field();
    
    // delete related records in offer_actions_shipping_methods_xref
    // we do this regardless of whether we are deleting or updating this offer action
    $query = "DELETE FROM offer_actions_shipping_methods_xref WHERE offer_action_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if offer action was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete offer action
        $query = "DELETE FROM offer_actions WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete records where this action was a selected action for offers
        $query = "DELETE FROM offers_offer_actions_xref WHERE offer_action_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('offer action (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
    // else offer action was not selected for delete
    } else {
        // convert dollars to cents
        $discount_order_amount = $_POST['discount_order_amount'] * 100;
        $discount_product_amount = $_POST['discount_product_amount'] * 100;
        $add_product_discount_amount = $_POST['add_product_discount_amount'] * 100;

        /* begin: set limit for percentages at 100 */

        if ($_POST['discount_order_percentage'] > 100) {
            $discount_order_percentage = 100;
        } else {
            $discount_order_percentage = $_POST['discount_order_percentage'];
        }

        if ($_POST['discount_product_percentage'] > 100) {
            $discount_product_percentage = 100;
        } else {
            $discount_product_percentage = $_POST['discount_product_percentage'];
        }

        if ($_POST['add_product_discount_percentage'] > 100) {
            $add_product_discount_percentage = 100;
        } else {
            $add_product_discount_percentage = $_POST['add_product_discount_percentage'];
        }
        
        if ($_POST['discount_shipping_percentage'] > 100) {
            $discount_shipping_percentage = 100;
        } else {
            $discount_shipping_percentage = $_POST['discount_shipping_percentage'];
        }

        /* end: set limit for percentages at 100 */

        // update offer action
        $query = "UPDATE offer_actions SET
                    name = '" . escape($_POST['name']) . "',
                    type = '" . escape($_POST['type']) . "',
                    discount_order_amount = '" . escape($discount_order_amount) . "',
                    discount_order_percentage = '" . escape($discount_order_percentage) . "',
                    discount_product_product_id = '" . escape($_POST['discount_product_product_id']) . "',
                    discount_product_amount = '" . escape($discount_product_amount) . "',
                    discount_product_percentage = '" . escape($discount_product_percentage) . "',
                    add_product_product_id = '" . escape($_POST['add_product_product_id']) . "',
                    add_product_quantity = '" . escape($_POST['add_product_quantity']) . "',
                    add_product_discount_amount = '" . escape($add_product_discount_amount) . "',
                    add_product_discount_percentage = '" . escape($add_product_discount_percentage) . "',
                    discount_shipping_percentage = '" . escape($discount_shipping_percentage) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through all shipping methods in order to add database records
        foreach ($shipping_methods as $shipping_method) {
            // if shipping method was selected, then add database record for shipping method for this offer action
            if ($_POST['shipping_method_' . $shipping_method['id']] == 1) {
                $query =
                    "INSERT INTO offer_actions_shipping_methods_xref (
                        offer_action_id,
                        shipping_method_id)
                    VALUES (
                        '" . escape($_POST['id']) . "',
                        '" . $shipping_method['id'] . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        log_activity('offer action (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
    }

    // forward user to view offer actions screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_offer_actions.php');
}
?>