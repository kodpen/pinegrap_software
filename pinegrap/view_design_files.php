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

$liveform = new liveform('view_design_files');

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['design']['view_design_files']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['design']['view_design_files']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    // store sort in session
    $_SESSION['software']['design']['view_design_files']['order'] = $_REQUEST['order'];
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
    $_SESSION['software']['design']['view_design_files']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['design']['view_design_files']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['design']['view_design_files']['query']) == true) && ($_SESSION['software']['design']['view_design_files']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['design']['view_design_files']['sort']) {
    case 'Name':
        $sort_column = 'name';
        break;
    case 'Folder':
        $sort_column = 'folder_name';
        break;
    case 'Type':
        $sort_column = 'type';
        break;
    case 'Size':
        $sort_column = 'size';
        break;

    case 'Optimized':
        $sort_column = 'optimized';
        break;

    case 'Description':
        $sort_column = 'description';
        break;
    case 'Last Modified':
        $sort_column = 'timestamp';
        break;
    default:
        $sort_column = 'timestamp';
        $_SESSION['software']['design']['view_design_files']['sort'] = 'Last Modified';
        break;
}

if ($_SESSION['software']['design']['view_design_files']['order']) {
    $asc_desc = $_SESSION['software']['design']['view_design_files']['order'];
} elseif ($sort_column == 'timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['design']['view_design_files']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['design']['view_design_files']['order'] = 'asc';
}

// get all stylesheet files
$query =
    "SELECT
        COUNT(files.id)
    FROM files
    WHERE
        (design = '1')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_design_files = $row[0];

$search_query = mb_strtolower($_SESSION['software']['design']['view_design_files']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',', files.name, folder.folder_name, user.user_username)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['design']['view_design_files']['query'])) {
    $where = "AND $sql_search ";
}


// get all files
$query =
    "SELECT
        files.id,
        files.name,
        files.folder,
        folder.folder_name,
        folder.folder_archived,
        files.description,
        files.type,
        files.size,
        files.optimized,
        user.user_username,
        files.timestamp,
        files.design
    FROM files
    LEFT JOIN folder ON files.folder = folder.folder_id
    LEFT JOIN user ON files.user = user.user_id
    WHERE (files.design = '1')" . $where . "
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
        $output_screen_links .= '<a class="submit-secondary" href="view_design_files.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_design_files.php?screen=\' + this.options[this.selectedIndex].value)">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_design_files.php?screen=' . $next . '">&gt;</a>';
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
        $archived_row_style = '';
        
        // if the file is inside of a archived folder, then set the table row's class to archived
        if ($files[$key]['folder_archived'] == '1') {
            $archived_row_style = ' style="font-style: italic;"';
        }

        // Convert file size to a user friendly output.
        $files[$key]['size'] = convert_bytes_to_string($files[$key]['size']);

        $optimized = '';

        if ($files[$key]['optimized']) {
            $optimized = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">';
        }

        // If the file is an image.
        if ((mb_strtolower($files[$key]['type']) == 'bmp') || (mb_strtolower($files[$key]['type']) == 'gif') || (mb_strtolower($files[$key]['type']) == 'jpg') || (mb_strtolower($files[$key]['type']) == 'jpeg') || (mb_strtolower($files[$key]['type']) == 'png') || (mb_strtolower($files[$key]['type']) == 'tif') || (mb_strtolower($files[$key]['type']) == 'tiff')) {

            // Get the dimensions of the image.
            $image_size = @getimagesize(FILE_DIRECTORY_PATH . '/' . $files[$key]['name']);
            $image_width = $image_size[0];
            $image_height = $image_size[1];

            // Output the image dimensions to the table.
            $output_image_dimensions = 'width: ' . $image_width . ' px height: ' . $image_height . ' px';

            // Set the maximum dimension size for the image.
            $max_dimension = 75;

            // Call function to resize image.
            $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, $max_dimension);

            // Output thumnail.
            $output_thumbnail = '<img src="' . OUTPUT_PATH . $files[$key]['name'] . '" width="' . $thumbnail_dimensions['width'] . '" height="' . $thumbnail_dimensions['height'] . '" alt="" title="Image Size:&nbsp;' . $output_image_dimensions . '" />';
        } else {

            $output_thumbnail = '';
            $output_image_dimensions = '';
        }

        $output_link_url = OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_design_file.php?id=' . $files[$key]['id'];

        $output_rows .=
            '<tr' . $archived_row_style . '>
                <td class="selectall"><input type="checkbox" name="files[]" value="' . $files[$key]['id'] . '" class="checkbox" /></td>
                <td style="text-align: center" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_thumbnail . '</td>
                <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">' . h($files[$key]['name']) . '</td>
                <td class="pointer"  onclick="window.location.href=\'' . $output_link_url . '\'">' . h($files[$key]['type']) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($files[$key]['size']) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $optimized . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($files[$key]['folder_name']) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . nl2br(h($files[$key]['description'])) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $files[$key]['timestamp'])) . ' by ' . h($files[$key]['user_username']) . '</td>
            </tr>';
    }
}

