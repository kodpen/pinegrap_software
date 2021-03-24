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

// prepare expanded product_groups array from cookie
$expanded_product_groups = explode(',', $_COOKIE['software']['product_group_tree']['expanded_product_groups']);

// output xml content
header("Content-type: text/xml");
print
'<?xml version="1.0" encoding="utf-8" ?>
<root>' . get_product_group_tree($_GET['product_group_id']) . '</root>';

function get_product_group_tree($parent_product_group_id)
{
    global $expanded_product_groups;
    
    // initialize arrays that will store data for sorting
    $items = array();
    $item_sort_orders = array();
    $item_names = array();
    
    // get product groups under this parent id
    $query = "SELECT
                id,
                name,
                enabled,
                parent_id,
                sort_order,
                seo_score
             FROM product_groups
             WHERE parent_id = '" . escape($parent_product_group_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        // if the SEO feature is disabled, then set the SEO score to 0, so that it is not displayed
        if ((defined('SEO') == TRUE) && (SEO == FALSE)) {
            $row['seo_score'] = 0;
        }
        
        // add product_group to items array
        $items[] = 
            array (
                'sort_order'=>$row['sort_order'],
                'name'=>h($row['name']),
                'enabled' => $row['enabled'],
                'id'=>$row['id'],
                'parent_id'=>$row['parent_id'],
                'seo_score'=>$row['seo_score'],
                'type'=>'product_group'
            );
        
        // add data to sorting arrays
        $item_sort_orders[] = $row['sort_order'];
        $item_names[] = mb_strtolower($row['name']);
    }
    
    // get products under this parent id
    $query = "SELECT
                products_groups_xref.sort_order,
                products_groups_xref.product_group,
                products.id,
                products.name,
                products.enabled,
                products.short_description,
                products.seo_score
             FROM products_groups_xref
             LEFT JOIN products ON products.id = products_groups_xref.product
             WHERE product_group = '" . escape($parent_product_group_id) . "'
             ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        // if the SEO feature is disabled, then set the SEO score to 0, so that it is not displayed
        if ((defined('SEO') == TRUE) && (SEO == FALSE)) {
            $row['seo_score'] = 0;
        }
        
        // add product to items array
        $items[] = 
            array (
                'sort_order'=>$row['sort_order'],
                'name'=>h($row['name']),
                'enabled' => $row['enabled'],
                'id'=>$row['id'],
                'parent_id'=>$row['product_group'],
                'type'=>'product',
                'short_description'=>h($row['short_description']),
                'seo_score'=>$row['seo_score']
            );
        
        // add data to sorting arrays
        $item_sort_orders[] = $row['sort_order'];
        $item_names[] = mb_strtolower($row['name']);
    }
    
    // sort the items by sort order and then by name
    array_multisort($item_sort_orders, $item_names, $items);
    
    // loop through each item in the items array
    foreach ($items as $key => $value) {
        switch($items[$key]['type']) {
            // if the type is set to product_group
            case 'product_group';
                // add product_group to items_xml
                $items_xml .= 
                    '<product_group>' .
                        '<id>' . $items[$key]['id'] . '</id>' .
                        '<name>' . $items[$key]['name'] . '</name>' .
                        '<enabled>' . $items[$key]['enabled'] . '</enabled>' .
                        '<parent_id>' . $items[$key]['parent_id'] . '</parent_id>' .
                        '<seo_score>' . $items[$key]['seo_score'] . '</seo_score>' . "\n";
                
                // if this product_group is expanded or if all product_groups are being expanded,
                // or if this is the root product_group,
                // use recursion to get other items in this product_group
                if (
                    (in_array($items[$key]['id'], $expanded_product_groups))
                    || ($_GET['expand_all'] == 'true')
                    || ($items[$key]['parent_id'] == 1)
                    ) {
                    $items_xml .= get_product_group_tree($items[$key]['id']);
                }
                
                $items_xml .= '</product_group>';
                break;
                
            // if the type is set to product
            case 'product':
                // add product to items_xml
                $items_xml .= 
                    '<product>' .
                        '<id>' . $items[$key]['id'] . '</id>' .
                        '<parent_id>' . $items[$key]['parent_id'] . '</parent_id>' .
                        '<name>' . $items[$key]['name'] . '</name>' .
                        '<enabled>' . $items[$key]['enabled'] . '</enabled>' .
                        '<short_description>' . $items[$key]['short_description'] . '</short_description>' .
                        '<seo_score>' . $items[$key]['seo_score'] . '</seo_score>' .
                    '</product>';
                break;  
        }
    }
    
    // return the xml
    return $items_xml;
}
?>