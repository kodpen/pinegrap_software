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
$liveform = new liveform('duplicate_product_group');

$product_group = db_item(
    "SELECT
        id,
        name
    FROM product_groups
    WHERE id = '" . escape($_REQUEST['id']) . "'");

// If the product group could not be found, then output error.
if ($product_group['id'] == '') {
    output_error('Sorry, that product group could not be found.');
}

// If the form has not just been submitted, then output form.
if (!$_POST) {
    echo
        output_header() . '
        <div id="subnav">
            <h1>' . h($product_group['name']) . '</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Duplicate Product Group</h1>
            <div class="subheading" style="margin-bottom: 1em">Please select any additional items that you want to duplicate.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type' => 'hidden', 'name' => 'id', 'value' => $_GET['id'])) . '
                <div>' . $liveform->output_field(array('type' => 'checkbox', 'id' => 'duplicate_product_groups', 'name' => 'duplicate_product_groups', 'value' => '1', 'class' => 'checkbox')) . '<label for="duplicate_product_groups"> Also duplicate Product Groups inside this Product Group.</label></div>
                <div>' . $liveform->output_field(array('type' => 'checkbox', 'id' => 'duplicate_products', 'name' => 'duplicate_products', 'value' => '1', 'class' => 'checkbox')) . '<label for="duplicate_products"> Also duplicate Products inside this Product Group.</label></div>
                <div class="buttons"><input type="submit" name="submit_duplicate" value="Duplicate" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_product_group.php?id=' . h(escape_javascript($_GET['id'])) . '\'" class="submit-secondary"></div>
            </form>
        </div>' .
        output_footer();
        
    $liveform->remove_form();

// Otherwise the form has been submitted, so process it.
} else {
    validate_token_field();

    $liveform->add_fields_to_session();

    if ($liveform->get_field_value('duplicate_product_groups') == 1) {
        $duplicate_product_groups = true;
    } else {
        $duplicate_product_groups = false;
    }

    if ($liveform->get_field_value('duplicate_products') == 1) {
        $duplicate_products = true;
    } else {
        $duplicate_products = false;
    }

    // Create an array that will be used as a global variable
    // inside the next function in order to keep track of which products
    // have already been duplicated, so the same product is not duplicated
    // multiple times.  The key/index of the array is the old product id
    // and the value is the new product id.
    $duplicated_products = array();

    // Create a function that can be used to duplicate a product group
    // and, optionally, all product groups and products under that product group.
    // This function uses recursion to go through the tree of product groups.
    function duplicate_product_group($properties)
    {
        global $duplicated_products;

        $product_group_id = $properties['product_group_id'];
        $parent_product_group_id = $properties['parent_product_group_id'];
        $duplicate_product_groups = $properties['duplicate_product_groups'];
        $duplicate_products = $properties['duplicate_products'];

        // Get product group info in order to duplicate it.
        $product_group = db_item(
            "SELECT
                id,
                name,
                enabled,
                parent_id,
                sort_order,
                short_description,
                full_description,
                details,
                code,
                keywords,
                image_name,
                display_type,
                featured,
                featured_sort_order,
                new_date,
                address_name,
                title,
                meta_description,
                meta_keywords,
                attributes
            FROM product_groups
            WHERE id = '$product_group_id'");

        // If the user selected to duplicate product groups,
        // then get child product groups in order to duplicate them later.
        // We do this now before we duplicate the main product group,
        // because if the user selected to duplicate the root group,
        // we are going to place the duplicated root in the root,
        // and don't want to consider that as an original child in order
        // to avoid extra duplication.
        if ($duplicate_product_groups == true) {
            $child_product_groups = db_items(
                "SELECT id
                FROM product_groups
                WHERE parent_id = '" . $product_group['id'] . "'
                ORDER BY sort_order ASC");
        }

        // Create array that will be used to store information for new product group
        // that will be created.
        $new_product_group = array();

        // If the parent product group id is blank, then that means this is the first
        // time this function has run, so we are dealing with the top-level product
        // group that the user selected to duplicate, so determine what the parent
        // of the new duplicated product group should be.
        if ($parent_product_group_id == '') {
            // If the user has selected the root product group to be duplicated,
            // then set the parent product group for the new duplicated group
            // to be the root product group, because there should only be one root
            // product group.
            if ($product_group['parent_id'] == 0) {
                $new_product_group['parent_id'] = $product_group['id'];

            // Otherwise the user has not selected the root group,
            // so use the same parent group as the group we are duplicating.
            } else {
                $new_product_group['parent_id'] = $product_group['parent_id'];
            }

            // For the top-level product group that the user selected to duplicate
            // we create a unique name (e.g. [1] on the end), so that the user
            // can easily distinguish the new group from the old one.  We don't change
            // the name for child product groups though.
            $new_product_group['name'] = get_unique_name(array(
                'name' => $product_group['name'],
                'type' => 'product_group'));

        // Otherwise the parent product group is not blank, so use it.
        } else {
            $new_product_group['parent_id'] = $parent_product_group_id;

            $new_product_group['name'] = $product_group['name'];
        }

        // If the address name is NOT blank then use that value for the address name.
        if ($product_group['address_name'] != '') {
            $new_product_group['address_name'] = $product_group['address_name'];
            
        // Otherwise if the short description is NOT blank then use that value.
        } elseif ($product_group['short_description'] != '') {
            $new_product_group['address_name'] = $product_group['short_description'];
            
        // Otherwise use the name as the value.
        } else {
            $new_product_group['address_name'] = $product_group['name'];
        }
        
        // Prepare the address name for the database.
        $new_product_group['address_name'] = prepare_catalog_item_address_name($new_product_group['address_name']);

        // Create new product group.
        db(
            "INSERT INTO product_groups (
                name,
                enabled,
                parent_id,
                sort_order,
                short_description,
                full_description,
                details,
                code,
                keywords,
                image_name,
                display_type,
                featured,
                featured_sort_order,
                new_date,
                address_name,
                title,
                meta_description,
                meta_keywords,
                attributes,
                user,
                timestamp)
            VALUES (
                '" . escape($new_product_group['name']) . "',
                '" . e($product_group['enabled']) . "',
                '" . $new_product_group['parent_id'] . "',
                '" . escape($product_group['sort_order']) . "',
                '" . escape($product_group['short_description']) . "',
                '" . escape($product_group['full_description']) . "',
                '" . escape($product_group['details']) . "',
                '" . escape($product_group['code']) . "',
                '" . escape($product_group['keywords']) . "',
                '" . escape($product_group['image_name']) . "',
                '" . escape($product_group['display_type']) . "',
                '" . escape($product_group['featured']) . "',
                '" . escape($product_group['featured_sort_order']) . "',
                '" . escape($product_group['new_date']) . "',
                '" . escape($new_product_group['address_name']) . "',
                '" . escape($product_group['title']) . "',
                '" . escape($product_group['meta_description']) . "',
                '" . escape($product_group['meta_keywords']) . "',
                '" . escape($product_group['attributes']) . "',
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");
        
        $new_product_group['id'] = mysqli_insert_id(db::$con);

        // If the user selected to duplicate product groups,
        // then loop through the child product groups in order to duplicate them.
        if ($duplicate_product_groups == true) {
            foreach ($child_product_groups as $child_product_group) {
                duplicate_product_group(array(
                    'product_group_id' => $child_product_group['id'],
                    'parent_product_group_id' => $new_product_group['id'],
                    'duplicate_product_groups' => $duplicate_product_groups,
                    'duplicate_products' => $duplicate_products));
            }
        }

        // Get all products that are in this product group,
        // in order to add products to new group.
        $product_references = db_items(
            "SELECT
                product AS id,
                sort_order,
                featured,
                featured_sort_order,
                new_date
            FROM products_groups_xref
            WHERE product_group = '" . $product_group['id'] . "'");

        // Loop through the products in order to add them to new group.
        foreach ($product_references as $product_reference) {
            // If the user selected to duplicate products, then deal with that.
            if ($duplicate_products == true) {
                // If this product has already been duplicated
                // as part of this duplication process,
                // then just use that new product (no need to duplicate again).
                if (isset($duplicated_products[$product_reference['id']]) == true) {
                    $product_id = $duplicated_products[$product_reference['id']];

                // Otherwise this product has not already been duplicated, so duplicate it.
                } else {
                    $product_id = duplicate_product($product_reference['id']);

                    $duplicated_products[$product_reference['id']] = $product_id;
                }

            // Otherwise the user did not select to duplicate products,
            // so just add existing product to new group we created.
            } else {
                $product_id = $product_reference['id'];
            }

            // Add product to new group that we created.
            db(
                "INSERT INTO products_groups_xref (
                    product,
                    product_group,
                    sort_order,
                    featured,
                    featured_sort_order,
                    new_date)
                VALUES (
                    '$product_id',
                    '" . $new_product_group['id'] . "',
                    '" . $product_reference['sort_order'] . "',
                    '" . $product_reference['featured'] . "',
                    '" . $product_reference['featured_sort_order'] . "',
                    '" . $product_reference['new_date'] . "')");
        }

        // Duplicate attribute associations with this product group.
        
        $attributes = db_items(
            "SELECT
                attribute_id,
                sort_order
            FROM product_groups_attributes_xref
            WHERE product_group_id = '" . $product_group['id'] . "'");

        foreach ($attributes as $attribute) {
            db(
                "INSERT INTO product_groups_attributes_xref (
                    product_group_id,
                    attribute_id,
                    sort_order)
                VALUES (
                    '" . $new_product_group['id'] . "',
                    '" . $attribute['attribute_id'] . "',
                    '" . $attribute['sort_order'] . "')");
        }

        return $new_product_group['id'];
    }

    $new_product_group_id = duplicate_product_group(array(
        'product_group_id' => $product_group['id'],
        'duplicate_product_groups' => $duplicate_product_groups,
        'duplicate_products' => $duplicate_products));

    // Update tag clouds.

    $search_results_pages = array();
        
    // get data from all search result pages that have "search products" enabled
    $query = "SELECT page_id, product_group_id FROM search_results_pages WHERE search_catalog_items = '1'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while($row = mysqli_fetch_assoc($result)) {
        $search_results_pages[] = $row;
    }
    
    $search_results_pages_using_parent_product_group = array();
    
    // loop through each search result page to get the search results page ids that uses this product group
    foreach ($search_results_pages as $search_results_page) {
        $search_results_page_product_groups = array();
        
        // get the product groups inside of this product group
        $search_results_page_product_groups = get_product_groups_in_product_group_tree($search_results_page['product_group_id']);
        
        // loop through the product groups to see if any of them match this one, and if there is a match then add it to the array
        foreach ($search_results_page_product_groups as $search_results_page_product_group) {
            if ($search_results_page_product_group['id'] == $new_product_group_id) {
                $search_results_pages_using_parent_product_group[] = $search_results_page;
            }
        }
    }
    
    // loop through the search result pages for the parent product group and delete then re-build it's tag cloud
    foreach($search_results_pages_using_parent_product_group as $search_results_page) {
        delete_tag_cloud_keywords_for_search_results_page($search_results_page['page_id']);
        update_tag_cloud_keywords_for_search_results_page_product_group($search_results_page['page_id'], $search_results_page['product_group_id']);
    }

    log_activity('product group (' . $product_group['name'] . ') was duplicated', $_SESSION['sessionusername']);

    $liveform_edit_product_group = new liveform('edit_product_group');
    $liveform_edit_product_group->add_notice('The product group has been duplicated, and you are now editing the duplicate.');

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/edit_product_group.php?id=' . $new_product_group_id);
}
?>