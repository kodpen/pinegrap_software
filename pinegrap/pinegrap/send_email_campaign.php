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

// get e-mail campaign information
$query =
    "SELECT
        type,
        email_campaign_profile_id,
        action,
        action_item_id,
        calendar_event_recurrence_number,
        from_name,
        from_email_address,
        reply_email_address,
        bcc_email_address,
        subject,
        format,
        body,
        page_id,
        status,
        purpose,
        created_user_id,
        created_timestamp
    FROM email_campaigns
    WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$row = mysqli_fetch_assoc($result);

$type = $row['type'];
$email_campaign_profile_id = $row['email_campaign_profile_id'];
$action = $row['action'];
$action_item_id = $row['action_item_id'];
$calendar_event_recurrence_number = $row['calendar_event_recurrence_number'];
$from_name = $row['from_name'];
$from_email_address = $row['from_email_address'];
$reply_email_address = $row['reply_email_address'];
$bcc_email_address = $row['bcc_email_address'];
$subject_template = $row['subject'];
$format = $row['format'];
$body_template = $row['body'];
$page_id = $row['page_id'];
$status = $row['status'];
$purpose = $row['purpose'];
$created_user_id = $row['created_user_id'];
$created_timestamp = $row['created_timestamp'];

// if user has a user role and user is not the creator of the e-mail campaign, then output error
if (($user['role'] == 3) && ($created_user_id != $user['id'])) {
    log_activity("access denied to send e-mail campaign because user is not the creator of the e-mail campaign", $_SESSION['sessionusername']);
    output_error_in_popup('Access denied.');
}

// if e-mail campaign status is cancelled, then output error
if ($status == 'cancelled') {
    output_error_in_popup('The e-mail campaign may not be sent because it is cancelled.  Please update the status in order to send the e-mail campaign.');
}

// get next 10 e-mail recipients to send e-mail campaign to
$query =
    "SELECT
        id,
        email_address,
        contact_id
     FROM email_recipients
     WHERE
        (email_campaign_id = '" . escape($_GET['id']) . "')
        AND (complete = '0')
     ORDER BY id
     LIMIT 10";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if there is at least one e-mail recipient left
