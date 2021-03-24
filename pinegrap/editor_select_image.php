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
        $_SESSION['software']['editor_select_image'][$key] = trim($value);
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

// If sort is not set, set to "newest".
if (isset($_SESSION['software']['editor_select_image']['sort']) == false) {
    $_SESSION['software']['editor_select_image']['sort'] = 'newest';
}

// If folder is not set yet, set to "all".
if (isset($_SESSION['software']['editor_select_image']['folder_id']) == false) {
    $_SESSION['software']['editor_select_image']['folder_id'] = 'all';
}

// If access control type is not set yet, set to "all".
if (isset($_SESSION['software']['editor_select_image']['access_control_type']) == false) {
    $_SESSION['software']['editor_select_image']['access_control_type'] = 'all';
}

$output_sort_newest_style = '';
$output_sort_oldest_style = '';
$output_sort_alphabetical_style = '';

switch ($_SESSION['software']['editor_select_image']['sort']) {
    default:
    case 'newest':
        $output_sort_newest_style = ' style="font-weight: bold"';
        break;

    case 'oldest':
        $output_sort_oldest_style = ' style="font-weight: bold"';
        break;

    case 'alphabetical':
        $output_sort_alphabetical_style = ' style="font-weight: bold"';
        break;
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['editor_select_image']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['editor_select_image']['query']) == true) && ($_SESSION['software']['editor_select_image']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true&CKEditorFuncNum=' . h(escape_javascript(urlencode($_GET['CKEditorFuncNum']))) . '\'" class="submit_small_secondary" />';
}

$folders_that_user_has_access_to = array();

if (USER_ROLE == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to(USER_ID);
}

switch ($_SESSION['software']['editor_select_image']['sort']) {
    default:
    case 'newest':
        $order_by =
            "files.timestamp DESC,
            files.name ASC";
        break;

    case 'oldest':
        $order_by =
            "files.timestamp ASC,
            files.name ASC";
        break;

    case 'alphabetical':
        $order_by = "files.name ASC";
        break;
}

$where = "";

// If there is a search query and it is not blank, then prepare filter.
if ((isset($_SESSION['software']['editor_select_image']['query']) == true) && ($_SESSION['software']['editor_select_image']['query'] != '')) {
    $where .= "AND (LOWER(CONCAT_WS(',', files.name, folder.folder_name, user.user_username)) LIKE '%" . escape(escape_like(mb_strtolower($_SESSION['software']['editor_select_image']['query']))) . "%')";
}

