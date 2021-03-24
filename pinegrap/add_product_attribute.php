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
$liveform = new liveform('add_product_attribute');

// If the form has not been submitted, then output it.
if (!$_POST) {
    $options = $liveform->get_field_value('options');

    if (($options != '') && ($options != '[]')) {
        $output_options = $options;
    } else {
        $output_options = '[{label: ""},{label: ""}]';
    }

    echo
        output_header() . '
        <div id="subnav">
            <h1>[new product attribute]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Product Attribute</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Create a new product attribute.</div>
            <form method="post" class="product_attribute_form">
                ' . get_token_field() . '
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
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted so process it.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');

    // If there is not already an error for the name field,
    // and that name is already in use, then output error.
    if (
        ($liveform->check_field_error('name') == false)
        && (db_value("SELECT COUNT(*) FROM product_attributes WHERE name = '" . e($liveform->get_field_value('name')) . "'") != 0)
    ) {
        $liveform->mark_error('name', 'Sorry, the name that you entered is already in use, so please enter a different name.');
    }

    $options = decode_json($liveform->get_field_value('options'));

    if (count($options) == 0) {
        $liveform->mark_error('', 'Please add an option.');
    }
    
    if ($liveform->check_form_errors() == true) {
        go(PATH . SOFTWARE_DIRECTORY . '/add_product_attribute.php');
    }
    
    db(
        "INSERT INTO product_attributes (
            name,
            label,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            '" . escape($liveform->get_field_value('label')) . "',
            '" . USER_ID . "',
            UNIX_TIMESTAMP(),
            '" . USER_ID . "',
            UNIX_TIMESTAMP())");

    $id = mysqli_insert_id(db::$con);

    $sort_order = 0;

    foreach ($options as $option) {
        $sort_order++;

        db(
            "INSERT INTO product_attribute_options (
                product_attribute_id,
                label,
                no_value,
                sort_order)
            VALUES (
                '$id',
                '" . escape($option['label']) . "',
                '" . escape($option['no_value']) . "',
                '$sort_order')");
    }
    
    log_activity('product attribute (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);
    
    $liveform_view_product_attributes = new liveform('view_product_attributes');
    $liveform_view_product_attributes->add_notice('The product attribute has been created.');

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/view_product_attributes.php');
}
?>