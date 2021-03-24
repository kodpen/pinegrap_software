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
validate_ecommerce_access($user);

include_once('liveform.class.php');
$liveform = new liveform('view_recurring_commission_profiles');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_recurring_commission_profiles'][$key] = trim($value);
    }
}

// if the user clicked the clear button, then clear the query
if (isset($_GET['clear']) == TRUE) {
    $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['query'] = '';
}

$output_clear_button = '';
$sql_search = "";

// if there is a search query, then prepare to output clear button and SQL where condition
if ((isset($_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['query']) == TRUE) && ($_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
    // we had to remove the order number from the concat below because it caused the keyword search to no longer work
    $sql_search = "WHERE (LOWER(CONCAT_WS(',', recurring_commission_profiles.affiliate_code, contacts.affiliate_name)) LIKE '%" . escape(escape_like($_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['query'])) . "%')";
}

// get total number of results for all screens
$query =
    "SELECT COUNT(recurring_commission_profiles.id) as number_of_results
    FROM recurring_commission_profiles
    LEFT JOIN contacts ON recurring_commission_profiles.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON recurring_commission_profiles.order_id = orders.id
    " . $sql_search;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_results = $row['number_of_results'];

// get total number of commissions
$query = "SELECT COUNT(id) as all_recurring_commission_profiles FROM recurring_commission_profiles";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$all_recurring_commission_profiles = $row['all_recurring_commission_profiles'];

// define the maximum number of results
$max = 100;

// get number of screens
$number_of_screens = ceil($number_of_results / $max);

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

// build Previous button if necessary
$previous = $screen - 1;
// if previous screen is greater than zero, output previous link
if ($previous > 0) {
    $output_screen_links .= '<a class="submit-secondary" href="view_recurring_commission_profiles.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
}

// if there are more than one screen
if ($number_of_screens > 1) {
    $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_recurring_commission_profiles.php?screen=\' + this.options[this.selectedIndex].value)">';

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
    $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_recurring_commission_profiles.php?screen=' . $next . '">&gt;</a>';
}

switch ($_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort']) {
    case 'Affiliate Name':
        $sort_column = 'contacts.affiliate_name';
        break;
        
    case 'Affiliate Code':
        $sort_column = 'recurring_commission_profiles.affiliate_code';
        break;
        
    case 'Enabled':
        $sort_column = 'recurring_commission_profiles.enabled';
        break;
    
    case 'Amount':
        $sort_column = 'recurring_commission_profiles.amount';
        break;
        
    case 'Start Date':
        $sort_column = 'recurring_commission_profiles.start_date';
        break;
        
    case 'Frequency':
        $sort_column = 'recurring_commission_profiles.period';
        break;
        
    case 'Commissions':
        $sort_column = 'recurring_commission_profiles.number_of_commissions';
        break;
        
    case 'Product':
        $sort_column = 'recurring_commission_profiles.product_name';
        break;

    case 'Created':
        $sort_column = 'recurring_commission_profiles.created_timestamp';
        break;
        
    case 'Last Modified':
        $sort_column = 'recurring_commission_profiles.last_modified_timestamp';
        break;

    default:
        $sort_column = 'recurring_commission_profiles.last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) == FALSE) {
    $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order'] = 'asc';
}

// prepare limit clause so we only get necessary results that appear on this screen
$start = $screen * $max - $max;
$limit = "LIMIT $start, $max";

// get results for just this screen
$query =
    "SELECT
        recurring_commission_profiles.id,
        recurring_commission_profiles.affiliate_code,
        recurring_commission_profiles.enabled,
        recurring_commission_profiles.amount,
        recurring_commission_profiles.start_date,
        recurring_commission_profiles.period,
        recurring_commission_profiles.number_of_commissions,
        recurring_commission_profiles.product_name,
        recurring_commission_profiles.product_short_description,
        recurring_commission_profiles.created_timestamp,
        recurring_commission_profiles.last_modified_timestamp,
        contacts.affiliate_name,
        orders.order_number,
        created_user.user_username as created_username,
        last_modified_user.user_username as last_modified_username
    FROM recurring_commission_profiles
    LEFT JOIN contacts ON recurring_commission_profiles.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON recurring_commission_profiles.order_id = orders.id
    LEFT JOIN user as created_user ON recurring_commission_profiles.created_user_id = created_user.user_id
    LEFT JOIN user as last_modified_user ON recurring_commission_profiles.last_modified_user_id = last_modified_user.user_id
    " . $sql_search . "
    ORDER BY " . $sort_column . " " . escape($_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . "
    " . $limit;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$recurring_commission_profiles = array();

// loop through results in order to add them to array
while ($row = mysqli_fetch_assoc($result)) {
    $recurring_commission_profiles[] = $row;
}

$output_rows = '';

// loop through all profiles in order to output them
foreach ($recurring_commission_profiles as $recurring_commission_profile) {
    $output_enabled_check_mark = '';
    
    // if this profile is enabled, then output check mark
    if ($recurring_commission_profile['enabled'] == 1) {
        $output_enabled_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    }
    
    // if the number of commissions is 0, then output "Unlimited"
    if ($recurring_commission_profile['number_of_commissions'] == 0) {
        $recurring_commission_profile['number_of_commissions'] = 'Unlimited';
        
    // else the number of commissions is not 0, so format the number
    } else {
        $recurring_commission_profile['number_of_commissions'] = number_format($recurring_commission_profile['number_of_commissions']);
    }
    
    // if there is a short description for the product, then prepend " - " so it appears correctly after the product name
    if ($recurring_commission_profile['product_short_description'] != '') {
        $recurring_commission_profile['product_short_description'] = ' - ' . $recurring_commission_profile['product_short_description'];
    }
    
    // if the created username is known, then prepend " by " so it appears correctly after the timestamp
    if ($recurring_commission_profile['created_username'] != '') {
        $recurring_commission_profile['created_username'] = ' by ' . $recurring_commission_profile['created_username'];
    }
    
    // if the last modified username is known, then prepend " by " so it appears correctly after the timestamp
    if ($recurring_commission_profile['last_modified_username'] != '') {
        $recurring_commission_profile['last_modified_username'] = ' by ' . $recurring_commission_profile['last_modified_username'];
    }
    
    $output_rows .=
        '<tr class="pointer" onclick="window.location.href=\'edit_recurring_commission_profile.php?id=' . $recurring_commission_profile['id'] . '\'">
            <td class="chart_label">' . h($recurring_commission_profile['affiliate_name']) . '</td>
            <td>' . h($recurring_commission_profile['affiliate_code']) . '</td>
            <td style="text-align: center">' . $output_enabled_check_mark . '</td>
            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($recurring_commission_profile['amount'] / 100, 2, '.', ',') . '</td>
            <td>' . prepare_form_data_for_output($recurring_commission_profile['start_date'], 'date') . '</td>
            <td>' . ucwords($recurring_commission_profile['period']) . '</td>
            <td>' . $recurring_commission_profile['number_of_commissions'] . '</td>
            <td>' . $recurring_commission_profile['order_number'] . '</td>
            <td>' . h($recurring_commission_profile['product_name']) . h($recurring_commission_profile['product_short_description']) . '</td>
            <td>' . get_relative_time(array('timestamp' => $recurring_commission_profile['created_timestamp'])) . h($recurring_commission_profile['created_username']) . '</td>
            <td>' . get_relative_time(array('timestamp' => $recurring_commission_profile['last_modified_timestamp'])) . h($recurring_commission_profile['last_modified_username']) . '</td>
        </tr>';
}

print
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Recurring Commission Profiles</h1>
        <div class="subheading" style="margin-bottom: 1em">View all profiles for recurring commissions.</div>
        <form id="search" action="view_recurring_commission_profiles.php" method="get" class="search_form"><input type="text" name="query" value="' . h($_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '</form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_recurring_commission_profiles) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Affiliate Name', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Affiliate Code', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th style="text-align: center">' . get_column_heading('Enabled', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th style="text-align: right">' . get_column_heading('Amount', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Start Date', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Frequency', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Commissions', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Order', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Product', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['sort'], $_SESSION['software']['ecommerce']['view_recurring_commission_profiles']['order']) . '</th>
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