// Get all images.
$all_images = db_items(
    "SELECT
        files.name,
        files.size,
        files.folder AS folder_id,
        folder.folder_name,
        files.timestamp AS last_modified_timestamp,
        user.user_username AS last_modified_username
    FROM files
    LEFT JOIN folder ON files.folder = folder.folder_id
    LEFT JOIN user ON files.user = user.user_id
    WHERE
        (
            (files.type = 'gif')
            || (files.type = 'jpg')
            || (files.type = 'jpeg')
            || (files.type = 'png')
            || (files.type = 'tif')
            || (files.type = 'tiff')
        )
        AND (files.design = '0')
        AND (files.attachment = '0')
        AND (folder.folder_archived = '0')
        $where
    ORDER BY $order_by");

// If a folder was selected, then store that folder and all child folders
// in an array so that we can later determine if images are in the selected folder scope.
if ($_SESSION['software']['editor_select_image']['folder_id'] != 'all') {
    $folders = array();

    // Start the folders off with the selected folder.
    $folders[] = $_SESSION['software']['editor_select_image']['folder_id'];

    // Get all folders in order to add child folders to array.
    $all_folders = db_items(
        "SELECT
            folder_id AS id,
            folder_parent AS parent_folder_id
        FROM folder");

    // Get child folders under the selected folder.
    $child_folders = get_child_folders($_SESSION['software']['editor_select_image']['folder_id'], $all_folders);

    // Add child folders to array.
    $folders = array_merge($folders, $child_folders);
}

// Create an array that we will use to store images that user has access to.
$images = array();

// Loop through all images in order to determine which to include in results.
foreach ($all_images as $image) {
    // If this user has edit access to this image's folder,
    // and this image is within the scope of the selected folder,
    // then continue to determine if this image should be included in results.
    if (
        (
            (USER_ROLE < 3)
            || (check_folder_access_in_array($image['folder_id'], $folders_that_user_has_access_to) == true)
        )
        &&
        (
            ($_SESSION['software']['editor_select_image']['folder_id'] == 'all')
            || (in_array($image['folder_id'], $folders) == true)
        )
    ) {
        // If an access control type has been selected, then get access control type for image,
        // in order to determine if image should be included in results.
        if ($_SESSION['software']['editor_select_image']['access_control_type'] != 'all') {
            $image['access_control_type'] = get_access_control_type($image['folder_id']);

            // If the access control type for this image is the same as the selected access
            // control type, then include image in results.
            if ($image['access_control_type'] == $_SESSION['software']['editor_select_image']['access_control_type']) {
                $images[] = $image;
            }

        // Otherwise an access control type has not been selected,
        // so include image in results.
        } else {
            $images[] = $image;
        }
    }
}

$output_images = '';

// If there is at least one result to display.
if ($images) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($images);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="editor_select_image.php?screen=' . $previous . '&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'editor_select_image.php?screen=\' + this.options[this.selectedIndex].value + \'&CKEditorFuncNum=' . h(escape_javascript(urlencode($_GET['CKEditorFuncNum']))) . '\')">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="editor_select_image.php?screen=' . $next . '&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($images) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        // If we did not get the access control type already up above for this image, then get it now.
        if (isset($images[$key]['access_control_type']) == false) {
            $images[$key]['access_control_type'] = get_access_control_type($images[$key]['folder_id']);
        }

        $image_size = @getimagesize(FILE_DIRECTORY_PATH . '/' . $images[$key]['name']);
        $image_width = $image_size[0];
        $image_height = $image_size[1];

        $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, 100);

        $output_last_modified_username = '';
        
        if ($images[$key]['last_modified_username'] != '') {
            $output_last_modified_username = ' by ' . h($images[$key]['last_modified_username']);
        }

        $output_images .=
            '<div class="image ' . h($images[$key]['access_control_type']) . '" onclick="window.opener.CKEDITOR.tools.callFunction(\'' . h(escape_javascript($_GET['CKEditorFuncNum'])) . '\', \'' . OUTPUT_PATH . h(escape_javascript(encode_url_path($images[$key]['name']))) . '\'); window.close();" title="Size: ' . h(convert_bytes_to_string($images[$key]['size'])) . "\n" . 'Dimensions: ' . h($image_width) . ' x ' . h($image_height) . "\n" . 'Folder: ' . h($images[$key]['folder_name']) . "\n" . 'Access: ' . h(get_access_control_type_name($images[$key]['access_control_type'])) . "\n" . 'Last Modified: ' . get_relative_time(array('timestamp' => $images[$key]['last_modified_timestamp'], 'format' => 'plain_text')) . $output_last_modified_username . '">
                <div class="thumbnail"><img src="' . OUTPUT_PATH . h($images[$key]['name']) . '" width="' . $thumbnail_dimensions['width'] . '" height="' . $thumbnail_dimensions['height'] . '" alt="" /></div>
                <div class="image_content">
                    <strong>' . h($images[$key]['name']) . '</strong><br/>
                    Size: ' . h(convert_bytes_to_string($images[$key]['size'])) . "\n" . '<br/>
                    ' . h($image_width) . ' x ' . h($image_height) . "\n" .'
                </div>
            </div>';
    }

// Otherwise there are no results, so output message.
} else {
    $output_images = '<p>Sorry, we could not find any images.</p>';
}

echo
    '<!DOCTYPE html>
    <html lang="'.language_ruler().'">
        <head>
            <meta charset="utf-8">
            <title>Browse Images</title>
            ' . get_generator_meta_tag() . '
            ' . output_control_panel_header_includes() . '
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        </head>
        <body class="select_image" style="overflow:auto;">
        <div id="filters">
            <form class=" " id="search" action="editor_select_image.php" method="get" >
                <input type="hidden" name="CKEditorFuncNum" value="' . h($_GET['CKEditorFuncNum']) . '" />
                <div style="float:left;width: 100%;text-align: left;padding: 1rem;">Folder:
                <select name="folder_id" onchange="submit_form(\'search\')"><option value="all">[All]</option>' . select_folder($_SESSION['software']['editor_select_image']['folder_id']) . '</select>
                </div>
                <div style="float:left;width: 100%;text-align: left;padding: 1rem;}">
                Access:
                <select name="access_control_type" class="' . h($_SESSION['software']['editor_select_image']['access_control_type']) . '" onchange="submit_form(\'search\')"><option value="all" class="all">[All]</option>' . select_access_control_type($_SESSION['software']['editor_select_image']['access_control_type'], false) . '</select>
                </div>
                <div style="float:left;width: 100%;text-align: left;padding: 1rem;">
                <input type="text" name="query" value="' . h($_SESSION['software']['editor_select_image']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
                </div>
            </form>
        </div>

        <div class="navigation fixed">
            <div class="title" title="Select the image that you want to embed in your content.  Hover over an image to see more info.">Browse Images
            <a href="editor_select_image.php?sort=newest&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '"' . $output_sort_newest_style . '>Newest</a> | <a href="editor_select_image.php?sort=oldest&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '"' . $output_sort_oldest_style . '>Oldest</a> | <a href="editor_select_image.php?sort=alphabetical&CKEditorFuncNum=' . h(urlencode($_GET['CKEditorFuncNum'])) . '"' . $output_sort_alphabetical_style . '>Alphabetical</a>
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
            minWidth: 200,
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
                <div class="images">
                    ' . $output_images . '
                </div>
                <div class="pagination">
                    ' . $output_screen_links . '
                </div>
            </div>
        </body>
    </html>';