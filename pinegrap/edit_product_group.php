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
$liveform = new liveform('edit_product_group');

// if the form has not been submitted
if (!$_POST) {
    // get product group information from database
    $query = 
        "SELECT 
            name,
            enabled,
            parent_id,
            short_description,
            full_description,
            details,
            code,
            keywords,
            image_name,
            display_type,
            address_name,
            title,
            meta_description,
            meta_keywords,
            seo_score,
            attributes
        FROM product_groups
        WHERE id = '" . e($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);
    
    $name = h($row['name']);
    $enabled = $row['enabled'];
    $parent_id = $row['parent_id'];
    $short_description = h($row['short_description']);
    $full_description = h(prepare_rich_text_editor_content_for_output($row['full_description']));
    $details = h(prepare_rich_text_editor_content_for_output($row['details']));
    $code = h($row['code']);
    $keywords = h($row['keywords']);
    $image_name = h($row['image_name']);
    $display_type = $row['display_type'];
    $address_name = $row['address_name'];
    $title = $row['title'];
    $meta_description = $row['meta_description'];
    $meta_keywords = $row['meta_keywords'];
    $seo_score = $row['seo_score'];
    $enable_attributes = $row['attributes'];

    $enabled_checked = '';

    if ($enabled) {
        $enabled_checked = ' checked="checked"';
    }
    
    // if display type is set to browse, then select that radio button and prepare to hide rows that only apply to select product groups
    if ($display_type == 'browse') {
        $display_type_browse_checked = ' checked="checked"';
        $display_type_select_checked = '';
        $details_row_style = ' style="display: none"';
        $keywords_row_style = ' style="display: none"';
        
    // else, display type is select, so select that radio button and prepare to show rows that only apply to select product groups
    } else {
        $display_type_select_checked = ' checked="checked"';
        $display_type_browse_checked = '';
        $details_row_style = '';
        $keywords_row_style = '';
    }
    
    // if this is the top-level product group, then do not allow user to change the display type
    if ($parent_id == 0) {
        $output_display_type_options = 'Display contents for browsing on catalog page (this top-level product group must be set to this)';
        
    // else this is not the top-level product group, so allow the user to select the display type
    } else {
        $output_display_type_options =
            '<input type="radio" class="radio" value="browse" id="browse" name="display_type"' . $display_type_browse_checked . ' onclick="show_or_hide_product_group_display_type(\'browse\')" /><label for="browse"> Display contents for browsing on catalog page</label><br />
            <input type="radio" class="radio" value="select" id="select" name="display_type"' . $display_type_select_checked . ' onclick="show_or_hide_product_group_display_type(\'select\')" /><label for="select"> Display contents for selection on catalog detail page</label>';
    }
    
    $items = array();
    $selected_products = array();
    
    // initialize arrays that will store data for sorting
    $item_sort_orders = array();
    $item_names = array();
    
    // get all product groups currently in this product group
    $query = 
        "SELECT
            id,
            name,
            enabled,
            short_description,
            sort_order
        FROM product_groups
        WHERE
            parent_id = '" . e($_GET['id']) . "'";
    
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    while ($row = mysqli_fetch_assoc($result)) {
        $row['type'] = 'product_group';
        $items[] = $row;
        
        $item_sort_orders[] = $row['sort_order'];
        $item_names[] = mb_strtolower($row['name']);
    }
    
    // if there are product groups inside of this product group
    if (count($items) > 0) {
        $child_product_group_exists = true;
    } else {
        $child_product_group_exists = false;
    }
    
    // get all products currently in this product group
    $query = 
        "SELECT
            product as id,
            sort_order,
            products.name,
            products.enabled,
            products.short_description,
            products.price
        FROM products_groups_xref
        LEFT JOIN products on products.id = product
        WHERE
            product_group = '" . e($_GET['id']) . "'";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    while ($row = mysqli_fetch_assoc($result)) {
        $row['price'] = $row['price'] / 100;
        $row['type'] = 'product';
        
        $selected_products[] = $row['id'];
        $items[] = $row;
        
        $item_sort_orders[] = $row['sort_order'];
        $item_names[] = mb_strtolower($row['name']);
    }

    // sort the items by sort order and then by name
    array_multisort($item_sort_orders, $item_names, $items);

    foreach ($items as $item) {
        // if this is a product
        if ($item['type'] == "product") {
            $output_checkbox = '<input type="checkbox" name="products[]" value="' . $item['id'] . '" checked="checked" class="checkbox" />';
            $price = prepare_amount($item['price']);
            $id_type = 'product_id';
            $output_url = 'edit_product.php?id=' . $item['id'];
            
        // else, this is a product_group
        } else {
            $output_checkbox = '<img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/check_mark.gif" width="7" height="7" border="0" alt="check mark" style="padding: .5em" />';
            $price = '';
            $id_type = 'product_group_id';
            $output_url = 'edit_product_group.php?id=' . $item['id'];
        }

        // If this item is enabled, then use green color for name and short description.
        if ($item['enabled'] == 1) {
            $status_class = 'status_enabled';
        
        // Otherwise this item is disabled, so use red color for name and short description.
        } else {
            $status_class = 'status_disabled';
        }

        $output_rows .=
            '<tr id="' . $item['id'] . '">
                <td class="selectall">' . $output_checkbox . '</td>
                <td onclick="window.location.href=\'' . $output_url . '\'" class="chart_label pointer ' . $status_class . '">' . h($item['name']) . '</td>
                <td class="pointer ' . $status_class . '" onclick="window.location.href=\'' . $output_url . '\'" style="white-space: normal">' . h($item['short_description']) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_url . '\'" style="white-space: normal">' . $price . '</td>
                <td><input type="text" name="sort_order_' . $item['type'] . '_' . $item['id'] . '" size="5" value="' . $item['sort_order'] . '" maxlength="4" /></td>
            </tr>';
    }
    
    // get all products
    $query = 
        "SELECT 
            id,
            name,
            enabled,
            short_description,
            price
        FROM products
        ORDER BY name";
    
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    while ($row = mysqli_fetch_assoc($result)) {
        // if this product is not in the selected products array
        if (!in_array($row['id'], $selected_products)) {
            $row['name'] = h($row['name']);
            $row['short_description'] = h($row['short_description']);
            $row['price'] = $row['price'] / 100;

            // If this item is enabled, then use green color for name and short description.
            if ($row['enabled'] == 1) {
                $status_class = 'status_enabled';
            
            // Otherwise this item is disabled, so use red color for name and short description.
            } else {
                $status_class = 'status_disabled';
            }

            $output_rows .=
                '<tr id="' . $row['id'] . '">
                    <td style="text-align: center"><input type="checkbox" name="products[]" value="' . $row['id'] . '" class="checkbox" /></td>
                    <td onclick="window.location.href=\'edit_product.php?id=' . $row['id'] . '\'" class="chart_label pointer '  . $status_class . '">' . $row['name'] . '</td>
                    <td class="pointer '  . $status_class . '" onclick="window.location.href=\'edit_product.php?id=' . $row['id'] . '\'" style="white-space: normal">' . $row['short_description'] . '</td>
                    <td class="pointer" onclick="window.location.href=\'edit_product.php?id=' . $row['id'] . '\'">' . prepare_amount($row['price']) . '</td>
                    <td><input type="text" name="sort_order_product_' . $row['id'] . '" size="5" class="order_product" value="" maxlength="4" /></td>
                </tr>';
        }
    }


    // Get product group images from xref.
    $query = "SELECT product_group,file_name FROM product_groups_images_xref WHERE product_group = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $xref_image_names = '';
    $output_xref_image_names ='';