if (mysqli_num_rows($result) > 0) {
    $email_recipients = array();
    
    // loop through e-mail recipients
    while ($row = mysqli_fetch_assoc($result)) {
        $email_recipients[] = $row;
    }
    
    foreach ($email_recipients as $email_recipient) {

        // If this is a commercial campaign and this contact is not the manually entered email
        // address (i.e. "Also send message to the following e-mail address"), and we can't find
        // and opted-in contact for this recipient's email address, then delete this recipient,
        // and skip to the next.  This is necessary because a campaign might have been created and
        // scheduled in the future, and then a contact might have opted-out after the campaign was
        // created.  We look for any contact with the same email that is opted-in, because
        // the original contact might have been deleted or merged.

        if (
            ($purpose == 'commercial')
            and $email_recipient['contact_id']
            and !db_value(
                "SELECT id
                FROM contacts
                WHERE
                    (email_address = '" . e($email_recipient['email_address']) . "')
                    AND (opt_in = '1')
                LIMIT 1")
        ) {

            db("DELETE FROM email_recipients WHERE id = '" . $email_recipient['id'] . "'");

            // Skip to the next recipient.
            continue;
        }

        // prepare email body content
        $subject = $subject_template;
        $body = $body_template;

        // Create an array that will store the fields for variables that need to be replaced.
        $fields = array();
        
        // setup variables
        $mail_merge_first_name = '';
        $mail_merge_last_name = '';
        $mail_merge_nickname = '';
        $mail_merge_salutation = '';
        $mail_merge_suffix = '';
        
        // get contact information for email recipient
        $query =
            "SELECT
                first_name,
                last_name,
                nickname,
                salutation,
                suffix
             FROM contacts
             WHERE id = " . $email_recipient['contact_id'];
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);

        $first_name = trim($row['first_name']);
        $last_name = trim($row['last_name']);
        
        // if there is a first name in the database, update the variable
        if ($row['first_name'] != '') {
            $mail_merge_first_name = ' ' . trim($row['first_name']);
        }
        
        // if there is a last name in the database, update the variable
        if ($row['last_name'] != '') {
            $mail_merge_last_name = ' ' . trim($row['last_name']);
        }
        
        // if there is a nickname in the database, update the variable
        if ($row['nickname'] != '') {
            $mail_merge_nickname = trim($row['nickname']);
        }
        
        // if there is a salutation in the database, update the variable
        if ($row['salutation'] != '') {
            $mail_merge_salutation = trim($row['salutation']);
        }
        
        // if there is a suffix in the database, update the variable
        if ($row['suffix'] != '') {
            $mail_merge_suffix = ', ' . trim($row['suffix']);
        }
        
        // if there is a nickname for the current email recipient, use that for the dynamic data
        if ($mail_merge_nickname != '') {
            $mail_merge_dynamic_data = trim($mail_merge_nickname);
            
        // else, there was not a nickname, so display the users full name
        } else {
            $mail_merge_dynamic_data = trim($mail_merge_salutation . $mail_merge_first_name . $mail_merge_last_name . $mail_merge_suffix);
        }

        // Add name field so that variable is replaced.
        $fields[] = array(
            'name' => 'name',
            'data' => $mail_merge_dynamic_data,
            'type' => '');

        // Add first_name field so that variable is replaced.
        $fields[] = array(
            'name' => 'first_name',
            'data' => $first_name,
            'type' => '');

        // Add last_name field so that variable is replaced.
        $fields[] = array(
            'name' => 'last_name',
            'data' => $last_name,
            'type' => '');

        // If this is an auto email campaign, then add fields for that type of campaign.
        if ($type == 'automatic') {
            // Add field for action date and time.
            $fields[] = array(
                'name' => 'action_date_and_time',
                'data' => date('Y-m-d H:i:s', $created_timestamp),
                'type' => 'date and time');

            // If this email campaign was created due to a calendar event being reserved, then add fields for that.
            if ($action == 'calendar_event_reserved') {
                $calendar_event = get_calendar_event($action_item_id, $calendar_event_recurrence_number);

                // Add field for calendar event date and time range.
                $fields[] = array(
                    'name' => 'calendar_event_date_and_time_range',
                    'data' => $calendar_event['date_and_time_range'],
                    'type' => '');

                // Add field for calendar event start date and time.
                $fields[] = array(
                    'name' => 'calendar_event_start_date_and_time',
                    'data' => $calendar_event['start_date_and_time'],
                    'type' => 'date and time');

                // Add field for calendar event end date and time.
                $fields[] = array(
                    'name' => 'calendar_event_end_date_and_time',
                    'data' => $calendar_event['end_date_and_time'],
                    'type' => 'date and time');
            }
        }

        // Replace variables in subject (e.g. ^^name^^).
        $subject = replace_variables(array(
            'content' => $subject,
            'fields' => $fields,
            'format' => 'plain_text'));

        // Replace variables in body (e.g. ^^name^^).
        $body = replace_variables(array(
            'content' => $body,
            'fields' => $fields,
            'format' => $format));
        
        // create email address reference code
        $email_recipient['reference_code'] = generate_email_recipient_reference_code();

        // if the format of the e-mail should be plain text, then prepare that
        if ($format == 'plain_text') {

            // If this is a commercial email, then prepare organization and email preferences info.
            if ($purpose == 'commercial') {

                $organization = '';

                // if there is an organization name, then add it to the organization
                if (ORGANIZATION_NAME != '') {
                    $organization .= ORGANIZATION_NAME;
                }

                // if there is an organization address 1, then add it to the organization
                if (ORGANIZATION_ADDRESS_1 != '') {
                    // if the organization is not blank, then add a space for separation
                    if ($organization != '') {
                        $organization .= ' ';
                    }

                    $organization .= ORGANIZATION_ADDRESS_1;
                }

                // if there is an organization address 2, then add it to the organization
                if (ORGANIZATION_ADDRESS_2 != '') {
                    // if the organization is not blank, then add a space for separation
                    if ($organization != '') {
                        $organization .= ' ';
                    }
                    
                    $organization .= ORGANIZATION_ADDRESS_2;
                }

                // if there is an organization city, then add it to the organization
                if (ORGANIZATION_CITY != '') {
                    // if the organization is not blank, then add a space for separation
                    if ($organization != '') {
                        $organization .= ' ';
                    }
                    
                    $organization .= ORGANIZATION_CITY;
                }

                // if there is an organization state, then add it to the organization
                if (ORGANIZATION_STATE != '') {
                    // if the organization is not blank, then add a space for separation
                    if ($organization != '') {
                        $organization .= ' ';
                    }
                    
                    $organization .= ORGANIZATION_STATE;
                }

                // if there is an organization zip code, then add it to the organization
                if (ORGANIZATION_ZIP_CODE != '') {
                    // if the organization is not blank, then add a space for separation
                    if ($organization != '') {
                        $organization .= ' ';
                    }
                    
                    $organization .= ORGANIZATION_ZIP_CODE;
                }

                // if there is an organization country, then add it to the organization
                if (ORGANIZATION_COUNTRY != '') {
                    // if the organization is not blank, then add a space for separation
                    if ($organization != '') {
                        $organization .= ' ';
                    }
                    
                    $organization .= ORGANIZATION_COUNTRY;
                }
				if(language_ruler()==='en'){
					$body .=
					    "\n" .
					    "\n" .
					    "\n" .
					    $organization . "\n" .
					    "\n" .
					    'Update email preferences or unsubscribe:' . "\n" .
					    "\n" .
					    URL_SCHEME . HOSTNAME_SETTING . PATH . SOFTWARE_DIRECTORY . '/email_preferences.php?id=' . urlencode(base64_encode(str_rot13($email_recipient['email_address'])));
				}
				else if(language_ruler()==='tr'){
					$body .=
					    "\n" .
					    "\n" .
					    "\n" .
					    $organization . "\n" .
					    "\n" .
					    'E-posta tercihlerini guncellemek veya aboneligi iptal etmek icin:' . "\n" .
					    "\n" .
					    URL_SCHEME . HOSTNAME_SETTING . PATH . SOFTWARE_DIRECTORY . '/email_preferences.php?id=' . urlencode(base64_encode(str_rot13($email_recipient['email_address'])));
				}
			}

        // else the format of the e-mail should be HTML, so prepare that
        } else {

            $body = preg_replace('/<reference_code><\/reference_code>/', $email_recipient['reference_code'], $body);
            $body = preg_replace('/<email_address_id><\/email_address_id>/', urlencode(base64_encode(str_rot13($email_recipient['email_address']))), $body);

        }
        
        email(array(
            'to' => $email_recipient['email_address'],
            'to_name' => $mail_merge_dynamic_data,
            'bcc' => $bcc_email_address,
            'from_name' => $from_name,
            'from_email_address' => $from_email_address,
            'reply_to' => $reply_email_address,
            'subject' => $subject,
            'format' => $format,
            'body' => $body,
            'type' => 'campaign'));
        
        // mark e-mail recipient as complete and set reference code
        $query = 
            "UPDATE email_recipients
            SET
                complete = '1',
                reference_code = '" . $email_recipient['reference_code'] . "'
            WHERE id = '" . $email_recipient['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // get total number of recipients
    $query = "SELECT COUNT(*) FROM email_recipients WHERE email_campaign_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_email_recipients = $row[0];
    
    // get total number of incomplete recipients
    $query = "SELECT COUNT(*) FROM email_recipients WHERE (email_campaign_id = '" . escape($_GET['id']) . "') AND (complete = '0')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_remaining_email_recipients = $row[0];

    $number_of_completed_email_recipients = $number_of_email_recipients - $number_of_remaining_email_recipients;
    $percent_complete = ($number_of_completed_email_recipients / $number_of_email_recipients) * 100;
    $percent_complete = round($percent_complete, 0);

    echo
        '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Send E-mail Campaign</title>
                ' . get_generator_meta_tag() . '
                <meta http-equiv="refresh" content="1;url=' . URL_SCHEME . h($_SERVER['HTTP_HOST']) . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/send_email_campaign.php?id=' . h(urlencode($_GET['id'])) . get_token_query_string_field() . '">
                <link rel="stylesheet" type="text/css" href="' . CONTROL_PANEL_STYLESHEET_URL . '" />
            </head>
            <body class="campaigns">
                <div id="subnav">
                    <h1>' . h($subject_template) . '</h1>
                    From Name: ' . h($from_name) . '<br />
                    From E-mail Address: ' . h($from_email_address) . '<br />
                    Reply to E-mail Address: ' . h($reply_email_address) . '
                </div>
                <div id="content">
                    <h1>Sending Campaign Now...</h1>
                    <div class="subheading" style="margin-bottom: 1em">Sending the e-mail message to each specific subscriber now.</div>
                    <div style="color: #c12b2b; margin-bottom: 1em">Closing this browser window before completion will pause this Campaign, but it can be resumed at any time.</div>
                    <div style="margin-bottom: 15px">' . number_format($number_of_completed_email_recipients) . ' of ' . number_format($number_of_email_recipients) . ' (' . $percent_complete . '%) completed</div>
                    <div style="border: 1px solid #666666; margin-bottom: 15px">
                        <div style="width: ' . $percent_complete . '%; background-color: #666666">&nbsp;</div>
                    </div>
                    <div class="buttons"><input type="button" value="Stop &amp; Close" onclick="window.close();" class="submit"></div>
                </div>
            </body>
        </html>';
    
// else there is not at least one e-mail recipient left, so e-mail campaign is complete
} else {
    // update e-mail campaign status
    $query = "UPDATE email_campaigns SET status = 'complete' WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // get total number of recipients
    $query = "SELECT COUNT(*) FROM email_recipients WHERE email_campaign_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_email_recipients = $row[0];

    $log_page = '';

    // if HTML was selected for the format, then add page info to log message
    if ($format == 'html') {
        $log_page = ', page: ' . get_page_name($page_id);
    }
    
    // log end of sending e-mails
    log_activity('e-mail campaign (subject: ' . $subject_template . $log_page . ') was sent to ' . number_format($number_of_email_recipients) . ' recipients', $_SESSION['sessionusername']);

    // If this is an automatic campaign, then check if auto campaigns need to be created.
    if ($type == 'automatic') {
        // Get contact id from the campaign that was just sent so it can be used for new auto campaigns if necessary.
        $contact_id = db_value("SELECT contact_id FROM email_recipients WHERE email_campaign_id = '" . escape($_GET['id']) . "'");

        // Check if there are auto e-mail campaigns that should be created based on this auto e-mail campaign being sent.
        create_auto_email_campaigns(array(
            'action' => 'email_campaign_sent',
            'action_item_id' => $email_campaign_profile_id,
            'contact_id' => $contact_id));
    }

    echo
        '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Send E-mail Campaign</title>
                ' . get_generator_meta_tag() . '
                <link rel="stylesheet" type="text/css" href="' . CONTROL_PANEL_STYLESHEET_URL . '" />
            </head>
            <body class="campaigns">
                <div id="subnav">
                    <h1>' . h($subject_template) . '</h1>
                    From Name: ' . h($from_name) . '<br />
                    From E-mail Address: ' . h($from_email_address) . '<br />
                    Reply to E-mail Address: ' . h($reply_email_address) . '
                </div>
                <div id="content">
                    <h1>Campaign Sent</h1>
                    <div class="subheading" style="margin-bottom: 1em">The e-mail campaign has been sent to all recipients.</div>
                    <div class="buttons"><input type="button" value="Close" onclick="window.opener.location.reload(true); window.close();" class="submit"></div>
                </div>
            </body>
        </html>';
}

function output_error_in_popup($error_message)
{
    $output = '<div style="color: red">Error: ' . $error_message . '</div>';
    print $output;
    exit();
}