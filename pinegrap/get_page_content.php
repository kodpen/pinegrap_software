<?php

/**
 *
 * pinegrap - Enterprise Website Platform
 * 
 * @author      Camelback Web Architects
 * @link        https://pinegrap.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */ 

// get the content for a page ($system_content is content that should be inserted in place of <system></system> tags)

function get_page_content($page_id, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = false, $dynamic_properties = array(), $toolbar = FALSE, $device_type = 'desktop') {

    global $user;
    
    // get data for the page
    $query = "SELECT * " .
             "FROM page " .
             "WHERE page_id = '" . e($page_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);

    $page_name = $row['page_name'];
    $page_folder = $row['page_folder'];
    $page_style = $row['page_style'];
    $mobile_style_id = $row['mobile_style_id'];
    $page_home = $row['page_home'];
    $page_title = $row['page_title'];
    $page_meta_description = $row['page_meta_description'];
    $page_meta_keywords = $row['page_meta_keywords'];
    $page_type = $row['page_type'];
    $comments = $row['comments'];
    $comments_label = $row['comments_label'];
    $comments_message = $row['comments_message'];
    $comments_allow_new_comments = $row['comments_allow_new_comments'];
    $comments_disallow_new_comment_message = $row['comments_disallow_new_comment_message'];
    $comments_automatic_publish = $row['comments_automatic_publish'];
    $comments_allow_user_to_select_name = $row['comments_allow_user_to_select_name'];
    $comments_require_login_to_comment = $row['comments_require_login_to_comment'];
    $comments_allow_file_attachments = $row['comments_allow_file_attachments'];
    $comments_show_submitted_date_and_time = $row['comments_show_submitted_date_and_time'];
    $comments_administrator_email_to_email_address = $row['comments_administrator_email_to_email_address'];
    $comments_administrator_email_conditional_administrators = $row['comments_administrator_email_conditional_administrators'];
    $comments_submitter_email_page_id = $row['comments_submitter_email_page_id'];
    $comments_watcher_email_page_id = $row['comments_watcher_email_page_id'];
    $comments_watchers_managed_by_submitter = $row['comments_watchers_managed_by_submitter'];
    $system_region_header = $row['system_region_header'];
    $system_region_footer = $row['system_region_footer'];

    // Remember the requested device type, because we might change this below
    // if a mobile style does not exist.  We want to remember the requested device type,
    // because we still want to output the appropriate mobile switch.
    $requested_device_type = $device_type;
    
    $preview_style = get_preview_style(array(
        'page_id' => $page_id,
        'folder_id' => $page_folder,
        'page_style_id' => $page_style,
        'page_mobile_style_id' => $mobile_style_id,
        'device_type' => $device_type));

    $style_id = $preview_style['id'];
    $device_type = $preview_style['device_type'];


	$edit_label ='';
	if(language_ruler()==='en'){
		$edit_label ='Edit';
	}else if(language_ruler()==='tr'){
		$edit_label ='D&uuml;zenle';
	}



	
    // Create global constant for style id so that we don't have to figure out the
    // current style again for the toolbar.
    define('STYLE_ID', $style_id);

    // Get style information.
    $query =
        "SELECT
            style.style_name,
            style.style_code,
            style.style_type,
            style.style_head,
            style.social_networking_position,
            style.collection,
            style.layout_type,
            style.theme_id,
            files.name AS style_theme_name
        FROM style
        LEFT JOIN files ON style.theme_id = files.id
        WHERE style.style_id = '$style_id'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $style_name = $row['style_name'];
    $content = $row['style_code'];
    $style_type = $row['style_type'];
    $style_head = $row['style_head'];
    $social_networking_position = $row['social_networking_position'];
    $collection = $row['collection'];
    $layout_type = $row['layout_type'];
    $style_theme_id = $row['theme_id'];
    $style_theme_name = $row['style_theme_name'];

    // Set a global constant to remember the collection, so that for primary and
    // secondary system regions, the code in the separate files to generate
    // those system regions will know what collection to use for those system regions.
    // This is currently only used for form list views and form item views.
    define('COLLECTION', $collection);

    // Set a global constant to remember the layout type override for the style,
    // if one exists, so we will know it when we generate the system content.
    define('STYLE_LAYOUT_TYPE', $layout_type);
    
    // if this is a system style and there is head content, then add head content
    if (($style_type == 'system') && ($style_head != '')) {
        $content = str_replace('</head>', $style_head . '</head>', $content);
    }

    // Find all designer regions and replace them with their content.
    // We do the designer regions first, so that they can contain common regions,
    // page regions or etc. Those embedded regions will get replaced properly if we
    // first replace designer regions.  We also replace designer regions inside of other
    // designer regions in this block of code (only one level though).
    preg_match_all('/<cregion>.*?<\/cregion>/i', $content, $regions);
    foreach ($regions[0] as $region) {
        $cregion_name = strip_tags($region);
        $query =
            "SELECT
                cregion_id,
                cregion_content,
                cregion_designer_type,
                cregion_name
            FROM cregion
            WHERE cregion_name = '" . escape($cregion_name) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $cregion_id = $row['cregion_id'];
        $cregion_content = $row['cregion_content'];
        $cregion_designer_type = $row['cregion_designer_type'];
        $cregion_name = $row['cregion_name'];

        // If this is a designer region, then continue to replace it.
        if ($cregion_designer_type == 'yes') {
            // Replace other designer regions that might exist inside this designer region.
            preg_match_all('/<cregion>.*?<\/cregion>/i', $cregion_content, $embedded_regions);
            foreach ($embedded_regions[0] as $embedded_region) {
                $embedded_cregion_name = strip_tags($embedded_region);
                $query =
                    "SELECT
                        cregion_id,
                        cregion_content,
                        cregion_designer_type,
                        cregion_name
                    FROM cregion
                    WHERE cregion_name = '" . escape($embedded_cregion_name) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                
                $embedded_cregion_id = $row['cregion_id'];
                $embedded_cregion_content = $row['cregion_content'];
                $embedded_cregion_designer_type = $row['cregion_designer_type'];
                $embedded_cregion_name = $row['cregion_name'];

                // If this is a designer region, then continue to replace it.
                if ($embedded_cregion_designer_type == 'yes') {
                    // If mode is edit and user is at least a designer then add edit container.
                    if (($mode == 'edit') && ($user['role'] < 2)) {
                        // if cregion content is empty, then add some default content to resolve spacing issues
                        if ($embedded_cregion_content == '') {
                            $embedded_cregion_content = '<div style="padding: 5px">&nbsp;</div>';
                        }
                        
                        $embedded_cregion_content = '<div class="edit_mode" style="position: relative; border: 1px dashed #68201E; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_designer_region.php?id=' . $embedded_cregion_id . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #68201E; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Designer Region: ' . h($embedded_cregion_name) . '">' . $edit_label . '</a>'. $embedded_cregion_content . '</div>';
                    }

                    $cregion_content = str_replace($embedded_region, $embedded_cregion_content, $cregion_content);
                }
            }

            // If mode is edit and user is at least a designer then add edit container.
            if (($mode == 'edit') && ($user['role'] < 2)) {
                // if cregion content is empty, then add some default content to resolve spacing issues
                if ($cregion_content == '') {
                    $cregion_content = '<div style="padding: 5px">&nbsp;</div>';
                }
                
                $cregion_content = '<div class="edit_mode" style="position: relative; border: 1px dashed #68201E; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_designer_region.php?id=' . $cregion_id . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #68201E; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Designer Region: ' . h($cregion_name) . '">' . $edit_label . '</a>'. $cregion_content . '</div>';
            }

            $content = str_replace($region, $cregion_content, $content);
        }
    }

    // It is important that we parse for PHP & dynamic regions towards the top of this script,
    // before we prepare other content, because, for security reasons, we need to make sure there
    // is no user input in any of the content, in order to prevent someone from including malicious
    // PHP code.
    // 
    // We deal with PHP & dynamic regions after designer regions, so that designer regions
    // can include PHP & dynamic regions like styles can.

    // If PHP regions are enabled, and there is a PHP region in the content, then execute PHP code.
    if (defined('PHP_REGIONS') and PHP_REGIONS and strpos($content, '<?') !== false) {
        ob_start();
        eval('?>' . $content);
        $content = ob_get_clean();
    }

    // If dynamic regions are enabled, then execute PHP code in them.
    if (defined('DYNAMIC_REGIONS') and DYNAMIC_REGIONS) {
        preg_match_all('/<dregion(.*?)>(.*?)<\/dregion>/i', $content, $dregions, PREG_SET_ORDER);
        if ($dregions) {
            foreach ($dregions as $key => $value) {
                $attribute_string = $dregions[$key][1];
                if ($attribute_string != '') {
                    preg_match('/"(.*?)"/', $attribute_string, $attribute_value);
                    $dregion_attribute = $attribute_value[1];
                }
                $dregion_name = $dregions[$key][2];
                $query = "SELECT dregion_code "
                        ."FROM dregion "
                        ."WHERE dregion_name = '" . escape($dregion_name) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                $row = mysqli_fetch_array($result);
                ob_start();
                eval(prepare_for_eval($row['dregion_code']));
                $dregion_output = ob_get_contents();
                ob_end_clean();
                $content = preg_replace('/<dregion.*?>.*?<\/dregion>/i', addcslashes($dregion_output, '\\$'), $content, 1);
            }
        }
    }

    // Prepare canonical URL for canonical tag and social.
    
    $path = '';
    $query_string = '';

    // If this is the home page, then just set the path to the base path.
    if ($page_home == 'yes') {

        $path = PATH;

    // Otherwise if this is an error, then use the URL that the visitor requested, so that we
    // don't use the same canonical to the error page (e.g. /site-error) for all errors.
    } else if ($page_type == 'error') {
        
        $path = REQUEST_URL;

    // Otherwise figure out the path and query string.
    } else {

        // if this page is a catalog or catalog detail page,
        // and there is an address name in the path,
        // then get the path in a specific way, because we can't use short links with an address name in the path
        if (
            (($page_type == 'catalog') || ($page_type == 'catalog detail'))
            && (mb_strpos($_GET['page'], '/') !== FALSE)
        ) {
            // get address name from the current path
            $address_name = mb_substr(mb_substr($_GET['page'], mb_strpos($_GET['page'], '/')), 1);
            
            // set the path
            $path = PATH . encode_url_path($page_name) . '/' . encode_url_path($address_name);

        // Otherwise if a pretty URL path is set, then that means this is a form item view
        // with a pretty URL (e.g. /blog/happy-holidays), so prepare path in a different way.
        } else if (defined('PRETTY_URL_PATH') == true) {
            $path = PATH . PRETTY_URL_PATH;
            
        // Otherwise, just use the page name for the path.
        } else {
            $path = PATH . encode_url_path($page_name);
        }
        
        // get the query string differently based on the page type
        switch ($page_type) {
                
            case 'form item view':

                // If the current request is not using a pretty URL,
                // and if a reference code is in the query string,
                // the add it to the query string
                if (
                    (defined('PRETTY_URL_PATH') == false)
                    && (isset($_GET['r']) == TRUE)
                ) {
                    $query_string = '?r=' . $_GET['r'];
                }

                break;

            case 'form list view':

                // If there is a specific page number for this page, then use it.
                if ($_GET[$page_id . '_page_number'] != '' and $_GET[$page_id . '_page_number'] > 1) {
                    $query_string = '?' . $page_id . '_page_number=' . urlencode(trim($_GET[$page_id . '_page_number']));
                    
                // Otherwise if there is a general page number, then use it.
                } else if ($_GET['page_number'] != '' and $_GET['page_number'] > 1) {
                    $query_string = '?page_number=' . urlencode(trim($_GET['page_number']));
                }
                
            case 'calendar event view':

                // if there is an id in the query string, then add it to the query string
                if (isset($_GET['id']) == TRUE) {
                    $query_string = '?id=' . $_GET['id'];
                }
                
                // if there is a recurrence number in the query string, then add it to the query string
                if (isset($_GET['recurrence_number']) == TRUE) {
                    // if there are no other values in the query string so far, then add question mark
                    if ($query_string == '') {
                        $query_string .= '?';
                        
                    // else there are other values in the query string, so add ampersand
                    } else {
                        $query_string .= '&';
                    }
                    
                    // add recurrence number to the query string
                    $query_string .= 'recurrence_number=' . $_GET['recurrence_number'];
                }

                break;

            case 'photo gallery':

                // if a folder id is in the query string, the add it to the query string
                if (isset($_GET['folder_id']) == TRUE) {
                    $query_string = '?folder_id=' . $_GET['folder_id'];
                }

                break;

            case 'search results':

                // If there is a specific query for this page, then use it.
                if ($_GET[$page_id . '_query'] != '') {
                    $query_string = '?' . $page_id . '_query=' . urlencode(trim($_GET[$page_id . '_query']));
                    
                // Otherwise if there is a general query, then use it.
                } else if ($_GET['query'] != '') {
                    $query_string = '?query=' . urlencode(trim($_GET['query']));
                }

                break;
        }
    }
    
    $canonical_url = URL_SCHEME . HOSTNAME_SETTING . $path . $query_string;

    // We purposely replace the designer regions up above, before we prepare the JS
    // below that needs to be injected, because the designer might have placed
    // various tags like the </head> tag in the designer region and we look for
    // those tags below to choose the placement for the injected JS.

    // if this is not an e-mail page, then prepare and add frontend javascript to page content
    if ($email == FALSE) {

        // If environment is development then output JS variable for that, so in other areas
        // we know whether to load .src or .min JS files.

        $software_environment = '';

        if (defined('ENVIRONMENT') and ENVIRONMENT == 'development') {
            $software_environment = 'var software_environment = "development";';
        }

        $output_kiosk_javascript = '';

        // If kiosk mode is enabled then output javascript variable for that.
        if ($_SESSION['software']['kiosk']['enabled'] == true) {
            if ($_SESSION['software']['kiosk']['activity'] == true) {
                $output_kiosk_activity_value = 'true';
            } else {
                $output_kiosk_activity_value = 'false';
            }

            $output_kiosk_javascript =
                'var software_kiosk = true;
                var software_kiosk_activity = ' . $output_kiosk_activity_value . ';
                var software_kiosk_inactivity_time = ' . escape_javascript($_SESSION['software']['kiosk']['inactivity_time']) . ';
                var software_kiosk_dialog_time = ' . escape_javascript($_SESSION['software']['kiosk']['dialog_time']) . ';
                var software_kiosk_dialog_message = "' . escape_javascript($_SESSION['software']['kiosk']['dialog_message']) . '";
                var software_kiosk_continue_button_label = "' . escape_javascript($_SESSION['software']['kiosk']['continue_button_label']) . '";
                var software_kiosk_logout_button_label = "' . escape_javascript($_SESSION['software']['kiosk']['logout_button_label']) . '";';
        }

        $output_edit_region_code = '';
        
        // if the mode is edit, then output the includes and css that is needed for the edit region dialog
        if ($mode == 'edit') {
            $output_edit_region_code .=
                '<link rel="stylesheet" type="text/css" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/theme/standard.' . ENVIRONMENT_SUFFIX . '.css" />
                ' . get_wysiwyg_editor_code(array('software_edit_region_textarea'), $activate_editors = false, $page_folder, $edit_region_dialog = TRUE, $style_theme_name) . '
                <script type="text/javascript">var software_editor_content = \'\';</script>
                <style type="text/css">
                    .submit-primary
                    {
                        color: #fff !important;
                        background: #3e6b0e url(' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/submit_primary_background.png) top repeat-x !important;
                        border: 1px solid #000 !important;
                        padding: .25em .5em !important;
                        font-size: 13px !important;
                        text-decoration: none !important;
                        -moz-border-radius: 5px !important;
                        -webkit-border-radius: 5px !important;
                        border-radius: 5px !important;
                    }
                    
                    .submit-secondary
                    {
                        color: #000 !important;
                        background: #d1d1d1 url(' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/submit_secondary_background.png) top repeat-x !important;
                        border: 1px solid !important;
                        padding: .25em .5em !important;
                        font-size: 13px !important;
                        text-decoration: none !important;
                        -moz-border-radius: 5px !important;
                        -webkit-border-radius: 5px !important;
                        border-radius: 5px !important;
                    }
                </style>';
        }
        
        $output_lightbox_includes = '';
        
        // if this is a photo gallery, then output the lightbox includes
        if ($page_type == 'photo gallery') {
            $output_lightbox_includes = '<script type="text/javascript" src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/lightbox/jquery.lightbox-0.5.js"></script><link rel="stylesheet" type="text/css" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/lightbox/jquery.lightbox-0.5.css" />';
        }

        // if CDN is enabled, then use Google CDN for jQuery for performance reasons
        if (
            (defined('CDN') == FALSE)
            || (CDN == TRUE)
        ) {
            $output_jquery =
                '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
                <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>';

        // else CDN is disabled, so use local jQuery
        } else {
            $output_jquery =
                '<script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-1.7.2.min.js"></script>
                <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-1.12.1.min.js"></script>';
        }

        // The start/end "pinegrap dynamic code" comments below are used by the
        // import feature to know which content to remove.

        $output_javascript_and_css =
        '<!-- Start Pinegrap dynamic code -->
        <script>
            var software_path = "' . escape_javascript(PATH) . '";
            var software_directory = "' . escape_javascript(SOFTWARE_DIRECTORY) . '";
            var software_token = "' . $_SESSION['software']['token'] . '";
            var software_device_type = "' . $device_type . '";
            var software_page_id = ' . $page_id . ';
            ' . $software_environment . '
            ' . $output_kiosk_javascript . '
        </script>
        ' . $output_jquery . '
        ' . $output_edit_region_code . '
        <script  src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/frontend.' . ENVIRONMENT_SUFFIX . '.js?v=' . @filemtime(dirname(__FILE__) . '/frontend.' . ENVIRONMENT_SUFFIX . '.js') . '"></script>
        ' . $output_lightbox_includes . '
        <!-- End Pinegrap dynamic code -->';

        // If there is a <stylesheet></stylesheet> tag, then add JavaScript and CSS after that tag for performance reasons.
        // We are outputting the JavaScript after the stylesheet in order to increase performance.
        // There might be problems with putting this code right before the </head>.
        // For example an administrator might have added their own jQuery version and it might only work
        // if our jQuery is outputted first. Eventually, we should look into outputting this code
        // right before the </body> tag for the best performance, however we can't do this currently
        // because JavaScript calls appear in the HTML code that is outputted before the </body>
        // (e.g. pop-up and accordion menus)
        if (preg_match('/<stylesheet><\/stylesheet>/i', $content) != 0) {
            $content = preg_replace('/(<stylesheet><\/stylesheet>)/i', '$1' . $output_javascript_and_css, $content);
        
        // else if there is a head tag, then add JavaScript and CSS after that tag
        } else if (preg_match('/<head\b.*?>/i', $content) != 0) {
            $content = preg_replace('/(<head\b.*?>)/i', '$1' . $output_javascript_and_css, $content);
            
        // else if there is a body tag, then add JavaScript and CSS after that tag
        } else if (preg_match('/<body.*?>/i', $content) != 0) {
            $content = preg_replace('/(<body.*?>)/i', '$1' . $output_javascript_and_css, $content);

        // else a stylesheet, head, or body tag was not found so if mode is edit
        // then add JavaScript and CSS to the beginning of the content
        // we only want to add JavaScript and CSS if mode is edit, because the page might be outputting
        // an XML file (e.g. sitemap.xml) where we don't want JavaScript or CSS outputted
        } else if ($mode == 'edit') {
            $content = $output_javascript_and_css . $content;
        }
        
        // if Google Analytics is enabled and there is a Web Property ID, then add Google Analytics JavaScript
        if ((GOOGLE_ANALYTICS == TRUE) && (GOOGLE_ANALYTICS_WEB_PROPERTY_ID != '')) {

            $output_ecommerce_tracking_data = '';

            // If this is an order receipt and this visitor has completed an order,
            // then output ecommerce data for Google Analytics.
            if (
                ($page_type == 'order receipt')
                && (isset($_SESSION['ecommerce']['completed_order_id']) == TRUE)
            ) {

                $order = db_item(
                    "SELECT
                        order_number,
                        order_date,
                        total,
                        tax,
                        shipping,
                        billing_city,
                        billing_state,
                        billing_country
                    FROM orders
                    WHERE id = '" . $_SESSION['ecommerce']['completed_order_id'] . "'");

                $output_ecommerce_tracking_data .=
                    "\n" .
                    'ga(\'require\', \'ecommerce\');' . "\n" .
                    'ga(\'ecommerce:addTransaction\', {' . "\n" .
                    '    \'id\': \'' . $order['order_number'] . '\',' . "\n" .
                    '    \'affiliation\': \'' . escape_javascript(ORGANIZATION_NAME) . '\',' . "\n" .
                    '    \'revenue\': \'' . $order['total'] / 100 . '\',' . "\n" .
                    '    \'shipping\': \'' . $order['shipping'] / 100 . '\',' . "\n" .
                    '    \'tax\': \'' . $order['tax'] / 100 . '\'' . "\n" .
                    '});' . "\n";

                $sql_recurring_start_date = "";

                // If the payment service is not ClearCommerce, then prepare to get recurring order items
                // where start date is less than or equal to order date.
                if (
                    (ECOMMERCE_CREDIT_DEBIT_CARD == FALSE)
                    || (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce')
                ) {
                    $sql_recurring_start_date = "|| (order_items.recurring_start_date <= '" . date('Y-m-d', $order['order_date']) . "')";
                }
                
                // Get order items that appear in today's charges (i.e. does not include future charges for recurring products,
                // because Google Analytics does not support that).
                // If there are multiple order items for the same product (e.g. multiple recipients),
                // then they will be grouped together, because Google requires that.
                // This code has an issue where the data might not be correct if there are multiple order items for the same product,
                // and the price is different for the order items because of offers that are only allowed to affect one recipient.
                // Google Analytics does not have a solution for this issue.
                $order_items = db_items(
                    "SELECT
                        order_items.product_name,
                        products.short_description,
                        order_items.price,
                        SUM(order_items.quantity) AS quantity
                    FROM order_items
                    LEFT JOIN products ON order_items.product_id = products.id
                    WHERE
                        (order_items.order_id = '" . $_SESSION['ecommerce']['completed_order_id'] . "')
                        AND
                        (
                            (order_items.recurring_payment_period = '')
                            " . $sql_recurring_start_date . "
                        )
                    GROUP BY order_items.product_id
                    ORDER BY order_items.id ASC");

                // Loop through all order items in order to send data to Google Analytics.
                foreach ($order_items as $order_item) {

                    $output_ecommerce_tracking_data .=
                        'ga(\'ecommerce:addItem\', {' . "\n" .
                        '    \'id\': \'' . $order['order_number'] . '\',' . "\n" .
                        '    \'name\': \'' . escape_javascript($order_item['short_description']) . '\',' . "\n" .
                        '    \'sku\': \'' . escape_javascript($order_item['product_name']) . '\',' . "\n" .
                        '    \'price\': \'' . $order_item['price'] / 100 . '\',' . "\n" .
                        '    \'quantity\': \'' . $order_item['quantity'] . '\'' . "\n" .
                        '});' . "\n";

                }

                $output_ecommerce_tracking_data .= 'ga(\'ecommerce:send\');' . "\n";
            }

            $output_google_analytics =
                '<script>' . "\n" .
                '    window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;' . "\n" .
                '    ga(\'create\', \'' . GOOGLE_ANALYTICS_WEB_PROPERTY_ID . '\', \'auto\');' . "\n" .
                '    ga(\'send\', \'pageview\');' . "\n" .
                    $output_ecommerce_tracking_data .
                '</script>' . "\n" .
                '<script async src=\'https://www.google-analytics.com/analytics.js\'></script>';

            // We are using stristr instead of mb_stristr because mb_stristr requires PHP 5.2,
            // and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).
            
            // if </head> is in the HTML, place Google Analytics code before </head> (this is recommended by Google)
            // we are not going to output Google Analytics code if there is no closing head, body, or html tag, because the page might contain some other type of data (e.g. XML)
            if (stristr($content, '</head>')) {
                $content = preg_replace('/<\/head>/i', $output_google_analytics . '</head>', $content);
            
            // if </body> is in the HTML, place Google Analytics code before </body>
            } else if (stristr($content, '</body>')) {
                $content = preg_replace('/<\/body>/i', $output_google_analytics . '</body>', $content);
                
            // else if </html> is in the HTML, place Google Analytics code before </html>
            } elseif (stristr($content, '</html>')) {
                $content = preg_replace('/<\/html>/i', $output_google_analytics . '</html>', $content);
            }
        }
        
        // if the mode is edit, then output the edit region content form and the function call that initializes the dialog box to the bottom of the HTML document
        if ($mode == 'edit') {
            $page_editor_version = db_value("SELECT page_editor_version FROM config");

            include_once('liveform.class.php');
            $liveform_region_content = new liveform('region_content');
            
            // prepare the text editor and dialog for output
            $output_text_editor_content = 
                '<div id="software_edit_region_dialog">
                    <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/save_region_content.php" method="post">
                        ' . get_token_field() . '
                        ' . $liveform_region_content->output_field(array('type'=>'hidden', 'id'=>'page_id', 'name'=>'page_id', 'value'=>$page_id)) . '
                        ' . $liveform_region_content->output_field(array('type'=>'hidden', 'id'=>'region_type', 'name'=>'region_type')) . '
                        ' . $liveform_region_content->output_field(array('type'=>'hidden', 'id'=>'region_id', 'name'=>'region_id')) . '
                        ' . $liveform_region_content->output_field(array('type'=>'hidden', 'id'=>'region_order', 'name'=>'region_order')) . '
                        ' . $liveform_region_content->output_field(array('type' => 'hidden', 'name' => 'collection', 'value' => $collection)) . '
                        ' . $liveform_region_content->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                        <div style="margin-bottom: 1.5em;">' . $liveform_region_content->output_field(array('type'=>'textarea', 'id'=>'software_edit_region_textarea', 'name'=>'region_content', 'value'=>'', 'style'=>'visibility: hidden')) . '</div>
                        <div><input type="submit" name="submit" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="software_$(software_$(\'#software_edit_region_dialog\')[0]).dialog(\'close\')" class="submit-secondary"></div>
                    </form>
                </div>
                <script type="text/javascript">software_initialize_edit_region_dialog()</script>';

            // We are using stristr instead of mb_stristr because mb_stristr requires PHP 5.2,
            // and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).
            
            // if </body> is in the HTML, place rich-text editor before </body>
            if (stristr($content, '</body>')) {
                $content = preg_replace('/<\/body>/i', $output_text_editor_content . '</body>', $content);
                
            // else if </html> is in the HTML, place rich-text editor before </html>
            } elseif (stristr($content, '</html>')) {
                $content = preg_replace('/<\/html>/i', $output_text_editor_content . '</html>', $content);
                
            // else HTML does not contain </body> or </html>, so just place rich-text editor at the end of the content
            } else {
                $content .= $output_text_editor_content;
            }
            
            $liveform_region_content->remove_form();
        }
    }

    // Find all if tags and replace them with content.
    // If tags allow blocks of content to only be outputted
    // if a visitor has view access to a particular folder.
    // Also an else tag can be added if visitor does not have access.
    // If tags might be used in the future for other types of checks.
    preg_match_all('/<if\s(.*?)>(.*?)<\/if>\s*(<else>(.*?)<\/else>)?/is', $content, $if_tags, PREG_SET_ORDER);

    foreach ($if_tags as $tag) {
        // If the if tag contains "view-access" then we know it is an if tag
        // that we need to deal with.  We only support that type of if tag for now.
        if (mb_strpos(mb_strtolower($tag[1]), 'view-access') !== false) {
            $if_content = '';

            // Get folder id.
            preg_match('/folder-id="(.*?)"/is', $tag[1], $folder_id_match);
            $folder_id = trim($folder_id_match[1]);

            // If a folder id was found, then check if folder exists.
            if ($folder_id) {
                $original_folder_id = $folder_id;

                $folder_id = db_value("SELECT folder_id FROM folder WHERE folder_id = '" . escape($folder_id) . "'");

                // If folder exists, then check if visitor has view access to the folder.
                if ($folder_id) {
                    // If visitor has view access to folder,
                    // then allow the visitor to see this content.
                    if (check_view_access($folder_id) == true) {
                        $if_content = $tag[2];

                    // Otherwise the visitor does not have view access,
                    // so if there is an else tag, then output content for it.
                    } else if ($tag[4] != '') {
                        $if_content = $tag[4];
                    }

                // Otherwise the folder does not exist, so if the user is in edit mode,
                // then output error.
                } else if ($mode == 'edit') {
                    $if_content =
                        '<div>
                            Sorry, a folder could not be found for the folder id ("' . h($original_folder_id) . '") in the if tag.
                            An incorrect folder id might have been entered or the folder might have been deleted since the if tag was added.
                            Please use the following format: ' . h('<if view-access folder-id="123">content</if>') . '
                        </div>';
                }
                
            // Otherwise a folder id was not found, so if the user is in edit mode,
            // then output error.
            } else if ($mode == 'edit') {
                $if_content =
                    '<div>
                        Sorry, the if tag is missing a folder id.
                        Please use the following format: ' . h('<if view-access folder-id="123">content</if>') . '
                    </div>';
            }

            // Replace the if tag with the appropriate content.
            $content = str_replace($tag[0], $if_content, $content);
        }
    }

    // If ads are enabled, then replace ad tags with ad content.
    if (ADS === true) {
        // assume that we don't need to include dynamic ad region javascript files, until we find out otherwise
        $include_dynamic_ad_region_javascript_files = false;

        // replace ad regions with ads
        preg_match_all('/<ad>.*?<\/ad>/i', $content, $regions);
        foreach ($regions[0] as $region) {
            $ad_region_name = strip_tags($region);
            
            // get ad region information
            $query =
                "SELECT
                    id,
                    display_type,
                    transition_type,
                    transition_duration,
                    slideshow,
                    slideshow_interval,
                    slideshow_continuous
                FROM ad_regions
                WHERE name = '" . escape($ad_region_name) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $ad_region_content = '';
            
            // if an ad region was found, then continue to get content for ad region
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                
                $ad_region_id = $row['id'];
                $ad_region_display_type = $row['display_type'];
                $ad_region_transition_type = $row['transition_type'];
                $ad_region_transition_duration = $row['transition_duration'];
                $ad_region_slideshow = $row['slideshow'];
                $ad_region_slideshow_interval = $row['slideshow_interval'];
                $ad_region_slideshow_continuous = $row['slideshow_continuous'];
                
                // if this is a static ad region, then prepare content for static ad region
                if ($ad_region_display_type == 'static') {
                    // Get a random ad that is assigned to this ad region
                    $query =
                        "SELECT
                            id,
                            name,
                            content
                        FROM ads
                        WHERE ad_region_id = '" . $ad_region_id . "'
                        ORDER BY RAND()
                        LIMIT 1";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    $ad_content = '';
                    
                    // if an ad was found, then continue to get content for ad region
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $ad_id = $row['id'];
                        $ad_name = $row['name'];
                        $ad_content = $row['content'];
                        
                        // if mode is edit, and user is at least a manager or if they have access to edit the containing ad region, then add edit button for images and edit button for ad
                        if (($mode == 'edit') && (($user['role'] < 3) || (in_array($ad_region_id, get_items_user_can_edit('ad_regions', $user['id'])) == true))) {
                            // add edit button for images
                            $ad_content = add_edit_button_for_images('ad', $ad_id, $ad_content);
                            
                            // add edit button for ad
                            $ad_content =
                                '<div class="edit_mode" style="position: relative; border: 1px dashed #788207; margin: -1px;">
                                    <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_ad.php?id=' . $ad_id . '&send_to=' . h(urlencode(get_request_uri())) . '"style="background: #788207; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 3px 8px !important; margin: -1px 25px 0 34px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Ad: ' . h($ad_name) . '">' . $edit_label . '</a>
                                    ' . $ad_content . '
                                </div>';
                        }
                    }
                    
                    $ad_region_content = '<div id="software_ad_region_' . $ad_region_name . '" class="software_ad_region_static">' . $ad_content . '</div>';
                    
                // else this is a dynamic ad region, so prepare content for dynamic ad region
                } else {
                    $output_ad_region_javascript_initialization = '';
                    $output_ad_region_interior_content = '';
                    
                    // get ads that are assigned to this ad region
                    $query =
                        "SELECT
                            id,
                            name,
                            content,
                            caption,
                            label,
                            sort_order
                        FROM ads
                        WHERE ad_region_id = '" . $ad_region_id . "'
                        ORDER BY sort_order ASC";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // if an ad was found, then continue to get content for ad region
                    if (mysqli_num_rows($result) > 0) {
                        // prepare javascript value for slideshow
                        if ($ad_region_slideshow == 1) {
                            $output_ad_region_slideshow_javascript_value = 'true';
                        } else {
                            $output_ad_region_slideshow_javascript_value = 'false';
                        }

                        // Prepare JavaScript value for slideshow continuous.
                        if ($ad_region_slideshow_continuous == 1) {
                            $output_ad_region_slideshow_continuous_javascript_value = 'true';
                        } else {
                            $output_ad_region_slideshow_continuous_javascript_value = 'false';
                        }
                        
                        // prepare javascript initialization
                        $output_ad_region_javascript_initialization = '<script type="text/javascript">software_initialize_dynamic_ad_region("' . $ad_region_name . '", "' . $ad_region_transition_type . '", ' . $ad_region_transition_duration . ', ' . $output_ad_region_slideshow_javascript_value . ', ' . $ad_region_slideshow_interval . ', ' . $output_ad_region_slideshow_continuous_javascript_value . ');</script>' . "\n";
                        
                        $ads = array();
                        
                        // assume that a sort order does not exist until we find out otherwise (we will use this later to determine if the order should be random)
                        $sort_order_exists = false;
                        
                        // loop through results in order to add ads to array
                        while($row = mysqli_fetch_assoc($result)) {
                            $ads[] = $row;
                            
                            // if there is a sort order, then remember that
                            if ($row['sort_order'] != 0) {
                                $sort_order_exists = true;
                            }
                        }
                        
                        // if a sort order does not exist, then the order should be random, so randomize the array
                        if ($sort_order_exists == false) {
                            shuffle($ads);
                        }
                        
                        $output_ad_region_menu_items = '';
                        $output_ad_region_ads = '';
                        $output_ad_region_captions = '';
                        
                        // loop through the ads in order to prepare content for them
                        foreach ($ads as $ad) {
                            // if there is a label, then prepare menu item
                            if ($ad['label'] != '') {
                                $output_ad_region_menu_items .= '<li><a href="#software_ad_' . $ad['id'] . '">' . h($ad['label']) . '</a></li>' . "\n";
                            } else {
                                $output_ad_region_menu_items .= '<li><a href="#software_ad_' . $ad['id'] . '"></a></li>' . "\n";
                            }
                            
                            // if mode is edit, and user is at least a manager or if they have access to edit the containing ad region, then add edit button for images and edit button for ad
                            if (($mode == 'edit') && (($user['role'] < 3) || (in_array($ad_region_id, get_items_user_can_edit('ad_regions', $user['id'])) == true))) {
                                // add edit button for images
                                $ad['content'] = add_edit_button_for_images('ad', $ad['id'], $ad['content']);
                                
                                // add edit button for ad
                                // we had to remove "position: relative; " from the first line below
                                // because it was interfering with the transition effect for the dynamic ad region
                                $ad['content'] =
                                    '<div style="border: 1px dashed #788207">
                                        <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_ad.php?id=' . $ad['id'] . '&send_to=' . h(urlencode(get_request_uri())) . '"style="background: #788207; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 3px 8px !important; margin: -1px 25px 0 34px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Ad: ' . h($ad['name']) . '">' . $edit_label . '</a>
                                        ' . $ad['content'] . '
                                    </div>';
                            }
                            
                            $output_ad_region_ads .= '<div class="item" id="software_ad_' . $ad['id'] . '">' . $ad['content'] . '</div>' . "\n";

                            // If there is a caption for this ad, then output it.
                            if ($ad['caption'] != '') {
                                $output_ad_region_captions .=
                                    '<div id="software_ad_' . $ad['id'] . '_caption" class="caption">
                                        <div class="caption_content">
                                            ' . $ad['caption'] . '
                                        </div>
                                    </div>';
                            }
                        }
                        
                        // if the transition type is slide, then add the ads container to the ads,
                        // and remember that we need to include dynamic ad region javascript files
                        if ($ad_region_transition_type == 'slide') {
                            $output_ad_region_ads = 
                                '<div class="items">
                                    ' . $output_ad_region_ads . '
                                </div>';
                            
                            // remember that we need to include dynamic ad region javascript files
                            $include_dynamic_ad_region_javascript_files = true;
                        }

                        $output_previous_and_next_buttons = '';

                        // If there is more than one ad, then output previous and next buttons.
                        if (count($ads) > 1) {
                            $output_previous_and_next_buttons =
                                '<a class="previous" title="Previous"></a>
                                <a class="next" title="Next"></a>';
                        }
                        
                        $output_ad_region_interior_content =
                            '<div class="items_container">
                                ' . $output_ad_region_ads . '
                            </div>
                            ' . $output_ad_region_captions . '
                            <ul class="menu">
                                ' . $output_ad_region_menu_items . '
                            </ul>
                            ' . $output_previous_and_next_buttons;
                    }
                    
                    $ad_region_content =
                        $output_ad_region_javascript_initialization . '
                        <div id="software_ad_region_' . $ad_region_name . '" class="software_ad_region_dynamic">
                            ' . $output_ad_region_interior_content . '
                        </div>';
                }
                
                // if mode is edit, and user is at least a manager or if they have access to edit the containing ad region, then add edit container
                if (($mode == 'edit') && (($user['role'] < 3) || (in_array($ad_region_id, get_items_user_can_edit('ad_regions', $user['id'])) == true))) {
                    $ad_region_content =
                        '<div class="edit_mode" style="position: relative; border: 1px dashed #68201E; margin: -1px;">
                            <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_ad_region.php?id=' . $ad_region_id . '&send_to=' . h(urlencode(get_request_uri())) . '"style="background: #68201E; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Ad Region: ' . h($ad_region_name) . '">' . $edit_label . '</a>
                            <div style="padding: 0">' . $ad_region_content . '</div>
                        </div>';
                }
            }
            
            // replace the ad region tag with the ad region content
            $content = preg_replace('/<ad>.*?<\/ad>/i', addcslashes($ad_region_content, '\\$'), $content, 1);
        }

        // if we need to include dynamic ad region javascript files, then do so
        if ($include_dynamic_ad_region_javascript_files == true) {
            $dynamic_ad_region_javascript_includes =
                '<script type="text/javascript" src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery.scrollTo-1.4.2.min.js"></script>
                <script type="text/javascript" src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery.serialScroll-1.2.3b.min.js"></script>' . "\n";
            
            $content = preg_replace('/(<\/head>)/i', $dynamic_ad_region_javascript_includes . '$1', $content);
        }
    }
    
    // if ecommerce is enabled and there is a cart region, then prepare to output content for cart region
    if ((ECOMMERCE == true) && (preg_match('/<cart><\/cart>/i', $content) != 0)) {
        // get the number of items in the order
        $query = "SELECT SUM(quantity) FROM order_items WHERE order_id = '" . escape($_SESSION['ecommerce']['order_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $number_of_items = $row[0];
        
        $cart_region_content = '';
        
        // if there is at least 1 item in the order, then output cart region
        if ($number_of_items > 0) {
            // assume that the visitor has not visited a shopping cart or express order page until we find out otherwise
            $shopping_cart_or_express_order_page_id = 0;
            
            // if the visitor has visited a shopping cart page last, then use that page
            if (isset($_SESSION['ecommerce']['shopping_cart_page_id']) == TRUE) {
                $shopping_cart_or_express_order_page_id = $_SESSION['ecommerce']['shopping_cart_page_id'];
                
            // else if the visitor has visited an express order page last, then use that page
            } else if (isset($_SESSION['ecommerce']['express_order_page_id']) == TRUE) {
                $shopping_cart_or_express_order_page_id = $_SESSION['ecommerce']['express_order_page_id'];
            }
            
            // assume that we are not going to output a link until we find out otherwise
            $output_link_start = '';
            $output_link_end = '';
            
            // if the visitor has visited a shopping cart or express order page then prepare to link cart region
            if ($shopping_cart_or_express_order_page_id != 0) {
                // get page name in order to verify that page still exists and to prepare link
                $shopping_cart_or_express_order_page_name = get_page_name($shopping_cart_or_express_order_page_id);
                
                // if a page name was found, then prepare to link cart region
                if ($shopping_cart_or_express_order_page_name != '') {
                    $output_link_start = '<a class="cart-url" href="' . OUTPUT_PATH . h(encode_url_path($shopping_cart_or_express_order_page_name)) . '">';
                    $output_link_end = ' <span style="font-size: 120%" class="arrows">&raquo;</span></a>';
                }
            }
            
            $plural_suffix = '';
            // if the number of items is greater than 1, then prepare to output plural suffix
            if ($number_of_items > 1) {
                $plural_suffix = 's';
            }
            $item_label ='';
			if(language_ruler()==='en'){
				$item_label ='Item' . $plural_suffix;
			}else if(language_ruler()==='tr'){
				$item_label ='&Uuml;r&uuml;n';
			}
            // prepare cart region content
            $cart_region_content =
                '<div class="software_cart_region full">
                    ' . $output_link_start . '
						<span class="cart-number-of-items">' . number_format($number_of_items) . '</span>
                        <span class="items"> ' . $item_label . '&nbsp;</span>
                        <span class="cart-subtotal-prices">' . prepare_price_for_output(get_order_subtotal(), FALSE, $discounted_price = '', 'html') . '</span>
                    ' . $output_link_end . '
                </div>';

        // Otherwise output empty cart region div with class so it can be styled by designers.
        } else {
            $cart_region_content =
                '<div class="software_cart_region empty"></div>';
        }
        
        // replace cart region tag with cart region content
        $content = preg_replace('/<cart><\/cart>/i', addcslashes($cart_region_content, '\\$'), $content);
    }
    
    // replace login region with login form
    preg_match_all('/<login>.*?<\/login>/i', $content, $regions);
    foreach ($regions[0] as $region) {
        $login_region_name = strip_tags($region);
        
        $login_region_content = '';
        
        // get login region id
        $query = "SELECT id FROM login_regions WHERE name = '" . escape($login_region_name) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        // if a login region was found, then get login region content
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $login_region_id = $row['id'];
            $login_region_content = get_login_region_content($login_region_id);
        }
        
        // replace the login tags with the software login form
        $content = preg_replace('/<login>.*?<\/login>/i', addcslashes($login_region_content, '\\$'), $content, 1);
    }
    
    // replace code with menus
    preg_match_all('/<menu>.*?<\/menu>/i', $content, $regions);
    foreach ($regions[0] as $region) {
        $menu_name = strip_tags($region);
        $query =
            "SELECT
                id,
                name,
                effect,
                first_level_popup_position,
                second_level_popup_position
            FROM menus
            WHERE name = '" . escape($menu_name) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $menu_content = '';
        
        // if a menu was found, then get menu content
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $menu_id = $row['id'];
            $menu_effect = $row['effect'];
            $menu_first_level_popup_position = $row['first_level_popup_position'];
            $menu_second_level_popup_position = $row['second_level_popup_position'];
            
            // get the first menu item id that has a link page id equal to the current page id,
            // so that we can mark the current menu item and know which menu items should be opened, if this is an accordion menu
            // We don't currently support multiple menu items being set as the current menu item
            // because it causes problems for the accordion menu.  The logic for the accordion menu
            // assumes there is only one current menu item.  If multiple are set,
            // then menu items do not collapse properly.  We should eventually spend some time and resolve this
            // in order to add support for multiple current menu items.
            $query =  "SELECT id FROM menu_items WHERE (link_page_id = '" . escape($page_id) . "') AND (menu_id = '" . $menu_id . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            $current_menu_item_id = $row[0];
            
            if ($current_menu_item_id == '') {
                $current_menu_item_id = 0;
            }
            
            // get menu content
            $menu_content .= get_menu_content($menu_id, 0, $current_menu_item_id);

            // If this page is not being e-mailed and it is a pop-up menu, then initialize pop-up menu.
            if ($email == FALSE) {
                if ($menu_effect == 'Pop-up') {
                    $menu_content .= '<script>software.init_menu({name: "' . escape_javascript($menu_name) . '", effect: "popup", first_level_popup_position: "' . mb_strtolower($menu_first_level_popup_position) . '", second_level_popup_position: "' . mb_strtolower($menu_second_level_popup_position) . '"})</script>';
                    
                } else if ($menu_effect == 'Accordion') {
                    $menu_content .= '<script>software.init_menu({name: "' . escape_javascript($menu_name) . '", effect: "accordion"})</script>';
                }
            }
            
            // if mode is edit, and user is at least a manager or if they have access to edit this menu, then add edit container
            if (($mode == 'edit') && (($user['role'] < 3) || (in_array($menu_id, get_items_user_can_edit('menus', $user['id'])) == true))) {
                $menu_content =
                    '<div class="edit_mode" style="position: relative; border: 1px dashed #68201E; margin: -1px;">
                        <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_menu_items.php?id=' . $menu_id . '&from=pages&send_to=' . h(urlencode(get_request_uri())) . '"style="background: #68201E; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Menu: ' . h($row['name']) . '">' . $edit_label . '</a>
                        ' . $menu_content . '
                    </div>';
            }
        }
        
        // replace the menu tag with the menu content
        $content = preg_replace('/<menu>.*?<\/menu>/i', addcslashes($menu_content, '\\$'), $content, 1);
    }
    
    // replace code with menu sequence region
    preg_match_all('/<menu_sequence>.*?<\/menu_sequence>/i', $content, $regions);
    foreach ($regions[0] as $region) {
        $menu_name = strip_tags($region);
        $query = "SELECT id FROM menus WHERE name = '" . escape($menu_name) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $menu_sequence_content = '';
        
        // if a menu was found, then prepare and output the menu sequence region
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $menu_id = $row['id'];
            
            $menu_sequence = Array();
            
            // get the menu sequence
            $menu_sequence = get_menu_sequence($menu_id);
            
            $current_menu_item = Array();
            $current_menu_item_array_index = 0;
            
            // loop through each menu item to find the menu item that correlates to this page
            foreach($menu_sequence as $key => $menu_item) {
                // if the menu item's link to page is equal to this page's name, then set the current menu item, and save it's array index
                if ($menu_item['link_page_name'] == $page_name) {
                    $current_menu_item = $menu_item;
                    $current_menu_item_array_index = $key;
                    break;
                }
            }
            
            // if there is a current menu item, then continue to prepare and output the menu sequence region
            if (empty($current_menu_item) == FALSE) {
                $output_links = '';
                
                // if there is a previous menu item, then output the previous link
                if (is_array($menu_sequence[$current_menu_item_array_index - 1]) == TRUE) {
                    $output_links = '<a href="' . OUTPUT_PATH . h($menu_sequence[$current_menu_item_array_index - 1]['link_page_name']) . '" class="previous">&lt;</a>&nbsp;&nbsp;&nbsp;';
                }
                
                $next_menu_item_array_index = 0;
                
                // if there is a next menu item, then set it
                if (is_array($menu_sequence[$current_menu_item_array_index + 1]) == TRUE) {
                    $next_menu_item_array_index = $current_menu_item_array_index + 1;
                }
                
                // build the output
                $menu_sequence_content = '<div class="software_menu_sequence">' . $output_links . '<a href="' . OUTPUT_PATH . h($menu_sequence[$next_menu_item_array_index]['link_page_name']) . '" class="next">&gt;</a></div>';
            }
        }
        
        // replace the menu tag with the menu content
        $content = preg_replace('/<menu_sequence>.*?<\/menu_sequence>/i', addcslashes($menu_sequence_content, '\\$'), $content, 1);
    }

    // If there is a mobile switch region, then determine if we should output mobile switch.
    if (preg_match('/<mobile_switch><\/mobile_switch>/i', $content) != 0) {
        // If mobile site setting is enabled, then output mobile switch.
        if (MOBILE == true) {
            $output_mobile_switch_class = '';
            $output_mobile_switch_device_type = '';
            $output_mobile_switch_label = '';

            // prepare parts of the mobile switch differently based on the device type
            switch ($requested_device_type) {
                case 'desktop':
                default:
                    $output_mobile_switch_class = 'software_mobile_switch_desktop';
                    $output_mobile_switch_device_type = 'mobile';
                    $output_mobile_switch_label = 'Mobile Site';

                    break;
                
                case 'mobile':
                    $output_mobile_switch_class = 'software_mobile_switch_mobile';
                    $output_mobile_switch_device_type = 'desktop';
                    $output_mobile_switch_label = 'Full Site';

                    break;
            }


            $output_mobile_switch = '<div class="software_mobile_switch ' . $output_mobile_switch_class . '"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/update_device_type.php?device_type=' . $output_mobile_switch_device_type . '&amp;send_to=' . h(urlencode(get_request_uri())) . get_token_query_string_field() . '" class="software_button_tiny_secondary">' . $output_mobile_switch_label . '</a></div>';

        // Otherwise the mobile site setting is disabled, so output nothing.
        } else  {
            $output_mobile_switch = '';
        }
       
        $content = preg_replace('/<mobile_switch><\/mobile_switch>/i', addcslashes($output_mobile_switch, '\\$'), $content);
    }
    
    // replace tag cloud region with a tag cloud
    preg_match_all('/<tcloud>.*?<\/tcloud>/i', $content, $regions);
    foreach ($regions[0] as $region) {
        $search_results_page_name = strip_tags($region);
        
        // if the search results page name is blank, then use the first one in the databse
        if ($search_results_page_name == '') {
            $query = "SELECT page_name FROM page WHERE page_type = 'search results' LIMIT 1";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $row = mysqli_fetch_assoc($result);
            $search_results_page_name = $row['page_name'];
        }
        
        // get search catalog items and product group id for this page
        $query = 
            "SELECT 
                page.page_id,
                page.page_name,
                search_results_pages.search_catalog_items, 
                search_results_pages.product_group_id,
                search_results_pages.catalog_detail_page_id
            FROM search_results_pages 
            LEFT JOIN page ON page.page_id = search_results_pages.page_id
            WHERE page_name = '" . escape($search_results_page_name) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $search_results_page_id = $row['page_id'];
        $search_results_page_name = $row['page_name'];
        $search_catalog_items = $row['search_catalog_items'];
        $product_group_id = $row['product_group_id'];
        $catalog_detail_page_id = $row['catalog_detail_page_id'];
        
        $tag_cloud_content = '';
        
        // if there is a search results page name, then build the tag cloud
        if ($search_results_page_id != '') {
            $tag_cloud_keywords = array();
        
            // if the search results page is set to search products, then get tag cloud keywords for just catalog items (i.e. not pages)
            if ($search_catalog_items == 1) {
                $tag_cloud_keywords_xref = array();
                
                // get data from tag cloud xref table
                $query = "SELECT item_id, item_type FROM tag_cloud_keywords_xref WHERE search_results_page_id = '" . escape($search_results_page_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                while ($row = mysqli_fetch_assoc($result)) {
                    $tag_cloud_keywords_xref[] = $row;
                }
                
                // if there is at least one tag cloud keyword xref, then prepare query to get tag cloud keywords
                if (count($tag_cloud_keywords_xref) > 0) {
                    $where = '';
                    
                    // loop through the tag cloud xref records to build an sql where statement
                    foreach ($tag_cloud_keywords_xref as $tag_cloud_keyword_xref) {
                        if ($where != '') {
                            $where .= ' OR ';
                        }
                        
                        $where .= "(item_id = '" . escape($tag_cloud_keyword_xref['item_id']) . "' AND item_type = '" . escape($tag_cloud_keyword_xref['item_type']) . "')";
                    }
                    
                    // get the top 25 keywords
                    $query =
                        "SELECT
                            keyword,
                            COUNT(keyword) AS count
                       FROM tag_cloud_keywords
                       WHERE ($where)
                        GROUP BY keyword
                        ORDER BY
                           count DESC,
                           keyword ASC
                       LIMIT 25";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $tag_cloud_keywords[] = $row;
                    }
                }
            
            // else the search results page is set to not search products, so get tag cloud keywords for just pages
            } else {
                // get the top 25 keywords
                $query =
                    "SELECT
                        keyword,
                        COUNT(keyword) AS count
                    FROM tag_cloud_keywords
                    WHERE (item_type = 'page')
                   GROUP BY keyword
                   ORDER BY
                       count DESC,
                       keyword ASC
                    LIMIT 25";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $tag_cloud_keywords[] = $row;
                }
            }
            
            // if there is at least one tag cloud keyword, then output them
            if (count($tag_cloud_keywords) > 0) {
                // get the max and min font size percentage
                $max_font_size = 200;
                $min_font_size = 100;
                
                // get the max and min spread
                $max_spread = $tag_cloud_keywords[0]['count'];
                $min_spread = $tag_cloud_keywords[count($tag_cloud_keywords) - 1]['count'];
                
                // get the spread
                $spread = $max_spread - $min_spread;
                
                // initialize the step at one so that if there is not a spread, then the font size calculation will always return 100%
                $step = 1;
                
                // if the spread is not zero, then get the step
                if ($spread != 0) {
                    $step = ($max_font_size - $min_font_size) / ($spread);
                }
                
                // put the tag cloud keywords in alphabetical order
                sort($tag_cloud_keywords);
                
                // loop through the keywords and prepare them for output
                foreach ($tag_cloud_keywords as $tag_cloud_keyword) {
                    // if there is already a tag in the tag cloud content, then add a space for separation
                    if ($tag_cloud_content != '') {
                        $tag_cloud_content .= ' &nbsp; ';
                    }
                    
                    // get the font size for the keyword
                    $font_size = round($min_font_size + (($tag_cloud_keyword['count'] - $min_spread) * $step));
                    
                    // build the tag
                    $tag_cloud_content .= '<a href="' . OUTPUT_PATH . h(encode_url_path($search_results_page_name)) . '?query=' . h(urlencode($tag_cloud_keyword['keyword'])) . '" style="font-size: ' . $font_size . '% !important">' . h($tag_cloud_keyword['keyword']) . '</a>';
                }
                
                // build the tag cloud
                $tag_cloud_content = '<div class="software_tag_cloud">' . $tag_cloud_content . '</div>';
            }
        }
        
        // replace the tag cloud tags with the tag cloud
        $content = preg_replace('/<tcloud>.*?<\/tcloud>/i', addcslashes($tag_cloud_content, '\\$'), $content, 1);
    }
    
    // if there is a PDF region, then prepare PDF link
    if (preg_match('/<pdf><\/pdf>/i', $content) != 0) {
        // parse the current URL in order to prepare new PDF URL
        $parsed_url = parse_url(get_request_uri());
        
        // if there is a query string in the current URL, then add the pdf attribute to the query string
        if ((isset($parsed_url['query']) == true) && ($parsed_url['query'] != '')) {
            $query_string = '?' . $parsed_url['query'] . '&pdf=true';
            
        // else there is not a query string, so add one with pdf attribute
        } else {
            $query_string = '?pdf=true';
        }
        
        $pdf_link = '<div class="software_pdf_link"><a href="' . URL_SCHEME . HOSTNAME . h($parsed_url['path'] . $query_string) . '" target="_blank"><img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_pdf.gif" width="55" height="20" alt="PDF Version" title="PDF Version" border="0" /></a></div>';
        
        $content = preg_replace('/<pdf><\/pdf>/i', addcslashes($pdf_link, '\\$'), $content);
    }

    // Create an array to store data about RSS feeds, so that when we loop through system regions
    // we can add data for each feed so we will have it later when we need to output link tags in the head.
    $rss_feeds = array();

    $inline_editing_region_count = 0;
    
    // replace system region tags with system region content
    preg_match_all('/<system>.*?<\/system>/i', $content, $regions);
    foreach ($regions[0] as $region) {
        // get the system page name
        $system_page_name = strip_tags($region);
        
        $system_region_properties = array();
        
        // if the system page name is blank, then use the current page's info
        if ($system_page_name == '') {
            $system_region_properties['page_id'] = $page_id;
            $system_region_properties['page_name'] = $page_name;
            $system_region_properties['page_type'] = $page_type;
            $system_region_properties['comments'] = $comments;
            $system_region_properties['page_folder'] = $page_folder;
            $system_region_properties['page_title'] = $page_title;
            $system_region_properties['comments_label'] = $comments_label;
            $system_region_properties['comments_message'] = $comments_message;
            $system_region_properties['comments_allow_new_comments'] = $comments_allow_new_comments;
            $system_region_properties['comments_disallow_new_comment_message'] = $comments_disallow_new_comment_message;
            $system_region_properties['comments_automatic_publish'] = $comments_automatic_publish;
            $system_region_properties['comments_allow_user_to_select_name'] = $comments_allow_user_to_select_name;
            $system_region_properties['comments_require_login_to_comment'] = $comments_require_login_to_comment;
            $system_region_properties['comments_allow_file_attachments'] = $comments_allow_file_attachments;
            $system_region_properties['comments_show_submitted_date_and_time'] = $comments_show_submitted_date_and_time;
            $system_region_properties['comments_administrator_email_to_email_address'] = $comments_administrator_email_to_email_address;
            $system_region_properties['comments_administrator_email_conditional_administrators'] = $comments_administrator_email_conditional_administrators;
            $system_region_properties['comments_submitter_email_page_id'] = $comments_submitter_email_page_id;
            $system_region_properties['comments_watcher_email_page_id'] = $comments_watcher_email_page_id;
            $system_region_properties['comments_watchers_managed_by_submitter'] = $comments_watchers_managed_by_submitter;
            $system_region_properties['system_region_header'] = $system_region_header;
            $system_region_properties['system_region_footer'] = $system_region_footer;
            
        // else the system page name is not blank, so get its page info
        } else {
            $query = 
                "SELECT
                    page_id,
                    page_name,
                    page_type,
                    page_folder,
                    page_title,
                    comments,
                    comments_label,
                    comments_message,
                    comments_allow_new_comments,
                    comments_disallow_new_comment_message,
                    comments_automatic_publish,
                    comments_allow_user_to_select_name,
                    comments_require_login_to_comment,
                    comments_allow_file_attachments,
                    comments_show_submitted_date_and_time,
                    comments_administrator_email_to_email_address,
                    comments_administrator_email_conditional_administrators,
                    comments_submitter_email_page_id,
                    comments_watcher_email_page_id,
                    comments_watchers_managed_by_submitter,
                    system_region_header,
                    system_region_footer
                FROM page
                WHERE page_name = '" . escape($system_page_name) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $system_region_properties = $row;
        }
        
        // init the primary system region variable to false until proven otherwise
        // this is used to output content only for the primary system region, ie system content variable
        $primary_system_region = FALSE;
        
        // if the system page id is equal to the page id, then this is the primary system region
        if ($system_region_properties['page_id'] == $page_id) {
            $primary_system_region = TRUE;
        }

        // assume that the visitor does not have edit access to this page, until we find out otherwise
        $edit_access = false;
        
        // if the visitor has edit access to this page, then remember that (we will use this is several places below)
        if (check_edit_access($system_region_properties['page_folder']) == true) {
            $edit_access = true;
        }
        
        $system_output = '';

        // if this is the primary system region
        // or this secondary system region's page type is valid,
        // then prepare system region content
        if (
            ($primary_system_region == TRUE)
            || ($system_region_properties['page_type'] == 'folder view')
            || ($system_region_properties['page_type'] == 'photo gallery')
            || ($system_region_properties['page_type'] == 'custom form')
            || ($system_region_properties['page_type'] == 'form list view')
            || ($system_region_properties['page_type'] == 'form view directory')
            || ($system_region_properties['page_type'] == 'calendar view')
            || ($system_region_properties['page_type'] == 'catalog')
            || ($system_region_properties['page_type'] == 'express order')
            || ($system_region_properties['page_type'] == 'order form')
            || ($system_region_properties['page_type'] == 'shopping cart')
            || ($system_region_properties['page_type'] == 'search results')
        ) {
            // if this system region is a secondary system region then add system region header
            if ($primary_system_region == FALSE) {
                $output_system_region_header = $system_region_properties['system_region_header'];

                // If the mode is edit and the user has edit access to this system region's page,
                // then add edit button for images and edit button for system region header.
                if (($mode == 'edit') && ($edit_access == true)) {
                    if ($page_editor_version == 'latest') {
                        $inline_editing_region_count++;

                        // If content is empty, then add some default content so inline editor is not collapsed.
                        if ($output_system_region_header == '') {
                            $output_system_region_header = '<p>&nbsp;</p>';
                        }

                        $output_system_region_header =
                            '<div class="edit_mode" style="position: relative; border: 1px dashed black; margin: -1px;">
                                <div id="software_inline_editing_region_' . $inline_editing_region_count . '" class="software_system_region_header" title="System Region Header: ' . h($system_region_properties['page_name']) . '">
                                    ' . $output_system_region_header . '
                                </div>
                                <script>software.inline_editing.init_region({container_id: "software_inline_editing_region_' . $inline_editing_region_count . '", type: "system_region_header", id: ' . $system_region_properties['page_id'] . ', count: ' . $inline_editing_region_count . '})</script>
                            </div>';

                    } else {
                        // If content is empty, then add some default content to resolve spacing issues.
                        if ($output_system_region_header == '') {
                            $output_system_region_header = '<div style="padding: 5px">&nbsp;</div>';
                        }

                        $output_system_region_header = add_edit_button_for_images('system_region_header', $system_region_properties['page_id'], $output_system_region_header);

                        $output_system_region_header =
                            '<div class="edit_mode" style="position: relative; border: 1px dashed black; margin: -1px;">
                                <a href="javascript:void(0)" onclick="software_open_edit_region_dialog(\'' . $system_region_properties['page_id'] . '\', \'system_region_header\', \'\', \'\')" style="background: black; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="System Region Header: ' . h($system_region_properties['page_name']) . '">' . $edit_label . '</a>
                                <div class="software_system_region_header">
                                    ' . $output_system_region_header . '
                                </div>
                            </div>';
                    }

                // Otherwise the mode is not edit, so just wrap header with a container.
                } else {
                    $output_system_region_header = '<div class="software_system_region_header">' . $output_system_region_header . '</div>';
                }

                $system_output .= $output_system_region_header;
            }

            $access_control_type = '';

            // If this system region might have an RSS feed or social networking buttons,
            // then get access control type, because we will need it further below.
            // We want to find the access control now for performance reasons,
            // so that we don't get it multiple times, and so we only get it for system regions
            // that we need it for.  We don't show social buttons in edit mode in order to increase
            // performance for the editor.
            if (
                (
                    ($system_region_properties['page_type'] == 'form list view')
                    || ($system_region_properties['page_type'] == 'calendar view')
                    || ($system_region_properties['page_type'] == 'order form')
                    || ($system_region_properties['page_type'] == 'catalog')
                    || ($system_region_properties['page_type'] == 'catalog detail')
                )
                ||
                (
                    (SOCIAL_NETWORKING == TRUE)
                    &&
                    (
                        (
                            (SOCIAL_NETWORKING_TYPE == 'simple')
                            &&
                            (
                                (SOCIAL_NETWORKING_FACEBOOK == TRUE)
                                || (SOCIAL_NETWORKING_TWITTER == TRUE)
                                || (SOCIAL_NETWORKING_ADDTHIS == TRUE)
                                || (SOCIAL_NETWORKING_PLUSONE == TRUE)
                                || (SOCIAL_NETWORKING_LINKEDIN == TRUE)
                            )
                        )
                        || (SOCIAL_NETWORKING_TYPE == 'advanced')
                    )
                    && ($social_networking_position != 'disabled')
                    && ($email == FALSE)
                    && ($mode != 'edit')
                    && ($primary_system_region == TRUE)
                    &&
                    (
                        ($page_type == 'standard')
                        || ($page_type == 'folder view')
                        || ($page_type == 'photo gallery')
                        || ($page_type == 'custom form')
                        || ($page_type == 'form list view')
                        || ($page_type == 'form item view')
                        || ($page_type == 'form view directory')
                        || ($page_type == 'calendar view')
                        || ($page_type == 'calendar event view')
                        || ($page_type == 'catalog')
                        || ($page_type == 'catalog detail')
                        || ($page_type == 'order form')
                    )
                )
            ) {
                $access_control_type = get_access_control_type($system_region_properties['page_folder']);
            }

            $output_rss_url = '';

            // If this page has a page type that supports RSS and the page is public, then prepare RSS info.
            if (
                (
                    ($system_region_properties['page_type'] == 'form list view')
                    || ($system_region_properties['page_type'] == 'calendar view')
                    || ($system_region_properties['page_type'] == 'order form')
                    || ($system_region_properties['page_type'] == 'catalog')
                    || ($system_region_properties['page_type'] == 'catalog detail')
                )
                && ($access_control_type == 'public')
            ) {
                // Get RSS URL differently based on the page type.
                switch ($system_region_properties['page_type']) {
                    case 'form list view':
                        // Get the the custom form page id for this form list view.
                        $query =
                            "SELECT custom_form_page_id
                            FROM form_list_view_pages
                            WHERE
                                (page_id = '" . escape($system_region_properties['page_id']) . "')
                                AND (collection = 'a')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        $custom_form_page_id = $row['custom_form_page_id'];

                        // determine if there are any RSS fields for the custom form
                        $query = "SELECT COUNT(*) FROM form_fields WHERE (page_id = '" . $custom_form_page_id . "') AND (rss_field != '')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);
                        
                        // if there is at least one RSS field, then continue
                        if ($row[0] > 0) {
                            $output_rss_url = OUTPUT_PATH . h(encode_url_path($system_region_properties['page_name'])) . '?rss=true';
                        }
                        
                        break;
                        
                    case 'calendar view':
                    case 'order form':                    
                        $output_rss_url = OUTPUT_PATH . h(encode_url_path($system_region_properties['page_name'])) . '?rss=true';
                        break;
                        
                    case 'catalog':
                        // if there is not a search query then output RSS button
                        if ((isset($_GET[$system_region_properties['page_id'] . '_query']) == FALSE) && (isset($_GET['query']) == FALSE)) {
                            // if an address name has been passed in the current URL, then prepare RSS button link with address name
                            if (mb_strpos($_GET['page'], '/') !== FALSE) {
                                // get address name
                                $address_name = mb_substr(mb_substr($_GET['page'], mb_strpos($_GET['page'], '/')), 1);
                                
                                $output_rss_url = OUTPUT_PATH . h(encode_url_path($system_region_properties['page_name'])) . '/' . h(encode_url_path($address_name)) . '?rss=true';
                                
                            // else an address name has not been passed in the current URL, so prepare RSS button with just the page name
                            } else {
                                $output_rss_url = OUTPUT_PATH . h(encode_url_path($system_region_properties['page_name'])) . '?rss=true';
                            }
                        }
                        
                        break;
                        
                    case 'catalog detail':
                        // If there is a forward slash in the page name then determine if we should prepare the RSS URL.
                        if (mb_strpos($_GET['page'], '/') !== FALSE) {
                            $item = get_catalog_item_from_url();

                            // if the item that is being viewed is a product group, then prepare RSS URL.
                            if ($item['type'] == 'product group') {
                                $output_rss_url = OUTPUT_PATH . h(encode_url_path($system_region_properties['page_name'])) . '/' . h(encode_url_path($item['address_name'])) . '?rss=true';
                            }
                        }

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
                        
                        $image_cover = $item['image_name'];
                        //if product image xref or product group  xref exist. this mean this selected multiple product image
                        if(mysqli_num_rows($image_results) != 0){
                            
                            $product_images = '"' . URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($image_cover)) . '",';
                            while ($image = mysqli_fetch_assoc($image_results)){
                                $image_xref = $image['file_name'];
                                $product_images .= '"' . URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($image_xref)) . '",';
                            }
                            $output_product_images = '"image": [' . $product_images . '],';
                        }else{
                            //else if less an image selected and only one image selected, but there is code for action we output single image
                            if($image_cover){
                                $output_product_images = '"image": "' . URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($image_cover)) . '",';
                            }
                        }

                        $output_product_name = '';
                        $output_product_description = '';
                        $output_availability ='';
                        $output_product_brand = '';
                        $output_product_price = '';
                        $output_product_offers = '';
                        $output_product_squ = '';
                    
                        //if product name/squ/id
                        if($item['name']){
                            $output_product_squ = '"squ": "' . $item['name'] . '",';
                        }

                        //if product short_description
                        if($item['short_description']){
                            $output_product_name = '"name": "' . $item['short_description'] . '",';
                        }

                        //if product full_description
                        if($item['full_description']){
                            $output_product_description = '"description": "' . strip_tags($item['full_description']) . '",';
                        }

                        //if item is a product 
                        if($item['type'] == 'product'){
                            // get product price and modify it for output
                            $amount = str_replace(',', '', $item['price']);
                            $amount =  ($amount / 100);
                            $price = number_format($amount, 2);
                            $output_product_price = '"price": "' . $price . '",';

                            // if the inventory is enabled for the product,
                            // and the product is out of stock
                            if ( ($item['inventory'] == 1) && ($item['inventory_quantity'] == 0) ) {

                                if($item['backorder'] == 1){
                                    // if backorder enabled but not in stock.
                                    $output_availability = '"https://schema.org/PreOrder"';
                                }else{

                                    // else backorder disabled and not in stock.
                                    $output_availability = '"https://schema.org/OutOfStock"';
                                }
                            }else{

                                //otherwise product in stock anyway output instock
                                $output_availability = '"availability": "https://schema.org/InStock"';
                            }

                            //if brand specified
                            if($item['brand']){
                                // removed for an error
                                //$output_product_brand = '"brand:{"@type":"Brand","name":"' . $item['brand'] . '",},';
                            }
                    
                        //else item is a product group
                        }else{
                            // get all products currently in this product group
                            $query = 
                            "SELECT
                            product as id,
                            sort_order,
                            products.name,
                            products.enabled,
                            products.short_description,
                            products.price as price,
                            products.brand as brand,
                            inventory,
                            inventory_quantity,
                            backorder
                            FROM products_groups_xref
                            LEFT JOIN products on products.id = product
                            WHERE
                            product_group = '" . e($item['id']) . "'";

                            
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                            while ($row = mysqli_fetch_assoc($result)) {
                                                               
                                // get product price and modify it for output
                                $amount = str_replace(',', '', $row['price']);
                                $amount =  ($amount / 100);
                                $price = number_format($amount, 2);
                                $output_product_price = '"price": "' . $price . '",';
                                
                                // if the inventory is enabled for the product,
                                // and the product is out of stock
                                if ( ($row['inventory'] == 1) && ($row['inventory_quantity'] == 0) ) {
                                    
                                    if($row['backorder'] == 1){
                                        // if backorder enabled but not in stock.
                                        $output_availability = '"https://schema.org/PreOrder"';
                                    }else{
                                        
                                        // else backorder disabled and not in stock.
                                        $output_availability = '"https://schema.org/OutOfStock"';
                                    }
                                }else{
                                    
                                    //otherwise product in stock anyway output instock
                                    $output_availability = '"availability": "https://schema.org/InStock"';
                                }
                                
                                //if brand specified
                                if($row['brand']){
                                    // removed for an error
                                    //$output_product_brand = '"brand:{"@type":"Brand","name":"' . $row['brand'] . '",},';
                                }
                            }
                        }


            //prepare product offers to output in strutured_data_feeds
            $output_product_offers = '"offers": { "@type": "Offer", "url": "' . h($canonical_url) . '", "priceCurrency": "' . BASE_CURRENCY_CODE . '",' . $output_product_price . $output_availability . '}';       
        //strutured_data_feeds is generate strutured data for product view pages.                   
        $strutured_data_feeds .= '
        <!-- Start Structured Data -->
        <script type="application/ld+json">{"@context": "https://schema.org/","@type": "Product",' . $output_product_name . $output_product_images . $output_product_description . $output_product_squ . $output_product_brand . $output_product_offers . '}</script>
        <!-- End Structured Data -->';
   
                
                
                
                 
                    break;
                }
                
                // If the page has an RSS feed, then prepare title and store info in array.
                if ($output_rss_url != '') {
                    // If there is a page title, then use that for the RSS title.
                    // In the future we need to get the title differently for product groups on catalog and catalog detail pages,
                    // so that the title is unique for the product group.
                    if ($system_region_properties['page_title'] != '') {
                        $output_rss_title = h(HOSTNAME) . ' - ' . h(trim($system_region_properties['page_title']));
                        
                        // Otherwise output page name with notice.
                    } else {
                        $output_rss_title = h(HOSTNAME) . ' - [No Web Browser Title property found for ' . h($system_region_properties['page_name']) . ']';
                    }
                    
                    // If this is a primary system region, then set the sort to 0,
                    // so that its link tag will be ouputted first.
                    if ($primary_system_region == TRUE) {
                        $sort = 0;
                        
                        // Otherwise this is a secondary system region, so set the sort to 1.
                    } else {
                        $sort = 1;
                    }
                    
                    // Store RSS feed info in array so we have it later when we need to output RSS link tags.
                    $rss_feeds[] = array(
                        'sort' => $sort,
                        'output_title' => $output_rss_title,
                        'output_url' => $output_rss_url
                    );
                }
            }
            
            $output_social_networking_buttons = '';
            
            // If social networking is enabled in the site settings,
            // and type is simple with at least one service selected or type is advanced,
            // and social networking is enabled in this page's style,
            // and this page is not for an e-mail,
            // and edit mode is disabled,
            // and this is the primary system region,
            // and the page type is valid for social networking,
            // and the page is public,
            // then determine if we should output social networking buttons.
            // We don't show social buttons in edit mode in order to increase
            // performance for the editor.
            if (
                (SOCIAL_NETWORKING == TRUE)
                &&
                (
                    (
                        (SOCIAL_NETWORKING_TYPE == 'simple')
                        &&
                        (
                            (SOCIAL_NETWORKING_FACEBOOK == TRUE)
                            || (SOCIAL_NETWORKING_TWITTER == TRUE)
                            || (SOCIAL_NETWORKING_ADDTHIS == TRUE)
                            || (SOCIAL_NETWORKING_PLUSONE == TRUE)
                            || (SOCIAL_NETWORKING_LINKEDIN == TRUE)
                        )
                    )
                    || (SOCIAL_NETWORKING_TYPE == 'advanced')
                )
                && ($social_networking_position != 'disabled')
                && ($email == FALSE)
                && ($mode != 'edit')
                && ($primary_system_region == TRUE)
                &&
                (
                    ($page_type == 'standard')
                    || ($page_type == 'folder view')
                    || ($page_type == 'photo gallery')
                    || ($page_type == 'custom form')
                    || ($page_type == 'form list view')
                    || ($page_type == 'form item view')
                    || ($page_type == 'form view directory')
                    || ($page_type == 'calendar view')
                    || ($page_type == 'calendar event view')
                    || ($page_type == 'catalog')
                    || ($page_type == 'catalog detail')
                    || ($page_type == 'order form')
                )
                && ($access_control_type == 'public')
            ) {
                // If the position is top left or bottom left, then float the social networking buttons to the left.
                if (
                    ($social_networking_position == 'top_left')
                    || ($social_networking_position == 'bottom_left')
                ) {
                    $output_float = 'left';

                // Otherwise the position is top right or bottom right, so float the social networking buttons to the right.
                } else {
                    $output_float = 'right';
                }

                // If the social networking type is "simple", then output buttons in a certain way.
                if (SOCIAL_NETWORKING_TYPE == 'simple') {

                    $output_rss_button = '';

                    // If there is an RSS feed for this system region's page, then prepare RSS button.
                    if ($output_rss_url != '') {
                        $output_rss_button = '<div style="float: left; margin-right: 14px; padding-top: .5em"><a href="' . $output_rss_url . '"><img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_rss.png" width="27" height="20" alt="RSS" title="Subscribe to RSS feed" border="0" /></a></div>';
                    }
                    
                    $output_addthis_button = '';
                    
                    // if AddThis is enabled, then output button
                    if (SOCIAL_NETWORKING_ADDTHIS == TRUE) {
                        // we are using a margin-right of 20px instead of em's because that is what the twitter button appears to use.
                        $output_addthis_button =
                            '<div id="addthis_button" style="float: left; margin-right: 16px; padding-top: .5em; padding-bottom .5em">
                                <div class="addthis_toolbox addthis_default_style ">
                                    <a class="addthis_counter addthis_pill_style" addthis:url="' . h($canonical_url) . '" addthis:ui_click="true"></a>
                                </div>
                                <script type="text/javascript" src="//s7.addthis.com/js/250/addthis_widget.js#username=xa-4cf41f0f23f56dc2"></script>
                            </div>';
                    }
                    
                    $output_twitter_button = '';
                    
                    // if Twitter is enabled, then output button
                    if (SOCIAL_NETWORKING_TWITTER == TRUE) {
                        $output_twitter_button =
                            '<div id="twitter_button" style="float: left; padding-top: .5em; padding-bottom .5em">
                                <a href="http://twitter.com/share" class="twitter-share-button" data-url="' . h($canonical_url) . '" data-count="horizontal">Tweet</a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
                            </div>';
                    }
                    
                    $output_facebook_button = '';
                    
                    // if Facebook is enabled, then output button
                    // Originally, we did not add margin-right to the Facebook like button because Facebook added its own spacing on the right,
                    // however now Facebook appears to have removed its own spacing, so we are adding margin-right
                    // like the other buttons in order to prevent the like button from touching other buttons after it.
                    if (SOCIAL_NETWORKING_FACEBOOK == TRUE) {
                        $output_facebook_button =
                            '<div id="facebook_button" style="float: left; margin-right: 14px; padding-top: .5em; padding-bottom .5em">
                                <div id="fb-root"></div><script src="//connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:send href="' . h($canonical_url) . '"></fb:send>
                            </div>
                            <div id="facebook_button_count" style="float: left; margin-right: 14px; padding-top: .5em; padding-bottom .5em">
                                <fb:like href="' . h($canonical_url) . '" layout="button_count" show_faces="false"></fb:like>
                            </div>';
                    }

                    $output_linkedin_button = '';
                    
                    // if Linked In is enabled, then output button
                    if (SOCIAL_NETWORKING_LINKEDIN == TRUE) {
                        $output_linkedin_button =
                            '<div id="linkedin_button" style="float: left; margin-right: 14px; padding-top: .5em; padding-bottom .5em">
                                <script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
                                <script type="IN/Share" data-counter="right"></script>
                            </div>';
                    }
                    
                    $output_plusone_button = '';
                    
                    // if Google Plus One is enabled, then output button
                    if (SOCIAL_NETWORKING_PLUSONE == TRUE) {
                        $output_plusone_button =
                            '<div id="plusone_button" style="float: left; padding-top: .5em; padding-bottom .5em">
                                <script type="text/javascript">
                                    (function() {
                                        var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
                                        po.src = \'//apis.google.com/js/plusone.js\';
                                        var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
                                    })();</script>
                                    <g:plusone size="medium"></g:plusone>
                            </div>';
                    }

                    $output_social_networking_buttons =
                        '<div class="software_social_networking" style="float: ' . $output_float . '; margin-top: 1em; margin-bottom: 1em">
                            ' . $output_rss_button . '
                            ' . $output_addthis_button . '
                            ' . $output_twitter_button . '
                            ' . $output_facebook_button . '
                            ' . $output_linkedin_button . '
                            ' . $output_plusone_button . '
                            <div style="clear: both"></div>
                        </div>
                        <div style="clear: both"></div>';

                // Otherwise the social networking type is "advanced", so output code for buttons.
                } else {
                    // Get social networking code.
                    $query = "SELECT social_networking_code FROM config";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $social_networking_code = $row['social_networking_code'];

                    // If there is social networking code, then output it.
                    if ($social_networking_code != '') {

                        // If there is an RSS feed for this system region's page, then replace RSS tags with the content in them
                        // and replace RSS URL.
                        if ($output_rss_url != '') {
                            $social_networking_code = preg_replace('/<rss>(.*?)<\/rss>/si', '$1', $social_networking_code);
                            $social_networking_code = preg_replace('/{rss_url}/i', addcslashes($output_rss_url, '\\$'), $social_networking_code);

                        // Otherwise there is not an RSS feed, so replace RSS tags with empty string.
                        } else {
                            $social_networking_code = preg_replace('/<rss>.*?<\/rss>/si', '', $social_networking_code);
                        }

                        // Replace URL placeholder with URL.
                        $social_networking_code = preg_replace('/{url}/i', addcslashes(h($canonical_url), '\\$'), $social_networking_code);

                        $output_social_networking_buttons =
                            '<div class="software_social_networking" style="float: ' . $output_float . '; margin-top: 1em; margin-bottom: 1em">
                                ' . $social_networking_code . '
                            </div>
                            <div style="clear: both"></div>';
                    }
                }
            }

            // If there are social networking buttons and the position is top, then output them here.
            if (
                ($output_social_networking_buttons != '')
                &&
                (
                    ($social_networking_position == 'top_left')
                    || ($social_networking_position == 'top_right')
                )
            ) {
                $system_output .= $output_social_networking_buttons;
            }
            
            // determine what type of page this is and output it's content
            switch ($system_region_properties['page_type']) {
                case 'change password':
                    
                    require_once(dirname(__FILE__) . '/get_change_password.php');

                    $system_output .= get_change_password(array(
                            'page_id' => $system_region_properties['page_id']));

                    break;

                case 'set password':

                    require_once(dirname(__FILE__) . '/get_set_password.php');

                    $system_output .= get_set_password(array(
                        'page_id' => $system_region_properties['page_id'],
                        'folder_id' => $page_folder));
                    
                    break;
                    
                case 'email a friend':
                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);

                    require_once(dirname(__FILE__) . '/get_email_a_friend_screen_content.php');

                    $system_output .= '<div class="software_email_a_friend">' .
                        get_email_a_friend_screen_content(
                            array (
                                'current_page_id'=>$system_region_properties['page_id'], 
                                'submit_button_label'=>$properties['submit_button_label'])) . '</div>';
                    break;

                case 'error':
                    $system_output .= '<div class="software_error_page software_error">';
                        // if there is system content, and if this is the primary system region, then use system content
                        if (($system_content) && ($primary_system_region == TRUE)) {
                              $system_output .= $system_content;
                        // else, assign system output
                        } else {
                            $system_output .= '<i>Site error messages will appear here.</i>';
                        }
                    $system_output .= '</div>';
                    break;

                case 'folder view':
                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);

                    require_once(dirname(__FILE__) . '/get_folder_view_screen_content.php');
                    
                    $system_output .= get_folder_view_screen_content(array(
                        'current_page_id'=>$system_region_properties['page_id'], 
                        'pages'=>$properties['pages'], 
                        'files'=>$properties['files']));

                    break;

                case 'forgot password':

                    // if there is system content, and if this is the primary system region, then use system content
                    if (($system_content) && ($primary_system_region == TRUE)) {
                        $system_output .= $system_content;

                    // else, assign system output
                    } else {
                        require_once(dirname(__FILE__) . '/get_forgot_password.php');

                        $system_output .= get_forgot_password(array(
                            'page_id' => $system_region_properties['page_id']));
                    }

                    break;

                case 'login':
                    $system_output .= '<div class="software_login">';

                    // if there is system content, and if this is the primary system region, then use system content
                    if (($system_content) && ($primary_system_region == TRUE)) {
                        $system_output .= $system_content;
                    // else, get system content
                    } else {
                        require_once(dirname(__FILE__) . '/get_login.php');

                        $system_output .= get_login(array(
                            'page_id' => $system_region_properties['page_id']));
                    }

                    $system_output .= '</div>';

                    break;

                case 'logout':
                    $system_output .= '<div class="software_logout">';
                    // if there is system content, and if this is the primary system region, then use system content
                    if (($system_content) && ($primary_system_region == TRUE)) {
                        $system_output .= $system_content;
                    // else, get system content
                    } else {
                        require_once(dirname(__FILE__) . '/get_logout_screen_content.php');

                        $system_output .= get_logout_screen_content();
                    }
                    $system_output .= '</div>';
                    break;
                    
                case 'photo gallery':
                    
                    $editable = false;
                    
                    // if mode is edit then edit container should be outputted
                    if ($mode == 'edit') {
                        $editable = true;
                    }

                    require_once(dirname(__FILE__) . '/get_photo_gallery.php');
                    
                    $system_output .= get_photo_gallery(array(
                                'page_id' => $system_region_properties['page_id'], 
                                'editable' => $editable,
                                'device_type' => $device_type));

                    break;
                
                case 'membership confirmation':
                    $system_output .= '<div class="software_membership_confirmation">';
                    // if there is system content, and if this is the primary system region, then use system content
                    if (($system_content) && ($primary_system_region == TRUE)) {
                        $system_output .= $system_content;
                    // else, get system content
                    } else {
                        require_once(dirname(__FILE__) . '/get_membership_confirmation_screen_content.php');

                        $system_output .= get_membership_confirmation_screen_content();
                    }
                    $system_output .= '</div>';
                    break;
                
                case 'membership entrance':
                    $system_output .= '<div class="software_membership_entrance">';

                    // if there is system content, and if this is the primary system region, then use system content
                    if (($system_content) && ($primary_system_region == TRUE)) {
                        $system_output .= $system_content;
                        
                    // else, get system content
                    } else {
                        require_once(dirname(__FILE__) . '/get_membership_entrance.php');

                        $system_output .= get_membership_entrance(array(
                            'page_id' => $system_region_properties['page_id'],
                            'device_type' => $device_type));
                    }

                    $system_output .= '</div>';

                    break;

                case 'my account':
                    require_once(dirname(__FILE__) . '/get_my_account.php');

                    $system_output .= get_my_account(array(
                            'page_id' => $system_region_properties['page_id']));

                    break;
                
                case 'my account profile':
                    require_once(dirname(__FILE__) . '/get_my_account_profile.php');

                    $system_output .=
                        '<div class="software_my_account_profile">
                            ' . get_my_account_profile(array(
                                'page_id' => $system_region_properties['page_id'])) . '
                        </div>';

                    break;
                    
                case 'email preferences':
                    require_once(dirname(__FILE__) . '/get_email_preferences.php');

                    $system_output .=
                        '<div class="software_email_preferences">
                            ' . get_email_preferences(array(
                                'page_id' => $system_region_properties['page_id'])) . '
                        </div>';

                    break;
                    
                case 'view order':
                    require_once(dirname(__FILE__) . '/get_view_order_screen_content.php');

                    $system_output .= '<div class="software_view_order">' . get_view_order_screen_content(array('device_type' => $device_type)) . '</div>';
                    
                    break;

                case 'update address book':
                    
                    require_once(dirname(__FILE__) . '/get_update_address_book.php');

                    $system_output .= '<div class="software_update_address_book">' . 
                        get_update_address_book(
                            array(
                                'page_id' => $system_region_properties['page_id']
                            )
                        ) . '</div>';

                    break;

                case 'custom form':

                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);
                    
                    $editable = false;
                    
                    // if mode is edit then edit container should be outputted
                    if ($mode == 'edit') {
                        $editable = true;
                    }
                    
                    require_once(dirname(__FILE__) . '/get_custom_form_screen_content.php');

                    $system_output .=
                        '<div class="software_custom_form">' .
                            get_custom_form_screen_content(array(
                                'current_page_id' => $system_region_properties['page_id'],
                                'form_name' => $properties['form_name'],
                                'enabled' => $properties['enabled'],
                                'label_column_width' => $properties['label_column_width'],
                                'watcher_page_id' => $properties['watcher_page_id'],
                                'save' => $properties['save'],
                                'submit_button_label' => $properties['submit_button_label'],
                                'membership' => $properties['membership'],
                                'membership_days' => $properties['membership_days'],
                                'confirmation_type' => $properties['confirmation_type'],
                                'confirmation_message' => $properties['confirmation_message'],
                                'return_type' => $properties['return_type'],
                                'return_message' => $properties['return_message'],
                                'return_page_id' => $properties['return_page_id'],
                                'return_alternative_page' => $properties['return_alternative_page'],
                                'return_alternative_page_contact_group_id' => $properties['return_alternative_page_contact_group_id'],
                                'return_alternative_page_id' => $properties['return_alternative_page_id'],
                                'editable' => $editable,
                                'device_type' => $device_type,
                                'folder_id_for_default_value' => $page_folder)) .
                        '</div>';

                    break;

                case 'custom form confirmation':
                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);

                    require_once(dirname(__FILE__) . '/get_custom_form_confirmation_screen_content.php');

                    $system_output .=
                        '<div class="software_custom_form_confirmation">
                            ' . get_custom_form_confirmation_screen_content(array(
                                    'current_page_id'=>$system_region_properties['page_id'],
                                    'continue_button_label'=>$properties['continue_button_label'],
                                    'next_page_id'=>$properties['next_page_id'],
                                    'form_id' => $dynamic_properties['form_id'])) . '
                        </div>';

                    break;

                case 'form list view':
                    
                    $editable = false;
                    
                    // if mode is edit then edit container should be outputted
                    if ($mode == 'edit') {
                        $editable = true;
                    }

                    require_once(dirname(__FILE__) . '/get_form_list_view.php');

                    $system_output .= get_form_list_view(array(
                        'page_id' => $system_region_properties['page_id'],
                        'editable' => $editable,
                        'email' => $email));
                    
                    break;

                case 'form item view':
                    
                    $editable = false;
                    
                    // if mode is edit then edit container should be outputted
                    if ($mode == 'edit') {
                        $editable = true;
                    }

                    require_once(dirname(__FILE__) . '/get_form_item_view.php');
                    
                    $output_form_item_view = get_form_item_view(array(
                        'page_id' => $system_region_properties['page_id'],
                        'form_id' => $dynamic_properties['form_id'],
                        'editable' => $editable));
                    
                    // if this form item view page is editable, then add edit buttons to images in content
                    if ($editable == true) {
                        $output_form_item_view = add_edit_button_for_images('form_item_view', 0, $output_form_item_view);
                    }

                    $system_output .= $output_form_item_view;
                    
                    break;
                    
                case 'form view directory':
                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);

                    require_once(dirname(__FILE__) . '/get_form_view_directory_screen_content.php');

                    $system_output .= '<div class="software_form_view_directory">' . 
                        get_form_view_directory_screen_content(
                            array (
                                'current_page_id' => $system_region_properties['page_id'], 
                                'summary' => $properties['summary'],
                                'summary_days' => $properties['summary_days'],
                                'summary_maximum_number_of_results' => $properties['summary_maximum_number_of_results'],
                                'form_list_view_heading' => $properties['form_list_view_heading'],
                                'subject_heading' => $properties['subject_heading'],
                                'number_of_submitted_forms_heading' => $properties['number_of_submitted_forms_heading']
                            )
                    
                        ) . '</div>';

                    break;
                    
                case 'calendar view':
                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);

                    require_once(dirname(__FILE__) . '/get_calendar_view_screen_content.php');

                    $system_output .= '<div class="software_calendar_view">' . get_calendar_view_screen_content(array('current_page_id'=>$system_region_properties['page_id'], 'default_view'=>$properties['default_view'], 'number_of_upcoming_events'=>$properties['number_of_upcoming_events'], 'calendar_event_view_page_id'=>$properties['calendar_event_view_page_id'], 'device_type' => $device_type)) . '</div>';

                    break;
                    
                case 'calendar event view':
                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);
                    
                    // if mode is edit then edit container should be outputted
                    $editable = false;
                    
                    if ($mode == 'edit') {
                        $editable = true;
                    }

                    require_once(dirname(__FILE__) . '/get_calendar_event_view_screen_content.php');
                    
                    $output_calendar_event_view =
                        '<div class="software_calendar_event_view">
                            ' . get_calendar_event_view_screen_content(array(
                                    'current_page_id' => $system_region_properties['page_id'],
                                    'notes' => $properties['notes'],
                                    'back_button_label' => $properties['back_button_label'],
                                    'editable' => $editable,
                                    'calendar_event_id' => $dynamic_properties['calendar_event_id'])) . '
                        </div>';
                    
                    // if the mode is edit then determine if user has access to edit calendar event in order to determine if edit container should be outputted
                    if ($mode == 'edit') {
                        // get calendar event info
                        $query =
                            "SELECT
                                name,
                                published
                            FROM calendar_events
                            WHERE id = '" . escape($_GET['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $calendar_event = mysqli_fetch_assoc($result);
                        
                        // if the user has access to edit calendar event, then output edit container
                        if (
                            ($user['role'] < 3)
                            ||
                            (
                                ($user['manage_calendars'] == TRUE) 
                                && (validate_calendar_event_access($_GET['id']) == TRUE)
                                &&
                                (
                                    ($calendar_event['published'] == 0)
                                    || ($user['publish_calendar_events'] == TRUE)
                                )
                            )
                        ) {
                            $output_query_string_recurrence_number = '';
                            
                            // if there is a recurrence number, then add recurrence number to query string
                            if (isset($_GET['recurrence_number']) == TRUE) {
                                $output_query_string_recurrence_number = '&recurrence_number=' . h($_GET['recurrence_number']);
                            }
                            
                            // add edit container to calendar event
                            $output_calendar_event_view = '<div class="edit_mode" style="position: relative; border: 1px dashed #FF9539; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_calendar_event.php?id=' . h($_GET['id']) . $output_query_string_recurrence_number . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #FF9539; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Calendar Event: ' . h($calendar_event['name']) . '">' . $edit_label . '</a>'. $output_calendar_event_view . '</div>';
                        }
                    }

                    $system_output .= $output_calendar_event_view;
                    
                    break;
                    
                case 'catalog':

                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);
                
                    // set the catalog product group, to be used later on
                    $catalog_product_group_id = $properties['product_group_id'];
                    
                    $editable = false;
                    
                    // if mode is edit and user has access to e-commerce, then enable edit mode
                    if (($mode == 'edit') && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
                        $editable = true;
                    }

                    require_once(dirname(__FILE__) . '/get_catalog.php');

                    $system_output .= get_catalog(array(
                        'page_id' => $system_region_properties['page_id'],
                        'editable' => $editable,
                        'device_type' => $device_type));
                    
                    break;
                    
                case 'catalog detail':
                    
                    $editable = false;
                    
                    // if mode is edit and user has access to e-commerce, then enable edit mode
                    if (($mode == 'edit') && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
                        $editable = true;
                    }

                    require_once(dirname(__FILE__) . '/get_catalog_detail.php');

                    $output_catalog_detail = get_catalog_detail(array(
                        'page_id' => $system_region_properties['page_id'],
                        'editable' => $editable,
                        'device_type' => $device_type));

                    // if there is a forward slash in the page name then get item information (we will use this in several places below) and output edit container if necessary
                    // we will use this information in several places below
                    if (mb_strpos($_GET['page'], '/') !== FALSE) {
                        $item = array();
                        $item = get_catalog_item_from_url();
                        
                        // if the mode is edit and the user has access to e-commerce, add edit container for product group or product
                        if (($mode == 'edit') && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
                            // if the item is a product group, then set information in a certain way
                            if ($item['type'] == 'product group') {
                                // output edit product group page to link
                                $output_link_page = 'edit_product_group.php';
                                
                                // output title for tooltip
                                $output_tooltip_title = "Product Group";
                                
                            // else the item is a product, so set information in a different way
                            } else {
                                // output edit product page to link
                                $output_link_page = 'edit_product.php';
                                
                                // output title for tooltip
                                $output_tooltip_title = "Product";
                            }
                            
                            // if there is a short description then use it for the tooltop
                            if ($item['short_description'] != '') {
                                $output_tooltip = $item['short_description'];
                            
                            // else if there is a name, then use it for the tooltip
                            } elseif ($item['name'] != '') {
                                $output_tooltip = $item['name'];
                            }
                            
                            // add edit container to product group
                            $output_catalog_detail = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/' . $output_link_page . '?id=' . $item['id'] . '&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $output_tooltip_title . ': ' . h($output_tooltip) . '">' . $edit_label . '</a>'. $output_catalog_detail . '</div>';
                        }
                    }

                    $system_output .= $output_catalog_detail;
                    
                    break;

                case 'express order':
                    
                    $editable = false;
                    
                    // if mode is edit and user has access to e-commerce, then enable edit mode
                    if (($mode == 'edit') && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
                        $editable = true;
                    }
                    
                    require_once(dirname(__FILE__) . '/get_express_order.php');

                    $system_output .= get_express_order(array(
                        'page_id'=>$system_region_properties['page_id'],
                        'editable'=>$editable,
                        'device_type' => $device_type,
                        'folder_id_for_default_value' => $page_folder));

                    break;

                case 'order form':

                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);

                    // Set the order form product group, to be used later on.
                    $order_form_product_group_id = $properties['product_group_id'];
                    
                    $editable = false;
                    
                    // if mode is edit and user has access to e-commerce, then enable edit mode
                    if (($mode == 'edit') && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
                        $editable = true;
                    }

                    require_once(dirname(__FILE__) . '/get_order_form.php');
                    
                    $output_order_form =
                        '<div class="software_order_form">' .
                            get_order_form(array(
                                'page_id' => $system_region_properties['page_id'],
                                'editable' => $editable,
                                'device_type' => $device_type)) . '
                        </div>';
                    
                    // if mode is edit and user has access to e-commerce, add edit container for product group
                    if (($mode == 'edit') && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
                        // Get product group name
                        $query =
                            "SELECT
                               id,
                               name,
                               short_description
                            FROM product_groups 
                            WHERE id = '" . escape($properties['product_group_id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        
                        // If there is a short description
                        if ($row['short_description'] != '') {
                            // Output the short description in the tooptip
                            $output_tooltip = $row['short_description'];
                        
                        // Else if there is a product group name
                        } elseif ($row['name'] != '') {
                            // Output the product group name
                            $output_tooltip = $row['name'];
                        }
                        
                        // add edit container to product group
                        $output_order_form = '<div class="edit_mode" style="position: relative; border: 1px dashed #69A823; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_product_group.php?id=' . $row['id'] . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #69A823; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style normal; font-weight: bold; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Product Group: ' . h($output_tooltip) . '">' . $edit_label . '</a>'. $output_order_form . '</div>';
                    }

                    $system_output .= $output_order_form;
                    
                    break;

                case 'shopping cart':
                    
                    $editable = false;
                    
                    // if mode is edit and user has access to e-commerce, then enable edit mode
                    if (($mode == 'edit') && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
                        $editable = true;
                    }
                    
                    require_once(dirname(__FILE__) . '/get_shopping_cart.php');

                    $system_output .= get_shopping_cart(array(
                        'page_id' => $system_region_properties['page_id'],
                        'editable' => $editable,
                        'device_type' => $device_type,
                        'folder_id_for_default_value' => $page_folder));
                    
                    break;

                case 'shipping address and arrival':
                    
                    // If mode is edit then edit container should be outputted.
                    if ($mode == 'edit') {
                        $editable = true;
                    } else {
                        $editable = false;
                    }

                    require_once(dirname(__FILE__) . '/get_shipping_address_and_arrival.php');

                    $system_output .= get_shipping_address_and_arrival(array(
                        'page_id' => $system_region_properties['page_id'],
                        'editable' => $editable,
                        'device_type' => $device_type,
                        'folder_id_for_default_value' => $page_folder));

                    break;

                case 'shipping method':

                    require_once(dirname(__FILE__) . '/get_shipping_method.php');

                    $system_output .= get_shipping_method(array(
                        'page_id' => $system_region_properties['page_id']));

                    break;

                case 'billing information':
                    
                    // If mode is edit then edit container should be outputted.
                    if ($mode == 'edit') {
                        $editable = true;
                    } else {
                        $editable = false;
                    }

                    require_once(dirname(__FILE__) . '/get_billing_information.php');

                    $system_output .= get_billing_information(array(
                        'page_id' => $system_region_properties['page_id'],
                        'editable' => $editable,
                        'device_type' => $device_type,
                        'folder_id_for_default_value' => $page_folder));

                    break;

                case 'order preview':

                    require_once(dirname(__FILE__) . '/get_order_preview.php');

                    $system_output .= get_order_preview(array(
                        'page_id' => $system_region_properties['page_id'],
                        'device_type' => $device_type));

                    break;

                case 'order receipt':

                    require_once(dirname(__FILE__) . '/get_order_receipt.php');
                    
                    $system_output .= get_order_receipt(array(
                        'page_id' => $system_region_properties['page_id'],
                        'device_type' => $device_type));

                    break;

                case 'registration confirmation':
                    $system_output .= '<div class="software_registration_confirmation">';

                    // if there is system content, and if this is the primary system region, then use system content
                    if (($system_content) && ($primary_system_region == TRUE)) {
                        $system_output .= $system_content;
                    // else, get system content
                    } else {
                        require_once(dirname(__FILE__) . '/get_registration_confirmation_screen_content.php');

                        $system_output .= get_registration_confirmation_screen_content();
                    }

                    $system_output .= '</div>';

                    break;

                case 'registration entrance':
                    $system_output .= '<div class="software_registration_entrance">';

                    // if there is system content, and if this is the primary system region, then use system content
                    if (($system_content) && ($primary_system_region == TRUE)) {
                        $system_output .= $system_content;

                    // else, get system content
                    } else {
                        require_once(dirname(__FILE__) . '/get_registration_entrance.php');

                        $system_output .= get_registration_entrance(array(
                            'page_id' => $system_region_properties['page_id'],
                            'device_type' => $device_type));
                    }

                    $system_output .= '</div>';

                    break;

                case 'search results':

                    // If mode is edit then edit container should be outputted.
                    if (
                        ($mode == 'edit')
                        && (
                            ($user['role'] < 3)
                            || ($user['manage_ecommerce'] == true)
                        )
                    ) {
                        $editable = true;
                    } else {
                        $editable = false;
                    }

                    require_once(dirname(__FILE__) . '/get_search_results.php');

                    $system_output .= get_search_results(array(
                        'page_id' => $system_region_properties['page_id'],
                        'editable' => $editable));

                    break;
                    
                case 'affiliate sign up form':
                    $properties = get_page_type_properties($system_region_properties['page_id'], $system_region_properties['page_type']);

                    require_once(dirname(__FILE__) . '/get_affiliate_sign_up_form_screen_content.php');

                    $system_output .= '<div class="software_affiliate_sign_up_form">' . get_affiliate_sign_up_form_screen_content(array('current_page_id'=>$system_region_properties['page_id'], 'terms_page_id'=>$properties['terms_page_id'], 'submit_button_label'=>$properties['submit_button_label'], 'next_page_id'=>$properties['next_page_id'])) . '</div>';

                    break;
                    
                case 'affiliate sign up confirmation':
                    require_once(dirname(__FILE__) . '/get_affiliate_sign_up_confirmation_screen_content.php');

                    $system_output .= '<div class="software_affiliate_sign_up_confirmation">' . get_affiliate_sign_up_confirmation_screen_content() . '</div>';

                    break;
                    
                case 'affiliate welcome':
                    require_once(dirname(__FILE__) . '/get_affiliate_welcome_screen_content.php');

                    $system_output .= '<div class="software_affiliate_welcome">' . get_affiliate_welcome_screen_content() . '</div>';

                    break;
            }

            // If there are social networking buttons and the position is bottom, then output them here.
            if (
                ($output_social_networking_buttons != '')
                &&
                (
                    ($social_networking_position == 'bottom_left')
                    || ($social_networking_position == 'bottom_right')
                )
            ) {
                $system_output .= $output_social_networking_buttons;
            }

            // If there is an RSS feed for this system region's page,
            // and the RSS button was not included in the social networking buttons because there were none,
            // then output solo RSS button.
            if (
                ($output_rss_url != '')
                && ($output_social_networking_buttons == '')
            ) {
                $system_output .= '<div class="software_rss_link" style="margin-top: .5em; margin-bottom: .5em"><a href="' . $output_rss_url . '"><img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_rss.png" width="27" height="20" alt="RSS" title="Subscribe to RSS feed" border="0" /></a></div>';
            }
            
            // if comments are turned on for this page then output comments, add comment form, and watch comments area
            if ($system_region_properties['comments'] == '1') {
                $item_id = '0';
                $item_type = '';
                $sql_where = '';
                $sql_where_watchers = '';

                $comment_label = get_comment_label(array('label' => $system_region_properties['comments_label']));
                $output_comment_label = h($comment_label);
                $comment_label_lowercase = mb_strtolower($comment_label);
                $output_comment_label_lowercase = h($comment_label_lowercase);
                
                include_once('liveform.class.php');
                $liveform = new liveform('add_comment', $system_region_properties['page_id']);
                
                // if this page has a page type which shows data for different items, then get item id, item type, and prepare sql filter
                if (
                    ($system_region_properties['page_type'] == 'catalog')
                    || ($system_region_properties['page_type'] == 'catalog detail')
                    || ($system_region_properties['page_type'] == 'calendar event view')
                    || ($system_region_properties['page_type'] == 'form item view')
                ) {
                    switch($system_region_properties['page_type']) {
                        case 'catalog':
                            // if there is a forward slash in the page name then get the items id
                            if (mb_strpos($_GET['page'], '/') !== FALSE) {
                                $item = get_catalog_item_from_url();
                                $item_id = $item['id'];
                                
                            // else if there is a default product group set, then use that
                            } elseif ($catalog_product_group_id != 0) {
                                $item_id = $catalog_product_group_id;
                                
                            // else no product group can be found, so get top-level product group
                            } else {
                                $query = "SELECT id FROM product_groups WHERE parent_id = '0'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                $row = mysqli_fetch_assoc($result);
                                $item_id = $row['id'];
                            }
                            
                            $item_type = 'product_group';
                            
                            break;
                        
                        case 'catalog detail':
                            // if there is a forward slash in the page name then get the items information
                            if (mb_strpos($_GET['page'], '/') !== FALSE) {
                                $item = get_catalog_item_from_url();
                                $item_id = $item['id'];
                                
                                // if the item type is a product group, then set the item type accordingly
                                if ($item['type'] == 'product group') {
                                    $item_type = 'product_group';
                                    
                                // else if item type is a product, then set the item type accordingly
                                } elseif ($item['type'] == 'product') {
                                    $item_type = 'product';
                                }
                            }
                            break;
                            
                        case 'calendar event view':
                            // If the calendar event id was passed via the dynamic properties (e.g. by the site search indexing)
                            // then use that for calendar event id.
                            if (isset($dynamic_properties['calendar_event_id']) == true) {
                                $item_id = $dynamic_properties['calendar_event_id'];
                                $item_type = 'calendar_event';

                            // Otherwise if an id was passed through the URL, then use that for the calendar event id.
                            } else if ($_GET['id']) {
                                $item_id = $_GET['id'];
                                $item_type = 'calendar_event';
                            }

                            break;
                            
                        case 'form item view':
                            // If the form id was passed via the dynamic properties (e.g. by the site search indexing)
                            // then get information for submitted form in a certain way.
                            if (isset($dynamic_properties['form_id']) == true) {
                                $submitted_form = db_item(
                                    "SELECT
                                        forms.id,
                                        forms.form_editor_user_id,
                                        user.user_username AS form_editor_username,
                                        user.user_email AS form_editor_email_address,
                                        forms.user_id
                                    FROM forms
                                    LEFT JOIN user ON forms.form_editor_user_id = user.user_id
                                    WHERE forms.id = '" . escape($dynamic_properties['form_id']) . "'");
                                
                                $item_id = $submitted_form['id'];
                                $form_editor_user_id = $submitted_form['form_editor_user_id'];
                                $form_editor_username = $submitted_form['form_editor_username'];
                                $form_editor_email_address = $submitted_form['form_editor_email_address'];
                                $form_submitter_user_id = $submitted_form['user_id'];
                                $item_type = 'submitted_form';

                            // Otherwise if a reference code was passed through the URL, then get submitted form info in a different way.
                            } else if ($_GET['r']) {
                                $query =
                                    "SELECT
                                        forms.id,
                                        forms.form_editor_user_id,
                                        user.user_username AS form_editor_username,
                                        user.user_email AS form_editor_email_address,
                                        forms.user_id
                                    FROM forms
                                    LEFT JOIN user ON forms.form_editor_user_id = user.user_id
                                    WHERE forms.reference_code = '" . escape($_GET['r']) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                $row = mysqli_fetch_assoc($result);
                                
                                $item_id = $row['id'];
                                $form_editor_user_id = $row['form_editor_user_id'];
                                $form_editor_username = $row['form_editor_username'];
                                $form_editor_email_address = $row['form_editor_email_address'];
                                $form_submitter_user_id = $row['user_id'];
                                
                                $item_type = 'submitted_form';
                            }
                            break;
                    }
                    
                    $sql_where = " AND (item_id = '" . escape($item_id) . "') AND (item_type = '" . escape($item_type) . "')";
                    
                    // store the sql where here, because we will need this later for the watchers area
                    $sql_where_watchers = $sql_where;
                }

                $form_submitter_email_address = '';

                // We will need the form submitter email address in various areas below,
                // so get that now if necessary.
                if (
                    ($system_region_properties['page_type'] == 'form item view')
                    and $system_region_properties['comments_watcher_email_page_id']
                ) {
                    $form_submitter_email_address = get_submitter_email_address($item_id);
                }

                $form_editor = false;

                // If the visitor is the form editor, then remember that for later.
                if (
                    ($system_region_properties['page_type'] == 'form item view')
                    && ($form_editor_username != '')
                    && (USER_LOGGED_IN)
                    && (USER_ID == $form_editor_user_id)
                ) {
                    $form_editor = true;
                }
                
                // If the visitor does not have edit access to this page,
                // and the visitor is not the form editor,
                // then prepare sql to restrict which comments should appear
                if (!$edit_access && !$form_editor) {
                    $sql_added_comments = "";
                    
                    // if session array is set then loop through comments and add them to sql statement
                    if (isset($_SESSION['software']['added_comments']) == TRUE) {
                        // loop through added comments and add each comment id to the sql statement
                        foreach ($_SESSION['software']['added_comments'] as $added_comment) {
                            if ($sql_added_comments != '') {
                                $sql_added_comments .= " OR ";
                            }
                            
                            $sql_added_comments .= "(comments.id = '" . escape($added_comment) . "')";
                        }
                        
                        // if there was an added comment, then add wrapper around added comment(s)
                        if ($sql_added_comments != '') {
                            $sql_added_comments = " OR ($sql_added_comments)";
                        }
                    }
                    
                    $sql_where .= " AND ((comments.published = '1')$sql_added_comments)";
                }

                // Get the total number of comments.  We will output this further below.
                // We do this before dealing with the featured comments because we want a total number of featured and non-featured comments.
                $query = "SELECT COUNT(*) FROM comments WHERE comments.page_id = '" . escape($system_region_properties['page_id']) . "'$sql_where";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_row($result);
                $number_of_comments = $row[0];

                // Determine if there are featured comments.  We will use this to decide if we need to filter
                // for featured comments and if we need to output different view links for "all" and "featured".
                $query =
                    "SELECT COUNT(*)
                    FROM comments
                    WHERE
                        (comments.page_id = '" . escape($system_region_properties['page_id']) . "')
                        $sql_where
                        AND comments.featured = '1'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_row($result);

                // Assume that there are not any featured comments until we find out otherwise.
                $featured_comments_exist = false;

                // If there are featured comments then remember that.
                if ($row[0] > 0) {
                    $featured_comments_exist = true;
                }

                // Assume that we are not just showing featured comments until we find out otherwise.
                $featured = false;

                // If featured comments exist and the comments view is not set in the query string
                // or it is set to "featured", then add filter to SQL where clause so query only gets
                // featured comments, and remember that we are only showing featured comments.
                if (
                    ($featured_comments_exist == true)
                    &&
                    (
                        (isset($_GET['comments']) == false)
                        || ($_GET['comments'] == 'featured')
                    )
                ) {
                    $sql_where .= " AND (comments.featured = '1')";
                    $featured = true;
                }
                
                // get comments data
                $query = 
                    "SELECT
                        comments.id,
                        comments.name,
                        comments.message,
                        files.name as file_name,
                        files.size as file_size,
                        comments.published,
                        comments.publish_date_and_time,
                        comments.publish_cancel,
                        comments.featured,
                        user.user_username as username,
                        user.user_role,
                        user.user_id,
                        user.user_badge,
                        user.user_badge_label,
                        comments.created_timestamp
                    FROM comments
                    LEFT JOIN files ON comments.file_id = files.id
                    LEFT JOIN user ON comments.created_user_id = user.user_id
                    WHERE comments.page_id = '" . escape($system_region_properties['page_id']) . "'$sql_where
                    ORDER BY comments.created_timestamp ASC";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $comments = array();
                
                // loop through all comments in order to add them to array
                while ($row = mysqli_fetch_assoc($result)) {
                    $comments[] = $row;
                }
                
                $output_comments = '';
                
                // if there is at least one comment, then prepare to output list of comments
                if (count($comments) > 0) {
                    $output_title = '';
                    $output_switch = '';

                    // If featured comments are being outputted, then prepare values for that.
                    if ($featured == true) {
						if(language_ruler()==='en'){
						    $output_title =
						        'Featured ' .
						        h(get_comment_label(array(
						            'label' => $system_region_properties['comments_label'],
						            'number' => count($comments))));
						}
						else if(language_ruler()==='tr'){
						    $output_title =
						        '&Ouml;ne &Ccedil;&#305;kan ' .
						        h(get_comment_label(array(
						            'label' => $system_region_properties['comments_label'])));
						}
                        // Get current URL parts in order to prepare URL for switch.
                        $url_parts = parse_url(get_request_uri());
                        
                        // Put query string parameters into an array in order to prepare new query string.
                        parse_str($url_parts['query'], $query_string_parameters);

                        $query_string = '';

                        // Loop through the query string parameters in order to build query string for view links.
                        foreach ($query_string_parameters as $name => $value) {
                            // If this is not the comments view query string parameter, then add it.
                            if ($name != 'comments') {
                                // If this is the first item that is being added to the query string, then add question mark.
                                if ($query_string == '') {
                                    $query_string = '?';
                                    
                                // Otherwise this is not the first item that is being added to the query string, so add ampersand.
                                } else {
                                    $query_string .= '&';
                                }
                                
                                $query_string .= urlencode($name) . '=' . urlencode($value);
                            }
                        }

                        $output_switch_url = h($url_parts['path'] . $query_string);

                        // If the query string is empty, then add ? before comments query string parameter.
                        if ($query_string == '') {
                            $output_switch_url .= '?';
                            
                        // Otherwise the query string is not empty, so add & before comments query string parameter.
                        } else {
                            $output_switch_url .= '&amp;';
                        }
						$output_switch ='';
                        $output_switch_url .= 'comments=all#software_comments';
						if(language_ruler()==='en'){
							$output_switch = '<a href="' . $output_switch_url . '" class="show_all">Show All (' . number_format($number_of_comments) . ')</a> &nbsp; &nbsp; ';
						}
						else if(language_ruler()==='tr'){
							$output_switch = '<a href="' . $output_switch_url . '" class="show_all">T&uuml;m&uuml;n&uuml; G&ouml;ster (' . number_format($number_of_comments) . ')</a> &nbsp; &nbsp; ';
						}


                    // Otherwise all comments are being outputted, so prepare values for that.
                    } else {
						if(language_ruler()==='en'){
                        $output_title =
                            number_format($number_of_comments) . ' ' .
                            h(get_comment_label(array(
                                'label' => $system_region_properties['comments_label'],
                                'number' => $number_of_comments)));
						}
						else if(language_ruler()==='tr'){
						$output_title =
                            number_format($number_of_comments) . ' ' .
                            h(get_comment_label(array(
                                'label' => $system_region_properties['comments_label'])));
						
						}

                        // If there are featured comments, then output switch for that.
                        if ($featured_comments_exist == true) {
                            // Get current URL parts in order to prepare URL for switch.
                            $url_parts = parse_url(get_request_uri());
                            
                            // Put query string parameters into an array in order to prepare new query string.
                            parse_str($url_parts['query'], $query_string_parameters);

                            $query_string = '';

                            // Loop through the query string parameters in order to build query string for view links.
                            foreach ($query_string_parameters as $name => $value) {
                                // If this is not the comments view query string parameter, then add it.
                                if ($name != 'comments') {
                                    // If this is the first item that is being added to the query string, then add question mark.
                                    if ($query_string == '') {
                                        $query_string = '?';
                                        
                                    // Otherwise this is not the first item that is being added to the query string, so add ampersand.
                                    } else {
                                        $query_string .= '&';
                                    }
                                    
                                    $query_string .= urlencode($name) . '=' . urlencode($value);
                                }
                            }

                            $output_switch_url = h($url_parts['path'] . $query_string . '#software_comments');
							if(language_ruler()==='en'){
								 $output_switch = '<a href="' . $output_switch_url . '" class="show_featured">Show Featured</a> &nbsp; &nbsp; ';
							}
							else if(language_ruler()==='tr'){
								 $output_switch = '<a href="' . $output_switch_url . '" class="show_featured">&Ouml;ne &Ccedil;&#305;kanlar&#305; G&ouml;ster</a> &nbsp; &nbsp; ';
							}
                           
                        }
                    }
                    
                    // start the list of comments with a heading with the number of comments

					$software_add_comment_link_label ='';
					if(language_ruler() === 'en') {
						$software_add_comment_link_label = 'Add ' . $output_comment_label;
					}else if(language_ruler() === 'tr') {
						$software_add_comment_link_label = $output_comment_label .' Ekle';
					}
                    $output_comments =
                        '<a name="software_comments"></a>
                        <div class="comments_heading">
                            <table width="100%" border="0">
                                <tr>
                                    <td class="title mobile_left mobile_width">' . $output_title . '</td>
                                    <td class="links mobile_left" style="text-align: right">' . $output_switch . '<a href="#software_add_comment" class="add_comment">'.$software_add_comment_link_label.'</a></td>
                                </tr>
                            </table>
                        </div>';
                    
                    $count = 0;
                    
                    // loop through all comments in order to prepare to output them
                    foreach ($comments as $comment) {
                        // increment the counter
                        $count++;

                        $output_edit_container_start = '';
                        $output_edit_container_start = '';
                        $output_published_notices = '';
                        
                        // If the visitor has edit access to this page and edit mode is on,
                        // then output grids.
                        if (($edit_access) && ($mode == 'edit')) {
                            $output_edit_container_start = '<div class="edit_mode" style="position: relative; border: 1px dashed #4780C5; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_comment.php?id=' . $comment['id'] . '&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Comment: #' . number_format($count) . '">' . $edit_label . '</a><div style="padding: 2em 0 0 0">';
                            $output_edit_container_end = '</div></div>';
                        }

                        // If the comment is not published,
                        // and the visitor has edit access to this page or is the form editor,
                        // then output published status.
                        if (
                            ($comment['published'] == '0')
                            && ($edit_access || $form_editor)
                        ) {
							if(language_ruler() === 'en') {
								$output_published_notices .= 'Not Published';
							}else if(language_ruler() === 'tr') {
								$output_published_notices .= 'Yay&#305;nlanmam&#305;&#351;';
							}
                            


                            if ($comment['publish_date_and_time'] != '0000-00-00 00:00:00') {
                                $output_relative_time = get_relative_time(array('timestamp' => strtotime($comment['publish_date_and_time'])));

                                // Change "A moment ago/from now" so the "A" is lowercase,
                                // because the relative time will appear in the middle of a phrase.
                                // We can't just lowercase the whole thing, because the
                                // relative time will sometimes contain things like "March 15".
                                $output_relative_time = str_replace('A moment', 'a moment', $output_relative_time);

                                $output_published_notices .= ' (scheduled for ' . $output_relative_time;

                                if ($comment['publish_cancel']) {
                                    $output_published_notices .= ', unless a new ' . $output_comment_label_lowercase . ' is added first';
                                }

                                $output_published_notices .= ')';
                            }
                        }

                        $output_featured_class = '';

                        // If this comment is featured, then output featured class.
                        if ($comment['featured'] == 1) {
                            $output_featured_class = ' featured';
                        }
                        
                        // If there were comments added this session,
                        // and if the visitor added this comment this session,
                        // and if the comment is not published,
                        // and if the comment is not scheduled,
                        // then output notice
                        if (
                            (isset($_SESSION['software']['added_comments']))
                            && (in_array($comment['id'], $_SESSION['software']['added_comments']) == TRUE)
                            && ($comment['published'] == '0')
                            && ($comment['publish_date_and_time'] == '0000-00-00 00:00:00')
                        ) {
                            if ($output_published_notices != '') {
                                $output_published_notices .= '<br />';
                            }

							$output_published_notices ='';
                            if(language_ruler() === 'en') {
								$output_published_notices .= 'Your ' . $output_comment_label_lowercase . ' is awaiting approval';
							}
							else if(language_ruler() === 'tr') {
								$output_published_notices .= '' . $output_comment_label_lowercase . ' onay bekliyor';
							}
                        }
                        
                        // if there is not a comment name to output, then output anonymous
                        if ($comment['name'] == '') {
							if(language_ruler() === 'en') {
								$comment['name'] = 'Anonymous';
							}else if(language_ruler() === 'tr') {
								$comment['name'] = 'Anonim';
							}
                        }
                        
                        $output_submitter_name = '';
                        
                        // if the visitor has edit access to this page and there is a username for the user that submitted this comment, then prepare to output the username next to name
                        if (($edit_access == TRUE) && ($comment['username'] != '')) {
                            $submitter_name = '';
                            
                            // if the username is the same as the display name, then get the user's full name from their contact record
                            if ($comment['username'] == $comment['name']) {
                                // get the user's contact info
                                $query =
                                    "SELECT
                                        contacts.first_name,
                                        contacts.last_name
                                    FROM user
                                    LEFT JOIN contacts ON user.user_contact = contacts.id
                                    WHERE user.user_id = '" . escape($comment['user_id']) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                                $row = mysqli_fetch_assoc($result);
                                $first_name = $row['first_name'];
                                $last_name = $row['last_name'];
                                
                                // if the first name is not blank, then add it to the submitter name
                                if ($first_name != '') {
                                    $submitter_name = $first_name;
                                }
                                
                                // if the last name is not blank, then add it to the submitter name
                                if ($last_name != '') {
                                    if ($first_name != '') {
                                        $submitter_name .= ' ';
                                    }
                                    
                                    $submitter_name .= $last_name;
                                }
                            }
                            
                            // if the submitter name is blank, then set the username as the submitter's name
                            if ($submitter_name == '') {
                                $submitter_name = $comment['username'];
                            }
                            
                            // output the submitter name
                            $output_submitter_name = ' (' . h($submitter_name) . ')';
                        }
                        
                        $output_badge = '';
                        
                        // If the badge is enabled for the comment submitter and there is a badge label, then output badge.
                        if (
                            ($comment['user_badge'] == 1)
                            &&
                            (
                                ($comment['user_badge_label'] != '')
                                || (BADGE_LABEL != '')
                            )
                        ) {
                            // If the user has a badge label, then use that.
                            if ($comment['user_badge_label'] != '') {
                                $badge_label = $comment['user_badge_label'];

                            // Otherwise, the user does not have a badge label, so use default label.
                            } else {
                                $badge_label = BADGE_LABEL;
                            }

                            $output_badge = ' <span class="software_badge ' . h(get_class_name($badge_label)) . '">' . h($badge_label) . '</span>';
                        }
                        
                        $output_submitted_date_and_time = '';
                        
                        // if the page is set so that the submitted date & time should be shown, then output it
                        if ($system_region_properties['comments_show_submitted_date_and_time'] == 1) {
                            $output_submitted_date_and_time = '<div class="date_and_time">' . get_relative_time(array('timestamp' => $comment['created_timestamp'])) . '</div>';
                        }
                        
                        $output_file_attachment = '';
                        
                        // if there is a file attachment, then output it
                        if ($comment['file_name'] != '') {
                            // we are using a separate link for the image and the file name because we don't want an underline on the image and we don't want to have to update all themes with new CSS
                            $output_file_attachment = '<div class="software_attachment" style="margin-top: 1.5em"><a href="' . OUTPUT_PATH . h(encode_url_path($comment['file_name'])) . '" target="_blank" style="background: none; padding: 0"><img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_attachment.png" width="16" height="16" alt="attachment" title="" border="0" style="padding-right: .5em; vertical-align: middle" /></a><a href="' . OUTPUT_PATH . h(encode_url_path($comment['file_name'])) . '" target="_blank">' . h($comment['file_name']) . '</a> (' . convert_bytes_to_string($comment['file_size']) . ')</div>';
                        }
                        
                        // Output comment.
                        // The c-123 in the bookmark anchor id attribute is the
                        // new standard that is shorter. We are leaving the old
                        // software_comment_123 in the name attribute for backwards
                        // compatibility reasons.  Should remove sometime in the future.
						$share_comment_label ='';
						$added_by_label ='';
						if(language_ruler()==='en'){
						$added_by_label ='Added by';
						$share_comment_label ='Share ' . $output_comment_label;}
						else if(language_ruler()==='tr'){
						$added_by_label ='Ekleyen Ad&#305;';
						$share_comment_label =$output_comment_label . ' Payla&#351;';}
                        $output_comments .=
                            $output_edit_container_start . '
                            <div class="comment row_' . ($count % 2) . $output_featured_class . '" style="position: relative">
                                <a id="c-' . $comment['id'] . '" name="software_comment_' . $comment['id'] . '"></a>
                                <div class="notice">' . $output_published_notices . '</div>
                                <div class="name_line">
                                    <span class="added_by">'.$added_by_label.'</span>
                                    <span class="name">' . h($comment['name']) . '</span>' .
                                    $output_submitter_name . $output_badge . '
                                </div>
                                ' . $output_submitted_date_and_time . '
                                <br />
                                <div class="message">' . convert_text_to_html($comment['message']) . '</div>
                                ' . $output_file_attachment . '
                                <a href="javascript:void(0)" class="share" style="position: absolute; top: 1em; right: 1em; padding: .25em" title="'.$share_comment_label.'" data-id="' . $comment['id'] . '">
                                        <img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/share.png" width="20" height="20" alt="Share" style="opacity: 0.15">
                                </a>
                            </div>
                            ' . $output_edit_container_end;
                    }
                }
                
                // assume that new comments are not allowed, until we find out otherwise
                $allow_new_comments = FALSE;
                
                // if these are comments for a specific item, then determine if new comments are allowed for the item
                if ($item_id != 0) {
                    $query = 
                        "SELECT allow_new_comments
                        FROM allow_new_comments_for_items
                        WHERE
                            (page_id = '" . escape($system_region_properties['page_id']) . "')
                            AND (item_id = '" . escape($item_id) . "')
                            AND (item_type = '" . escape($item_type) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // if a result was found, then use the result
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        
                        // if new comments are are allowed then remember that
                        if ($row['allow_new_comments'] == 1) {
                            $allow_new_comments = TRUE;
                        }
                        
                    // else a result was not found, so use value from page
                    } else {
                        // if new comments are allowed for the page, then remember that
                        if ($system_region_properties['comments_allow_new_comments'] == 1) {
                            $allow_new_comments = TRUE;
                        }
                    }
                    
                // else these are comments just for the page, not for an item, so use value from page
                } else {
                    // if new comments are allowed for the page, then remember that
                    if ($system_region_properties['comments_allow_new_comments'] == 1) {
                        $allow_new_comments = TRUE;
                    }
                }
                
                $output_allow_new_comments_form = '';
                
                // if the user has edit rights to this page, then output form that will allow the user to allow/disallow new comments
                if ($edit_access == TRUE) {
                    $liveform_allow_or_disallow_new_comments = new liveform('allow_or_disallow_new_comments', $system_region_properties['page_id']);
                    
                    // if new comments are allowed, then prepare action and prepare to output disallow button
                    if ($allow_new_comments == TRUE) {
                        $action = 'disallow';
							if(language_ruler() === 'en') {
								$output_allow_new_comments_button_label_prefix =
								'Do Not Allow New ' .
								h(get_comment_label(array(
                                'label' => $system_region_properties['comments_label'],
                                'number' => 2)));
							}else if(language_ruler() === 'tr') {
								$output_allow_new_comments_button_label_prefix =
								'Yeni ' .
								h(get_comment_label(array(
                                'label' => $system_region_properties['comments_label']))).' Kabul Etme';

							}

                       
                    // else new comments are not allowed, so prepare action and prepare to output allow button
                    } else {
                        $action = 'allow';
							if(language_ruler() === 'en') {
								$output_allow_new_comments_button_label_prefix =
								'Allow New ' .
								h(get_comment_label(array(
                                'label' => $system_region_properties['comments_label'],
                                'number' => 2)));
							}else if(language_ruler() === 'tr') {
								
								$output_allow_new_comments_button_label_prefix =
								'Yeni ' .
								h(get_comment_label(array(
                                'label' => $system_region_properties['comments_label']))).' Kabul Et';
							}
                        
                    }
                    
                    $output_allow_new_comments_form =
                        '<a name="software_allow_or_disallow_new_comments"></a>
                        <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/allow_or_disallow_new_comments.php" method="post" style="margin-top: 1em; margin-bottom: 1em">
                            ' . get_token_field() . '
                            ' . $liveform_allow_or_disallow_new_comments->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                            ' . $liveform_allow_or_disallow_new_comments->output_field(array('type'=>'hidden', 'name'=>'page_id', 'value'=>$system_region_properties['page_id'])) . '
                            ' . $liveform_allow_or_disallow_new_comments->output_field(array('type'=>'hidden', 'name'=>'item_id', 'value'=>$item_id)) . '
                            ' . $liveform_allow_or_disallow_new_comments->output_field(array('type'=>'hidden', 'name'=>'item_type', 'value'=>$item_type)) . '
                            ' . $liveform_allow_or_disallow_new_comments->output_field(array('type'=>'hidden', 'name'=>'action', 'value'=>$action)) . '
                            <input type="submit" name="submit" value="' . $output_allow_new_comments_button_label_prefix . '" class="software_input_submit_secondary new_comments_button" />
                        </form>';
                }

                // Assume that the visitor is not a watcher, until we find out otherwise.
                // We will use this in several places below.
                $visitor_is_a_watcher = false;

                // If watching is enabled and the visitor is logged in,
                // then continue to check if visitor is a watcher.
                if (
                    ($system_region_properties['comments_watcher_email_page_id'] != 0)
                    && (USER_LOGGED_IN == true)
                ) {
                    $query = 
                        "SELECT id
                        FROM watchers
                        WHERE
                            (
                                (user_id = '" . USER_ID . "')
                                || (email_address = '" . USER_EMAIL_ADDRESS . "')
                            )
                            AND (page_id = '" . escape($system_region_properties['page_id']) . "')$sql_where_watchers";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // if the user is already a watcher, then remember that
                    if (mysqli_num_rows($result) > 0) {
                        $visitor_is_a_watcher = true;
                    }
                }
                
                $output_comment_form = '';
                $output_disallow_new_comment_message = '';
                $add_comment_login_message_shown = false;
                $add_comment_heading ='';
                // if new comments are allowed, then prepare to output add comment form.
                if ($allow_new_comments == TRUE) {
                    // add the stuff at the start of the comment form
					if(language_ruler() === 'en') {
						$add_comment_heading ='Add ' . $output_comment_label;
					}else if(language_ruler() === 'tr') {
						$add_comment_heading =$output_comment_label.' Ekle';
					}

                    $output_comment_form =
                        '<a name="software_add_comment"></a>
                        ' . $liveform->output_errors() . '
                        <div class="add_comment_heading">'.$add_comment_heading.':</div>
                        <div class="add_comment_message">' . h($system_region_properties['comments_message']) . '</div>';
                    
                    // if the user is not logged in and a login is required to comment, then output the login message
                    if ((isset($_SESSION['sessionusername']) == FALSE) && ($system_region_properties['comments_require_login_to_comment'] == 1)) {
                        $link_url = '';
                        
                        // find if there is a registration entrance page
                        $query = "SELECT page_id FROM page WHERE page_type = 'registration entrance'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                        
                        // if there is a registration entrance page
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            
                            // set the link url
                            $link_url = OUTPUT_PATH . h(get_page_name($row['page_id'])) . '?send_to=' . h(urlencode(get_request_uri() . '#software_add_comment'));
                            
                        // else there is not a registration entrance page, so use default screen
                        } else {
                            $link_url = OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . h(urlencode(get_request_uri() . '#software_add_comment'));
                        }
                        
                        $output_link = '';
                        
                        // if comment watching is enabled, then output the appropriate link
                        if ($system_region_properties['comments_watcher_email_page_id'] != 0) {

							if(language_ruler() === 'en') {
								$output_link = 'Please <a style="font-weight: bold;" href="' . $link_url . '">login or register</a> to add your ' . $output_comment_label_lowercase . ' or get notified when a ' . $output_comment_label_lowercase . ' is added.';
							}else if(language_ruler() === 'tr') {
								$output_link = 'L&uuml;tfen ' . $output_comment_label_lowercase . ' eklemek veya yeni bir ' . $output_comment_label_lowercase . ' eklendi&#287;inde bildirim almak i&ccedil;in <a style="font-weight: bold;" href="' . $link_url . '">giri&#351; yap&#305;n veya kay&#305;t olun</a>.';
							}
                        // else output the standard link
                        } else {
						
								if(language_ruler() === 'en') {
									$output_link = 'Please <a style="font-weight: bold;" href="' . $link_url . '">login or register</a> to add your ' . $output_comment_label_lowercase . '.';
								}else if(language_ruler() === 'tr') {
									$output_link = 'L&uuml;tfen  ' . $output_comment_label_lowercase . ' eklemek i&ccedil;in <a style="font-weight: bold;" href="' . $link_url . '">giri&#351; yap&#305;n veya kay&#305;t olun.</a>.';
								}
                            
                        }

                        // Remember that we have already told the visitor to login to add a comment,
                        // so that we don't show redundant info below for the watcher area.
                        $add_comment_login_message_shown = true;
                        
                        // output the link
                        $output_comment_form .= '<div class="text-box-notice" style="display: inline-block; margin-bottom: 1em;">' . $output_link . '</div>';
                        
                    // else prepare and output the comment form
                    } else {
                        // assume that we don't need to set enctype for HTML form until we find out otherwise
                        $enctype = '';
                        
                        // if file attachments are allowed for comments, then prepare to set enctype for HTML form
                        if ($system_region_properties['comments_allow_file_attachments'] == 1) {
                            $enctype = ' enctype="multipart/form-data"';
                        }
                        
                        $folders_that_user_has_access_to = array();
                        
                        // If the user is a basic user then get the folders they have access to.
                        if ($user['role'] == 3) {
                            $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
                        }
                        
                        $output_comment_name_field = '';
                        $output_name_optional_label = '';
                        
                        // if the user is not logged in,
                        // or if the user is greater than a user role
                        // or if the user has edit rights to this page
                        // or if the user is viewing a submitted form and the user is the form editor for this form
                        // then output the name input field
                        if (
                            (isset($_SESSION['sessionusername']) == FALSE)
                            || (($user['role'] != '') && ($user['role'] < 3))
                            || (check_folder_access_in_array($system_region_properties['page_folder'], $folders_that_user_has_access_to) == TRUE)
                            || (($item_type == 'submitted_form') && ($user['id'] == $form_editor_user_id))
                        ) {
							if(language_ruler()==='en'){
								$output_name_optional_label = ' (optional)';
							}
							else if(language_ruler()==='tr'){
								$output_name_optional_label = ' (iste&#287;e ba&#287;l&#305;)';
							}
                            
                            
                            // if the user is logged in, and if the add comment form was not just submitted, then get contact info for user in order to prefill name field
                            if ((isset($_SESSION['sessionusername']) == TRUE) && (isset($_SESSION['software']['liveforms']['add_comment'][$system_region_properties['page_id']]) == FALSE)) {
                                $query =
                                    "SELECT
                                        contacts.first_name,
                                        contacts.last_name
                                    FROM user
                                    LEFT JOIN contacts ON user_contact = contacts.id
                                    WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                                
                                // if a contact was found, then continue
                                if (mysqli_num_rows($result) > 0) {
                                    $contact = mysqli_fetch_assoc($result);
                                    
                                    $name = '';
                                    
                                    // if there is a first name, then add first name to name
                                    if ($contact['first_name'] != '') {
                                        $name .= $contact['first_name'];
                                    }
                                    
                                    // if there is a last name, then add last name to name
                                    if ($contact['last_name'] != '') {
                                        // if there is already content in the name, then add a space
                                        if ($name != '') {
                                            $name .= ' ';
                                        }
                                        
                                        // add last name
                                        $name .= $contact['last_name'];
                                    }
                                    
                                    // prefill name field
                                    $liveform->assign_field_value('name', $name);
                                }
                            }
                            
                            // output the name input field
                            $output_comment_name_field = $liveform->output_field(array('type'=>'text', 'name'=>'name', 'maxlength'=>'50', 'size'=>'32', 'class'=>'software_input_text'));
                        
                        // else the user is logged in and does not have edit rights to the page, so output the name in a different way
                        } else {
                            // if the user is allowed to select name, then output pick list
                            if ($system_region_properties['comments_allow_user_to_select_name'] == 1) {
                                // get name for the user
                                $query =
                                    "SELECT
                                        contacts.first_name,
                                        contacts.last_name
                                    FROM user
                                    LEFT JOIN contacts ON user_contact = contacts.id
                                    WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                                $contact = mysqli_fetch_assoc($result);
                                
                                $name_options = array();
                                
                                // prepare username option
                                $label = h($user['username']);
                                $name_options[$label] = 'username';
                                
                                // if there is a first name, then prepare option for it
                                if ($contact['first_name'] != '') {
                                    $label = h($contact['first_name']);
                                    $name_options[$label] = 'first_name';
                                }
                                
                                // if there is a first name and last name, then prepare additional options
                                if (($contact['first_name'] != '') && ($contact['last_name'] != '')) {
                                    // prepare option for first name last initial
                                    $label = h($contact['first_name']) . ' ' . h(mb_substr($contact['last_name'], 0, 1)) . '.';
                                    $name_options[$label] = 'first_name_last_initial';
                                    
                                    // prepare option for first initial last name
                                    $label = h(mb_substr($contact['first_name'], 0, 1)) . '. ' . h($contact['last_name']);
                                    $name_options[$label] = 'first_initial_last_name';
                                    
                                    // prepare option for first initial last initial
                                    $label = h(mb_substr($contact['first_name'], 0, 1)) . '. ' . h(mb_substr($contact['last_name'], 0, 1)) . '.';
                                    $name_options[$label] = 'first_initial_last_initial';
                                    
                                    // prepare option for full name
                                    $label = h($contact['first_name']) . ' ' . h($contact['last_name']);
                                    $name_options[$label] = 'full_name';
                                }
                                
                                // prepare option for Anonymous
                                $name_options['Anonymous'] = 'anonymous';
                                
                                $output_comment_name_field .= $liveform->output_field(array('type'=>'select', 'name'=>'name_type', 'options'=>$name_options, 'class'=>'software_select'));
                                
                            // else the user is not allowed to select name, so just output the username
                            } else {
                                $output_comment_name_field = '<strong>' . h($user['username']) . '</strong>';
                            }
                        }
                        
                        $output_file_attachment_field = '';
                        
                        // if file attachments are allowed for comments, then output file attachment field
                        if ($system_region_properties['comments_allow_file_attachments'] == 1) {
							if(language_ruler()==='en'){
								$output_file_attachment_field = '<div style="margin-bottom: 1em"><span class="file_upload_label">Attach a File: </span>' . $liveform->output_field(array('type'=>'file', 'name'=>'file', 'class'=>'software_input_file')) . '</div>';
							}
							else if(language_ruler()==='tr'){
								$output_file_attachment_field = '<div style="margin-bottom: 1em"><span class="file_upload_label">Dosya Tuttur: </span>' . $liveform->output_field(array('type'=>'file', 'name'=>'file', 'class'=>'software_input_file')) . '</div>';
							}
						}
                        
                        $output_publish = '';
                        
                        // If the visitor has edit access to this page or is the form editor,
                        // then allow visitor to select publish option.
                        if ($edit_access || $form_editor) {
                            $publish_options = array();
							if(language_ruler()==='en'){
								$publish_options['Now'] = 'now';
								$publish_options['At a Scheduled Time'] = 'schedule';
								$publish_options['Maybe Later'] = 'manual';
							}
							else if(language_ruler()==='tr'){
								$publish_options['&#350;imdi'] = 'now';
								$publish_options['Planlanan Bir Zamanda'] = 'schedule';
								$publish_options['Belki Daha Sonra'] = 'manual';
							}
                            // If the add comment form has not been submitted yet,
                            // then set default values for fields.
                            if ($liveform->get_field_value('publish') == '') {
                                // If auto-publish is on then select now option.
                                if ($system_region_properties['comments_automatic_publish'] == '1') {
                                    $liveform->assign_field_value('publish', 'now');

                                // Otherwise auto-publish is disabled, so select manual option.
                                } else {
                                    $liveform->assign_field_value('publish', 'manual');
                                }

                                // If the date format is month and then day, then use that format.
                                if (DATE_FORMAT == 'month_day') {
                                    $month_and_day_format = 'n/j';

                                // Otherwise the date format is day and then month, so use that format.
                                } else {
                                    $month_and_day_format = 'j/n';
                                }

                                $liveform->assign_field_value('publish_date_and_time', date($month_and_day_format . '/Y g:i A', time() + 3600));

                                $liveform->assign_field_value('publish_cancel', '1');
                            }
                            $publish_label = '';
							$publish_cancel_label ='';
							if(language_ruler() === 'en') {
								$publish_label = 'Publish: ';
								$publish_cancel_label = 'Cancel if a new ' . $output_comment_label_lowercase . ' is added first.';
							}else if(language_ruler() === 'tr') {
								$publish_label = 'Yay&#305;nla: ';
								$publish_cancel_label = 'Yeni ' . $output_comment_label_lowercase . ' eklenirse iptal et.';
							}

                            $output_publish =
                                '<div style="margin-bottom: 1em">
                                    ' .$publish_label.
                                    $liveform->output_field(array(
                                        'type' => 'select',
                                        'id' => 'publish',
                                        'name' => 'publish',
                                        'options' => $publish_options,
                                        'class' => 'software_select')) . '

                                    <span class="publish_schedule" style="display: none"> ' .
                                        $liveform->output_field(array(
                                            'type' => 'text',
                                            'id' => 'publish_date_and_time',
                                            'name' => 'publish_date_and_time',
                                            'maxlength' => '19',
                                            'size' => '21',
                                            'class' => 'software_input_text')) . ' &nbsp; ' .

                                        $liveform->output_field(array(
                                            'type' => 'checkbox',
                                            'name' => 'publish_cancel',
                                            'id' => 'publish_cancel',
                                            'value' => '1',
                                            'class' => 'software_input_checkbox')) .
                                        '<label for="publish_cancel"> '.$publish_cancel_label.'</label>
                                    </span>

                                    <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
                                    ' . get_date_picker_format() . '
                                    <script>software.init_add_comment_publish()</script>
                                </div>';
                        }
                        
                        $output_watcher_check_box = '';
                        
                        // If comment watching is enabled and the visitor is logged in,
                        // and the visitor is not already a watcher,
                        // then determine if we should output the watcher check box in the add comment form.
                        if (
                            ($system_region_properties['comments_watcher_email_page_id'] != 0)
                            && (USER_LOGGED_IN == true)
                            && ($visitor_is_a_watcher == false)
                        ) {                            
                            // Assume that the user is not notified already, until we find out otherwise.
                            // We don't want to show the watcher option when adding a comment if the user is already
                            // notified by a different feature.
                            $user_is_notified_already = FALSE;

                            // If the user is a moderator or form editor,
                            // then the user is notified already, so remember that.
                            if (
                                (mb_strpos(mb_strtolower($system_region_properties['comments_administrator_email_to_email_address']), mb_strtolower(USER_EMAIL_ADDRESS)) !== FALSE)
                                || $form_editor
                            ) {
                                $user_is_notified_already = TRUE;
                            }

                            // If the user is not already notified so far and this is a form item view,
                            // then check other features where a user might be notified.
                            if (
                                ($user_is_notified_already == FALSE)
                                && ($system_region_properties['page_type'] == 'form item view')
                            ) {
                                // If e-mailing conditional administrators is enabled, then check that feature.
                                if ($system_region_properties['comments_administrator_email_conditional_administrators'] == 1) {

                                    // Get the custom form for this form item view.
                                    $custom_form_page_id = db("
                                        SELECT custom_form_page_id FROM form_item_view_pages
                                        WHERE
                                            (page_id = '" . e($system_region_properties['page_id']) . "')
                                            AND (collection = 'a')");
                                    
                                    // Check if this user's e-mail address matches the conditonal
                                    // administrator for this submitted form.  We support multiple
                                    // conditional admin email addresses, separated by comma,
                                    // (e.g. ^^example1@example.com,example2@example.com^^),
                                    // so that is why we use LIKE instead of a direct comparison.
                                    $query = 
                                        "SELECT COUNT(*)
                                        FROM form_field_options
                                        LEFT JOIN form_data ON form_field_options.form_field_id = form_data.form_field_id
                                        WHERE
                                            (form_field_options.page_id = '" . $custom_form_page_id . "')
                                            AND (form_data.form_id = '" . escape($item_id) . "')
                                            AND (form_field_options.email_address LIKE '%" . e(escape_like(USER_EMAIL_ADDRESS)) . "%')
                                            AND (form_data.data = form_field_options.value)";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                    $row = mysqli_fetch_row($result);

                                    // If this user's e-mail address matches the conditional administrator for this submitted form,
                                    // then the user is notified already, so remember that.
                                    if ($row[0] != 0) {
                                        $user_is_notified_already = TRUE;
                                    }
                                }

                                // If the user is not already notified so far and if the custom form submitter
                                // is e-mailed when a comment is published, and this user is the submitter,
                                // then remember that.
                                if (
                                    ($user_is_notified_already == FALSE)
                                    and ($system_region_properties['comments_submitter_email_page_id'] != 0)
                                    and (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($form_submitter_email_address))
                                ) {
                                    $user_is_notified_already = true;
                                }
                            }
							$output_watcher_check_box ='';
                            // If this user is not notified already, then add watcher check box to add comment form.
                            if ($user_is_notified_already == FALSE) {
                                // If the add comment form has not been submitted yet,
                                // then check watcher check box by default.
                                if (!$liveform->field_in_session('page_id')) {
                                    $liveform->assign_field_value('watcher', '1');
                                }
								
								if(language_ruler()==='en'){
									$output_watcher_check_box = '<div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'watcher', 'id'=>'watcher', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="watcher"> Notify me when a ' . $output_comment_label_lowercase . ' is added.</label></div>';
								}
								else if(language_ruler()==='tr'){
									$output_watcher_check_box = '<div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'watcher', 'id'=>'watcher', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="watcher">Yeni bir ' . $output_comment_label_lowercase . ' eklendi&#287;inde bana bildir.</label></div>';
								}
                            }
                        }
                        
                        $output_captcha_fields = '';
                        
                        // if CAPTCHA is enabled then prepare to output CAPTCHA fields
                        if (CAPTCHA == TRUE) {
                            // get captcha fields if there are any
                            $output_captcha_fields = get_captcha_fields($liveform);
                            
                            // if there are captcha fields to be displayed, then output them in a container
                            if ($output_captcha_fields != '') {
                                $output_captcha_fields = '<div style="margin-bottom: 1em">' . $output_captcha_fields . '</div>';
                            }
                        }
                        
                        // display the comment form
						$added_form_by_label ='';
						$comment_add_button_value ='';

						if(language_ruler() === 'en') {
							$added_form_by_label ='Added by';
							$comment_add_button_value ='Add ' . $output_comment_label;
						}else if(language_ruler() === 'tr') {
							$added_form_by_label ='Ekleyen Ad&#305;';
							$comment_add_button_value =$output_comment_label.' Ekle';
						}

                        $output_comment_form .= 
                            '<form' . $enctype . ' action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/add_comment.php" method="post" onsubmit="document.getElementById(\'submit_add_comment\').disabled = true; return true" class="add_comment_form">
                                ' . get_token_field() . '
                                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'page_id', 'value'=>$system_region_properties['page_id'])) . '
                                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'item_id', 'value'=>$item_id)) . '
                                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'item_type', 'value'=>$item_type)) . '
                                <div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'textarea', 'name'=>'message', 'cols'=>'46', 'rows'=>'8', 'class'=>'software_textarea')) . '</div>
                                <div style="margin-bottom: 1em">'. $added_form_by_label . $output_name_optional_label . ': ' . $output_comment_name_field . '</div>
                                ' . $output_file_attachment_field . '
                                ' . $output_publish . '
                                ' . $output_watcher_check_box . '
                                ' . $output_captcha_fields . '
                                <input type="submit" id="submit_add_comment" name="submit" value="'.$comment_add_button_value.'" class="software_input_submit_primary add_comment_button" />
                            </form>
                            <script>software.init_add_comment({comment_label: \'' . escape_javascript($comment_label_lowercase) . '\'})</script>';
                    }
                    
                // else new comments are not allowed, so if there is a disallow new comment message, then output it
                } else if ($system_region_properties['comments_disallow_new_comment_message'] != '') {
                    $output_disallow_new_comment_message = '<div>' . h($system_region_properties['comments_disallow_new_comment_message']) . '</div>';
                }

                $output_watcher_container = '';
                
                // if comment watching is enabled, then prepare to output area
                if ($system_region_properties['comments_watcher_email_page_id'] != 0) {
                    $liveform_add_or_remove_watcher = new liveform('add_or_remove_watcher', $system_region_properties['page_id']);
                    
                    // get the number of watchers
                    $query = 
                        "SELECT COUNT(id) as number_of_watchers
                        FROM watchers
                        WHERE page_id = '" . escape($system_region_properties['page_id']) . "'$sql_where_watchers";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $number_of_watchers = $row['number_of_watchers'];

                    // If this page is a form item view and there is a form editor,
                    // then check if visitor has access to view form editor in watcher list
                    // in order to know if we should increase watcher count here and
                    // later include form editor in watcher list.
                    if (
                        ($system_region_properties['page_type'] == 'form item view')
                        && ($form_editor_username != '')
                    ) {
                        // Assume that user does not have access to view form editor in watcher list
                        // until we find out otherwise.
                        $form_editor_view_access = false;

                        // If this visitor is logged in and either has edit access to this page
                        // or is the form editor, then increment watcher count and remember
                        // that this visitor has access to view form editor for use later.
                        if (
                            (USER_LOGGED_IN == true)
                            &&
                            (
                                ($edit_access == true)
                                || (USER_ID == $form_editor_user_id)
                            )
                        ) {
                            $number_of_watchers++;
                            $form_editor_view_access = true;
                        }
                    }

                    // If this page is a form item view and it is set to notify the form submitter,
                    // and there is a form submitter, then increase the number of watchers by 1.
                    if (
                        ($system_region_properties['page_type'] == 'form item view')
                        and $system_region_properties['comments_submitter_email_page_id']
                        and $form_submitter_email_address
                    ) {
                        $number_of_watchers++;
                    }
                    
                    $output_watcher_count = '';
                    
                    // if the number of watchers is 1 and the visitor is not a watcher, then prepare to output number of watchers
                    if (($number_of_watchers == 1) && ($visitor_is_a_watcher == FALSE)) {
                        $output_watcher_count = '<div class="watcher_count">1 person will be notified when a ' . $output_comment_label_lowercase . ' is added.</div>';
                        
                    // else if the number of watchers is greater than 1, then prepare to output number of watchers
                    } else if ($number_of_watchers > 1) {
                        $output_watcher_count = '<div class="watcher_count">' . number_format($number_of_watchers) . ' people will be notified when a ' . $output_comment_label_lowercase . ' is added.</div>';
                    }

                    // Assume that the visitor does not have access to manage watchers, until we find out otherwise.
                    $watcher_management_access = FALSE;

                    // If the visitor is logged in, then continue to check if user has access to manage watchers.
                    if (USER_LOGGED_IN == TRUE) {
                        // If the user has edit access to this page then user has access to manage watchers.
                        if ($edit_access == TRUE) {
                            $watcher_management_access = TRUE;

                        // Otherwise, if this page is a form item view, then check if user has access in a different way
                        } else if ($system_region_properties['page_type'] == 'form item view') {
                            // If this user is the form editor for this submitted form,
                            // then the user has access to manage watchers.
                            if (USER_ID == $form_editor_user_id) {
                                $watcher_management_access = TRUE;

                            // Otherwise this user is not the form editor, so if the submitter is allowed to manage watchers,
                            // then check if this user is the submitter.
                            } else if ($system_region_properties['comments_watchers_managed_by_submitter'] == 1) {
                                // If this user has the same user id as the user that submitted the form,
                                // or if this user's email address matches the form submitter email address,
                                // then this user is allowed to manage watchers.
                                if (
                                    (USER_ID == $form_submitter_user_id)
                                    or (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($form_submitter_email_address))
                                ) {
                                    $watcher_management_access = true;
                                }
                            }
                        }
                    }

                    // If the user has access to manage watchers, then output watcher management area.
                    if ($watcher_management_access == TRUE) {
                        $output_watchers = '';

                        // If this is a form item view and there is a form editor for this form
                        // and this visitor has access to view the form editor, then add
                        // form editor to list of watchers.  It will not be removeable.
                        if (
                            ($system_region_properties['page_type'] == 'form item view')
                            && ($form_editor_username != '')
                            && ($form_editor_view_access == true)
                        ) {
                            $output_form_editor_name = h($form_editor_username);

                            // If the e-mail address for the form editor is not the same as the username,
                            // then also output the e-mail address in parenthesis.
                            if (mb_strtolower($form_editor_email_address) != mb_strtolower($form_editor_username)) {
                                $output_form_editor_name .= ' (' . h($form_editor_email_address) . ')';
                            }

                            $output_watchers .= '<div class="form_editor" style="font-style: italic; margin-bottom: .5em; margin-left: 1em">' . $output_form_editor_name . '</div>';
                        }

                        // If a form submitter was found up above,
                        // then we want to add the form submitter to the list of watchers.
                        // It will not be removable.
                        if ($form_submitter_email_address != '') {
                            $output_watchers .= '<div style="margin-bottom: .5em; margin-left: 1em">' . h($form_submitter_email_address) . '</div>';
                        }

                        // Get watchers so we can output a list of them.
                        $query = 
                            "SELECT
                                watchers.id,
                                watchers.email_address,
                                user.user_username AS username,
                                user.user_email AS user_email_address
                            FROM watchers
                            LEFT JOIN user ON watchers.user_id = user.user_id
                            WHERE
                                (watchers.page_id = '" . escape($system_region_properties['page_id']) . "')
                                AND (watchers.item_id = '" . escape($item_id) . "')
                                AND (watchers.item_type = '" . escape($item_type) . "')
                            ORDER BY watchers.id ASC";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $watchers = mysqli_fetch_items($result);

                        // Loop through the watchers in order to output a list of them.
                        foreach ($watchers as $watcher) {
                            // If a user was found for the watcher, then output username and possibly e-mail address for user.
                            if ($watcher['username'] != '') {
                                $output_watcher_name = h($watcher['username']);

                                // If this user is a manager or above and the e-mail address for the watcher is not the same as the username,
                                // then also output the e-mail address for the watcher in parenthesis.
                                // We only want to show the e-mail address for manager and above, because otherwise
                                // a basic user like a form submitter could enter any username and see the user's e-mail address.
                                if (
                                    (USER_ROLE < 3)
                                    && (mb_strtolower($watcher['user_email_address']) != mb_strtolower($watcher['username']))
                                ) {
                                    $output_watcher_name .= ' (' . h($watcher['user_email_address']) . ')';
                                }

                            // Otherwise a user was not found for this watcher, so just output e-mail address.
                            } else {
                                $output_watcher_name = h($watcher['email_address']);
                            }

                            $output_watchers .=
                                '<div style="margin-bottom: .5em; margin-left: 1em">
                                    ' . $output_watcher_name . '
                                    <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/add_or_remove_watcher.php" method="post" style="display: inline">
                                        ' . get_token_field() . '
                                        ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'management', 'value'=>'true')) . '
                                        ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$watcher['id'])) . '
                                        ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                                        ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'page_id', 'value'=>$system_region_properties['page_id'])) . '
                                        ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'item_id', 'value'=>$item_id)) . '
                                        ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'item_type', 'value'=>$item_type)) . '
                                        ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'action', 'value'=>'remove')) . '
                                        <input type="submit" name="submit" value="x" class="software_input_submit_tiny_secondary" />
                                    </form>
                                </div>';
                        }
						$output_watcher_input ='';
						$output_watcher_input_button ='';
						if(language_ruler()==='en'){
							$output_watcher_input = $liveform_add_or_remove_watcher->output_field(array('type'=>'text', 'name'=>'username_or_email_address', 'maxlength'=>'100', 'size'=>'30', 'class'=>'software_input_text mobile_text_width', 'onblur'=> 'if ( this.value == \'\' ) { this.value = \'username or email address\'; }', 'onfocus'=> 'if ( this.value == \'username or email address\' ) { this.value = \'\'; }', 'value'=>'username or email address'));
							$output_watcher_input_button ='<input type="submit" name="submit" value="Add Watcher" class="software_input_submit_small_secondary" />';
						}
						else if(language_ruler()==='tr'){
							$output_watcher_input = $liveform_add_or_remove_watcher->output_field(array('type'=>'text', 'name'=>'username_or_email_address', 'maxlength'=>'100', 'size'=>'30', 'class'=>'software_input_text mobile_text_width', 'onblur'=> 'if ( this.value == \'\' ) { this.value = \'eposta\'; }', 'onfocus'=> 'if ( this.value == \'eposta\' ) { this.value = \'\'; }', 'value'=>'eposta'));
							$output_watcher_input_button ='<input type="submit" name="submit" value="&#304;zleyici Ekle" class="software_input_submit_small_secondary" />';
						}

                        $output_watcher_container =
                            '<div class="watcher_container">
                                <a name="software_watcher"></a>
                                ' . $liveform_add_or_remove_watcher->output_errors() . '
                                ' . $liveform_add_or_remove_watcher->output_notices() . '
                                ' . $output_watcher_count . '
                                ' . $output_watchers . '
                                <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/add_or_remove_watcher.php" method="post">
                                    ' . get_token_field() . '
                                    ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'management', 'value'=>'true')) . '
                                    ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                                    ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'page_id', 'value'=>$system_region_properties['page_id'])) . '
                                    ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'item_id', 'value'=>$item_id)) . '
                                    ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'item_type', 'value'=>$item_type)) . '
                                    ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'action', 'value'=>'add')) . 
									$output_watcher_input . 
									$output_watcher_input_button . '
                                </form>
                            </div>';

                    // Otherwise the user does not have access to manage watchers, so output standard watcher area.
                    } else {
                        $output_watcher_question = '';
                        $output_watcher_action = '';

                        // If this visitor is not logged in or if this user is not the form submitter that is already going to be notified,
                        // then show form to allow the visitor to add/remove him/herself as a watcher.
                        // We don't want to allow the form submitter that is already going to be e-mailed to add/remove him/herself
                        // from being a watcher, because it might confuse the user.
                        if (
                            (USER_LOGGED_IN == FALSE)
                            || 
                            (
                                !$system_region_properties['comments_submitter_email_page_id']
                                or (mb_strtolower(USER_EMAIL_ADDRESS) != mb_strtolower($form_submitter_email_address))
                            )
                        ) {
                            // If the visitor is logged in and he/she is not a watcher,
                            // or the visitor is not logged in and the login message
                            // was not already shown up above for the add comment area,
                            // then prepare to output question in a certain way
							$output_watcher_question ='';
                            if (
                                (
                                    (isset($_SESSION['sessionusername']) == TRUE)
                                    && ($visitor_is_a_watcher == FALSE)
                                )
                                ||
                                (
                                    (isset($_SESSION['sessionusername']) == FALSE)
                                    && (!$add_comment_login_message_shown)
                                )
                            ) {
								if(language_ruler()==='en'){
									$output_watcher_question = '<div class="watcher_question">Would you like to be notified when a ' . $output_comment_label_lowercase . ' is added?</div>';
                                }
								else if(language_ruler()==='tr'){
									$output_watcher_question = '<div class="watcher_question">Bir ' . $output_comment_label_lowercase . ' eklendi&#287;inde haberdar olmak ister misiniz?</div>';
								}
                            // else if the visitor is logged in and he/she is a watcher, then prepare to output question in a different way
                            } else if ((isset($_SESSION['sessionusername']) == TRUE) && ($visitor_is_a_watcher == TRUE)) {
								if(language_ruler()==='en'){
									$output_watcher_question = '<div class="watcher_question">Do you no longer want to be notified when a ' . $output_comment_label_lowercase . ' is added?</div>';
								}
								else if(language_ruler()==='tr'){
									$output_watcher_question = '<div class="watcher_question">Art&#305;k yeni bir ' . $output_comment_label_lowercase . ' eklendi&#287;inde haberdar olmak istemiyor musunuz?</div>';
								}
                            }
                            
                            // if the visitor is logged in, then prepare to output form that will allow the user to add or remove him/herself from the watch list
                            if (isset($_SESSION['sessionusername']) == TRUE) {
                                // if the visitor is already a watcher, then prepare action and prepare to output remove button
                                if ($visitor_is_a_watcher == TRUE) {
                                    $action = 'remove';
									if(language_ruler()==='en'){
										$output_add_or_remove_button_label = 'Remove Me';
									}
									else if(language_ruler()==='tr'){
										$output_add_or_remove_button_label = 'Beni &Ccedil;&#305;kart';
									}
                                    
                                // else the user is not a watcher, so prepare action and prepare to output add button
                                } else {
                                    $action = 'add';
									if(language_ruler()==='en'){
										$output_add_or_remove_button_label = 'Add Me';
									}
									else if(language_ruler()==='tr'){
										$output_add_or_remove_button_label = 'Beni Ekle';
									}

                                }
                                
                                $output_watcher_action =
                                    '<div class="watcher_action">
                                        <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/add_or_remove_watcher.php" method="post">
                                            ' . get_token_field() . '
                                            ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                                            ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'page_id', 'value'=>$system_region_properties['page_id'])) . '
                                            ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'item_id', 'value'=>$item_id)) . '
                                            ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'item_type', 'value'=>$item_type)) . '
                                            ' . $liveform_add_or_remove_watcher->output_field(array('type'=>'hidden', 'name'=>'action', 'value'=>$action)) . '
                                            <input type="submit" name="submit" value="' . $output_add_or_remove_button_label . '" class="software_input_submit_secondary watcher_button" /> (' . h(USER_EMAIL_ADDRESS) . ')
                                        </form>
                                    </div>';
                                
                            // Otherwise the visitor is not logged in, so if a login message has not already
                            // been shown to the visitor up above in the add comment area, then output login message.
                            } else if (!$add_comment_login_message_shown) {

								if(language_ruler()==='en'){
									$output_watcher_action = '<div class="watcher_action">Please <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri() . '#software_watcher') . '">login or register</a> first.</div>';
								}else if(language_ruler()==='tr'){
									$output_watcher_action = '<div class="watcher_action">L&uuml;tfen &ouml;nce <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri() . '#software_watcher') . '">giri&#351; yap veya kaydol</a>.</div>';
								}
							
							}
                        }
                        
                        $output_watcher_container =
                            '<div class="watcher_container">
                                <a name="software_watcher"></a>
                                ' . $liveform_add_or_remove_watcher->output_notices() . '
                                ' . $output_watcher_count . '
                                ' . $output_watcher_question . '
                                ' . $output_watcher_action . '
                            </div>';
                    }
                    
                    $liveform_add_or_remove_watcher->remove_form();
                }
                
                // output comments
                $system_output .= '
                    <div class="software_comments">
                        ' . $output_comments . '
                        <script>software.init_share_comment({comment_label: \'' . escape_javascript($comment_label) . '\'})</script>
                        ' . $output_allow_new_comments_form . '
                        ' . $output_disallow_new_comment_message . '
                        ' . $output_comment_form . '
                        ' . $output_watcher_container . '
                    </div>';
                
                $liveform->remove_form();
            }
            
            // if there is any extra system content, then output it now
            if (($extra_system_content != '') && ($primary_system_region == TRUE)) {
                $system_output .= $extra_system_content;
            }

            // if this system region is a secondary system region then add system region footer
            if ($primary_system_region == FALSE) {
                $output_system_region_footer = $system_region_properties['system_region_footer'];

                // If the mode is edit and the user has edit access to this system region's page,
                // then add edit button for images and edit button for system region footer.
                if (($mode == 'edit') && ($edit_access == true)) {
                    if ($page_editor_version == 'latest') {
                        $inline_editing_region_count++;

                        // If content is empty, then add some default content so inline editor is not collapsed.
                        if ($output_system_region_footer == '') {
                            $output_system_region_footer = '<p>&nbsp;</p>';
                        }

                        $output_system_region_footer =
                            '<div class="edit_mode" style="position: relative; border: 1px dashed black; margin: -1px;">
                                <div id="software_inline_editing_region_' . $inline_editing_region_count . '" class="software_system_region_footer" title="System Region Footer: ' . h($system_region_properties['page_name']) . '">
                                    ' . $output_system_region_footer . '
                                </div>
                                <script>software.inline_editing.init_region({container_id: "software_inline_editing_region_' . $inline_editing_region_count . '", type: "system_region_footer", id: ' . $system_region_properties['page_id'] . ', count: ' . $inline_editing_region_count . '})</script>
                            </div>';

                    } else {
                        // If content is empty, then add some default content to resolve spacing issues.
                        if ($output_system_region_footer == '') {
                            $output_system_region_footer = '<div style="padding: 5px">&nbsp;</div>';
                        }

                        $output_system_region_footer = add_edit_button_for_images('system_region_footer', $system_region_properties['page_id'], $output_system_region_footer);

                        $output_system_region_footer =
                            '<div class="edit_mode" style="position: relative; border: 1px dashed black; margin: -1px;">
                                <a href="javascript:void(0)" onclick="software_open_edit_region_dialog(\'' . $system_region_properties['page_id'] . '\', \'system_region_footer\', \'\', \'\')" style="background: black; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="System Region Footer: ' . h($system_region_properties['page_name']) . '">' . $edit_label . '</a>
                                <div class="software_system_region_footer">
                                    ' . $output_system_region_footer . '
                                </div>
                            </div>';
                    }

                // Otherwise the mode is not edit, so just wrap footer with a container.
                } else {
                    $output_system_region_footer = '<div class="software_system_region_footer">' . $output_system_region_footer . '</div>';
                }

                $system_output .= $output_system_region_footer;
            }
            
            // If mode is edit, and user has access to edit this system region's page,
            // then add container around system content with edit button and dashed lines.
            if (($mode == 'edit') && ($edit_access == true)) {
                $system_output =
                    '<div class="edit_mode" style="border: 1px dotted black; margin: .25em -1px; position: relative">
                        <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $system_region_properties['page_id'] . '&amp;send_to=' . h(urlencode(get_request_uri())) . '#interactive_page_feature" style="background: black; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="System Region: ' . h($system_region_properties['page_name']) . '">' . $edit_label . '</a>
                        <div style="padding: 2em 0 0 0">' . $system_output . '</div>
                    </div>';
            }
        }
        
        $content = preg_replace('/<system>' . $system_page_name . '<\/system>/i', addcslashes($system_output, '\\$'), $content);
    }

    // replace code with common regions
    preg_match_all('/<cregion>.*?<\/cregion>/i', $content, $regions);
    foreach ($regions[0] as $region) {
        $cregion_name = strip_tags($region);
        $query =
            "SELECT
                cregion_id,
                cregion_content,
                cregion_designer_type,
                cregion_name
            FROM cregion
            WHERE cregion_name = '" . escape($cregion_name) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $cregion_id = $row['cregion_id'];
        $cregion_content = $row['cregion_content'];
        $cregion_designer_type = $row['cregion_designer_type'];
        $cregion_name = $row['cregion_name'];

        // If this is a common region (not a designer region), then continue to replace it.
        if ($cregion_designer_type == 'no') {
            // If mode is edit and user has edit access to common region, then add edit container.
            if (
                ($mode == 'edit')
                &&
                (
                    ($user['role'] < 3)
                    || (in_array($cregion_id, get_items_user_can_edit('common_regions', $user['id'])) == true)
                )
            ) {
                if ($page_editor_version == 'latest') {
                    $inline_editing_region_count++;

                    // If content is empty, then add some default content so inline editor is not collapsed.
                    if ($cregion_content == '') {
                        $cregion_content = '<p>&nbsp;</p>';
                    }

                    $cregion_content =
                        '<div class="edit_mode" style="position: relative; border: 1px dashed #68201E; margin: -1px;">
                            <div id="software_inline_editing_region_' . $inline_editing_region_count . '" title="Common Region: ' . h($cregion_name) . '">
                                '. $cregion_content . '
                            </div>
                            <script>software.inline_editing.init_region({container_id: "software_inline_editing_region_' . $inline_editing_region_count . '", type: "common", id: ' . $cregion_id . ', count: ' . $inline_editing_region_count . '})</script>
                        </div>';

                } else {
                    // If content is empty, then add some default content to resolve spacing issues.
                    if ($cregion_content == '') {
                        $cregion_content = '<div style="padding: 5px">&nbsp;</div>';
                    }

                    $cregion_content = add_edit_button_for_images('cregion', $cregion_id, $cregion_content);

                    $cregion_content =
                        '<div class="edit_mode" style="position: relative; border: 1px dashed #68201E; margin: -1px;">
                            <a href="javascript:void(0)" onclick="software_open_edit_region_dialog(\'' . $cregion_id . '\', \'cregion\', \'' . h(escape_javascript($cregion_name)) . '\', \'\')" style="background: #68201E; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Common Region: ' . h($cregion_name) . '">' . $edit_label . '</a>
                            '. $cregion_content . '
                        </div>';
                }
            }

            $content = str_replace($region, $cregion_content, $content);
        }
    }
    
    // Insert content for page regions.

    preg_match_all('/<pregion><\/pregion>/i', $content, $regions);
    $number_of_regions = count($regions[0]);

    // If there is at least one page region, then insert content for page regions.
    if ($number_of_regions > 0) {
        // Get page regions for this page.
        $page_regions = db_items(
            "SELECT
                pregion_id AS id,
                pregion_content AS content
            FROM pregion
            WHERE
                (pregion_page = '" . escape($page_id) . "')
                AND (collection = '$collection')
            ORDER BY pregion_order ASC
            LIMIT $number_of_regions");

        // Loop through all of the regions in the content,
        // in order to replace them with the page region content.
        for ($i = 1; $i <= $number_of_regions; $i++) {
            $pregion_id = $page_regions[$i - 1]['id'];
            $pregion_content = $page_regions[$i - 1]['content'];
            
            // if mode is edit, and the user is able to edit the page then add edit button for images and edit button for region
            if ($mode == 'edit') {
                if ($page_editor_version == 'latest') {
                    $inline_editing_region_count++;

                    if ($pregion_id == '') {
                        $output_region_id = '0';
                    } else {
                        $output_region_id = $pregion_id;
                    }

                    // If content is empty, then add some default content so inline editor is not collapsed.
                    if ($pregion_content == '') {
                        $pregion_content = '<p>&nbsp;</p>';
                    }

                    $pregion_content =
                        '<div class="edit_mode" id="pregion_' . $pregion_id . '" style="position: relative; border: 1px dashed #4780C5; margin: -1px;">
                            <div id="software_inline_editing_region_' . $inline_editing_region_count . '" title="Page Region: #'. $i .'">
                                '. $pregion_content . '
                            </div>
                            <script>software.inline_editing.init_region({container_id: "software_inline_editing_region_' . $inline_editing_region_count . '", type: "page", id: ' . $output_region_id . ', order: ' . $i . ', collection: "' . $collection . '", count: ' . $inline_editing_region_count . '})</script>
                        </div>';

                } else {
                    // If content is empty, then add some default content to resolve spacing issues.
                    if ($pregion_content == '') {
                        $pregion_content = '<div style="padding: 5px">&nbsp;</div>';
                    }

                    $pregion_content = add_edit_button_for_images('pregion', $pregion_id, $pregion_content);

                    $pregion_content =
                        '<div class="edit_mode" id="pregion_' . $pregion_id . '" style="position: relative; border: 1px dashed #4780C5; margin: -1px;">
                            <a href="javascript:void(0)" onclick="software_open_edit_region_dialog(\'' . $pregion_id . '\', \'pregion\', \'\', \'' . $i . '\')" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Page Region: #'. $i .'">' . $edit_label . '</a>
                            '. $pregion_content . '
                        </div>';
                }
            }
            
            $content = preg_replace('/<pregion><\/pregion>/i', addcslashes($pregion_content, '\\$'), $content, 1);
        }
    }

    $item = array();
    $current_product_group_id = '';
    
    // if page is a catalog or a catalog detail page, and if meta data for item exists, then set item meta data for page
    if (($page_type == 'catalog') || (($page_type == 'catalog detail'))) {
        // if there is a forward slash in the page name then get the items information
        if (mb_strpos($_GET['page'], '/') !== FALSE) {
            $item = get_catalog_item_from_url();
        }
        
        // if there is an item and if the item type is product, then prepare sql to get meta data for product
        if (($item['id'] != '') && ($item['type'] == 'product')) {
            $sql_table = 'products';
        
        // else if there is an item and if the item type is a product group, then use that product group
        } elseif (($item['id'] != '') && ($item['type'] == 'product group')) {
            $current_product_group_id = $item['id'];
            
        // else if there is a default product group set, then use that product group
        } elseif ($catalog_product_group_id != 0) {
            $current_product_group_id = $catalog_product_group_id;
            
        // else no product or product group can be found, so get top-level product group
        } else {
            $query = "SELECT id FROM product_groups WHERE parent_id = '0'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $current_product_group_id = $row['id'];
        }
        
        // if there is a product group id set, then prepare sql to get meta data for product group
        if ($current_product_group_id != '') {
            $item['id'] = $current_product_group_id;
            $sql_table = 'product_groups';
        }
        
        // if there is an item id, then get meta data for item and output it
        if ($item['id'] != '') {
            // get meta data for item
            $query =
                "SELECT
                    title,
                    meta_description,
                    meta_keywords,
                    image_name
                FROM $sql_table
                WHERE id = '" . escape($item['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            // if title was found then override pages title
            if ($row['title'] != '') {
                $page_title = $row['title'];
            }
            
            // if meta description was found then override pages meta description
            if ($row['meta_description'] != '') {
                $page_meta_description = $row['meta_description'];
            }
            
            // if meta keywords was found then override pages meta keywords
            if ($row['meta_keywords'] != '') {
                $page_meta_keywords = $row['meta_keywords'];
            }
            
            // store image name for open graph code later
            $image_name = $row['image_name'];
        }
    }

    // If this page is a form item view, then get title and description from submitted form data.
    if ($page_type == 'form item view') {
        // If a submitted form id was passed to this function (e.g. for emailing a form item view as a confirmation
        // after a custom form was submitted, then use that id.
        if ($dynamic_properties['form_id']) {
            $submitted_form['id'] = $dynamic_properties['form_id'];

        // Otherwise a submitted form id was not passed, so if there is a reference code,
        // then use it to get the form id.
        } else if ($_GET['r']) {
            $submitted_form['id'] = db_value("SELECT id FROM forms WHERE reference_code = '" . escape($_GET['r']) . "'");
        }

        // If a submitted form was found, then continue to get title and description.
        if ($submitted_form['id']) {
            // Get title values for this submitted form.  The title field is the one where the RSS title
            // setting is enabled for the field.  There can be multiple title fields (e.g. first name, last name).
            $titles = db_items(
                "SELECT
                    form_data.data,
                    form_data.type
                FROM form_data
                LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                WHERE
                    (form_data.form_id = '" . escape($submitted_form['id']) . "')
                    AND (form_fields.rss_field = 'title')
                ORDER BY form_fields.sort_order ASC");

            $combined_title = '';

            // Loop through the title values, in order to prepare the combined title.
            foreach ($titles as $title) {
                // If the title contains HTML, from a rich-text editor field,
                // and the title is not blank, then convert HTML to plain-text,
                // because HTML is not supported in the title tag.  It was necessary
                // to do this because some systems, like Facebook, will show the HTML tags
                // as plain text (e.g. <bold>example</bold>).
                if (($title['type'] == 'html') && ($title['data'] != '')) {
                    $title['data'] = trim(convert_html_to_text($title['data']));
                }

                // If the title is not blank, then add it to combined title.
                if ($title['data'] != '') {
                    // If this is not the first title then add a space for separation.
                    if ($combined_title != '') {
                        $combined_title .= ' ';
                    }

                    $combined_title .= $title['data'];
                }
            }

            // If submitted form title was found then override page's title.
            if ($combined_title != '') {
                $page_title = $combined_title;
            }

            $description = db_item(
                "SELECT
                    form_data.data,
                    form_data.type
                FROM form_data
                LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                WHERE
                    (form_data.form_id = '" . escape($submitted_form['id']) . "')
                    AND (form_fields.rss_field = 'description')");

            // If the description contains HTML, from a rich-text editor field,
            // and the description is not blank, then convert HTML to plain-text,
            // because HTML is not supported in the meta description tag.  It was necessary
            // to do this because some systems, like Facebook, will show the HTML tags
            // as plain text (e.g. <bold>example</bold>).
            if (($description['type'] == 'html') && ($description['data'] != '')) {
                $description['data'] = trim(convert_html_to_text($description['data']));
            }

            // If submitted form description was found then override page's meta description.
            if ($description['data'] != '') {
                $page_meta_description = $description['data'];
            }
        }
    }
    
    // If this page has a title then use that for title tag.
    if ($page_title != '') {
        $content = preg_replace('/<title>.*?<\/title>/i', '<title>' . h($page_title) . '</title>', $content);

    // else page does not have a title set, so let's look elsewhere for a title
    } else {
        // look for a title in the style
        preg_match('/<title>(.*?)<\/title>/i', $content, $matches);

        // if title in style for page does not contain a title, add default title
        // We store the title so we can include it in the open graph tag.
        if (!$matches[1]) {
            $content = preg_replace('/<title>.*?<\/title>/i', '<title>' . h(TITLE) . '</title>', $content);
            $page_title = TITLE;
        } else {
            $page_title = unhtmlspecialchars(trim($matches[1]));
        }
    }

    // if page does not have a meta description, set meta description to account wide meta description
    if ($page_meta_description == '') {
        $page_meta_description = META_DESCRIPTION;
    }

    // if page does not have a meta keywords, set meta keywords to account wide meta keywords
    if ($page_meta_keywords == '') {
        $page_meta_keywords = META_KEYWORDS;
    }

    $open_graph = '';
    $open_graph_description = '';

    // If open graph tags are not disabled, then prepare them.

    if (!defined('OPEN_GRAPH') or OPEN_GRAPH) {

        $open_graph ='        <meta property="og:title" content="' . h($page_title) . '">' . "\n" .
            '        <meta property="og:type" content="website">' . "\n" .
            '        <meta property="og:url" content="' . h($canonical_url) . '">' . "\n";
        
        // Prepare open graph image tag.

        // if the page is a catalog or catalog detail page then get the image name in a certain way
        if (($page_type == 'catalog') || ($page_type == 'catalog detail')) {

            // if the image name is not blank then continue to prepare the open graph image tag
            if ($image_name != '') {
                $open_graph .= '         <meta property="og:image" content="' . URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($image_name)) . '">' . "\n";
            }
            
        // else if the page is an order form, then get the image name in a different way
        } else if ($page_type == 'order form') {

            // Get image name for product group.
            $image_name = db("SELECT image_name FROM product_groups WHERE id = '" . e($order_form_product_group_id) . "'");
            
            // if the image name is not blank then continue to prepare the open graph image tag
            if ($image_name != '') {
                $open_graph .= '         <meta property="og:image" content="' . URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($image_name)) . '">' . "\n";
            }
        }

        $open_graph_description = 'property="og:description" ';
    }

    $twitter_card = '';

    // If Twitter Card is not disabled, then prepare it.  We don't have to include the other Twitter
    // tags, because Twitter will use the Open Graph tags.

    if (!defined('TWITTER_CARD') or TWITTER_CARD) {
        $twitter_card = '        <meta name="twitter:card" content="summary">' . "\n";
    }


    // We combine the open graph description and meta description into one tag so we don't include
    // two tags with duplicate content.  The open graph description attribute has to appear before
    // the name attribute in the meta tag in order for the description to work with Twitter Cards.

    $meta_tags ='<meta ' . $open_graph_description . 'name="description" content="' . h($page_meta_description) . '">' . "\n" . 
    '        <meta name="keywords" content="' . h($page_meta_keywords) . '">' . "\n" .
        $open_graph .
        $twitter_card .
        get_generator_meta_tag() . "\n" .
        '        <link rel="canonical" href="' . h($canonical_url) . '">' . "\n";

    // embed meta tags in code
    $content = preg_replace('/<meta_tags><\/meta_tags>/i', $meta_tags, $content);

    $theme_id = '';
    $theme_name = '';
    
    // If the user is editing a theme css file, then do not add a stylesheet, just remove the tag.
    if ($_GET['edit_theme'] == 'true') {
        $stylesheet = '';
        
    // else if the user is previewing a theme, then prepare to include that theme
    } elseif (isset($_SESSION['software']['preview_theme_id']) == TRUE) {
        // if user has selected a theme to preview, then prepare to include that theme
        if ($_SESSION['software']['preview_theme_id'] != '') {
            // get theme name in order to output path to theme
            $query = "SELECT id, name FROM files WHERE id = '" . escape($_SESSION['software']['preview_theme_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $theme_id = $row['id'];
            $theme_name = $row['name'];
            
            $stylesheet =
                '<link rel="stylesheet" type="text/css" href="' . OUTPUT_PATH . h($theme_name) .
                    '?v=' . @filemtime(FILE_DIRECTORY_PATH . '/' . $theme_name) . '">';
        
        // else the user has selected "-None-" from the theme preview pick list, so don't include a stylesheet
        } else {
            $stylesheet = '';
        }

    // Otherwise if this page's style has an override theme set for it, then use it.
    } elseif ($style_theme_name != '') {
        $stylesheet =
            '<link rel="stylesheet" type="text/css" href="' . OUTPUT_PATH . h($style_theme_name) .
                '?v=' . @filemtime(FILE_DIRECTORY_PATH . '/' . $style_theme_name) . '">';

        $theme_id = $style_theme_id;
        $theme_name = $style_theme_name;
        
    // Otherwise output activated theme for stylesheet.
    } else {
        // do things differently based on the device type (i.e. desktop or mobile)
        switch ($device_type) {
            // if the device type is desktop then get the activated desktop theme name
            case 'desktop':
            default:
                $query = "SELECT id, name FROM files WHERE activated_desktop_theme = '1'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                break;
            
            // if the device type is mobile, then get the activated mobile theme name
            // and fall back to the activated desktop theme if necessary
            case 'mobile':
                $query = "SELECT id, name FROM files WHERE activated_mobile_theme = '1'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                // if an activated mobile theme could not be found, then get activated desktop theme name
                if (mysqli_num_rows($result) == 0) {
                    $query = "SELECT id, name FROM files WHERE activated_desktop_theme = '1'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }

                break;
        }

        // if an activated theme was found, then output it for the stylesheet
        if (mysqli_num_rows($result) != 0) {
            $row = mysqli_fetch_assoc($result);
            $theme_id = $row['id'];
            $theme_name = $row['name'];

            $stylesheet =
                '<link rel="stylesheet" type="text/css" href="' . OUTPUT_PATH . h($theme_name) .
                    '?v=' . @filemtime(FILE_DIRECTORY_PATH . '/' . $theme_name) . '">';

        // else there is not an activated desktop theme, so do not output stylesheet
        } else {
            $stylesheet = '';
        }
    }

    // If visitor is logged in and is a designer or admin,
    // then check if <stylesheet> tag exists, so later we will know
    // whether we should pass theme info to page designer or not.
    if (
        (USER_LOGGED_IN)
        && (USER_ROLE < 2)
    ) {
        if (stripos($content, '<stylesheet></stylesheet>') !== false) {
            $stylesheet_tag_exists = true;
        } else {
            $stylesheet_tag_exists = false;
        }
    }
    
    // embed link to stylesheet
    $content = preg_replace('/<stylesheet><\/stylesheet>/i', $stylesheet, $content);

    // If this page is not being prepared for an email, then add JS to bottom of page.
    if (!$email) {
        $output_js = '';

        // If visitor is logged in and is a designer or admin,
        // then output js variables for the page, style, and theme,
        // so if the page designer is open, it will know that info.
        if (
            (USER_LOGGED_IN)
            && (USER_ROLE < 2)
        ) {
            $output_js .=
                'var software_page = {id: ' . $page_id . ', name: "' . escape_javascript($page_name) . '"};
                var software_style = {id: ' . $style_id . ', name: "' . escape_javascript($style_name) . '", layout_type: "' . escape_javascript($layout_type) . '"};' . "\n";

            // If a <stylesheet> tag existed and a theme was outputted for it, then output js for it.
            if (($stylesheet_tag_exists == true) && ($theme_id)) {
                $output_js .= 'var software_theme = {id: ' . $theme_id . ', name: "' . escape_javascript($theme_name) . '"};' . "\n";
            }
        }

        // If auto dialogs are enabled, then determine if we need to show any.
        if (AUTO_DIALOGS) {
            // If the user is previewing an auto dialog, and the user has access to do that
            // (admin, designer, or manager), then just load that one auto dialog for a preview.
            if (
                $_GET['preview_auto_dialog']
                && USER_LOGGED_IN
                && (USER_ROLE < 3)
            ) {
                $auto_dialogs = db_items(
                    "SELECT
                        id,
                        url,
                        width,
                        height
                    FROM auto_dialogs
                    WHERE id = '" . e($_GET['preview_auto_dialog']) . "'");

                // If an auto dialog was found for that id,
                // then continue to init auto dialogs.
                if ($auto_dialogs) {
                    $output_js .=
                        'software.init_auto_dialogs({
                            auto_dialogs: ' . encode_json($auto_dialogs) . ',
                            preview: true
                        });';
                }

            } else {
                // Get enabled auto dialogs.
                $auto_dialogs = db_items(
                    "SELECT
                        id,
                        url,
                        width,
                        height,
                        delay,
                        frequency,
                        page
                    FROM auto_dialogs
                    WHERE enabled = '1'
                    ORDER BY id");

                // If there is at least one enabled auto dialog, then init them.
                if ($auto_dialogs) {
                    $current_timestamp = time();

                    // If this is the first page view of this visit,
                    // then record the visit start timestamp, so that we can determine the visit
                    // length in order to make sure that the auto dialogs are delayed correctly.
                    if (!$_SESSION['software']['visit_start_timestamp']) {
                        $_SESSION['software']['visit_start_timestamp'] = $current_timestamp;
                    }

                    $visit_length = $current_timestamp - $_SESSION['software']['visit_start_timestamp'];

                    $valid_auto_dialogs = array();

                    // Loop through the auto dialogs to determine which are valid for this visitor.
                    foreach ($auto_dialogs as $auto_dialog) {
                        // If this dialog has not been shown to this visitor before,
                        // or if this dialog is a recurring dialog, and enough time
                        // has passed since the last time it was shown,
                        // and the auto dialog does not have a page set,
                        // or the page that is set matches this page, then show it.
                        if (
                            (
                                (!$_COOKIE['software']['auto_dialog_' . $auto_dialog['id']])
                                ||
                                (
                                    ($auto_dialog['frequency'])
                                    && (($current_timestamp - $_COOKIE['software']['auto_dialog_' . $auto_dialog['id']]) >= ($auto_dialog['frequency'] * 3600))
                                )
                            )
                            &&
                            (
                                ($auto_dialog['page'] == '')
                                || (mb_strpos(mb_strtolower($page_name), mb_strtolower($auto_dialog['page'])) !== false) 
                            )
                        ) {
                            $valid_auto_dialogs[] = $auto_dialog;
                        }
                    }

                    $output_js .= 'software.init_auto_dialogs({
                        visit_length: ' . $visit_length . ',
                        auto_dialogs: ' . encode_json($valid_auto_dialogs) . '});';
                }
            }
        }

        // If there is JS to output, then output it.
        if ($output_js != '') {
            // Wrap JS in script tags.
            $output_js = '<script>' . $output_js . '</script>';

            // If </body> is in the HTML, place js before </body>.
            if (stripos($content, '</body>') !== false) {
                $content = str_ireplace('</body>', $output_js .'</body>', $content);
                
            // Otherwise if </html> is in the HTML, place js before </html>.
            } elseif (stripos($content, '</html>') !== false) {
                $content = str_ireplace('</html>', $output_js .'</html>', $content);
                
            // Otherwise HTML does not contain </body> or </html>, so just place js at end.
            } else {
                $content .= $output_js ;
            }
        }





        if(language_ruler() != 'en'){
            

            $output_language_js = language_pack_init();
            // If </body> is in the HTML, place js before </body>.
            if (stripos($content, '</body>') !== false) {
                $content = str_ireplace('</body>', $output_language_js .'</body>', $content);
                
            // Otherwise if </html> is in the HTML, place js before </html>.
            } elseif (stripos($content, '</html>') !== false) {
                $content = str_ireplace('</html>', $output_language_js .'</html>', $content);
                
            // Otherwise HTML does not contain </body> or </html>, so just place js at end.
            } else {
                $content .= $output_language_js ;
            }
        }
    }
  
    // If there is at least one Structured Data feed on this page, then output application/ld+json in head.

    if ($strutured_data_feeds) {
        // Add code in head
        $content = preg_replace('/<\/head>/i', $strutured_data_feeds . '</head>', $content);
    }

    // If there is at least one RSS feed on this page, then output link tags in head.
    if (count($rss_feeds) > 0) {
        sort($rss_feeds);

        $output_rss_link_tags = '';

        // Loop through RSS feeds in order to output link tags.
        foreach ($rss_feeds as $rss_feed) {
            $output_rss_link_tags .='
        <link rel="alternate" type="application/rss+xml" title="' . $rss_feed['output_title'] . '" href="' . $rss_feed['output_url'] . '" />' . "\n";
        }

        // Add link tags to head.
        $content = preg_replace('/<\/head>/i', $output_rss_link_tags . '</head>', $content);
    }
    
    // replace path placeholders
    $content = preg_replace('/{path}/i', OUTPUT_PATH, $content);

    // Replace software directory placeholders.
    $content = preg_replace('/{software_directory}/i', OUTPUT_SOFTWARE_DIRECTORY, $content);

    // If email protection is enabled and this page is not being emailed,
    // and we are not in edit mode, then protect email addresses in email links.
    // We can't protect email addresses if this page is being emailed because JavaScript does
    // not work in emails and protection is not necessary for emails anyway.
    // We don't protect in edit mode, because it is not necessary,
    // and because inline editing with CKEditor has issues with the noscript tag,
    // where it will keep adding additional noscript tags each time a region is edited.
    if (
        (
            (defined('EMAIL_PROTECTION') == FALSE)
            || (EMAIL_PROTECTION == TRUE)
        )
        && ($email == FALSE)
        && ($mode != 'edit')
    ) {
        // Get all email links.
        preg_match_all('/<\s*a\s+[^>]*href\s*=\s*["\']mailto:.*?["\'].*?>.*?<\/a>/is', $content, $email_links, PREG_SET_ORDER);

        // Loop through all email links in order to replace them with JavaScript.
        foreach ($email_links as $email_link) {
            // Replace email link with JavaScript.  We rot13 and then base64 encode the email link.
            // We include an id on the script tag so that we can delete the script tag after execution,
            // in order to avoid a bug, where a blank white page appears, if this content is located in a dynamic area
            // that is processed by jQuery (e.g. tab content).
            $content = str_replace($email_link[0], '<script id="software_email_link_script">software.output_email_link(\'' . base64_encode(str_rot13($email_link[0])) . '\')</script><noscript>You may enable JavaScript to see this email address.</noscript>', $content);
        }
    }
    return $content;
}