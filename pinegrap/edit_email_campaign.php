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

// if user has a user role
if ($user['role'] == 3) {
    // get user that created this e-mail campaign, in order to check if user has access to this e-mail campaign
    $query =
        "SELECT created_user_id
        FROM email_campaigns
        WHERE id = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $created_user_id = $row['created_user_id'];
    
    // if user did not create this e-mail campaign, then output error
    if ($created_user_id != $user['id']) {
        log_activity("access denied to edit or view campaign because user is not the creator of the campaign", $_SESSION['sessionusername']);
        output_error('Access denied.');
    }
}

// if form has not been submitted yet
if (!$_POST) {
    // get e-mail campaign information
    $query = 
        "SELECT 
            email_campaigns.type,
            email_campaigns.subject,
            email_campaigns.format,
            email_campaigns.body,
            email_campaigns.status,
            email_campaigns.email_campaign_profile_id,
            email_campaign_profiles.name AS email_campaign_profile_name,
            email_campaigns.order_id,
            orders.reference_code AS order_reference_code,
            email_campaigns.from_name, 
            email_campaigns.from_email_address, 
            email_campaigns.reply_email_address, 
            email_campaigns.bcc_email_address, 
            email_campaigns.start_time,
            email_campaigns.purpose,
            email_campaigns.created_timestamp, 
            email_campaigns.page_id,
            user.user_username
        FROM email_campaigns
        LEFT JOIN email_campaign_profiles ON email_campaigns.email_campaign_profile_id = email_campaign_profiles.id
        LEFT JOIN orders ON email_campaigns.order_id = orders.id
        LEFT JOIN user ON user.user_id = email_campaigns.created_user_id
        WHERE email_campaigns.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $type = $row['type'];
    $subject = $row['subject'];
    $format = $row['format'];
    $body = $row['body'];
    $status = $row['status'];
    $email_campaign_profile_id = $row['email_campaign_profile_id'];
    $email_campaign_profile_name = $row['email_campaign_profile_name'];
    $order_id = $row['order_id'];
    $order_reference_code = $row['order_reference_code'];
    $creator_username = $row['user_username'];
    $from_name = $row['from_name'];
    $from_email_address = $row['from_email_address'];
    $reply_email_address = $row['reply_email_address'];
    $bcc_email_address = $row['bcc_email_address'];
    $start_time = $row['start_time'];
    $purpose = $row['purpose'];
    $created_timestamp = $row['created_timestamp'];
    $page_id = $row['page_id'];
    
    // if the creator username is blank then set to placeholder
    if ($creator_username == '') {
        $creator_username = '[Unknown]';
    }
    
    // if the start time is set to 0's, then set to blank
    if ($start_time == '0000-00-00 00:00:00') {
        $start_time = '';
    }
    
    $output_button_bar = '';
    
    // if e-mail campaign job is off and e-mail campaign status is ready, then prepare to output button bar with send campaign button
    if (((defined('EMAIL_CAMPAIGN_JOB') == false) || (EMAIL_CAMPAIGN_JOB == false)) && ($status == 'ready')) {
        $output_button_bar =
            '<div id="button_bar">
                <a href="send_email_campaign.php?id=' . h($_GET['id']) . get_token_query_string_field() . '" onclick="window.open(\'send_email_campaign.php?id=' . h($_GET['id']) . get_token_query_string_field() . '\', \'\', \'width=450, height=350, resizable=1, scrollbars=0\'); return false;" class="button">Send Campaign</a>
            </div>';
    }
    
    // if the e-mail campaign is not complete, then prepare information for editing
    if ($status != 'complete') {
        $output_heading = 'Edit Campaign';
        $output_subheading = 'Update this e-mail campaign\'s properties.';
        
        $output_form_start =
            '<form name="form" action="edit_email_campaign.php" method="post" style="margin: 0">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">';
        
        switch ($status) {
            case 'ready':
                $ready_status = ' selected="selected"';
                break;

            case 'paused':
                $paused_status = ' selected="selected"';
                break;

            case 'cancelled':
                $cancelled_status = ' selected="selected"';
                break;
        }
        
        if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB === true) {
            $ready_label = 'Scheduled';
        } else {
            $ready_label = 'Ready to Send';
        }
        
        $output_status_options = '<option value="ready"' . $ready_status . '>' . $ready_label . '</option>';
        
        $output_status_options .= '<option value="paused"' . $paused_status . '>Paused</option>';
        
        $output_status_options .= '<option value="cancelled"' . $cancelled_status . '>Cancelled</option>';
        
        $output_status = '<select name="status">' .  $output_status_options . '</select>';
        
        $output_subject = '<input type="text" name="subject" size="80" maxlength="255" value="' . h($subject) . '" />';
        $output_bcc_email_address = '<input type="text" name="bcc_email_address" size="40" value="' . h($bcc_email_address) . '" maxlength="100" />';
        $output_from_name = '<input type="text" name="from_name" size="40" value="' . h($from_name) . '" maxlength="100" />';
        $output_from_email_address = '<input type="text" name="from_email_address" size="40" value="' . h($from_email_address) . '" maxlength="100" />';
        $output_reply_email_address = '<input type="text" name="reply_email_address" size="40" value="' . h($reply_email_address) . '" maxlength="100" />';
        $output_start_time =
            '<input type="text" id="start_time" name="start_time" size="20" maxlength="19" value="' . prepare_form_data_for_output($start_time, 'date and time') . '" /> (Leave blank to send as soon as possible.)
            ' . get_date_picker_format() . '
            <script>
                $("#start_time").datetimepicker({
                    dateFormat: date_picker_format,
                    timeFormat: "h:mm TT"
                });
            </script>';

        if ($purpose == 'commercial') {
            $purpose_commercial_checked = 'checked="checked"';
            $purpose_transactional_checked = '';
        } else {
            $purpose_commercial_checked = '';
            $purpose_transactional_checked = 'checked="checked"';
        }

        $output_purpose =
            '<div>
                <input
                    type="radio"
                    id="purpose_commercial"
                    name="purpose"
                    value="commercial"
                    class="radio"
                    ' . $purpose_commercial_checked . '><label for="purpose_commercial"> Commercial &nbsp;(send email to opted-in contacts only. Example: "We have an offer for you")</label>
            </div>

            <div>
                <input
                    type="radio"
                    id="purpose_transactional"
                    name="purpose"
                    value="transactional"
                    class="radio"
                    ' . $purpose_transactional_checked . '><label for="purpose_transactional"> Transactional &nbsp;(send email, regardless of opt-in. Example: "Your order has been shipped")</label>
            </div>';

        $output_buttons =
            '<div class="buttons">
                <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
            </div>';
        $output_form_end = '</form>';
        
    // else the e-mail campaign is complete, so prepare information for viewing
    } else {
        $output_heading = 'View Campaign';
        $output_subheading = 'View this e-mail campaign\'s information.';
        $output_form_start = '';
        $output_status = h(get_email_campaign_status_name($status));
        $output_subject = h($subject);
        $output_bcc_email_address = h($bcc_email_address);
        $output_from_name = h($from_name);
        $output_from_email_address = h($from_email_address);
        $output_reply_email_address = h($reply_email_address);
        
        $output_start_time = h(prepare_form_data_for_output($start_time, 'date and time'));
        
        // if the start time is blank, then set to better value
        if ($output_start_time == '') {
            $output_start_time = 'Immediately';
        }

        $output_purpose = h(ucwords($purpose));
        
        $output_buttons = '';
        $output_form_end = '';
    }

    $output_auto_campaign = '';

    if ($email_campaign_profile_id) {

        $output_order = '';

        if ($order_reference_code) {
            $output_order =
                '<tr>
                    <td>Order:</td>
                    <td>
                        <a href="view_order.php?id=' . $order_id . '">' . $order_reference_code . '</a>
                    </td>
                </tr>';
        }

        $output_auto_campaign =
            '<tr>
                <th colspan="2"><h2>Auto Campaign</h2></th>
            </tr>
            <tr>
                <td>Profile:</td>
                <td>
                    <a href="edit_email_campaign_profile.php?id=' . $email_campaign_profile_id . '">
                        ' . h($email_campaign_profile_name) . '
                    </a>
                </td>
            </tr>
            ' . $output_order;
    }
    
    // get total number of recipients
    $query = "SELECT COUNT(*) FROM email_recipients WHERE email_campaign_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_email_recipients = $row[0];
    
    // get total number of complete recipients
    $query = "SELECT COUNT(*) FROM email_recipients WHERE (email_campaign_id = '" . escape($_GET['id']) . "') AND (complete = '1')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_completed_email_recipients = $row[0];
    
    if ($number_of_email_recipients > 0) {
        $output_progress_percentage = number_format($number_of_completed_email_recipients / $number_of_email_recipients * 100);
    } else {
        $output_progress_percentage = '100';
    }

    $output_format = '';
    $output_body = '';

    // if the format is plain text, then output that
    if ($format == 'plain_text') {
        $output_format = 'Plain Text';

        $output_readonly_attribute = '';

        // if the status is complete, then make the text area read-only
        if ($status == 'complete') {
            $output_readonly_attribute = ' readonly="readonly"';
        }

        $output_body = '<textarea name="body"' . $output_readonly_attribute . ' style="width: 99%; height: 300px">' . h($body) . '</textarea>';

    // else the format is HTML, so output that
    } else {
        $output_format = 'HTML';
        $output_body = '<iframe src="view_email_campaign.php?id=' . h($_GET['id']) .'" style="width: 99%; height: 300px"></iframe>';
    }

    // Prepare to area differently based on the type of campaign.
    switch ($type) {
        case 'manual':
            // get all contact groups that are associated with this e-mail campaign
            $query =
                "SELECT
                    contact_groups.name,
                    contact_groups_email_campaigns_xref.type
                FROM contact_groups_email_campaigns_xref
                LEFT JOIN contact_groups ON contact_groups_email_campaigns_xref.contact_group_id = contact_groups.id
                WHERE contact_groups_email_campaigns_xref.email_campaign_id = '" . escape($_GET['id']) . "'
                ORDER BY contact_groups.name ASC";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $output_included_contact_groups = '';
            $output_excluded_contact_groups = '';
            
            // loop through all contact groups, in order to prepare list of contact groups
            while ($row = mysqli_fetch_assoc($result)) {
                $contact_group_name = $row['name'];
                $contact_group_type = $row['type'];
                
                // if contact group is included
                if ($contact_group_type == 'included') {
                    if ($output_included_contact_groups) {
                        $output_included_contact_groups .= '<br />';
                    }
                    
                    $output_included_contact_groups .= h($contact_group_name);
                    
                // else contact group is excluded
                } else {
                    if ($output_excluded_contact_groups) {
                        $output_excluded_contact_groups .= '<br />';
                    }
                    
                    $output_excluded_contact_groups .= h($contact_group_name);
                }
            }
            
            // if there are no included contact groups, then prepare notice
            if ($output_included_contact_groups == '') {
                $output_included_contact_groups = '[None]';
            }
            
            // if there are no excluded contact groups, then prepare notice
            if ($output_excluded_contact_groups == '') {
                $output_excluded_contact_groups = '[None]';
            }
            
            // get entered e-mail address
            $query = "SELECT email_address FROM email_recipients WHERE (email_campaign_id = '" . escape($_GET['id']) . "') AND (contact_id = '0')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if an entered e-mail address was found, then prepare to output it
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $output_entered_email_address = h($row['email_address']);
                
            // else an entered e-mail address was not found, so prepare to output notice
            } else {
                $output_entered_email_address = '[None]';
            }

            $output_to_rows =
                '<tr>
                    <th colspan="2"><h2>E-Mail Message To My Contact Groups</h2></th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="margin-bottom: 1em">Send message to all Subscribers in my selected Contact Groups:</div>
                        <div style="margin-bottom: 1.5em">
                            ' . $output_included_contact_groups . '
                        </div>
                        <div style="margin-bottom: 1em">But don\'t send message to any Subscribers that also exist in any of the following Contact Groups:</div>
                        <div style="margin-bottom: 1.5em">
                            ' . $output_excluded_contact_groups . '
                        </div>
                        <div>
                            <div>Also send message to the following e-mail address: ' . $output_entered_email_address . '</div>
                        </div>
                    </td>
                </tr>';

            break;
        
        case 'automatic':
            $to_email_address = db_value("SELECT email_address FROM email_recipients WHERE email_campaign_id = '" . escape($_GET['id']) . "'");

            $output_to_rows =
                '<tr>
                    <th colspan="2"><h2>E-Mail Message To</h2></th>
                </tr>
                <tr>
                    <td>To:</td>
                    <td>' . h($to_email_address) . '</td>
                </tr>
                <tr>
                    <th colspan="2"><h2>E-Mail Message BCC</h2></th>
                </tr>
                <tr>
                    <td>BCC E-mail Address:</td>
                    <td>' . $output_bcc_email_address . '</td>
                </tr>';

            break;
    }
    
    // if an e-mail campaign job is setup on the server, then allow e-mail campaign to be scheduled
    if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB === true) {
        $output_start_time_rows =
            '<tr>
                <th colspan="2"><h2>E-Mail Message Delivery Schedule</h2></th>
            </tr>
            <tr>
                <td>Send at this Date &amp; Time:</td>
                <td>' . $output_start_time . '</td>
            </tr>';
    }
    
    print
        output_header() . '
        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
        <div id="subnav">
            <h1>' . h($subject) . '</h1>
            <div class="subheading">Created ' . get_relative_time(array('timestamp' => $created_timestamp)) . ' by ' . h($creator_username) . '.</div>
        </div>
        ' . $output_button_bar . '
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>' . $output_heading . '</h1>
            <div class="subheading">' . $output_subheading . '</div>
            ' . $output_form_start . '
                <table class="field" style="width: 100%">
                    <tr>
                        <th colspan="2"><h2>Status</h2></th>
                    </tr>
                    <tr>
                        <td style="width: 20%">Status:</td>
                        <td>
                            ' . $output_status . '
                        </td>
                    </tr>
                    <tr>
                        <td>Progress:</td>
                        <td>
                            ' . $output_progress_percentage . '% (' . number_format($number_of_completed_email_recipients) . ' of ' . number_format($number_of_email_recipients) . ' subscribers)
                        </td>
                    </tr>
                    ' . $output_auto_campaign . '
                    <tr>
                        <th colspan="2"><h2>E-Mail Message</h2></th>
                    </tr>
                    <tr>
                        <td>Subject:</td>
                        <td>' . $output_subject . '</td>
                    </tr>
                    <tr>
                        <td>Format:</td>
                        <td>' . $output_format . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="margin-bottom: 1em">Body:</div>
                            ' . $output_body . '
                        </td>
                    </tr>
                    ' . $output_to_rows . '
                    <tr>
                        <th colspan="2"><h2>E-Mail Message From</h2></th>
                    </tr>
                    <tr>
                        <td>From Name:</td>
                        <td>' . $output_from_name . '</td>
                    </tr>
                    <tr>
                        <td>From E-mail Address:</td>
                        <td>' . $output_from_email_address . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>E-Mail Message Reply To</h2></th>
                    </tr>
                    <tr>
                        <td>Reply to E-Mail Address:</td>
                        <td>' . $output_reply_email_address . '</td>
                    </tr>
                    ' . $output_start_time_rows . '
                    <tr>
                        <th colspan="2"><h2>Purpose (as defined by the CAN-SPAM Act)</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Purpose:</td>
                        <td>' . $output_purpose . '</td>
                    </tr>
                </table>
                ' . $output_buttons . '
            ' . $output_form_end . '
        </div>' .
        output_footer();
    
