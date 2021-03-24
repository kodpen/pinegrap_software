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

// get page info
$query =
    "SELECT
        page.page_id,
        page.page_name,
        page.page_folder,
        page.page_home,
        page.page_search,
        page.page_search_keywords,
        page.page_style,
        page.mobile_style_id,
        page.page_type,
        page.comments,
        page.comments_automatic_publish,
        page.comments_administrator_email_to_email_address,
        page.seo_score,
        page.sitemap,
        folder.folder_archived
    FROM page
    LEFT JOIN folder ON page.page_folder = folder.folder_id
    WHERE page.page_id = '" . escape($_GET['page_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$page_id = $row['page_id'];
$page_name = $row['page_name'];
$page_folder = $row['page_folder'];
$page_home = $row['page_home'];
$page_search = $row['page_search'];
$page_search_keywords = $row['page_search_keywords'];
$page_style = $row['page_style'];
$mobile_style_id = $row['mobile_style_id'];
$page_type = $row['page_type'];
$comments = $row['comments'];
$comments_automatic_publish = $row['comments_automatic_publish'];
$comments_administrator_email_to_email_address = $row['comments_administrator_email_to_email_address'];
$seo_score = $row['seo_score'];
$sitemap = $row['sitemap'];
$folder_archived = $row['folder_archived'];

$access_control_type = get_access_control_type($page_folder);

$style_id = $_GET['style_id'];

// get style information
$query =
    "SELECT
        style_name,
        style_type
    FROM style
    WHERE style_id = '" . escape($style_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$style_name = $row['style_name'];
$style_type = $row['style_type'];

// if this pages folder is archived, then output a notice next to the page name
if ($folder_archived == '1') {
    $page_name .= ' [ARCHIVED]';
}

$output_subnav_page_properties .= '';

