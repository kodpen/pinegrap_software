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
validate_email_access($user);

include_once('liveform.class.php');
$liveform = new liveform('view_email_campaign_profiles');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['view_email_campaign_profiles'][$key] = trim($value);
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
    $_SESSION['software']['view_email_campaign_profiles']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['view_email_campaign_profiles']['query']) == true) && ($_SESSION['software']['view_email_campaign_profiles']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['view_email_campaign_profiles']['sort']) {
    case 'Name':
        $sort_column = 'email_campaign_profiles.name';
        break;

    case 'Enabled':
        $sort_column = 'email_campaign_profiles.enabled';
        break;
        
    case 'Action':
        $sort_column = 'email_campaign_profiles.action';
        break;
        
    case 'Subject':
        $sort_column = 'email_campaign_profiles.subject';
        break;

    case 'Page to Send':
        $sort_column = 'page.page_name';
        break;

    case 'Purpose':
        $sort_column = 'email_campaign_profiles.purpose';
        break;

    case 'Created':
        $sort_column = 'email_campaign_profiles.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'email_campaign_profiles.last_modified_timestamp';
        break;

    default:
        $sort_column = 'email_campaign_profiles.last_modified_timestamp';
        $_SESSION['software']['view_email_campaign_profiles']['sort'] = 'Last Modified';
        $_SESSION['software']['view_email_campaign_profiles']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['view_email_campaign_profiles']['order']) == false) {
    $_SESSION['software']['view_email_campaign_profiles']['order'] = 'asc';
}

$all_email_campaign_profiles = 0;

// get the total number of email campaign profiles
$query = "SELECT COUNT(*) FROM email_campaign_profiles";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_email_campaign_profiles = $row[0];

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['view_email_campaign_profiles']['query']) == true) && ($_SESSION['software']['view_email_campaign_profiles']['query'] != '')) {
    $where .= "WHERE (LOWER(CONCAT_WS(',', email_campaign_profiles.name, email_campaign_profiles.action, email_campaign_profiles.subject, email_campaign_profiles.purpose, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape(mb_strtolower($_SESSION['software']['view_email_campaign_profiles']['query'])) . "%')";
}

// If user has a user role, then only get profiles that he/she created.
if (USER_ROLE == 3) {
    // If where is blank, then prepare where in a certain way.
    if ($where == '') {
        $where = "WHERE (email_campaign_profiles.created_user_id = '" . USER_ID . "')";

    // Otherwise where is not blank, so prepare where in a different way.
    } else {
        $where .= "AND (email_campaign_profiles.created_user_id = '" . USER_ID . "')";
    }
}

