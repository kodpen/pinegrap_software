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
validate_area_access($user, 'designer');

$liveform = new liveform('view_styles');

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['design']['view_styles']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['design']['view_styles']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    // store sort in session
    $_SESSION['software']['design']['view_styles']['order'] = $_REQUEST['order'];
}

$number_of_results = 0;
$output_clear_button = '';

// If there is a query then store it in a session
if (isset($_REQUEST['query']) == true) {
    $_SESSION['software']['design']['view_styles']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['design']['view_styles']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['design']['view_styles']['query']) == true) && ($_SESSION['software']['design']['view_styles']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch($_SESSION['software']['design']['view_styles']['sort'])
{
    case 'Name':
        $sort_column = 'style_name';
        break;
    case 'Theme':
        $sort_column = 'theme_name';
        break;
    case 'Collection':
        $sort_column = 'collection';
        break;

    case 'Layout Type':
        $sort_column = 'layout_type';
        break;

    case 'Last Modified':
        $sort_column = 'style_timestamp';
        break;
    default:
        $sort_column = 'style_timestamp';
        $_SESSION['software']['design']['view_styles']['sort'] = 'Last Modified';
}

if ($_SESSION['software']['design']['view_styles']['order']) {
    $asc_desc = $_SESSION['software']['design']['view_styles']['order'];
} elseif ($sort_column == 'style_timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['design']['view_styles']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['design']['view_styles']['order'] = 'asc';
}

// get total number of styles
$query = "SELECT COUNT(style_id) FROM style";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_styles = $row[0];

// get the number of folders and pages that use each style
$style_usage = get_style_usage();

$search_query = mb_strtolower($_SESSION['software']['design']['view_styles']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',', style.style_name, style.style_code, files.name, user.user_username)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['design']['view_styles']['query'])) {
    // Get only the results the user wanted in the search.
    $where .= "WHERE $sql_search";
}

$query =
    "SELECT
        style.style_id as id,
        style.style_name as name,
        style.style_type as type,
        files.name AS theme_name,
        style.collection,
        style.layout_type,
        style.style_timestamp as last_modified_timestamp,
        user.user_username as last_modified_username
    FROM style
    LEFT JOIN files ON style.theme_id = files.id
    LEFT JOIN user ON style.style_user = user.user_id
    $where
    ORDER BY $sort_column $asc_desc";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$styles = array();

// Loop through the results
while ($row = mysqli_fetch_assoc($result)) {
    $styles[] = $row;
}

// loop through styles in order to prepare to output them
foreach ($styles as $style) {
    $output_link_url = 'edit_' . $style['type'] . '_style.php?id=' . $style['id'];
    
    // if the last modified username was found, then prepare to output it
    if ($style['last_modified_username']) {
        $last_modified_username = $style['last_modified_username'];
        
    // else the last modified username was not found, so prepare placeholder
    } else {
        $last_modified_username = '[Unknown]';
    }
    
    $number_of_results++;
    
    $output_rows .= '
        <tr>
            <td class="selectall"><input type="checkbox" name="styles[]" value="' . $style['id'] . '" class="checkbox" /></td>
            <td class="chart_label pointer" onclick="window.location.href=\'' . $output_link_url . '\'" nowrap>' . h($style['name']) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . number_format($style_usage[$style['id']]['number_of_folders']) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . number_format($style_usage[$style['id']]['number_of_pages']) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($style['theme_name']) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . strtoupper($style['collection']) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . ucwords($style['layout_type']) . '</td>
            <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" nowrap>' . get_relative_time(array('timestamp' => $style['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>
        </tr>';
}

print 
    output_header() . '
    <div id="subnav">
        ' . get_design_subnav() . '
    </div>
    <div id="button_bar">
        <a href="add_style.php">Create Page Style</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Page Styles</h1>
        <div class="subheading">All HTML templates that define the design and content layout for any page.</div>
        <form action="view_styles.php" method="get" class="search_form">
            <input type="text" name="query" value="' . h($_SESSION['software']['design']['view_styles']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_styles) . ' Total
        </div>
        <form action="delete_styles.php" method="post" class="disable_shortcut">
            ' . get_token_field() . '
            <table class="chart">
                <tr>
                    <th style="text-align: center" id="select_all">Select</th>
                    <th nowrap>' . get_column_heading('Name', $_SESSION['software']['design']['view_styles']['sort'], $_SESSION['software']['design']['view_styles']['order']) . '</th>
                    <th nowrap style="text-align: center">Folders Using Style</th>
                    <th nowrap style="text-align: center">Pages Using Style</th>
                    <th>' . get_column_heading('Theme', $_SESSION['software']['design']['view_styles']['sort'], $_SESSION['software']['design']['view_styles']['order']) . '</th>
                    <th style="text-align: center">' . get_column_heading('Collection', $_SESSION['software']['design']['view_styles']['sort'], $_SESSION['software']['design']['view_styles']['order']) . '</th>
                    <th>' . get_column_heading('Layout Type', $_SESSION['software']['design']['view_styles']['sort'], $_SESSION['software']['design']['view_styles']['order']) . '</th>
                    <th nowrap>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_styles']['sort'], $_SESSION['software']['design']['view_styles']['order']) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="buttons">
                <input type="submit" value="Delete Selected" class="delete" onclick="return confirm(\'WARNING: The selected page style(s) will be permanently deleted.\')" />
            </div>
        </form>
    </div>' .
    output_footer();

