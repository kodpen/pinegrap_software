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

$liveform = new liveform('view_themes');

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['design']['view_themes']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['design']['view_themes']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    // store sort in session
    $_SESSION['software']['design']['view_themes']['order'] = $_REQUEST['order'];
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

$output_clear_button = '';

// If there is a query then store it in a session
if (isset($_REQUEST['query']) == true) {
    $_SESSION['software']['design']['view_themes']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['design']['view_themes']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['design']['view_themes']['query']) == true) && ($_SESSION['software']['design']['view_themes']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['design']['view_themes']['sort']) {
    case 'File Name':
        $sort_column = 'name';
        break;

    case 'Description':
        $sort_column = 'description';
        break;

    case 'Folder':
        $sort_column = 'folder_name';
        break;

    case 'Activated for Desktop':
        $sort_column = 'activated_desktop_theme';
        break;

    case 'Activated for Mobile':
        $sort_column = 'activated_mobile_theme';
        break;

    case 'Last Modified':
        $sort_column = 'timestamp';
        break;

    default:
        $sort_column = 'timestamp';
        $_SESSION['software']['design']['view_themes']['sort'] = 'Last Modified';
        break;
}

if ($_SESSION['software']['design']['view_themes']['order']) {
    $asc_desc = $_SESSION['software']['design']['view_themes']['order'];
} elseif ($sort_column == 'timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['design']['view_themes']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['design']['view_themes']['order'] = 'asc';
}

// count all stylesheet files
$query =
    "SELECT
        COUNT(files.id)
    FROM files
    WHERE
        (type = 'css')
        AND (design = '1')
        AND (theme = '1')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_theme_files = $row[0];

$search_query = mb_strtolower($_SESSION['software']['design']['view_themes']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',', files.name, folder.folder_name, user.user_username)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['design']['view_themes']['query'])) {
    // Get only the results the user wanted in the search.
    $where .= " AND ($sql_search) ";
}

// get all stylesheet files
$query =
    "SELECT
        files.id,
        files.name,
        files.description,
        files.folder,
        files.activated_desktop_theme,
        files.activated_mobile_theme,
        files.timestamp,
        user.user_username,
        folder.folder_name
    FROM files
    LEFT JOIN user ON user.user_id = files.user
    LEFT JOIN folder ON folder.folder_id = files.folder
    WHERE
        (type = 'css')
        AND (design = '1')
        AND (theme = '1')
        $where
    ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_assoc($result)) {
    $files[] = $row;
}

// if there is at least one result to display
if ($files) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($files);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_themes.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_themes.php?screen=\' + this.options[this.selectedIndex].value)">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_themes.php?screen=' . $next . '">&gt;</a>';
    }
    
    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($files) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $output_activated_desktop_theme_check_mark = '';

        // if this theme is the activated desktop theme, then output check mark
        if ($files[$key]['activated_desktop_theme'] == 1) {
            $output_activated_desktop_theme_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        }

        $output_activated_mobile_theme_check_mark = '';

        // if this theme is the activated mobile theme, then output check mark
        if ($files[$key]['activated_mobile_theme'] == 1) {
            $output_activated_mobile_theme_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        }
        
        // If the theme was upload by a user then display their name.
        if (isset($files[$key]['user_username']) == TRUE) {
            $output_created_by_user = ' by ' . h($files[$key]['user_username']);
        } else {
            $output_created_by_user = ' by [Unknown]';
        }

        // Get folder access control type
        $folder_access_control_type = get_access_control_type($files[$key]['folder']);
        // Get folder access control name
        $output_access_control = get_access_control_type_name($folder_access_control_type);

        $output_link_url = h(escape_javascript(PATH . SOFTWARE_DIRECTORY)) . '/edit_theme_file.php?id=' . $files[$key]['id'];

        $output_rows .=
            '<tr>
                <td class="chart_label pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($files[$key]['name']) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . nl2br(h($files[$key]['description'])) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($files[$key]['folder_name']) . '&nbsp;(' . $output_access_control . ')</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_activated_desktop_theme_check_mark . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_activated_mobile_theme_check_mark . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $files[$key]['timestamp'])) . $output_created_by_user . '</td>
            </tr>';
    }
}

print
    output_header() . '
    <div id="subnav">
        ' . get_design_subnav() . '
    </div>
    <div id="button_bar">
        <a href="add_theme_file.php">Create Theme</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Themes</h1>
        <div class="subheading">All CSS stylesheet files used to add consistency to design and content.</div>
        <form action="view_themes.php" method="get" class="search_form">
            <input type="text" name="query" value="' . h($_SESSION['software']['design']['view_themes']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_theme_files) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('File Name', $_SESSION['software']['design']['view_themes']['sort'], $_SESSION['software']['design']['view_themes']['order']) . '</th>
                <th>' . get_column_heading('Description', $_SESSION['software']['design']['view_themes']['sort'], $_SESSION['software']['design']['view_themes']['order']) . '</th>
                <th>' . get_column_heading('Folder', $_SESSION['software']['design']['view_themes']['sort'], $_SESSION['software']['design']['view_themes']['order']) . '</th>
                <th style="text-align: center">' . get_column_heading('Activated for Desktop', $_SESSION['software']['design']['view_themes']['sort'], $_SESSION['software']['design']['view_themes']['order']) . '</th>
                <th style="text-align: center">' . get_column_heading('Activated for Mobile', $_SESSION['software']['design']['view_themes']['sort'], $_SESSION['software']['design']['view_themes']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_themes']['sort'], $_SESSION['software']['design']['view_themes']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form('view_themes');