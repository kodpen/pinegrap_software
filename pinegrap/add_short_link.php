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
validate_area_access($user, 'user');

include_once('liveform.class.php');
$liveform = new liveform('add_short_link');

// if the form has not been submitted
if (!$_POST) {
    $destination_type_options = array(
        '' => '',
        'Page' => 'page',
        'Product Group' => 'product_group',
        'Product' => 'product',
        'URL' => 'url');

    // Hide certain fields until we know which should be shown.
    $output_page_id_row_style = ' style="display: none"';
    $output_catalog_page_id_row_style = ' style="display: none"';
    $output_or_row_style = ' style="display: none"';
    $output_catalog_detail_page_id_row_style = ' style="display: none"';
    $output_product_group_id_row_style = ' style="display: none"';
    $output_product_id_row_style = ' style="display: none"';
    $output_url_row_style = ' style="display: none"';
    $output_tracking_code_row_style = ' style="display: none"';

    switch ($liveform->get_field_value('destination_type')) {
        case 'page':
            $output_page_id_row_style = '';
            $output_tracking_code_row_style = '';
            break;
        
        case 'product_group':
            $output_catalog_page_id_row_style = '';
            $output_or_row_style = '';
            $output_catalog_detail_page_id_row_style = '';
            $output_product_group_id_row_style = '';
            $output_tracking_code_row_style = '';
            break;

        case 'product':
            $output_catalog_detail_page_id_row_style = '';
            $output_product_id_row_style = '';
            $output_tracking_code_row_style = '';
            break;

        case 'url':
            $output_url_row_style = '';
            break;
    }

    print
        output_header() . '
        <div id="subnav">
            <h1>[new short link]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Short Link</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Create a shortcut alias for a Page, Product Group, Product, or URL.</div>
            <form action="add_short_link.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <td>Name:</td>
                        <td><span style="white-space: nowrap">' . URL_SCHEME . HOSTNAME . OUTPUT_PATH . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'50', 'maxlength'=>'100')) . '</span></td>
                    </tr>
                    <tr>
                        <td>Destination Type:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'id'=>'destination_type', 'name'=>'destination_type', 'options'=>$destination_type_options, 'onchange'=>'change_short_link_destination_type()')) . '</td>
                    </tr>
                    <tr id="page_id_row"' . $output_page_id_row_style . '>
                        <td>Page:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'page_id', 'options'=>get_page_options())) . '</td>
                    </tr>
                    <tr id="catalog_page_id_row"' . $output_catalog_page_id_row_style . '>
                        <td>Catalog Page:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'catalog_page_id', 'options'=>get_page_options('', 'catalog'))) . '</td>
                    </tr>
                    <tr id="or_row"' . $output_or_row_style . '>
                        <td colspan="2" style="padding-left: 1em">- or -</td>
                    </tr>
                    <tr id="catalog_detail_page_id_row"' . $output_catalog_detail_page_id_row_style . '>
                        <td>Catalog Detail Page:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'catalog_detail_page_id', 'options'=>get_page_options('', 'catalog detail'))) . '</td>
                    </tr>
                    <tr id="product_group_id_row"' . $output_product_group_id_row_style . '>
                        <td>Product Group:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'product_group_id', 'options'=>get_product_group_options($product_group_id = 0, $parent_product_group_id = 0, $excluded_product_group_id = 0, $level = 0, $product_groups = array(), $include_select_product_groups = TRUE, $format = 'array', $include_blank_option = TRUE))) . '</td>
                    </tr>
                    <tr id="product_id_row"' . $output_product_id_row_style . '>
                        <td>Product:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'product_id', 'options'=>get_product_options())) . '</td>
                    </tr>
                    <tr id="url_row"' . $output_url_row_style . '>
                        <td>Destination URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'url', 'size'=>'80', 'maxlength'=>'255', 'placeholder' => 'Enter URL that visitor should be redirected to')) . '</td>
                    </tr>
                    <tr id="tracking_code_row"' . $output_tracking_code_row_style . '>
                        <td>Tracking Code:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'tracking_code', 'size'=>'30', 'maxlength'=>'100')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();

    $handle = @fopen(HTACCESS_FILE_PATH, 'a');

    // If the rewrite file does not exist or can not be written to, then add error.
    if ($handle == FALSE) {
        $liveform->mark_error('', HTACCESS_FILE_NAME . ' is not writeable so a short link cannot be added.');

    // Otherwise the rewrite file is writeable, so close handle.
    } else {
        @fclose($handle);
    }
    
    $liveform->validate_required_field('name', 'Name is required.');

    $name = $liveform->get_field_value('name');

    // Replace spaces with underscores for the name.
    $name = str_replace(' ', '_', $name);

    // Update name in liveform.
    $liveform->assign_field_value('name', $name);

    // If there is not already an error for the name field and it contains invalid characters, then add error.
    if (
        ($liveform->check_field_error('name') == FALSE)
        && (preg_match('/[^A-Za-z0-9._\-\/]/', $name) == 1)
    ) {
        $liveform->mark_error('name', 'The name may only contain letters, numbers, periods, underscores, hyphens, and forward slashes.');
    }

    // If there is not already an error for the name field,
    // and the name is already in use, then add error.
    if (
        ($liveform->check_field_error('name') == false)
        && (check_name_availability(array('name' => $name)) == false)
    ) {
        $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
    }

    $liveform->validate_required_field('destination_type', 'Destination Type is required.');

    // If there is not already an error for the destination type and the destination type is invalid, then add error.
    if (
        ($liveform->check_field_error('destination_type') == FALSE)
        &&
        (
            ($liveform->get_field_value('destination_type') != 'page')
            && ($liveform->get_field_value('destination_type') != 'product_group')
            && ($liveform->get_field_value('destination_type') != 'product')
            && ($liveform->get_field_value('destination_type') != 'url')
        )
    ) {
        $liveform->mark_error('destination_type', 'The destination type is not valid.');
    }

    // If there is not already an error for the destination type, then validate other fields.
    if ($liveform->check_field_error('destination_type') == FALSE) {
        // Validate other fields differently based on the destination type.
        switch ($liveform->get_field_value('destination_type')) {
            case 'page':
                $liveform->validate_required_field('page_id', 'Page is required.');
                break;

            case 'product_group':
                // If neither a catalog page or a catalog detail page was selected, then add error.
                if (
                    ($liveform->get_field_value('catalog_page_id') == '')
                    && ($liveform->get_field_value('catalog_detail_page_id') == '')
                ) {
                    $liveform->mark_error('catalog_page_id', 'Catalog Page or Catalog Detail Page is required.');
                    $liveform->mark_error('catalog_detail_page_id', '');
                }

                // If both a catalog page and a catalog detail page were selected, then add error.
                if (
                    ($liveform->get_field_value('catalog_page_id') != '')
                    && ($liveform->get_field_value('catalog_detail_page_id') != '')
                ) {
                    $liveform->mark_error('catalog_page_id', 'Please select either a Catalog Page or a Catalog Detail Page, not both.');
                    $liveform->mark_error('catalog_detail_page_id', '');
                }

                $liveform->validate_required_field('product_group_id', 'Product Group is required.');

                // If there is not already an error for the product group, then check if product group exists.
                if ($liveform->check_field_error('product_group_id') == FALSE) {
                    $query = "SELECT COUNT(*) FROM product_groups WHERE id = '" . escape($liveform->get_field_value('product_group_id')) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_row($result);

                    // If the selected product group does not exist, then add error.
                    if ($row[0] == 0) {
                        $liveform->mark_error('product_group_id', 'The product group does not exist.');
                    }
                }
            
                break;
            
            case 'product':
                $liveform->validate_required_field('catalog_detail_page_id', 'Catalog Detail Page is required.');
                $liveform->validate_required_field('product_id', 'Product is required.');

                // If there is not already an error for the product, then check if product exists.
                if ($liveform->check_field_error('product_id') == FALSE) {
                    $query = "SELECT COUNT(*) FROM products WHERE id = '" . escape($liveform->get_field_value('product_id')) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_row($result);

                    // If the selected product does not exist, then add error.
                    if ($row[0] == 0) {
                        $liveform->mark_error('product_id', 'The product does not exist.');
                    }
                }

                break;

            case 'url':
                $liveform->validate_required_field('url', 'Destination URL is required.');

                // If there is not already an error for the url field
                // and the url contains white-space, then output error.
                // We have to do this to prevent outputting invalid code to the rewrite file
                // which would cause all URL's at the site to stop working.
                if (
                    ($liveform->check_field_error('url') == false)
                    && (preg_match('/\s/', $liveform->get_field_value('url')) == 1)
                ) {
                    $liveform->mark_error('url', 'Sorry, you may not enter a space in the URL.');
                }

                break;
        }
    }

    $page_id = 0;

    // If this is a certain destination type then check selected page in different ways.
    if (
        ($liveform->get_field_value('destination_type') == 'page')
        || ($liveform->get_field_value('destination_type') == 'product_group')
        || ($liveform->get_field_value('destination_type') == 'product')
    ) {
        // If there is not already an error, then prepare values that we will need below.
        if ($liveform->check_form_errors() == FALSE) {
            switch ($liveform->get_field_value('destination_type')) {
                case 'page':
                    $page_field_name = 'page_id';
                    break;

                case 'product_group':
                    // If a catalog page was selected, then remember that.
                    if ($liveform->get_field_value('catalog_page_id') != '') {
                        $page_field_name = 'catalog_page_id';

                    // Otherwise a catalog detail page was selected, so remember that.
                    } else {
                        $page_field_name = 'catalog_detail_page_id';
                    }
                
                    break;
                
                case 'product':
                    $page_field_name = 'catalog_detail_page_id';
                    break;
            }

            $page_id = $liveform->get_field_value($page_field_name);
        }

        // If there is not already an error, then check if page exists.
        if ($liveform->check_form_errors() == FALSE) {
            $query = "SELECT COUNT(*) FROM page WHERE page_id = '" . escape($page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);

            // If the selected page does not exist, then add error.
            if ($row[0] == 0) {
                $liveform->mark_error($page_field_name, 'The page does not exist.');
            }
        }

        // If there is not already an error and the user has a user role,
        // then check if user has edit rights to page.
        if (
            ($liveform->check_form_errors() == FALSE)
            && (USER_ROLE == 3)
        ) {
            // Get the page's folder in order to check if the user has edit rights to the page.
            $query = "SELECT page_folder AS folder_id FROM page WHERE page_id = '" . escape($page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $folder_id = $row['folder_id'];

            // If the user does not have edit rights to the page's folder, then log activity and add error.
            if (check_edit_access($folder_id) == false) {
                log_activity('access denied to add short link for page because user does not have edit rights to page', $_SESSION['sessionusername']);
                $liveform->mark_error($page_field_name, 'Sorry, you do not have access to that page.');
            }
        }
    }
    
    // If there is an error, forward user back to previous screen.
    if ($liveform->check_form_errors() == TRUE) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_short_link.php');
        exit();
    }

    $sql_field = "";
    $sql_value = "";

    // Prepare the SQL differently based on the destination type.
    switch ($liveform->get_field_value('destination_type')) {
        case 'product_group':
            $sql_field = "product_group_id,";
            $sql_value = "'" . escape($liveform->get_field_value('product_group_id')) . "',";
            break;
        
        case 'product':
            $sql_field = "product_id,";
            $sql_value = "'" . escape($liveform->get_field_value('product_id')) . "',";
            break;

        case 'url':
            $sql_field = "url,";
            $sql_value = "'" . escape($liveform->get_field_value('url')) . "',";
            break;
    }
    
    // create short link
    $query =
        "INSERT INTO short_links (
            name,
            destination_type,
            page_id,
            " . $sql_field . "
            tracking_code,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($name) . "',
            '" . escape($liveform->get_field_value('destination_type')) . "',
            '" . escape($page_id) . "',
            " . $sql_value . "
            '" . escape($liveform->get_field_value('tracking_code')) . "',
            '" . USER_ID . "',
            UNIX_TIMESTAMP(),
            '" . USER_ID . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('short link (' . $name . ') was created', $_SESSION['sessionusername']);
    
    $liveform_view_short_links = new liveform('view_short_links');
    $liveform_view_short_links->add_notice('The short link has been created.');

    // forward user to view short links screen
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_short_links.php');
    
    $liveform->remove_form();
}
?>