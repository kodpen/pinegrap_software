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

include_once('liveform.class.php');
$liveform = new liveform('view_short_links');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['view_short_links'][$key] = trim($value);
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

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['view_short_links']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['view_short_links']['query']) == true) && ($_SESSION['software']['view_short_links']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['view_short_links']['sort']) {
    case 'Name':
        $sort_column = 'short_links.name';
        break;
        
    case 'Destination Type':
        $sort_column = 'short_links.destination_type';
        break;

    case 'Created':
        $sort_column = 'short_links.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'short_links.last_modified_timestamp';
        break;

    default:
        $sort_column = 'short_links.last_modified_timestamp';
        $_SESSION['software']['view_short_links']['sort'] = 'Last Modified';
        $_SESSION['software']['view_short_links']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['view_short_links']['order']) == false) {
    $_SESSION['software']['view_short_links']['order'] = 'asc';
}

$all_short_links = 0;

// get the total number of short links
$query = "SELECT COUNT(*) FROM short_links";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_short_links = $row[0];

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['view_short_links']['query']) == true) && ($_SESSION['software']['view_short_links']['query'] != '')) {
    $where .= "WHERE (LOWER(CONCAT_WS(',', short_links.name, short_links.destination_type, page.page_name, product_groups.name, products.name, short_links.url, short_links.tracking_code, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape(mb_strtolower($_SESSION['software']['view_short_links']['query'])) . "%')";
}

// Get all short links.
$query =
    "SELECT
        short_links.id,
        short_links.name,
        short_links.destination_type,
        page.page_name,
        page.page_folder AS folder_id,
        product_groups.address_name AS product_group_address_name,
        products.address_name AS product_address_name,
        short_links.tracking_code,
        short_links.url,
        created_user.user_username AS created_username,
        short_links.created_timestamp,
        last_modified_user.user_username AS last_modified_username,
        short_links.last_modified_timestamp
    FROM short_links
    LEFT JOIN page ON short_links.page_id = page.page_id
    LEFT JOIN product_groups ON short_links.product_group_id = product_groups.id
    LEFT JOIN products ON short_links.product_id = products.id
    LEFT JOIN user AS created_user ON short_links.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON short_links.last_modified_user_id = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . escape($_SESSION['software']['view_short_links']['order']);
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$short_links = mysqli_fetch_items($result);

// If this user has a user role then remove short links that the user does not have access to.
// A user has access to a short link if he/she has edit rights to the short link's page
// or for url type: created the short link.
if (USER_ROLE == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to(USER_ID);

    // Loop through the short links in order to remove short links that user does not have access to.
    foreach ($short_links as $key => $short_link) {
        // Determine if the user has access to the short link differently based on the destination type.
        switch ($short_link['destination_type']) {
            default:
                // If the user does not have edit access to the page's folder, then remove short link.
                if (check_folder_access_in_array($short_link['folder_id'], $folders_that_user_has_access_to) == false) {
                    unset($short_links[$key]);
                }

                break;

            case 'url':
                // If this user is not the user that created the short link, then remove short link.
                if (USER_USERNAME != $short_link['created_username']) {
                    unset($short_links[$key]);
                }

                break;
        }
    }

    // Refresh the indexes of the array so the code further below works.
    $short_links = array_values($short_links);
}

$output_rows = '';

// if there is at least one result to display
if ($short_links) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($short_links);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_short_links.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_short_links.php?screen=\' + this.options[this.selectedIndex].value)">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_short_links.php?screen=' . $next . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($short_links) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $output_destination_type = '';
        $output_destination = '';

        switch ($short_links[$key]['destination_type']) {
            case 'page':
                $output_destination_type = 'Page';

                $output_destination = h(encode_url_path($short_links[$key]['page_name']));

                break;

            case 'product_group':
                $output_destination_type = 'Product Group';

                $output_destination = h(encode_url_path($short_links[$key]['page_name'])) . '/' . h(encode_url_path($short_links[$key]['product_group_address_name']));

                break;

            case 'product':
                $output_destination_type = 'Product';

                $output_destination = h(encode_url_path($short_links[$key]['page_name'])) . '/' . h(encode_url_path($short_links[$key]['product_address_name']));

                break;

            case 'url':
                $output_destination_type = 'URL';

                $output_destination = h($short_links[$key]['url']);

                break;
        }

        // If there is a tracking code and the destination type is a certain type,
        // then add tracking code to destination.
        if (
            ($short_links[$key]['tracking_code'] != '')
            &&
            (
                ($short_links[$key]['destination_type'] == 'page')
                || ($short_links[$key]['destination_type'] == 'product_group')
                || ($short_links[$key]['destination_type'] == 'product')
            )
        ) {
            $output_destination .= '?t=' . h(urlencode($short_links[$key]['tracking_code']));
        }

        $created_username = '';
        
        if ($short_links[$key]['created_username']) {
            $created_username = ' by ' . $short_links[$key]['created_username'];
        }
        
        $last_modified_username = '';
        
        if ($short_links[$key]['last_modified_username']) {
            $last_modified_username = ' by ' . $short_links[$key]['last_modified_username'];
        }

        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'edit_short_link.php?id=' . $short_links[$key]['id'] . '\'">
                <td class="chart_label">' . h($short_links[$key]['name']) . '</td>
                <td>' . $output_destination_type . '</td>
                <td>' . $output_destination . '</td>
                <td>' . get_relative_time(array('timestamp' => $short_links[$key]['created_timestamp'])) . h($created_username) . '</td>
                <td>' . get_relative_time(array('timestamp' => $short_links[$key]['last_modified_timestamp'])) . h($last_modified_username) . '</td>
            </tr>';
    }
}


$output_auto_dialogs = '';

if (AUTO_DIALOGS && (USER_ROLE < 3)) {
    $output_auto_dialogs =
        '<td>
            <ul>
                <li><a href="view_auto_dialogs.php">Auto Dialogs</a></li>
            </ul>
        </td>';
}


print
    output_header() . '
   
    <div id="subnav">
        <table>
            <tbody>
                <tr>   
                   <td>
                        <ul>
                            <li><a href="view_pages.php">My Pages</a></li>
                        </ul>
                    </td>
                    <td>
                        <ul>
                            <li><a href="view_short_links.php">My Short Links</a></li>
                        </ul>
                    </td>
                    ' . $output_auto_dialogs . '
                    <td>
                        <ul>
                            <li><a href="view_comments.php">Comments</a></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <div id="button_bar">
        <a href="add_short_link.php">Create Short Link</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>My Short Links</h1>
        <div class="subheading">All shortcut aliases, that I have access to, for Pages, Product Groups, Products, and URLs.</div>
        <form id="search" action="view_short_links.php" method="get" class="search_form">
            <input type="text" name="query" value="' . h($_SESSION['software']['view_short_links']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_short_links) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['view_short_links']['sort'], $_SESSION['software']['view_short_links']['order']) . '</th>
                <th>' . get_column_heading('Destination Type', $_SESSION['software']['view_short_links']['sort'], $_SESSION['software']['view_short_links']['order']) . '</th>
                <th>Destination</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['view_short_links']['sort'], $_SESSION['software']['view_short_links']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['view_short_links']['sort'], $_SESSION['software']['view_short_links']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();