print
    output_header() . '
    <div id="subnav">
        ' . get_design_subnav() . '
    </div>
    <div id="button_bar">
        <a href="add_design_file.php">Upload Design Files</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Design Files</h1>
        <div class="subheading">All design files that can be referenced by any theme, page style, or region, and managed only by site designers.</div>
        <form action="view_design_files.php" method="get" class="search_form">
            <input type="text" name="query" value="' . h($_SESSION['software']['design']['view_design_files']['query']) . '" /> <input type="submit" name="submit_search" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_design_files) . ' Total
        </div>
        <form name="form" action="edit_files.php" method="post">
            ' . get_token_field() . '
            <input type="hidden" name="action" />
            <input type="hidden" name="move_to_folder" />
            <input type="hidden" name="edit_design" />
            <input type="hidden" name="optimize">
            <input type="hidden" name="from" value="view_design_files" />
            <input type="hidden" name="send_to" value="' . h(get_request_uri()) . '" />
            <table class="chart">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
                    <th style="text-align: center">Thumbnail</th>
                    <th>' . get_column_heading('Name', $_SESSION['software']['design']['view_design_files']['sort'], $_SESSION['software']['design']['view_design_files']['order']) . '</th>
                    <th>' . get_column_heading('Type', $_SESSION['software']['design']['view_design_files']['sort'], $_SESSION['software']['design']['view_design_files']['order']) . '</th>
                    <th>' . get_column_heading('Size', $_SESSION['software']['design']['view_design_files']['sort'], $_SESSION['software']['design']['view_design_files']['order']) . '</th>
                    <th style="text-align: center">' . get_column_heading('Optimized', $_SESSION['software']['design']['view_design_files']['sort'], $_SESSION['software']['design']['view_design_files']['order']) . '</th>
                    <th>' . get_column_heading('Folder', $_SESSION['software']['design']['view_design_files']['sort'], $_SESSION['software']['design']['view_design_files']['order']) . '</th>
                    <th>' . get_column_heading('Description', $_SESSION['software']['design']['view_design_files']['sort'], $_SESSION['software']['design']['view_design_files']['order']) . '</th>
                    <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_design_files']['sort'], $_SESSION['software']['design']['view_design_files']['order']) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="pagination">
                ' . $output_screen_links . '
            </div>
            <div class="buttons">
                <input type="button" value="Modify Selected" class="submit-secondary" onclick="window.open(\'edit_files.php\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=no,width=550,height=350\'); document.form.action.value = \'edit\';" />&nbsp;&nbsp;&nbsp;<input type="button" value="Delete Selected" class="delete" onclick="edit_files(\'delete\')" />
            </div>
        </form>
    </div>' .
    output_footer();

$liveform->remove_form('view_design_files');