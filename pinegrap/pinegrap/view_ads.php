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

// if user has a user role and if they do not have access to edit any ad regions, output error
if (($user['role'] == 3) && (count(get_items_user_can_edit('ad_regions', $user['id'])) == 0)) {
    log_activity("access denied because user does not have access to ads", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('view_ads');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ads']['view_ads'][$key] = trim($value);
    }
}

// if ad region is not set yet, set default to [All]
if (isset($_SESSION['software']['ads']['view_ads']['ad_region_id']) == false) {
    $_SESSION['software']['ads']['view_ads']['ad_region_id'] = '[All]';
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

$sql_join = '';
$where = '';

// if user is a user role, then prepare sql join and where
if ($user['role'] == 3) {
    $sql_join = 'LEFT JOIN users_ad_regions_xref ON ad_regions.id = users_ad_regions_xref.ad_region_id';
    $where = "WHERE users_ad_regions_xref.user_id = '" . escape($user['id']) . "'";
}

// get all ad regions in order to prepare options for ad region pick list
$query = 
    "SELECT
        ad_regions.id,
        ad_regions.name
    FROM ad_regions
    $sql_join
    $where
    ORDER BY name ASC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$ad_regions = array();

// loop through all ad regions in order to add ad regions to array
while ($row = mysqli_fetch_assoc($result)){
    $ad_regions[] = $row;
}

$output_ad_region_options = '';

// loop through all ad regions in order to prepare options for ad region pick list
foreach ($ad_regions as $ad_region) {
    // if this ad region is equal to the selected ad region
    if ($ad_region['id'] == $_SESSION['software']['ads']['view_ads']['ad_region_id']) {
        $selected = ' selected="selected"';
    } else {
        $selected = '';
    }
    
    // get the number of ads that are assigned to this ad region
    $query = "SELECT COUNT(*) FROM ads WHERE ad_region_id = '" . $ad_region['id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_ads = $row[0];
    
    $output_ad_region_options .= '<option value="' . $ad_region['id'] . '"' . $selected . '>' . h($ad_region['name']) . ' (' . number_format($number_of_ads) . ')</option>';
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['ads']['view_ads']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['ads']['view_ads']['query']) == true) && ($_SESSION['software']['ads']['view_ads']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['ads']['view_ads']['sort']) {
    case 'Name':
        $sort_column = 'ads.name';
        break;
        
    case 'Ad Region':
        $sort_column = 'ad_regions.name';
        break;
        
    case 'Display Type':
        $sort_column = 'ad_regions.display_type';
        break;

    case 'Created':
        $sort_column = 'ads.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'ads.last_modified_timestamp';
        break;

    default:
        $sort_column = 'ads.last_modified_timestamp';
        $_SESSION['software']['ads']['view_ads']['sort'] = 'Last Modified';
        $_SESSION['software']['ads']['view_ads']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ads']['view_ads']['order']) == false) {
    $_SESSION['software']['ads']['view_ads']['order'] = 'asc';
}

$all_ads = 0;

$sql_join = '';
$where = '';

// if user is a user role, then prepare sql join and where
if ($user['role'] == 3) {
    $sql_join = 
        'LEFT JOIN ad_regions ON ads.ad_region_id = ad_regions.id
        LEFT JOIN users_ad_regions_xref ON ad_regions.id = users_ad_regions_xref.ad_region_id';
    
    $where = "WHERE users_ad_regions_xref.user_id = '" . escape($user['id']) . "'";
}

// get the total number of ads
$query = 
    "SELECT
        COUNT(*)
    FROM ads
    $sql_join
    $where";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_ads = $row[0];

$sql_join = '';
$where = "";

// if user is a user role, then prepare sql join and where
if ($user['role'] == 3) {
    $sql_join = 'LEFT JOIN users_ad_regions_xref ON ad_regions.id = users_ad_regions_xref.ad_region_id';
    
    // if this is the first where clause, then add where first
    if ($where == '') {
        $where .= "WHERE ";
        
    // else this is not the first where clause, so add and
    } else {
        $where .= "AND ";
    }
    
    $where .= "users_ad_regions_xref.user_id = '" . escape($user['id']) . "'";
}

// if user has not choosen [All] filter for ad region pick list, then prepare where clause
if ($_SESSION['software']['ads']['view_ads']['ad_region_id'] != '[All]') {
    // if this is the first where clause, then add where first
    if ($where == '') {
        $where .= "WHERE ";
        
    // else this is not the first where clause, so add and
    } else {
        $where .= "AND ";
    }
    
    $where .= "(ads.ad_region_id = '" . escape($_SESSION['software']['ads']['view_ads']['ad_region_id']) . "') ";
}

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ads']['view_ads']['query']) == true) && ($_SESSION['software']['ads']['view_ads']['query'] != '')) {
    // if this is the first where clause, then add where first
    if ($where == '') {
        $where .= "WHERE ";
        
    // else this is not the first where clause, so add and
    } else {
        $where .= "AND ";
    }
    
    $where .= "((LOWER(CONCAT_WS(',', ads.name, ad_regions.name, ads.label, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape(mb_strtolower($_SESSION['software']['ads']['view_ads']['query'])) . "%')) ";
}