$liveform->remove_form('view_styles');

// get the number of folders and pages that use each style
// we pass data in arrays instead of performing a query at each branch of the folder tree for performance reasons
function get_style_usage($parent_folder_id = 0, $style_id = 0, $mobile_style_id = 0, $folders = array(), $pages = array())
{
    $style_usage = array();
    
    // if this is the first time this function has run, then get all folders and pages
    if ($parent_folder_id == 0) {
        // get all folders
        $query =
            "SELECT
                folder_id as id,
                folder_parent as parent_folder_id,
                folder_style as style_id,
                mobile_style_id
            FROM folder
            ORDER BY folder_level, folder_order";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        while ($row = mysqli_fetch_assoc($result)) {
            // if this folder is the parent folder, then set styles
            if ($row['id'] == $parent_folder_id) {
                $style_id = $row['style_id'];
                $mobile_style_id = $row['mobile_style_id'];
            }
            
            $folders[] = $row;
        }
        
        // get all pages
        $query =
            "SELECT
                page_folder as folder_id,
                page_style as style_id,
                mobile_style_id
            FROM page";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        while ($row = mysqli_fetch_assoc($result)) {
            $pages[] = $row;
        }
    }
    
    // increase the number of folders that use the desktop style
    ++$style_usage[$style_id]['number_of_folders'];

    // if the mobile style is not also the desktop style, then increase the number of folders that use the mobile style
    // if they are using custom styles, then they could technically select the same style for both desktop and mobile,
    // if that happens, we don't want to count the style twice for the same folder
    if ($mobile_style_id != $style_id) {
        ++$style_usage[$mobile_style_id]['number_of_folders'];
    }
    
    $child_folders = array();
    
    // loop through folders array in order to get all folders that are in parent folder
    foreach ($folders as $folder) {
        // if the parent folder id for this folder is equal to the parent folder id, then this is a child folder, so add to array
        if ($folder['parent_folder_id'] == $parent_folder_id) {
            $child_folders[] = $folder;
        }
    }
    
    // loop through child folders
    foreach ($child_folders as $folder) {
        // if the child folder does not have a desktop style set, then set desktop style to inherited style
        if ($folder['style_id'] == 0) {
            $folder['style_id'] = $style_id;
        }

        // if the child folder does not have a mobile style set, then set mobile style to inherited style
        if ($folder['mobile_style_id'] == 0) {
            $folder['mobile_style_id'] = $mobile_style_id;
        }
        
        $child_folder_style_usage = get_style_usage($folder['id'], $folder['style_id'], $folder['mobile_style_id'], $folders, $pages);
        
        // loop through child folder style usage, so that we can add values to array
        foreach ($child_folder_style_usage as $key => $style_id_usage) {
            $style_usage[$key]['number_of_folders'] = $style_usage[$key]['number_of_folders'] + $style_id_usage['number_of_folders'];
            $style_usage[$key]['number_of_pages'] = $style_usage[$key]['number_of_pages'] + $style_id_usage['number_of_pages'];
        }
    }
    
    // loop through all pages in order to figure out which pages are in this folder
    foreach ($pages as $page) {
        // if this page is in the parent folder, then get style data for page
        if ($page['folder_id'] == $parent_folder_id) {
            // if this page has a desktop style set for it, then increment data for that desktop style
            if ($page['style_id'] != 0) {
                ++$style_usage[$page['style_id']]['number_of_pages'];
                
            // else this page does not have a desktop style set for it, so increment data for inherited desktop style
            } else {
                ++$style_usage[$style_id]['number_of_pages'];
            }

            // if the mobile style is not also the desktop style, then increase the number of pages that use the mobile style
            if ($page['mobile_style_id'] != $page['style_id']) {
                // if this page has a mobile style set for it, then increment data for that mobile style
                if ($page['mobile_style_id'] != 0) {
                    ++$style_usage[$page['mobile_style_id']]['number_of_pages'];
                    
                // else this page does not have a mobile style set for it, so increment data for inherited mobile style
                } else {
                    ++$style_usage[$mobile_style_id]['number_of_pages'];
                }
            }
        }
    }
    
    return $style_usage;
}