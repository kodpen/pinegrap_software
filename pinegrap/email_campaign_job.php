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

require('init.php');

// if the e-mail campaign job is not activated, then output error
if ((defined('EMAIL_CAMPAIGN_JOB') == false) || (EMAIL_CAMPAIGN_JOB != true)) {
    $error_message = 'The e-mail campaign job did not run because it is not activated in the config.php file.';
    log_activity($error_message, 'UNKNOWN');
    print 'Error: ' . $error_message;
    exit();
}

// if the number of e-mails to be sent is defined, then use that value
if ((defined('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS') == true) && (EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS != '')) {
    $number_of_emails = EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS;
    
// else the number of e-mails to be sent is not defined, so use default value
} else {
    $number_of_emails = 25;
}

// Cancel all order abandoned auto campaigns that would have been sent now,
// if the order has been deleted or the order does not have any enabled
// products in it, so we don't annoy the customer with a worthless email.

db(
    "UPDATE email_campaigns
    LEFT JOIN orders ON email_campaigns.order_id = orders.id
    SET
        email_campaigns.status = 'cancelled',
        email_campaigns.last_modified_user_id = '',
        email_campaigns.last_modified_timestamp = UNIX_TIMESTAMP()
    WHERE
        (email_campaigns.action = 'order_abandoned')
        AND (email_campaigns.status = 'ready')
        AND (email_campaigns.start_time <= NOW())
        AND
        (
            (orders.id IS NULL)
            OR
            NOT EXISTS (
                SELECT 1
                FROM order_items
                LEFT JOIN products ON order_items.product_id = products.id
                WHERE
                    (order_items.order_id = orders.id)
                    AND (products.enabled = '1')
                LIMIT 1)
        )");

// get e-mail recipients to send e-mail campaign to
$query = "SELECT
            email_recipients.id,
            email_recipients.email_campaign_id,
            email_recipients.email_address,
            email_recipients.contact_id,
            email_campaigns.type,
            email_campaigns.action,
            email_campaigns.action_item_id,
            email_campaigns.calendar_event_recurrence_number,
            orders.reference_code AS order_reference_code,
            email_campaigns.from_name,
            email_campaigns.from_email_address,
            email_campaigns.reply_email_address,
            email_campaigns.bcc_email_address,
            email_campaigns.subject,
            email_campaigns.format,
            email_campaigns.purpose,
            email_campaigns.created_timestamp
         FROM email_recipients
         LEFT JOIN email_campaigns on email_recipients.email_campaign_id = email_campaigns.id
         LEFT JOIN orders ON email_campaigns.order_id = orders.id
         WHERE
            (email_recipients.complete = '0')
            AND (email_campaigns.status = 'ready')
            AND (email_campaigns.start_time <= NOW())
         ORDER BY email_campaigns.start_time, email_recipients.email_campaign_id, email_recipients.id
         LIMIT $number_of_emails";
$result = mysqli_query(db::$con, $query);

$email_recipients = array();
$email_campaigns = array();

// loop through e-mail recipients
while ($row = mysqli_fetch_assoc($result)) {
    $email_recipients[] = $row;
    
    // if e-mail campaign has not already been added to array, add it
    if (in_array($row['email_campaign_id'], $email_campaigns) == false) {
        $email_campaigns[] = $row['email_campaign_id'];
    }
}

$count = 0;

foreach ($email_recipients as $email_recipient) {

    // Lock tables, so no one can read tables, to prevent job(s) from sending duplicate e-mails to
    // the same recipients.  Although we only care about locking the email_recipients &
    // email_campaigns tables, we have to lock the other tables because MySQL requires that we lock
    // additional tables that we read from or write to.  For example, the email() function might
    // write to the log table, so that is why that lock is required.  We should consider moving to
    // InnoDB so that we can lock or do transactions in a better way in the future.

    $sql_calendar_event_locks = "";

    // If this email campaign was created due to a calendar event being reserved, then lock extra tables.
    // We have to do this because there are various tables that functions select from in order to deal with these types of campaigns.
    if ($email_recipient['action'] == 'calendar_event_reserved') {
        $sql_calendar_event_locks = ", calendar_events WRITE, products WRITE, number_of_remaining_spots WRITE, calendar_events_calendar_event_locations_xref WRITE, calendar_event_locations WRITE";
    }

    $query = "LOCK TABLES email_recipients WRITE, email_campaigns WRITE, contacts WRITE, log WRITE" . $sql_calendar_event_locks;
    $result = mysqli_query(db::$con, $query);

    // get body for e-mail campaign (we can't get body in join query above for some reason because MySQL takes too long)
    // this will also allow us to make sure this recipient is still not complete (i.e. to make sure another job hasn't recently sent to this recipient already).
    $query =
        "SELECT email_campaigns.body
        FROM email_recipients
        LEFT JOIN email_campaigns on email_recipients.email_campaign_id = email_campaigns.id
        WHERE
           (email_recipients.id = '" . $email_recipient['id'] . "')
           AND (email_recipients.complete = '0')
           AND (email_campaigns.status = 'ready')
           AND (email_campaigns.start_time <= NOW())";
    $result = mysqli_query(db::$con, $query);
    
    // if data for recipient was found, continue with sending e-mail to recipient
    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);
        $email_recipient['body'] = $row['body'];

        // If this is a commercial campaign and this contact is not the manually entered email
        // address (i.e. "Also send message to the following e-mail address"), and we can't find
        // and opted-in contact for this recipient's email address, then delete this recipient,
        // and skip to the next.  This is necessary because a campaign might have been created and
        // scheduled in the future, and then a contact might have opted-out after the campaign was
        // created.  We look for any contact with the same email that is opted-in, because
        // the original contact might have been deleted or merged.

        if (
            ($email_recipient['purpose'] == 'commercial')
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

            db("UNLOCK TABLES");

            // Skip to the next recipient.
            continue;
        }
        
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
        
        $result = mysqli_query(db::$con, $query);
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
        if ($email_recipient['type'] == 'automatic') {
            // Add field for action date and time.
            $fields[] = array(
                'name' => 'action_date_and_time',
                'data' => date('Y-m-d H:i:s', $email_recipient['created_timestamp']),
                'type' => 'date and time');

            // If this email campaign was created due to a calendar event being reserved, then add fields for that.
            if ($email_recipient['action'] == 'calendar_event_reserved') {
                $calendar_event = get_calendar_event($email_recipient['action_item_id'], $email_recipient['calendar_event_recurrence_number']);

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

            // Otherwise if this email campaign was created due to an order being
            // abandoned, then add order reference code field.
            } else if ($email_recipient['action'] == 'order_abandoned') {
                $fields[] = array(
                    'name' => 'order_reference_code',
                    'data' => $email_recipient['order_reference_code'],
                    'type' => '');
            }
        }

        // Replace variables in subject (e.g. ^^name^^).
        $email_recipient['subject'] = replace_variables(array(
            'content' => $email_recipient['subject'],
            'fields' => $fields,
            'format' => 'plain_text'));

        // Replace variables in body (e.g. ^^name^^).
        $email_recipient['body'] = replace_variables(array(
            'content' => $email_recipient['body'],
            'fields' => $fields,
            'format' => $email_recipient['format']));
        
        // create email address reference code
        $email_recipient['reference_code'] = generate_email_recipient_reference_code();

        // if the format of the e-mail should be plain text, then prepare that
        if ($email_recipient['format'] == 'plain_text') {

            // If this is a commercial email, then prepare organization and email preferences info.
            if ($email_recipient['purpose'] == 'commercial') {
                
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
					$email_recipient['body'] .=
					    "\n" .
					    "\n" .
					    "\n" .
					    $organization . "\n" .
					    "\n" .
					    'Update email preferences or unsubscribe:' . "\n" .
					    "\n" .
					    URL_SCHEME . HOSTNAME_SETTING . PATH . SOFTWARE_DIRECTORY . '/email_preferences.php?id=' . urlencode(base64_encode(str_rot13($email_recipient['email_address'])));
				}else if(language_ruler()==='tr'){
					$email_recipient['body'] .=
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

            $email_recipient['body'] = preg_replace('/<reference_code><\/reference_code>/', $email_recipient['reference_code'], $email_recipient['body']);
            $email_recipient['body'] = preg_replace('/<email_address_id><\/email_address_id>/', urlencode(base64_encode(str_rot13($email_recipient['email_address']))), $email_recipient['body']);
            
        }

        email(array(
            'to' => $email_recipient['email_address'],
            'to_name' => $mail_merge_dynamic_data,
            'bcc' => $email_recipient['bcc_email_address'],
            'from_name' => $email_recipient['from_name'],
            'from_email_address' => $email_recipient['from_email_address'],
            'reply_to' => $email_recipient['reply_email_address'],
            'subject' => $email_recipient['subject'],
            'format' => $email_recipient['format'],
            'body' => $email_recipient['body'],
            'type' => 'campaign'));
        
        // mark e-mail recipient as complete and set reference code
        $query = 
            "UPDATE email_recipients
            SET 
                complete = '1',
                reference_code = '" . $email_recipient['reference_code'] . "'
            WHERE id = '" . $email_recipient['id'] . "'";
        $result = mysqli_query(db::$con, $query);
        
        $count++;
    }
    
    // release lock on tables
    $query = "UNLOCK TABLES";
    $result = mysqli_query(db::$con, $query);
}

