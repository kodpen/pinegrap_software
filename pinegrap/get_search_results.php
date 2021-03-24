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

function get_search_results($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];

    $properties = get_page_type_properties($page_id, 'search results');

    $search_folder_id = $properties['search_folder_id'];
    $search_catalog_items = $properties['search_catalog_items'];
    $product_group_id = $properties['product_group_id'];
    $catalog_detail_page_id = $properties['catalog_detail_page_id'];

    $layout_type = get_layout_type($page_id);

    $form = new liveform('search_results');

    $featured_items = array();
    $catalog_items = array();
    $pages = array();
    $pages_info = array();
    $results = array();

    $query = '';
    
    // if there is a specific query for this page, then use it
    if (isset($_GET[$page_id . '_query'])) {
        $query = trim($_GET[$page_id . '_query']);
        
    // else if there is a general query, then use it (this is for backwards compatibility)
    } else if (isset($_GET['query'])) {
        $query = trim($_GET['query']);
    }

    // If the visitor has searched and visitor tracking is enabled and this visitor
    // has been initialized, then add site search terms to visitor record
    if (
        ($query != '')
        && (VISITOR_TRACKING == true)
        && (isset($_SESSION['software']['visitor_id']) == true)
    ) {
        // get visitor's search terms from visitors table
        $db_query = "SELECT site_search_terms FROM visitors WHERE id = '" . $_SESSION['software']['visitor_id'] . "'";
        $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $site_search_terms = $row['site_search_terms'];
        
        // if there are search terms, then add a delimiter
        if ($site_search_terms != '') {
            $site_search_terms .= '|';
        }
        
        // add the new query to the search terms
        $site_search_terms .= $query;
        
        // update search terms in the visitors table for this visitor
        $db_query =
            "UPDATE visitors
            SET
                site_search_terms = '" . escape($site_search_terms) . "',
                stop_timestamp = UNIX_TIMESTAMP()
            WHERE (id = '" . $_SESSION['software']['visitor_id'] . "')";
        $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
    }

    // get current URL parts in order to deal with query string parameters
    $url_parts = parse_url(REQUEST_URL);
    
    // put query string parameters into an array
    parse_str($url_parts['query'], $query_string_parameters);

    $output_hidden_fields = '';
    
    // loop through the query string parameters in order to prepare hidden fields for each query string parameter,
    // so that we don't lose any when the form is submitted
    foreach ($query_string_parameters as $name => $value) {
        // if this is not the specific query parameter (already going to be a field for that), then add hidden field for it
        if ($name != $page_id . '_query') {
            $output_hidden_fields .= '<input type="hidden" name="' . h($name) . '" value="' . h($value) . '">';
        }
    }

    // If the visitor has searched, then get search results.
    if ($query != '') {

        // If ecommerce is enabled and search catalog is enabled then get catalog search results.
        if (ECOMMERCE and $search_catalog_items) {
            // If there is no product group selected for this catalog page,
            // then get top-level product group.
            if ($product_group_id == 0) {
                $db_query = "SELECT id FROM product_groups WHERE parent_id = '0'";
                $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $product_group_id = $row['id'];
            }

            $catalog_search_query = $query;

            // If search is set to advanced in the site settings,
            // then remove boolean characters from search query
            // because we don't support those for product searching.
            if (SEARCH_TYPE == 'advanced') {
                $catalog_search_query = str_replace('"', '', $catalog_search_query);
                $catalog_search_query = str_replace('+', '', $catalog_search_query);
            }
            
            $catalog_items = get_catalog_search_results($catalog_search_query, $product_group_id);
        }

        // If the search type is simple, then get results in that way.
        if (SEARCH_TYPE == 'simple') {

            // Get featured pages that we possibly need to include in results.
            // Featured pages are pages where the promote on keyword matches the search query.
            $featured_items = db_items(
                "SELECT
                    page.page_id AS id,
                    page.page_name AS name,
                    page.page_folder AS folder_id,
                    page.page_title AS title,
                    page.page_meta_description AS description
                FROM page
                LEFT JOIN folder ON page.page_folder = folder.folder_id
                WHERE
                    (page.page_search = '1')
                    AND (folder.folder_archived = '0')
                    AND (page.page_search_keywords = '" . e($query) . "')
                ORDER BY page.page_name ASC",
                'id');

            // Loop through the featured pages in order to prepare them.
            foreach ($featured_items as $key => $item) {
                // If the visitor does not have view access to the page,
                // then remove page from the array and continue to next one.
                if (!check_view_access($item['folder_id'], true)) {
                    unset($featured_items[$key]);
                    continue;
                }

                $item['url'] = PATH . encode_url_path($item['name']);
                $item['full_url'] = HOSTNAME_SETTING . $item['url'];

                if ($item['title'] == '') {
                    $item['title'] = $item['name'];
                }

                $featured_items[$key] = $item;
            }

            // get all pages without a matching keyword
            $result = mysqli_query(db::$con, "SELECT page_id, page_name, page_folder, page_title, page_meta_description, page_style FROM page WHERE page_search = 1 and page_search_keywords != '" . escape($query) . "'") or output_error('Query failed');
            while($row = mysqli_fetch_array($result))
            {
                $this_page_id = $row['page_id'];
                $page_name = $row['page_name'];
                $page_folder = $row['page_folder'];
                $page_title = $row['page_title'];
                $page_meta_description = $row['page_meta_description'];
                $style_id = $row['page_style'];

                if (get_access_control_type($page_folder) == 'private') {
                    $private = true;
                } else {
                    $private = false;
                }

                // if page is private and user is logged in
                if (($private == TRUE) && $_SESSION['sessionusername']) {
                    $access_check = check_private_access($row['page_folder']);

                    // If the user has access to the page, then remember that.
                    if ($access_check['access'] == true) {
                        $access = TRUE;
                    } else {
                        $access = FALSE;
                    }
                }

                // if the page is not private OR the user has access to page
                if (($private == FALSE) || ($access == TRUE)) {
                    // store page name and title
                    $pages_info[$this_page_id]['name'] = $page_name;
                    $pages_info[$this_page_id]['title'] = $page_title;
                    $pages_info[$this_page_id]['meta_description'] = $page_meta_description;

                    // If the page does not have a style set, then get style from folder.
                    if (!$style_id) {
                        $style_id = get_style($page_folder);
                    }

                    // Get the collection from the style, so that we know what collection
                    // of page regions to get.
                    $collection = db_value("SELECT collection FROM style WHERE style_id = '$style_id'");

                    // If we could not find a collection for whatever reason, then just set the collection to "a".
                    if ($collection == '') {
                        $collection = 'a';
                    }

                    // get all page regions for page
                    $db_query = "SELECT pregion_id, pregion_content FROM pregion WHERE (pregion_page = '$this_page_id') AND (collection = '$collection')";
                    $result_2 = mysqli_query(db::$con, $db_query) or output_error('Query failed');
                    while($row = mysqli_fetch_array($result_2)) {
                        $pregion_content = $row['pregion_content'];
                        
                        // increment rating depending on how many times the query is found (case-insensitive)
                        $increment_value = mb_substr_count(mb_strtolower($pregion_content), mb_strtolower($query));
                        
                        if($increment_value != 0) {
                            $pages[$this_page_id] = $pages[$this_page_id] + $increment_value;
                        }
                    }
                }
            }
            
            // get all pages that have a matching keyword in their meta keywords
            $db_query = 
                "SELECT 
                    page_id, 
                    page_name, 
                    page_folder, 
                    page_title, 
                    page_meta_description 
                FROM page 
                WHERE 
                    (page_search = 1) 
                    AND (page_search_keywords != '" . escape($query) . "')
                    AND (page_meta_keywords LIKE '%" . escape(escape_like($query)) . "%')";
            $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
            while ($row = mysqli_fetch_array($result)) {
                $this_page_id = $row['page_id'];
                $page_name = $row['page_name'];
                $page_folder = $row['page_folder'];
                $page_title = $row['page_title'];
                $page_meta_description = $row['page_meta_description'];
                
                if (get_access_control_type($page_folder) == 'private') {
                    $private = true;
                } else {
                    $private = false;
                }
                
                // if page is private and user is logged in
                if (($private == TRUE) && $_SESSION['sessionusername']) {
                    $access_check = check_private_access($row['page_folder']);

                    // if user is an administrator, desiger, or manager OR the user has access to the page
                    if ($access_check['access'] == true) {
                        $access = TRUE;
                    } else {
                        $access = FALSE;
                    }
                }
                
                // if the page is not private OR the user has access to page
                if (($private == FALSE) || ($access == TRUE)) {
                    // if this page is not already in the primary or pages arrays then add it
                    if ((array_key_exists($this_page_id, $featured_items) == FALSE) && (array_key_exists($this_page_id, $pages) == FALSE)) {
                        // store page name and title
                        $pages_info[$this_page_id]['name'] = $page_name;
                        $pages_info[$this_page_id]['title'] = $page_title;
                        $pages_info[$this_page_id]['meta_description'] = $page_meta_description;
                        
                        $pages[$this_page_id] = 1;
                    }
                }
            }
            
            // sort the array based upon how many matches each page had.
            arsort($pages);
            
            $comments = array();
            $pages_with_comment_matches = array();
            
            // get all comments for basic pages that are searchable
            $db_query = 
                "SELECT
                    comments.page_id,
                    comments.name,
                    comments.message,
                    page.page_folder
                FROM comments
                LEFT JOIN page ON page.page_id = comments.page_id
                WHERE
                    (comments.item_id = '0')
                    AND (comments.published = '1')
                    AND (page.page_search = '1')
                    AND (LOWER(CONCAT_WS(',', comments.name, comments.message)) LIKE '%" . escape($query) . "%')";
            $result = mysqli_query(db::$con, $db_query) or output_error('Query failed.');
            while($row = mysqli_fetch_array($result)) {
                // if the page for this comment is not already a search result, then add the comment to the comments array
                if ((array_key_exists($row['page_id'], $featured_items) == FALSE) && (array_key_exists($row['page_id'], $pages) == FALSE)) {
                    $comments[] =
                        array(
                            'page_id' => $row['page_id'],
                            'page_folder' => $row['page_folder'],
                            'name' => $row['name'],
                            'message' => $row['message']
                        );
                }
            }
            
            // loop through all comments that matched the search results to see if they should be outputted
            foreach ($comments as $comment) {
                $increment_value = 0;
                $private = FALSE;
                $access = FALSE;
                
                // Get the access control type that is needed to view this page.
                $get_access_control = get_access_control_type($comment['page_folder']);
                
                // if the user is logged in, and if this is a private page, then check to see if the user has view access
                if (($_SESSION['sessionusername'] != '') && ($get_access_control == 'private')) {
                    $access_check = check_private_access($comment['page_folder']);

                    // if the user is a manger or above, or if this is a basic user and if they have access to view the page, then allow access
                    if ($access_check['access'] == true) {
                        $access = TRUE;
                    }
                }
                
                // if the user has access or if this is not a private page, then count the amount of matches, and add comment to the array
                if(($access == TRUE) || ($get_access_control != 'private')) {
                    // increment rating depending on how many times the query is found (case-insensitive)
                    $increment_value = mb_substr_count(mb_strtolower($comment['name']), mb_strtolower($query));
                    $increment_value = $increment_value + mb_substr_count(mb_strtolower($comment['message']), mb_strtolower($query));
                    
                    // add comment to the array
                    $pages_with_comment_matches[$comment['page_id']] = $pages_with_comment_matches[$comment['page_id']] + $increment_value;
                }
            }
            
            // sort the array based upon how many matches each page had in it's comments.
            arsort($pages_with_comment_matches);
            
            // loop through the pages with comment matches in order to add the pages to the pages array
            foreach ($pages_with_comment_matches as $this_page_id => $number_of_matches) {
                $pages[$this_page_id] = $number_of_matches;
            }

        // Otherwise the search is set to advanced so get results for that type.
        } else {

            // If the search folder no longer exists, then set search folder to the root folder.
            if (db_value("SELECT folder_id FROM folder WHERE folder_id = '" . escape($search_folder_id) . "'") == '') {
                $search_folder_id = db_value("SELECT folder_id FROM folder WHERE folder_parent = '0'");
            }

            // Create an array to store all folders that are within the scope of this search.
            $folders = array();

            // Start the folders off with the search folder.
            $folders[] = $search_folder_id;

            // Get all folders in order to add child folders to array.
            $all_folders = db_items(
                "SELECT
                    folder_id AS id,
                    folder_parent AS parent_folder_id
                FROM folder");
            
            // Get child folders that are within the scope of this search.
            $child_folders = get_child_folders($search_folder_id, $all_folders);

            // Add child folders to array.
            $folders = array_merge($folders, $child_folders);

            $search_folder_parent_id = db_value("SELECT folder_parent FROM folder WHERE folder_id = '" . escape($search_folder_id) . "'");

            $sql_where_folders = "";

            // If the scope of this search is limited (i.e. not side wide) or if this visitor is not a manager or above,
            // then we need to limit the scope of the search to only certain folders
            if (
                ($search_folder_parent_id != 0)
                || (USER_LOGGED_IN == false)
                ||
                (
                    (USER_LOGGED_IN == true)
                    && (USER_ROLE == 3)
                )
            ) {
                // If this visitor is not a manager or above then remove folders that visitor does not have view access to.
                if (
                    (USER_LOGGED_IN == false)
                    || (USER_ROLE == 3)
                ) {
                    // Loop through folders in order to remove ones that visitor does not have access to.
                    foreach ($folders as $key => $folder_id) {
                        if (check_view_access($folder_id, $always_grant_access_for_registration_and_guest = true) == false) {
                            unset($folders[$key]);
                        }
                    }

                    // If visitor does not have access to any folders, then return error.
                    // This should not happen, because visitor should at least have access to the folder for this search results page
                    // however we just add this protection anyway, because if we allow this to proceed with no
                    // filter in the SQL query then it could be a security issue.
                    if (count($folders) == 0) {
                        return 'Sorry, you do not have access to any of the content for this search.';
                    }
                }

                $sql_where_folders = "(";

                // Loop through the folders in order to prepare the filters for the SQL query.
                foreach ($folders as $folder_id) {
                    // If a folder filter has already been added, then add an "OR".
                    if ($sql_where_folders != '(') {
                        $sql_where_folders .= " OR ";
                    }

                    $sql_where_folders .= "(search_items.folder_id = '" . $folder_id . "')";
                }

                $sql_where_folders .= ")";
            }

            $sql_boolean_mode = "";

            // Start the filter search query off with the search query that the customer entered.
            // This will be the query that is used in the where condition to filter results.
            $filter_search_query = $query;

            // If the search query does not contain a space (i.e. does not contain multiple keywords)
            // and does not already contain a quote, then add quotes around search query,
            // so that it is treated as a boolean exact phrase query.  We do this so that if a search query
            // contains a dash or possibly other connecting characters (e.g. multi-recipient) then
            // MySQL will only return results where the exact full query existed (e.g. "multi-recipient"
            // instead of results that contain "multi" and "recipient" in separate areas).
            if (
                (mb_strpos($filter_search_query, ' ') === false)
                && (mb_strpos($filter_search_query, '"') === false)
            ) {
                $filter_search_query = '"' . $filter_search_query . '"';
            }

            // Start the score search query off with the filter search query.
            $score_search_query = $filter_search_query;

            // If the search query contains boolean characters, then turn boolean mode on,
            // and remove the boolean characters for the score query, because we use a natural language search for the score,
            // even if it is a boolean search, because the boolean relevancy is bad.
            if (
                (mb_strpos($filter_search_query, '"') !== false)
                || (mb_strpos($filter_search_query, '+') !== false)
            ) {
                $sql_boolean_mode = " IN BOOLEAN MODE";

                $score_search_query = str_replace('"', '', $score_search_query);
                $score_search_query = str_replace('+', '', $score_search_query);
            }

            $sql_where_and = "";

            // If there are folder filters, then add an "AND" for the featured query.
            if ($sql_where_folders != '') {
                $sql_where_and .= " AND ";
            }

            // Do a fulltext search for the keywords column first in order to get
            // featured items.
            $featured_items = db_items(
                "SELECT
                    id,
                    url,
                    title,
                    description,
                    MATCH(keywords) AGAINST ('" . e($score_search_query) . "') AS score
                FROM search_items
                WHERE
                    $sql_where_folders
                    $sql_where_and
                    (MATCH(keywords) AGAINST('" . e($filter_search_query) . "'$sql_boolean_mode))
                ORDER BY score DESC
                LIMIT 100");

            // Prepare where filter in order to prevent getting results that
            // already appeared in featured area.
            $sql_where_not_featured = "";

            foreach ($featured_items as $key => $item) {

                // If there are folder filters or if this is not the first not featured filter,
                // then add an "AND".
                if (($sql_where_folders != '') || ($sql_where_not_featured != '')) {
                    $sql_where_not_featured .= " AND ";
                }

                $sql_where_not_featured .= "(id != '" . $item['id'] . "')";
                
                $item['url'] = PATH . $item['url'];
                $item['full_url'] = HOSTNAME_SETTING . $item['url'];

                $featured_items[$key] = $item;
            }

            $sql_where_and = "";

            // If there are folder or not featured filters, then add an "AND".
            if (($sql_where_folders != '') || ($sql_where_not_featured != '')) {
                $sql_where_and .= " AND ";
            }

            // Now do a fulltext search to get the normal results.
            $results = db_items(
                "SELECT
                    url,
                    title,
                    description,
                    (
                        (3 * (MATCH(title) AGAINST ('" . e($score_search_query) . "')))
                        + (2 * (MATCH(description) AGAINST ('" . e($score_search_query) . "')))
                        + (1 * (MATCH(content) AGAINST ('" . e($score_search_query) . "')))
                    ) AS score
                FROM search_items
                WHERE
                    $sql_where_folders
                    $sql_where_not_featured
                    $sql_where_and
                    (MATCH(content) AGAINST('" . e($filter_search_query) . "'$sql_boolean_mode))
                ORDER BY score DESC
                LIMIT 100");
        }
    }

    if ($layout_type == 'system') {

        // If search is set to advanced in the site settings, then do that type of search.
        if (SEARCH_TYPE == 'advanced') {

            $site_search_value = h($query);

            if ($site_search_value == '') {
                $site_search_value = 'Search';
            }

            $output_results = '';

            // If the search query is not blank, then continue to search.
            if ($query != '') {

                $output_featured_items = '';

                // If there is at least one featured item, then prepare to output them.
                if (count($featured_items) > 0) {
                    $output_featured_items .=
                        '<fieldset class="software_fieldset">
                            <legend class="software_legend">Featured Search Results</legend>
                            <div style="margin: 10px">';
                    
                    $row_count = 1;

                    // Loop through featured items in order to output them.
                    foreach ($featured_items as $item) {

                        $output_description = '';

                        if ($item['description'] != '') {
                            $output_description = h($item['description']);
                        }

                        $output_featured_items .=
                            '<div class="data row_' . ($row_count % 2) .'" style="margin-bottom: 1em">
                                <div class="search-title">
                                    <a href="' . h($item['url']) . '">' . h($item['title']) . '</a>
                                </div>
                                <div class="search-link">' . h($item['full_url']) . '</div>
                                <div class="search-description">' . $output_description . '</div>
                            </div>';

                        $row_count++;
                    }
                    
                    $output_featured_items .=
                        '   </div>
                        </fieldset>';
                }

                $output_catalog_items = '';

                // if the amount of catalog items is greater than 0, then output items
                if (count($catalog_items) > 0) {
                    // get prices for products that have been discounted by offers, so we can show the discounted price
                    $discounted_product_prices = get_discounted_product_prices();
                    
                    $output_catalog_items .= '<div class="software_catalog_search_results">';

                    $row_count = 1;

                    // loop through all catalog items in order to prepare output
                    foreach ($catalog_items as $item) {
                        $output_link_start = '';
                        $output_link_end = '';
                        
                        // if there is a catalog detail page selected, then output link to catalog detail page
                        if ($catalog_detail_page_id != 0) {
                            // if item is a product group, then set id name
                            if ($item['type'] == 'product group') {
                                $id_name = 'product_group_id';
                                
                            // else item is a product, so set id name
                            } else {
                                $id_name = 'product_id';
                            }
                            
                            $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path(get_page_name($catalog_detail_page_id))) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($item['id'], $item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                            $output_link_end = '</a>';
                        }
                        
                        $output_image = '';
                        
                        // if there is an image name then prepare and output image
                        if ($item['image_name'] != '') {
                            // Get the dimensions of the image
                            $image_size = getimagesize(FILE_DIRECTORY_PATH . '/' . $item['image_name']);
                            $image_width = $image_size[0];
                            $image_height = $image_size[1];

                            // get scaled dimensions of the image
                            $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, 100);
                            
                            // output image
                            $output_image = '<div class="image">' . $output_link_start . '<img class="image-primary" src="' . OUTPUT_PATH . h(encode_url_path($item['image_name'])) . '" width="' . $thumbnail_dimensions['width'] . '" height="' . $thumbnail_dimensions['height'] . '" alt="" border="0" />' . $output_link_end . '</div>';
                            
                            // if edit mode is on then add the edit button to images in content
                            if ($editable == TRUE) {
                                $output_image = add_edit_button_for_images(str_replace(' ' , '_', $item['type']), $item['id'], $output_image, 'image_name');
                            }
                        }
                        
                        $output_short_description = '';
                        
                        // if there is a short description then output short description
                        if ($item['short_description'] != '') {
                            $output_short_description = '<div class="short_description search-title">' . $output_link_start . h($item['short_description']) . $output_link_end . '</div>';
                        }
                        
                        $output_price = '';
                        
                        // if this item is a product group, then get price range for all product groups and products in this product group
                        if ($item['type'] == 'product group') {
                            // this product group is appearing in search results, so we know it has a select display type
                            $item['display_type'] = 'select';
                            
                            $price_range = get_price_range($item['id'], $discounted_product_prices);
                            
                            // if there are non-donation products in product group, then output price range
                            if ($price_range['non_donation_products_exist'] == true) {
                                // if the smallest price and largest price are the same, then just output a price without a range
                                if ($price_range['smallest_price'] == $price_range['largest_price']) {
                                    // if there is only one product and it is discounted, then prepare to show original price and discounted price
                                    if (($price_range['number_of_products'] == 1) && ($price_range['discounted_products_exist'] == TRUE)) {
                                        $output_price = '<div class="price search-description">' . prepare_price_for_output($price_range['original_price'], TRUE, $price_range['smallest_price'], 'html') . '</div>';
                                        
                                    // else there is more than one product or there are no discounted products, so prepare to just show original price
                                    } else {
                                        // if this product group has a select display type and there are discounted products in it, then prepare to output single price with discounted styling
                                        if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                            $output_price = '<div class="price search-description"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                            
                                        // else this product group does not have a select display type or there are no discounted products in it, so prepare to output single price without discounted styling
                                        } else {
                                            $output_price = '<div class="price search-description">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                        }
                                    }
                                    
                                // else the smallest price and largest price are not the same, so output price range
                                } else {
                                    // if this product group has a select display type and there are discounted products in it, then prepare to output price range with discounted styling
                                    if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                        $output_price = '<div class="price search-description"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                        
                                    // else this product group does not have a select display type or there are no discounted products in it, so prepare to output price without discounted styling
                                    } else {
                                        $output_price = '<div class="price search-description">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
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
                            
                            $output_price = '<div class="price search-description">' . prepare_price_for_output($item['price'], $discounted, $discounted_price, 'html') . '</div>';
                        }
                        
                        $output_catalog_items .=
                            '<div class="item data row_' . ($row_count % 2) . '" style="display: inline-block; text-align: center; width: 120px; margin-right: 2em; vertical-align: top;">
                                ' . $output_image . '
                                ' . $output_short_description . '
                                ' . $output_price . '
                            </div>';
                        $row_count++;
                    }

                    $output_catalog_items .= '</div>';
                }

                $row_count = 1;

                foreach ($results as $result) {
                    $output_title = '';

                    if ($result['title'] != '') {
                        $output_title = '<a href="' . h(URL_SCHEME . HOSTNAME . PATH . $result['url']) . '">' . h($result['title']) . '</a>';
                    } else {
                        $output_title = '<a href="' . h(URL_SCHEME . HOSTNAME . PATH . $result['url']) . '">' . h(URL_SCHEME . HOSTNAME . PATH . $result['url']) . '</a>';
                    }

                    $output_description = '';

                    if ($result['description'] != '') {
                        $output_description = h($result['description']);
                    }

                    $output_url = '';

                    if ($result['url'] != '') {
                        $output_url = HOSTNAME . OUTPUT_PATH . h($result['url']);
                    }

                    $output_results .=
                        '<div class="data row_' . ($row_count % 2) .'" style="margin-bottom: 1em">
                            <div class="search-title">' . $output_title . '</div>
                            <div class="search-link">' . $output_url . '</div>
                            <div class="search-description">' . $output_description . '</div>
                        </div>';

                    $row_count++;
                }
            }

            $number_of_results = count($featured_items) + count($catalog_items) + count($results);

            // If there is at least one result, then output heading for that.
            if ($number_of_results > 0) {
                // If the number of results from the fulltext/boolean search is 100,
                // then that means the 100 result limit was enforced, so output message to express that.
                if (count($results) == 100) {
                    $output_heading = '<div class="search_results_heading" style="font-weight: bold; margin-bottom: 1.5em">Showing ' . number_format($number_of_results) . ' of the most relevant results for: ' . h($query) . '</div>';

                // Otherwise the number of results from the fulltext/boolean search is less than 100,
                // so the results were not limited, so output message to express that.
                } else {
                    $plural_suffix = '';

                    if ($number_of_results > 1) {
                        $plural_suffix = 's';
                    }

                    $output_heading = '<div class="search_results_heading" style="font-weight: bold; margin-bottom: 1.5em">Found ' . number_format($number_of_results) . ' result' . $plural_suffix . ' for: ' . h($query) . '</div>';
                }

            // Otherwise there are no results, so if there is a search query,
            // then output heading for that.
            } else if ($query != '') {
                $output_heading = '<div class="search_results_heading" style="font-weight: bold">No results were found for: ' . h($query) . '</div>';

            // Otherwise there are no results because there was no search query,
            // so output heading for that.
            } else {
                $output_heading = '<div class="search_results_heading" style="font-weight: bold">Please enter keyword(s) or phrase to search.</div>';
            }

            return
                '<div class="software_search_results">
                    <div class="search_results_search">
                        <form class="mobile_align_left" action="" method="get" style="text-align: right; margin: 0em 0em 1em 0em">
                            ' . $output_hidden_fields . '
                            <span class="search">
                                <span class="simple">
                                    <input class="software_input_text mobile_fixed_width query" style="margin-bottom: 0 !important" type="text" name="' . $page_id . '_query" value="' . $site_search_value . '"
                                    onfocus="if ( this.value == \'Search\' ) { this.value = \'\';}" onblur="if ( this.value == \'\') { this.value = \'Search\';}" />
                                    <input type="submit" title="Search" value="" class="submit"/>
                                </span>
                            </span>
                        </form>
                    </div>
                    ' . $output_heading . '
                    ' . $output_featured_items . '
                    ' . $output_catalog_items . '
                    ' . $output_results . '
                </div>';

        // Otherwise search is set to simple in the site settings, so do that type of search.
        } else {

            $site_search_value = h($query);
            if ($site_search_value == '') {
                $site_search_value = 'Search';
            }
            
            $search_content =
                '<div class="search_results_search">
                    <form class="mobile_align_left" action="" method="get" style="text-align: right; margin: 0em 0em 1em 0em">
                        ' . $output_hidden_fields . '
                        <span class="search">
                            <span class="simple">
                                <input class="software_input_text mobile_fixed_width query" style="margin-bottom: 0 !important" type="text" name="' . $page_id . '_query" value="' . $site_search_value . '"
                                onfocus="if ( this.value == \'Search\' ) { this.value = \'\';}" onblur="if ( this.value == \'\') { this.value = \'Search\';}" />
                                <input type="submit" title="Search" value="" class="submit"/>
                            </span>
                        </span>
                    </form>
                </div>';
            
            // if there are more than 0 results
            if ((count($pages) > 0) || (count($featured_items) > 0) || (count($catalog_items) > 0)) {
                $search_content .= '<span class="search_results_heading" style="font-weight:bold; margin-bottom:1em">Found ' . number_format(count($pages) + count($featured_items) + count($catalog_items)) . ' result(s) for &quot;' . h($query) . '&quot;.</span><br /><br />';
                // If the keyword matching returned results, display them in separately.
                if (count($featured_items) > 0) {
                    $search_content .=
                        '<fieldset class="software_fieldset">
                            <legend class="software_legend">Featured Search Results</legend>
                            <div style="margin: 10px">';
                    
                    $row_count = 1;

                    // Loop through featured items in order to output them.
                    foreach ($featured_items as $item) {

                        $output_description = '';
                        
                        // if there is a description for page
                        if ($item['description']) {
                            $output_description = '<span class="search-description">' . h($item['description']) . '</span><br />';
                        }
                        
                        $search_content .=
                            '<div class="data row_' . ($row_count % 2) .'">' . '
                                <a href="' . h($item['url']) . '" class="search-title">' . h($item['title']) . '</a><br>
                                ' . $output_description . '<br>
                            </div>';

                        $row_count++;
                    }
                    
                    $search_content .=
                        '   </div>
                        </fieldset>';
                }
                
                // if the amount of catalog items is greater than 0, then output items
                if (count($catalog_items) > 0) {
                    // get prices for products that have been discounted by offers, so we can show the discounted price
                    $discounted_product_prices = get_discounted_product_prices();
                    
                    $output_catalog_items = '';
                    $row_count = 1;

                    // loop through all catalog items in order to prepare output
                    foreach ($catalog_items as $item) {
                        $output_link_start = '';
                        $output_link_end = '';
                        
                        // if there is a catalog detail page selected, then output link to catalog detail page
                        if ($catalog_detail_page_id != 0) {
                            // if item is a product group, then set id name
                            if ($item['type'] == 'product group') {
                                $id_name = 'product_group_id';
                                
                            // else item is a product, so set id name
                            } else {
                                $id_name = 'product_id';
                            }
                            
                            $output_link_start = '<a href="' . OUTPUT_PATH . h(encode_url_path(get_page_name($catalog_detail_page_id))) . '/' . h(encode_url_path(get_catalog_item_address_name_from_id($item['id'], $item['type']))) . '?previous_url_id=' . urlencode(generate_url_id()) . '">';
                            $output_link_end = '</a>';
                        }
                        
                        $output_image = '';
                        
                        // if there is an image name then prepare and output image
                        if ($item['image_name'] != '') {
                            // Get the dimensions of the image
                            $image_size = getimagesize(FILE_DIRECTORY_PATH . '/' . $item['image_name']);
                            $image_width = $image_size[0];
                            $image_height = $image_size[1];

                            // get scaled dimensions of the image
                            $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, 100);
                            
                            // output image
                            $output_image = '<div class="image">' . $output_link_start . '<img class="image-primary" src="' . OUTPUT_PATH . h(encode_url_path($item['image_name'])) . '" width="' . $thumbnail_dimensions['width'] . '" height="' . $thumbnail_dimensions['height'] . '" alt="" border="0" />' . $output_link_end . '</div>';
                            
                            // if edit mode is on then add the edit button to images in content
                            if ($editable == TRUE) {
                                $output_image = add_edit_button_for_images(str_replace(' ' , '_', $item['type']), $item['id'], $output_image, 'image_name');
                            }
                        }
                        
                        $output_short_description = '';
                        
                        // if there is a short description then output short description
                        if ($item['short_description'] != '') {
                            $output_short_description = '<div class="short_description search-title">' . $output_link_start . h($item['short_description']) . $output_link_end . '</div>';
                        }
                        
                        $output_price = '';
                        
                        // if this item is a product group, then get price range for all product groups and products in this product group
                        if ($item['type'] == 'product group') {
                            // this product group is appearing in search results, so we know it has a select display type
                            $item['display_type'] = 'select';
                            
                            $price_range = get_price_range($item['id'], $discounted_product_prices);
                            
                            // if there are non-donation products in product group, then output price range
                            if ($price_range['non_donation_products_exist'] == true) {
                                // if the smallest price and largest price are the same, then just output a price without a range
                                if ($price_range['smallest_price'] == $price_range['largest_price']) {
                                    // if there is only one product and it is discounted, then prepare to show original price and discounted price
                                    if (($price_range['number_of_products'] == 1) && ($price_range['discounted_products_exist'] == TRUE)) {
                                        $output_price = '<div class="price search-description">' . prepare_price_for_output($price_range['original_price'], TRUE, $price_range['smallest_price'], 'html') . '</div>';
                                        
                                    // else there is more than one product or there are no discounted products, so prepare to just show original price
                                    } else {
                                        // if this product group has a select display type and there are discounted products in it, then prepare to output single price with discounted styling
                                        if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                            $output_price = '<div class="price search-description"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                            
                                        // else this product group does not have a select display type or there are no discounted products in it, so prepare to output single price without discounted styling
                                        } else {
                                            $output_price = '<div class="price search-description">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
                                        }
                                    }
                                    
                                // else the smallest price and largest price are not the same, so output price range
                                } else {
                                    // if this product group has a select display type and there are discounted products in it, then prepare to output price range with discounted styling
                                    if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                        $output_price = '<div class="price search-description"><span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</span></div>';
                                        
                                    // else this product group does not have a select display type or there are no discounted products in it, so prepare to output price without discounted styling
                                    } else {
                                        $output_price = '<div class="price search-description">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</div>';
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
                            
                            $output_price = '<div class="price search-description">' . prepare_price_for_output($item['price'], $discounted, $discounted_price, 'html') . '</div>';
                        }
                        
                        $output_catalog_items .=
                            '<div class="item data row_' . ($row_count % 2) . '" style="display: inline-block; text-align: center; width: 120px; margin-right: 2em; vertical-align: top;">
                                ' . $output_image . '
                                ' . $output_short_description . '
                                ' . $output_price . '
                            </div>';
                        $row_count++;
                    }
                    
                    // output items
                    $search_content .= '<div class="software_catalog_search_results">' . $output_catalog_items . '</div>';
                    
                }
                
                // If there were still more results returned from the search that did not have a matching keyword, return them now.
                if (count($pages) > 0) {
                    $row_count = 1;

                    foreach ($pages as $key => $value) {
                        // if there is a title, then prepare link with title
                        if ($pages_info[$key]['title']) {
                            $output_link = '<a href="' . OUTPUT_PATH . h($pages_info[$key]['name']) . '" class="search-title">' . h($pages_info[$key]['title']) . '</a>';
                            
                        // else there is not a title, so prepare URL link
                        } else {
                            $output_link = '<a href="' . OUTPUT_PATH . h($pages_info[$key]['name']) . '" class="search-title">' . HOSTNAME_SETTING . OUTPUT_PATH . h($pages_info[$key]['name']) . '</a>';
                        }

                        $output_meta_description = '';
                        
                        // if there is a meta description for page
                        if ($pages_info[$key]['meta_description']) {
                            $output_meta_description = '<span class="search-description">' . h($pages_info[$key]['meta_description']) . '</span><br />';
                        }
                        
                        $search_content .=
                            '<div class="data row_' . ($row_count % 2) .'">'
                                . $output_link . '<br />
                                ' . $output_meta_description . '<br />
                            </div>';
                            
                        $row_count++;
                    }
                }
                
            // otherwise there were 0 results
            } else {
                if ($query != '') {
                    $search_content .= '<span class="search_results_heading" style="font-weight:bold">No results were found for &quot;' . h($query) . '&quot;.</span><br /><br />';
                } else {
                    $search_content .= '<span class="search_results_heading" style="font-weight:bold">Please enter a keyword or phrase to search.</span>';
                }
            }

            return
                '<div class="software_search_results">
                    ' . $search_content . '
                </div>';

        }

    // Otherwise the layout is custom.
    } else {

        $form->add_fields_to_session();

        $attributes = 'action="" method="get"';
        
        $system = $output_hidden_fields;

        $limited = false;

        $number_of_results = 0;

        // If the visitor has entered a search query, then get results.
        if ($query != '') {

            // If this was an advanced search that was limited, then store that,
            // so that we can show a specific message concerning that.
            if ((SEARCH_TYPE == 'advanced') and (count($results) == 100)) {
                $limited = true;
            }

            if ($catalog_items) {

                if ($catalog_detail_page_id) {
                    $url = PATH . encode_url_path(get_page_name($catalog_detail_page_id)) . '/';
                    $query_string = '?previous_url_id=' . urlencode(generate_url_id());
                }

                // Get prices for products that have been discounted by offers,
                // so we can show the discounted price.
                $discounted_product_prices = get_discounted_product_prices();

                // Loop through the catalog items in order to prepare more info.
                foreach ($catalog_items as $key => $item) {
                    $item['url'] = '';

                    if ($catalog_detail_page_id) {
                        $item['url'] = $url . encode_url_path(get_catalog_item_address_name_from_id($item['id'], $item['type'])) . $query_string;
                    }

                    $item['image_url'] = '';

                    if ($item['image_name'] != '') {
                        $item['image_url'] = PATH . encode_url_path($item['image_name']);
                    }

                    $output_price = '';
                    
                    // if this item is a product group, then get price range for all product groups and products in this product group
                    if ($item['type'] == 'product group') {
                        // this product group is appearing in search results, so we know it has a select display type
                        $item['display_type'] = 'select';
                        
                        $price_range = get_price_range($item['id'], $discounted_product_prices);

                        $item['price_range'] = $price_range;
                        
                        // if there are non-donation products in product group, then output price range
                        if ($price_range['non_donation_products_exist'] == true) {
                            // if the smallest price and largest price are the same, then just output a price without a range
                            if ($price_range['smallest_price'] == $price_range['largest_price']) {
                                // if there is only one product and it is discounted, then prepare to show original price and discounted price
                                if (($price_range['number_of_products'] == 1) && ($price_range['discounted_products_exist'] == TRUE)) {
                                    $output_price = prepare_price_for_output($price_range['original_price'], TRUE, $price_range['smallest_price'], 'html');
                                    
                                // else there is more than one product or there are no discounted products, so prepare to just show original price
                                } else {
                                    // if this product group has a select display type and there are discounted products in it, then prepare to output single price with discounted styling
                                    if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                        $output_price = '<span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html') . '</span>';
                                        
                                    // else this product group does not have a select display type or there are no discounted products in it, so prepare to output single price without discounted styling
                                    } else {
                                        $output_price = prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html');
                                    }
                                }
                                
                            // else the smallest price and largest price are not the same, so output price range
                            } else {
                                // if this product group has a select display type and there are discounted products in it, then prepare to output price range with discounted styling
                                if (($item['display_type'] == 'select') && ($price_range['discounted_products_exist'] == TRUE)) {
                                    $output_price = '<span class="software_discounted_price">' . prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html') . '</span>';
                                    
                                // else this product group does not have a select display type or there are no discounted products in it, so prepare to output price without discounted styling
                                } else {
                                    $output_price = prepare_price_for_output($price_range['smallest_price'], FALSE, $discounted_price = '', 'html', $show_code = FALSE) . ' - ' . prepare_price_for_output($price_range['largest_price'], FALSE, $discounted_price = '', 'html');
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
                        
                        $output_price = prepare_price_for_output($item['price'], $discounted, $discounted_price, 'html');
                    }

                    $item['price_info'] = $output_price;

                    $item['price'] = $item['price'] / 100;

                    if ($item['price_range']) {
                        $item['price_range']['smallest_price'] = $item['price_range']['smallest_price'] / 100;
                        $item['price_range']['largest_price'] = $item['price_range']['largest_price'] / 100;
                        $item['price_range']['original_price'] = $item['price_range']['original_price'] / 100;
                    }

                    $catalog_items[$key] = $item;
                }

            }

            // If the search type is simple, then prepare page info.
            if (SEARCH_TYPE == 'simple') {
                foreach ($pages as $this_page_id => $value) {
                    $page = $pages_info[$this_page_id];

                    $result = array();

                    if ($page['title']) {
                        $result['title'] = $page['title'];
                    } else {
                        $result['title'] = $page['name'];
                    }

                    $result['url'] = PATH . encode_url_path($page['name']);
                    $result['full_url'] = HOSTNAME_SETTING . $result['url'];
                    $result['description'] = $page['meta_description'];

                    $results[] = $result;
                }

            // Otherwise the search type is advanced, so prepare data for that.
            } else {
                foreach ($results as $key => $result) {
                    $result['url'] = PATH . $result['url'];
                    $result['full_url'] = HOSTNAME_SETTING . $result['url'];

                    if (!$result['title']) {
                        $result['title'] = $result['full_url'];
                    }

                    $results[$key] = $result;
                }
            }

            $number_of_results =
                count($featured_items) + count($catalog_items) + count($results);
        }

        $content = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'system' => $system,
            'query' => $query,
            'limited' => $limited,
            'number_of_results' => $number_of_results,
            'featured_items' => $featured_items,
            'catalog_items' => $catalog_items,
            'results' => $results));

        $content = $form->prepare($content);

        $form->remove();

        return
            '<div class="software_search_results">
                ' . $content . '
            </div>';

    }
}