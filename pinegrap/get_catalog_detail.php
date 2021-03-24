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

function get_catalog_detail($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];

    $properties = get_page_type_properties($page_id, 'catalog detail');

    $allow_customer_to_add_product_to_order = $properties['allow_customer_to_add_product_to_order'];
    $add_button_label = $properties['add_button_label'];
    $back_button_label = $properties['back_button_label'];

    $layout_type = get_layout_type($page_id);

    if ($_GET['from'] == 'control_panel') {
        return
            '<div class="software_catalog_detail">
                <p class="software_notice">Product details will be displayed here when this page is linked to from a Catalog Page Type.</p>
            </div>';
    }
    
    $form = new liveform('catalog_detail');
    
    $item = array();
    $products = array();
    
    // if a page name has been passed in the query string and if it contains a forward slash, then get the item information
    if (mb_strpos($_GET['page'], '/') !== FALSE) {
        $item = get_catalog_item_from_url();
    }

    // If an item was not found, then output error.
    if (!$item['id']) {
        return error('Sorry, the item could not be found.', 404);
    }

    // If the item is disabled, then output error.
    if (!$item['enabled']) {
        return error('Sorry, the item is not currently available.', 410);
    }

    if ($item['type'] == 'product group') {
        // get product group information
        $query =
            "SELECT
                parent_id,
                image_name,
                short_description,
                full_description,
                keywords,
                details,
                code,
                address_name,
                attributes
            FROM product_groups
            WHERE id = '" . escape($item['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
    } else {
        // get product information
        $query =
            "SELECT
                id,
                image_name,
                short_description,
                full_description,
                keywords,
                details,
                code,
                address_name,
                price,
                selection_type,
                shippable,
                inventory,
                inventory_quantity,
                backorder,
                out_of_stock_message
            FROM products
            WHERE id = '" . escape($item['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    $row = mysqli_fetch_assoc($result);

    $item['parent_id'] = $row['parent_id'];
    $item['image_name'] = $row['image_name'];
    $item['short_description'] = $row['short_description'];
    $item['full_description'] = $row['full_description'];
    $item['keywords'] = $row['keywords'];
    $item['details'] = $row['details'];

    //check if type is product group or product
    if($item['type'] == 'product group'){
    	//check for image list from product_groups_images_xref
    	$item_images = "SELECT product_group,file_name FROM product_groups_images_xref WHERE product_group = '" . $item['id'] . "'";
        $image_results = mysqli_query(db::$con, $item_images) or output_error('Query failed');
	} else {
    	//check for image list from products_images_xref
    	$item_images = "SELECT product,file_name FROM products_images_xref WHERE product = '" . $item['id'] . "'";
        $image_results = mysqli_query(db::$con, $item_images) or output_error('Query failed');
    }
    //if code has ^^image_loop_start^^ and ^^image_url^^ and ^^image_loop_end^^. with these we can make an ease loop
    if( (strpos($row['code'], '^^image_url^^') !== false)&&
        (strpos($row['code'], '^^image_loop_start^^') !== false)&&
        (strpos($row['code'], '^^image_loop_end^^') !== false)
    ){        
        $code_header_position = strpos($row['code'], '^^image_loop_start^');//number
        $code_content_position = strpos($row['code'], '^^image_url^^');
        $code_footer_position = strpos($row['code'], '^^image_loop_end^');//number
        $code_header = substr( $row['code'], 0 ,strpos($row['code'], '^^image_loop_start^') );
        $code_content_raw = substr( $row['code'], (strpos($row['code'], '^^image_loop_start^') + 20) , (strpos($row['code'], '^^image_loop_end^') - strpos($row['code'], '^^image_loop_start^')  - 20) );
        $code_footer = substr($row['code'], strpos($row['code'], '^^image_loop_end^') + 18 );
        $code_image_alt = false;
        if(strpos($row['code'], '^^image_alt^^') !== false){  
            $code_image_alt = true;
        }

        //if product image xref or product group  xref exist. this mean this selected multiple product image
        if(mysqli_num_rows($image_results) != 0){
            //if there is image alt tag
            if($code_image_alt !== false){  
                $code_content = str_replace("^^image_url^^", PATH . encode_url_path($item['image_name']) , str_replace("^^image_alt^^", $item['short_description'] ,$code_content_raw) );
            //if there is no image alt tag
            }else{
                $code_content = str_replace("^^image_url^^", PATH . encode_url_path($item['image_name']) , $code_content_raw);

            }
            while ($image = mysqli_fetch_assoc($image_results)){
                //if there is image alt tag
                if($code_image_alt !== false){  
                    $code_content .= str_replace("^^image_url^^", PATH . encode_url_path($image['file_name']) , str_replace("^^image_alt^^", $item['short_description'] ,$code_content_raw) );
                //if there is no image alt tag
                }else{
                    $code_content .= str_replace("^^image_url^^", PATH . encode_url_path($image['file_name']) , $code_content_raw);
                }
            }
            $item['code'] = $code_header.$code_content.$code_footer;
        }else{
            //else if less an image selected and only one image selected, but there is code for action we output single image
            if($item['image_name']){
                //if there is image alt tag
                if($code_image_alt !== false){  
                    $code_single_content = str_replace("^^image_url^^", PATH . encode_url_path($item['image_name']) , str_replace("^^image_alt^^", $item['short_description'] ,$code_content_raw) );
                //if there is no image alt tag
                }else{
                    $code_single_content = str_replace("^^image_url^^", PATH . encode_url_path($item['image_name']) , $code_content_raw);
                }
                $item['code'] = $code_header.$code_single_content.$code_footer;
            }else{
                $item['code'] ='';
            }
        }

    }else{
        // else there is no code spacial elements so we output direct code.
        $item['code'] = $row['code'];
    }



    
    $item['address_name'] = $row['address_name'];
    $item['attributes'] = $row['attributes'];
    
    // if item is a product, then set product properties
    if ($item['type'] == 'product') {
        $item['price'] = $row['price'];
        $item['selection_type'] = $row['selection_type'];
        $item['inventory'] = $row['inventory'];
        $item['inventory_quantity'] = $row['inventory_quantity'];
        $item['backorder'] = $row['backorder'];
        $item['out_of_stock_message'] = $row['out_of_stock_message'];
        
        $products[] = $row;
        
        // if the inventory is enabled for the product,
        // and the product is out of stock,
        // and there is an out of stock message
        // then append out of stock message to full description
        if (
            ($item['inventory'] == 1)
            && ($item['inventory_quantity'] == 0)
            && ($item['out_of_stock_message'] != '')
            && ($item['out_of_stock_message'] != '<p></p>')
        ) {
            $item['full_description'] .= ' ' . $item['out_of_stock_message'];
        }
    }

    // If there is a full description or if attributes are enabled,
    // and edit mode is enabled, then add edit button for images.
    if (
        (($item['full_description'] != '') or $item['attributes'])
        and $editable
    ) {
        $item['full_description'] = add_edit_button_for_images(str_replace(' ' , '_', $item['type']), $item['id'], $item['full_description'], 'full_description');
    }

    $catalog_page = db_item(
        "SELECT
            page.page_id AS id,
            page.page_name AS name,
            catalog_pages.product_group_id
        FROM catalog_pages
        LEFT JOIN page ON catalog_pages.page_id = page.page_id
        WHERE catalog_pages.catalog_detail_page_id = '" . e($page_id) . "'
        LIMIT 1");

    // If there is no product group selected for the catalog page,
    // then get top-level product group.
    if (!$catalog_page['product_group_id']) {
        $catalog_page['product_group_id'] = db_value("SELECT id FROM product_groups WHERE parent_id = '0'");
    }

    $keywords = array();

    // If there are keywords, then prepare them.
    if ($item['keywords'] != '') {
        // Split keywords by comma.
        $keywords = explode(',', $item['keywords']);

        // Remove white-space around all keywords.
        $keywords = array_map('trim', $keywords);

        // Remove blank keywords.
        $keywords = array_filter($keywords);

        // If there is at least one keyword, then prepare them.
        if ($keywords) {
            if ($catalog_page['id']) {
                $url_start = PATH . encode_url_path($catalog_page['name']) . '?' . $catalog_page['id'] . '_query=';
                $url_end = '&previous_url_id=' . urlencode(generate_url_id());
            }

            foreach ($keywords as $key => $value) {
                $keyword = array();
                $keyword['keyword'] = $value;

                if ($catalog_page['id']) {
                    $keyword['url'] = $url_start . urlencode($keyword['keyword']). $url_end;
                } else {
                    $keyword['url'] = '';
                }

                $keywords[$key] = $keyword;
            }
        }
    }

    // Get prices for products that have been discounted by offers,
    // so we can show the discounted price.
    $discounted_product_prices = get_discounted_product_prices();

    if ($item['type'] == 'product group') {
        $item['price_range'] = get_price_range($item['id'], $discounted_product_prices);

        // This product group is on a catalog detail screen,
        // so we know it has a select display type.
        $item['display_type'] = 'select';

        // Get all products that are in product group.
        $products = db_items(
            "SELECT
                products.id,
                products.name,
                products.short_description,
                products.image_name,
                products.price,
                products.selection_type,
                products.shippable,
                products.inventory,
                products.inventory_quantity,
                products.backorder,
                products.out_of_stock_message,
                products.timestamp AS last_modified_timestamp
            FROM products_groups_xref
            LEFT JOIN products ON products.id = products_groups_xref.product
            WHERE
                (products_groups_xref.product_group = '" . escape($item['id']) . "')
                AND (products.enabled = '1')
            ORDER BY products_groups_xref.sort_order, products.name");
    }

    $non_donation_products_exist = false;
    $shippable_products = false;

    // Loop through the products to prepare info.
    foreach ($products as $key => $product) {

        if (isset($discounted_product_prices[$product['id']])) {
            $product['discounted'] = true;
            $product['discounted_price'] = $discounted_product_prices[$product['id']];
        } else {
            $product['discounted'] = false;
        }

        // if this product is available then continue to take note of what type of product it is
        if (
            ($product['inventory'] == 0)
            || ($product['inventory_quantity'] > 0)
            || ($product['backorder'] == 1)
        ) {
            // if product does not have a donation selection type, then remember that
            if ($product['selection_type'] != 'donation') {
                $non_donation_products_exist = true;
            }
            
            // if product is shippable remember that
            if ($product['shippable'] == 1) {
                $shippable_products = true;
            }
        }

        $products[$key] = $product;

    }

    // Get the price or price range for output.
    $price_info = get_price_info(array(
        'item' => $item,
        'discounted_product_prices' => $discounted_product_prices));

    // Determine if there are any available products that can be ordered.

    $available_products_exist = false;

    // If there are products to be displayed, and this page allows products to be ordered,
    // then determine if any are available to be ordered.
    if ($products and $allow_customer_to_add_product_to_order) {
        // if this item is a product group, then loop through through products in order to determine if there are available products
        if ($item['type'] == 'product group') {
            foreach ($products as $product) {
                // if inventory is disabled for the product,
                // or the inventory quantity is greater than 0,
                // or the product is allowed to be backordered,
                // then the product is available so remember that
                if (
                    ($product['inventory'] == 0)
                    || ($product['inventory_quantity'] > 0)
                    || ($product['backorder'] == 1)
                ) {
                    $available_products_exist = true;
                    break;
                }
            }
            
        // else this item is a product, so determine if it is available
        } else {
            // if inventory is disabled for the product,
            // or the inventory quantity is greater than 0,
            // or the product is allowed to be backordered,
            // then the product is available so remember that
            if (
                ($item['inventory'] == 0)
                || ($item['inventory_quantity'] > 0)
                || ($item['backorder'] == 1)
            ) {
                $available_products_exist = true;
            }
        }
    }

    // If there are details or if attributes are enabled,
    // and edit mode is enabled, then add edit button for images.
    if (
        (($item['details'] != '') or $item['attributes'])
        and $editable
    ) {
        $item['details'] = add_edit_button_for_images(str_replace(' ' , '_', $item['type']), $item['id'], $item['details'], 'details');
    }

    if ($layout_type == 'system') {
    
        $output_full_description = '';
        
        // If there is a full description or if attributes are enabled, then output full description.
        if (($item['full_description'] != '') || ($item['attributes'])) {
            $output_full_description = '<div class="full_description">' . $item['full_description'] . '</div>';
        }
        
        $output_keywords = '';

        if ($keywords) {
            $output_keywords .= '<div class="keywords">Keywords: ';
            
            // loop through keyword parts, in order to prepare output for keyword links
            foreach ($keywords as $key => $keyword) {
                // if a link has already been added, then add a comma and a space
                if ($key) {
                    $output_keywords .= ', ';
                }

                if ($keyword['url']) {
                    $output_keywords .= '<a href="' . h($keyword['url']) . '">';
                }
                
                $output_keywords .= h($keyword['keyword']);

                if ($keyword['url']) {
                    $output_keywords .= '</a>';
                }
            }

            $output_keywords .= '</div>';
        }

        $output_price = '';

        if ($price_info != '') {
            $output_price = '<div class="price">' . $price_info . '</div>';
        }
        
        $output_form = '';
        $output_init_product_attributes = '';
        
        // if this item is a product group and there is at least one product in this product group,
        // or if this item is a product,
        // then prepare to output form so the customer can add a product to the order
        if ((($item['type'] == 'product group') && (count($products) > 0)) || ($item['type'] == 'product')) {
            $output_form_start = '';
            $output_form_end = '';
            $output_hidden_page_id_field = '';
            $output_hidden_product_id_field = '';
            $output_hidden_require_cookies_field = '';
            
            // if there are available products, then prepare various data
            if ($available_products_exist == TRUE) {
                $output_form_start =
                    '<form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/catalog_detail.php" method="post" style="margin: 0em">
                        ' . get_token_field();
                $output_form_end = '</form>';
                $output_hidden_page_id_field = $form->output_field(array('type'=>'hidden', 'name'=>'page_id', 'value'=>$page_id));
                $output_hidden_current_url_field = $form->output_field(array('type'=>'hidden', 'name'=>'current_url', 'value' => REQUEST_URL));
                
                // if item type is product or there is only one product, then prepare hidden product id field
                if (($item['type'] == 'product') || (count($products) == 1)) {
                    $product = $products[0];
                    
                    $output_hidden_product_id_field = $form->output_field(array('type'=>'hidden', 'name'=>'product_id', 'value'=>$product['id']));
                }
                
                $output_hidden_require_cookies_field = $form->output_field(array('type'=>'hidden', 'name'=>'require_cookies', 'value'=>'true'));
            }
            
            $output_product_attribute_table = '';
            $output_product_row = '';
            
            // if this item is a product group then output product row
            if ($item['type'] == 'product group') {
                if ($item['attributes']) {
                    $sql_products = "";

                    foreach ($products as $product) {
                        if ($sql_products != '') {
                            $sql_products .= " OR ";
                        }

                        $sql_products .= "(product_id = '" . $product['id'] . "')";
                    }

                    // Get all attributes for products in this product group.
                    $product_attributes = db_items(
                        "SELECT
                            product_id,
                            attribute_id AS id,
                            option_id,
                            product_attributes.name,
                            product_attributes.label
                        FROM products_attributes_xref
                        LEFT JOIN product_attributes ON products_attributes_xref.attribute_id = product_attributes.id
                        WHERE $sql_products");

                    // If at least one attribute was found, then continue to prepare
                    // attributes and order them.
                    if ($product_attributes) {
                        $output_hidden_product_id_field = $form->output_field(array(
                            'type' => 'hidden',
                            'name' => 'product_id',
                            'value' => '',
                            'class' => 'product_id'));

                        $products_for_js = array();

                        // Prepare new products array that we will pass to js function.
                        foreach ($products as $product) {
                            $product_for_js = array();

                            // If the user is in edit mode, then store timestamp in the array,
                            // so we can sort by the timestamp later, and also store date & time
                            // in a value, so we don't have to do that in the JS.
                            // The timestamp has to be stored as the first item in the array,
                            // so that the sorting later will work.  This info is used by the attribute helper.
                            if ($editable == true) {
                                $product_for_js['last_modified_timestamp'] = $product['last_modified_timestamp'];
                                $product_for_js['relative_time'] = get_relative_time(array('timestamp' => $product['last_modified_timestamp']));
                            }

                            $product_for_js['id'] = $product['id'];
                            $product_for_js['name'] = $product['name'];
                            $product_for_js['short_description'] = $product['short_description'];
                            $product_for_js['image_name'] = $product['image_name'];
                            $product_for_js['price'] = $product['price'];
                            $product_for_js['selection_type'] = $product['selection_type'];
                            $product_for_js['attributes'] = array();

                            $products_for_js[$product['id']] = $product_for_js;
                        }

                        // Loop through the product attributes, in order to add attributes
                        // to array that we will pass to js.
                        foreach ($product_attributes as $attribute) {
                            $attribute_for_js = array();
                            $attribute_for_js['id'] = $attribute['id'];
                            $attribute_for_js['option_id'] = $attribute['option_id'];

                            $products_for_js[$attribute['product_id']]['attributes'][] = $attribute_for_js;
                        }

                        // If the user is in edit mode then the attribute helper will be shown,
                        // so sort the products so that the most recently modified will be shown at the top.
                        if ($editable == true) {
                            rsort($products_for_js);
                        }

                        // Get sort orders for attributes for this product group.
                        $product_group_attributes = db_items(
                            "SELECT
                                attribute_id AS id,
                                default_option_id,
                                sort_order
                            FROM product_groups_attributes_xref
                            WHERE product_group_id = '" . e($item['id']) . "'", 'id');

                        // Get all options for products in this product group,
                        // so that we know whether we should include an option
                        // for an attribute or not.
                        $product_options = db_values(
                            "SELECT DISTINCT(option_id)
                            FROM products_attributes_xref
                            WHERE $sql_products");

                        $attributes = array();

                        // Create array that we will use to determine if we have already
                        // dealt with a certain attribute.
                        $added_attributes = array();

                        // Loop through the raw product attributes array in order to prepare
                        // new attributes array that we can use to sort the attributes.
                        foreach ($product_attributes as $attribute) {
                            // If this attribute has already been dealt with,
                            // then continue to next attribute.
                            if (in_array($attribute['id'], $added_attributes)) {
                                continue;
                            }

                            // If there is a sort order for this attribute, then set it.
                            if ($product_group_attributes[$attribute['id']]) {
                                $sort_order = $product_group_attributes[$attribute['id']]['sort_order'];

                            // Otherwise just set the sort order to a really high number
                            // so attributes appears at the bottom.
                            } else {
                                $sort_order = 9999999;
                            }

                            // Get all the options for this attribute, so we can include them in array.
                            $options = db_items(
                                "SELECT
                                    id,
                                    label,
                                    no_value
                                FROM product_attribute_options
                                WHERE product_attribute_id = '" . $attribute['id'] . "'
                                ORDER BY sort_order");

                            // Loop through the options in order to remove ones that are
                            // not in use by a product in this product group.
                            foreach ($options as $key => $option) {
                                // Assume that the option is not valid, until we find out otherwise.
                                // An option is valid if it matches at least one product.
                                $option_valid = false;

                                // If there is product that has this option,
                                // or if this is a "no thanks" option,
                                // and there is at least one product that would match
                                // this "no thanks" option, then the option is valid.
                                if (
                                    (in_array($option['id'], $product_options) == true)
                                    ||
                                    (
                                        ($option['no_value'] == 1)
                                        &&
                                        (
                                            db_value(
                                                "SELECT products.id
                                                FROM products_groups_xref
                                                LEFT JOIN products ON products_groups_xref.product = products.id
                                                LEFT JOIN products_attributes_xref
                                                    ON
                                                        (products_groups_xref.product = products_attributes_xref.product_id)
                                                        AND (products_attributes_xref.attribute_id = '" . e($attribute['id']) . "')
                                                WHERE
                                                    (products_groups_xref.product_group = '" . e($item['id']) . "')
                                                    AND (products.enabled = '1')
                                                    AND (products_attributes_xref.product_id IS NULL)
                                                LIMIT 1")
                                        )
                                    )
                                ) {
                                    $option_valid = true;
                                }

                                // If the option is not valid, then remove it.
                                if ($option_valid == false) {
                                    unset($options[$key]);
                                }
                            }

                            $unselected = false;

                            // If there is a field in the session for this attribute,
                            // and the value is blank, then remember that this attribute has been
                            // specifically unselected, so that we don't select an option by default,
                            // if there is just one option.
                            if (
                                ($form->field_in_session('attribute_' . $attribute['id']) == true)
                                && ($form->get_field_value('attribute_' . $attribute['id']) == '')
                            ) {
                                $unselected = true;
                            }

                            // Create a new clean attribute array for this attribute
                            // that we can add to the attributes array.
                            // We use array_values() below so that options is treated
                            // as an array instead of an object in js.
                            $attribute = array(
                                'sort_order' => $sort_order,
                                'id' => $attribute['id'],
                                'label' => $attribute['label'],
                                'enabled' => true,
                                'default_option_id' => $product_group_attributes[$attribute['id']]['default_option_id'],
                                'unselected' => $unselected,
                                'options' => array_values($options));

                            $attributes[] = $attribute;

                            $added_attributes[] = $attribute['id'];
                        }

                        sort($attributes);

                        $output_attribute_rows = '';

                        foreach ($attributes as $attribute) {
                            $pick_list_options = array();

                            $pick_list_options[''] = '';

                            foreach ($attribute['options'] as $option) {
                                $pick_list_options[h($option['label'])] = $option['id'];
                            }

                            // If there is no field in the session for this attribute,
                            // then check if we should select an option by default.
                            if ($form->field_in_session('attribute_' . $attribute['id']) == false) {
                                // If there is only one option, then select it by default.
                                if (count($attribute['options']) == 1) {
                                    $form->assign_field_value('attribute_' . $attribute['id'], $attribute['options'][0]['id']);

                                // Otherwise if there is a default option for this attribute,
                                // then select it by default.
                                } else if ($attribute['default_option_id']) {
                                    $form->assign_field_value('attribute_' . $attribute['id'], $attribute['default_option_id']);
                                }
                            }

                            $output_attribute_rows .=
                                '<tr class="attribute_' . $attribute['id'] . ' attribute_row">
                                    <td><strong>' . h($attribute['label']) . '</strong></td>
                                    <td>
                                        ' . $form->output_field(array(
                                            'type' => 'select',
                                            'name' => 'attribute_' . $attribute['id'],
                                            'options' => $pick_list_options,
                                            'class' => 'software_select')) . '
                                        <span class="clear"> <a href="javascript:void(0)" class="software_button_small_secondary remove_button">X</a></span>
                                    </td>
                                </tr>';
                        }

                        // The spacer row is added so that the columns in the attribute table
                        // will align with the product selection table below it, assuming all attribute
                        // labels are shorter than "or add name:".
                        $output_product_attribute_table =
                            '<table class="product_attributes">
                                <tr class="attribute_spacer" style="visibility: collapse"><td>or add name:</td><td></td></tr>
                                ' . $output_attribute_rows . '
                            </table>';

                        // We use array_values() below so that the associative products
                        // is converted into an array in JSON instead of an object,
                        // because it is easier to deal with as an array in js.
                        $output_init_product_attributes =
                            '<script>
                                software.init_product_attributes({
                                    attributes: ' . encode_json($attributes) . ',
                                    products: ' . encode_json(array_values($products_for_js)) . ',
                                    default_image_name: \'' . escape_javascript($item['image_name']) . '\',
                                    default_short_description: \'' . escape_javascript($item['short_description']) . '\',
                                    default_full_description: \'' . escape_javascript($item['full_description']) . '\',
                                    default_details: \'' . escape_javascript($item['details']) . '\',
                                    default_code: \'' . escape_javascript($item['code']) . '\',
                                    discounted_product_prices: ' . encode_json($discounted_product_prices) . ',
                                    visitor_currency_symbol: \'' . escape_javascript(VISITOR_CURRENCY_SYMBOL) . '\',
                                    visitor_currency_exchange_rate: \'' . escape_javascript(VISITOR_CURRENCY_EXCHANGE_RATE) . '\',
                                    visitor_currency_code_for_output: \'' . escape_javascript(VISITOR_CURRENCY_CODE_FOR_OUTPUT) . '\'
                                });
                            </script>';
                    }
                }

                // If attributes are not being outputted, then output product row.
                if ($output_product_attribute_table == '') {
                    // if there are available products or there is only one product, then prepare singular label
                    if (($available_products_exist == TRUE) || (count($products) == 1)) {
                        $output_item_label = 'Item';
                        
                    // else prepare plural label
                    } else {
                        $output_item_label = 'Items';
                    }
                    
                    $output_products = '';
                    
                    // loop through products in order to prepare to output products in list or pick list
                    foreach ($products as $product) {
                        // if there are no available products or there is only one product to output, then prepare to output product in list
                        if (($available_products_exist == FALSE) || (count($products) == 1)) {
                            // if a product has already been added, then add a line break
                            if ($output_products != '') {
                                $output_products .= '<br />' . "\n";
                            }
                            
                            $output_product_description = h($product['short_description']) . ' (' . prepare_price_for_output($product['price'], $product['discounted'], $product['discounted_price'], 'html') . ')';
                            
                            // if inventory is enabled for the product,
                            // and the product is out of stock,
                            // and there is an out of stock message
                            // then append out of stock message to label
                            if (
                                ($product['inventory'] == 1)
                                && ($product['inventory_quantity'] == 0)
                                && ($product['backorder'] != 1)
                                && ($product['out_of_stock_message'] != '')
                                && ($product['out_of_stock_message'] != '<p></p>')
                            ) {
                                $out_of_stock_message = trim(convert_html_to_text($product['out_of_stock_message']));
                                
                                // if the message is longer than 50 characters, then truncate it
                                if (mb_strlen($out_of_stock_message) > 50) {
                                    $out_of_stock_message = mb_substr($out_of_stock_message, 0, 50) . '...';
                                }
                                
                                $output_product_description .= ' - ' . h($out_of_stock_message);
                            }
                            
                            $output_products .= $output_product_description;
                            
                        // else the customer is allowed to add a product to the order and there is more than one product, so prepare to output product in pick list
                        } else {
                            $output_product_description = h($product['short_description']) . ' (' . prepare_price_for_output($product['price'], $product['discounted'], $product['discounted_price'], 'plain_text') . ')';
                            
                            // if inventory is enabled for the product,
                            // and the product is out of stock,
                            // and it cannot be backordered,
                            // and there is an out of stock message,
                            // then append out of stock message to label
                            if (
                                ($product['inventory'] == 1)
                                && ($product['inventory_quantity'] == 0)
                                && ($product['backorder'] != 1)
                                && ($product['out_of_stock_message'] != '')
                                && ($product['out_of_stock_message'] != '<p></p>')
                            ) {
                                $out_of_stock_message = trim(convert_html_to_text($product['out_of_stock_message']));
                                
                                // if the message is longer than 50 characters, then truncate it
                                if (mb_strlen($out_of_stock_message) > 50) {
                                    $out_of_stock_message = mb_substr($out_of_stock_message, 0, 50) . '...';
                                }
                                
                                $output_product_description .= ' - ' . h($out_of_stock_message);
                            }

                            // If the device type is mobile then prepare radio button for product,
                            // because we will output a set of radio buttons for mobile instead of a pick list.
                            if ($device_type == 'mobile') {
                                $checked = '';

                                // If this is the first product that will be listed, then select radio button by default.
                                if ($output_products_for_mobile == '') {
                                    $checked = 'checked';
                                }

                                $output_products_for_mobile .= $form->output_field(array('type'=>'radio', 'name'=>'product_id', 'id'=>'product_id_' . $product['id'], 'value'=>$product['id'], 'checked'=>$checked, 'class'=>'software_input_radio')) . '<label for="product_id_' . $product['id'] . '"> ' . $output_product_description . '</label><br />';

                            // Otherwise the device type is desktop so prepare option for pick list.
                            } else {
                                $product_options[$output_product_description] = $product['id'];
                            }
                        }
                    }
                    
                    // if output products is empty, then we need to output pick list (or radio buttons if mobile)
                    if ($output_products == '') {
                        if ($device_type == 'mobile') {
                            $output_products = $output_products_for_mobile;
                        } else {
                            $output_products = $form->output_field(array('type'=>'select', 'name'=>'product_id', 'options'=>$product_options, 'class'=>'software_select'));
                        }
                    }
                    
                    $output_product_row =
                        '<tr id="product_row">
                            <td><strong>' . $output_item_label . ':</strong></td>
                            <td>' . $output_products . '</td>
                        </tr>';
                }
            }
                
            $output_recipient_rows = '';
            $output_quantity_row = '';
            $output_add_button = '';
            
            // if there are available products, then prepare recipient rows, quantity row, and add button
            if ($available_products_exist == TRUE) {
                
                $output_recipient_rows = '';
                
                // if shipping is on and recipient mode is multi-recipient and there are shippable products on this catalog detail screen, then allow customer to select a recipient
                if ((ECOMMERCE_SHIPPING == true) && (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') && ($shippable_products == true)) {
                    $output_recipient_rows =
                        '<tr id="ship_to_row">
                            <td><strong>Ship to:</strong></td>
                            <td>' . $form->output_field(array('type'=>'select', 'name'=>'ship_to', 'options'=>get_recipient_options(), 'class'=>'software_select')) . '</td>
                        </tr>
                        <tr id="add_name_row">
                            <td>or add name:</td>
                            <td>' . $form->output_field(array('type'=>'text', 'name'=>'add_name', 'maxlength'=>'50', 'size'=>'12', 'class'=>'software_input_text  mobile_text_width')) . ' &nbsp;(e.g. "Tom")</td>
                        </tr>';
                }

                $output_quantity_row = '';
                
                // if non donation products exist, then output quantity row
                if ($non_donation_products_exist == true) {
                    $output_quantity_row =
                        '<tr id="quantity_row">
                            <td><strong>Qty:</strong></td>
                            <td class="mobile_left">' . $form->output_field(array('type'=>'number', 'name'=>'quantity', 'size'=>'3', 'maxlength'=>'9', 'min'=>'1', 'max'=>'999999999', 'value'=>'1', 'class'=>'software_input_text')) . '</td>
                        </tr>';
                }
                
                $output_add_button_label = '';
                
                // if an add button label was entered for the page, then use that
                if ($add_button_label) {
                    $output_add_button_label = h($add_button_label);
                
                // else an add button label could not be found, so use a default label
                } else {
                    $output_add_button_label = 'Continue';
                }
                
                $output_add_button = '<input type="submit" name="submit" value="' . $output_add_button_label . '" class="software_input_submit_primary add_button" />';
            }
            
            $output_back_button = '';
            
            // if there is a previous url set, then add back button
            if ((isset($_GET['previous_url_id']) == true) && (isset($_SESSION['software']['urls'][$_GET['previous_url_id']]) == true)) {
                $output_spacing = '';
                
                // if there is an add button, then output spacing
                if ($output_add_button != '') {
                    $output_spacing = '&nbsp;&nbsp;&nbsp;';
                }
                
                // if back button label is blank, then set to "Back"
                if ($back_button_label == '') {
                    $back_button_label = 'Back';
                }
                
                $output_back_button = $output_spacing . '<a href="' . h($_SESSION['software']['urls'][$_GET['previous_url_id']]) . '" class="software_button_secondary back_button">' . h($back_button_label) . '</a>';
            }
            
            $output_button_container_start = '';
            $output_button_container_end = '';
            
            // if there is an add button or back button, then prepare to output button container start and end tags
            if (($output_add_button != '') || ($output_back_button != '')) {
                $output_button_container_start = '<div>';
                $output_button_container_end = '</div>';
            }
            
            $output_form =
                $output_form_start . '
                    ' . $output_hidden_page_id_field . '
                    ' . $output_hidden_current_url_field . '
                    ' . $output_hidden_product_id_field . '
                    ' . $output_hidden_require_cookies_field . '
                    ' . $output_product_attribute_table . '
                    <table class="product_selection" style="margin-bottom: 1em">
                        ' . $output_product_row . '
                        ' . $output_recipient_rows . '
                        ' . $output_quantity_row . '
                    </table>
                    ' . $output_button_container_start . $output_add_button . $output_back_button . $output_button_container_end . '
                ' . $output_form_end;
        }

        $output_details = '';
        
        // If there are details or if attributes are enabled, then output full description.
        if (($item['details'] != '') || ($item['attributes'])) {
            $output_details = '<div style="clear: both; padding-top: 1em" class="details">' . $item['details'] . '</div>';
        }
        
        $output_code = '';
        
        // if there is code, then output code
        if (($item['code'] != '') or $item['attributes']) {
            $output_code = '<div class="code">' . $item['code'] . '</div>';
        }
        
        $output_back_button = '';
        
        // if there is no form and there is a previous url set, then the back button will not be outputted in the form, so prepare to output back button
        if (($output_form == '') && (isset($_GET['previous_url_id']) == true) && (isset($_SESSION['software']['urls'][$_GET['previous_url_id']]) == true)) {
            // if back button label is blank, then set to "Back"
            if ($back_button_label == '') {
                $back_button_label = 'Back';
            }
            
            $output_back_button = '<div style="margin-top: 1em"><a href="' . h($_SESSION['software']['urls'][$_GET['previous_url_id']]) . '" class="software_button_secondary back_button">' . h($back_button_label) . '</a></div>';
        }

        // It is important that init product attributes be outputted after the details below
        // because the init code updates the details content, and we don't wait for the 
        // whole page to be ready in the code, for speed reasons.
        
        $output =
            $form->output_errors() . '
            ' . $form->output_notices() . '
            <div>
                ' . $output_full_description . '
                ' . $output_keywords . '
                ' . $output_price . '
                <div class="form" style="float: left">
                    ' . $output_form . '
                </div>
                <div style="clear: both"></div>
                ' . $output_details . '
                ' . $output_init_product_attributes . '
                ' . $output_code . '
                ' . $output_back_button . '
            </div>
            ' . get_update_currency_form();
        
        $form->remove();
        
        return
            '<div class="software_catalog_detail">
                ' . $output . '
            </div>';

    // Otherwise the layout is custom.
    } else {

        $image_url = '';

        if ($item['image_name'] != '') {
            $image_url = PATH . encode_url_path($item['image_name']);
        }

        $form_attributes = '';
        $attributes = array();
        $footer_system = '';
        $product_row = false;
        $product_pick_list = false;
        $system = '';
        $product_id_field = false;
        $recipient = false;
        $quantity = false;

        // If there is at least one product to display, then prepare info.
        if ($products) {

            if ($available_products_exist) {
                $form_attributes =
                    'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/catalog_detail.php" ' .
                    'method="post"';

                $system =
                    get_token_field() . '
                    <input type="hidden" name="page_id" value="' . h($page_id) . '">
                    <input type="hidden" name="current_url" value="' . h(REQUEST_URL) . '">
                    <input type="hidden" name="require_cookies" value="true">';

                // If there is only one product then output hidden field for the product id.
                if (count($products) == 1) {
                    $system .= '<input type="hidden" name="product_id" value="' . h($products[0]['id']) . '" class="product_id">';

                    // Remember that we have already added the product id field,
                    // so that we don't add it further below in the attribute logic.
                    $product_id_field = true;
                }
            }

            // If this item is a product group then prepare descriptions for products
            // for the product row or pick list.
            if ($item['type'] == 'product group') {

                if ($item['attributes']) {
                    $sql_products = "";

                    foreach ($products as $product) {
                        if ($sql_products != '') {
                            $sql_products .= " OR ";
                        }

                        $sql_products .= "(product_id = '" . $product['id'] . "')";
                    }

                    // Get all attributes for products in this product group.
                    $product_attributes = db_items(
                        "SELECT
                            product_id,
                            attribute_id AS id,
                            option_id,
                            product_attributes.name,
                            product_attributes.label
                        FROM products_attributes_xref
                        LEFT JOIN product_attributes ON products_attributes_xref.attribute_id = product_attributes.id
                        WHERE $sql_products");

                    // If at least one attribute was found, then continue to prepare
                    // attributes and order them.
                    if ($product_attributes) {
                        // If the product id field has not already been added, then add it.
                        if (!$product_id_field) {
                            $system .= '<input type="hidden" name="product_id" class="product_id">';
                        }

                        $products_for_js = array();

                        // Prepare new products array that we will pass to js function.
                        foreach ($products as $product) {
                            $product_for_js = array();

                            // If the user is in edit mode, then store timestamp in the array,
                            // so we can sort by the timestamp later, and also store date & time
                            // in a value, so we don't have to do that in the JS.
                            // The timestamp has to be stored as the first item in the array,
                            // so that the sorting later will work.  This info is used by the attribute helper.
                            if ($editable == true) {
                                $product_for_js['last_modified_timestamp'] = $product['last_modified_timestamp'];
                                $product_for_js['relative_time'] = get_relative_time(array('timestamp' => $product['last_modified_timestamp']));
                            }

                            $product_for_js['id'] = $product['id'];
                            $product_for_js['name'] = $product['name'];
                            $product_for_js['short_description'] = $product['short_description'];
                            $product_for_js['image_name'] = $product['image_name'];
                            $product_for_js['price'] = $product['price'];
                            $product_for_js['selection_type'] = $product['selection_type'];
                            $product_for_js['attributes'] = array();

                            $products_for_js[$product['id']] = $product_for_js;
                        }

                        // Loop through the product attributes, in order to add attributes
                        // to array that we will pass to js.
                        foreach ($product_attributes as $attribute) {
                            $attribute_for_js = array();
                            $attribute_for_js['id'] = $attribute['id'];
                            $attribute_for_js['option_id'] = $attribute['option_id'];

                            $products_for_js[$attribute['product_id']]['attributes'][] = $attribute_for_js;
                        }

                        // If the user is in edit mode then the attribute helper will be shown,
                        // so sort the products so that the most recently modified will be shown at the top.
                        if ($editable == true) {
                            rsort($products_for_js);
                        }

                        // Get sort orders for attributes for this product group.
                        $product_group_attributes = db_items(
                            "SELECT
                                attribute_id AS id,
                                default_option_id,
                                sort_order
                            FROM product_groups_attributes_xref
                            WHERE product_group_id = '" . e($item['id']) . "'", 'id');

                        // Get all options for products in this product group,
                        // so that we know whether we should include an option
                        // for an attribute or not.
                        $product_options = db_values(
                            "SELECT DISTINCT(option_id)
                            FROM products_attributes_xref
                            WHERE $sql_products");

                        $attributes = array();

                        // Create array that we will use to determine if we have already
                        // dealt with a certain attribute.
                        $added_attributes = array();

                        // Loop through the raw product attributes array in order to prepare
                        // new attributes array that we can use to sort the attributes.
                        foreach ($product_attributes as $attribute) {
                            // If this attribute has already been dealt with,
                            // then continue to next attribute.
                            if (in_array($attribute['id'], $added_attributes)) {
                                continue;
                            }

                            // If there is a sort order for this attribute, then set it.
                            if ($product_group_attributes[$attribute['id']]) {
                                $sort_order = $product_group_attributes[$attribute['id']]['sort_order'];

                            // Otherwise just set the sort order to a really high number
                            // so attributes appears at the bottom.
                            } else {
                                $sort_order = 9999999;
                            }

                            // Get all the options for this attribute, so we can include them in array.
                            $options = db_items(
                                "SELECT
                                    id,
                                    label,
                                    no_value
                                FROM product_attribute_options
                                WHERE product_attribute_id = '" . $attribute['id'] . "'
                                ORDER BY sort_order");

                            // Loop through the options in order to remove ones that are
                            // not in use by a product in this product group.
                            foreach ($options as $key => $option) {
                                // Assume that the option is not valid, until we find out otherwise.
                                // An option is valid if it matches at least one product.
                                $option_valid = false;

                                // If there is product that has this option,
                                // or if this is a "no thanks" option,
                                // and there is at least one product that would match
                                // this "no thanks" option, then the option is valid.
                                if (
                                    (in_array($option['id'], $product_options) == true)
                                    ||
                                    (
                                        ($option['no_value'] == 1)
                                        &&
                                        (
                                            db_value(
                                                "SELECT products.id
                                                FROM products_groups_xref
                                                LEFT JOIN products ON products_groups_xref.product = products.id
                                                LEFT JOIN products_attributes_xref
                                                    ON
                                                        (products_groups_xref.product = products_attributes_xref.product_id)
                                                        AND (products_attributes_xref.attribute_id = '" . e($attribute['id']) . "')
                                                WHERE
                                                    (products_groups_xref.product_group = '" . e($item['id']) . "')
                                                    AND (products.enabled = '1')
                                                    AND (products_attributes_xref.product_id IS NULL)
                                                LIMIT 1")
                                        )
                                    )
                                ) {
                                    $option_valid = true;
                                }

                                // If the option is not valid, then remove it.
                                if ($option_valid == false) {
                                    unset($options[$key]);
                                }
                            }

                            $unselected = false;

                            // If there is a field in the session for this attribute,
                            // and the value is blank, then remember that this attribute has been
                            // specifically unselected, so that we don't select an option by default,
                            // if there is just one option.
                            if (
                                ($form->field_in_session('attribute_' . $attribute['id']) == true)
                                && ($form->get_field_value('attribute_' . $attribute['id']) == '')
                            ) {
                                $unselected = true;
                            }

                            // Create a new clean attribute array for this attribute
                            // that we can add to the attributes array.
                            // We use array_values() below so that options is treated
                            // as an array instead of an object in js.
                            $attribute = array(
                                'sort_order' => $sort_order,
                                'id' => $attribute['id'],
                                'label' => $attribute['label'],
                                'enabled' => true,
                                'default_option_id' => $product_group_attributes[$attribute['id']]['default_option_id'],
                                'unselected' => $unselected,
                                'options' => array_values($options));

                            $attributes[] = $attribute;

                            $added_attributes[] = $attribute['id'];
                        }

                        sort($attributes);

                        $output_attribute_rows = '';

                        foreach ($attributes as $attribute) {
                            $pick_list_options = array();

                            $pick_list_options[''] = '';

                            foreach ($attribute['options'] as $option) {
                                $pick_list_options[h($option['label'])] = $option['id'];
                            }

                            // If there is no field in the session for this attribute,
                            // then check if we should select an option by default.
                            if ($form->field_in_session('attribute_' . $attribute['id']) == false) {
                                // If there is only one option, then select it by default.
                                if (count($attribute['options']) == 1) {
                                    $form->assign_field_value('attribute_' . $attribute['id'], $attribute['options'][0]['id']);

                                // Otherwise if there is a default option for this attribute,
                                // then select it by default.
                                } else if ($attribute['default_option_id']) {
                                    $form->assign_field_value('attribute_' . $attribute['id'], $attribute['default_option_id']);
                                }
                            }

                            $form->set('attribute_' . $attribute['id'], 'options', $pick_list_options);
                        }

                        // We use array_values() below so that the associative products
                        // is converted into an array in JSON instead of an object,
                        // because it is easier to deal with as an array in js.
                        $footer_system .=
                            '<script>
                                software.init_product_attributes({
                                    attributes: ' . encode_json($attributes) . ',
                                    products: ' . encode_json(array_values($products_for_js)) . ',
                                    default_image_name: \'' . escape_javascript($item['image_name']) . '\',
                                    default_short_description: \'' . escape_javascript($item['short_description']) . '\',
                                    default_full_description: \'' . escape_javascript($item['full_description']) . '\',
                                    default_details: \'' . escape_javascript($item['details']) . '\',
                                    default_code: \'' . escape_javascript($item['code']) . '\',
                                    discounted_product_prices: ' . encode_json($discounted_product_prices) . ',
                                    visitor_currency_symbol: \'' . escape_javascript(VISITOR_CURRENCY_SYMBOL) . '\',
                                    visitor_currency_exchange_rate: \'' . escape_javascript(VISITOR_CURRENCY_EXCHANGE_RATE) . '\',
                                    visitor_currency_code_for_output: \'' . escape_javascript(VISITOR_CURRENCY_CODE_FOR_OUTPUT) . '\'
                                });
                            </script>';
                    }
                }

                // If attributes are not being outputted, then prepare product row.
                if (!$attributes) {

                    $product_row = true;

                    if ($available_products_exist and (count($products) > 1)) {
                        $product_pick_list = true;
                        $pick_list_options = array();
                        $format = 'plain_text';
                    } else {
                        $product_pick_list = false;
                        $format = 'html';
                    }

                    foreach ($products as $key => $product) {

                        $product['description'] = h($product['short_description']) . ' (' . prepare_price_for_output($product['price'], $product['discounted'], $product['discounted_price'], $format) . ')';

                        // if inventory is enabled for the product,
                        // and the product is out of stock,
                        // and there is an out of stock message
                        // then append out of stock message to label
                        if (
                            ($product['inventory'] == 1)
                            && ($product['inventory_quantity'] == 0)
                            && ($product['backorder'] != 1)
                            && ($product['out_of_stock_message'] != '')
                            && ($product['out_of_stock_message'] != '<p></p>')
                        ) {
                            $out_of_stock_message = trim(convert_html_to_text($product['out_of_stock_message']));
                            
                            // if the message is longer than 50 characters, then truncate it
                            if (mb_strlen($out_of_stock_message) > 50) {
                                $out_of_stock_message = mb_substr($out_of_stock_message, 0, 50) . '...';
                            }
                            
                            $product['description'] .= ' - ' . h($out_of_stock_message);
                        }

                        if ($product_pick_list) {
                            $pick_list_options[$product['description']] = $product['id'];
                        }

                        $products[$key] = $product;
                        
                    }

                    if ($product_pick_list) {
                        $form->set('product_id', 'options', $pick_list_options);
                        $form->set('product_id', 'required', true);
                    }

                }

            }

            if ($available_products_exist) {

                // If multi-recipient shipping is enabled and there are shippable products
                // that are available, then allow customer to select a recipient.
                if (
                    ECOMMERCE_SHIPPING
                    and (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient')
                    and $shippable_products
                ) {
                    $recipient = true;

                    $form->set('ship_to', 'options', get_recipient_options());
                    $form->set('add_name', 'maxlength', 50);
                }

                if ($non_donation_products_exist) {
                    $quantity = true;

                    $form->set('quantity', 'maxlength', 9);
                    $form->set('quantity', 'required', true);
                }

                // If an add button label was not entered for the page, then set default label.
                if ($add_button_label == '') {
                    $add_button_label = 'Continue';
                }

            }
        }

        $back_button_url = '';

        // if there is a previous url set, then add back button
        if ((isset($_GET['previous_url_id']) == true) && (isset($_SESSION['software']['urls'][$_GET['previous_url_id']]) == true)) {
            $back_button_url = $_SESSION['software']['urls'][$_GET['previous_url_id']];

            // If a back button label was not entered for the page, then set default label.
            if ($back_button_label == '') {
                $back_button_label = 'Back';
            }
        }

        $product_groups = array();

        if ($catalog_page['id']) {

            $parent_product_groups = array();

            // If the item is a product, then get all product groups that include
            // this product, so we can determine the current product group later.
            if ($item['type'] == 'product') {
                $parent_product_groups = db_values(
                    "SELECT product_group
                    FROM products_groups_xref
                    WHERE product = '" . e($item['id']). "'");
            }

            $product_groups = get_product_groups(array(
                'id' => $catalog_page['product_group_id'],
                'display_type' => 'browse',
                'status' => 'enabled'));

            $catalog_url = PATH . encode_url_path($catalog_page['name']) . '/';
            $query_string = '?previous_url_id=' . urlencode(generate_url_id());

            $current_found = false;

            // Loop through the product groups in order to prepare additional info.
            foreach ($product_groups as $key => $product_group) {
                $product_group['url'] = $catalog_url . encode_url_path(get_catalog_item_address_name_from_id($product_group['id'], 'product group')) . $query_string;

                // If the product group that should be marked as current
                // has not been found yet, then check if this product group is current.
                if (!$current_found) {
                    if ($item['type'] == 'product group') {
                        if ($product_group['id'] == $item['parent_id']) {
                            $product_group['current'] = true;
                            $current_found = true;
                        }

                    // Otherwise the item is a product, so check array of parent product
                    // groups to determine if this product group is a parent of the product.
                    // A product could be in multiple product groups, but we only mark
                    // the first product group in the list as the current one.
                    } else {
                        if (in_array($product_group['id'], $parent_product_groups)) {
                            $product_group['current'] = true;
                            $current_found = true;
                        }
                    }
                }
                
                $product_groups[$key] = $product_group;
            }

        }

        $search_attributes = '';

        if ($catalog_page['id']) {
            $search_attributes =
                'action="' . OUTPUT_PATH . h(encode_url_path($catalog_page['name'])) . '"
                method="get"';
        }

        $currency = false;
        $currency_attributes = '';
        $currencies = array();
        $currency_system = '';

        if (ECOMMERCE_MULTICURRENCY) {
            // Get all currencies where the exchange rate is not 0, with base currency first.
            $currencies = db_items(
                "SELECT
                    id,
                    name,
                    base,
                    code,
                    symbol,
                    exchange_rate
                FROM currencies
                WHERE exchange_rate != '0'
                ORDER BY
                    base DESC,
                    name ASC");

            // If there is at least one extra currency, in addition to the base currency,
            // then continue to prepare currency info.
            if (count($currencies) > 1) {

                $currency = true;

                $currency_attributes =
                    'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/update_currency.php" ' .
                    'method="post"';

                $currency_options = array();

                foreach ($currencies as $currency) {
                    $label = h($currency['name'] . ' (' . $currency['code'] . ')');

                    $currency_options[$label] = $currency['id'];
                }

                $form->set('currency_id', 'options', $currency_options);
                $form->set('currency_id', VISITOR_CURRENCY_ID);
                $form->set('send_to', REQUEST_URL);

                $currency_system =
                    get_token_field() . '
                    <input type="hidden" name="send_to">
                    <script>software.init_currency()</script>';

            } else {
                $currencies = array();
            }
        }

        $price = 0;
        $price_range = array();

        // Prepare product group price range or product price in dollars in order
        // to pass data to template.

        if ($item['type'] == 'product group') {
            $price_range['smallest_price'] = $item['price_range']['smallest_price'] / 100;
            $price_range['largest_price'] = $item['price_range']['largest_price'] / 100;
            $price_range['original_price'] = $item['price_range']['original_price'] / 100;
        } else {
            $price = $item['price'] / 100;
        }

        // Loop through the products in order to prepare price in dollars.
        foreach ($products as $key => $product) {
            $product['price'] = $product['price'] / 100;
            $product['discounted_price'] = $product['discounted_price'] / 100;

            $products[$key] = $product;
        }

        $content = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'item' => $item,
            'type' => $item['type'],
            'image_name' => $item['image_name'],
            'image_url' => $image_url,
            'code' => $item['code'],
            'short_description' => $item['short_description'],
            'full_description' => $item['full_description'],
            'keywords' => $keywords,
            'keywords_list' => $item['keywords'],
            'price_info' => $price_info,
            'price' => $price,
            'price_range' => $price_range,
            'products' => $products,
            'available_products' => $available_products_exist,
            'form_attributes' => $form_attributes,
            'attributes' => $attributes,
            'product_row' => $product_row,
            'product_pick_list' => $product_pick_list,
            'recipient' => $recipient,
            'quantity' => $quantity,
            'add_button_label' => $add_button_label,
            'back_button_url' => $back_button_url,
            'back_button_label' => $back_button_label,
            'system' => $system,
            'details' => $item['details'],
            'footer_system' => $footer_system,
            'catalog_page' => $catalog_page,
            'product_groups' => $product_groups,
            'search_attributes' => $search_attributes,
            'currency' => $currency,
            'currency_attributes' => $currency_attributes,
            'currencies' => $currencies,
            'currency_system' => $currency_system,
            'address_name' => $item['address_name']));

        $content = $form->prepare($content);

        $form->remove();

        return
            '<div class="software_catalog_detail">
                ' . $content . '
            </div>';

    }

}