// else form has been submitted
} else {
    validate_token_field();
    
    // check if the e-mail campaign is complete
    $query =
        "SELECT
            type,
            status,
            subject,
            format
        FROM email_campaigns
        WHERE id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $type = $row['type'];
    $status = $row['status'];
    $subject = $row['subject'];
    $format = $row['format'];
    
    // if the e-mail campaign is complete, then output error
    if ($status == 'complete') {
        output_error('You may not edit a completed campaign. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    $sql_body = "";

    // if the format is plain text, then allow the body to be updated
    if ($format == 'plain_text') {
        $sql_body = "body = '" . escape($_POST['body']) . "',";
    }

    $sql_bcc_email_address = "";

    // If the campaign is an automatic campaign, then allow BCC email address to be updated.
    if ($type == 'automatic') {
        $sql_bcc_email_address = "bcc_email_address = '" . escape($_POST['bcc_email_address']) . "',";
    }
    
    $query =
        "UPDATE email_campaigns
        SET
            status = '" . escape($_POST['status']) . "',
            subject = '" . escape($_POST['subject']) . "',
            " . $sql_body . "
            from_name = '" . escape($_POST['from_name']) . "',
            from_email_address = '" . escape($_POST['from_email_address']) . "',
            reply_email_address = '" . escape($_POST['reply_email_address']) . "',
            " . $sql_bcc_email_address . "
            start_time = '" . escape(prepare_form_data_for_input($_POST['start_time'], 'date and time')) . "',
            purpose = '" . e($_POST['purpose']) . "',
            last_modified_user_id = '" . $user['id'] . "',
            last_modified_timestamp = UNIX_TIMESTAMP()
        WHERE id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('campaign (subject: ' . $subject . ') was modified', $_SESSION['sessionusername']);

    include_once('liveform.class.php');

    if (mb_strpos($_POST['send_to'], 'view_email_campaign_history.php') !== false) {
        $liveform = new liveform('view_email_campaign_history');
    } else {
        $liveform = new liveform('view_email_campaigns');
    }

    $liveform->add_notice('The campaign has been saved.');

    // If there is a send to set, then forward user to send to.
    if ($_POST['send_to'] != '') {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
        
    // Otherwise there is not a send to set, so forward user to view e-mail campaigns screen.
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_email_campaigns.php');
    }
}