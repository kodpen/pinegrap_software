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

$liveform = new liveform('view_email_campaign_history');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['view_email_campaign_history'][$key] = trim($value);
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

switch ($_SESSION['software']['view_email_campaign_history']['sort']) {
    case 'Profile':
        $sort_column = 'email_campaign_profiles.name';
        break;

    case 'Subject':
        $sort_column = 'email_campaigns.subject';
        break;

    case 'Page to Send':
        $sort_column = 'page.page_name';
        break;

    case 'Scheduled Time':
        $sort_column = 'email_campaigns.start_time';
        break;

    case 'Status':
        $sort_column = 'email_campaigns.status';
        break;

    case 'Purpose':
        $sort_column = 'email_campaigns.purpose';
        break;

    case 'Created':
        $sort_column = 'email_campaigns.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'email_campaigns.last_modified_timestamp';
        break;

    default:
        $sort_column = 'email_campaigns.last_modified_timestamp';
        $_SESSION['software']['view_email_campaign_history']['sort'] = 'Last Modified';
        $_SESSION['software']['view_email_campaign_history']['order'] = 'desc';

        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['view_email_campaign_history']['order']) == false) {
    $_SESSION['software']['view_email_campaign_history']['order'] = 'asc';
}

if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB) {
    $output_start_time_heading = '<th>' . get_column_heading('Scheduled Time', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>';
} else {
    $output_start_time_heading = '';
}

$my_campaigns = 0;
$all_campaigns = 0;

// get all e-mail campaigns
$query =
    "SELECT
        email_campaigns.id,
        email_campaigns.type,
        email_campaign_profiles.name AS email_campaign_profile_name,
        email_campaigns.subject,
        page.page_name,
        email_campaigns.start_time,
        email_campaigns.status,
        email_campaigns.purpose,
        email_campaigns.created_user_id,
        created_user.user_username as created_username,
        email_campaigns.created_timestamp,
        last_modified_user.user_username as last_modified_username,
        email_campaigns.last_modified_timestamp
     FROM email_campaigns
     LEFT JOIN email_campaign_profiles ON email_campaigns.email_campaign_profile_id = email_campaign_profiles.id
     LEFT JOIN page ON email_campaigns.page_id = page.page_id
     LEFT JOIN user as created_user ON email_campaigns.created_user_id = created_user.user_id
     LEFT JOIN user as last_modified_user ON email_campaigns.last_modified_user_id = last_modified_user.user_id
     WHERE
        (email_campaigns.status = 'cancelled')
        OR (email_campaigns.status = 'complete')
     ORDER BY $sort_column " . escape($_SESSION['software']['view_email_campaign_history']['order']);
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$email_campaigns = array();

while ($row = mysqli_fetch_assoc($result)) {
    // Add one to all campaigns
    $all_campaigns++;

    // if user has a role that is greater than user role or if user created e-mail campaign
    if (($user['role'] < 3) || ($row['created_user_id'] == $user['id'])) {
        $email_campaigns[] = $row;

        // Add one to my campaigns
        $my_campaigns++;
    }
}

$output_rows = '';

// if there is at least one result to display
if ($email_campaigns) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($email_campaigns);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_email_campaign_history.php?screen=' . $previous . $keys_and_values . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_email_campaign_history.php?screen=\' + this.options[this.selectedIndex].value) + \'' . $keys_and_values . '\'">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_email_campaign_history.php?screen=' . $next . $keys_and_values . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($email_campaigns) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $output_link_url = 'edit_email_campaign.php?id=' . $email_campaigns[$key]['id'] . '&amp;send_to=' . h(escape_javascript(urlencode(REQUEST_URL)));
        
        // if the e-mail campaign job is enabled, then prepare to show start time cell
        if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB) {
            // if start time was not set, then clear start time
            if ($email_campaigns[$key]['start_time'] == '0000-00-00 00:00:00') {
                $start_time = '';
            
            // else start time was set, so prepare format for start time
            } else {
                $start_time = get_relative_time(array('timestamp' => strtotime($email_campaigns[$key]['start_time'])));
            }
            
            $output_start_time_cell = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $start_time . '</td>';
        }
        
        // get total number of recipients
        $query = "SELECT COUNT(*) FROM email_recipients WHERE email_campaign_id = '" . $email_campaigns[$key]['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $number_of_email_recipients = $row[0];

        // Set the to value differently based on the campaign type.
        switch ($email_campaigns[$key]['type']) {
            case 'manual':
                $plural_suffix = '';

                if (($number_of_email_recipients == 0) or ($number_of_email_recipients > 1)) {
                    $plural_suffix = 's';
                }

                $output_to = number_format($number_of_email_recipients) . ' Contact' . $plural_suffix;

                break;
            
            case 'automatic':
                // Set to value to the single recipient's email address for this automatic campaign.
                $output_to = h(db_value("SELECT email_address FROM email_recipients WHERE email_campaign_id = '" . $email_campaigns[$key]['id'] . "'"));
                break;
        }

        // get total number of complete recipients
        $query = "SELECT COUNT(*) FROM email_recipients WHERE (email_campaign_id = '" . $email_campaigns[$key]['id'] . "') AND (complete = '1')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $number_of_completed_email_recipients = $row[0];

        if ($number_of_email_recipients > 0) {
            $progress_percentage = number_format($number_of_completed_email_recipients / $number_of_email_recipients * 100);
        } else {
            $progress_percentage = '100';
        }

        if ($email_campaigns[$key]['created_username']) {
            $created_username = $email_campaigns[$key]['created_username'];
        } else {
            $created_username = '[Unknown]';
        }

        if ($email_campaigns[$key]['last_modified_username']) {
            $last_modified_username = $email_campaigns[$key]['last_modified_username'];
        } else {
            $last_modified_username = '[Unknown]';
        }

        $output_rows .=
            '<tr>
                <td class="selectall"><input type="checkbox" name="email_campaigns[]" value="' . $email_campaigns[$key]['id'] . '" class="checkbox" /></td>
                <td onclick="window.location.href=\'' . $output_link_url . '\'" class="pointer">' . $output_to . '</td>
                <td onclick="window.location.href=\'' . $output_link_url . '\'" class="pointer">' . h($email_campaigns[$key]['email_campaign_profile_name']) . '</td>
                <td onclick="window.location.href=\'' . $output_link_url . '\'" class="pointer">' . h($email_campaigns[$key]['subject']) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($email_campaigns[$key]['page_name']) . '</td>
                ' . $output_start_time_cell . '
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_email_campaign_status_name($email_campaigns[$key]['status']) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $progress_percentage . '% (' . number_format($number_of_completed_email_recipients) . ' of ' . number_format($number_of_email_recipients) . ')</td>
                <td onclick="window.location.href=\'' . $output_link_url . '\'" class="pointer">' . h(ucwords($email_campaigns[$key]['purpose'])) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $email_campaigns[$key]['created_timestamp'])) . ' by ' . h($created_username) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $email_campaigns[$key]['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>
            </tr>';
    }
}

