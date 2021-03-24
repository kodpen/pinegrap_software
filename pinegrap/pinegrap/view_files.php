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
validate_area_access($user, 'user');

$liveform = new liveform('view_files');

// If there is a filter set.
if (isset($_GET['filter']) == true) {
    // Send the filter to the search form.
    $filter = $_GET['filter'];
} else {
    $filter = 'default';
}

$filter_for_links = '&filter=' . $filter;
$output_filter_for_links = h($filter_for_links);

// build filters array
$filters_in_array = 
    array(
        'all_my_files'=>'All My Files',
        'all_my_archived_files'=>'All My Archived Files',
        'my_documents'=>'My Documents',
        'my_photos'=>'My Photos',
        'my_media'=>'My Media',
        'my_attachments'=>'My Attachments',
        'my_public_files'=>'My Public Access Files',
        'my_guest_files'=>'My Guest Access Files',
        'my_registration_files'=>'My Registration Access Files',
        'my_member_files'=>'My Member Access Files',
        'my_private_files'=>'My Private Access Files'
    );

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['files']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['files']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    // store sort in session
    $_SESSION['software']['files']['order'] = $_REQUEST['order'];
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

// If there is a query then store it in a session
if (isset($_REQUEST['query']) == true) {
    $_SESSION['software']['view_files']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['view_files']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['view_files']['query']) == true) && ($_SESSION['software']['view_files']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['files']['sort']) {
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
        $_SESSION['software']['files']['sort'] = 'Last Modified';
        break;
}

if ($_SESSION['software']['files']['order']) {
    $asc_desc = $_SESSION['software']['files']['order'];
} elseif ($sort_column == 'timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['files']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['files']['order'] = 'asc';
}

$folders_that_user_has_access_to = array();

// if user is a basic user, then get folders that user has access to
if ($user['role'] == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
}

// Default images on page to be off.
$images_on_page = false;

// if the filter is not all my archived files, then add the sql where statement to prevent getting archived files
if ($filter != 'all_my_archived_files') {
    $where = "(folder.folder_archived = '0')";

// else this is the all my archived files filter, so only get archived files
} else {
    $where = "(folder.folder_archived = '1')";
}

// Switch between the filters.
switch($filter) {
    case 'all_my_archived_files':
        // Change the heading and subheading.
        $heading = 'All My Archived Files';
        $subheading = 'All archived files I can edit &amp; replace.';
        break;
    
    case 'my_documents':
        // Set the query filter.
        $where .= " AND ((files.type != 'jpg') AND (files.type != 'bmp') AND (files.type != 'png') AND (files.type != 'gif') AND (files.type != 'tiff') AND (files.type != 'tif') AND (files.type != 'aiff') AND (files.type != 'au') AND (files.type != 'avi') AND (files.type != 'flv') AND (files.type != 'mpg') AND (files.type != 'mpeg') AND (files.type != 'mov') AND (files.type != 'mid') AND (files.type != 'mp3') AND (files.type != 'rm') AND (files.type != 'ram') AND (files.type != 'snd') AND (files.type != 'swf') AND (files.type != 'wav') AND (files.type != 'wma') AND (files.type != 'wmv'))";

        // Change the heading and subheading.
        $heading = 'My Documents';
        $subheading = 'These files can be securely stored and downloaded from any page.';
        break;

    case 'my_photos':
        // Set the query filter.
        $where .= " AND ((files.type = 'bmp') OR (files.type = 'gif') OR (files.type = 'jpg') OR (files.type = 'jpeg') OR (files.type = 'png') OR (files.type = 'tif') OR (files.type = 'tiff'))";

        // Change the heading and subheading.
        $heading = 'My Photos';
        $subheading = 'These image files can be embedded into any page or viewed in a slideshow using a photo gallery page.';
        
        // Turn images on
        $images_on_page = true;
        break;

    case 'my_media':
        // Set the query filter.
        $where .= " AND ((files.type = 'aiff') OR (files.type = 'au') OR (files.type = 'avi') OR (files.type = 'flv') OR (files.type = 'mpg') OR (files.type = 'mpeg') OR (files.type = 'mov') OR (files.type = 'mid') OR (files.type = 'mp3') OR (files.type = 'rm') OR (files.type = 'ram') OR (files.type = 'snd') OR (files.type = 'swf') OR (files.type = 'wav') OR (files.type = 'wma') OR (files.type = 'wmv'))";

        // Change the heading and subheading.
        $heading = 'My Media';
        $subheading = 'These audio &amp; video files can be embedded into any page or downloaded from any page.';
        break;
        
    case 'my_attachments':
        // Set the query filter.
        $where .= " AND (files.attachment = '1')";

        // Change the heading and subheading.
        $heading = 'My Attachments';
        $subheading = 'These are Files that were uploaded with a Submitted Form or a Comment.';
        break;

    case 'my_public_files':

        // Set the folder access control filter
        $folder_access_control_filter = 'public';

        // Change the heading and subheading.
        $heading = 'My Public Access Files';
        $subheading = 'These files can be downloaded by any website visitor.';
        
        // Turn images on
        $images_on_page = true;
        break;

    case 'my_guest_files':

        // Set the folder access control filter
        $folder_access_control_filter = 'guest';

        // Change the heading and subheading.
        $heading = 'My Guest Access Files';
        $subheading = 'These files can be downloaded by any website visitor after they are offered the option to login or register.';
        // Turn images on
        $images_on_page = true;
        break;

    case 'my_registration_files':

        // Set the folder access control filter
        $folder_access_control_filter = 'registration';

        // Change the heading and subheading.
        $heading = 'My Registration Access Files';
        $subheading = 'These files can be downloaded by any website visitor, but only after they login or register.';
        // Turn images on
        $images_on_page = true;
        break;

    case 'my_member_files':

        // Set the folder access control filter
        $folder_access_control_filter = 'membership';

        // Change the heading and subheading.
        $heading = 'My Member Access Files';
        $subheading = 'These files can be downloaded by any website user, but only after they have either registered with a valid member id, purchased a membership product, completed a membership trial custom form, or logged in as an unexpired member.';
        // Turn images on
        $images_on_page = true;
        break;

    case 'my_private_files':

        // Set the folder access control filter
        $folder_access_control_filter = 'private';

        // Change the heading and subheading.
        $heading = 'My Private Access Files';
        $subheading = 'These files can only be downloaded by website users who have been granted view access to the parent folder, or who have purchased a product that grants private access to the parent folder.';
        // Turn images on
        $images_on_page = true;
        break;

    default:

        // Change the heading and subheading.
        $heading = 'All My Files';
        $subheading = 'All files I can edit &amp; replace.';
        break;

}