// loop through all e-mail campaigns that were sent in order to check if e-mail campaign is complete
foreach ($email_campaigns as $email_campaign_id) {
    // check to see if there are any incomplete e-mail recipients for this e-mail campaign,
    // so we know if we need to update status of e-mail campaign
    $query = "SELECT COUNT(*) FROM email_recipients WHERE (complete = '0') AND (email_campaign_id = '" . $email_campaign_id . "')";
    $result = mysqli_query(db::$con, $query);
    $row = mysqli_fetch_row($result);
    
    // if all e-mail campaign recipients are complete, update e-mail campaign status
    if ($row[0] == 0) {
        // update e-mail campaign status
        $query = "UPDATE email_campaigns SET status = 'complete' WHERE id = '" . $email_campaign_id . "'";
        $result = mysqli_query(db::$con, $query);

        // Get info about this email campaign in order to determine if we need to create auto campaigns based on this campaign being sent.
        $email_campaign = db_item(
            "SELECT
                id,
                type,
                email_campaign_profile_id
            FROM email_campaigns
            WHERE id = '$email_campaign_id'");

        // If this is an automatic campaign, then check if auto campaigns need to be created.
        if ($email_campaign['type'] == 'automatic') {
            // Get contact id from the campaign that was just sent so it can be used for new auto campaigns if necessary.
            $contact_id = db_value("SELECT contact_id FROM email_recipients WHERE email_campaign_id = '" . $email_campaign['id'] . "'");

            // Check if there are auto e-mail campaigns that should be created based on this auto e-mail campaign being sent.
            create_auto_email_campaigns(array(
                'action' => 'email_campaign_sent',
                'action_item_id' => $email_campaign['email_campaign_profile_id'],
                'contact_id' => $contact_id));
        }
    }
}

// if e-mail campaign job e-mailed at least 1 recipient, then log action
if ($count > 0) {
    log_activity('e-mail campaign job sent e-mail(s) to ' . $count . ' recipient(s)', $_SESSION['sessionusername']);
}