echo
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
        <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY .'/add_email_campaign.php">Create Campaign</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>My Campaign History</h1>
        <div class="subheading">All completed and cancelled e-mail campaigns that I can manage.</div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($my_campaigns) . ' I can access. ' . number_format($all_campaigns) . ' Total
        </div>
        <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/delete_email_campaigns.php" method="post" class="disable_shortcut">
            ' . get_token_field() . '
            <input type="hidden" name="send_to" value="' . h(REQUEST_URL) . '" />
            <table class="chart" style="margin-bottom: 1em">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
                    <th>To</th>
                    <th>' . get_column_heading('Profile', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>
                    <th>' . get_column_heading('Subject', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>
                    <th>' . get_column_heading('Page to Send', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>
                    ' . $output_start_time_heading . '
                    <th>' . get_column_heading('Status', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>
                    <th>Progress (Subscribers)</th>
                    <th>' . get_column_heading('Purpose', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>
                    <th>' . get_column_heading('Created', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>
                    <th>' . get_column_heading('Last Modified', $_SESSION['software']['view_email_campaign_history']['sort'], $_SESSION['software']['view_email_campaign_history']['order']) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="buttons">
                <input type="submit" value="Delete Selected" class="delete" onclick="return confirm(\'WARNING: The selected campaign(s) will be permanently deleted.\')" />
            </div>
        </form>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();