// if user is higher than a user role OR if user has edit rights to at least one folder
if ($user['role'] < 3 || no_acl_check($user['id'])) {
    
    // If the user is a basic user than get the folder user has access to.
    if ($user['role'] == 3) {
        $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
    }
    
    // if user has access to folder that page is in, then output body
    if (($user['role'] < 3) || (check_folder_access_in_array($page_folder, $folders_that_user_has_access_to) == true)) {
        // Output page access type.
        $output_subnav_page_properties = h(get_access_control_type_name($access_control_type));
        
        // Get and output page type
        if ($page_type != '') {
            if ($output_subnav_page_properties != '') {
                $output_subnav_page_properties .= ' | ';
            }
            
            $output_subnav_page_properties .= h(get_page_type_name($page_type));
        }

        // Get most recent short link for this page if one exists.
        $query =
            "SELECT name
            FROM short_links
            WHERE
                (destination_type = 'page')
                AND (page_id = '" . escape($_GET['page_id']) . "')
            ORDER BY last_modified_timestamp DESC
            LIMIT 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $short_link = mysqli_fetch_assoc($result);
        
        // if there is a short link, then prepare to output description in sub-navigation area
        if ($short_link['name'] != '') {
            if ($output_subnav_page_properties != '') {
                $output_subnav_page_properties .= ' | ';
            }
            
            $output_subnav_page_properties .= 'Short Link: ' . h($short_link['name']);
        }

        // Check to see if the page has page type properties
        $check_for_page_type_properties = check_for_page_type_properties($page_type);
        
        // If the page has page type properties.
        if ($check_for_page_type_properties == true) {
            // Get the page type properties.
            $page_type_properties = get_page_type_properties($page_id, $page_type);
            
            // if there is a next page,
            // and the page type is not catalog detail,
            // or ordering is enabled,
            // then prepare to output next page
            if (
                (isset($page_type_properties['next_page_id']) == true)
                &&
                (
                    ($page_type != 'catalog detail')
                    || ($page_type_properties['allow_customer_to_add_product_to_order'] == 1)
                )
            ) {
                // Get the next page name and output the next page.
                $next_page_name = get_page_name($page_type_properties['next_page_id']);
                
                if ($output_subnav_page_properties != '') {
                    $output_subnav_page_properties .= ' | ';
                }
                
                // Next page is blank then output none.
                if ($next_page_name == '') {
                    $output_subnav_page_properties .= 'Next Page: None';
                    
                // else output name
                } else {
                    $output_subnav_page_properties .= 'Next Page: <a href="' . OUTPUT_PATH . $next_page_name . '" target="_parent">' . $next_page_name . '</a>';
                }
            } else if((isset($page_type_properties['add_button_next_page_id']) && ($page_type_properties['add_button_next_page_id'] != 0))) {
                // Get the next page name and output the next page. 
                $next_page_name = get_page_name($page_type_properties['add_button_next_page_id']);
                
                if ($output_subnav_page_properties != '') {
                    $output_subnav_page_properties .= ' | ';
                }
                
                // Next page is blank then output none.
                if ($next_page_name == '') {
                    $output_subnav_page_properties .= 'Next Page: None';
                // else output name
                } else {
                    $output_subnav_page_properties .= 'Next Page: <a href="' . OUTPUT_PATH . $next_page_name . '" target="_parent">' . $next_page_name . '</a>';
                }
            }
            
            if((isset($page_type_properties['skip_button_next_page_id']) && ($page_type_properties['skip_button_next_page_id'] != 0))) {
                // Get the next page name and output the next page.
                $skip_page_name = get_page_name($page_type_properties['skip_button_next_page_id']);
                
                if ($output_subnav_page_properties != '') {
                    $output_subnav_page_properties .= ' | ';
                }
                
                $output_subnav_page_properties .= 'Skip Page: <a href="' . OUTPUT_PATH . $skip_page_name . '" target="_parent">' . $skip_page_name . '</a>';   
            }
        }
        
        // if this is a home page, then prepare to output description in sub-navigation area
        if ($page_home == 'yes') {
            $output_subnav_home = '<img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY .'/images/icon_home_white_small.png" width="16" height="14" alt="" title="Home Page Icon" class="icon_home" />';
        }

        // if search is enabled, then prepare to output description in sub-navigation area
        if ($page_search == 1) {
            if ($output_subnav_page_properties != '') {
                $output_subnav_page_properties .= ' | ';
            }
            
            $output_subnav_page_properties .= 'Searchable';
            
            // if there are search keywords, then prepare to output description in sub-navigation area
            if ($page_search_keywords != '') {
                if ($output_subnav_page_properties != '') {
                    $output_subnav_page_properties .= ' | ';
                }
                
                $output_subnav_page_properties .= 'Keyword: ' . h($page_search_keywords);
            }
        }
        
        $output_comments_properties = '';
        
        // if comments are enabled, then output information to toolbar
        if ($comments == '1') {
            if ($output_subnav_page_properties != '') {
                $output_subnav_page_properties .= ' | ';
            }
            
            // if auto publish is enabled, then output enabled message
            if ($comments_automatic_publish == '1') {
                $output_subnav_page_properties .= 'Comments automatically published';
                
            // else output disabled message
            } else {
                $output_subnav_page_properties .= 'Comments require approval';
            }
            
            // if moderator email address is not blank, then output it
            if ($comments_administrator_email_to_email_address != '') {
                if ($output_subnav_page_properties != '') {
                    $output_subnav_page_properties .= ' | ';
                }
                
                $output_subnav_page_properties .= 'Moderator: ' . h($comments_administrator_email_to_email_address);
            }
        }
        
        // if this is a form list view page, then verify that RSS is enabled and then output information to toolbar
        if ($page_type == 'form list view') {
            // get the count of rss fields from the custom form
            $query =
                "SELECT 
                    COUNT(form_fields.rss_field) as number_of_rss_fields
                FROM form_list_view_pages
                LEFT JOIN form_fields ON form_fields.page_id = form_list_view_pages.custom_form_page_id
                WHERE
                    (form_list_view_pages.page_id = '" . escape($page_id) . "')
                    AND (form_list_view_pages.collection = 'a')
                    AND (rss_field != '')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $row = mysqli_fetch_assoc($result);

            // if there are RSS fields in the custom form, then check to see if this is a public page, and then output information to toolbar
            if ($row['number_of_rss_fields'] > 0) {
                if ($output_subnav_page_properties != '') {
                    $output_subnav_page_properties .= ' | ';
                }
                
                if ($access_control_type == 'public') {
                    $output_subnav_page_properties .= 'RSS: Enabled';
                } else {
                    $output_subnav_page_properties .= 'RSS: Disabled because Page is not in a Public Folder.';
                }
            }
        }
        
        // if this is a calendar view page, and if it is public then output information to toolbar
        if ($page_type == 'calendar view') {
            if ($output_subnav_page_properties != '') {
                $output_subnav_page_properties .= ' | ';
            }
            
            if ($access_control_type == 'public') {
                $output_subnav_page_properties .= 'RSS: Enabled';
            } else {
                $output_subnav_page_properties .= 'RSS: Disabled because Page is not in a Public Folder.';
            }
        }

        // prepare separator for style
        if ($output_subnav_page_properties != '') {
            $output_subnav_page_properties .= ' | ';
        }
        
        // if the user has design access, then output style name with link to edit style
        if ($user['role'] <= 1) {
            $output_subnav_page_properties .= '<a href="edit_' . $style_type . '_style.php?id=' . h($style_id) . '&send_to=' . h(urlencode($_GET['send_to'])) . '" target="_parent">' . h($style_name) . '</a>';
            
        // else the user does not have design access, so just output style name without a link
        } else {
            $output_subnav_page_properties .= h($style_name);
        }
        
        // if sitemap is enabled, then prepare to output description in sub-navigation area
        if ($sitemap == 1) {
            if ($output_subnav_page_properties != '') {
                $output_subnav_page_properties .= ' | ';
            }
            
            // if this page is public and is not archived then output that page is included in sitemap
            if (($access_control_type == 'public') && ($folder_archived == 0)) {
                $output_subnav_page_properties .= 'sitemap.xml: Included';
                
            // else this page is not public or is achived, so explain why page is not included in sitemap
            } else {
                // if page is not public, then prepare message for that
                if ($access_control_type != 'public') {
                    $message = 'not in a Public Folder.';
                    
                // else page is archived, so prepare message for that
                } else {
                    $message = 'archived.';
                }
                
                $output_subnav_page_properties .= 'sitemap.xml: Excluded because Page is ' . $message;
            }
        }

        $output_page_designer_button = '';

        // If the user is an admin or designer, then output page designer button.
        if (USER_ROLE < 2) {
            $output_page_designer_button = '<a href="page_designer.php?url=' . h(urlencode($_GET['send_to'])) . '" class="page_designer_button closed" target="_parent" title="Open Page Designer (Ctrl+G | &#8984;+G)"></a>';
        }

        $output_mobile_button = '';

        // If mobile is enabled in the site settings, then output mobile button.
        if (MOBILE == true) {
            $output_mobile_button_class = '';
            $output_mobile_button_device_type = '';
            $output_mobile_button_title = '';

            // prepare parts of the mobile button differently based on the device type
            switch ($_SESSION['software']['device_type']) {
                case 'desktop':
                default:
                    $output_mobile_button_class = 'desktop';
                    $output_mobile_button_device_type = 'mobile';
                    $output_mobile_button_title = 'Enable Mobile Mode';

                    break;
                
                case 'mobile':
                    $output_mobile_button_class = 'mobile';
                    $output_mobile_button_device_type = 'desktop';
                    $output_mobile_button_title = 'Enable Desktop Mode';

                    break;
            }

            $output_mobile_button = '<a id="mobile_button" class="' . $output_mobile_button_class . '" href="update_device_type.php?device_type=' . $output_mobile_button_device_type . '&amp;send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '" target="_parent" title="' . $output_mobile_button_title . '">&nbsp;</a>';
        }
        
        $output_edit_page_style_button = '';
        
        // if the user has design access, then output style name with link to edit style
        if ($user['role'] <= 1) {
            // If the user is previewing themes/styles, then use short label
            // for edit page style button in order to save space.
            if (isset($_SESSION['software']['preview_theme_id']) == true) {
                $output_edit_page_style_button_label = 'Edit';
                $output_title = ' title="Edit Page Style"';

            // Otherwise use long label.
            } else {
                $output_edit_page_style_button_label = 'Edit Page Style';
                $output_title = '';
            }

            $output_edit_page_style_button .= '<a class="page_edit_style_button" href="edit_' . $style_type . '_style.php?id=' . h($style_id) . '&send_to=' . h(urlencode($_GET['send_to'])) . '" target="_parent"' . $output_title . '>' . $output_edit_page_style_button_label . '</a>';
        }
        
        $output_duplicate_page_button = '';
        
        // if the user is at least a manager or has create pages turned on, then output the duplicate page button
        if (($user['role'] < '3') || ($user['create_pages'] == TRUE)) {
            $output_duplicate_page_button = '<a class="page_duplicate_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/duplicate_page.php?id=' . h($_GET['page_id']) . get_token_query_string_field() . '" target="_parent" title="Duplicate Page">Duplicate</a>';
        }
        
        $output_theme_pick_list = '';
        $output_preview_theme_link = '';
        
        // If user is manager or above
        if ($user['role'] < 3) {
            // if the user is previewing a theme and user role is administrator or designer, then prepare to include theme preview pick list
            if (isset($_SESSION['software']['preview_theme_id']) == TRUE) {
                $activated_theme_id = db_value("SELECT id FROM files WHERE activated_" . $_SESSION['software']['device_type'] . "_theme = '1'");

                if ($_SESSION['software']['device_type'] == 'desktop') {
                    $activated_style_id_for_device_type = $page_style;

                } else {
                    $activated_style_id_for_device_type = $mobile_style_id;
                }

                $output_preview_style_options = '';

                // If the user is previewing the activated theme or the "none" theme,
                // then prepare style options in a certain way.
                if (
                    ($_SESSION['software']['preview_theme_id'] == $activated_theme_id)
                    || ($_SESSION['software']['preview_theme_id'] == '')
                ) {
                    $output_default_label = '';

                    // If the activated style for this page is default, then show that in label.
                    if (!$activated_style_id_for_device_type) {
                        $output_default_label .= '[A] ';
                    }

                    $output_default_label .= 'Default';

                    // Get default style id.
                    $default_style_id = get_style($page_folder, $_SESSION['software']['device_type']);

                    // If the device type is set to mobile and a default mobile style id could not be found, then get desktop style id
                    // because we fallback to a desktop style when a mobile style cannot be found for a page
                    if (
                        ($_SESSION['software']['device_type'] == 'mobile')
                        && ($default_style_id == 0)
                    ) {
                        $default_style_id = get_style($page_folder, 'desktop');
                    }

                    // If a default style was found, then output default label with style name.
                    if ($default_style_id) {
                        $default_style_name = db_value("SELECT style_name FROM style WHERE style_id = '" . $default_style_id . "'");

                        if ($default_style_name != '') {
                            $output_default_label .= ': ' . h($default_style_name);
                        }
                    }

                    $output_preview_style_options .= '<option value="">' . $output_default_label . '</option>';
                    
                    $sql_where = "";

                    // If device type is desktop then only get desktop styles.
                    if ($_SESSION['software']['device_type'] == 'desktop') {
                        $sql_where = "WHERE style_layout != 'one_column_mobile'";

                    // Otherwise the device type if mobile, so if mobile is enabled in the site settings,
                    // then only get styles that can be mobile styles.
                    } else if (MOBILE == true) {
                        $sql_where = "WHERE (style_type = 'custom') OR (style_layout = 'one_column_mobile')";
                    }

                    $styles = db_items(
                        "SELECT
                            style_id AS id,
                            style_name AS name
                        FROM style
                        $sql_where
                        ORDER BY style_name ASC");

                    foreach ($styles as $style) {
                        $output_selected = '';

                        // If the user has recently selected a style, and this is that style,
                        // then select it by default, or if the user has not recently selected
                        // a style, and this is the activated style, then select it by default.
                        if (
                            (
                                (isset($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $_SESSION['software']['device_type']]) == true)
                                && ($style['id'] == $_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $_SESSION['software']['device_type']])
                            )
                            ||
                            (
                                (isset($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $_SESSION['software']['device_type']]) == false)
                                && ($style['id'] == $activated_style_id_for_device_type)
                            )
                        ) {
                            $output_selected = ' selected="selected"';
                        }

                        $output_label = '';

                        // If this style is the activated style, then output [ACTIVATED] prefix.
                        if ($style['id'] == $activated_style_id_for_device_type) {
                            $output_label .= '[A] ';
                        }

                        $output_label .= h($style['name']);

                        $output_preview_style_options .= '<option value="' . $style['id'] . '"' . $output_selected . '>' . $output_label . '</option>';
                    }


                // Otherwise the user is not previewing the activated theme,
                // so prepare style options in a different way.
                } else {
                    $output_activated_label = '[A] ';

                    // If this page has an activated style set, then output style name.
                    if ($activated_style_id_for_device_type) {
                        $activated_style_name = db_value("SELECT style_name FROM style WHERE style_id = '" . $activated_style_id_for_device_type . "'");

                        $output_activated_label .= h($activated_style_name);

                    // Otherwise this page's activated style is set to default, so output default,
                    // along with default style name.
                    } else {
                        $output_activated_label .= 'Default';

                        // Get default style id.
                        $default_style_id = get_style($page_folder, $_SESSION['software']['device_type']);

                        // If the device type is set to mobile and a default mobile style id could not be found, then get desktop style id
                        // because we fallback to a desktop style when a mobile style cannot be found for a page
                        if (
                            ($_SESSION['software']['device_type'] == 'mobile')
                            && ($default_style_id == 0)
                        ) {
                            $default_style_id = get_style($page_folder, 'desktop');
                        }

                        // If a default style was found, then output default label with style name.
                        if ($default_style_id) {
                            $default_style_name = db_value("SELECT style_name FROM style WHERE style_id = '" . $default_style_id . "'");

                            if ($default_style_name != '') {
                                $output_activated_label .= ': ' . h($default_style_name);
                            }
                        }
                    }

                    $output_preview_style_options .= '<option value="">' . $output_activated_label . '</option>';

                    $preview_style_id = '';

                    // If a theme is selected, then get the style that is currently
                    // set as the preview style for this page, theme, and device type,
                    // so that we can indicate that below with a [PREVIEW] prefix for
                    // the option in the pick list.
                    if ($_SESSION['software']['preview_theme_id']) {
                        $preview_style_id = db_value(
                            "SELECT style_id
                            FROM preview_styles
                            WHERE
                                (page_id = '" . escape($page_id) . "')
                                AND (theme_id = '" . escape($_SESSION['software']['preview_theme_id']) . "')
                                AND (device_type = '" . escape($_SESSION['software']['device_type']) . "')");
                    }

                    $sql_where = "";

                    // If device type is desktop then only get desktop styles.
                    if ($_SESSION['software']['device_type'] == 'desktop') {
                        $sql_where = "WHERE style_layout != 'one_column_mobile'";

                    // Otherwise the device type if mobile, so if mobile is enabled in the site settings,
                    // then only get styles that can be mobile styles.
                    } else if (MOBILE == true) {
                        $sql_where = "WHERE (style_type = 'custom') OR (style_layout = 'one_column_mobile')";
                    }

                    $styles = db_items(
                        "SELECT
                            style_id AS id,
                            style_name AS name
                        FROM style
                        $sql_where
                        ORDER BY style_name ASC");

                    foreach ($styles as $style) {
                        $output_selected = '';

                        // If the user has recently selected a style, and this is that style,
                        // then select it by default, or if the user has not recently selected
                        // a style, and this is the preview style, then select it by default.
                        if (
                            (
                                (isset($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $_SESSION['software']['device_type']]) == true)
                                && ($style['id'] == $_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $_SESSION['software']['device_type']])
                            )
                            ||
                            (
                                (isset($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $_SESSION['software']['device_type']]) == false)
                                && ($style['id'] == $preview_style_id)
                            )
                        ) {
                            $output_selected = ' selected="selected"';
                        }

                        $output_label = '';

                        // If this style is the preview style, then output [PREVIEW] prefix.
                        if ($style['id'] == $preview_style_id) {
                            $output_label .= '[P] ';
                        }

                        $output_label .= h($style['name']);

                        $output_preview_style_options .= '<option value="' . $style['id'] . '"' . $output_selected . '>' . $output_label . '</option>';
                    }
                }

                $output_preview_style_pick_list =
                    '<select name="style_id" id="style_id" onchange="parent.location.href = \'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/preview_style.php?mode=preview&amp;style_id=\' + document.getElementById(\'style_id\').value + \'&amp;page_id=' . $page_id . '&amp;send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '\';">
                        ' . $output_preview_style_options . '
                    </select>';

                // If the user is previewing the activated theme or the "none" theme,
                // then prepare specific title for set button.
                if (
                    ($_SESSION['software']['preview_theme_id'] == $activated_theme_id)
                    || ($_SESSION['software']['preview_theme_id'] == '')
                ) {
                    $output_preview_style_set_button_title = 'Set Activated Page Style';

                // Otherwise prepare different title for set button.
                } else {
                    $output_preview_style_set_button_title = 'Set Preview Page Style';
                }

                $output_preview_style_set_button = '<a class="page_preview_style_set_button" href="preview_style.php?mode=set&amp;page_id=' . $page_id . '&amp;send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '" target="_parent" title="' . $output_preview_style_set_button_title . '">Set</a>';

                $is_system_theme = FALSE;
                $selected_file_id = 0;
                
                // get all themes for preview theme pick list
                $query =
                    "SELECT
                        id,
                        name,
                        activated_" . $_SESSION['software']['device_type'] . "_theme
                    FROM files
                    WHERE
                        (type = 'css')
                        AND (design = '1')
                        AND (theme = '1')
                    ORDER BY name ASC";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $themes = mysqli_fetch_items($result);

                $output_theme_preview_options = '';

                // loop through the themes in order to prepare pick list options
                foreach ($themes as $theme) {
                    $selected_or_not = '';
                    
                    // if this theme is the theme currently being previewed, then select it
                    if ($theme['id'] == $_SESSION['software']['preview_theme_id']) {
                        $selected_or_not = ' selected="selected"';
                        
                        // check to see if this is a system theme
                        $query = "SELECT COUNT(id) FROM system_theme_css_rules WHERE file_id = '" . escape($theme['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);
                        
                        // if this is a system theme then output the edit theme button
                        if ($row[0] > 0) {
                            $is_system_theme = TRUE;
                        }
                        
                        // set the selected file id
                        $selected_file_id = $theme['id'];
                    }

                    $output_activated_prefix = '';

                    // If this is the activated theme for current device type,
                    // then add [ACTIVATED] prefix.
                    if ($theme['activated_' . $_SESSION['software']['device_type'] . '_theme'] == 1) {
                        $output_activated_prefix = '[A] ';
                    }
                    
                    $output_theme_preview_options .= '<option value="' . h($theme['id']) . '"' . $selected_or_not . '>' . $output_activated_prefix . h($theme['name']) . '</option>';
                }
                
                $output_edit_theme_link = '';
                
                // If a real theme is selected (not the "none" theme) and the user is at least a designer,
                // then output the edit theme link.
                if (
                    ($_SESSION['software']['preview_theme_id'])
                    && ($user['role'] < 2)
                ) {
                    // if the selected theme is a system theme, then output the edit theme button
                    if ($is_system_theme == TRUE) {
                        $output_edit_theme_link = '<a class="page_preview_style_edit_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/theme_designer.php?id=' . h($selected_file_id) . '&send_to=' . h(urlencode($_GET['send_to'])) . '&clear_theme_designer_session=true&page_to_preview_id=' . $page_id . '" target="_parent" title="Edit Theme">Edit</a>';
                        
                    // else output the edit css link
                    } else {
                        $output_edit_theme_link = '<a class="page_preview_style_edit_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_theme_css.php?id=' . h($selected_file_id) . '&send_to=' . h(urlencode($_GET['send_to'])) . '" target="_parent" title="Edit Theme">Edit</a>';
                    }
                }
                
                $output_theme_pick_list =
                    '<span id="theme_pick_list">
                        <select name="theme_id" id="theme_id" onchange="parent.location.href = \'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/preview_theme.php?mode=preview&amp;id=\' + document.getElementById(\'theme_id\').value + \'&amp;send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '\';">
                            <option value="">-None-</option>' 
                            . $output_theme_preview_options . '
                        </select>
                    </span>' .
                    $output_edit_theme_link .
                    '<a class="page_preview_style_cancel_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/preview_theme.php?mode=cancel&amp;send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '" target="_parent" title="Close Preview">x</a>';
                
            // else the user is not previewing a stylesheet, so output preview themes link
            } else {
                $output_preview_theme_link = '<a class="preview_theme_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/preview_theme.php?mode=preview&amp;send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '" target="_parent" title="Preview Page Styles &amp; Themes">Preview Themes</a>';
            }
        }

        $output_button_bar =
            '<div id="button_bar">
                <div class="left">' .
                    $output_mobile_button .
                    $output_page_designer_button .
                    $output_preview_style_pick_list .
                    $output_edit_page_style_button .
                    $output_preview_style_set_button .
                '</div>
                <div class="right">' .
                    $output_preview_theme_link .
                    $output_theme_pick_list .
                '</div>
                <div class="center">' . 
                    '<a class="page_edit_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_page.php?id=' . h($_GET['page_id']) . '&amp;send_to=' . h(urlencode($_GET['send_to'])) . '" target="_parent" title="Edit Page Properties">Edit Page Properties</a>' .
                    $output_duplicate_page_button .
                '</div>
                <div style="clear: both; display: block"></div>
            </div>';
    }
}

echo
    output_header($toolbar = true) . '
    <style>body.pages.toolbar{
        background: black;
        overflow: auto;
        max-height: 90vw;
        padding: 0px;
        margin: 0;

    }</style>
            <div class="toolbar_content_box">
                <div id="subnav">
                    <a href="#" class="white_help_button" style="float: right; text-decoration: none">Help</a>
                    ' . $output_subnav_home . ' <strong>' . h($page_name) . '</strong> 
                    <div class="subheading" style="display: inline-block; margin-left: 1.5em;">' . $output_subnav_page_properties . '</div>
                </div>
                ' . $output_button_bar . '
            </div>
            <div style="height:3px;"></div>
               
            <script>
                var loaded = false;

                $(window).on("load", function() {
                    loaded = true;
                });
            </script>
        </body>
    </html>';
?>