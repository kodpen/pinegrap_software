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
validate_area_access($user, 'manager');

$number_of_results = 0;

// if mass deletion is allowed, then prepare to output delete logs button
$output_delete_logs_button ='';
if (MASS_DELETION == true) {
	$output_delete_logs_button = '<form action="view_log.php" method="get" name="form"> <input type="submit" name="submit_data" value="Delete Logs" class="delete_small" onclick="return confirm(\'WARNING: All site logs that match the filters will be permanently deleted.  This includes logs from all result pages that might exist.  Please make sure that you perform an update to the filters before you attempt to delete.  An update will allow you to see which logs will be deleted before you actually delete them.  If you would like to continue with the deletion, please click OK.  Otherwise, please click Cancel.\')" /></form>';
}
// Get number of all timestamps.
$query = "SELECT COUNT(log_id) "
        ."FROM log ";
$result = mysqli_query(db::$con, $query) or output_error("Query failed.");
$row = mysqli_fetch_row($result);
$all_logs = $row[0];

// get oldest timestamp
$query = "SELECT MIN(log_timestamp) "
        ."FROM log ";
$result = mysqli_query(db::$con, $query) or output_error("Query failed.");
$row = mysqli_fetch_row($result);
$oldest_timestamp = $row[0];
// if view all was selected
if ($_GET['view'] == 'all') {
    $start_month = date('m', $oldest_timestamp);
    $start_day = date('d', $oldest_timestamp);
    $start_year = date('Y', $oldest_timestamp);
    $stop_month = date('m');
    $stop_day = date('d');
    $stop_year = date('Y');
    $limit = '';

// if mass deletion is allowed and user requested to delete logs, delete logs
} elseif ((MASS_DELETION == true) && ($_GET['submit_data'] == 'Delete Logs')) {

	// get all logs that need to be deleted
	$query = "SELECT log_id as id "
        ."FROM log ";
	$result = mysqli_query(db::$con, $query) or output_error('Query failed');
	$number_of_logs = mysqli_num_rows($result);
    $logs = array();
    // loop through all logs that need to be deleted, so they can be added to array
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }

    // loop through all logs that need to be deleted, so they can be deleted
    foreach ($logs as $log) {
        // delete contact
        $query = "DELETE FROM log WHERE log_id = '" . $log['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    // if at least one log was deleted
    if ($number_of_logs > 0) {
        log_activity(number_format($number_of_logs) . " site log(s) were deleted", $_SESSION['sessionusername']);
		include_once('liveform.class.php');
		$liveform = new liveform('settings');
        $liveform->add_notice(number_format($number_of_logs) . " site log(s) were deleted");
    } else {
        $liveform->mark_error("No site log were deleted");
    }

    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/settings.php');


// if date range has not be completed yet
} elseif (!$_REQUEST['start_month']) {
    // assign default values to start and stop dates
    $start_month = date('m', time() - 2678400);
    $start_day = date('d', time() - 2678400);
    $start_year = date('Y', time() - 2678400);
    $stop_month = date('m');
    $stop_day = date('d');
    $stop_year = date('Y');
    $limit = 'LIMIT 100';
} else {
    $start_month = $_REQUEST['start_month'];
    $start_day = $_REQUEST['start_day'];
    $start_year = $_REQUEST['start_year'];
    $stop_month = $_REQUEST['stop_month'];
    $stop_day = $_REQUEST['stop_day'];
    $stop_year = $_REQUEST['stop_year'];
    $limit = '';
}
// get minimum year from oldest timestamp
$oldest_year = date('Y', $oldest_timestamp);
$current_year = date('Y');
// create html for year options
for ($i = $oldest_year; $i <= $current_year; $i++) {
    $year_options .= '<option value="'.$i.'">'.$i.'</option>';
}
// get timestamps for start and stop dates
$start_timestamp = mktime (0, 0, 0, $start_month, $start_day, $start_year);
$stop_timestamp = mktime (23, 59, 59, $stop_month, $stop_day, $stop_year);

// get query data for URL
$keys_and_values = h("&start_month=$start_month&start_day=$start_day&start_year=$start_year&stop_month=$stop_month&stop_day=$stop_day&stop_year=$stop_year&query=" . $_REQUEST['query']);

switch($_GET['sort']) {
    case 'Time':
        $sort_column = 'log_timestamp';
        break;
    case 'User':
        $sort_column = 'log_user';
        break;
    case 'Description':
        $sort_column = 'log_description';
        break;
    case 'IP Address':
        $sort_column = 'log_ip';
        break;
    default:
        $sort_column = 'log_timestamp';
}
if($_GET['sort']) {
    $asc_desc = $_GET['order'];
} else {
    $asc_desc = 'DESC';
}
if(($_GET['sort'] == 'log_timestamp') && (!$_GET['order'])) {
    $asc_desc = 'ASC';
}

$query = "SELECT log_id, log_description, log_ip, log_user, log_timestamp "
        ."FROM log "
        ."WHERE (log_timestamp >= $start_timestamp) AND (log_timestamp <= $stop_timestamp) AND ((log_description LIKE '%" . escape($_REQUEST['query']) . "%') OR (log_ip LIKE '%" . escape($_REQUEST['query']) . "%') OR (log_user LIKE '%" . escape($_REQUEST['query']) . "%')) "
        ."ORDER BY $sort_column $asc_desc, log_id $asc_desc "
        .$limit;
$result = mysqli_query(db::$con, $query) or output_error('Query failed');
while ($row = mysqli_fetch_array($result)){

    $log_id = $row['log_id'];
    $log_description = $row['log_description'];
    $log_ip = $row['log_ip'];
    $log_user = $row['log_user'];
    
    // if the username is blank, then set to UNKNOWN
    if ($log_user == '') {
        $log_user = 'UNKNOWN';
    }
    
    $log_timestamp = $row['log_timestamp'];
    
    $number_of_results++;
    
    // output style row
    $output_rows .= '
        <tr id="' . h($log_id) . '">
             <td nowrap>'. get_relative_time(array('timestamp' => $log_timestamp)). '</td>
             <td nowrap>' . h($log_user) . '</td>
             <td>' . convert_text_to_html($log_description) . '</td>
             <td nowrap>' . h($log_ip) . '</td>
        </tr>
';
}
$mailchimp_settings = '';

if (ECOMMERCE) {
    $mailchimp_settings = '<td><ul><li><a href="mailchimp_settings.php">MailChimp Settings</a></li></ul></td>';
}
$terminal_output ='';
if(is_file(dirname(__FILE__) . '/../terminal.php')){
    if (USER_ROLE < 1) {
       $terminal_output ='
        <td>    
            <ul>
                <li><a href="../terminal.php"> Terminal</a></li>
            </ul>
        </td>';
    }

    
}
$subnav='   
    <div id="subnav">   
        <table>
            <tbody>
                <tr>   
                   
                    <td>    
                        <ul>
                            <li><a href="settings.php">Site Settings</a></li>
                        </ul>
                    </td> 
                    ' . $mailchimp_settings . '
                    <td>    
                        <ul>
                            <li><a href="smtp_settings.php">SMTP Settings</a></li>
                        </ul>
                    </td>
					<td>    
						<ul>
							<li><a href="backups.php">Backup Manager</a></li>
						</ul>
					</td>
					<td>
						<ul>
						<li><a href="view_log.php" >Site Log</a></li>
						</ul>
					</td>
					'. $terminal_output .'
                </tr>
            </tbody>
        </table>
    </div>';
echo
output_header() . $subnav . '

<div id="content">
<h1>Site Log</h1>
<div class="subheading">Audit all website events and changes by any site visitor or user.</div>

    <a href="#" id="help_link">Help</a>
    <h1></h1>
    <div class="subheading" style="margin-bottom: 1em"></div>
    <form action="view_log.php" method="get" name="form" style="padding: 0 0 .5em;">
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
               
                <td style="padding: 0;">From:&nbsp;<select name="start_month"><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select><select name="start_day"><option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option></select><select name="start_year">'.$year_options.'</select></td>
               
                <td style="padding: 0;">&nbsp;&nbsp;To:&nbsp;<select name="stop_month"><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select><select name="stop_day"><option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option></select><select name="stop_year">'.$year_options.'</select>
                &nbsp; &nbsp; &nbsp; Keyword:&nbsp;
                <input type="text" name="query" value="' . h($_REQUEST['query']) . '" style="width: 200px;">
    			<input type="submit" name="submit_update" value="Update" class="submit_small_secondary"></td>
            </tr>
        </table>
        <input type="hidden" name="sort" value="' . h($_REQUEST['sort']) . '">
        <input type="hidden" name="order" value="' . h($_REQUEST['order']) . '">
    </form>
    <script>
        document.form.start_month.value = "' . escape_javascript($start_month) . '";
        document.form.start_day.value = "' . escape_javascript($start_day) . '";
        document.form.start_year.value = "' . escape_javascript($start_year) . '";
        document.form.stop_month.value = "' . escape_javascript($stop_month) . '";
        document.form.stop_day.value = "' . escape_javascript($stop_day) . '";
        document.form.stop_year.value = "' . escape_javascript($stop_year) . '";
    </script>
    <div class="view_summary">
        <a href="view_log.php?view=all&amp;sort=' . h($_REQUEST['sort']) . '&amp;order=' . h($_REQUEST['order']) .'">View All</a>&nbsp;&nbsp;|&nbsp;&nbsp;Viewing '. number_format($number_of_results) .' of ' . number_format($all_logs) . ' Total &nbsp;&nbsp;&nbsp;&nbsp;'.$output_delete_logs_button.'
    </div>
    <table class="chart">
    <tr>
        <th nowrap>' .asc_or_desc('Time','view_log', $keys_and_values). '</th>
        <th nowrap>' .asc_or_desc('User','view_log', $keys_and_values). '</th>
        <th nowrap>' .asc_or_desc('Description','view_log', $keys_and_values). '</th>
        <th nowrap>' .asc_or_desc('IP Address','view_log', $keys_and_values). '</th>
    </tr>
    ' . $output_rows . '
   </table>
</div>' .
output_footer();