if (mysqli_num_rows($result) != 0){
    $xref_image_names = array();

    while ($row = mysqli_fetch_assoc($result)){
        $xref_image_names[]= $row['file_name'];
    }
    foreach($xref_image_names as $xref_image_name) {
        $output_xref_images .= '<li><img src="' . PATH . $xref_image_name . '" ><div class="list-item-title">' . $xref_image_name . '</div><div class="list-item-remove no-drag" onclick="$(this)[0].parentNode.remove();">x</div><input type="hidden" name="selected_images[]" id="selected_images" value="' . $xref_image_name . '" /></li>';
    }
}

$output_cover_image ='';
if($image_name == true){
    $cover_image_name = $image_name;
    $output_cover_image ='<div style="float: left; padding: .5em 1em .5em 0em"><a href="'. PATH . $cover_image_name . '" target="_blank"><img src="'. PATH . $image_name . '" style="width:50px;height:50px;    object-fit: scale-down;"/></a></div>';
    $output_selected_image = '<li><img src="' . PATH . $image_name . '" ><div class="list-item-title">' . $image_name . '</div><div class="list-item-remove no-drag" onclick="$(this)[0].parentNode.remove();">x</div><input type="hidden" name="selected_images[]" id="selected_images" value="' . $image_name . '" /></li>';
}

