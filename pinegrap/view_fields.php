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

$output_layout_buttons = '';
$output_rss_table_heading = '';
$output_office_use_only_table_heading = '';

// if there is a page_id supplied in the query string, then this is a page form
if ((isset($_GET['page_id'])) && ($_GET['page_id'] != '')) {
    validate_area_access($user, 'user');
    
    // get page info
    $query =
        "SELECT
            page_type,
            page_folder,
            page_name
        FROM page
        WHERE page_id = '" . escape($_GET['page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $page_type = $row['page_type'];
    $folder_id = $row['page_folder'];
    $page_name = $row['page_name'];
    
    $form_type = '';
    
    // get the form type by looking at the page type
    switch ($page_type) {
        case 'custom form':
            $form_type = 'custom';
            break;

        // Express order can have a shipping and/or billing form, so check query string for the type
        // of form that we are dealing with
        case 'express order':

            if ($_REQUEST['form_type'] == 'shipping') {
                $form_type = 'shipping';
            } else {
                $form_type = 'billing';
            }

            break;

        case 'shipping address and arrival':
            $form_type = 'shipping';
            break;

        case 'billing information':
            $form_type = 'billing';
            break;
    }

    // Get the form type name that we will output to user

    $form_type_name = '';

    switch ($form_type) {
        case 'custom':
            $form_type_name = 'custom form';
            break;

        case 'shipping':
            $form_type_name = 'custom shipping form';
            break;

        case 'billing':
            $form_type_name = 'custom billing form';
            break;
    }
    
    $form_type_identifier_id = 'page_id';

    // Prepare sql filter in order to get correct fields

    $form_type_filter =
        "form_fields." . $form_type_identifier_id . " = '" . e($_REQUEST[$form_type_identifier_id]) . "'";

    // If the page type is express order then we need to add an extra filter for the form type
    if ($page_type == 'express order') {
        $form_type_filter .=
            " AND form_fields.form_type = '" . e($form_type) . "'";
    }
    
    // validate user's access
    if (check_edit_access($folder_id) == false) {
        log_activity('access denied to view fields for ' . $form_type_name . ' because user does not have access to modify folder that ' . $form_type_name . ' is in', $_SESSION['sessionusername']);
        output_error('Access denied.');
    }

    $form_name = '';

    // If this is a page and form type that supports a form name, then get it
    if ($page_type != 'express order' or $form_type != 'shipping') {

        // get form name for page
        $query = "SELECT form_name FROM " . str_replace(' ', '_', $page_type) . "_pages WHERE page_id = '" . escape($_GET['page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $form_name = $row['form_name'];
    }
    
    // if form name is blank, use page name for form name
    if (!$form_name) {
        $form_name = $page_name;
    }
    
    // setup form designer heading, content heading and subheading.
    $output_form_designer_subnav_heading = h($form_name);
    
    $output_form_designer_subnav_subheading = '';
    
    // if the page name is different from the form name, then output page name
    if ($form_name != $page_name) {
        $output_form_designer_subnav_subheading = 'Displayed in Page: ' . h($page_name) . '';
    }

    // If this page supports a custom layout and the user is an admin or designer,
    // then determine if we need to output generate layout button.
    if (
        check_if_page_type_supports_layout($page_type)
        && (USER_ROLE < 2)
    ) {
        $layout_type = db_value(
            "SELECT layout_type
            FROM page
            WHERE page_id = '" . e($_GET['page_id']) . "'");

        // If this page has a custom layout type then output generate layout button.
        if ($layout_type == 'custom') {
            $output_layout_buttons =
                '<a href="page_designer.php?url=' . h(urlencode(PATH . encode_url_path($page_name))) . '&amp;type=layout&amp;id=' . h($_GET['page_id']) . '" target="_top">Edit Layout</a>
                <a href="generate_layout.php?page_id=' . h($_GET['page_id']) . '">Generate Layout</a>';
        }
    }
    
    $output_form_designer_content_heading = 'Edit ' . ucwords($form_type_name);
    $output_form_designer_content_subheading = 'Add fields to this ' . $form_type_name . '.';
    $delete_data_warning = '';
    
    // If this is a custom form, then output certain content.
    if ($form_type == 'custom') {
        $output_rss_table_heading = '<th>RSS Element</th>';
        $output_office_use_only_table_heading = '<th style="text-align: center">Office Use Only</th>';
        $delete_data_warning = ' and ALL SUBMITTED FORM DATA for the selected field(s)';
    }
    
    $output_form_designer_footer = '
        <div class="buttons">
            <input type="button" value="Back to Page" class="submit-secondary" onclick="window.location.href=\'' . h(escape_javascript($_GET['send_to'])) . '\'" />&nbsp;&nbsp;&nbsp;<input type="submit" value="Delete Selected" class="delete" onclick="return confirm(\'WARNING: The selected field(s)' . $delete_data_warning . ' will be permanently deleted.\')" />
        </div>';
    
// else if there is a product_id supplied in the query string, this is a product form
} elseif ((isset($_GET['product_id'])) && ($_GET['product_id'] != '')) {
    validate_ecommerce_access($user);
    
    $form_type = 'product';
    $form_type_name = 'product form';
    $form_type_identifier_id = 'product_id';
    $form_type_filter =
        "form_fields." . $form_type_identifier_id . " = '" . e($_GET[$form_type_identifier_id]) . "'";
    
    // get product name, short description and form name to determine what we will use for the form name
    $query = "SELECT 
                 name,
                 short_description,
                 form_name
             FROM products
             WHERE id = '" . escape($_GET['product_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $product_name = $row['name'];
    $short_description = $row['short_description'];
    $form_name = $row['form_name'];
    
    // if form name is blank and short description is not, use short description for form name
    if (($form_name == '') && ($short_description != '')) {
        $form_name = $short_description;
        
    // else, if form name is blank and product name is not, use product name for form name
    } else if (($form_name == '') && ($product_name != '')) {
        $form_name = $product_name;
    }
    
    // setup form designer heading, content heading and subheading
    $output_form_designer_subnav_heading = h($short_description);
    $output_form_designer_subnav_subheading = 'Product ID: ' . h($product_name) . ' | Form Name: ' . h($form_name);
    $output_form_designer_content_heading = 'Edit Product Form';
    $output_form_designer_content_subheading = 'Add fields to this product form.';
    
    $output_form_designer_footer = 
        '<div class="buttons">
            <input type="button" value="Back to Product" class="submit-secondary" onclick="window.location.href=\'edit_product.php?id=' . h(escape_javascript($_GET['product_id'])) . '\'" />&nbsp;&nbsp;&nbsp;<input type="button" value="Preview" class="submit-secondary" onclick="document.location.href = \'preview_form.php?product_id=\' + document.getElementById(\'product_id\').value" />&nbsp;&nbsp;&nbsp;<input type="submit" value="Delete Selected" class="delete" onclick="return confirm(\'WARNING: The selected field(s) will be permanently deleted.\')" />
        </div>';
    
    // Send the product id through the browser url.
    $output_product_id_to_url = '&product_id=' . h(escape_javascript(urlencode($_GET['product_id'])));
}

// get fields
$query = "SELECT
            form_fields.id,
            form_fields.name,
            form_fields.rss_field,
            form_fields.label,
            form_fields.information,
            form_fields.type,
            form_fields.required,
            form_fields.office_use_only,
            user.user_username as username,
            form_fields.timestamp
         FROM form_fields
         LEFT JOIN user ON form_fields.user = user.user_id
         WHERE $form_type_filter
         ORDER BY form_fields.sort_order";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$number_of_results = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $name = $row['name'];
    $rss_field = $row['rss_field'];
    $type = get_field_type_name($row['type']);
    $office_use_only = '';
    
    if ($row['type'] == 'information') {
        $label_or_information = $row['information'];
    } else {
        $label_or_information = $row['label'];
    }
    
    if ($row['required'] == 1) {
        $required = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    } else {
        $required = '';
    }
    
    $office_use_only = '';
    $output_rss_field_cell = '';
    
    $username = $row['username'];
    $timestamp = $row['timestamp'];
    
    $output_link_url = 'edit_field.php?id=' . $id . $output_product_id_to_url . '&send_to=' . h(escape_javascript(urlencode($_GET['send_to'])));
    
    // if this is a custom form
    if ($form_type == 'custom') {
        $output_rss_field_cell = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($rss_field) . '</td>';
        
        // if office_use_only is set to 1, output * in the cell
        if ($row['office_use_only'] == 1) {
            $office_use_only = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center"><img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" /></td>';
        } else {
            $office_use_only = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'"></td>';
        }
    }
    
    $number_of_results++;
    
    $output_rows .=
        '<tr>
            <td class="selectall"><input type="checkbox" name="fields[]" value="' . $id . '" class="checkbox" /></td>
            <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">' . h($name) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $label_or_information . '</td>
            ' . $output_rss_field_cell . '
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($type) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center;">' . $required . '</td>
            ' . $office_use_only . '
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

$liveform = new liveform('view_fields');

echo
    output_header() . '
    <div id="subnav">
        <h1>' . $output_form_designer_subnav_heading . '</h1>
        <div class="subheading">' . $output_form_designer_subnav_subheading . '</div>
    </div>
    <div id="button_bar">
        <a href="add_field.php?' . $form_type_identifier_id . '=' . h(urlencode($_GET[$form_type_identifier_id])) . '&amp;form_type=' . $form_type . '&amp;send_to=' . h(urlencode($_GET['send_to'])) . '" >Create Form Field</a>
        ' . $output_layout_buttons . '
    </div>
    <div id="content">
        ' . $liveform->get_messages() . '
        <a href="#" id="help_link">Help</a>
        <h1>' . $output_form_designer_content_heading . '</h1>
        <div class="subheading">' . $output_form_designer_content_subheading . '</div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
        </div>
        <form name="form" action="delete_fields.php" method="post" style="margin: 0" class="disable_shortcut">
            ' . get_token_field() . '
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
            <input type="hidden" name="' . h($form_type_identifier_id) . '" id="' . h($form_type_identifier_id) . '" value="' . h($_GET[$form_type_identifier_id]) . '">
            <input type="hidden" name="form_type" value="' . $form_type . '">
            <table class="chart" style="margin-bottom: 15px">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
                    <th>Name</th>
                    <th>Label / Information</th>
                    ' . $output_rss_table_heading . '
                    <th>Field Type</th>
                    <th style="text-align: center">Required</th>
                    ' . $output_office_use_only_table_heading . '
                    <th>Last Modified</th>
                </tr>
                ' . $output_rows . '
            </table>
            ' . $output_form_designer_footer . '
        </form>
    </div>' . 
    output_footer();

$liveform->remove_form();

function get_field_type_name($field_type)
{
    switch ($field_type) {
        case 'text box':
            return 'Text Box';
            break;
            
        case 'text area':
            return 'Text Area';
            break;
        
        case 'pick list':
            return 'Pick List';
            break;
        
        case 'radio button':
            return 'Radio Button';
            break;
        
        case 'check box':
            return 'Check Box';
            break;
            
        case 'file upload':
            return 'File Upload';
            break;
            
        case 'date':
            return 'Date';
            break;
            
        case 'date and time':
            return 'Date & Time';
            break;
            
        case 'email address':
            return 'E-mail Address';
            break;
        
        case 'information':
            return 'Information';
            break;
            
        case 'time':
            return 'Time';
            break;
    }
}