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

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['editor_select_page_or_file'][$key] = trim($value);
    }
}

// If a screen was passed and it is a positive integer, then use it.
// These checks are necessary in order to avoid SQL errors below for a bogus screen value.
if (
    $_REQUEST['screen']
    and is_numeric($_REQUEST['screen'])
    and $_REQUEST['screen'] > 0
    and $_REQUEST['screen'] == round($_REQUEST['screen'])
) {
    $screen = (int) $_REQUEST['screen'];

// Otherwise, use the default, which is the first screen.
} else {
    $screen = 1;
}

if (isset($_SESSION['software']['editor_select_page_or_file']['type']) == false) {
    $_SESSION['software']['editor_select_page_or_file']['type'] = 'page';
}

// If folder is not set yet, set to "all".
if (isset($_SESSION['software']['editor_select_page_or_file']['folder_id']) == false) {
    $_SESSION['software']['editor_select_page_or_file']['folder_id'] = 'all';
}

// If access control type is not set yet, set to "all".
if (isset($_SESSION['software']['editor_select_page_or_file']['access_control_type']) == false) {
    $_SESSION['software']['editor_select_page_or_file']['access_control_type'] = 'all';
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['editor_select_page_or_file']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['editor_select_page_or_file']['query']) == true) && ($_SESSION['software']['editor_select_page_or_file']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true&CKEditorFuncNum=' . h(escape_javascript(urlencode($_GET['CKEditorFuncNum']))) . '\'" class="submit_small_secondary" />';
}

// If a folder was selected, then store that folder and all child folders
// in an array so that we can later determine if items are in the selected folder scope.
if ($_SESSION['software']['editor_select_page_or_file']['folder_id'] != 'all') {
    $folders = array();

    // Start the folders off with the selected folder.
    $folders[] = $_SESSION['software']['editor_select_page_or_file']['folder_id'];

    // Get all folders in order to add child folders to array.
    $all_folders = db_items(
        "SELECT
            folder_id AS id,
            folder_parent AS parent_folder_id
        FROM folder");

    // Get child folders under the selected folder.
    $child_folders = get_child_folders($_SESSION['software']['editor_select_page_or_file']['folder_id'], $all_folders);

    // Add child folders to array.
    $folders = array_merge($folders, $child_folders);
}

$extras = '&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum']));

// Create an array that we will use to store all items.
$items = array();

// Create an array that will be used to store data for the column
// that will be sorted.
$items_for_sorting = array();

$folders_that_user_has_access_to = array();

if (USER_ROLE == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to(USER_ID);
}

$output_type_page_style = '';
$output_type_file_style = '';
$output_type_short_link_style = '';