$all_files = 0;
$my_files = 0;

// Get file's id and folder number from files.
$query =
    "SELECT
       files.id,
       files.folder
    FROM files
    LEFT JOIN folder ON files.folder = folder.folder_id
    WHERE " . $where;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// Loop through the results
while ($row = mysqli_fetch_assoc($result)) {
    // If the folder access control filter is set.
    if ($folder_access_control_filter) {
        // Compare the filter to the folder's access control type and set the row if they match.
        if (get_access_control_type($row['folder']) == $folder_access_control_filter) {

            // Add one to all files.
            $all_files++;

            // if user has access to file then add one to my files.
            if (check_folder_access_in_array($row['folder'], $folders_that_user_has_access_to) == true) {
                $my_files++;
            }
       }

    // else the access control filter is not set
    } else {
        // Add one to all files.
        $all_files++;

        // if user has access to file then add one to my files.
        if (check_folder_access_in_array($row['folder'], $folders_that_user_has_access_to) == true) {
            $my_files++;
        }
    }
}

// Search query.
$search_query = mb_strtolower($_SESSION['software']['view_files']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',', files.name, folder.folder_name, user.user_username)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['view_files']['query'])) {
    // Get only the results the user wanted in the search.
    $where .= " AND ($sql_search)";
}

// get all files
$query =
    "SELECT
        files.id,
        files.name,
        files.folder,
        folder.folder_name,
        folder.folder_access_control_type,
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
    WHERE " . $where . "
    ORDER BY $sort_column $asc_desc";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_assoc($result)) {

    // if user has access to file then continue
    if (check_folder_access_in_array($row['folder'], $folders_that_user_has_access_to) == true) {

        // If the folder access control filter is set.
        if ($folder_access_control_filter) {

            // Compare the filter to the folder's access control type and set the row if they match.
            if (get_access_control_type($row['folder']) == $folder_access_control_filter) {
                $files[] = $row;
            }
        } else {
            $files[] = $row;
        }
    }
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
        $output_screen_links .= '<a class="submit-secondary" href="view_files.php?screen=' . $previous . $output_filter_for_links . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_files.php?screen=\' + this.options[this.selectedIndex].value) + \'' . h(escape_javascript($filter_for_links)) . '\'">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_files.php?screen=' . $next . $output_filter_for_links . '">&gt;</a>';
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
        $output_check_box = '';
        $output_link = '';
        $output_pointer_class = '';

        // if this is not a design file or if the user has access to design files, then output link
        if (
            ($files[$key]['design'] == 0)
            || ($user['role'] <= 1)
        ) {
            $output_check_box = '<input type="checkbox" name="files[]" value="' . $files[$key]['id'] . '" class="checkbox" />';
            $output_link = ' onclick="window.location.href=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_file.php?id=' . $files[$key]['id'] . '&send_to=' . h(escape_javascript(REQUEST_URL)) . '\'"';
            $output_pointer_class = ' pointer';
        }

        $output_design_class = '';

        // if this is a design file, then output design class
        if ($files[$key]['design'] == 1) {
            $output_design_class = ' design';
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

            // Output the image dimension to the table.
            $output_image_dimensions = 'width: ' . $image_width . ' px height ' . $image_height . ' px';

            // Set the maximum dimension size for the image.
            $max_dimension = 75;

            // Call function to resize image.
            $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, $max_dimension);

            // Output thumnail.
            $output_thumbnail = '<img class="lazy" src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/loading.gif" data-src="' . OUTPUT_PATH . h($files[$key]['name']) . '" width="' . $thumbnail_dimensions['width'] . '" height="' . $thumbnail_dimensions['height'] . '" alt="" title="Image Size: ' . $output_image_dimensions . '" />';
        } else {
            $output_thumbnail = '';
            $output_image_dimensions = '';
        }

        // If there are going to be images on the page then ouput the table rows and headings for them
        if ($images_on_page == true) {
            $output_thumbnail_cell = '<td class="' . $output_pointer_class . $output_design_class . '" style="text-align: center"' . $output_link . '>' . $output_thumbnail . '</td>';
            $output_thumbnail_heading_cell = '<th style="text-align: center">Thumbnail</th> ';
        }

        $output_rows .=
            '<tr>
                <td class="selectall">' . $output_check_box . '</td>
                ' . $output_thumbnail_cell . '
                <td' . $output_link . ' class="chart_label' . $output_pointer_class . $output_design_class . '">' . h($files[$key]['name']) . '</td>
                <td class="' . $output_pointer_class . $output_design_class . '"' . $output_link . '>' . h($files[$key]['type']) . '</td>
                <td class="' . $output_pointer_class . $output_design_class . '"' . $output_link . '>' . h($files[$key]['size']) . '</td>
                <td class="' . $output_pointer_class . $output_design_class . '"' . $output_link . ' style="text-align: center">' . $optimized . '</td>
                <td class="' . $output_pointer_class . $output_design_class . ' ' . $files[$key]['folder_access_control_type'] . '"' . $output_link . '>' . h($files[$key]['folder_name']) . '</td>
                <td class="' . $output_pointer_class . $output_design_class . '"' . $output_link . '>' . nl2br(h($files[$key]['description'])) . '</td>
                <td class="' . $output_pointer_class . $output_design_class . '"' . $output_link . '>' . get_relative_time(array('timestamp' => $files[$key]['timestamp'])) . ' by ' . h($files[$key]['user_username']) . '</td>
            </tr>';
    }
}

