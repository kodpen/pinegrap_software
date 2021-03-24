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

function update_product_group_status($properties) {

    $id = $properties['id'];
    $status = $properties['status'];

    if ($status == 'enabled') {
        $enabled = 1;
    } else {
        $enabled = 0;
    }

    // Get all the product groups that need to be updated, which includes this
    // product group and all child product groups.
    $product_groups = get_product_groups(array('id' => $id));

    $items = array();

    $sql_product_groups = "";
    $sql_not_in_product_groups = "";

    // Loop through the product groups in order to update the status for each.
    foreach ($product_groups as $product_group) {

        // Update product group status.
        db(
            "UPDATE product_groups
            SET
                enabled = '$enabled',
                user = '" . USER_ID . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($product_group['id']) . "'");

        $items[] = array(
            'id' => $product_group['id'],
            'type' => 'product_group',
            'status' => $status);

        // Add this product group to the SQL that we will use below to get
        // products in the scope of this operation.

        if ($sql_product_groups != '') {
            $sql_product_groups .= " OR ";
        }

        $sql_product_groups .= "(product_group = '" . e($product_group['id']) . "')";

        // Add this product group to the SQL that we will use further below
        // to check if a product is enabled in a different product group scope.
        $sql_not_in_product_groups .= " AND (products_groups_xref.product_group != '" . e($product_group['id']) . "')";

    }

    // Get all the products in this group and child groups, so we can update them.
    $products = db_items(
        "SELECT DISTINCT(product) AS id
        FROM products_groups_xref
        WHERE $sql_product_groups");

    // Loop through the products in order to update the status for each.
    foreach ($products as $product) {

        // If we are disabling products, and this product is enabled
        // in an enabled product group in a different area of the catalog,
        // then skip this product, because we don't want to disable a product
        // that is enabled in a different area of the catalog.
        if (
            $status == 'disabled'
            and db_value(
                "SELECT products_groups_xref.product
                FROM products_groups_xref
                LEFT JOIN products ON products_groups_xref.product = products.id
                LEFT JOIN product_groups ON products_groups_xref.product_group = product_groups.id
                WHERE
                    (products_groups_xref.product = '" . e($product['id']) . "')
                    AND (products.enabled = '1')
                    AND (product_groups.enabled = '1')
                    $sql_not_in_product_groups
                LIMIT 1")

        ) {
            continue;
        }

        // Update product status.
        db(
            "UPDATE products
            SET
                enabled = '$enabled',
                user = '" . USER_ID . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($product['id']) . "'");

        $items[] = array(
            'id' => $product['id'],
            'type' => 'product',
            'status' => $status);

    }

    $product_group = db_item(
        "SELECT name
        FROM product_groups
        WHERE id = '" . e($id) . "'");

    if ($status == 'enabled') {
        log_activity('product group (' . $product_group['name'] . ') and all items in it were enabled');
    } else {
        log_activity('product group (' . $product_group['name'] . ') and all appropriate items in it were disabled');
    }

    return $items;

}