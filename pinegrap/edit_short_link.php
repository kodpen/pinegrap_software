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
$liveform = new liveform('edit_short_link');

// Get short link data that we will use later.
$query =
    "SELECT 
        short_links.id,
        short_links.name,
        short_links.destination_type,
        short_links.page_id,
        short_links.product_group_id,
        short_links.product_id,
        short_links.url,
        short_links.tracking_code,
        short_links.created_user_id,
        page.page_folder AS folder_id,
        page.page_type
    FROM short_links
    LEFT JOIN page ON short_links.page_id = page.page_id
    WHERE short_links.id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$short_link = mysqli_fetch_assoc($result);

// If this user has a user role then determine if user has access to short link.
// A user has access to a short link if he/she has edit rights to the short link's page
// or for url type: created the short link.
if (USER_ROLE == 3) {
    // Determine if the user has access to the short link differently based on the destination type.
    switch ($short_link['destination_type']) {
        default:
            // If the user does not have edit access to the page's folder, then output error.
            if (check_edit_access($short_link['folder_id']) == false) {
                log_activity('access denied to edit short link because user does not have edit rights to page', $_SESSION['sessionusername']);
                output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
            }

            break;

        case 'url':
            // If this user is not the user that created the short link, then output error.
            if (USER_ID != $short_link['created_user_id']) {
                log_activity('access denied to edit short link because user did not create short link', $_SESSION['sessionusername']);
                output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
            }

            break;
    }
}

// If the form was not just submitted then output form.
if (!$_POST) {
    // If the form has not been submitted at all yet, pre-populate fields with data.
    if ($liveform->field_in_session('id') == FALSE) {
        $liveform->assign_field_value('name', $short_link['name']);
        $liveform->assign_field_value('destination_type', $short_link['destination_type']);

        // Set the page field differently based on the destination type.
        switch ($liveform->get_field_value('destination_type')) {
            case 'page':
                $liveform->assign_field_value('page_id', $short_link['page_id']);
                break;
            
            case 'product_group':
                // If the page is a catalog page, then set page for that field.
                if ($short_link['page_type'] == 'catalog') {
                    $liveform->assign_field_value('catalog_page_id', $short_link['page_id']);

                // Otherwise the page is a catalog detail page, so set page for that field.
                } else {
                    $liveform->assign_field_value('catalog_detail_page_id', $short_link['page_id']);
                }

                break;

            case 'product':
                $liveform->assign_field_value('catalog_detail_page_id', $short_link['page_id']);
                break;

            case 'url':
                $liveform->assign_field_value('url', $short_link['url']);
                break;
        }

        $liveform->assign_field_value('product_group_id', $short_link['product_group_id']);
        $liveform->assign_field_value('product_id', $short_link['product_id']);
        $liveform->assign_field_value('tracking_code', $short_link['tracking_code']);
    }

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
            <h1><a href="' . OUTPUT_PATH . h($short_link['name']) . '" target="_blank">' . h($short_link['name']) . '</a></h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Short Link</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Update this shortcut alias for a Page, Product Group, Product, or URL.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id'])) . '
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
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This short link will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form was just submitted, so process it.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();

    $handle = @fopen(HTACCESS_FILE_PATH, 'a');

    // If the rewrite file does not exist or can not be written to, then add error and send user back to previous screen.
    if ($handle == FALSE) {
        $liveform->mark_error('', HTACCESS_FILE_NAME . ' is not writeable so your request could not be completed.');
        header('Location: ' . URL_SCHEME . HOSTNAME . $_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
        exit();

    // Otherwise the rewrite file is writeable, so close handle.
    } else {
        @fclose($handle);
    }

    // If the user selected to delete this short link, then delete it.
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        $query = "DELETE FROM short_links WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('short link (' . $short_link['name'] . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_short_links = new liveform('view_short_links');
        $liveform_view_short_links->add_notice('The short link has been deleted.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_short_links.php');

    // Otherwise the user selected to save the short link, so save it.
    } else {
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
            && (check_name_availability(array('name' => $name, 'ignore_item_id' => $liveform->get_field_value('id'), 'ignore_item_type' => 'short_link')) == false)
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
            header('Location: ' . URL_SCHEME . HOSTNAME . $_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
            exit();
        }

        $product_group_id = '';
        $product_id = '';
        $url = '';

        // Prepare the SQL differently based on the destination type.
        switch ($liveform->get_field_value('destination_type')) {
            case 'product_group':
                $product_group_id = $liveform->get_field_value('product_group_id');
                break;
            
            case 'product':
                $product_id = $liveform->get_field_value('product_id');
                break;

            case 'url':
                $url = $liveform->get_field_value('url');
                break;
        }

        // Update short link.
        $query =
            "UPDATE short_links
            SET
                name = '" . escape($name) . "',
                destination_type = '" . escape($liveform->get_field_value('destination_type')) . "',
                page_id = '" . escape($page_id) . "',
                product_group_id = '" . escape($product_group_id) . "',
                product_id = '" . escape($product_id) . "',
                url = '" . escape($url) . "',
                tracking_code = '" . escape($liveform->get_field_value('tracking_code')) . "',
                last_modified_user_id = '" . USER_ID . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('short link (' . $name . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_short_links = new liveform('view_short_links');
        $liveform_view_short_links->add_notice('The short link has been saved.');

        // Forward user to view short links screen.
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_short_links.php');
    }
    
    $liveform->remove_form();
    exit();
}
?>