// Prepare info differently based on the selected type.
switch ($_SESSION['software']['editor_select_page_or_file']['type']) {
    default:
    case 'page':
        $output_type_page_style = ' style="font-weight: bold"';

        switch ($_SESSION['software']['editor_select_page_or_file']['sort']) {
            case 'URL':
                $sort_column = 'url';
                break;

            case 'Folder':
                $sort_column = 'folder_name';
                break;
                
            case 'Page Type':
                $sort_column = 'page_type';
                break;

            case 'Last Modified':
                $sort_column = 'last_modified_timestamp';
                break;

            default:
                $sort_column = 'last_modified_timestamp';
                $_SESSION['software']['editor_select_page_or_file']['sort'] = 'Last Modified';
                $_SESSION['software']['editor_select_page_or_file']['order'] = 'desc';
                break;
        }

        // If order is not set, set to ascending.
        if (isset($_SESSION['software']['editor_select_page_or_file']['order']) == false) {
            $_SESSION['software']['editor_select_page_or_file']['order'] = 'asc';
        }

        $output_heading_cells =
            '<th>' . get_column_heading('URL', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Folder', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Page Type', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Last Modified', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>';

        $where = "";

        // If there is a search query and it is not blank, then prepare filter.
        if ((isset($_SESSION['software']['editor_select_page_or_file']['query']) == true) && ($_SESSION['software']['editor_select_page_or_file']['query'] != '')) {
            $where .= "AND (LOWER(CONCAT_WS(',', page.page_name, page.page_type, folder.folder_name, user.user_username)) LIKE '%" . escape(escape_like(mb_strtolower($_SESSION['software']['editor_select_page_or_file']['query']))) . "%')";
        }

        // Get all pages.
        $pages = db_items(
            "SELECT
                page.page_name AS name,
                page.page_folder AS folder_id,
                folder.folder_name,
                page.page_type AS type,
                page.page_timestamp AS last_modified_timestamp,
                user.user_username AS last_modified_username
            FROM page
            LEFT JOIN folder ON page.page_folder = folder.folder_id
            LEFT JOIN user ON page.page_user = user.user_id
            WHERE
                (folder.folder_archived = '0')
                $where
            ORDER BY page_name ASC");

        // Loop through the pages in order to decide which we want to include.
        foreach ($pages as $page) {
            // Assume that this page should not be included until we find out otherwise.
            $include = false;

            // If this user has edit access to this page's folder,
            // and this page is within the scope of the selected folder,
            // then continue to determine if this page should be included in results.
            if (
                (
                    (USER_ROLE < 3)
                    || (check_folder_access_in_array($page['folder_id'], $folders_that_user_has_access_to) == true)
                )
                &&
                (
                    ($_SESSION['software']['editor_select_page_or_file']['folder_id'] == 'all')
                    || (in_array($page['folder_id'], $folders) == true)
                )
            ) {
                // If an access control type has been selected, then get access control type for page,
                // in order to determine if page should be included in results.
                if ($_SESSION['software']['editor_select_page_or_file']['access_control_type'] != 'all') {
                    $page['access_control_type'] = get_access_control_type($page['folder_id']);

                    // If the access control type for this page is the same as the selected access
                    // control type, then include page in results.
                    if ($page['access_control_type'] == $_SESSION['software']['editor_select_page_or_file']['access_control_type']) {
                        $include = true;
                    }

                // Otherwise an access control type has not been selected,
                // so include page in results.
                } else {
                    $include = true;
                }
            }

            // If this page should be included in results, then include it.
            if ($include == true) {
                $url = PATH . $page['name'];

                $items[] = array(
                    'url' => $url,
                    'folder_id' => $page['folder_id'],
                    'folder_name' => $page['folder_name'],
                    'page_type' => $page['type'],
                    'access_control_type' => $page['access_control_type'],
                    'last_modified_timestamp' => $page['last_modified_timestamp'],
                    'last_modified_username' => $page['last_modified_username']);

                // Store the appropriate value in the sort array.
                switch ($sort_column) {
                    case 'url':
                        $items_for_sorting[] = $url;
                        break;

                    case 'folder_name':
                        $items_for_sorting[] = $page['folder_name'];
                        break;
                        
                    case 'page_type':
                        $items_for_sorting[] = $page['type'];
                        break;

                    case 'last_modified_timestamp':
                        $items_for_sorting[] = $page['last_modified_timestamp'];
                        break;
                }
            }
        }

        break;

    case 'file':
        $output_type_file_style = ' style="font-weight: bold"';

        switch ($_SESSION['software']['editor_select_page_or_file']['sort']) {
            case 'URL':
                $sort_column = 'url';
                break;

            case 'Folder':
                $sort_column = 'folder_name';
                break;
                
            case 'Size':
                $sort_column = 'size';
                break;

            case 'Last Modified':
                $sort_column = 'last_modified_timestamp';
                break;

            default:
                $sort_column = 'last_modified_timestamp';
                $_SESSION['software']['editor_select_page_or_file']['sort'] = 'Last Modified';
                $_SESSION['software']['editor_select_page_or_file']['order'] = 'desc';
                break;
        }

        // If order is not set, set to ascending.
        if (isset($_SESSION['software']['editor_select_page_or_file']['order']) == false) {
            $_SESSION['software']['editor_select_page_or_file']['order'] = 'asc';
        }

        $output_heading_cells =
            '<th>&nbsp;</th>
            <th>' . get_column_heading('URL', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Folder', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Size', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Last Modified', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>';

        $where = "";

        // If there is a search query and it is not blank, then prepare filter.
        if ((isset($_SESSION['software']['editor_select_page_or_file']['query']) == true) && ($_SESSION['software']['editor_select_page_or_file']['query'] != '')) {
            $where .= "AND (LOWER(CONCAT_WS(',', files.name, folder.folder_name, user.user_username)) LIKE '%" . escape(escape_like(mb_strtolower($_SESSION['software']['editor_select_page_or_file']['query']))) . "%')";
        }

        // Get all files.
        $files = db_items(
            "SELECT
                files.name,
                files.folder AS folder_id,
                folder.folder_name,
                files.size,
                files.type,
                files.timestamp AS last_modified_timestamp,
                user.user_username AS last_modified_username
            FROM files
            LEFT JOIN folder ON files.folder = folder.folder_id
            LEFT JOIN user ON files.user = user.user_id
            WHERE
                (files.design = 0)
                AND (files.attachment = 0)
                AND (folder.folder_archived = '0')
                $where
            ORDER BY files.name ASC");

        // Loop through the files in order to decide which we want to include.
        foreach ($files as $file) {
            // Assume that this files should not be included until we find out otherwise.
            $include = false;

            // If this user has edit access to this files's folder,
            // and this files is within the scope of the selected folder,
            // then continue to determine if this files should be included in results.
            if (
                (
                    (USER_ROLE < 3)
                    || (check_folder_access_in_array($file['folder_id'], $folders_that_user_has_access_to) == true)
                )
                &&
                (
                    ($_SESSION['software']['editor_select_page_or_file']['folder_id'] == 'all')
                    || (in_array($file['folder_id'], $folders) == true)
                )
            ) {
                // If an access control type has been selected, then get access control type for file,
                // in order to determine if file should be included in results.
                if ($_SESSION['software']['editor_select_page_or_file']['access_control_type'] != 'all') {
                    $file['access_control_type'] = get_access_control_type($file['folder_id']);

                    // If the access control type for this file is the same as the selected access
                    // control type, then include file in results.
                    if ($file['access_control_type'] == $_SESSION['software']['editor_select_page_or_file']['access_control_type']) {
                        $include = true;
                    }

                // Otherwise an access control type has not been selected,
                // so include file in results.
                } else {
                    $include = true;
                }
            }

            // If this file should be included in results, then include it.
            if ($include == true) {
                $url = PATH . $file['name'];

                $items[] = array(
                    'name' => $file['name'],
                    'url' => $url,
                    'folder_id' => $file['folder_id'],
                    'folder_name' => $file['folder_name'],
                    'size' => $file['size'],
                    'file_type' => mb_strtolower($file['type']),
                    'access_control_type' => $file['access_control_type'],
                    'last_modified_timestamp' => $file['last_modified_timestamp'],
                    'last_modified_username' => $file['last_modified_username']);

                // Store the appropriate value in the sort array.
                switch ($sort_column) {
                    case 'url':
                        $items_for_sorting[] = $url;
                        break;

                    case 'folder_name':
                        $items_for_sorting[] = $file['folder_name'];
                        break;

                    case 'size':
                        $items_for_sorting[] = $file['size'];
                        break;

                    case 'last_modified_timestamp':
                        $items_for_sorting[] = $file['last_modified_timestamp'];
                        break;
                }
            }
        }

        break;

    case 'short_link':
        $output_type_short_link_style = ' style="font-weight: bold"';

        switch ($_SESSION['software']['editor_select_page_or_file']['sort']) {
            case 'URL':
                $sort_column = 'url';
                break;

            case 'Destination URL':
                $sort_column = 'destination_url';
                break;

            case 'Folder':
                $sort_column = 'folder_name';
                break;

            case 'Last Modified':
                $sort_column = 'last_modified_timestamp';
                break;

            default:
                $sort_column = 'last_modified_timestamp';
                $_SESSION['software']['editor_select_page_or_file']['sort'] = 'Last Modified';
                $_SESSION['software']['editor_select_page_or_file']['order'] = 'desc';
                break;
        }

        // If order is not set, set to ascending.
        if (isset($_SESSION['software']['editor_select_page_or_file']['order']) == false) {
            $_SESSION['software']['editor_select_page_or_file']['order'] = 'asc';
        }

        $output_heading_cells =
            '<th>' . get_column_heading('URL', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Destination URL', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Folder', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>
            <th>' . get_column_heading('Last Modified', $_SESSION['software']['editor_select_page_or_file']['sort'], $_SESSION['software']['editor_select_page_or_file']['order'], $extras) . '</th>';

        $where = "";

        // If there is a search query and it is not blank, then prepare filter.
        if ((isset($_SESSION['software']['editor_select_page_or_file']['query']) == true) && ($_SESSION['software']['editor_select_page_or_file']['query'] != '')) {
            $where .= "WHERE (LOWER(CONCAT_WS(',', short_links.name, short_links.destination_type, page.page_name, product_groups.name, products.name, short_links.url, short_links.tracking_code, last_modified_user.user_username)) LIKE '%" . escape(escape_like(mb_strtolower($_SESSION['software']['editor_select_page_or_file']['query']))) . "%')";
        }

        // Get all short links.
        $short_links = db_items(
            "SELECT
                short_links.name,
                short_links.destination_type,
                page.page_name,
                page.page_folder AS folder_id,
                folder.folder_name,
                product_groups.address_name AS product_group_address_name,
                products.address_name AS product_address_name,
                short_links.tracking_code,
                short_links.url,
                created_user.user_username AS created_username,
                last_modified_user.user_username AS last_modified_username,
                short_links.last_modified_timestamp
            FROM short_links
            LEFT JOIN page ON short_links.page_id = page.page_id
            LEFT JOIN folder ON page.page_folder = folder.folder_id
            LEFT JOIN product_groups ON short_links.product_group_id = product_groups.id
            LEFT JOIN products ON short_links.product_id = products.id
            LEFT JOIN user AS created_user ON short_links.created_user_id = created_user.user_id
            LEFT JOIN user AS last_modified_user ON short_links.last_modified_user_id = last_modified_user.user_id
            $where
            ORDER BY short_links.name ASC");

        // Loop through the short links in order to decide which we want to include.
        foreach ($short_links as $short_link) {
            // Assume that this short link should not be included until we find out otherwise.
            $include = false;

            // If this user has edit access to this short link,
            // and this short link is within the scope of the selected folder,
            // then continue to determine if this short link should be included in results.
            if (
                (
                    (USER_ROLE < 3)
                    ||
                    (
                        ($short_link['destination_type'] != 'url')
                        && (check_folder_access_in_array($short_link['folder_id'], $folders_that_user_has_access_to) == true)
                    )
                    ||
                    (
                        ($short_link['destination_type'] == 'url')
                        && (USER_USERNAME == $short_link['created_username'])
                    )
                )
                &&
                (
                    ($_SESSION['software']['editor_select_page_or_file']['folder_id'] == 'all')
                    ||
                    (
                        ($short_link['destination_type'] != 'url')
                        && (in_array($short_link['folder_id'], $folders) == true)
                    )
                )
            ) {
                // If an access control type has been selected, then determine if short link
                // should be included in results.
                if ($_SESSION['software']['editor_select_page_or_file']['access_control_type'] != 'all') {
                    // If the destination type for this short link is not url,
                    // then continue to check if short link should be included.
                    // When a user selects an access control type, other than "all",
                    // then we do not include short links with url destination type.
                    if ($short_link['destination_type'] != 'url') {
                        $short_link['access_control_type'] = get_access_control_type($short_link['folder_id']);

                        // If the access control type for this short link is the same as the selected access
                        // control type, then include short link in results.
                        if ($short_link['access_control_type'] == $_SESSION['software']['editor_select_page_or_file']['access_control_type']) {
                            $include = true;
                        }
                    }

                // Otherwise an access control type has not been selected,
                // so include short link in results.
                } else {
                    $include = true;
                }
            }

            // If this short link should be included in results, then include it.
            if ($include == true) {
                $url = PATH . $short_link['name'];

                switch ($short_link['destination_type']) {
                    case 'page':
                        $destination_url = PATH . $short_link['page_name'];
                        break;

                    case 'product_group':
                        $destination_url = PATH . $short_link['page_name'] . '/' . $short_link['product_group_address_name'];
                        break;

                    case 'product':
                        $destination_url = PATH . $short_link['page_name'] . '/' . $short_link['product_address_name'];
                        break;

                    case 'url':
                        $destination_url = $short_link['url'];
                        break;
                }

                // If there is a tracking code and the destination type is a certain type,
                // then add tracking code to destination.
                if (
                    ($short_link['tracking_code'] != '')
                    &&
                    (
                        ($short_link['destination_type'] == 'page')
                        || ($short_link['destination_type'] == 'product_group')
                        || ($short_link['destination_type'] == 'product')
                    )
                ) {
                    $destination_url .= '?t=' . $short_link['tracking_code'];
                }

                $folder_id = '';
                $folder_name = '';

                // If the destination type is not url, then prepare some properties.
                if ($short_link['destination_type'] != 'url') {
                    $folder_id = $short_link['folder_id'];
                    $folder_name = $short_link['folder_name'];
                }

                $items[] = array(
                    'url' => $url,
                    'destination_type' => $short_link['destination_type'],
                    'destination_url' => $destination_url,
                    'folder_id' => $folder_id,
                    'folder_name' => $folder_name,
                    'access_control_type' => $short_link['access_control_type'],
                    'last_modified_timestamp' => $short_link['last_modified_timestamp'],
                    'last_modified_username' => $short_link['last_modified_username']);

                // Store the appropriate value in the sort array.
                switch ($sort_column) {
                    case 'url':
                        $items_for_sorting[] = $url;
                        break;

                    case 'destination_url':
                        $items_for_sorting[] = $destination_url;
                        break;

                    case 'folder_name':
                        $items_for_sorting[] = $folder_name;
                        break;

                    case 'last_modified_timestamp':
                        $items_for_sorting[] = $short_link['last_modified_timestamp'];
                        break;
                }
            }
        }

        break;
}

$output_rows = '';

// if there is at least one result to display
if ($items) {
    // If the order is ascending, then sort like that.
    if ($_SESSION['software']['editor_select_page_or_file']['order'] == 'asc') {
        array_multisort($items_for_sorting, SORT_ASC, $items);

    // Otherwise the order is descending, so sort like that.
    } else {
        array_multisort($items_for_sorting, SORT_DESC, $items);
    }

    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($items);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="editor_select_page_or_file.php?screen=' . $previous . '&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'editor_select_page_or_file.php?screen=\' + this.options[this.selectedIndex].value + \'&CKEditorFuncNum=' . h(escape_javascript(urlencode($_GET['CKEditorFuncNum']))) . '\')">';

        // build HTML output for links to screens
        for ($i = 1; $i <= $number_of_screens; $i++) {
            // if this number is the current screen, then select option
            if ($i == $screen) {
                $selected = ' selected="selected"';
            // else this number is not the current screen, so do not select option
            } else {
                $selected = '';
            }

            $output_screen_links .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
        }

        $output_screen_links .= '</select>';
    }

    // build Next button if necessary
    $next = $screen + 1;
    // if next screen is less than or equal to the total number of screens, output next link
    if ($next <= $number_of_screens) {
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="editor_select_page_or_file.php?screen=' . $next . '&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($items) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $output_last_modified_username = '';
        
        if ($items[$key]['last_modified_username'] != '') {
            $output_last_modified_username = ' by ' . h($items[$key]['last_modified_username']);
        }

        // Prepare the rows of items differently based on the type.
        switch ($_SESSION['software']['editor_select_page_or_file']['type']) {
            default:
            case 'page':
                // If we did not get the access control type already up above for this page, then get it now.
                if ($items[$key]['access_control_type'] == '') {
                    $items[$key]['access_control_type'] = get_access_control_type($items[$key]['folder_id']);
                }

                $output_rows .=
                    '<tr class="pointer ' . h($items[$key]['access_control_type']) . '" onclick="window.opener.CKEDITOR.tools.callFunction(\'' . h(escape_javascript($_GET['CKEditorFuncNum'])) . '\', \'' . h(escape_javascript($items[$key]['url'])) . '\'); window.close();">
                        <td class="chart_label">' . h($items[$key]['url']) . '</td>
                        <td>' . h($items[$key]['folder_name']) . '</td>
                        <td>' . h(get_page_type_name($items[$key]['page_type'])) . '</td>
                        <td>' . get_relative_time(array('timestamp' => $items[$key]['last_modified_timestamp'])) . $output_last_modified_username . '</td>
                    </tr>';

                break;

            case 'file':
                // If we did not get the access control type already up above for this file, then get it now.
                if ($items[$key]['access_control_type'] == '') {
                    $items[$key]['access_control_type'] = get_access_control_type($items[$key]['folder_id']);
                }

                $output_thumbnail = '';

                // If this item is an image, then output thumbnail.
                if (
                    ($items[$key]['file_type'] == 'bmp')
                    || ($items[$key]['file_type'] == 'gif')
                    || ($items[$key]['file_type'] == 'jpg')
                    || ($items[$key]['file_type'] == 'jpeg')
                    || ($items[$key]['file_type'] == 'png')
                    || ($items[$key]['file_type'] == 'tif')
                    || ($items[$key]['file_type'] == 'tiff')
                ) {
                    // Get the dimensions of the image.
                    $image_size = @getimagesize(FILE_DIRECTORY_PATH . '/' . $items[$key]['name']);
                    $image_width = $image_size[0];
                    $image_height = $image_size[1];

                    // Set the maximum dimension size for the image.
                    $max_dimension = 75;

                    // Call function to resize image.
                    $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, $max_dimension);

                    // Output thumnail.
                    $output_thumbnail = '<img src="' . OUTPUT_PATH . h(encode_url_path($items[$key]['name'])) . '" width="' . $thumbnail_dimensions['width'] . '" height="' . $thumbnail_dimensions['height'] . '" alt="" title="' . $image_width . 'x' . $image_height . '" />';
                }

                $output_rows .=
                    '<tr class="pointer ' . h($items[$key]['access_control_type']) . '" onclick="window.opener.CKEDITOR.tools.callFunction(\'' . h(escape_javascript($_GET['CKEditorFuncNum'])) . '\', \'' . h(escape_javascript($items[$key]['url'])) . '\'); window.close();">
                        <td style="text-align: center">' . $output_thumbnail . '</td>
                        <td class="chart_label">' . h($items[$key]['url']) . '</td>
                        <td>' . h($items[$key]['folder_name']) . '</td>
                        <td>' . h(convert_bytes_to_string($items[$key]['size'])) . '</td>
                        <td>' . get_relative_time(array('timestamp' => $items[$key]['last_modified_timestamp'])) . $output_last_modified_username . '</td>
                    </tr>';

                break;

            case 'short_link':
                $output_access_control_type_class = '';

                // If the destination type is not url, then output access control type.
                if ($items[$key]['destination_type'] != 'url') {
                    // If we did not get the access control type already up above for this short link, then get it now.
                    if ($items[$key]['access_control_type'] == '') {
                        $items[$key]['access_control_type'] = get_access_control_type($items[$key]['folder_id']);
                    }

                    $output_access_control_type_class = ' ' . h($items[$key]['access_control_type']);
                }

                $output_rows .=
                    '<tr class="pointer' . $output_access_control_type_class . '" onclick="window.opener.CKEDITOR.tools.callFunction(\'' . h(escape_javascript($_GET['CKEditorFuncNum'])) . '\', \'' . h(escape_javascript($items[$key]['url'])) . '\'); window.close();">
                        <td class="chart_label">' . h($items[$key]['url']) . '</td>
                        <td>' . h($items[$key]['destination_url']) . '</td>
                        <td>' . h($items[$key]['folder_name']) . '</td>
                        <td>' . get_relative_time(array('timestamp' => $items[$key]['last_modified_timestamp'])) . $output_last_modified_username . '</td>
                    </tr>';

                break;
        }
    }
}

echo
    '<!DOCTYPE html>
    <html lang="'.language_ruler().'">
        <head>
            <meta charset="utf-8">
            <title>Browse Items</title>
            ' . get_generator_meta_tag() . '
            ' . output_control_panel_header_includes() . '
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        </head>
        <body class="select_page_or_file" style="overflow:auto;">


        <div id="filters">
                <form id="search" action="editor_select_page_or_file.php" method="get">
                    <input type="hidden" name="CKEditorFuncNum" value="' . h($_GET['CKEditorFuncNum']) . '" />
                    <div style="float:left;width: 100%;text-align: left;padding: 1rem;">Folder:
                    <select name="folder_id" onchange="submit_form(\'search\')"><option value="all">[All]</option>' . select_folder($_SESSION['software']['editor_select_page_or_file']['folder_id']) . '</select>
                    </div>
                    <div style="float:left;width: 100%;text-align: left;padding: 1rem;">Access:
                    <select name="access_control_type" class="' . h($_SESSION['software']['editor_select_page_or_file']['access_control_type']) . '" onchange="submit_form(\'search\')"><option value="all" class="all">[All]</option>' . select_access_control_type($_SESSION['software']['editor_select_page_or_file']['access_control_type'], false) . '</select>
                    </div>
                    <div style="float:left;width: 100%;text-align: left;padding: 1rem;">
                    <input type="text" name="query" value="' . h($_SESSION['software']['editor_select_page_or_file']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
                    </div>
                </form>
        </div>

        <div class="navigation fixed">
            <div class="title" title="Select the Page, File, or Short Link that you want to link to.">Browse Items
            <a href="editor_select_page_or_file.php?type=page&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '"' . $output_type_page_style . '>Pages</a> | <a href="editor_select_page_or_file.php?type=file&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '"' . $output_type_file_style . '>Files</a> | <a href="editor_select_page_or_file.php?type=short_link&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '"' . $output_type_short_link_style . '>Short Links</a>
            </div>
            
            <ul class="right">
                <li><a id="show_filters" href="#!" title="more filters" ><i class="material-icons">filter_list</i></a></li>
                <li><a href="#!" class="red-text" onclick="window.close()" ><i class="material-icons">close</i></a></li>
            </ul>
        </div>
        <script> 
        var filterDialog = $("#filters");
        var wWidth = $(window).width();
        var dWidth = wWidth * 0.5;
        filterDialog.dialog({
            autoOpen: false,
            width: dWidth,
            title:"Filters",
            minWidth: 300,
            modal: true,
            sticky: true,
            stack: true,
            close: function(event, ui) { $("#wrap").show(); },
            open: function(event, ui)  { 
                $(".ui-widget-overlay").bind("click", function(){ 
                    filterDialog.dialog("close"); 
                }); 
            }
        });
        $("#show_filters").on( "click", function() {
            filterDialog.parent().css({position:"fixed"}).end().dialog( "open" );
        });
    </script>
            <div id="content">
                <table class="chart">
                    <tr>
                        ' . $output_heading_cells . '
                    </tr>
                    ' . $output_rows . '
                </table>
                <div class="pagination">
                    ' . $output_screen_links . '
                </div>
            </div>
        </body>
    </html>';