// If user requested to export, then export them.
if (isset($_GET['export'])) {
    header('Content-type: text/csv');
    header('Content-disposition: attachment; filename=campaign_profiles.csv');

    // Output column headings for CSV data.
    echo
        '"name",' .
        '"enabled",' .
        '"action",' .
        '"action_item",' .
        '"action_item_id",' .
        '"subject",' .
        '"format",' .
        '"body",' .
        '"page_name",' .
        '"page_id",' .
        '"from_name",' .
        '"from_email_address",' .
        '"reply_email_address",' .
        '"bcc_email_address",' .
        '"schedule_length",' .
        '"schedule_unit",' .
        '"schedule_period",' .
        '"schedule_base",' .
        '"schedule_time",' .
        '"purpose",' .
        '"created",' .
        '"created_username",' .
        '"last_modified",' .
        '"last_modified_username"' . "\n";

    // Get all campaign profiles.
    $email_campaign_profiles = db_items(
        "SELECT
            email_campaign_profiles.name,
            email_campaign_profiles.enabled,
            email_campaign_profiles.action,
            email_campaign_profiles.action_item_id,
            email_campaign_profiles.subject,
            email_campaign_profiles.format,
            email_campaign_profiles.body,
            email_campaign_profiles.page_id,
            email_campaign_profiles.from_name,
            email_campaign_profiles.from_email_address,
            email_campaign_profiles.reply_email_address,
            email_campaign_profiles.bcc_email_address,
            email_campaign_profiles.schedule_time,
            email_campaign_profiles.schedule_length,
            email_campaign_profiles.schedule_unit,
            email_campaign_profiles.schedule_period,
            email_campaign_profiles.schedule_base,
            email_campaign_profiles.purpose,
            email_campaign_profiles.created_timestamp,
            created_user.user_username AS created_username,
            email_campaign_profiles.last_modified_timestamp,
            last_modified_user.user_username AS last_modified_username
        FROM email_campaign_profiles
        LEFT JOIN page ON email_campaign_profiles.page_id = page.page_id
        LEFT JOIN user AS created_user ON email_campaign_profiles.created_user_id = created_user.user_id
        LEFT JOIN user AS last_modified_user ON email_campaign_profiles.last_modified_user_id = last_modified_user.user_id
        $where
        ORDER BY $sort_column " . e($_SESSION['software']['view_email_campaign_profiles']['order']));

    $date_format_code = get_date_format_code();

    // Loop through the campaign profiles in order to output CSV data.
    foreach ($email_campaign_profiles as $email_campaign_profile) {
        $action_item = '';

        switch ($email_campaign_profile['action']) {
            case 'calendar_event_reserved':
                $action_item = db_value("SELECT name FROM calendar_events WHERE id = '" . $email_campaign_profile['action_item_id'] . "'");
                break;

            case 'custom_form_submitted':
                $action_item = db_value("SELECT page_name FROM page WHERE page_id = '" . $email_campaign_profile['action_item_id'] . "'");
                break;

            case 'email_campaign_sent':
                $action_item = db_value("SELECT name FROM email_campaign_profiles WHERE id = '" . $email_campaign_profile['action_item_id'] . "'");
                break;

            case 'product_ordered':
                $product = db_item(
                    "SELECT
                        name,
                        short_description
                    FROM products
                    WHERE id = '" . $email_campaign_profile['action_item_id'] . "'");

                if ($product['name'] != '') {
                    $action_item .= $product['name'];
                }

                if (($product['short_description'] != '') && ($product['short_description'] != $product['name'])) {
                    if ($action_item != '') {
                        $action_item .= ' - ';
                    }

                    $action_item .= $product['short_description'];
                }

                break;
        }

        $page_name = '';

        // If the format is "html", then prepare values for that.
        if ($email_campaign_profile['format'] == 'html') {
            $email_campaign_profile['body'] = '';

            $page_name = db_value("SELECT page_name FROM page WHERE page_id = '" . $email_campaign_profile['page_id'] . "'");

        // Otherwise the format is "plain_text", so prepare values for that.
        } else {
            $email_campaign_profile['page_id'] = '';
        }

        $schedule_time = '';

        if ($email_campaign_profile['schedule_time'] != '00:00:00') {
            $schedule_time = prepare_form_data_for_output($email_campaign_profile['schedule_time'], 'time');
        }

        echo
            '"' . escape_csv($email_campaign_profile['name']) . '",' .
            '"' . $email_campaign_profile['enabled'] . '",' .
            '"' . $email_campaign_profile['action'] . '",' .
            '"' . escape_csv($action_item) . '",' .
            '"' . $email_campaign_profile['action_item_id'] . '",' .
            '"' . escape_csv($email_campaign_profile['subject']) . '",' .
            '"' . $email_campaign_profile['format'] . '",' .
            '"' . escape_csv($email_campaign_profile['body']) . '",' .
            '"' . escape_csv($page_name) . '",' .
            '"' . $email_campaign_profile['page_id'] . '",' .
            '"' . escape_csv($email_campaign_profile['from_name']) . '",' .
            '"' . escape_csv($email_campaign_profile['from_email_address']) . '",' .
            '"' . escape_csv($email_campaign_profile['reply_email_address']) . '",' .
            '"' . escape_csv($email_campaign_profile['bcc_email_address']) . '",' .
            '"' . $email_campaign_profile['schedule_length'] . '",' .
            '"' . $email_campaign_profile['schedule_unit'] . '",' .
            '"' . $email_campaign_profile['schedule_period'] . '",' .
            '"' . $email_campaign_profile['schedule_base'] . '",' .
            '"' . $schedule_time . '",' .
            '"' . $email_campaign_profile['purpose'] . '",' .
            '"' . date($date_format_code . '/Y g:i:s A T', $email_campaign_profile['created_timestamp']) . '",' .
            '"' . escape_csv($email_campaign_profile['created_username']) . '",' .
            '"' . date($date_format_code . '/Y g:i:s A T', $email_campaign_profile['last_modified_timestamp']) . '",' .
            '"' . escape_csv($email_campaign_profile['last_modified_username']) . '"' . "\n";
    }

    exit;

// Otherwise the user did not select to export, so just list the campaign profiles.
} else {

    // Get all email campaign profiles.
    $query =
        "SELECT
            email_campaign_profiles.id,
            email_campaign_profiles.name,
            email_campaign_profiles.enabled,
            email_campaign_profiles.action,
            email_campaign_profiles.action_item_id,
            email_campaign_profiles.subject,
            email_campaign_profiles.format,
            page.page_name,
            email_campaign_profiles.schedule_time,
            email_campaign_profiles.schedule_length,
            email_campaign_profiles.schedule_unit,
            email_campaign_profiles.schedule_period,
            email_campaign_profiles.schedule_base,
            email_campaign_profiles.purpose,
            created_user.user_username AS created_username,
            email_campaign_profiles.created_timestamp,
            last_modified_user.user_username AS last_modified_username,
            email_campaign_profiles.last_modified_timestamp
        FROM email_campaign_profiles
        LEFT JOIN page ON email_campaign_profiles.page_id = page.page_id
        LEFT JOIN user AS created_user ON email_campaign_profiles.created_user_id = created_user.user_id
        LEFT JOIN user AS last_modified_user ON email_campaign_profiles.last_modified_user_id = last_modified_user.user_id
        $where
        ORDER BY $sort_column " . escape($_SESSION['software']['view_email_campaign_profiles']['order']);
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $email_campaign_profiles = mysqli_fetch_items($result);

    $output_rows = '';

    // if there is at least one result to display
    if ($email_campaign_profiles) {
        // define the maximum number of results to display on one screen
        $max = 100;

        $number_of_results = count($email_campaign_profiles);

        // get number of screens
        $number_of_screens = ceil($number_of_results / $max);

        // build Previous button if necessary
        $previous = $screen - 1;
        // if previous screen is greater than zero, output previous link
        if ($previous > 0) {
            $output_screen_links .= '<a class="submit-secondary" href="view_email_campaign_profiles.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
        }

        // if there are more than one screen
        if ($number_of_screens > 1) {
            $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_email_campaign_profiles.php?screen=\' + this.options[this.selectedIndex].value)">';

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
            $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_email_campaign_profiles.php?screen=' . $next . '">&gt;</a>';
        }

        // determine where result set should start
        $start = $screen * $max - $max;

        // determine where result set should end
        $end = $start + $max - 1;

        // get the value of the last index of the array
        $last_index = count($email_campaign_profiles) - 1;

        // if the end if past the last index of the array, set the end to the last index of the array
        if ($end > $last_index) {
            $end = $last_index;
        }

        for ($key = $start; $key <= $end; $key++) {
            // If this profile is enabled, then use green color for name.
            if ($email_campaign_profiles[$key]['enabled'] == 1) {
                $output_name_color = '#009900';
            
            // Otherwise this profile is disabled, so use red color for name.
            } else {
                $output_name_color = '#ff0000';
            }

            $output_enabled_check_mark = '';

            // If this profile is enabled, then output check mark.
            if ($email_campaign_profiles[$key]['enabled'] == 1) {
                $output_enabled_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
            }

            switch ($email_campaign_profiles[$key]['action']) {
                case 'calendar_event_reserved':
                    $output_action = 'Calendar Event Reserved';
                    $output_action_item = h(db_value("SELECT name FROM calendar_events WHERE id = '" . $email_campaign_profiles[$key]['action_item_id'] . "'"));
                    break;

                case 'custom_form_submitted':
                    $output_action = 'Custom Form Submitted';
                    $output_action_item = h(db_value("SELECT page_name FROM page WHERE page_id = '" . $email_campaign_profiles[$key]['action_item_id'] . "'"));
                    break;

                case 'email_campaign_sent':
                    $output_action = 'Auto Campaign Sent';
                    $output_action_item = h(db_value("SELECT name FROM email_campaign_profiles WHERE id = '" . $email_campaign_profiles[$key]['action_item_id'] . "'"));
                    break;

                case 'order_abandoned':
                    $output_action = 'Order Abandoned';
                    $output_action_item = '';
                    break;

                case 'order_completed':
                    $output_action = 'Order Completed';
                    $output_action_item = '';
                    break;

                case 'order_shipped':
                    $output_action = 'Order Shipped';
                    $output_action_item = '';
                    break;

                case 'product_ordered':
                    $output_action = 'Product Ordered';

                    $product = db_item(
                        "SELECT
                            name,
                            short_description
                        FROM products
                        WHERE id = '" . $email_campaign_profiles[$key]['action_item_id'] . "'");

                    $output_action_item = '';

                    if ($product['name'] != '') {
                        $output_action_item .= h($product['name']);
                    }

                    if (($product['short_description'] != '') && ($product['short_description'] != $product['name'])) {
                        if ($output_action_item != '') {
                            $output_action_item .= ' - ';
                        }

                        $output_action_item .= h($product['short_description']);
                    }

                    break;
            }

            $output_page_name = '';

            // If the format is "html", then prepare page name.
            if ($email_campaign_profiles[$key]['format'] == 'html') {
                $output_page_name = h($email_campaign_profiles[$key]['page_name']);
            }

            $output_schedule = '';

            // If the length is 0, then use "immediately" terminology.
            if ($email_campaign_profiles[$key]['schedule_length'] == 0) {
                $output_schedule .= 'Immediately ';

            // Otherwise the length is greater than 0, so prepare content for that.
            } else {
                $output_schedule .= $email_campaign_profiles[$key]['schedule_length'] . ' ';

                // If the unit is days then prepare to output unit is a certain way.
                if ($email_campaign_profiles[$key]['schedule_unit'] == 'days') {
                    // If the length is 1 the use singular unit.
                    if ($email_campaign_profiles[$key]['schedule_length'] == 1) {
                        $output_schedule .= 'day ';

                    // Otherwise the length is 0 or more than 1, so output plural unit.
                    } else {
                        $output_schedule .= 'days ';
                    }

                // Otherwise the unit is hours, so prepare to output unit in a different way.
                } else {
                    // If the length is 1 the use singular unit.
                    if ($email_campaign_profiles[$key]['schedule_length'] == 1) {
                        $output_schedule .= 'hour ';

                    // Otherwise the length is 0 or more than 1, so output plural unit.
                    } else {
                        $output_schedule .= 'hours ';
                    }
                }
            }

            $output_schedule .= $email_campaign_profiles[$key]['schedule_period'] . ' ';

            // If the base is action, then prepare to output that.
            if ($email_campaign_profiles[$key]['schedule_base'] == 'action') {
                $output_schedule .= 'action';

            // Otherwise the base is calendar event start time, so output that.
            } else {
                $output_schedule .= 'calendar event start time';
            }

            // If there is a time, then add it to schedule.
            if ($email_campaign_profiles[$key]['schedule_time'] != '00:00:00') {
                $output_schedule .= ' (at ' . prepare_form_data_for_output($email_campaign_profiles[$key]['schedule_time'], 'time') . ')';
            }

            $created_username = '';
            
            if ($email_campaign_profiles[$key]['created_username']) {
                $created_username = ' by ' . $email_campaign_profiles[$key]['created_username'];
            }
            
            $last_modified_username = '';
            
            if ($email_campaign_profiles[$key]['last_modified_username']) {
                $last_modified_username = ' by ' . $email_campaign_profiles[$key]['last_modified_username'];
            }

            $output_rows .=
                '<tr class="pointer" onclick="window.location.href=\'edit_email_campaign_profile.php?id=' . $email_campaign_profiles[$key]['id'] . '\'">
                    <td class="chart_label" style="color: ' . $output_name_color . '">' . h($email_campaign_profiles[$key]['name']) . '</td>
                    <td style="text-align: center">' . $output_enabled_check_mark . '</td>
                    <td>' . $output_action . '</td>
                    <td>' . $output_action_item . '</td>
                    <td>' . h($email_campaign_profiles[$key]['subject']) . '</td>
                    <td>' . $output_page_name . '</td>
                    <td>' . $output_schedule . '</td>
                    <td>' . h(ucwords($email_campaign_profiles[$key]['purpose'])) . '</td>
                    <td>' . get_relative_time(array('timestamp' => $email_campaign_profiles[$key]['created_timestamp'])) . h($created_username) . '</td>
                    <td>' . get_relative_time(array('timestamp' => $email_campaign_profiles[$key]['last_modified_timestamp'])) . h($last_modified_username) . '</td>
                </tr>';
        }
    }

    print
        output_header() . '
        <div id="subnav">
            <table>
                <tr>
                    <td>
                        <ul>
                            <li><a href="view_email_campaigns.php">My Campaigns</a></li>
                        </ul>
                    </td>
                    <td>
                        <ul>
                            <li><a href="view_email_campaign_history.php">My Campaign History</a></li>
                        </ul>
                    </td>
                    <td>
                        <ul>
                            <li><a href="view_email_campaign_profiles.php">My Campaign Profiles</a></li>
                        </ul>
                    </td>
                </tr>
            </table>
        </div>
        <div id="button_bar">
            <a href="add_email_campaign_profile.php">Create Campaign Profile</a>
            <a href="import_email_campaign_profiles.php">Import Campaign Profiles</a>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>My Campaign Profiles</h1>
            <div class="subheading">Use Campaign Profiles to schedule e-mails automatically for certain actions (e.g. Calendar Event reservation).</div>
            <form id="search" action="view_email_campaign_profiles.php" method="get" class="search_form">
                <input type="text" name="query" value="' . h($_SESSION['software']['view_email_campaign_profiles']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
            </form>
            <div class="view_summary">
                Viewing '. number_format($number_of_results) .' of ' . number_format($all_email_campaign_profiles) . ' Total&nbsp;&nbsp;&nbsp;&nbsp;
                <form method="get" style="margin: 0; display: inline">
                    <input type="submit" name="export" value="Export" class="submit_small_secondary">
                </form>
            </div>
            <table class="chart">
                <tr>
                    <th>' . get_column_heading('Name', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                    <th style="text-align: center;">' . get_column_heading('Enabled', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                    <th>' . get_column_heading('Action', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                    <th>Action Item</th>
                    <th>' . get_column_heading('Subject', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                    <th>' . get_column_heading('Page to Send', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                    <th>Schedule</th>
                    <th>' . get_column_heading('Purpose', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                    <th>' . get_column_heading('Created', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                    <th>' . get_column_heading('Last Modified', $_SESSION['software']['view_email_campaign_profiles']['sort'], $_SESSION['software']['view_email_campaign_profiles']['order']) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="pagination">
                ' . $output_screen_links . '
            </div>
        </div>' .
        output_footer();

    $liveform->remove_form();
}
?>