// get all ads
$query =
    "SELECT
        ads.id,
        ads.name as ad_name,
        ad_regions.name as ad_region_name,
        ad_regions.display_type as ad_region_display_type,
        ads.label as label,
        ads.sort_order as sort_order,
        created_user.user_username as created_username,
        ads.created_timestamp,
        last_modified_user.user_username as last_modified_username,
        ads.last_modified_timestamp
    FROM ads
    LEFT JOIN user AS created_user ON ads.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON ads.last_modified_user_id = last_modified_user.user_id
    LEFT JOIN ad_regions ON ads.ad_region_id = ad_regions.id
    $sql_join
    $where
    ORDER BY $sort_column " . escape($_SESSION['software']['ads']['view_ads']['order']);

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$ads = array();

while ($row = mysqli_fetch_assoc($result)) {
    $ads[] = $row;
}

$output_rows = '';

// if there is at least one result to display
if ($ads) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($ads);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_ads.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_ads.php?screen=\' + this.options[this.selectedIndex].value)">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_ads.php?screen=' . $next . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($ads) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        if ($ads[$key]['created_username']) {
            $created_username = $ads[$key]['created_username'];
        } else {
            $created_username = '[Unknown]';
        }

        if ($ads[$key]['last_modified_username']) {
            $last_modified_username = $ads[$key]['last_modified_username'];
        } else {
            $last_modified_username = '[Unknown]';
        }
        
        // if the ad region display type is static then prepare output value
        if ($ads[$key]['ad_region_display_type'] == 'static') {
            $output_ad_region_display_type = 'Static';
            
        // else the ad region display type is dynamic, so prepare output value
        } else {
            $output_ad_region_display_type = 'Dynamic';
        }
        
        $output_label = '';
        $output_sort_order = '';
        
        // if the ad region display type is dynamic, then prepare to output label and sort order
        if ($ads[$key]['ad_region_display_type'] == 'dynamic') {
            $output_label = h($ads[$key]['label']);
            
            // if the sort order is not equal to 0, then set value
            if ($ads[$key]['sort_order'] != 0) {
                $output_sort_order = number_format($ads[$key]['sort_order']);
            }
        }
        
        $output_link_url = 'edit_ad.php?id=' . $ads[$key]['id'];

        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                <td class="chart_label">' . h($ads[$key]['ad_name']) . '</td>
                <td class="chart_label">' . h($ads[$key]['ad_region_name']) . '</td>
                <td>' . $output_ad_region_display_type . '</td>
                <td>' . $output_label . '</td>
                <td>' . $output_sort_order . '</td>
                <td>' . get_relative_time(array('timestamp' => $ads[$key]['created_timestamp'])) . ' by ' . h($created_username) . '</td>
                <td>' . get_relative_time(array('timestamp' => $ads[$key]['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>
            </tr>';
    }
}

print
    output_header() . '
    <div id="subnav"></div>
    <div id="button_bar">
        <a href="add_ad.php">Create Ad</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All My Ads</h1>
        <div class="subheading">All shared content that can be rotated on one or more pages that I can edit.</div>
        <form id="search" action="view_ads.php" method="get" class="search_form">
            Ad Region: <select name="ad_region_id" onchange="submit_form(\'search\')"><option value="[All]">[All] (' . number_format($all_ads) . ')</option>' . $output_ad_region_options . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['ads']['view_ads']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_ads) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['ads']['view_ads']['sort'], $_SESSION['software']['ads']['view_ads']['order']) . '</th>
                <th>' . get_column_heading('Ad Region', $_SESSION['software']['ads']['view_ads']['sort'], $_SESSION['software']['ads']['view_ads']['order']) . '</th>
                <th>' . get_column_heading('Display Type', $_SESSION['software']['ads']['view_ads']['sort'], $_SESSION['software']['ads']['view_ads']['order']) . '</th>
                <th>Label</th>
                <th>Sort Order</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['ads']['view_ads']['sort'], $_SESSION['software']['ads']['view_ads']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['ads']['view_ads']['sort'], $_SESSION['software']['ads']['view_ads']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();
?>