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
require_once(dirname(__FILE__) . '/generate_system_theme_css.php');

$user = validate_user();
validate_area_access($user, 'designer');

$liveform = new liveform('theme_designer');

if ($_GET['clear_theme_designer_session'] == TRUE) {
    unset($_SESSION['software']['theme_designer'][$_REQUEST['id']]);
}

if (!$_POST) {
    // get theme name from database
    $query =
        "SELECT
            name,
            activated_desktop_theme
        FROM files
        WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_array($result);
    $file_name = $row['name'];
    $activated_desktop_theme = $row['activated_desktop_theme'];
    
    // if this is the activated theme, then add the active label to the file name
    if ($activated_desktop_theme == 1) {
        $file_name .=  ' [ACTIVE]';
    }
    
    // if there is a page id in the URL, then set the page to preview id for this theme in the session to the URL's value
    if ($_GET['page_to_preview_id'] != '') {
        $_SESSION['software']['theme_designer'][$_GET['id']]['page_to_preview_id'] = $_GET['page_to_preview_id'];
    }
    
    // if there is a page to preview id in the session, then get the pages data
    if ($_SESSION['software']['theme_designer'][$_GET['id']]['page_to_preview_id'] != '') {
        $where = "WHERE page_id = '" . escape($_SESSION['software']['theme_designer'][$_GET['id']]['page_to_preview_id']) . "'";
        $limit = '';
    
    // else get the homepage, and set it to the page to preview
    } else {
        $where = "WHERE page_home = 'yes'";
        $limit = ' LIMIT 1';
    }
    
    // get page info
    $query = 
        "SELECT
            page_id,
            page_name,
            page_folder,
            page_style,
            mobile_style_id,
            page_type
        FROM page 
        $where$limit";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_array($result);
    $page_to_preview_id = $row['page_id'];
    $page_to_preview_name = $row['page_name'];
    $page_folder = $row['page_folder'];
    $page_style = $row['page_style'];
    $mobile_style_id = $row['mobile_style_id'];
    $page_type = $row['page_type'];

    $style_id = '';

    // If theme/style preview is enabled, and the theme being previewed is the same theme
    // that is being edited in the theme designer now, then use preview style.
    if (
        (isset($_SESSION['software']['preview_theme_id']) == true)
        && ($_SESSION['software']['preview_theme_id'] == $_GET['id'])
    ) {
        $device_type = $_SESSION['software']['device_type'];
        $page_id = $page_to_preview_id;

        // If the selected theme is the activated theme, then get style in a certain way.
        if (
            ($_SESSION['software']['preview_theme_id'])
            && ($_SESSION['software']['preview_theme_id'] == db_value("SELECT id FROM files WHERE activated_" . $device_type . "_theme = '1'"))
        ) {
            // If a style has been selected, then use that style.
            if (isset($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $device_type]) == true) {
                // If the selected style is blank, then that means,
                // the default option has been selected, so get style from folders.
                if (!$_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $device_type]) {
                    $style_id = get_style($page_folder, $device_type);

                    // If the device type is set to mobile and a default mobile style id
                    // could not be found, then get desktop style id because we fallback
                    // to a desktop style when a mobile style cannot be found for a page
                    if (
                        ($device_type == 'mobile')
                        && ($style_id == 0)
                    ) {
                        $style_id = get_style($page_folder, 'desktop');
                    }

                // Otherwise the selected style is not blank, so use that style.
                } else {
                    $style_id = $_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $device_type];
                }
            }

        // Otherwise the selected theme is not the activated theme,
        // so get style in a different way.
        } else {
            // If a style has not been selected yet, then check for preview style in database.
            if (isset($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $device_type]) == false) {
                $style_id = db_value(
                    "SELECT style_id
                    FROM preview_styles
                    WHERE
                        (page_id = '" . escape($page_id) . "')
                        AND (theme_id = '" . escape($_SESSION['software']['preview_theme_id']) . "')
                        AND (device_type = '" . escape($device_type) . "')");

            // Otherwise, use style that has been selected.
            } else {
                $style_id = $_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $page_id . '_' . $device_type];
            }
        }
    }

    // If a style has not been found yet during preview theme/style checks above, then just get activated style.
    if (!$style_id) {
        // get the style id differently based on the device type
        switch ($_SESSION['software']['device_type']) {
            case 'desktop':
            default:
                $style_id = $page_style;

                // if the page does not have a desktop style, then get desktop style from parent folders
                if ($style_id == 0) {
                    $style_id = get_style($page_folder, 'desktop');
                }

                break;
            
            case 'mobile':
                $style_id = $mobile_style_id;

                // if the page does not have a mobile style, then get mobile style from parent folders
                if ($style_id == 0) {
                    $style_id = get_style($page_folder, 'mobile');

                    // if a mobile style could not be found, then get desktop style
                    if ($style_id == 0) {
                        $style_id = $page_style;

                        // if the page does not have a desktop style, then get desktop style from parent folders
                        if ($style_id == 0) {
                            $style_id = get_style($page_folder, 'desktop');
                        }
                    }
                }

                break;
        }
    }
    
    // if the page style is not a system page style, then get a random page that is using a system style to preview
    if (is_system_page_style($style_id) == FALSE) {
        $system_page_styles = array();
        
        // get system page styles
        $query = "SELECT style_id FROM style WHERE style_type = 'system'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while ($row = mysqli_fetch_assoc($result)) {
            $system_page_styles[] = $row['style_id'];
        }
        
        $pages = array();
        
        // get pages
        $query =
            "SELECT
                page.page_id,
                page.page_name,
                page.page_folder,
                page.page_style,
                page.mobile_style_id
            FROM page
            LEFT JOIN folder ON page.page_folder = folder.folder_id
            WHERE folder.folder_archived = '0'
            ORDER BY page.page_name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through all pages so that we can find a page that is using a system page style
        while ($row = mysqli_fetch_assoc($result)) {
            // get the style id differently based on the device type
            switch ($_SESSION['software']['device_type']) {
                case 'desktop':
                default:
                    $style_id = $row['page_style'];

                    // if the page does not have a desktop style, then get desktop style from parent folders
                    if ($style_id == 0) {
                        $style_id = get_style($row['page_folder'], 'desktop');
                    }

                    break;
                
                case 'mobile':
                    $style_id = $row['mobile_style_id'];

                    // if the page does not have a mobile style, then get mobile style from parent folders
                    if ($style_id == 0) {
                        $style_id = get_style($row['page_folder'], 'mobile');

                        // if a mobile style could not be found, then get desktop style
                        if ($style_id == 0) {
                            $style_id = $row['page_style'];

                            // if the page does not have a desktop style, then get desktop style from parent folders
                            if ($style_id == 0) {
                                $style_id = get_style($row['page_folder'], 'desktop');
                            }
                        }
                    }

                    break;
            }
            
            // if this page style is a system page style, then update the page variables so that this is now the page to preview
            if (in_array($style_id, $system_page_styles) == TRUE) {
                $page_to_preview_id = $row['page_id'];
                $page_to_preview_name = $row['page_name'];
                $page_folder = $row['page_folder'];
                $page_style = $row['page_style'];
                break;
            }
        }
    }
    
    // set the page to preview session to the current page to preview page id
    $_SESSION['software']['theme_designer'][$_GET['id']]['page_to_preview_id'] = $page_to_preview_id;
    
    // get page style properties
    $query = "SELECT style_name, style_layout FROM style WHERE style_id = '$style_id'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);
    $page_style_name = $row['style_name'];
    $page_style_layout = $row['style_layout'];
    
    $objects = array();
    
    // get all of the dynamic objects for this style so that we can build modules for them (and get it the right order)
    $query =
        "SELECT
            area,
            `row`, # Backticks for reserved word.
            col,
            region_type,
            region_name
        FROM system_style_cells
        WHERE style_id = '$style_id'
        ORDER BY
            area,
            `row`, # Backticks for reserved word.
            col";
        
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    while($row = mysqli_fetch_assoc($result)) {
        $objects[] = $row;
    }
    
    $output_site_top_modules = '';
    $output_site_header_modules = '';
    $output_area_header_modules = '';
    $output_area_footer_modules = '';
    $output_sidebar_modules = '';
    $output_page_header_modules = '';
    $output_page_content_modules = '';
    $output_page_content_left_modules = '';
    $output_page_content_right_modules = '';
    $output_page_footer_modules = '';
    $output_site_footer_modules = '';
    
    $page_region_count = 0;
    
    // loop through each object so that we can prepare it for output
    foreach ($objects as $object) {
        $output_region_label = '';

        // If this is a page region, then output region label in a certain way.
        if ($object['region_type'] == 'page') {
            $page_region_count++;

            $output_region_label = 'Page Region';

            // If the layout is not three column sidebar left, then output page region number.
            // We choose not to output the number for that layout because the number might be incorrect if we did,
            // because of the issue where three column sidebar left layout outputs the sidebar first.
            // We would have to restructure this code in order to support that.
            if ($page_style_layout != 'three_column_sidebar_left') {
                $output_region_label .= ' #' . $page_region_count;
            }

        // Otherwise this is not a page region, so output region label in a different way
        } else {
            // Start region label with the type.
            $output_region_label = h(ucwords(str_replace('_', ' ', $object['region_type'])));

            // If this is a type of region that supports a name then determine if we should add name to label.
            if (
                ($object['region_type'] == 'ad')
                || ($object['region_type'] == 'common')
                || ($object['region_type'] == 'designer')
                || ($object['region_type'] == 'dynamic')
                || ($object['region_type'] == 'login')
                || ($object['region_type'] == 'menu')
                || ($object['region_type'] == 'menu_sequence')
                || ($object['region_type'] == 'tag_cloud')
                || ($object['region_type'] == 'system')
            ) {
                // If there is a name, then add name.
                if ($object['region_name'] != '') {
                    $output_region_label .= ': ' . h($object['region_name']);

                // Otherwise if this is a system region, then output "Use Page" for name.
                } else if ($object['region_type'] == 'system') {
                    $output_region_label .= ': Use Page';
                }
            }
        }

        // If the region is an ad region, get the ad region's display type "dynamic" or "static"
        if ($object['region_type'] == 'ad') {
            $find_name = $object['region_name'];
            $query = "SELECT display_type FROM ad_regions WHERE name = '$find_name'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $row = mysqli_fetch_assoc($result);
            $output_ad_region_type = $row['display_type'];

            // If the display type is "static" treat this ad region's styling options like any other common region
            // since we don't have code for static ad regions.
            if ($output_ad_region_type == "static") {
                $object['region_type'] = '';
            }
        }

        // NOTE: the theme designer has a known limitation and cannot handle ad/menu region names ($object['region_name']) with an underscore in them!

        // add fold location information
        $output_region_label .= '<span class="theme_fold_css">' . h('.r' . $object['row'] . 'c' . $object['col']) . '</span>';
      
        // output html for object based on the object type
        switch($object['region_type']) {
            case 'ad':
                ${'output_' . $object['area'] . '_modules'} .= 
                    '<div id="ad_region_' . $object['region_name'] . '" class="module">
                        <a class="anchor" name="ad_region_' . $object['region_name'] . '"></a>
                        <div class="header" onmouseover="highlight_module(\'.ad_' . $object['region_name'] . '\')" onmouseout="unhighlight_module(\'.ad_' . $object['region_name'] . '\')">' . $output_region_label . '<span class="heading_arrow_image closed">&nbsp;</span></div>
                        <div class="content" style="display: none">
                            <div id="ad_region_' . $object['region_name'] . '_ad_region_layout" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_ad_region_layout"></a>
                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_ad_region_background_borders_and_spacing" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_background_borders_and_spacing"></a>
                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_text" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_text"></a>
                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_headings" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_headings"></a>
                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="ad_region_' . $object['region_name'] . '_headings_general" class="module">
                                        <a class="anchor" name="ad_region_' . $object['region_name'] . '_headings_general"></a>
                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="ad_region_' . $object['region_name'] . '_heading_1" class="module">
                                        <a class="anchor" name="ad_region_' . $object['region_name'] . '_heading_1"></a>
                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="ad_region_' . $object['region_name'] . '_heading_2" class="module">
                                        <a class="anchor" name="ad_region_' . $object['region_name'] . '_heading_2"></a>
                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="ad_region_' . $object['region_name'] . '_heading_3" class="module">
                                        <a class="anchor" name="ad_region_' . $object['region_name'] . '_heading_3"></a>
                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="ad_region_' . $object['region_name'] . '_heading_4" class="module">
                                        <a class="anchor" name="ad_region_' . $object['region_name'] . '_heading_4"></a>
                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="ad_region_' . $object['region_name'] . '_heading_5" class="module">
                                        <a class="anchor" name="ad_region_' . $object['region_name'] . '_heading_5"></a>
                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="ad_region_' . $object['region_name'] . '_heading_6" class="module">
                                        <a class="anchor" name="ad_region_' . $object['region_name'] . '_heading_6"></a>
                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_links" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_links"></a>
                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_links_hover" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_links_hover"></a>
                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_image_primary" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_image_primary"></a>
                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_image_secondary" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_image_secondary"></a>
                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_ad_region_menu" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_ad_region_menu"></a>
                                <div class="header">Menu <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_ad_region_menu_item" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_ad_region_menu_item"></a>
                                <div class="header">Menu Item <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_ad_region_menu_item_hover" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_ad_region_menu_item_hover"></a>
                                <div class="header">Menu Item Hover <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="ad_region_' . $object['region_name'] . '_ad_region_previous_and_next_buttons" class="module">
                                <a class="anchor" name="ad_region_' . $object['region_name'] . '_ad_region_previous_and_next_buttons"></a>
                                <div class="header">Previous &amp; Next Buttons <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                        </div>
                    </div>';
                break;
            
            case 'menu':
                ${'output_' . $object['area'] . '_modules'} .= 
                    '<div id="menu_region_' . $object['region_name'] . '" class="module">
                        <a class="anchor" name="menu_region_' . $object['region_name'] . '"></a>
                        <div class="header" onmouseover="highlight_module(\'.menu_' . $object['region_name'] . '\')" onmouseout="unhighlight_module(\'.menu_' . $object['region_name'] . '\')">' . $output_region_label . '<span class="heading_arrow_image closed">&nbsp;</span></div>
                        <div class="content" style="display: none">
                            <div id="menu_region_' . $object['region_name'] . '_menu_region_layout" class="module">
                                <a class="anchor" name="menu_region_' . $object['region_name'] . '_menu_region_layout"></a>
                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="menu_region_' . $object['region_name'] . '_menu_region_background_borders_and_spacing" class="module">
                                <a class="anchor" name="menu_region_' . $object['region_name'] . '_menu_region_background_borders_and_spacing"></a>
                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="menu_region_' . $object['region_name'] . '_menu_region_menu_item" class="module">
                                <a class="anchor" name="menu_region_' . $object['region_name'] . '_menu_region_menu_item"></a>
                                <div class="header">Menu Item <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="menu_region_' . $object['region_name'] . '_menu_region_menu_item_hover" class="module">
                                <a class="anchor" name="menu_region_' . $object['region_name'] . '_menu_region_menu_item_hover"></a>
                                <div class="header">Menu Item Hover <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="menu_region_' . $object['region_name'] . '_menu_region_submenu_background_borders_and_spacing" class="module">
                                <a class="anchor" name="menu_region_' . $object['region_name'] . '_menu_region_submenu_background_borders_and_spacing"></a>
                                <div class="header">Sub-Menu <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="menu_region_' . $object['region_name'] . '_menu_region_submenu_menu_item" class="module">
                                <a class="anchor" name="menu_region_' . $object['region_name'] . '_menu_region_submenu_menu_item"></a>
                                <div class="header">Sub-Menu Item <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="menu_region_' . $object['region_name'] . '_menu_region_submenu_menu_item_hover" class="module">
                                <a class="anchor" name="menu_region_' . $object['region_name'] . '_menu_region_submenu_menu_item_hover"></a>
                                <div class="header">Sub-Menu Item Hover <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                        </div>
                    </div>';
                break;
            
            default:
                ${'output_' . $object['area'] . '_modules'} .= 
                    '<div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '" class="module">
                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '"></a>
                        <div class="header" onmouseover="highlight_module(\'#' . $object['area'] . ' .r' . $object['row'] . 'c' . $object['col'] . '\')" onmouseout="unhighlight_module(\'#' . $object['area'] . ' .r' . $object['row'] . 'c' . $object['col'] . '\')">' . $output_region_label . '<span class="heading_arrow_image closed">&nbsp;</span></div>
                        <div class="content" style="display: none">
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_layout" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_layout"></a>
                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_background_borders_and_spacing" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_background_borders_and_spacing"></a>
                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_text" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_text"></a>
                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_headings" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_headings"></a>
                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_headings_general" class="module">
                                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_headings_general"></a>
                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_1" class="module">
                                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_1"></a>
                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_2" class="module">
                                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_2"></a>
                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_3" class="module">
                                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_3"></a>
                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_4" class="module">
                                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_4"></a>
                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_5" class="module">
                                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_5"></a>
                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_6" class="module">
                                        <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_heading_6"></a>
                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_links" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_links"></a>
                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_links_hover" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_links_hover"></a>
                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_image_primary" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_image_primary"></a>
                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_image_secondary" class="module">
                                <a class="anchor" name="' . $object['area'] . '_r' . $object['row'] . 'c' . $object['col'] . '_image_secondary"></a>
                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                        </div>
                    </div>';
                break;
        }
    }
    
    $output_email_border = '';
    $output_site_border = '';
    $output_mobile_border = '';
    
    // if the page layout is an e-mail layout, then output email border
    if ($page_style_layout == 'one_column_email') {
        $output_email_border = 
            '<div id="email_border" class="module">
                <a class="anchor" name="email_border"></a>
                <div class="header" onmouseover="highlight_module(\'#email_border\')" onmouseout="unhighlight_module(\'#email_border\')">&nbsp;E-Mail Border<span class="theme_fold_css">#email_border</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                <div class="content" style="display: none"></div>
            </div>';

    // else if the page layout is a mobile layout, then output mobile border
    } else if ($page_style_layout == 'one_column_mobile') {
        $output_mobile_border = 
            '<div id="mobile_border" class="module">
                <a class="anchor" name="mobile_border"></a>
                <div class="header" onmouseover="highlight_module(\'#mobile_border\')" onmouseout="unhighlight_module(\'#mobile_border\')">&nbsp;Mobile Border<span class="theme_fold_css">#mobile_border</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                <div class="content" style="display: none"></div>
            </div>';

    // else the page layout is not an e-mail or mobile layout, so output site border
    } else {
        $output_site_border = 
            '<div id="site_border" class="module">
                <a class="anchor" name="site_border"></a>
                <div class="header" onmouseover="highlight_module(\'#site_border\')" onmouseout="unhighlight_module(\'#site_border\')">&nbsp;Site Border<span class="theme_fold_css">#site_border</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                <div class="content" style="display: none"></div>
            </div>';
    }
    
    $output_page_content_left = '';
    $output_page_content_right = '';
    
    // if the page layout is a three column page style, then output the page content left and page content right areas
    if ($page_style_layout == 'three_column_sidebar_left') {
        $output_page_content_left = 
            '<div id="page_content_left" class="module">
                <a class="anchor" name="page_content_left"></a>
                <div class="header" onmouseover="highlight_module(\'#page_content_left\')" onmouseout="unhighlight_module(\'#page_content_left\')">Content Left<span class="theme_fold_css">#page_content_left</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                <div class="content" style="display: none">
                    <div id="page_content_left_general" class="module">
                        <a class="anchor" name="page_content_left_general"></a>
                        <div class="header" onmouseover="highlight_module(\'#page_content_left\')" onmouseout="unhighlight_module(\'#page_content_left\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                        <div class="content" style="display: none">
                            <div id="page_content_left_layout" class="module">
                                <a class="anchor" name="page_content_left_layout"></a>
                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_left_background_borders_and_spacing" class="module">
                                <a class="anchor" name="page_content_left_background_borders_and_spacing"></a>
                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_left_text" class="module">
                                <a class="anchor" name="page_content_left_text"></a>
                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_left_headings" class="module">
                                <a class="anchor" name=""></a>
                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="page_content_left_headings_general" class="module">
                                        <a class="anchor" name="page_content_left_headings_general"></a>
                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_left_heading_1" class="module">
                                        <a class="anchor" name="page_content_left_heading_1"></a>
                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_left_heading_2" class="module">
                                        <a class="anchor" name="page_content_left_heading_2"></a>
                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_left_heading_3" class="module">
                                        <a class="anchor" name="page_content_left_heading_3"></a>
                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_left_heading_4" class="module">
                                        <a class="anchor" name="page_content_left_heading_4"></a>
                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_left_heading_5" class="module">
                                        <a class="anchor" name="page_content_left_heading_5"></a>
                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_left_heading_6" class="module">
                                        <a class="anchor" name="page_content_left_heading_6"></a>
                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="page_content_left_links" class="module">
                                <a class="anchor" name="page_content_left_links"></a>
                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_left_links_hover" class="module">
                                <a class="anchor" name="page_content_left_links_hover"></a>
                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_left_image_primary" class="module">
                                <a class="anchor" name="page_content_left_image_primary"></a>
                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_left_image_secondary" class="module">
                                <a class="anchor" name="page_content_left_image_secondary"></a>
                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                        </div>
                    </div>
                    ' . $output_page_content_left_modules . '
                </div>
            </div>';
        
        $output_page_content_right = 
            '<div id="page_content_right" class="module">
                <a class="anchor" name="page_content_right"></a>
                <div class="header" onmouseover="highlight_module(\'#page_content_right\')" onmouseout="unhighlight_module(\'#page_content_right\')">Content Right<span class="theme_fold_css">#page_content_right</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                <div class="content" style="display: none">
                    <div id="page_content_right_general" class="module">
                        <a class="anchor" name="page_content_right_general"></a>
                        <div class="header" onmouseover="highlight_module(\'#page_content_right\')" onmouseout="unhighlight_module(\'#page_content_right\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                        <div class="content" style="display: none">
                            <div id="page_content_right_layout" class="module">
                                <a class="anchor" name="page_content_right_layout"></a>
                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_right_background_borders_and_spacing" class="module">
                                <a class="anchor" name="page_content_right_background_borders_and_spacing"></a>
                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_right_text" class="module">
                                <a class="anchor" name="page_content_right_text"></a>
                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_right_headings" class="module">
                                <a class="anchor" name="page_content_right_headings"></a>
                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="page_content_right_headings_general" class="module">
                                        <a class="anchor" name="page_content_right_headings_general"></a>
                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_right_heading_1" class="module">
                                        <a class="anchor" name="page_content_right_heading_1"></a>
                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_right_heading_2" class="module">
                                        <a class="anchor" name="page_content_right_heading_2"></a>
                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_right_heading_3" class="module">
                                        <a class="anchor" name="page_content_right_heading_3"></a>
                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_right_heading_4" class="module">
                                        <a class="anchor" name="page_content_right_heading_4"></a>
                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_right_heading_5" class="module">
                                        <a class="anchor" name="page_content_right_heading_5"></a>
                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="page_content_right_heading_6" class="module">
                                        <a class="anchor" name="page_content_right_heading_6"></a>
                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="page_content_right_links" class="module">
                                <a class="anchor" name="page_content_right_links"></a>
                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_right_links_hover" class="module">
                                <a class="anchor" name="page_content_right_links_hover"></a>
                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_right_image_primary" class="module">
                                <a class="anchor" name="page_content_right_image_primary"></a>
                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_content_right_image_secondary" class="module">
                                <a class="anchor" name="page_content_right_image_secondary"></a>
                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                        </div>
                    </div>
                    ' . $output_page_content_right_modules . '
                </div>
            </div>';
    }
    
    $output_sidebar = '';
    
    // if the page layout is a two column or three column page style, then output the sidebar area
    if (
        ($page_style_layout == 'two_column_sidebar_left')
        || ($page_style_layout == 'two_column_sidebar_right')
        || ($page_style_layout == 'three_column_sidebar_left')
    ) {
        $output_sidebar = 
            '<div id="sidebar" class="module">
                <a class="anchor" name="sidebar"></a>
                <div class="header" onmouseover="highlight_module(\'#sidebar\')" onmouseout="unhighlight_module(\'#sidebar\')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sidebar<span class="theme_fold_css">#sidebar</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                <div class="content" style="display: none">
                    <div id="sidebar_general" class="module">
                        <a class="anchor" name="sidebar_general"></a>
                        <div class="header" onmouseover="highlight_module(\'#sidebar\')" onmouseout="unhighlight_module(\'#sidebar\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                        <div class="content" style="display: none">
                            <div id="sidebar_layout" class="module">
                                <a class="anchor" name="sidebar_layout"></a>
                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="sidebar_background_borders_and_spacing" class="module">
                                <a class="anchor" name="sidebar_background_borders_and_spacing"></a>
                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="sidebar_text" class="module">
                                <a class="anchor" name="sidebar_text"></a>
                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="sidebar_headings" class="module">
                                <a class="anchor" name="sidebar_headings"></a>
                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="sidebar_headings_general" class="module">
                                        <a class="anchor" name="sidebar_headings_general"></a>
                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="sidebar_heading_1" class="module">
                                        <a class="anchor" name="sidebar_heading_1"></a>
                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="sidebar_heading_2" class="module">
                                        <a class="anchor" name="sidebar_heading_2"></a>
                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="sidebar_heading_3" class="module">
                                        <a class="anchor" name="sidebar_heading_3"></a>
                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="sidebar_heading_4" class="module">
                                        <a class="anchor" name="sidebar_heading_4"></a>
                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="sidebar_heading_5" class="module">
                                        <a class="anchor" name="sidebar_heading_5"></a>
                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="sidebar_heading_6" class="module">
                                        <a class="anchor" name="sidebar_heading_6"></a>
                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="sidebar_links" class="module">
                                <a class="anchor" name="sidebar_links"></a>
                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="sidebar_links_hover" class="module">
                                <a class="anchor" name="sidebar_links_hover"></a>
                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="sidebar_image_primary" class="module">
                                <a class="anchor" name="sidebar_image_primary"></a>
                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="sidebar_image_secondary" class="module">
                                <a class="anchor" name="sidebar_image_secondary"></a>
                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                        </div>
                    </div>
                    ' . $output_sidebar_modules . '
                </div>
            </div>';
    }
    
    // if there is not already theme designer preview properties, then get the properties from the database
    if ((isset($_SESSION['software']['theme_designer'][$_GET['id']]['preview_properties']) == FALSE) || ($_SESSION['software']['theme_designer'][$_GET['id']]['preview_properties'] == '')) {
        // get the properties from the database, then loop through them to add them to the array
        $query = 
            "SELECT
                area,
                `row`, # Backticks for reserved word.
                col,
                module,
                property,
                value,
                region_type,
                region_name
            FROM system_theme_css_rules 
            WHERE file_id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while($row = mysqli_fetch_array($result)) {
            // if this is an ad region, then set the properties in the ad regions area of the array
            if ($row['region_type'] == 'ad') {
                $_SESSION['software']['theme_designer'][$_GET['id']]['preview_properties']['ad_region'][$row['region_name']][$row['module']][$row['property']] = $row['value'];
            
            // else if this is a menu region, then set the properties in the menu regions area of the array
            } elseif ($row['region_type'] == 'menu') {
                $_SESSION['software']['theme_designer'][$_GET['id']]['preview_properties']['menu_region'][$row['region_name']][$row['module']][$row['property']] = $row['value'];
                
            } else {
                // if there is a row then output the object
                if ($row['row'] != 0) {
                    $object = 'r' . $row['row'] . 'c' . $row['col'];
                    
                // else set the object as the base object
                } else {
                    $object = 'base_object';
                }
                
                // if the module is not blank, then set the module
                if ($row['module'] != '') {
                    $module = $row['module'];
                    
                // else set the module to the base module
                } else {
                    $module = 'base_module';
                }
                
                $_SESSION['software']['theme_designer'][$_GET['id']]['preview_properties'][$row['area']][$object][$module][$row['property']] = $row['value'];
            }
        }
    }
    
    // generate the system theme css and save the code in the edit theme code session
    $_SESSION['software']['theme_designer'][$_GET['id']]['code'] = generate_system_theme_css($_SESSION['software']['theme_designer'][$_GET['id']]['preview_properties']);
    
    // if there is a last open module, then output the javascript needed to open it
    if ($_SESSION['software']['theme_designer'][$_GET['id']]['last_opened_module'] != '') {
        $output_javascript = 'open_theme_designer_accordion_module(\'' . escape_javascript($_SESSION['software']['theme_designer'][$_GET['id']]['last_opened_module']) . '\');';
    }
    
    // if there is a send to, then set it as the send to value
    if ($_REQUEST['send_to'] != '') {
        $send_to = $_REQUEST['send_to'];
    
    // else set the send to to the edit theme file script
    } else {
        $send_to = PATH . SOFTWARE_DIRECTORY . '/edit_theme_file.php?id=' . $_GET['id'];
    }
    
    $query_string_from = '';
    
    // if page type is a certain page type, then prepare from for the query string
    switch ($page_type) {
        case 'view order':
        case 'custom form':
        case 'custom form confirmation':
        case 'form item view':
        case 'calendar event view':
        case 'catalog detail':
        case 'shipping address and arrival':
        case 'shipping method':
        case 'logout':
            $query_string_from = '&from=control_panel';
            break;
    }

    // if mobile page style, add glowing border around displayed name 
    if ($page_style_layout == 'one_column_mobile') {
        $display_page_style_name = '<span style="border: 1px solid #99FF33;padding: 0.25em 0.75em 0.25em 0.75em;-moz-border-radius-topleft: 5px;-webkit-border-top-left-radius: 5px;border-top-left-radius: 5px;-moz-border-radius-topright: 5px;-webkit-border-top-right-radius: 5px;border-top-right-radius: 5px;-moz-border-radius-bottomleft: 5px;-webkit-border-bottom-left-radius: 5px;border-bottom-left-radius: 5px;-moz-border-radius-bottomright: 5px;-webkit-border-bottom-right-radius: 5px;border-bottom-right-radius: 5px;">' . h($page_style_name) . '</span>';
    } else {
        $display_page_style_name = h($page_style_name);
    }
    
    echo
    '<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>Theme Designer</title>
            ' . get_generator_meta_tag() . '
            ' . output_control_panel_header_includes() . '
            <link rel="stylesheet" media="screen" type="text/css" href="colorpicker/css/colorpicker.css" />
            <script type="text/javascript" src="colorpicker/js/colorpicker.js"></script>
            <script type="text/javascript" src="colorpicker/js/eye.js"></script>
            ' . get_codemirror_includes() . '
            <script type="text/javascript">
                
                $(document).ready(function() {
                    // Resize the theme preview to match the windows height after waiting a little bit.
                    // We have to wait a little in order to avoid a bug where the buttons on the bottom
                    // get pushed too far down, out of view, after a fresh or hard refresh.
                    setTimeout (function () {
                        resize_theme_designer();
                    }, 50);
                    
                    // if the window is being resized, then resize the theme preview to match the windows height
                    $(window).resize(function() {
                        resize_theme_designer();
                    });
                    
                    // initiate the accordion
                    init_theme_designer_accordion();
                    ' . $output_javascript . '
                }); 
            </script>
            <style>
            #button_bar{height:50px;}
            #button_bar * {
                width: 30%;
            }
            </style>
        </head>
        <body class="design coloron">
            <div id="theme_designer_toolbar">
                <div id="header" style="    height: 50px;line-height: 50px;">
                    <img style="height:inherit;height: 37px;width: auto;margin-left: 1rem;" src="' . LOGO_URL . '" width="auto" border="0" alt="logo" title="" /><br />
                </div>
                <div id="subnav" style="font-weight: bold">
                    ' . h($file_name) . '
                </div>
                <div id="button_bar">
                    <a href="javascript:void(0)" onclick="open_advanced_styling_from_button_bar()">CSS</a>
                    <a href="javascript:void(0)" onclick="document.form.submit();">Update Preview Pane</a>
                    <a href="javascript:void(0)" onclick="open_view_source()">View Source</a>
                    <div id="view_source" style="display: none">
                        <div style="margin-bottom: 1.5em"><textarea id="view_source_textarea">' . h($_SESSION['software']['theme_designer'][$_GET['id']]['code']) . '</textarea></div>
                        <div><input type="button" value="Close" class="submit-primary" onclick="$(\'#view_source\').dialog(\'close\')" /></div>
                    </div>
                </div>
                <div id="content">
                    <div id="content_header">
                        ' . $liveform->output_errors() . '
                        ' . $liveform->output_notices() . '
                        <a href="#" class="green_help_button" style="float: right; margin-top: .5em; text-decoration: none">Help</a>
                        <h1 style="margin-bottom: .5em">Theme Designer</h1>
                        <table class="field">
                            <tr>
                                <td>Preview Page:</td>
                                <td>' . h($page_to_preview_name) . '</td>
                            </tr>
                            <tr>
                                <td>Page Style:</td>
                                <td>' . $display_page_style_name . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: left; padding: 0" class="theme_fold_css">.' . h($page_style_layout) . ' .' . h(get_class_name($page_style_name)) . '</td>
                            </tr>
                        </table>
                    </div>
                    <form name="form" action="theme_designer.php" method="post">
                        <div id="modules">
                            <div id="site_wide" class="module">
                                <a class="anchor" name="site_wide"></a>
                                <div class="header" onmouseover="highlight_module(\'body\')" onmouseout="unhighlight_module(\'body\')">Site Wide<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="site_wide_general" class="module">
                                        <a class="anchor" name="site_wide_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'body\')" onmouseout="unhighlight_module(\'body\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_text" class="module">
                                        <a class="anchor" name="site_wide_text"></a>
                                        <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_headings" class="module">
                                        <a class="anchor" name="site_wide_headings"></a>
                                        <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="site_wide_headings_general" class="module">
                                                <a class="anchor" name="site_wide_headings_general"></a>
                                                <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_wide_heading_1" class="module">
                                                <a class="anchor" name="site_wide_heading_1"></a>
                                                <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_wide_heading_2" class="module">
                                                <a class="anchor" name="site_wide_heading_2"></a>
                                                <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_wide_heading_3" class="module">
                                                <a class="anchor" name="site_wide_heading_3"></a>
                                                <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_wide_heading_4" class="module">
                                                <a class="anchor" name="site_wide_heading_4"></a>
                                                <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_wide_heading_5" class="module">
                                                <a class="anchor" name="site_wide_heading_5"></a>
                                                <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_wide_heading_6" class="module">
                                                <a class="anchor" name="site_wide_heading_6"></a>
                                                <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="site_wide_links" class="module">
                                        <a class="anchor" name="site_wide_links"></a>
                                        <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_links_hover" class="module">
                                        <a class="anchor" name="site_wide_links_hover"></a>
                                        <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_paragraph" class="module">
                                        <a class="anchor" name="site_wide_paragraph"></a>
                                        <div class="header">Paragraph <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_input" class="module">
                                        <a class="anchor" name="site_wide_input"></a>
                                        <div class="header">Form Fields <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_primary_buttons" class="module">
                                        <a class="anchor" name="site_wide_primary_buttons"></a>
                                        <div class="header">Primary Buttons <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_primary_buttons_hover" class="module">
                                        <a class="anchor" name="site_wide_primary_buttons_hover"></a>
                                        <div class="header">Primary Buttons Hover <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_secondary_buttons" class="module">
                                        <a class="anchor" name="site_wide_secondary_buttons"></a>
                                        <div class="header">Secondary Buttons <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_secondary_buttons_hover" class="module">
                                        <a class="anchor" name="site_wide_secondary_buttons_hover"></a>
                                        <div class="header">Secondary Buttons Hover <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_image_primary" class="module">
                                        <a class="anchor" name="site_wide_image_primary"></a>
                                        <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                    <div id="site_wide_image_secondary" class="module">
                                        <a class="anchor" name="site_wide_image_secondary"></a>
                                        <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="body" class="module">
                                <a class="anchor" name="body"></a>
                                <div class="header" onmouseover="highlight_module(\'body\')" onmouseout="unhighlight_module(\'body\')">Site Background<span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            ' . $output_email_border . '
                            ' . $output_mobile_border . '
                            ' . $output_site_border . '
                            <div id="site_top" class="module">
                                <a class="anchor" name="site_top"></a>
                                <div class="header" onmouseover="highlight_module(\'#site_top\')" onmouseout="unhighlight_module(\'#site_top\')">&nbsp;&nbsp;Site Top<span class="theme_fold_css">#site_top</span><span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="site_top_general" class="module">
                                        <a class="anchor" name="site_top_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#site_top\')" onmouseout="unhighlight_module(\'#site_top\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="site_top_layout" class="module">
                                                <a class="anchor" name="site_top_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_top_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="site_top_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_top_text" class="module">
                                                <a class="anchor" name="site_top_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_top_links" class="module">
                                                <a class="anchor" name="site_top_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_top_links_hover" class="module">
                                                <a class="anchor" name="site_top_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_site_top_modules . '
                                </div>
                            </div>
                            <div id="site_header" class="module">
                                <a class="anchor" name="site_header"></a>
                                <div class="header" onmouseover="highlight_module(\'#site_header\')" onmouseout="unhighlight_module(\'#site_header\')">&nbsp;&nbsp;Site Header<span class="theme_fold_css">#site_header</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="site_header_general" class="module">
                                        <a class="anchor" name="site_header_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#site_header\')" onmouseout="unhighlight_module(\'#site_header\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="site_header_layout" class="module">
                                                <a class="anchor" name="site_header_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_header_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="site_header_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_header_text" class="module">
                                                <a class="anchor" name="site_header_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_header_headings" class="module">
                                                <a class="anchor" name="site_header_headings"></a>
                                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none">
                                                    <div id="site_header_headings_general" class="module">
                                                        <a class="anchor" name="site_header_headings_general"></a>
                                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_header_heading_1" class="module">
                                                        <a class="anchor" name="site_header_heading_1"></a>
                                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_header_heading_2" class="module">
                                                        <a class="anchor" name="site_header_heading_2"></a>
                                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_header_heading_3" class="module">
                                                        <a class="anchor" name="site_header_heading_3"></a>
                                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_header_heading_4" class="module">
                                                        <a class="anchor" name="site_header_heading_4"></a>
                                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_header_heading_5" class="module">
                                                        <a class="anchor" name="site_header_heading_5"></a>
                                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_header_heading_6" class="module">
                                                        <a class="anchor" name="site_header_heading_6"></a>
                                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="site_header_links" class="module">
                                                <a class="anchor" name="site_header_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_header_links_hover" class="module">
                                                <a class="anchor" name="site_header_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_header_image_primary" class="module">
                                                <a class="anchor" name="site_header_image_primary"></a>
                                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_header_image_secondary" class="module">
                                                <a class="anchor" name="site_header_image_secondary"></a>
                                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_site_header_modules . '
                                </div>
                            </div>
                            <div id="area_border" class="module">
                                <a class="anchor" name="area_border"></a>
                                <div class="header" onmouseover="highlight_module(\'#area_border\')" onmouseout="unhighlight_module(\'#area_border\')">&nbsp;&nbsp;Area Border<span class="theme_fold_css">#area_border</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="area_header" class="module">
                                <a class="anchor" name="area_header"></a>
                                <div class="header" onmouseover="highlight_module(\'#area_header\')" onmouseout="unhighlight_module(\'#area_header\')">&nbsp;&nbsp;&nbsp;Area Header<span class="theme_fold_css">#area_header</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="area_header_general" class="module">
                                        <a class="anchor" name="area_header_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#area_header\')" onmouseout="unhighlight_module(\'#area_header\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="area_header_layout" class="module">
                                                <a class="anchor" name="area_header_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_header_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="area_header_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_header_text" class="module">
                                                <a class="anchor" name="area_header_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_header_headings" class="module">
                                                <a class="anchor" name="area_header_headings"></a>
                                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none">
                                                    <div id="area_header_headings_general" class="module">
                                                        <a class="anchor" name="area_header_headings_general"></a>
                                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_header_heading_1" class="module">
                                                        <a class="anchor" name="area_header_heading_1"></a>
                                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_header_heading_2" class="module">
                                                        <a class="anchor" name="area_header_heading_2"></a>
                                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_header_heading_3" class="module">
                                                        <a class="anchor" name="area_header_heading_3"></a>
                                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_header_heading_4" class="module">
                                                        <a class="anchor" name="area_header_heading_4"></a>
                                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_header_heading_5" class="module">
                                                        <a class="anchor" name="area_header_heading_5"></a>
                                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_header_heading_6" class="module">
                                                        <a class="anchor" name="area_header_heading_6"></a>
                                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="area_header_links" class="module">
                                                <a class="anchor" name="area_header_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_header_links_hover" class="module">
                                                <a class="anchor" name="area_header_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_header_image_primary" class="module">
                                                <a class="anchor" name="area_header_image_primary"></a>
                                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_header_image_secondary" class="module">
                                                <a class="anchor" name="area_header_image_secondary"></a>
                                                <div class="header">Secondary Images <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_area_header_modules . '
                                </div>
                            </div>
                            <div id="page_wrapper" class="module">
                                <a class="anchor" name="page_wrapper"></a>
                                <div class="header" onmouseover="highlight_module(\'#page_wrapper\')" onmouseout="unhighlight_module(\'#page_wrapper\')">&nbsp;&nbsp;&nbsp;Page Wrapper<span class="theme_fold_css">#page_wrapper</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_border" class="module">
                                <a class="anchor" name="page_border"></a>
                                <div class="header" onmouseover="highlight_module(\'#page_border\')" onmouseout="unhighlight_module(\'#page_border\')">&nbsp;&nbsp;&nbsp;&nbsp;Page Border<span class="theme_fold_css">#page_border</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="page_header" class="module">
                                <a class="anchor" name="page_header"></a>
                                <div class="header" onmouseover="highlight_module(\'#page_header\')" onmouseout="unhighlight_module(\'#page_header\')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Page Header<span class="theme_fold_css">#page_header</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="page_header_general" class="module">
                                        <a class="anchor" name="page_header_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#page_header\')" onmouseout="unhighlight_module(\'#page_header\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="page_header_layout" class="module">
                                                <a class="anchor" name="page_header_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_header_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="page_header_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_header_text" class="module">
                                                <a class="anchor" name="page_header_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_header_headings" class="module">
                                                <a class="anchor" name="page_header_headings"></a>
                                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none">
                                                    <div id="page_header_headings_general" class="module">
                                                        <a class="anchor" name="page_header_headings_general"></a>
                                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_header_heading_1" class="module">
                                                        <a class="anchor" name="page_header_heading_1"></a>
                                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_header_heading_2" class="module">
                                                        <a class="anchor" name="page_header_heading_2"></a>
                                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_header_heading_3" class="module">
                                                        <a class="anchor" name="page_header_heading_3"></a>
                                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_header_heading_4" class="module">
                                                        <a class="anchor" name="page_header_heading_4"></a>
                                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_header_heading_5" class="module">
                                                        <a class="anchor" name="page_header_heading_5"></a>
                                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_header_heading_6" class="module">
                                                        <a class="anchor" name="page_header_heading_6"></a>
                                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="page_header_links" class="module">
                                                <a class="anchor" name="page_header_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_header_links_hover" class="module">
                                                <a class="anchor" name="page_header_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_header_image_primary" class="module">
                                                <a class="anchor" name="page_header_image_primary"></a>
                                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_header_image_secondary" class="module">
                                                <a class="anchor" name="page_header_image_secondary"></a>
                                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_page_header_modules . '
                                </div>
                            </div>
                            <div id="page_content" class="module">
                                <a class="anchor" name="page_content"></a>
                                <div class="header" onmouseover="highlight_module(\'#page_content\')" onmouseout="unhighlight_module(\'#page_content\')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Page Content<span class="theme_fold_css">#page_content</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="page_content_general" class="module">
                                        <a class="anchor" name="page_content_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#page_content\')" onmouseout="unhighlight_module(\'#page_content\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="page_content_layout" class="module">
                                                <a class="anchor" name="page_content_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_content_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="page_content_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_content_text" class="module">
                                                <a class="anchor" name="page_content_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_content_headings" class="module">
                                                <a class="anchor" name="page_content_headings"></a>
                                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none">
                                                    <div id="page_content_headings_general" class="module">
                                                        <a class="anchor" name="page_content_headings_general"></a>
                                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_content_heading_1" class="module">
                                                        <a class="anchor" name="page_content_heading_1"></a>
                                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_content_heading_2" class="module">
                                                        <a class="anchor" name="page_content_heading_2"></a>
                                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_content_heading_3" class="module">
                                                        <a class="anchor" name="page_content_heading_3"></a>
                                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_content_heading_4" class="module">
                                                        <a class="anchor" name="page_content_heading_4"></a>
                                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_content_heading_5" class="module">
                                                        <a class="anchor" name="page_content_heading_5"></a>
                                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_content_heading_6" class="module">
                                                        <a class="anchor" name="page_content_heading_6"></a>
                                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="page_content_links" class="module">
                                                <a class="anchor" name="page_content_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_content_links_hover" class="module">
                                                <a class="anchor" name="page_content_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_content_image_primary" class="module">
                                                <a class="anchor" name="page_content_image_primary"></a>
                                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_content_image_secondary" class="module">
                                                <a class="anchor" name="page_content_image_secondary"></a>
                                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_page_content_modules . '
                                    ' . $output_page_content_left . '
                                    ' . $output_page_content_right . '
                                </div>
                            </div>
                            ' . $output_sidebar . '
                            <div id="page_footer" class="module">
                                <a class="anchor" name="page_footer"></a>
                                <div class="header" onmouseover="highlight_module(\'#page_footer\')" onmouseout="unhighlight_module(\'#page_footer\')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Page Footer<span class="theme_fold_css">#page_footer</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="page_footer_general" class="module">
                                        <a class="anchor" name="page_footer_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#page_footer\')" onmouseout="unhighlight_module(\'#page_footer\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="page_footer_layout" class="module">
                                                <a class="anchor" name="page_footer_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_footer_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="page_footer_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_footer_text" class="module">
                                                <a class="anchor" name="page_footer_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_footer_headings" class="module">
                                                <a class="anchor" name="page_footer_headings"></a>
                                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none">
                                                    <div id="page_footer_headings_general" class="module">
                                                        <a class="anchor" name="page_footer_headings_general"></a>
                                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_footer_heading_1" class="module">
                                                        <a class="anchor" name="page_footer_heading_1"></a>
                                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_footer_heading_2" class="module">
                                                        <a class="anchor" name="page_footer_heading_2"></a>
                                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_footer_heading_3" class="module">
                                                        <a class="anchor" name="page_footer_heading_3"></a>
                                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_footer_heading_4" class="module">
                                                        <a class="anchor" name="page_footer_heading_4"></a>
                                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_footer_heading_5" class="module">
                                                        <a class="anchor" name="page_footer_heading_5"></a>
                                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="page_footer_heading_6" class="module">
                                                        <a class="anchor" name="page_footer_heading_6"></a>
                                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="page_footer_links" class="module">
                                                <a class="anchor" name="page_footer_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_footer_links_hover" class="module">
                                                <a class="anchor" name="page_footer_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_footer_image_primary" class="module">
                                                <a class="anchor" name="page_footer_image_primary"></a>
                                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="page_footer_image_secondary" class="module">
                                                <a class="anchor" name="page_footer_image_secondary"></a>
                                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_page_footer_modules . '
                                </div>
                            </div>
                            <div id="area_footer" class="module">
                                <a class="anchor" name="area_footer"></a>
                                <div class="header" onmouseover="highlight_module(\'#area_footer\')" onmouseout="unhighlight_module(\'#area_footer\')">&nbsp;&nbsp;&nbsp;&nbsp;Area Footer<span class="theme_fold_css">#area_footer</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="area_footer_general" class="module">
                                        <a class="anchor" name="area_footer_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#area_footer\')" onmouseout="unhighlight_module(\'#area_footer\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="area_footer_layout" class="module">
                                                <a class="anchor" name="area_footer_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_footer_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="area_footer_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_footer_text" class="module">
                                                <a class="anchor" name="area_footer_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_footer_headings" class="module">
                                                <a class="anchor" name="area_footer_headings"></a>
                                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none">
                                                    <div id="area_footer_headings_general" class="module">
                                                        <a class="anchor" name="area_footer_headings_general"></a>
                                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_footer_heading_1" class="module">
                                                        <a class="anchor" name="area_footer_heading_1"></a>
                                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_footer_heading_2" class="module">
                                                        <a class="anchor" name="area_footer_heading_2"></a>
                                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_footer_heading_3" class="module">
                                                        <a class="anchor" name="area_footer_heading_3"></a>
                                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_footer_heading_4" class="module">
                                                        <a class="anchor" name="area_footer_heading_4"></a>
                                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_footer_heading_5" class="module">
                                                        <a class="anchor" name="area_footer_heading_5"></a>
                                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="area_footer_heading_6" class="module">
                                                        <a class="anchor" name="area_footer_heading_6"></a>
                                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="area_footer_links" class="module">
                                                <a class="anchor" name="area_footer_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_footer_links_hover" class="module">
                                                <a class="anchor" name="area_footer_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_footer_image_primary" class="module">
                                                <a class="anchor" name="area_footer_image_primary"></a>
                                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="area_footer_image_secondary" class="module">
                                                <a class="anchor" name="area_footer_image_secondary"></a>
                                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_area_footer_modules . '
                                </div>
                            </div>
                            <div id="site_footer_border" class="module">
                                <a class="anchor" name="site_footer_border"></a>
                                <div class="header" onmouseover="highlight_module(\'#site_footer_border\')" onmouseout="unhighlight_module(\'#site_footer_border\')">&nbsp;&nbsp;Site Footer Border<span class="theme_fold_css">#site_footer_border</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none"></div>
                            </div>
                            <div id="site_footer" class="module">
                                <a class="anchor" name="site_footer"></a>
                                <div class="header" onmouseover="highlight_module(\'#site_footer\')" onmouseout="unhighlight_module(\'#site_footer\')">&nbsp;&nbsp;&nbsp;Site Footer <span class="theme_fold_css">#site_footer</span> <span class="heading_arrow_image closed">&nbsp;</span></div>
                                <div class="content" style="display: none">
                                    <div id="site_footer_general" class="module">
                                        <a class="anchor" name="site_footer_general"></a>
                                        <div class="header" onmouseover="highlight_module(\'#site_footer\')" onmouseout="unhighlight_module(\'#site_footer\')">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                        <div class="content" style="display: none">
                                            <div id="site_footer_layout" class="module">
                                                <a class="anchor" name="site_footer_layout"></a>
                                                <div class="header">Layout <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_footer_background_borders_and_spacing" class="module">
                                                <a class="anchor" name="site_footer_background_borders_and_spacing"></a>
                                                <div class="header">Background, Borders and Spacing <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_footer_text" class="module">
                                                <a class="anchor" name="site_footer_text"></a>
                                                <div class="header">Text <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_footer_headings" class="module">
                                                <a class="anchor" name="site_footer_headings"></a>
                                                <div class="header">Headings <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none">
                                                    <div id="site_footer_headings_general" class="module">
                                                        <a class="anchor" name="site_footer_headings_general"></a>
                                                        <div class="header">General <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_footer_heading_1" class="module">
                                                        <a class="anchor" name="site_footer_heading_1"></a>
                                                        <div class="header">Heading 1 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_footer_heading_2" class="module">
                                                        <a class="anchor" name="site_footer_heading_2"></a>
                                                        <div class="header">Heading 2 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_footer_heading_3" class="module">
                                                        <a class="anchor" name="site_footer_heading_3"></a>
                                                        <div class="header">Heading 3 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_footer_heading_4" class="module">
                                                        <a class="anchor" name="site_footer_heading_4"></a>
                                                        <div class="header">Heading 4 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_footer_heading_5" class="module">
                                                        <a class="anchor" name="site_footer_heading_5"></a>
                                                        <div class="header">Heading 5 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                    <div id="site_footer_heading_6" class="module">
                                                        <a class="anchor" name="site_footer_heading_6"></a>
                                                        <div class="header">Heading 6 <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                        <div class="content" style="display: none"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="site_footer_links" class="module">
                                                <a class="anchor" name="site_footer_links"></a>
                                                <div class="header">Links <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_footer_links_hover" class="module">
                                                <a class="anchor" name="site_footer_links_hover"></a>
                                                <div class="header">Links Hover Effect <span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_footer_image_primary" class="module">
                                                <a class="anchor" name="site_footer_image_primary"></a>
                                                <div class="header">Primary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                            <div id="site_footer_image_secondary" class="module">
                                                <a class="anchor" name="site_footer_image_secondary"></a>
                                                <div class="header">Secondary Images<span class="heading_arrow_image closed">&nbsp;</span></div>
                                                <div class="content" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                    ' . $output_site_footer_modules . '
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="file_id" name="id" value="' . h($_GET['id']) . '" />
                        ' . get_token_field() . '
                        <input type="hidden" name="send_to" value="' . h($send_to) . '" />
                        <div id="content_footer" class="buttons" style="padding-bottom: 1em\9;">
                            <input type="submit" name="submit_button" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_button" value="Duplicate" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="theme_designer_cancel_confirm(\'' . h(escape_javascript($send_to)) . '\')" class="submit-secondary">
                        </div>
                    </form>
                </div>
            </div>
            <iframe id="theme_preview_iframe" scrolling="auto" src="' . OUTPUT_PATH . h($page_to_preview_name) . '?edit_theme=true&theme_id=' . h(urlencode($_GET['id'])) . '&edit=no' . $query_string_from . '" frameborder="0"><p class="error">Your Browser does not support frames.</iframe>
        </body>
    </html>';
    
    $liveform->remove_form('edit_theme_file');

// else process the action that was submitted
} else {
    validate_token_field();
    
    // if the user choose to save the file, then process a file save
    if ($_POST['submit_button'] == 'Save') {

        // convert the post data into an array that contains the css rule's selector, property and value
        $new_css_properties = prepare_theme_designer_post_data($_POST);
        
        // if there are new css properties, then merge them into the preview properties session
        if ((is_array($new_css_properties) == TRUE) && (empty($new_css_properties) == FALSE)) {
            // loop through each level of the array to add the properties to the session
            foreach($new_css_properties as $area => $objects) {
                foreach($objects as $object => $modules) {
                    foreach($modules as $module => $properties) {
                        // if the properties are not empty, then add them to the session
                        if (empty($properties) == FALSE) {
                            $_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties'][$area][$object][$module] = $properties;
                        }
                    }
                }
            }
        }
        
        // loop through the css properties in order to add them to the database
        foreach($_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties'] as $area => $objects) {
            // loop through the objects in order to continue to update the database
            foreach($objects as $object => $modules) {
                $object_row = '';
                $object_column = '';
                
                // if the object is the base object, then set the object's row and column to 0
                if (($area == 'ad_region') || ($area == 'menu_region') || ($object == 'base_object')) {
                    $object_row = 0;
                    $object_column = 0;
                
                // else break the object's row and column out of the object name
                } else {
                    preg_match('/r(\d*).*?/i', $object, $matches);
                    $object_row = $matches[1];
                    
                    preg_match('/c(\d*).*?/i', $object, $matches);
                    $object_column = $matches[1];
                }
                
                // loop through the modules, delete all of the rules for each module from the database, and then continue to update the database with the new properties
                foreach($modules as $module => $properties) {
                    if ($module == 'base_module') {
                        $module = '';
                    }
                    
                    // if this is an ad region, then remove the properties from for this module
                    if ($area == 'ad_region') {
                        $query = "DELETE FROM system_theme_css_rules WHERE ((file_id = '" . escape($_POST['id']) . "') AND (region_type = 'ad') AND (region_name = '" . escape($object) . "') AND (module = '" . escape($module) . "'))";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // else if this is a menu region, then remove the properties from for this module
                    } elseif ($area == 'menu_region') {
                        $query = "DELETE FROM system_theme_css_rules WHERE ((file_id = '" . escape($_POST['id']) . "') AND (region_type = 'menu') AND (region_name = '" . escape($object) . "') AND (module = '" . escape($module) . "'))";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                    // else delete all of the rules from the database for this module
                    } else {
                        $query = "DELETE FROM system_theme_css_rules WHERE ((file_id = '" . escape($_POST['id']) . "') AND (area = '" . escape($area) . "') AND (`row` = '" . escape($object_row) . "') AND (col = '" . escape($object_column) . "') AND (module = '" . escape($module) . "'))";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                    
                    // if there are properties then loop through each property and add it to the database
                    if (is_array($properties) == TRUE) {
                        foreach($properties as $property => $value) {
                            // if there is a value, then add the property and value to the css rules table
                            if ($value != '') {
                                // if this is an ad region property, then insert the property
                                if ($area == 'ad_region') {
                                    $query = 
                                        "INSERT INTO system_theme_css_rules 
                                        (
                                            file_id,
                                            area,
                                            `row`, # Backticks for reserved word.
                                            col,
                                            module,
                                            region_type,
                                            region_name,
                                            property,
                                            value
                                        ) VALUES (
                                            '" . escape($_POST['id']) . "',
                                            '" . escape($area) . "',
                                            '" . escape($object_row) . "',
                                            '" . escape($object_column) . "',
                                            '" . escape($module) . "',
                                            'ad',
                                            '" . escape($object) . "',
                                            '" . escape($property) . "',
                                            '" . escape($value) . "'
                                        )";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                // else if this is a menu region property, then insert the property
                                } elseif ($area == 'menu_region') {
                                    $query = 
                                        "INSERT INTO system_theme_css_rules 
                                        (
                                            file_id,
                                            area,
                                            `row`, # Backticks for reserved word.
                                            col,
                                            module,
                                            region_type,
                                            region_name,
                                            property,
                                            value
                                        ) VALUES (
                                            '" . escape($_POST['id']) . "',
                                            '" . escape($area) . "',
                                            '" . escape($object_row) . "',
                                            '" . escape($object_column) . "',
                                            '" . escape($module) . "',
                                            'menu',
                                            '" . escape($object) . "',
                                            '" . escape($property) . "',
                                            '" . escape($value) . "'
                                        )";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                // else insert the property as a regular property
                                } else {
                                    $query = 
                                        "INSERT INTO system_theme_css_rules 
                                        (
                                            file_id,
                                            area,
                                            `row`, # Backticks for reserved word.
                                            col,
                                            module,
                                            property,
                                            value
                                        ) VALUES (
                                            '" . escape($_POST['id']) . "',
                                            '" . escape($area) . "',
                                            '" . escape($object_row) . "',
                                            '" . escape($object_column) . "',
                                            '" . escape($module) . "',
                                            '" . escape($property) . "',
                                            '" . escape($value) . "'
                                        )";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // get filename from the database
        $query = "SELECT name FROM files WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_array($result);
        $file_name = $row['name'];
        
        // remove the old css file for this theme
        unlink(FILE_DIRECTORY_PATH . '/' . $file_name);
        
        // generate the system theme css and save the code in the edit theme code session
        $_SESSION['software']['theme_designer'][$_POST['id']]['code'] = generate_system_theme_css($_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties']);
        
        // save the css file to the files directory
        $handle = fopen(FILE_DIRECTORY_PATH . '/' . $file_name, 'w');
		fwrite($handle, $_SESSION['software']['theme_designer'][$_POST['id']]['code']);
		fclose($handle);
        
        // update the last modified in the files tab
        $query =
            "UPDATE files 
            SET 
                timestamp = UNIX_TIMESTAMP(), 
                user = '" . $user['id'] . "' 
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the edit theme file script name is in the send to, then add a notice
        if (preg_match('/edit_theme_file.php/i', $_POST['send_to']) > 0) {
            // create a new liveform for the edit theme file script
            $liveform_edit_theme_file = new liveform('edit_theme_file');
            $liveform_edit_theme_file->add_notice('The theme was edited successfully.');
        }
        
        // log that the theme was edited
        log_activity("theme (" . $file_name . ") was edited", $_SESSION['sessionusername']);

        // clear theme data from session
        unset($_SESSION['software']['theme_designer'][$_POST['id']]);

        // send the user to a screen that will reload the theme so that it will clear the user's cache
        // so that the user does not view an old version of the theme
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/reload_theme.php?name=' . urlencode($file_name) . '&send_to=' . urlencode($_POST['send_to']));
        exit();
        
    // else if the user selected to save a new copy, then save a new copy
    } elseif ($_POST['submit_button'] == 'Duplicate') {
        
        // get the file data from the database
        $query =
            "SELECT
                name,
                folder,
                description,
                theme
            FROM files
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_array($result);
        $file_name = $row['name'];
        $folder = $row['folder'];
        $description = $row['description'];
        $theme = $row['theme'];

        $new_file_name = get_unique_name(array(
            'name' => $file_name,
            'type' => 'file'));
        
        // Get the position of the last period in order to get the extension.
        $position_of_last_period = mb_strrpos($new_file_name, '.');

        $file_extension = '';
        
        // If there is an extension then remember it.
        if ($position_of_last_period !== false) {
            $file_extension = mb_substr($new_file_name, $position_of_last_period + 1);
        }
        
        // insert duplicated file's data into files table
        // NOTE: the file size is set to 0 because at this point we do not have the actual file created yet. However we have to insert the record to get the file id,
        // the file size will be updated later on
        $query =
            "INSERT INTO files (
                name,
                folder,
                description,
                type,
                size,
                design,
                theme,
                user,
                timestamp)
            VALUES (
                '" . escape($new_file_name) . "',
                '" . escape($folder) . "',
                '" . escape($description) . "',
                '" . escape($file_extension) . "',
                '0',
                '1',
                '$theme',
                '$user[id]',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $new_theme_id = mysqli_insert_id(db::$con);
        
        // convert the post data into an array that contains the css rule's selector, property and value
        $new_css_properties = prepare_theme_designer_post_data($_POST);
        
        // if there are new css properties, then merge them into the preview properties session
        if ((is_array($new_css_properties) == TRUE) && (empty($new_css_properties) == FALSE)) {
            // loop through each level of the array to add the properties to the session
            foreach($new_css_properties as $area => $objects) {
                foreach($objects as $object => $modules) {
                    foreach($modules as $module => $properties) {
                        // if the properties are not empty, then add them to the session
                        if (empty($properties) == FALSE) {
                            $_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties'][$area][$object][$module] = $properties;
                        }
                    }
                }
            }
        }
        
        // generate the system theme css and save the code in the edit theme code session
        $_SESSION['software']['theme_designer'][$_POST['id']]['code'] = generate_system_theme_css($_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties']);
        
        // save the css file to the files directory
        $handle = fopen(FILE_DIRECTORY_PATH . '/' . $new_file_name, 'w');
		fwrite($handle, $_SESSION['software']['theme_designer'][$_POST['id']]['code']);
		fclose($handle);
        
        // update the filesize for this file in the database
        $query = "UPDATE files SET size = '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $new_file_name)) . "' WHERE id = '" . $new_theme_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through the css properties in order to add them to the database
        foreach($_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties'] as $area => $objects) {
            // loop through the objects in order to continue to update the database
            foreach($objects as $object => $modules) {
                $object_row = '';
                $object_column = '';
                
                // if the object is the base object, then set the object's row and column to 0
                if (($area == 'ad_region') || ($area == 'menu_region') || ($object == 'base_object')) {
                    $object_row = 0;
                    $object_column = 0;
                
                // else break the object's row and column out of the object name
                } else {
                    preg_match('/r(\d*).*?/i', $object, $matches);
                    $object_row = $matches[1];
                    
                    preg_match('/c(\d*).*?/i', $object, $matches);
                    $object_column = $matches[1];
                }
                
                // loop through the modules, delete all of the rules for each module from the database, and then continue to update the database with the new properties
                foreach($modules as $module => $properties) {
                    if ($module == 'base_module') {
                        $module = '';
                    }
                    
                    // if there are properties then loop through each property and add it to the database
                    if (is_array($properties) == TRUE) {
                        foreach($properties as $property => $value) {
                            // if there is a value, then add the property and value to the css rules table
                            if ($value != '') {
                                // if this is an ad region property, then insert the property
                                if ($area == 'ad_region') {
                                    $query = 
                                        "INSERT INTO system_theme_css_rules 
                                        (
                                            file_id,
                                            area,
                                            `row`, # Backticks for reserved word.
                                            col,
                                            module,
                                            region_type,
                                            region_name,
                                            property,
                                            value
                                        ) VALUES (
                                            '" . escape($new_theme_id) . "',
                                            '" . escape($area) . "',
                                            '" . escape($object_row) . "',
                                            '" . escape($object_column) . "',
                                            '" . escape($module) . "',
                                            'ad',
                                            '" . escape($object) . "',
                                            '" . escape($property) . "',
                                            '" . escape($value) . "'
                                        )";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                // else if this is a menu region property, then insert the property
                                } elseif ($area == 'menu_region') {
                                    $query = 
                                        "INSERT INTO system_theme_css_rules 
                                        (
                                            file_id,
                                            area,
                                            `row`, # Backticks for reserved word.
                                            col,
                                            module,
                                            region_type,
                                            region_name,
                                            property,
                                            value
                                        ) VALUES (
                                            '" . escape($new_theme_id) . "',
                                            '" . escape($area) . "',
                                            '" . escape($object_row) . "',
                                            '" . escape($object_column) . "',
                                            '" . escape($module) . "',
                                            'menu',
                                            '" . escape($object) . "',
                                            '" . escape($property) . "',
                                            '" . escape($value) . "'
                                        )";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                // else insert the property as a regular property
                                } else {
                                    $query = 
                                        "INSERT INTO system_theme_css_rules 
                                        (
                                            file_id,
                                            area,
                                            `row`, # Backticks for reserved word.
                                            col,
                                            module,
                                            property,
                                            value
                                        ) VALUES (
                                            '" . escape($new_theme_id) . "',
                                            '" . escape($area) . "',
                                            '" . escape($object_row) . "',
                                            '" . escape($object_column) . "',
                                            '" . escape($module) . "',
                                            '" . escape($property) . "',
                                            '" . escape($value) . "'
                                        )";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                }
                            }
                        }
                    }
                }
            }
        }

        // Duplicate preview style records.

        $preview_styles = db_items(
            "SELECT
                page_id,
                style_id,
                device_type
            FROM preview_styles
            WHERE theme_id = '" . escape($_POST['id']) . "'");
        
        foreach ($preview_styles as $preview_style) {
            db(
                "INSERT INTO preview_styles (
                    page_id,
                    theme_id,
                    style_id,
                    device_type)
                VALUES (
                    '" . $preview_style['page_id'] . "',
                    '" . $new_theme_id . "',
                    '" . $preview_style['style_id'] . "',
                    '" . $preview_style['device_type'] . "')");
        }
        
        // log that the theme was edited
        log_activity("a new copy of the theme (" . $file_name . ") was created", $_SESSION['sessionusername']);

        // clear theme data from session
        unset($_SESSION['software']['theme_designer'][$_POST['id']]);

        // Send the user to a screen that will reload the theme so that it will clear the user's cache
        // so that the user does not view an old version of the theme. Even though we are saving a new theme
        // with a new name, we still do this in case the new name was used in the past and the user has a cache of it.
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/reload_theme.php?name=' . urlencode($new_file_name) . '&send_to=' . urlencode(PATH . SOFTWARE_DIRECTORY . '/theme_designer.php?id=' . $new_theme_id));
        exit();
        
    // else the user selected to preview the file, so process a preview
    } else {
        // convert the post data into an array that contains the css rule's selector, property and value
        $new_css_properties = prepare_theme_designer_post_data($_POST);

        // if there are new css properties, then merge them into the preview properties session
        if ((is_array($new_css_properties) == TRUE) && (empty($new_css_properties) == FALSE)) {
            // loop through each level of the array to add the properties to the session
            foreach($new_css_properties as $area => $objects) {
                foreach($objects as $object => $modules) {
                    foreach($modules as $module => $properties) {
                        // if the properties are not empty, then add them to the session
                        if (empty($properties) == FALSE) {
                            $_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties'][$area][$object][$module] = $properties;
                        }
                    }
                }
            }
        }

        // generate the system theme css and save the code in the edit theme code session
        $_SESSION['software']['theme_designer'][$_POST['id']]['code'] = generate_system_theme_css($_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties']);
        
        $query_string_send_to = '';
        
        // if there is a send to, then prepare it for the url string
        if ($_REQUEST['send_to'] != '') {
            $query_string_send_to = '&send_to=' . urlencode($_REQUEST['send_to']);
        }
        
        // refresh the screen
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/theme_designer.php?id=' . urlencode($_POST['id']) . $query_string_send_to);
        exit();
    }
}

// this function removes blank entries from the input array, and returns an array that contains all of the properties and their values
function prepare_theme_designer_post_data($post_data = array()) 
{
    $css_rules = array();
    
    // loop through the post data array to remove blank entries and add each property to the css rules array
    foreach($post_data as $area => $objects) {
        // if the area is the submit button, then break out of the loop
        if ($area == 'id') {
            break;
        }
        
        // define the area part of the array
        $css_rules[$area] = array();
        
        // loop through the area's objects (eg. base, r1c1, r1c2) to prepare the object row and column, and also to add the properties to the css rules array
        foreach($objects as $object => $modules) {
            // define the area's object part of the array
            $css_rules[$area][$object] = array();
            
            // loop through the objects's modules to continue to add the properties to the css rules array
            foreach($modules as $module => $properties) {
                // define the area object's module part of the array
                $css_rules[$area][$object][$module] = array();

                // If this module is for previous & next buttons and there is not a property for the toggle,
                // then that means the toggle was not checked, so add property in order to make sure that
                // the toggle is disabled. We are not sure why we only need to do this for this particular toggle.
                // Maybe the other toggles also need this code and have bugs because they don't?
                if (
                    ($module == 'previous_and_next_buttons')
                    && (isset($properties['previous_and_next_buttons_toggle']) == FALSE)
                ) {
                    $properties['previous_and_next_buttons_toggle'] = '';
                }
                
                // initialize toggle variables to be used to determine what properties should be added to the css rules array
                $background_type = '';
                $borders_enabled = FALSE;
                $rounded_corners_enabled = FALSE;
                $shadows_enabled = FALSE;
                $previous_and_next_buttons_enabled = FALSE;
                
                // loop through each property, determine if it should be added to the css rules array, and then add it to the css rules array
                foreach($properties as $property => $property_value) {
                    $add_property = TRUE;
                    
                    // if this is the background type then save the background type to be used to determine if it's properties should be added to the css rules array
                    if ($property == 'background_type') {
                        $background_type = $property_value;
                    }
                    
                    // if the background type is "none", and if this is one of the background properties, then do not add the property to the css rules array
                    if (
                        ($background_type == '')
                        &&
                        (
                            ($property == 'background_color')
                            || ($property == 'background_image')
                            || ($property == 'background_horizontal_position')
                            || ($property == 'background_vertical_position')
                        )
                    ) {
                        $add_property = FALSE;
                    
                    // else if the background type is set to solid_color, and if this is one of the background image properties, then do not add the property to the css rules array
                    } elseif (
                        ($background_type == 'solid_color')
                        &&
                        (
                            ($property == 'background_image')
                            || ($property == 'background_horizontal_position')
                            || ($property == 'background_vertical_position')
                        )
                    ) {
                        $add_property = FALSE;
                    }
                    
                    // if this is the borders toggle, and if it's value is 1, then set borders enabled to true so that it's properties are not added to the css rules array
                    if (($property == 'borders_toggle') && ($property_value == 1)) {
                        $borders_enabled = TRUE;
                    }
                    
                    // if borders are not enabled, and if this is a border property, then do not add the property to the css rules array
                    if (
                        ($borders_enabled == FALSE) 
                        && 
                        (
                            ($property == 'borders_toggle')
                            || ($property == 'border_size')
                            || ($property == 'border_color')
                            || ($property == 'border_style')
                            || ($property == 'border_position')
                        )
                    ) {
                        $add_property = FALSE;
                    }
                    
                    // if this is the rounded corners toggle, and if it's value is 1, then set rounded corners enabled to true so that it's properties are not added to the css rules array
                    if (($property == 'rounded_corners_toggle') && ($property_value == 1)) {
                        $rounded_corners_enabled = TRUE;
                    }
                    
                    // if rounded corners are not enabled, and if this is a rounded corner property, then do not add the property to the array
                    if (
                        ($rounded_corners_enabled == FALSE) 
                        && 
                        (
                            ($property == 'rounded_corners_toggle')
                            || ($property == 'rounded_corner_top_left')
                            || ($property == 'rounded_corner_top_right')
                            || ($property == 'rounded_corner_bottom_left')
                            || ($property == 'rounded_corner_bottom_right')
                        )
                    ) {
                        $add_property = FALSE;
                    }
                     
                    // if this is the shadows toggle, and if it's value is 1, then set shadows enabled to true so that it's properties are not added to the css rules array
                    if (($property == 'shadows_toggle') && ($property_value == 1)) {
                        $shadows_enabled = TRUE;
                    }
                     
                    // if shadows are not enabled, and if this is a shadow property, then do not add the property to the array
                    if (
                        ($shadows_enabled == FALSE) 
                        && 
                        (
                            ($property == 'shadows_toggle')
                            || ($property == 'shadow_horizontal_offset')
                            || ($property == 'shadow_vertical_offset')
                            || ($property == 'shadow_blur_radius')
                            || ($property == 'shadow_color')
                        )
                    ) {
                        $add_property = FALSE;
                    }

                    // If this is the previous & next buttons toggle, and if it's value is 1, then set previous & next buttons enabled to true so that it's properties are added to the css rules array.
                    if (($property == 'previous_and_next_buttons_toggle') && ($property_value == 1)) {
                        $previous_and_next_buttons_enabled = TRUE;
                    }
                     
                    // If previous & next buttons are not enabled, and if this is a previous & next buttons property, then do not add the property to the array.
                    if (
                        ($previous_and_next_buttons_enabled == FALSE) 
                        && 
                        (
                            ($property == 'previous_and_next_buttons_toggle')
                            || ($property == 'previous_and_next_buttons_horizontal_offset')
                            || ($property == 'previous_and_next_buttons_vertical_offset')
                        )
                    ) {
                        $add_property = FALSE;
                    }
                   
                    $value = '';
                    
                    // if the properties value is an array, and if the amount is not blank, then save the value
                    if (is_array($property_value) == TRUE) {
                        if ($property_value['amount'] != '') {
                            $value = $property_value['amount'] . $property_value['unit'];
                        }
                        
                    // else use the property value as the value
                    } else {
                        $value = $property_value;
                    }
                    
                    // if the value is not blank, and if add property is true, then add it to the array
                    if (($value != '') && ($add_property == TRUE)) {
                        $css_rules[$area][$object][$module][$property] = $value;
                    
                    // else if the module is in the preview properties session, then set the property's value to blank
                    } elseif (empty($_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties'][$area][$object][$module]) == FALSE) {
                        $_SESSION['software']['theme_designer'][$_POST['id']]['preview_properties'][$area][$object][$module][$property] = '';
                    }
                }
            }
        }
    }
    
    // return the css rules array
    return $css_rules;
}

// this functions checks to see if this is a system page style and returns a boolean value
function is_system_page_style($style_id)
{
    // check to see if this is a system page style
    $query = "SELECT style_type FROM style WHERE style_id = '" . $style_id . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $is_system_page_style = FALSE;
    
    if ($row['style_type'] == 'system') {
        $is_system_page_style = TRUE;
    }
    
    return $is_system_page_style;
}