$output_selected_images =  $output_selected_image . $output_xref_images;





    $output_attributes = '';

    // If there is at least one attribute in the system, then output area for them.
    if (db_value("SELECT COUNT(*) FROM product_attributes")) {
        $output_attributes_heading_row_style = ' style="display: none"';
        $output_enable_attributes_row_style = ' style="display: none"';
        $output_attributes_row_style = ' style="display: none"';
        $output_enable_attributes_checked = '';

        if ($display_type == 'select') {
            $output_attributes_heading_row_style = '';
            $output_enable_attributes_row_style = '';

            if ($enable_attributes == 1) {
                $output_attributes_row_style = '';
            }
        }

        if ($enable_attributes == 1) {
            $output_enable_attributes_checked = ' checked="checked"';
        }

        $attributes = array();

        // If this product group contains at least one product, then continue
        // to get attributes for those products.
        if ($selected_products) {
            $sql_products = "";

            // Loop through the products in this product group in order to prepare
            // SQL for getting attributes for those products.
            foreach ($selected_products as $product_id) {
                if ($sql_products != '') {
                    $sql_products .= " OR ";
                }

                $sql_products .= "(product_id = '$product_id')";
            }

            // Get all attributes for products in this product group.
            $attributes_without_sort_order = db_items(
                "SELECT
                    DISTINCT(attribute_id) AS id,
                    product_attributes.name
                FROM products_attributes_xref
                LEFT JOIN product_attributes ON products_attributes_xref.attribute_id = product_attributes.id
                WHERE $sql_products");

            // If at least one attribute was found, then continue to prepare
            // attributes and order them.
            if ($attributes_without_sort_order) {
                // Get sort orders for attributes for this product group.
                $sort_orders = db_items(
                    "SELECT
                        attribute_id AS id,
                        default_option_id,
                        sort_order
                    FROM product_groups_attributes_xref
                    WHERE product_group_id = '" . e($_GET['id']) . "'", 'id');

                // Get all options for products in this product group,
                // so that we know whether we should include an option
                // for an attribute or not.
                $product_options = db_values(
                    "SELECT DISTINCT(option_id)
                    FROM products_attributes_xref
                    WHERE $sql_products");

                // Loop through the raw attributes array in order to prepare
                // new attributes array that we can use to sort the attributes.
                foreach ($attributes_without_sort_order as $attribute) {
                    // If there is a sort order for this attribute, then set it.
                    if ($sort_orders[$attribute['id']]) {
                        $sort_order = $sort_orders[$attribute['id']]['sort_order'];

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
                                            (products_groups_xref.product_group = '" . e($_GET['id']) . "')
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

                    $attribute = array('sort_order' => $sort_order) + $attribute;

                    $attribute['options'] = array_values($options);
                    $attribute['default_option_id'] = $sort_orders[$attribute['id']]['default_option_id'];

                    $attributes[] = $attribute;
                }

                sort($attributes);
            }
        }

        // If there was at least one attribute found for a product,
        // then init js to allow attributes to be ordered.
        if ($attributes) {
            $output_attribute_management =
                '<table class="attributes" style="margin-top: 1em">
                    <tr>
                        <th>Name</th>
                        <th>Default Option</th>
                        <th style="text-align: center">Order</th>
                    </tr>
                </table>
                <script>init_product_group_attributes({attributes: ' . encode_json($attributes) . '})</script>';

        // Otherwise no attributes were found for products in this product group,
        // so output message to user that explains that.
        } else {
            $output_attribute_management =
                '<div class="attributes" style="margin-top: 1em">
                    No Attributes were found for Products in this Product Group.  Once you assign Attributes to Products and include those Products in this Product Group,
                    then you may manage those Attributes here.
                </div>';
        }

        $output_attributes =
            '<tr id="attributes_heading_row"' . $output_attributes_heading_row_style . '>
                <th colspan="2"><h2>Attributes</h2></th>
            </tr>
            <tr id="enable_attributes_row"' . $output_enable_attributes_row_style . '>
                <td><label for="enable_attributes">Enable Attributes:</label></td>
                <td>
                    <input type="hidden" name="enable_attributes_exists" value="true">
                    <input type="checkbox" id="enable_attributes" name="enable_attributes" value="1"' . $output_enable_attributes_checked . ' class="checkbox">
                    <script>
                        $("#enable_attributes").click(function() {
                            if ($(this).attr("checked") == "checked") {
                                $("#attributes_row").show();
                            } else {
                                $("#attributes_row").hide();
                            }
                        });
                    </script>
                </td>
            </tr>
            <tr id="attributes_row"' . $output_attributes_row_style . '>
                <td colspan="2">
                    ' . $output_attribute_management . '
                </td>
            </tr>';
    }
    
    // if this is not the root parent product group, then output delete button
    if ($parent_id != 0) {
        $output_parent_product_group_row =
            '<tr>
                <td>Parent Product Group:&nbsp;</td>
                <td><select name="parent_id">' . get_product_group_options($parent_id, 0, $_GET['id'], $level = 0, $product_groups = array(), $include_select_product_groups = FALSE) . '</select></td>
            </tr>';
        
        // if there is a child product group in this product group, then disable delete button
        if ($child_product_group_exists == true) {
            $output_delete_button = '&nbsp;&nbsp;&nbsp;<input type="button" value="Delete" class="delete" onclick="alert(\'Please delete all product groups in this product group before deleting this product group.\')" />';
        // else there is not a child object in this product group, so allow delete
        } else {
            $output_delete_button = '&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_button" value="Delete" class="delete" onclick="return confirm(\'WARNING: This product group will be permanently deleted.\')">';
        }
    
    // else, then this is the root parent product group. Do not allow it to be deleted.
    } else {
        $output_parent_product_group_row = '';
        $output_delete_button = '';
    }
    
    echo
        output_header() . '

        <div id="subnav">
            '.$output_cover_image.'
            <h1>' . $name . '</h1>
        </div>
        <div id="button_bar"><a href="duplicate_product_group.php?id=' . h($_GET['id']) . '">Duplicate</a></div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            ' . get_wysiwyg_editor_code(array('full_description', 'details')) . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Product Group</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit a product group and assign products to them.</div>
            <form name="form" action="edit_product_group.php" method="post" class="product_group_form" enctype="multipart/form-data">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Product Group Information</h2></th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" value="' . $name . '" /></td>
                    </tr>
                    <tr>
                        <td><label for="enabled">Enable:</label></td>
                        <td><input type="checkbox" id="enabled" name="enabled" value="1"' . $enabled_checked . ' class="checkbox"></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Catalog Page Display Options</h2></th>
                    </tr>
                    ' . $output_parent_product_group_row . '
                    <tr>
                        <td>Short Description:</td>
                        <td><input type="text" name="short_description" maxlength="100" size="60" value="' . $short_description . '" /></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Full Description:</td>
                        <td><textarea name="full_description" id="full_description" style="width: 600px; height: 200px;">' . $full_description . '</textarea></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Display Type:</td>
                        <td>
                            ' . $output_display_type_options . '
                        </td>
                    </tr>
                    <tr id="details_row"' . $details_row_style . '>
                        <td style="vertical-align: top">Details:</td>
                        <td><textarea name="details" id="details" style="width: 600px; height: 200px;">' . $details . '</textarea></td>
                        </tr>                    
              

  
                        <div id="code_tips_dialog" style="display:none;" title="Code Area usage tips">                      
                        <strong>Code System Fields</strong>
                        <div class="scrollable fields" style="height: 100px; padding: 5px;max-width:100%;">
                            ^^image_loop_start^^<br>^^image_alt^^<br>^^image_url^^<br>^^image_loop_end^^
                        </div>
                    <strong>Hints</strong>
                        <ul style="margin-top: 0px; margin-left: 20px">
                            <li>Copy fields from here and paste in the layout below.</li>
                            <li>example 1 - this is best and newest way to use code. add code and select multiple product image, its auto insert images in code.
                            <textarea readonly rows="10" style="width:85%;border:none !important;box-shadow:none !important;    background-color: #0000001f;height:auto;" ><style>img.image-url {display: none !important;}</style>
<div class="image-slider slider-thumb-controls controls-inside">
<ul class="slides">
^^image_loop_start^^
<li>
<img alt="^^image_alt^^" src="^^image_url^^" style="visibility: hidden"/>
</li>
^^image_loop_end^^
</ul> 
</div></textarea>
                        </li>
                        <li>or example 2 - this methot is oldest way to use code. you have to select one product image and insert image names with manuel.
                            <textarea readonly rows="17" style="width:85%;border:none !important;box-shadow:none !important;    background-color: #0000001f;height:auto;" ><style>img.image-url {display: none !important;}</style>
<div class="image-slider slider-thumb-controls controls-inside">
<ul class="slides">
<li>
    <img alt="Image" src="image1.jpg" style="visibility: hidden"/>
</li>
<li>
    <img alt="Image" src="image2.jpg" style="visibility: hidden"/>
</li>
<li>
    <img alt="Image" src="image3.jpg" style="visibility: hidden"/>
</li>
<li>
    <img alt="Image" src="image4.jpg" style="visibility: hidden"/>
</li>
</ul> 
</div></textarea>
</li>
                        <li>
                           for using code system fields as example 1 you must insert all tag: ^^image_loop_start^^	^^image_url^^	^^image_loop_end^^. else its not work!. It is basicly loop all codes between the tags: ^^image_loop_start^^	^^image_loop_end^^, replace ^^image_url^^ and ^^image_url^^ tag to product image selected. example 1 and example 2 methods is perfect compatible with your theme if use theme slider or carausel html codes. Choose your own choice.
                        </li>
                       
                    </ul>
                </div>
        
                        <tr>
                        <td style="vertical-align: top">Code:</td>
                        <td>
                            <a id="show_code_tips_dialog" class="button">Show Code Tips</a>
                            <textarea id="code" name="code" style="width: 500px; height: 100px">' . $code . '</textarea>
                            ' . get_codemirror_includes() . '
                            ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed')) . '
                        </td>
                    </tr>
                    <tr id="keywords_row"' . $keywords_row_style . '>
                        <td>Search Keywords:</td>
                        <td><input type="text" name="keywords" maxlength="255" size="100" value="' . $keywords . '" /></td>
                    </tr>
                    <tr>
                        <td>Select Image: </td>
                        <td>
                    
                            <ul class="sortable-list img-list">
                                ' . $output_selected_images . '
                                <li class="add_new_item no-drag">
                                    <a id="show_img_selector_dialog" class="button">Add Image</a>
                                </li>
                            </ul>
                            
                            <div id="img_selector_dialog" style="display:none;" class="select_image" title="Select Product Image(s)">   
                                <div class="images">
                                    <input type="file" id="file" style="visibility:hidden;width:0;height:0;line-height:0;"  accept="image/*"/>
                                    <div class="image image_selector_item upload" id="imageupload" style="width: 100%;display: flex;justify-content: center;align-items: center;"><p>Upload</p></div>
                                    ' . select_image_options($image_name) . '  
                                </div>     
                            </div>
                        </td> 
                    </tr>
                    ' . $output_attributes . '
                    <tr>
                        <th colspan="2"><h2>Search Engine Optimization</h2></th>
                    </tr>
                    <tr>
                        <td>Catalog Name:</td>
                        <td><span style="white-space: nowrap">' . URL_SCHEME . HOSTNAME . OUTPUT_PATH . 'example-catalog/<input type="text" name="address_name" value="' . h($address_name) . '" size="60" maxlength="255" /></span></td>
                    </tr>
                    <tr>
                        <td>
                            <label for="title">Web Browser Title:</label>
                        </td>
                        <td>
                            <input id="title" name="title" type="text" value="' . h($title) . '" maxlength="255" style="width: 98%">
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">
                            <label for="meta_description">Web Browser Description:</label>
                        </td>
                        <td>
                            <textarea id="meta_description" name="meta_description" maxlength="255" rows="3" style="width: 99%">'
                                . h($meta_description) .
                            '</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">
                            <label for="meta_keywords">Web Browser Keywords:</label>
                        </td>
                        <td>
                            <textarea id="meta_keywords" name="meta_keywords" rows="3" style="width: 99%">'
                                . h($meta_keywords) .
                            '</textarea>
                        </td>
                    </tr>
                </table>
                <h2 style="margin-bottom: 1em">Products and Child Product Groups to Include</h2>
                <table class="chart" style="margin-bottom: 1.5em">
                    <tr>
                        <th style="text-align: center;" id="select_all">Select</th>
                        <th>Name</th>
                        <th>Short Description</th>
                        <th>Price</th>
                        <th>Order</th>
                    </tr>
                    ' . $output_rows . '
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_button" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">' . $output_delete_button . '
                </div>
                <input type="hidden" name="max_input_vars_test" value="true">
            </form>
        </div>
        <script>
    
        
        $(document).ready(function() {
            
            $( "#show_img_selector_dialog" ).on( "click", function() {
                $("body").attr("style","overflow:hidden;");
                img_selector_dialog.dialog( "open" );
            });
            
            var width_o_screen = $(window).width() - 100;
            var height_o_screen = $(window).height() - 100;
    
    
            var img_selector_dialog = $( "#img_selector_dialog" );
            img_selector_dialog.dialog({
                autoOpen: false,
                modal: true,
                height: height_o_screen,
                width: width_o_screen,
                beforeClose: function( event, ui ) {
                    $("body").attr("style","overflow:auto;");
                    
                }
            });
            var tips_dialog = $( "#code_tips_dialog" );
            tips_dialog.dialog({
                 autoOpen: false,
                 height: height_o_screen,
                 width: width_o_screen,
                 modal: true,
            });
            $( "#show_code_tips_dialog" ).on( "click", function() {
              tips_dialog.dialog( "open" );
            });
            
            $(".sortable-list").sortable({
                items: "> li:not(.add_new_item)",
                placeholder: ".list-placeholder",
                connectWith: "ul",
                cancel: ".no-drag"
            });
            
            $(".sortable-list li:not(.add_new_item)").append("<div class=\u0022list-item-remove no-drag\u0022 onclick=\u0022$(this)[0].parentNode.remove();\u0022>x</div>");
            $(".image_selector_item.upload").click(function(){
                var file = $("#file");
                file.click();
            });
            $("#file").change(function(){
                readURL(this);
            });
            $items = $(".image_selector_item:not(.upload):not(.uploading)");
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.fileName = input.files[0].name;
                    reader.fileSize = (input.files[0].size / (1024*1024)).toFixed(2) + " MB ";
                    reader.fileExtention = input.files[0].name.split(".").pop().toLowerCase();
                    reader.onload = function (e) {
                    var data = e.target.result;
                    var name = input.files[0].name;
                    $(".image_selector_item.upload").after("<div class=\u0022image image_selector_item uploading\u0022 ><div class=\u0022thumbnail\u0022><img  src=\u0022' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/loading.gif\u0022 width=\u0022100\u0022 height=\u0022100\u0022 alt=\u0022\u0022 style=\u0022display: block; overflow: hidden;\u0022></div><div class=\u0022image_content\u0022><strong class=\u0022image_name\u0022>Uploading...</strong></div></div>");
                    // Use AJAX to upload image.
                    $.ajax({
                        contentType: "application/json",
                        url: "api.php",
                        data: JSON.stringify({
                            action: "upload_file",
                            token:software_token ,
                            data: data,
                            name: name,
                            contentType: false,
                            processData: false,
                        }),
                        type: "POST",
                        success: function(response) {
                            // Check the values in console
                            $status = response.status;
                            
                            if($status == "success"){
                                console.log(response.message);
                                $(".image_selector_item.upload").after("<div class=\u0022image image_selector_item\u0022 ><div class=\u0022thumbnail\u0022><img  src=\u0022' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . '" + response.name + "\u0022 width=\u0022100\u0022 height=\u0022100\u0022 alt=\u0022\u0022 style=\u0022display: block; overflow: hidden;\u0022></div><div class=\u0022image_content\u0022><strong class=\u0022image_name\u0022>" + response.name + "</strong><br><span>Size: " + response.filesize + "</span></div></div>");
                                $(".image_selector_item.uploading").remove();
                                $items = $(".image_selector_item:not(.upload):not(.uploading)");
                                $items.on("click",function(){
                                    $img_name = $(this).find(".image_name").text();
                                    $( "#img_selector_dialog" ).dialog( "close" );
                                    if (!$img_name) {
                                        return false;
                                    }
                                    $img_url = "' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . '" + $img_name;
                                    $("<li><img src=\u0022" + $img_url + "\u0022><div class=\u0022 list-item-title \u0022>" + $img_name + "</div><div class=\u0022list-item-remove no-drag\u0022 onclick=\u0022$(this)[0].parentNode.remove();\u0022>x</div><input type=\u0022hidden\u0022 name=\u0022selected_images[]\u0022 id=\u0022selected_images\u0022 value=\u0022" + $img_name + "\u0022 /></li>").insertBefore(".add_new_item ");
                                    $img_name = "";
                                    $img_url = "";
                                });
                            }
                        }
                    });
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            $( "#show_img_selector_dialog" ).on( "click", function() {
                $("body").attr("style","overflow:hidden;");
                img_selector_dialog.dialog( "open" );
            });
            $items.on("click",function(){
                $img_name = $(this).find(".image_name").text();
                $( "#img_selector_dialog" ).dialog( "close" );
                if (!$img_name) {
                    return false;
                }
                $img_url = "' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . '" + $img_name;
                $("<li><img src=\u0022" + $img_url + "\u0022><div class=\u0022 list-item-title \u0022>" + $img_name + "</div><div class=\u0022list-item-remove no-drag\u0022 onclick=\u0022$(this)[0].parentNode.remove();\u0022>x</div><input type=\u0022hidden\u0022 name=\u0022selected_images[]\u0022 id=\u0022selected_images\u0022 value=\u0022" + $img_name + "\u0022 /></li>").insertBefore(".add_new_item ");
                $img_name = "";
                $img_url = "";
            });
            
        });
    </script>' .
        output_footer();
        
        $liveform->remove_form();

} else {
    validate_token_field();
    
    // If the max_input_vars_test hidden field is not in the post data then that means the post data was truncated,
    // so output error. This can happen because of max_input_vars (i.e. php.ini
    // setting added in PHP v5.3.9 and often backported to earlier versions).
    // This can happen when there is a large number of products (e.g. 1,000+).
    // The default value for max_input_vars is 1,000.
    if (isset($_POST['max_input_vars_test']) == FALSE) {
        output_error('Sorry, the server did not accept the form that you submitted. We recommend that you ask the server administrator to check the max_input_vars PHP setting in the php.ini file.  We recommend that it be set to a number that is at least double the number of Products that the site will contain. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    // get parent_id for product group
    $query = 
        "SELECT 
            parent_id
        FROM product_groups
        WHERE id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);
    $parent_id = $row['parent_id'];

    // Delete product group images references (we do this for both delete and update).
    db("DELETE FROM product_groups_images_xref WHERE product_group = '" . escape($_POST['id']) . "'");

    // if product group was selected for delete
    if ($_POST['submit_button'] == 'Delete') {
        // if the parent_id is not set to 0, allow user to delete the product group because it is not the root product group
        if ($parent_id != '0') {
            $new_product_ids = array();
            
            $search_results_pages = array();
            
            // get data from all search result pages that have "search products" enabled
            $query = "SELECT page_id, product_group_id FROM search_results_pages WHERE search_catalog_items = '1'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            while($row = mysqli_fetch_assoc($result)) {
                $search_results_pages[] = $row;
            }
            
            $search_results_pages_using_current_product_group = array();
            
            // loop through each search result page to get the search results page ids that uses this product group
            foreach ($search_results_pages as $search_results_page) {
                $search_results_page_product_groups = array();
                
                // get the product groups inside of this product group
                $search_results_page_product_groups = get_product_groups_in_product_group_tree($search_results_page['product_group_id']);
                
                // loop through the product groups to see if any of them match this one, and if there is a match then add it to the array
                foreach ($search_results_page_product_groups as $product_group) {
                    if ($product_group['id'] == $_POST['id']) {
                        $search_results_pages_using_current_product_group[] = $search_results_page;
                    }
                }
            }
            
            // loop through the search result pages for this product group and delete it's tag cloud
            foreach ($search_results_pages_using_current_product_group as $search_results_page) {
                delete_tag_cloud_keywords_for_search_results_page($search_results_page['page_id']);
            }
            
            // delete product group
            $query = "DELETE FROM product_groups ".
                     "WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            
            // delete all entries in products_groups_xref table
            $query = "DELETE FROM products_groups_xref ".
                     "WHERE product_group = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            
            // loop through the search result pages for this product group and re-build it's tag cloud
            foreach ($search_results_pages_using_current_product_group as $search_results_page) {
                update_tag_cloud_keywords_for_search_results_page_product_group($search_results_page['page_id'], $search_results_page['product_group_id']);
            }

            // Check if this product group has short links, in order to determine if we need to delete them and update rewrite file.
            $query =
                "SELECT COUNT(*)
                FROM short_links
                WHERE
                    (destination_type = 'product_group')
                    AND (product_group_id = '" . escape($_POST['id']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);

            // If a short link exists, then delete them and update short links in rewrite file.
            if ($row[0] != 0) {
                $query =
                    "DELETE FROM short_links
                    WHERE
                        (destination_type = 'product_group')
                        AND (product_group_id = '" . escape($_POST['id']) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }

            db("DELETE FROM product_groups_attributes_xref WHERE product_group_id = '" . e($_POST['id']) . "'");
            
            log_activity("product group ($_POST[name]) was deleted", $_SESSION['sessionusername']);

        // else, output an error stating that the product group cannot be deleted because it is the root product group
        } else {
            $liveform->mark_error('', 'This product group could not be deleted because it is the root product group.');
            
            // forward user to view product groups page
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_product_group.php?id=' . $_POST['id']);
            exit();
        }
    // else product group was not selected for delete
    } else {
        // if the name is blank, then mark error and forward user back to previous screen
        if ($_POST['name'] == '') {
            $liveform->mark_error('name', 'Name is required.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_product_group.php?id=' . $_POST['id']);
            exit();
        }
        
        // if the post value for parent_id is blank, set it to 0
        if (!$_POST['parent_id']) {
            $_POST['parent_id'] = 0;
        }
        
        // assume that we will not update the display type until we find out otherwise
        $sql_display_type = "";
        
        // if this is not a top-level product group, then prepare to update display type
        if ($parent_id != 0) {
            $sql_display_type = "display_type = '" . escape($_POST['display_type']) . "',";
        }
        
        // if the address name is NOT blank then use that value for the address name
        if ($_POST['address_name'] != '') {
            $address_name = $_POST['address_name'];
            
        // else if the short description is NOT blank then use that value
        } elseif ($_POST['short_description'] != '') {
            $address_name = $_POST['short_description'];
            
        // else use the name as the value
        } else {
            $address_name = $_POST['name'];
        }





        
        // prepare the address name for the database
        $address_name = prepare_catalog_item_address_name($address_name, $_POST['id']);
        
        // get current product group information
        $query =
            "SELECT
                title,
                meta_description,
                full_description,
                details,
                seo_analysis_current,
                address_name
            FROM product_groups
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $title = $row['title'];
        $meta_description = $row['meta_description'];
        $full_description = $row['full_description'];
        $details = $row['details'];
        $seo_analysis_current = $row['seo_analysis_current'];
        $current_address_name = $row['address_name'];
        
        $search_results_pages = array();
        
        // get data from all search result pages that have "search products" enabled
        $query = "SELECT page_id, product_group_id FROM search_results_pages WHERE search_catalog_items = '1'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while($row = mysqli_fetch_assoc($result)) {
            $search_results_pages[] = $row;
        }
        
        $search_results_pages_using_current_product_group = array();
        $search_results_pages_using_parent_product_group = array();
        
        // loop through each search result page to get the search results page ids that uses this product group
        foreach ($search_results_pages as $search_results_page) {
            $search_results_page_product_groups = array();
            
            // get the product groups inside of this product group
            $search_results_page_product_groups = get_product_groups_in_product_group_tree($search_results_page['product_group_id']);
            
            // loop through the product groups to see if any of them match this one, and if there is a match then add it to the arrays
            foreach ($search_results_page_product_groups as $product_group) {
                if ($product_group['id'] == $_POST['id']) {
                    $search_results_pages_using_current_product_group[] = $search_results_page;
                
                } elseif ($product_group['id'] == $_POST['parent_id']) {
                    $search_results_pages_using_parent_product_group[] = $search_results_page;
                }
            }
        }
        
        // loop through the search result pages for this product group and delete it's tag cloud
        foreach ($search_results_pages_using_current_product_group as $search_results_page) {
            delete_tag_cloud_keywords_for_search_results_page($search_results_page['page_id']);
        }
        
        // loop through the search result pages for the parent product group and delete it's tag cloud
        foreach ($search_results_pages_using_parent_product_group as $search_results_page) {
            delete_tag_cloud_keywords_for_search_results_page($search_results_page['page_id']);
        }
        
        $sql_seo_analysis_current = "";
        
        // if the seo analysis is current and the title, meta description, full description, or details has changed, the prepare to clear current status
        if (
            ($seo_analysis_current == 1)
            &&
            (
                (trim($title) != trim($_POST['title']))
                || (trim($meta_description) != trim($_POST['meta_description']))
                || (trim($full_description) != trim(prepare_rich_text_editor_content_for_input($_POST['full_description'])))
                || (trim($details) != trim(prepare_rich_text_editor_content_for_input($_POST['details'])))
            )
        ) {
            $sql_seo_analysis_current = "seo_analysis_current = '0',";
        }

        $sql_enable_attributes = "";

        // If the enable attributes check box appeared on the form, then update value.
        if ($_POST['enable_attributes_exists'] == 'true') {
            $sql_enable_attributes = "attributes = '" . escape($_POST['enable_attributes']) . "',";
        }

        // Before we update the product group, get the old status, so later
        // we know if the status changed and need to update status for children.
        $old_enabled = db_value(
            "SELECT enabled
            FROM product_groups
            WHERE id = '" . e($_POST['id']) . "'");

        $selected_images = array();
        foreach ($_POST['selected_images'] as $selected_image ) {
            $selected_images[] = $selected_image ;
        }

        $selected_count = 0;
        foreach ($selected_images as $value) {
            $selected_count++;
        }
        if($selected_count >= 1){
            $selected_cover_image = reset($selected_images);
            array_shift($selected_images);
            if($selected_cover_image){
                $sql_imagename = 
                "image_name = '" . escape($selected_cover_image) . "',";
            }
        }else{
            $sql_imagename = 
            "image_name = '',";
        }

        // update product group
        db(
            "UPDATE product_groups SET
                name = '" . e($_POST['name']) . "',
                enabled = '" . e($_POST['enabled']) . "',
                parent_id = '" . e($_POST['parent_id']) . "',
                short_description = '" . e($_POST['short_description']) . "',
                full_description = '" . e(prepare_rich_text_editor_content_for_input($_POST['full_description'])) . "',
                details = '" . e(prepare_rich_text_editor_content_for_input($_POST['details'])) . "',
                code = '" . e($_POST['code']) . "',
                keywords = '" . e($_POST['keywords']) . "',
                $sql_imagename
                $sql_display_type
                address_name = '" . e($address_name) . "',
                title = '" . e($_POST['title']) . "',
                meta_description = '" . e($_POST['meta_description']) . "',
                meta_keywords = '" . e($_POST['meta_keywords']) . "',
                $sql_seo_analysis_current
                $sql_enable_attributes
                user = '" . $user['id'] . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($_POST['id']) . "'");
        
        // flush appropriate entries from products_groups_xref table
        $query = "DELETE FROM products_groups_xref WHERE product_group = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        if($selected_count > 1){
            foreach ($selected_images as $value) {db("INSERT INTO product_groups_images_xref (product_group,file_name)VALUES ('" . escape($_POST['id']) . "','" . escape($value) . "')");}
        }

        // if at least one product was selected then proceed with update to products_groups_xref table
        if ($_POST['products']) {
            // foreach product that was selected
            foreach ($_POST['products'] as $product_id) {
                $query = "INSERT INTO products_groups_xref (product, product_group, sort_order) VALUES ('" . escape($product_id) . "', '" . escape($_POST['id']) . "', '" . escape($_POST['sort_order_product_' . $product_id]) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            }
        }
        
        // get all product groups currently in this product group
        $query = 
            "SELECT
                id
            FROM product_groups
            WHERE
                parent_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($_POST['sort_order_product_group_' . $row['id']])) {
                $query = 
                    "UPDATE product_groups SET 
                        sort_order = '" . escape($_POST['sort_order_product_group_' . $row['id']]) . "' 
                    WHERE id = '" . $row['id'] . "'";
                $result2 = mysqli_query(db::$con, $query) or output_error('Query failed');
            }
        }
        
        // loop through the search result pages for this product group and re-build it's tag cloud
        foreach ($search_results_pages_using_current_product_group as $search_results_page) {
            update_tag_cloud_keywords_for_search_results_page_product_group($search_results_page['page_id'], $search_results_page['product_group_id']);
        }
        
        // loop through the search result pages for the parent product group and re-build it's tag cloud
        foreach ($search_results_pages_using_parent_product_group as $search_results_page) {
            update_tag_cloud_keywords_for_search_results_page_product_group($search_results_page['page_id'], $search_results_page['product_group_id']);
        }

        db("DELETE FROM product_groups_attributes_xref WHERE product_group_id = '" . e($_POST['id']) . "'");

        // If there are attributes to save, then save them.
        if ($_POST['attributes']) {
            $attributes = decode_json($_POST['attributes']);

            $sort_order = 0;

            foreach ($attributes as $attribute) {
                $sort_order++;
                
                db(
                    "INSERT INTO product_groups_attributes_xref (
                        product_group_id,
                        attribute_id,
                        default_option_id,
                        sort_order)
                    VALUES (
                        '" . e($_POST['id']) . "',
                        '" . e($attribute['id']) . "',
                        '" . e($attribute['default_option_id']) . "',
                        '$sort_order')");
            }
        }

        // If the status of the product group has changed, then update status
        // for child items.
        if ($_POST['enabled'] != $old_enabled) {

            require_once(dirname(__FILE__) . '/update_product_group_status.php');

            if ($_POST['enabled']) {
                $status = 'enabled';
            } else {
                $status = 'disabled';
            }

            update_product_group_status(array(
                'id' => $_POST['id'],
                'status' => $status));

        }
        
        log_activity("product group ($_POST[name]) was modified", $_SESSION['sessionusername']);
    }
    
    // if there is a send to set, then forward user to send to
    if ($_POST['send_to'] != '') {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
        
    // else there is not a send to set, so forward user to view product groups screen.
    } else {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_product_groups.php');
    }
}
?>