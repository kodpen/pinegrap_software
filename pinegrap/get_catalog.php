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

function get_catalog($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];

    $properties = get_page_type_properties($page_id, 'catalog');

    $product_group_id = $properties['product_group_id'];
    $menu = $properties['menu'];
    $search = $properties['search'];
    $number_of_featured_items = $properties['number_of_featured_items'];
    $number_of_new_items = $properties['number_of_new_items'];
    $number_of_columns = $properties['number_of_columns'];
    $image_width = $properties['image_width'];
    $image_height = $properties['image_height'];
    $back_button_label = $properties['back_button_label'];
    $catalog_detail_page_id = $properties['catalog_detail_page_id'];

    $layout_type = get_layout_type($page_id);
    
    $current_page_name = get_page_name($page_id);
    $catalog_detail_page_name = get_page_name($catalog_detail_page_id);
    
    // get prices for products that have been discounted by offers, so we can show the discounted price
    $discounted_product_prices = get_discounted_product_prices();
    
    // if there is no product group selected for this catalog page, then get top-level product group
    if ($product_group_id == 0) {
        $query = "SELECT id FROM product_groups WHERE parent_id = '0'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $product_group_id = $row['id'];
    }
    
    $current_product_group_id = 0;

    if ($layout_type == 'system') {
    
        $search_query = '';

        // If the clear button was not clicked, then get search query value.
        if (isset($_GET[$page_id . '_simple_clear']) == false) {
            // if there is a specific query for this page, then use it
            if (isset($_GET[$page_id . '_query']) == TRUE) {
                $search_query = trim($_GET[$page_id . '_query']);
                
            // else if there is a general query, then use it (this is for backwards compatibility)
            } else if (isset($_GET['query']) == TRUE) {
                $search_query = trim($_GET['query']);
            }
        }
        
        // if there is not a search query, then set mode and prepare for mode
        if ($search_query == '') {
            $mode = 'browse';
            
            // if a page name has been passed in the query string and if it contains a forward slash, then get the product group id
            if (mb_strpos($_GET['page'], '/') !== FALSE) {
                // get product group address name
                $product_group_address_name = mb_substr(mb_substr($_GET['page'], mb_strpos($_GET['page'], '/')), 1);
                
                // get the product group id
                $query = "SELECT id FROM product_groups WHERE address_name = '" . escape($product_group_address_name) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $current_product_group_id = $row['id'];
                
            // else if there is a default product group set, then use that
            } elseif ($product_group_id != 0) {
                $current_product_group_id = $product_group_id;
                
            // else no product group can be found, so get top-level product group
            } else {
                $query = "SELECT id FROM product_groups WHERE parent_id = '0'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $current_product_group_id = $row['id'];
            }
            
            // get product group information and determine if product group exists
            $query =
                "SELECT
                    enabled,
                    full_description,
                    code
                FROM product_groups
                WHERE id = '" . escape($current_product_group_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if a product group cannot be found, then output error
            if (mysqli_num_rows($result) == 0) {
                return error('Sorry, the item could not be found.', 404);
            }
            
            $row = mysqli_fetch_assoc($result);

            // If the product group is disabled, then output error.
            if (!$row['enabled']) {
                return error('Sorry, the item is not currently available.', 410);
            }

            $full_description = $row['full_description'];

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
            //if product image xref or product group  xref exist. this mean this selected multiple product image
            //and if code has ^^image_loop_start^^ and ^^image_url^^ and ^^image_loop_end^^. with these we can make an ease loop
            if( (mysqli_num_rows($image_results) != 0)&&
                (strpos($row['code'], '^^image_url^^') !== false)&&
                (strpos($row['code'], '^^image_loop_start^^') !== false)&&
                (strpos($row['code'], '^^image_loop_end^^') !== false)
            ){        
                $code_header_position = strpos($row['code'], '^^image_loop_start^');//number
                $code_content_position = strpos($row['code'], '^^image_url^^');
                $code_footer_position = strpos($row['code'], '^^image_loop_end^');//number
                $code_header = substr( $row['code'], 0 ,strpos($row['code'], '^^image_loop_start^') );
                $code_content_raw = substr( $row['code'], (strpos($row['code'], '^^image_loop_start^') + 20) , (strpos($row['code'], '^^image_loop_end^') - strpos($row['code'], '^^image_loop_start^')  - 20) );
                $code_footer = substr($row['code'], strpos($row['code'], '^^image_loop_end^') + 18 );

                while ($image = mysqli_fetch_assoc($image_results)){
                    $code_content .= str_replace("^^image_url^^", OUTPUT_PATH . $image['file_name'] , $code_content_raw );
                }

                $item['code'] = $code_header.$code_content.$code_footer;

            }else{
                // else there is only one image selected and no spacial code elements so we output code directly.
                $item['code'] = $row['code'];
            }
            
        // else search is enabled and there is a search query, so set mode
        } else {
            $mode = 'search';
        }
        
        $output_image_width = '';
        
        // if the image width setting is greater than 0, then prepare to constrain image width
        if ($image_width > 0) {
            $output_image_width = ' width="' . $image_width . '"';
        }
        
        $output_image_height = '';
        
        // if the image height setting is greater than 0, then prepare to constrain image height
        if ($image_height > 0) {
            $output_image_height = ' height="' . $image_height . '"';
        }
        
        $output_menu_form = '';
        
        // If the catalog menu is turned on then output product group pick list
        if ($menu == '1') {
            // Get the parent product group's name
            $query = "SELECT name FROM product_groups WHERE id = '" . escape($product_group_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $parent_product_group_name = $row['name'];
            
            // output product group pick list
            $output_menu_form = '
                <div class="catalog_browse" style="float: left">
                    <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/browse_catalog_menu.php" method="get" style="margin: 0em 0em 1em 0em">
                    <input type="hidden" name="current_page_id" value="' . h($page_id) . '" />
                    <input type="hidden" name="previous_url_id" value="' . h(generate_url_id()) . '" />
                    <select name="product_group_id" class="software_select"><option value="' . $product_group_id . '">' . h($parent_product_group_name) . '</option>' .
                        get_product_group_options($current_product_group_id, $product_group_id, 0, 1, $product_groups = array(), $include_select_product_groups = FALSE, 'text', false, false) . '</select> <input type="submit" name="submit" value="Browse" class="software_input_submit_small_secondary" />
                    </form>
                </div>';
        }
        
        $output_search_form = '';
        
        // If search is enabled, then prepare to output search form
        if ($search == '1') {
            // get current URL parts in order to deal with query string parameters
            $url_parts = parse_url(get_request_uri());
            
            // put query string parameters into an array
            parse_str($url_parts['query'], $query_string_parameters);
            
            $output_hidden_fields = '';
            
            // loop through the query string parameters in order to prepare hidden fields for each query string parameter,
            // so that we don't lose any when the form is submitted
            foreach ($query_string_parameters as $name => $value) {
                // If this is not the specific query parameter (already going to be a field for that)
                // and not the previous url id, and not the clear field,
                // then add hidden field for it
                if (
                    ($name != $page_id . '_query')
                    && ($name != 'previous_url_id')
                    && ($name != $page_id . '_simple_clear')
                ) {
                    $output_hidden_fields .= '<input type="hidden" name="' . h($name) . '" value="' . h($value) . '" />' . "\n";
                }
            }

            $output_clear_button = '';
            
            // If there is a search query, then output clear button.
            if ($search_query != '') {
                $output_clear_button = '<input type="submit" name="' . $page_id . '_simple_clear" value="" class="clear" title="Clear">';
            }
            
            $output_search_form =
                '<div class="catalog_search mobile_left mobile_width" style="float: right">
                    <form action="" method="get" class="mobile_align_left" style="text-align: right; margin: 0em 0em 1em 0em">' .
                        $output_hidden_fields . '
                        <input type="hidden" name="previous_url_id" value="' . h(generate_url_id()) . '" />
                        <span class="search">
                            <span class="simple">
                                <input type="text" name="' . $page_id . '_query" value="' . h($search_query) . '" placeholder="Search" class="software_input_text mobile_fixed_width query" style="margin-bottom: 0 !important">
                                <input type="submit" name="' . $page_id . '_simple_submit" title="Search" value="" class="submit">
                                ' . $output_clear_button . '
                            </span>
                        </span>
                    </form>
                </div>';
        }
        
        $output_menu_and_search_forms = '';
        
        // If the menu is enabled and the search is enabled, then prepare to output the menu and the search forms in a table
        if (($menu == '1') && ($search == '1')) {
            $output_menu_and_search_forms = 
                '<div>' .
                    $output_menu_form .
                    $output_search_form .
                '</div>';
            
        // Else if only the menu is enabled, then prepare to output the menu form
        } else if ($menu == '1') {
            $output_menu_and_search_forms = $output_menu_form;
            
        // Else if only search is enabled, then prepare to output search form
        } else if ($search == '1') {
            $output_menu_and_search_forms = $output_search_form;
        }

        // Add clear to menu and search divs
        $output_menu_and_search_forms .= '<div style="clear: both"></div>';
        
        $output_full_description = '';
        
        // if mode is browse and there is a full description, then output full description
        if (($mode == 'browse') && ($full_description != '')) {
            // if page is in edit mode, then output edit button for images
            if ($editable == TRUE) {
                $full_description = add_edit_button_for_images('product_group', $current_product_group_id, $full_description, 'full_description');
            }
            $output_full_description = '<div class="full_description">' . $full_description . '</div>';
        }
        
        // prepare cell width that will be used for featured and new items right sidebar and the more items table below it
        $output_cell_width = '';
        
        // if there is more than one column, then prepare cell width, so that columns have equal widths
        if ($number_of_columns > 1) {
            // if there is an image width set then use it for the cell width
            if ($image_width != 0) {
                if ($device_type == 'mobile') {
                    $output_cell_width = 'width: ' . strval(intval($image_width)) . 'px;';
                } else {
                    $output_cell_width = 'width: ' . $image_width . 'px;';
                }
            // else there is not an image width set, so set percentage
            } else {
                $output_cell_width = 'width: ' . round(100 / $number_of_columns, 2) . '%;';
            }
        }

        // prepare cell height for mobile to be the thumbnail height + 100px (an approximation) to extend beyond products fields and line up any wrapping cells
        // also prepare spacer value for mobile divs
        $output_cell_height = '';
        $output_mobile_spacer = '';

        if ($device_type == 'mobile') {
            $height_intval = 100 + intval($image_height);
            $output_cell_height = 'height: ' . strval($height_intval) . 'px;';
            $output_mobile_spacer = '&nbsp;&nbsp;';
        }
        
        $output_featured_and_new_items = '';
        $output_more_items_heading = '';
        
        // if mode is browse and featured or new items are enabled, then output featured and new items
        if (($mode == 'browse') && (($number_of_featured_items > 0) || ($number_of_new_items > 0))) {
            // get featured and new items
            $featured_and_new_items = get_featured_and_new_items($current_product_group_id);
            
            // initialize arrays for storing featured and new items
            $featured_items = array();
            $new_items = array();
            
            // if featured items is enabled, then add featured items to array
            if ($number_of_featured_items > 0) {
                $featured_items = $featured_and_new_items['featured'];
            }
            
            // if new items is enabled, then add new items to array
            if ($number_of_new_items > 0) {
                $new_items = $featured_and_new_items['new'];
            }
            
            // if there is at least one featured or new item, then continue
            if ((count($featured_items) > 0) || (count($new_items) > 0)) {
                // if featured items is enabled, then sort the featured items and then remove extras
                if ($number_of_featured_items > 0) {
                    // initialize arrays that will be used for sorting featured items
                    $item_featured_sort_orders = array();
                    $item_names = array();
                    
                    // loop through the featured items in order to prepare arrays for sorting
                    foreach ($featured_items as $featured_item) {
                        $item_featured_sort_orders[] = $featured_item['featured_sort_order'];
                        $item_names[] = mb_strtolower($featured_item['name']);
                    }
                    
                    // sort the items by featured sort order and then by name
                    array_multisort($item_featured_sort_orders, $item_names, $featured_items);
                    
                    // remove extra featured items that there is not room for
                    $featured_items = array_slice($featured_items, 0, $number_of_featured_items);
                    
                    // initialize array in order to remove duplicate products
                    $featured_product_ids = array();
                    
                    // loop through the featured items in order to remove duplicate products
                    foreach ($featured_items as $key => $featured_item) {
                        // if this item is a product, then continue
                        if ($featured_item['type'] == 'product') {
                            // if this product has not already been added to the featured product ids array, then add it
                            if (in_array($featured_item['id'], $featured_product_ids) == FALSE) {
                                $featured_product_ids[] = $featured_item['id'];
                                
                            // else this product has already been added to the featured product ids array, so it is a duplicate, so remove it
                            } else {
                                unset($featured_items[$key]);
                            }
                        }
                    }
                }
                
                // if new items is enabled, then sort the new items and then remove extras
                if ($number_of_new_items > 0) {
                    // initialize arrays that will be used for sorting new items
                    $item_new_dates = array();
                    $item_names = array();
                    
                    // loop through the new items in order to prepare arrays for sorting
                    foreach ($new_items as $new_item) {
                        $item_new_dates[] = $new_item['new_date'];
                        $item_names[] = mb_strtolower($new_item['name']);
                    }
                    
                    // sort the items by new date, descending, and then by name
                    array_multisort($item_new_dates, SORT_DESC, $item_names, $new_items);
                    
                    // remove extra new items that there is not room for
                    $new_items = array_slice($new_items, 0, $number_of_new_items);
                    
                    // initialize array in order to remove duplicate products
                    $new_product_ids = array();
                    
                    // loop through the new items in order to remove duplicate products
                    foreach ($new_items as $key => $new_item) {
                        // if this item is a product, then continue
                        if ($new_item['type'] == 'product') {
                            // if this product has not already been added to the new product ids array, then add it
                            if (in_array($new_item['id'], $new_product_ids) == FALSE) {
                                $new_product_ids[] = $new_item['id'];
                                
                            // else this product has already been added to the new product ids array, so it is a duplicate, so remove it
                            } else {
                                unset($new_items[$key]);
                            }
                        }
                    }
                }
                
                // if there is at least one featured item, then prepare top item and prepare to output top item heading for a featured item
                if (count($featured_items) > 0) {
                    $top_item_type = 'featured';
                    $top_item = $featured_items[0];
                    $output_top_item_heading = 'Featured';
                    
                // else there is not at least one featured item, so prepare top item and prepare to output top item heading for a new item
                } else {
                    $top_item_type = 'new';
                    $top_item = $new_items[0];
                    $output_top_item_heading = 'New';
                }
                
                $output_top_item_full_description = '';
                
                // if the top item has a full description, then prepare and output full description
                if ($top_item['full_description'] != '') {
                    // if user has edit access, then add the edit button to images
                    if ($editable == TRUE) {
                        $top_item['full_description'] = add_edit_button_for_images(str_replace(' ' , '_', $top_item['type']), $top_item['id'], $top_item['full_description'], 'full_description');
                    }
                    
                    $output_top_item_full_description = '<div class="full_description">' . $top_item['full_description'] . '</div>';
                }
                
                $output_top_item_keywords = '';
                
                // if there are keywords to output, then prepare to output keywords
                if ($top_item['keywords'] != '') {
                    // split keywords by comma
                    $keywords = explode(',', $top_item['keywords']);
                    
                    $output_keyword_links = '';
                    
                    // loop through keyword parts, in order to prepare output for keyword links
                    foreach ($keywords as $keyword) {
                        $keyword = trim($keyword);
                        
                        // if the keyword is not blank, then continue to prepare output for keyword links
                        if ($keyword != '') {
                            // if a link has already been added, then add a comma and a space
                            if ($output_keyword_links != '') {
                                $output_keyword_links .= ', ';
                            }
                            
                            $output_keyword_links .= '<a href="' . OUTPUT_PATH . h(encode_url_path($current_page_name)) . '?query=' . urlencode($keyword) . '&previous_url_id=' . urlencode(generate_url_id()) . '">' . h($keyword) . '</a>';
                        }
                    }
                    
                    // if there is at least one keyword link to output, then prepare to output keywords
                    if ($output_keyword_links != '') {
                        $output_top_item_keywords = '<div class="keywords">Keywords: ' . $output_keyword_links . '</div>';
                    }
                }
                
                $output_top_item_price = '';
                
                // if this item is a product group, then get price range for all product groups and products in this product group
                if ($top_item['type'] == 'product group') {
                    $price_range = get_price_range($top_item['id'], $discounted_product_prices);
                    
                    // if there are non-donation products in product group, then output price range
                    if ($price_range['non_donation_products_exist'] == true) {
                        // if the smallest price and largest price are the same, then just output a price without a range
                        if ($price_range['smallest_price'] == $price_range['largest_price']) {
                            // if there is only one product and it is discounted, then prepare to show original price and discounted price
                            if (($price_range['number_of_products'] == 1) && ($price_range['discounted_products_exist'] == TRUE)) {
                                $output_top_item_price = '<div class="price">' . prepare_price_for_output($price_range['original_price'], TRUE, $price_range['smallest_price'], 'html') . '</div>';
                                
                            // else there is more than one product or there are no discounted products, so prepare to just show original price
                            } else {
                                // if this product group has a select display type and there are discounted products in it, then prepare to output single price with discounted styling
                                if (($top_item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                    $output_top_item_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                    
                                // else this product group does not have a select display type or there are no discounted products in it, so prepare to output single price without discounted styling
                                } else {
                                    $output_top_item_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                }
                            }
                            
                        // else the smallest price and largest price are not the same, so output price range
                        } else {
                            // if this product group has a select display type and there are discounted products in it, then prepare to output price range with discounted styling
                            if (($top_item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                $output_top_item_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                
                            // else this product group does not have a select display type or there are no discounted products in it, so prepare to output price without discounted styling
                            } else {
                                $output_top_item_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                            }
                        }
                    }
                    
                // else this item is a product, so if product is not a donation, then output product's price
                } elseif ($top_item['selection_type'] != 'donation') {
                    // assume that the product is not discounted, until we find out otherwise
                    $discounted = FALSE;
                    $discounted_price = '';

                    // if the product is discounted, then prepare to show that
                    if (isset($discounted_product_prices[$top_item['id']]) == TRUE) {
                        $discounted = TRUE;
                        $discounted_price = $discounted_product_prices[$top_item['id']];
                    }
                    
                    $output_top_item_price = '<div class="price">' . prepare_price_for_output($top_item['price'], $discounted, $discounted_price, 'html') . '</div>';
                }
                
                $output_top_item_more_detail = '';
                
                // if this item is a product group and this product group is set to display its contents on a catalog page, then prepare link to catalog page
                if (($top_item['type'] == 'product group') && ($top_item['display_type'] == 'browse')) {
                    $output_top_item_more_detail = '<div class="more_detail"><a href="' . OUTPUT_PATH . h(encode_url_path($current_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($top_item['id'], $top_item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">More Detail</a></div>';
                    
                // else this item is a product or a product group that is set to display its contents on a catalog detail page,
                // so if there is a catalog detail page selected, then prepare link to catalog detail page
                } elseif ($catalog_detail_page_id != 0) {
                    
                    $output_top_item_more_detail = '<div class="more_detail"><a href="' . OUTPUT_PATH . h(encode_url_path($catalog_detail_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($top_item['id'], $top_item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">More Detail</a></div>';
                }
                
                $output_edit_container_start = '';
                $output_edit_container_end = '';
                
                // if items are editable, prepare to add edit container around item
                if ($editable) {
                    // If item is a product group
                    if ($top_item['type'] == 'product group') {
                        // Output link to edit product group
                        $output_edit_button_link = 'edit_product_group.php';
                        // Output tooltip title
                        $tool_tip_title = 'Product Group';
                        
                    // else if the items is a product
                    } else {
                        // Output link to edit product
                        $output_edit_button_link = 'edit_product.php';
                        // Output tooltip title
                        $tool_tip_title = 'Product';
                    }
                    
                    // If there is a short description, output short description in tooltip
                    if ($top_item['short_description'] != '') {
                        $output_tooltip_name = $top_item['short_description'];
                        
                    // Else output name in tooltip
                    } else {
                        $output_tooltip_name = $top_item['name'];
                    }
                    
                    // prepare edit container
                    $output_edit_container_start = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/' . $output_edit_button_link . '?id=' . $top_item['id'] . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; font-style: normal; font-weight: normal; color: #FFFFFF; font-size: 12px; text-decoration: none; border: none; padding: 5px 8px; margin: -1px 25px 0 -1px; z-index: 10; line-height: normal; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $tool_tip_title . ': ' . h($output_tooltip_name) . '">Edit</a>';
                    $output_edit_container_end = '</div>';
                }
                
                $output_featured_items .= $output_edit_container_start;

                if ($device_type == 'mobile') {
                    $output_featured_items .= '<div class="item mobile_left" style="' . $output_cell_width . $output_cell_height . '">';

                } else {
                    $output_featured_items .= '<div class="item mobile_left">';
                }

                $output_featured_items .=
                            $output_image . '
                            ' . $output_short_description . '
                            ' . $output_price . '
                        </div>
                        <div style="clear: both"></div>
                    ' . $output_edit_container_end;
                
                // prepare to output top item
                $output_top_item =
                    '<div class="top_item">
                        <div class="heading" style="border: none"><h4>' . $output_top_item_heading . '</h4></div>
                        ' . $output_edit_container_start . '
                        ' . $output_top_item_full_description . '
                        ' . $output_top_item_keywords . '
                        ' . $output_top_item_price . '
                        ' . $output_top_item_more_detail . '
                        ' . $output_edit_container_end . '
                    </div>';
                
                // if there is more than one featured and new items, then prepare to output table for them
                if ((count($featured_items) + count($new_items)) > 1) {
                    $output_featured_items = '';
                    
                    // if there is more than one featured item, then prepare to output featured items
                    if (count($featured_items) > 1) {
                        $output_featured_items .= '<div class="heading mobile_align_left" style="border: none"><h4>Featured</h4></div>';
                        
                        // loop through featured items in order to output them
                        foreach ($featured_items as $key => $featured_item) {
                            // if this is not the first featured item, then continue to output item (because the first featured item has already been outputted as the top item)
                            if ($key != 0) {
                                $output_link_start = '';
                                $output_link_end = '';
                                
                                // if this item is a product group and this product group is set to display its contents on a catalog page, then prepare link to catalog page
                                if (($featured_item['type'] == 'product group') && ($featured_item['display_type'] == 'browse')) {
                                    $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path($current_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($featured_item['id'], $featured_item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                                    $output_link_end = '</a>';
                                    
                                // else this item is a product or a product group that is set to display its contents on a catalog detail page,
                                // so if there is a catalog detail page selected, then prepare link to catalog detail page
                                } elseif ($catalog_detail_page_id != 0) {
                                    $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path($catalog_detail_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($featured_item['id'], $featured_item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                                    $output_link_end = '</a>';
                                }
                                
                                $output_image = '';
                                
                                // if there is an image name then output image
                                if ($featured_item['image_name'] != '') {
                                    $output_image = '<div class="image">' . $output_link_start . '<img class="image-primary" src="' . OUTPUT_PATH . h(encode_url_path($featured_item['image_name'])) . '"' . $output_image_width . $output_image_height . ' alt="" border="0" />' . $output_link_end . '</div>';
                                    
                                    // if edit mode is on then add the edit button to images in content
                                    if ($editable == TRUE) {
                                        $output_image = add_edit_button_for_images(str_replace(' ' , '_', $featured_item['type']), $featured_item['id'], $output_image, 'image_name');
                                    }
                                }
                                
                                $output_short_description = '';
                                
                                // if there is a short description then output short description
                                if ($featured_item['short_description'] != '') {
                                    $output_short_description = '<div class="short_description">' . $output_link_start . h($featured_item['short_description']) . $output_link_end . '</div>';
                                }
                                
                                $output_price = '';
                                
                                // if this item is a product group, then get price range for all product groups and products in this product group
                                if ($featured_item['type'] == 'product group') {
                                    $price_range = get_price_range($featured_item['id'], $discounted_product_prices);
                                    
                                    // if there are non-donation products in product group, then output price range
                                    if ($price_range['non_donation_products_exist'] == true) {
                                        // if the smallest price and largest price are the same, then just output a price without a range
                                        if ($price_range['smallest_price'] == $price_range['largest_price']) {
                                            // if there is only one product and it is discounted, then prepare to show original price and discounted price
                                            if (($price_range['number_of_products'] == 1) && ($price_range['discounted_products_exist'] == TRUE)) {
                                                $output_price = '<div class="price">' . prepare_price_for_output($price_range['original_price'], TRUE, $price_range['smallest_price'], 'html') . '</div>';
                                                
                                            // else there is more than one product or there are no discounted products, so prepare to just show original price
                                            } else {
                                                // if this product group has a select display type and there are discounted products in it, then prepare to output single price with discounted styling
                                                if (($featured_item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                                    $output_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                                    
                                                // else this product group does not have a select display type or there are no discounted products in it, so prepare to output single price without discounted styling
                                                } else {
                                                    $output_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                                }
                                            }
                                            
                                        // else the smallest price and largest price are not the same, so output price range
                                        } else {
                                            // if this product group has a select display type and there are discounted products in it, then prepare to output price range with discounted styling
                                            if (($featured_item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                                $output_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                                
                                            // else this product group does not have a select display type or there are no discounted products in it, so prepare to output price without discounted styling
                                            } else {
                                                $output_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                            }
                                        }
                                    }
                                    
                                // else this item is a product, so if product is not a donation, then output product's price
                                } elseif ($featured_item['selection_type'] != 'donation') {
                                    // assume that the product is not discounted, until we find out otherwise
                                    $discounted = FALSE;
                                    $discounted_price = '';

                                    // if the product is discounted, then prepare to show that
                                    if (isset($discounted_product_prices[$featured_item['id']]) == TRUE) {
                                        $discounted = TRUE;
                                        $discounted_price = $discounted_product_prices[$featured_item['id']];
                                    }

                                    $output_price = '<div class="price">' . prepare_price_for_output($featured_item['price'], $discounted, $discounted_price, 'html') . '</div>';
                                }
                                
                                $output_edit_container_start = '';
                                $output_edit_container_end = '';
                                
                                // if items are editable, prepare to add edit container around item
                                if ($editable) {
                                    // If item is a product group
                                    if ($featured_item['type'] == 'product group') {
                                        // Output link to edit product group
                                        $output_edit_button_link = 'edit_product_group.php';
                                        // Output tooltip title
                                        $tool_tip_title = 'Product Group';
                                        
                                    // else if the items is a product
                                    } else {
                                        // Output link to edit product
                                        $output_edit_button_link = 'edit_product.php';
                                        // Output tooltip title
                                        $tool_tip_title = 'Product';
                                    }
                                    
                                    // If there is a short description, output short description in tooltip
                                    if ($featured_item['short_description'] != '') {
                                        $output_tooltip_name = $featured_item['short_description'];
                                        
                                    // Else output name in tooltip
                                    } else {
                                        $output_tooltip_name = $featured_item['name'];
                                    }
                                    
                                    // prepare edit container
                                    $output_edit_container_start = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/' . $output_edit_button_link . '?id=' . $featured_item['id'] . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; font-style: normal; font-weight: normal; color: #FFFFFF; font-size: 12px; text-decoration: none; border: none; padding: 5px 8px; margin: -1px 25px 0 -1px; z-index: 10; line-height: normal; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $tool_tip_title . ': ' . h($output_tooltip_name) . '">Edit</a>';
                                    $output_edit_container_end = '</div>';
                                }
                                
                                $output_featured_items .= $output_edit_container_start;

                                if ($device_type == 'mobile') {
                                    $output_featured_items .= '<div class="item mobile_left" style="' . $output_cell_width . $output_cell_height . '">';

                                } else {
                                    $output_featured_items .= '<div class="item mobile_left">';
                                }

                                $output_featured_items .=
                                            $output_image . '
                                            ' . $output_short_description . '
                                            ' . $output_price . '
                                        </div>
                                        <div style="clear: both"></div>
                                    ' . $output_edit_container_end;
                            }
                        }
                    }
                    
                    $output_new_items = '';
                    
                    // if there is more than one new item or the top item was featured and there is just one new item, then prepare to output new items
                    if ((count($new_items) > 1) || (($top_item_type == 'featured') && (count($new_items) > 0))) {
                        // if the top item was a new item, then output a certain heading
                        if ($top_item_type == 'new') {
                            $output_new_items .= '<div class="heading mobile_align_left" style="border: none"><h4>New</h4></div>';
                            
                        // else the top item was not a new item, so output a different heading
                        } else {
                            $output_new_items .= '<div class="heading mobile_align_left" style="border: none"><h4>New</h4></div>';
                        }
                        
                        // loop through new items in order to output them
                        foreach ($new_items as $key => $new_item) {
                            // if this is not the first new item or the top item was not a new item, then continue to output item
                            if (($key != 0) || ($top_item_type != 'new')) {
                                $output_link_start = '';
                                $output_link_end = '';
                                
                                // if this item is a product group and this product group is set to display its contents on a catalog page, then prepare link to catalog page
                                if (($new_item['type'] == 'product group') && ($new_item['display_type'] == 'browse')) {
                                    $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path($current_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($new_item['id'], $new_item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                                    $output_link_end = '</a>';
                                    
                                // else this item is a product or a product group that is set to display its contents on a catalog detail page,
                                // so if there is a catalog detail page selected, then prepare link to catalog detail page
                                } elseif ($catalog_detail_page_id != 0) {
                                    // if item is a product group, then set id name
                                    if ($new_item['type'] == 'product group') {
                                        $id_name = 'product_group_id';
                                        
                                    // else item is a product, so set id name
                                    } else {
                                        $id_name = 'product_id';
                                    }
                                    
                                    $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path($catalog_detail_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($new_item['id'], $new_item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                                    $output_link_end = '</a>';
                                }
                                
                                $output_image = '';
                                
                                // if there is an image name then output image
                                if ($new_item['image_name'] != '') {
                                    $output_image = '<div class="image">' . $output_link_start . '<img class="image-primary" src="' . OUTPUT_PATH . h(encode_url_path($new_item['image_name'])) . '"' . $output_image_width . $output_image_height . ' alt="" border="0" />' . $output_link_end . '</div>';
                                    
                                    // if edit mode is on then add the edit button to images in content
                                    if ($editable == TRUE) {
                                        $output_image = add_edit_button_for_images(str_replace(' ' , '_', $new_item['type']), $new_item['id'], $output_image, 'image_name');
                                    }
                                }
                                
                                $output_short_description = '';
                                
                                // if there is a short description then output short description
                                if ($new_item['short_description'] != '') {
                                    $output_short_description = '<div class="short_description">' . $output_link_start . h($new_item['short_description']) . $output_link_end . '</div>';
                                }
                                
                                $output_price = '';
                                
                                // if this item is a product group, then get price range for all product groups and products in this product group
                                if ($new_item['type'] == 'product group') {
                                    $price_range = get_price_range($new_item['id'], $discounted_product_prices);
                                    
                                    // if there are non-donation products in product group, then output price range
                                    if ($price_range['non_donation_products_exist'] == true) {
                                        // if the smallest price and largest price are the same, then just output a price without a range
                                        if ($price_range['smallest_price'] == $price_range['largest_price']) {
                                            // if there is only one product and it is discounted, then prepare to show original price and discounted price
                                            if (($price_range['number_of_products'] == 1) && ($price_range['discounted_products_exist'] == TRUE)) {
                                                $output_price = '<div class="price">' . prepare_price_for_output($price_range['original_price'], TRUE, $price_range['smallest_price'], 'html') . '</div>';
                                                
                                            // else there is more than one product or there are no discounted products, so prepare to just show original price
                                            } else {
                                                // if this product group has a select display type and there are discounted products in it, then prepare to output single price with discounted styling
                                                if (($new_item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                                    $output_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                                    
                                                // else this product group does not have a select display type or there are no discounted products in it, so prepare to output single price without discounted styling
                                                } else {
                                                    $output_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                                }
                                            }
                                            
                                        // else the smallest price and largest price are not the same, so output price range
                                        } else {
                                            // if this product group has a select display type and there are discounted products in it, then prepare to output price range with discounted styling
                                            if (($new_item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                                $output_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                                
                                            // else this product group does not have a select display type or there are no discounted products in it, so prepare to output price without discounted styling
                                            } else {
                                                $output_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                            }
                                        }
                                    }
                                    
                                // else this item is a product, so if product is not a donation, then output product's price
                                } elseif ($new_item['selection_type'] != 'donation') {
                                    // assume that the product is not discounted, until we find out otherwise
                                    $discounted = FALSE;
                                    $discounted_price = '';

                                    // if the product is discounted, then prepare to show that
                                    if (isset($discounted_product_prices[$new_item['id']]) == TRUE) {
                                        $discounted = TRUE;
                                        $discounted_price = $discounted_product_prices[$new_item['id']];
                                    }
                                    
                                    $output_price = '<div class="price">' . prepare_price_for_output($new_item['price'], $discounted, $discounted_price, 'html') . '</div>';
                                }
                                
                                $output_edit_container_start = '';
                                $output_edit_container_end = '';
                                
                                // if items are editable, prepare to add edit container around item
                                if ($editable) {
                                    // If item is a product group
                                    if ($new_item['type'] == 'product group') {
                                        // Output link to edit product group
                                        $output_edit_button_link = 'edit_product_group.php';
                                        // Output tooltip title
                                        $tool_tip_title = 'Product Group';
                                        
                                    // else if the items is a product
                                    } else {
                                        // Output link to edit product
                                        $output_edit_button_link = 'edit_product.php';
                                        // Output tooltip title
                                        $tool_tip_title = 'Product';
                                    }
                                    
                                    // If there is a short description, output short description in tooltip
                                    if ($new_item['short_description'] != '') {
                                        $output_tooltip_name = $new_item['short_description'];
                                        
                                    // Else output name in tooltip
                                    } else {
                                        $output_tooltip_name = $new_item['name'];
                                    }
                                    
                                    // prepare edit container
                                    $output_edit_container_start = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/' . $output_edit_button_link . '?id=' . $new_item['id'] . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-style: normal; font-weight: normal; font-size: 12px; text-decoration: none; border: none; padding: 5px 8px; margin: -1px 25px 0 -1px; z-index: 10; line-height: normal; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $tool_tip_title . ': ' . h($output_tooltip_name) . '">Edit</a>';
                                    $output_edit_container_end = '</div>';
                                }
                                
                                $output_new_items .= $output_edit_container_start;

                                if ($device_type == 'mobile') {
                                    $output_new_items .= '<div class="item mobile_left" style="' . $output_cell_width . $output_cell_height . '">';

                                } else {
                                    $output_new_items .= '<div class="item mobile_left">';
                                }

                                $output_new_items .=
                                            $output_image . '
                                            ' . $output_short_description . '
                                            ' . $output_price . '
                                        </div>
                                        <div class="mobile_no_clear" style="clear: both"></div>
                                    ' . $output_edit_container_end;
                            }
                        }
                    }

                    // don't set width for mobile
                    if ($device_type == 'mobile') {
                        $output_new_cell_width = '';
                    } else {
                        $output_desktop_cell_width = $output_cell_width;
                    }
                    
                    $output_featured_and_new_items =
                        '<table class="featured_and_new_item_table" style="width: 100%">
                            <tr>
                                <td class="mobile_left" style="vertical-align: top">
                                    ' . $output_top_item . '
                                </td>
                                <td class="mobile_left mobile_width" style="text-align: center; vertical-align: top; ' . $output_desktop_cell_width . '">
                                    ' . $output_featured_items . '
                                    <div style="clear: both"></div>
                                    ' . $output_new_items . '
                                </td>
                            </tr>
                        </table>';
                
                // else there is just one featured and new item, so prepare to output top item
                } else {
                    $output_featured_and_new_items = $output_top_item;
                }
                
                $output_more_items_heading = '<div class="heading" style="border: none; clear: both"></div>';
            }
        }
        
        $output_search_heading = '';
        
        // if mode is search, then output search heading
        if ($mode == 'search') {
            $output_search_heading = '';
        }
        
        // prepare to get items that will be displayed in catalog
        $items = array();
        
        // if mode is browse, then get items that are in this product group
        if ($mode == 'browse') {
            // initialize arrays that will store data for sorting
            $item_sort_orders = array();
            $item_names = array();
            
            // get all products groups in this product group
            $query =
                "SELECT
                    id,
                    name,
                    sort_order,
                    short_description,
                    keywords,
                    image_name,
                    display_type
                FROM product_groups
                WHERE
                    (parent_id = '" . e($current_product_group_id) . "')
                    AND (enabled = '1')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // loop through all product groups in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'short_description' => $row['short_description'],
                    'keywords' => $row['keywords'],
                    'image_name' => $row['image_name'],
                    'display_type' => $row['display_type'],
                    'type' => 'product group'
                );
                
                // add data to sorting arrays
                $item_sort_orders[] = $row['sort_order'];
                $item_names[] = mb_strtolower($row['name']);
            }
            
            // get all products that are in product group
            $query =
                "SELECT
                    products.id,
                    products.name,
                    products_groups_xref.sort_order,
                    products.short_description,
                    products.keywords,
                    products.image_name,
                    products.price,
                    products.selection_type,
                    inventory,
                    inventory_quantity
                FROM products_groups_xref
                LEFT JOIN products ON products.id = products_groups_xref.product
                WHERE
                    (products_groups_xref.product_group = '" . escape($current_product_group_id) . "')
                    AND (products.enabled = '1')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // loop through all products in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'short_description' => $row['short_description'],
                    'keywords' => $row['keywords'],
                    'image_name' => $row['image_name'],
                    'price' => $row['price'],
                    'inventory' => $row['inventory'],
                    'inventory_quantity' => $row['inventory_quantity'],
                    'type' => 'product'
                );
                
                // add data to sorting arrays
                $item_sort_orders[] = $row['sort_order'];
                $item_names[] = mb_strtolower($row['name']);
            }
            
            // sort the items by sort order and then by name
            array_multisort($item_sort_orders, $item_names, $items);
            
        // else mode is search so if the search query is not blank then get items that match search
        } else if ($search_query != '') {
            $items = get_catalog_search_results($search_query, $product_group_id);
        }
        
        $output_search_result_message = '';
        
        // if mode is search and at least one item was found, then prepare to output search result message
        if (($mode == 'search') && (count($items) > 0)) {
            $plural_suffix = '';
            
            // if the number of items is greater than 1, then prepare to output plural suffix
            if (count($items) > 1) {
                $plural_suffix = 's';
            }
            
            $output_search_result_message = '<div style="margin-bottom: 1em; font-weight: bold">Found ' . number_format(count($items)) . ' item' . $plural_suffix . ' for "' . h($search_query) . '".</div>';
        }
        
        $output_items = '';
        
        // if there is at least one item and number_of_columns is greater than 0, output items
        if ((count($items) > 0) && ($number_of_columns > 0)) {
            // initialize variable that will store output for item rows
            $output_item_rows = '';
            
            // loop through all items in order to prepare output
            foreach ($items as $key => $item) {
                $item_number = $key + 1;
                
                $output_start_row = '';
                
                // if this is the first item in the row, then start row
                if ((($item_number % $number_of_columns) == 1) || ($number_of_columns == 1)) {
                    $output_start_row .= '<tr>';
                }

                $output_keywords = ($item['keywords'] != '') ? ' data-product-keywords="' . h($item['keywords']) . '"' : '';
                
                $output_link_start = '';
                $output_link_end = '';
                
                // if this item is a product group and this product group is set to display its contents on a catalog page, then prepare link to catalog page
                if (($item['type'] == 'product group') && ($item['display_type'] == 'browse')) {
                    $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path($current_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($item['id'], $item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                    $output_link_end = '</a>';
                    
                // else this item is a product or a product group that is set to display its contents on a catalog detail page,
                // so if there is a catalog detail page selected, then prepare link to catalog detail page
                } elseif ($catalog_detail_page_id != 0) {
                    // if item is a product group, then set id name
                    if ($item['type'] == 'product group') {
                        $id_name = 'product_group_id';
                        
                    // else item is a product, so set id name
                    } else {
                        $id_name = 'product_id';
                    }
                    
                    $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path($catalog_detail_page_name)) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($item['id'], $item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                    $output_link_end = '</a>';
                }
                
                $output_image = '';
                
                // if there is an image name then output image
                if ($item['image_name'] != '') {
                    $output_image = '<div class="image">' . $output_link_start . '<img class="image-primary" src="' . OUTPUT_PATH . h(encode_url_path($item['image_name'])) . '"' . $output_image_width . $output_image_height . ' alt="" border="0" />' . $output_link_end . '</div>';
                    
                    // if edit mode is on then add the edit button to images in content
                    if ($editable == TRUE) {
                        $output_image = add_edit_button_for_images(str_replace(' ' , '_', $item['type']), $item['id'], $output_image, 'image_name');
                    }
                }
                
                $output_short_description = '';
                
                // if there is a short description then output short description
                if ($item['short_description'] != '') {
                    $output_short_description = '<div class="short_description">' . $output_link_start . h($item['short_description']) . $output_link_end . '</div>';
                }
                
                $output_price = '';
                
                // if this item is a product group, then get price range for all product groups and products in this product group
                if ($item['type'] == 'product group') {
                    $price_range = get_price_range($item['id'], $discounted_product_prices);
                    
                    // if there are non-donation products in product group, then output price range
                    if ($price_range['non_donation_products_exist'] == true) {
                        // if the smallest price and largest price are the same, then just output a price without a range
                        if ($price_range['smallest_price'] == $price_range['largest_price']) {
                            // if there is only one product and it is discounted, then prepare to show original price and discounted price
                            if (($price_range['number_of_products'] == 1) && ($price_range['discounted_products_exist'] == TRUE)) {
                                $output_price = '<div class="price">' . prepare_price_for_output($price_range['original_price'], TRUE, $price_range['smallest_price'], 'html') . '</div>';
                                
                            // else there is more than one product or there are no discounted products, so prepare to just show original price
                            } else {
                                // if this product group has a select display type and there are discounted products in it, then prepare to output single price with discounted styling
                                if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                    $output_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                    
                                // else this product group does not have a select display type or there are no discounted products in it, so prepare to output single price without discounted styling
                                } else {
                                    $output_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                }
                            }
                            
                        // else the smallest price and largest price are not the same, so output price range
                        } else {
                            // if this product group has a select display type and there are discounted products in it, then prepare to output price range with discounted styling
                            if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                $output_price = '<div class="price"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                
                            // else this product group does not have a select display type or there are no discounted products in it, so prepare to output price without discounted styling
                            } else {
                                $output_price = '<div class="price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                            }
                        }
                    }
                    
                // else this item is a product, so if product is not a donation, then output product's price
                } elseif ($item['selection_type'] != 'donation') {
                    // assume that the product is not discounted, until we find out otherwise
                    $discounted = FALSE;
                    $discounted_price = '';

                    // if the product is discounted, then prepare to show that
                    if (isset($discounted_product_prices[$item['id']]) == TRUE) {
                        $discounted = TRUE;
                        $discounted_price = $discounted_product_prices[$item['id']];
                    }
                    
                    $output_price = '<div class="price">' . prepare_price_for_output($item['price'], $discounted, $discounted_price, 'html') . '</div>';
                }
                
                $output_end_row = '';
                
                $remainder = $item_number % $number_of_columns;
                
                $output_spacer_cell = '';
                
                // if this is not the last item in the row, then output spacer cell
                if ($remainder != 0) {
                    $output_spacer_cell = '<td class="mobile_left mobile_spacer">&nbsp;</td>';
                }
                
                // if this is the last item in the row or the last item entirely, then end row
                if (($remainder == 0) || ($item_number == count($items))) {
                    // if this is not the last cell in a row, then output empty cells to complete the rest of the row
                    if ($remainder != 0) {
                        $number_of_empty_cells = $number_of_columns - $remainder;
                        
                        // loop through the number of empty cells in order to output empty cells
                        for ($i = 1; $i <= $number_of_empty_cells; $i++) {
                            $output_end_row .= '<td class="mobile_left mobile_spacer" style="text-align: center; ' . $output_cell_width . '">&nbsp;</td>';
                            
                            // if this is the not the last empty cell, then output spacer cell
                            if ($i != $number_of_empty_cells) {
                                $output_end_row .= '<td class="mobile_left mobile_spacer">&nbsp;</td>';
                            }
                        }
                    }
                    
                    $output_end_row .= '</tr>';
                }
                
                $output_edit_container_start = '';
                $output_edit_container_end = '';
                
                // if items are editable, prepare to add edit container around item
                if ($editable) {
                    // If item is a product group
                    if ($item['type'] == 'product group') {
                        // Output link to edit product group
                        $output_edit_button_link = 'edit_product_group.php';
                        // Output tooltip title
                        $tool_tip_title = 'Product Group';
                        
                    // else if the items is a product
                    } else {
                        // Output link to edit product
                        $output_edit_button_link = 'edit_product.php';
                        // Output tooltip title
                        $tool_tip_title = 'Product';
                    }
                    
                    // If there is a short description, output short description in tooltip
                    if ($item['short_description'] != '') {
                        $output_tooltip_name = $item['short_description'];
                        
                    // Else output name in tooltip
                    } else {
                        $output_tooltip_name = $item['name'];
                    }
                    
                    // prepare edit container
                    $output_edit_container_start = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/' . $output_edit_button_link . '?id=' . $item['id'] . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; font-style: normal; font-weight: normal; color: #FFFFFF; font-size: 12px; text-decoration: none; border: none; padding: 5px 8px; margin: -1px 25px 0 -1px; z-index: 10; line-height: normal; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $tool_tip_title . ': ' . h($output_tooltip_name) . '">Edit</a>';
                    $output_edit_container_end = '</div>';
                }

                $output_item_rows .=
                    $output_start_row . '
                        <td class="mobile_left" style="text-align: center; vertical-align: top; '. $output_cell_width . '">
                            ' . $output_edit_container_start . '
                            <div class="item mobile_left" style="'. $output_cell_height . '"' . $output_keywords . '>
                                ' . $output_image . '
                                ' . $output_short_description . '
                                ' . $output_price . '
                            </div>
                            <div style="clear: both"></div>
                            ' . $output_edit_container_end . '
                        </td>
                        ' . $output_spacer_cell . '
                    ' . $output_end_row;
                
            }
            
            $output_items =
                '<table class="item_table" style="width: 100%">
                    ' . $output_item_rows . '
                </table>';
            
        // else there is not at least one item, so output message
        } else {
            // if mode is browse, then prepare browse message
            if ($mode == 'browse') {
                $output_items = '<div style="font-weight: bold">There are no items in this group.</div>';
                
            // else mode is search, so prepare search message
            } else {
                $output_items = '<div style="font-weight: bold">No items were found for "' . h($search_query) . '".</div>';
            }
        }
        
        $output_code = '';
        
        // if mode is browse and there is code, then output code
        if (($mode == 'browse') && ($code != '')) {
            $output_code = '<div class="code">' . $code . '</div>';
        }
        
        $output_back_button = '';
        
        // if there is a previous url set, then add back button
        if ((isset($_GET['previous_url_id']) == true) && (isset($_SESSION['software']['urls'][$_GET['previous_url_id']]) == true)) {
            // if back button label is blank, then set to "Back"
            if ($back_button_label == '') {
                $back_button_label = 'Back';
            }
            
            $output_back_button = '<div class="edit_mode" style="margin-top: 1em; margin-bottom: 5px"><a href="' . h($_SESSION['software']['urls'][$_GET['previous_url_id']]) . '" class="software_button_secondary back_button">' . h($back_button_label) . '</a></div>';
        }
        
        // prepare to output edit container for product group if necessary
        $output_edit_container_start = '';
        $output_edit_container_end = '';

        // if product group is editable, then prepare to add edit container around product group
        if ($editable == TRUE) {
            // if mode is browse, then set the editable product group to the current product group
            if ($mode == 'browse') {
                $editable_product_group_id = $current_product_group_id;
                
            // else mode is search, so set the editable product group to the product group that is selected for this catalog page
            } else {
                $editable_product_group_id = $product_group_id;
            }
            
            // Get the editable product group name
            $query = "SELECT name FROM product_groups WHERE id = '" . escape($editable_product_group_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $editable_product_group_name = $row['name'];

            // prepare edit container
            $output_edit_container_start = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_product_group.php?id=' . $editable_product_group_id . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; font-style: normal; font-weight: normal; color: #FFFFFF; font-size: 12px; text-decoration: none; border: none; padding: 5px 8px; margin: -1px 25px 0 -1px; z-index: 10; line-height: normal; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Product Group: ' . h($editable_product_group_name) . '">Edit</a><div style="padding: 2em .25em .25em .25em">';
            $output_edit_container_end = '</div></div>';
        }
        
        return
            '<div class="software_catalog">
                ' . $output_menu_and_search_forms . '
                <div>
                    ' . $output_edit_container_start . '
                    ' . $output_full_description . '
                    ' . $output_featured_and_new_items . '
                    ' . $output_more_items_heading . '
                    ' . $output_search_heading . '
                    ' . $output_search_result_message . '
                    ' . $output_items . '
                    ' . $output_code . '
                    ' . $output_edit_container_end . '
                    ' . $output_back_button . '
                </div>
                ' . get_update_currency_form() . '
            </div>';

    // Otherwise the layout is custom.
    } else {

        $form = new liveform('catalog');

        $form->add_fields_to_session();

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

        settype($search, 'bool');

        $search_attributes = '';
        $query = '';
        $search_system = '';
        $items = array();
        $number_of_items = 0;
        $code = '';

        $search_attributes = 'action="" method="get"';

        // If the clear button was clicked, then clear query.
        if ($form->field_in_session($page_id . '_clear')) {
            $form->set($page_id . '_query', '');
        }

        $query = $form->get($page_id . '_query');

        // get current URL parts in order to deal with query string parameters
        $url_parts = parse_url(REQUEST_URL);
        
        // put query string parameters into an array
        parse_str($url_parts['query'], $query_string_parameters);
        
        // loop through the query string parameters in order to prepare hidden fields for each query string parameter,
        // so that we don't lose any when the form is submitted
        foreach ($query_string_parameters as $name => $value) {
            // If this is not the specific query parameter (already going to be a field for that)
            // and not the previous url id, and not the clear field,
            // then add hidden field for it
            if (
                ($name != $page_id . '_query')
                && ($name != 'previous_url_id')
                && ($name != $page_id . '_clear')
                && ($name != $page_id . '_submit')
            ) {
                $search_system .= '<input type="hidden" name="' . h($name) . '" value="' . h($value) . '">';
            }
        }

        $full_description = '';

        // if there is not a search query, then set mode and prepare for mode
        if ($query == '') {
            $mode = 'browse';
            
            // if a page name has been passed in the query string and if it contains a forward slash, then get the product group id
            if (mb_strpos($_GET['page'], '/') !== FALSE) {
                // get product group address name
                $product_group_address_name = mb_substr(mb_substr($_GET['page'], mb_strpos($_GET['page'], '/')), 1);
                
                // get the product group id
                $db_query = "SELECT id FROM product_groups WHERE address_name = '" . escape($product_group_address_name) . "'";
                $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $current_product_group_id = $row['id'];
                
            // else if there is a default product group set, then use that
            } elseif ($product_group_id != 0) {
                $current_product_group_id = $product_group_id;
                
            // else no product group can be found, so get top-level product group
            } else {
                $db_query = "SELECT id FROM product_groups WHERE parent_id = '0'";
                $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $current_product_group_id = $row['id'];
            }
            
            // get product group information and determine if product group exists
            $db_query =
                "SELECT
                    enabled,
                    full_description,
                    code
                FROM product_groups
                WHERE id = '" . escape($current_product_group_id) . "'";
            $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
            
            // If a product group cannot be found, then output error.
            if (mysqli_num_rows($result) == 0) {
                return error('Sorry, the item could not be found.', 404);
            }
            
            $row = mysqli_fetch_assoc($result);

            // If the product group is disabled, then output error.
            if (!$row['enabled']) {
                return error('Sorry, the item is not currently available.', 410);
            }

            $full_description = $row['full_description'];   

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
                //if product image xref or product group  xref exist. this mean this selected multiple product image
                if(mysqli_num_rows($image_results) != 0){
                    while ($image = mysqli_fetch_assoc($image_results)){
                        $code_content .= str_replace("^^image_url^^", PATH . encode_url_path($image['file_name']) , $code_content_raw );
                    }
                    $item['code'] = $code_header.$code_content.$code_footer;
                }else{
                    //else if less an image selected and only one image selected, but there is code for action we output single image
                    if($item['image_name']){
                        $code_single_content = str_replace("^^image_url^^", PATH . encode_url_path($item['image_name']) , $code_content_raw );
                        $item['code'] = $code_header.$code_single_content.$code_footer;
                    }else{
                        $item['code'] ='';
                    }
                }
            }else{
                // else there is no code spacial elements so we output direct code.
                $item['code'] = $row['code'];
            }
            
            if ($full_description and $editable) {
                $full_description = add_edit_button_for_images('product_group', $current_product_group_id, $full_description, 'full_description');
            }

            // initialize arrays that will store data for sorting
            $item_sort_orders = array();
            $item_names = array();
            
            // get all products groups in this product group
            $db_query =
                "SELECT
                    id,
                    name,
                    sort_order,
                    short_description,
                    keywords,
                    image_name,
                    display_type
                FROM product_groups
                WHERE
                    (parent_id = '" . e($current_product_group_id) . "')
                    AND (enabled = '1')";

            $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
            
            // loop through all product groups in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'short_description' => $row['short_description'],
                    'keywords' => $row['keywords'],
                    'image_name' => $row['image_name'],
                    'display_type' => $row['display_type'],
                    'type' => 'product group'
                );
                
                // add data to sorting arrays
                $item_sort_orders[] = $row['sort_order'];
                $item_names[] = mb_strtolower($row['name']);
            }
            
            // get all products that are in product group
            $db_query =
                "SELECT
                    products.id,
                    products.name,
                    products_groups_xref.sort_order,
                    products.short_description,
                    products.keywords,
                    products.image_name,
                    products.price,
                    products.selection_type,
                    inventory,
                    inventory_quantity
                FROM products_groups_xref
                LEFT JOIN products ON products.id = products_groups_xref.product
                WHERE
                    (products_groups_xref.product_group = '" . escape($current_product_group_id) . "')
                    AND (products.enabled = '1')";
            $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
            
            // loop through all products in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'short_description' => $row['short_description'],
                    'keywords' => $row['keywords'],
                    'image_name' => $row['image_name'],
                    'price' => $row['price'],
                    'selection_type' => $row['selection_type'],
                    'inventory' => $row['inventory'],
                    'inventory_quantity' => $row['inventory_quantity'],
                    'type' => 'product'
                );
                
                // add data to sorting arrays
                $item_sort_orders[] = $row['sort_order'];
                $item_names[] = mb_strtolower($row['name']);
            }
            
            // sort the items by sort order and then by name
            array_multisort($item_sort_orders, $item_names, $items);
            
        // else search is enabled and there is a search query, so set mode
        } else {
            $mode = 'search';

            $items = get_catalog_search_results($query, $product_group_id);
        }

        $edit = false;
        $edit_start = '';
        $edit_end = '';

        // If edit mode is on, then output grid around product group.
        if ($editable) {
            $edit = true;

            // if mode is browse, then set the editable product group to the current product group
            if ($mode == 'browse') {
                $editable_product_group_id = $current_product_group_id;
                
            // else mode is search, so set the editable product group to the product group that is selected for this catalog page
            } else {
                $editable_product_group_id = $product_group_id;
            }
            
            // Get the editable product group name
            $db_query = "SELECT name FROM product_groups WHERE id = '" . escape($editable_product_group_id) . "'";
            $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $editable_product_group_name = $row['name'];

            // prepare edit container
            $edit_start = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_product_group.php?id=' . h($editable_product_group_id) . '&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; font-style: normal; font-weight: normal; color: #FFFFFF; font-size: 12px; text-decoration: none; border: none; padding: 5px 8px; margin: -1px 25px 0 -1px; z-index: 10; line-height: normal; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Product Group: ' . h($editable_product_group_name) . '">Edit</a><div style="padding: 2em .25em .25em .25em">';

            $edit_end = '</div></div>';
        }

        if ($items) {
            $catalog_url = PATH . encode_url_path($current_page_name) . '/';
            $query_string = '?previous_url_id=' . urlencode(generate_url_id());

            if ($catalog_detail_page_id) {
                $catalog_detail_url = PATH . encode_url_path($catalog_detail_page_name) . '/';
            }

            // Loop through the catalog items in order to prepare more info.
            foreach ($items as $key => $item) {
                $item['url'] = '';

                if (($item['type'] == 'product group') && ($item['display_type'] == 'browse')) {
                    $item['url'] = $catalog_url . encode_url_path(get_catalog_item_address_name_from_id($item['id'], $item['type'])) . $query_string;

                } elseif ($catalog_detail_page_id) {
                    $item['url'] = $catalog_detail_url . encode_url_path(get_catalog_item_address_name_from_id($item['id'], $item['type'])) . $query_string;
                }

                $item['image_url'] = '';

                if ($item['image_name'] != '') {
                    $item['image_url'] = PATH . encode_url_path($item['image_name']);
                }
                
                if ($item['type'] == 'product group') {
                    $item['price_range'] = get_price_range($item['id'], $discounted_product_prices);
                }

                // Get the price or price range for output.
                $item['price_info'] = get_price_info(array(
                    'item' => $item,
                    'discounted_product_prices' => $discounted_product_prices));

                if ($item['type'] == 'product group') {
                    $item['price_range']['smallest_price'] = $item['price_range']['smallest_price'] / 100;
                    $item['price_range']['largest_price'] = $item['price_range']['largest_price'] / 100;
                    $item['price_range']['original_price'] = $item['price_range']['original_price'] / 100;

                // Otherwise this is a product, so prepare price in dollars.
                } else {
                    $item['price'] = $item['price'] / 100;
                }

                $item['edit_start'] = '';
                $item['edit_end'] = '';

                // If edit mode is on, then output edit grid around item.
                if ($editable) {

                    // If item is a product group
                    if ($item['type'] == 'product group') {
                        // Output link to edit product group
                        $output_edit_button_link = 'edit_product_group.php';
                        // Output tooltip title
                        $tool_tip_title = 'Product Group';
                        
                    // else if the items is a product
                    } else {
                        // Output link to edit product
                        $output_edit_button_link = 'edit_product.php';
                        // Output tooltip title
                        $tool_tip_title = 'Product';
                    }
                    
                    // If there is a short description, output short description in tooltip
                    if ($item['short_description'] != '') {
                        $output_tooltip_name = $item['short_description'];
                        
                    // Else output name in tooltip
                    } else {
                        $output_tooltip_name = $item['name'];
                    }
                    
                    // prepare edit container
                    $item['edit_start'] = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/' . $output_edit_button_link . '?id=' . $item['id'] . '&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; font-style: normal; font-weight: normal; color: #FFFFFF; font-size: 12px; text-decoration: none; border: none; padding: 5px 8px; margin: -1px 25px 0 -1px; z-index: 10; line-height: normal; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $tool_tip_title . ': ' . h($output_tooltip_name) . '">Edit</a>';

                    $item['edit_end'] = '</div>';

                }

                $items[$key] = $item;
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

        settype($menu, 'bool');

        $product_groups = get_product_groups(array(
            'id' => $product_group_id,
            'display_type' => 'browse',
            'status' => 'enabled'));

        $catalog_url = PATH . encode_url_path($current_page_name) . '/';
        $query_string = '?previous_url_id=' . urlencode(generate_url_id());

        // Loop through the product groups in order to prepare additional info.
        foreach ($product_groups as $key => $product_group) {
            $product_group['url'] = $catalog_url . encode_url_path(get_catalog_item_address_name_from_id($product_group['id'], 'product group')) . $query_string;

            if ($product_group['id'] == $current_product_group_id) {
                $product_group['current'] = true;
            }

            $product_groups[$key] = $product_group;
        }

        $content = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'currency' => $currency,
            'currency_attributes' => $currency_attributes,
            'currencies' => $currencies,
            'currency_system' => $currency_system,
            'form' => $form,
            'mode' => $mode,
            'menu' => $menu,
            'product_groups' => $product_groups,
            'search' => $search,
            'search_attributes' => $search_attributes,
            'query' => $query,
            'search_system' => $search_system,
            'edit' => $edit,
            'edit_start' => $edit_start,
            'edit_end' => $edit_end,
            'full_description' => $full_description,
            'items' => $items,
            'number_of_items' => count($items),
            'code' => $code,
            'back_button_url' => $back_button_url,
            'back_button_label' => $back_button_label));

        $content = $form->prepare($content);

        $form->remove();

        return
            '<div class="software_catalog">
                ' . $content . '
            </div>';

    }

}

function get_featured_and_new_items($parent_product_group_id, $items = array())
{
    // initialize array for storing featured and new items
    $featured_and_new_items = array();
    $featured_and_new_items['featured'] = array();
    $featured_and_new_items['new'] = array();
    
    // if this is the first time this function has run, then get all product groups and instances of products and store in array
    if (count($items) == 0) {
        // get all products groups where display type is browse or where the product group is featured or new
        $query =
            "SELECT
                id,
                parent_id as parent_product_group_id,
                name,
                short_description,
                full_description,
                keywords,
                image_name,
                display_type,
                featured,
                featured_sort_order,
                new_date
            FROM product_groups
            WHERE
                (display_type = 'browse')
                OR (featured = '1')
                OR ((new_date > '0000-00-00') AND (new_date <= '" . date('Y-m-d') . "'))";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through all product groups in order to add them to the array
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = array(
                'type' => 'product group',
                'id' => $row['id'],
                'parent_product_group_id' => $row['parent_product_group_id'],
                'name' => $row['name'],
                'short_description' => $row['short_description'],
                'full_description' => $row['full_description'],
                'keywords' => $row['keywords'],
                'image_name' => $row['image_name'],
                'display_type' => $row['display_type'],
                'featured' => $row['featured'],
                'featured_sort_order' => $row['featured_sort_order'],
                'new_date' => $row['new_date']
            );
        }
        
        // get all instances of products where the product is featured or new
        $query =
            "SELECT
                products.id,
                products_groups_xref.product_group as parent_product_group_id,
                products.name,
                products.short_description,
                products.full_description,
                products.keywords,
                products.price,
                inventory,
                inventory_quantity,
                products.selection_type,
                products.image_name,
                products_groups_xref.featured,
                products_groups_xref.featured_sort_order,
                products_groups_xref.new_date
            FROM products_groups_xref
            LEFT JOIN products ON products_groups_xref.product = products.id
            WHERE
                (products.enabled = '1')
                AND
                (
                    (products_groups_xref.featured = '1')
                    OR ((products_groups_xref.new_date > '0000-00-00') AND (products_groups_xref.new_date <= '" . date('Y-m-d') . "'))
                )";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through all instances of products in order to add them to the array
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = array(
                'type' => 'product',
                'id' => $row['id'],
                'parent_product_group_id' => $row['parent_product_group_id'],
                'name' => $row['name'],
                'short_description' => $row['short_description'],
                'full_description' => $row['full_description'],
                'keywords' => $row['keywords'],
                'price' => $row['price'],
                'selection_type' => $row['selection_type'],
                'image_name' => $row['image_name'],
                'inventory' => $row['inventory'],
                'inventory_quantity' => $row['inventory_quantity'],
                'featured' => $row['featured'],
                'featured_sort_order' => $row['featured_sort_order'],
                'new_date' => $row['new_date']
            );
        }
    }
    
    // get child items
    $child_items = array();
    
    // loop through items in order to get all child items that are in parent product group
    foreach ($items as $item) {
        // if the parent product group id for this item is equal to the parent product group id,
        // then add the item to the child items array
        if ($item['parent_product_group_id'] == $parent_product_group_id) {
            $child_items[] = $item;
        }
    }
    
    // loop through child items
    foreach ($child_items as $child_item) {
        // if this child item is featured, then add it to the array
        if ($child_item['featured'] == 1) {
            $featured_and_new_items['featured'][] = $child_item;
        }
        
        // if this child item is new, then add it to the array
        if (($child_item['new_date'] > '0000-00-00') && ($child_item['new_date'] <= date('Y-m-d'))) {
            $featured_and_new_items['new'][] = $child_item;
        }
        
        // if this child item is a product group and the product group is a browse product group, then use recursion to get child items for product group
        if (($child_item['type'] == 'product group') && ($child_item['display_type'] == 'browse')) {
            $child_featured_and_new_items = get_featured_and_new_items($child_item['id'], $items);
            $featured_and_new_items['featured'] = array_merge($featured_and_new_items['featured'], $child_featured_and_new_items['featured']);
            $featured_and_new_items['new'] = array_merge($featured_and_new_items['new'], $child_featured_and_new_items['new']);
        }
    }
    
    return $featured_and_new_items;
}