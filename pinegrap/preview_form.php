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

$liveform = new liveform('preview_form');

// get product info
$query =
    "SELECT 
        name,
        short_description,
        form_name,
        form_label_column_width
    FROM products
    WHERE id = '" . escape($_GET['product_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$product_name = $row['name'];
$short_description = $row['short_description'];
$form_name = $row['form_name'];
$form_label_column_width = $row['form_label_column_width'];

$form_description = $form_name;

// if form name is blank and short description is not, use short description for form name
if (($form_name == '') && ($short_description != '')) {
    $form_description = $short_description;
    
// else, if form name is blank and product name is not, use product name for form name
} else if (($form_name == '') && ($product_name != '')) {
    $form_description = $product_name;
}

$output_legend = '';

// if there is a form name, then output a legend
if ($form_name != '') {
    $output_legend = '<legend class="software_legend">' . h($form_name) . '</legend>';
}

$form_info = get_form_info(0, $_GET['product_id'], 0, 0, $form_label_column_width, $office_use_only = false, $liveform, 'backend');

// assume that we don't need to output wywiwyg javascript until we find out otherwise
$output_wysiwyg_javascript = '';

// if there is at least one wysiwyg field, prepare wysiwyg fields
if ($form_info['wysiwyg_fields']) {
    $output_wysiwyg_javascript = get_wysiwyg_editor_code($form_info['wysiwyg_fields']);
}

$output = output_header() . '
<div id="subnav">
    <h1>' . h($form_description) . '</h1>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>Preview Form</h1>
    <div class="subheading" style="margin-bottom: 1em">The form layout will look like this when displayed within a page.</div>
    ' . $output_wysiwyg_javascript . '
    <fieldset style="margin-bottom: 1em">
        ' . $output_legend . '
        <div style="padding: 0.7em">
            <table>
                ' . $form_info['content'] . '
            </table>
        </div>
    </fieldset>
    <div class="buttons">
        <input type="button" value="Back to Product Form" class="submit-primary" class="submit-secondary" onclick="document.location.href = \'view_fields.php?product_id=' . h($_GET['product_id']) . '\'" />
    </div>
</div>' .
output_footer();

print $output;