echo
    output_header() . '
    <div id="subnav"></div>
    <div id="button_bar">
        <a href="add_file.php?send_to=' . h(urlencode(REQUEST_URL)) . '">Upload Files</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>' . $heading . '</h1>
        <div class="subheading">' . $subheading . '</div>
        <form id="search_form" action="view_files.php" method="get" class="search_form">
            <input type="hidden" name="filter" value="' . h($filter) . '">
            Show: <select name="filter" onchange="submit_form(\'search_form\')">' . get_filter_options($filters_in_array, $filter) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['view_files']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($my_files) . ' I can access. ' . number_format($all_files) . ' Total
        </div>
        <form name="form" action="edit_files.php" method="post">
            ' . get_token_field() . '
            <input type="hidden" name="action" />
            <input type="hidden" name="move_to_folder" />
            <input type="hidden" name="edit_design" />
            <input type="hidden" name="optimize">
            <input type="hidden" name="from" value="view_files" />
            <input type="hidden" name="send_to" value="' . h(REQUEST_URL) . '" />
            <table class="chart">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
                    ' . $output_thumbnail_heading_cell . '
                    <th>' . get_column_heading('Name', $_SESSION['software']['files']['sort'], $_SESSION['software']['files']['order'], $output_filter_for_links) . '</th>
                    <th>' . get_column_heading('Type', $_SESSION['software']['files']['sort'], $_SESSION['software']['files']['order'], $output_filter_for_links) . '</th>
                    <th>' . get_column_heading('Size', $_SESSION['software']['files']['sort'], $_SESSION['software']['files']['order'], $output_filter_for_links) . '</th>
                    <th style="text-align: center">' . get_column_heading('Optimized', $_SESSION['software']['files']['sort'], $_SESSION['software']['files']['order'], $output_filter_for_links) . '</th>
                    <th>' . get_column_heading('Folder', $_SESSION['software']['files']['sort'], $_SESSION['software']['files']['order'], $output_filter_for_links) . '</th>
                    <th>' . get_column_heading('Description', $_SESSION['software']['files']['sort'], $_SESSION['software']['files']['order'], $output_filter_for_links) . '</th>
                    <th>' . get_column_heading('Last Modified', $_SESSION['software']['files']['sort'], $_SESSION['software']['files']['order'], $output_filter_for_links) . '</th>
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
    </div>
    <script>
        $(function () {
            $("img.lazy").Lazy();
        });
    </script>' .
    output_footer();

$liveform->remove_form('view_files');