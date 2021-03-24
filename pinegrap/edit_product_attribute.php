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
$liveform = new liveform('edit_product_attribute');

$product_attribute = db_item(
    "SELECT
        name,
        label
    FROM product_attributes
    WHERE id = '" . escape($_REQUEST['id']) . "'");

$options = db_items(
    "SELECT
        id,
        label,
        no_value
    FROM product_attribute_options
    WHERE product_attribute_id = '" . escape($_REQUEST['id']) . "'
    ORDER BY sort_order");

// If the form has not just been submitted, then output form.
if (!$_POST) {
    // If the form has not been submitted yet, then pre-populate fields with data.
    if ($liveform->field_in_session('id') == false) {
        $liveform->assign_field_value('name', $product_attribute['name']);
        $liveform->assign_field_value('label', $product_attribute['label']);
        $liveform->assign_field_value('options', encode_json($options));
    }

    $options = $liveform->get_field_value('options');

    if (($options != '') && ($options != '[]')) {
        $output_options = $options;
    } else {
        $output_options = '[{label: ""},{label: ""}]';
    }
    
    echo
        output_header() . '
        <div id="subnav">
            <h1>' . h($product_attribute['name']) . '</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Product Attribute</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Update the name, label, and options for this product attribute.</div>
            <form method="post" class="product_attribute_form">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type' => 'hidden', 'name' => 'id', 'value' => $_GET['id'])) . '
                <table class="field">
                    <tr>
                        <td>Name:</td>
                        <td>
                            ' . $liveform->output_field(array(
                                'type' => 'text',
                                'name' => 'name',
                                'size' => '50',
                                'maxlength' => '100')) . '
                        </td>
                    </tr>
                    <tr>
                        <td>Label:</td>
                        <td>
                            ' . $liveform->output_field(array(
                                'type' => 'text',
                                'name' => 'label',
                                'size' => '50',
                                'maxlength' => '255')) . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Options</h2></th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="options" style="margin-bottom: 2em"></div>
                            <script>init_product_attribute_options(' . $output_options . ')</script>
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This product attribute will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted, so process it.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // If the user selected to delete this product attribute, then delete it.
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        db("DELETE FROM product_attributes WHERE id = '" . escape($liveform->get_field_value('id')) . "'");
        db("DELETE FROM product_attribute_options WHERE product_attribute_id = '" . escape($liveform->get_field_value('id')) . "'");
        db("DELETE FROM products_attributes_xref WHERE attribute_id = '" . escape($liveform->get_field_value('id')) . "'");
        db("DELETE FROM product_groups_attributes_xref WHERE attribute_id = '" . escape($liveform->get_field_value('id')) . "'");
        
        log_activity('product attribute (' . $product_attribute['name'] . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_product_attributes = new liveform('view_product_attributes');
        $liveform_view_product_attributes->add_notice('The product attribute has been deleted.');

        $liveform->remove_form();

        go(PATH . SOFTWARE_DIRECTORY . '/view_product_attributes.php');
        
    // Otherwise the user selected to save the product attribute, so save it.
    } else {
        $liveform->validate_required_field('name', 'Name is required.');

        // If there is not already an error for the name field,
        // and that name is already in use, then output error.
        if (
            ($liveform->check_field_error('name') == false)
            && (db_value("SELECT COUNT(*) FROM product_attributes WHERE (name = '" . e($liveform->get_field_value('name')) . "') AND (id != '" . e($liveform->get_field_value('id')) . "')") != 0)
        ) {
            $liveform->mark_error('name', 'Sorry, the name that you entered is already in use, so please enter a different name.');
        }

        $options = decode_json($liveform->get_field_value('options'));

        if (count($options) == 0) {
            $liveform->mark_error('', 'Please add an option.');
        }
        
        // If there is an error, forward user back to previous screen.
        if ($liveform->check_form_errors() == true) {
            go($_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
        }
        
        db(
            "UPDATE product_attributes
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                label = '" . escape($liveform->get_field_value('label')) . "',
                last_modified_user_id = '" . USER_ID . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($liveform->get_field_value('id')) . "'");

        // Loop through the options in order to update or add new ones.

        $sort_order = 0;
        $sql_delete_exception = "";

        foreach ($options as $option) {
            $sort_order++;

            // If there is an id for an existing option, then update option.
            if ($option['id']) {
                db(
                    "UPDATE product_attribute_options
                    SET
                        label = '" . e($option['label']) . "',
                        no_value = '" . e($option['no_value']) . "',
                        sort_order = '$sort_order'
                    WHERE id = '" . e($option['id']) . "'");

                $id = $option['id'];

            // Otherwise there is not an id, so create new option.
            } else {
                db(
                    "INSERT INTO product_attribute_options (
                        product_attribute_id,
                        label,
                        no_value,
                        sort_order)
                    VALUES (
                        '" . e($liveform->get_field_value('id')) . "',
                        '" . e($option['label']) . "',
                        '" . e($option['no_value']) . "',
                        '$sort_order')");

                $id = mysqli_insert_id(db::$con);
            }

            // Update delete exception so that later we don't delete this option.
            $sql_delete_exception .= " AND (id != '" . e($id) . "')";
        }

        // Get all options that we need to delete.
        $deleted_options = db_items(
            "SELECT id
            FROM product_attribute_options
            WHERE
                (product_attribute_id = '" . e($liveform->get_field_value('id')) . "')
                $sql_delete_exception");

        // Loop through all options that need to be deleted in order to delete them.
        foreach ($deleted_options as $option) {
            // Delete option.
            db("DELETE FROM product_attribute_options WHERE id = '" . $option['id'] . "'");

            // Delete product associations with this option.
            db("DELETE FROM products_attributes_xref WHERE option_id = '" . $option['id'] . "'");
        }
        
        log_activity('product attribute (' . $product_attribute['name'] . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_product_attributes = new liveform('view_product_attributes');
        $liveform_view_product_attributes->add_notice('The product attribute has been saved.');

        $liveform->remove_form();

        go(PATH . SOFTWARE_DIRECTORY . '/view_product_attributes.php');
    }
}
?>