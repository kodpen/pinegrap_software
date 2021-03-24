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

// get current date
$current_date = date('Y-m-d');

// if membership expiration warning e-mail is enabled and a page is selected, then send e-mail to members
if ((MEMBERSHIP_EXPIRATION_WARNING_EMAIL == true) && (MEMBERSHIP_EXPIRATION_WARNING_EMAIL_PAGE_ID != 0)) {
    // check if page exists
    $query = "SELECT page_id FROM page WHERE page_id = '" . MEMBERSHIP_EXPIRATION_WARNING_EMAIL_PAGE_ID . "'";
    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
    
    // if page was found, then continue to send e-mail to members
    if (mysqli_num_rows($result) > 0) {
        // get current year, month, and day
        $current_year = date('Y');
        $current_month = date('m');
        $current_day = date('d');
        
        // get maximum expiration date, because we only want to warn members who's expiration expires soon
        $maximum_expiration_time = mktime(0, 0, 0, $current_month, $current_day + MEMBERSHIP_EXPIRATION_WARNING_EMAIL_DAYS_BEFORE_EXPIRATION, $current_year);
        $maximum_expiration_date = date('Y-m-d', $maximum_expiration_time);
        
        // get all members that should receive expiration warning e-mail
        $query =
            "SELECT
                id,
                email_address,
                expiration_date
            FROM contacts
            WHERE
                (member_id != '')
                AND (member_id IS NOT NULL)
                AND (expiration_date >= '$current_date')
                AND (expiration_date <= '$maximum_expiration_date')
                AND (expiration_date != warning_expiration_date)
                AND (email_address != '')
                AND (email_address IS NOT NULL)";
        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
        
        $contacts = array();
        
        // loop through members in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $contacts[] = $row;
        }
        
        // loop through members in order to send warning e-mail
        foreach ($contacts as $contact) {

            require_once(dirname(__FILE__) . '/get_page_content.php');

            $body = get_page_content(MEMBERSHIP_EXPIRATION_WARNING_EMAIL_PAGE_ID, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true);
            
            email(array(
                'to' => $contact['email_address'],
                'bcc' => MEMBERSHIP_EMAIL_ADDRESS,
                'from_name' => ORGANIZATION_NAME,
                'from_email_address' => EMAIL_ADDRESS,
                'subject' => MEMBERSHIP_EXPIRATION_WARNING_EMAIL_SUBJECT . ' ' . prepare_form_data_for_output($contact['expiration_date'], 'date'),
                'format' => 'html',
                'body' => $body));
            
            // set expiration date for warning e-mail, so that warning e-mail is not sent again for this same expiration date
            $query = "UPDATE contacts SET warning_expiration_date = '" . $contact['expiration_date'] . "' WHERE id = '" . $contact['id'] . "'";
            $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
        }
        
        // if membership job e-mailed at least 1 member, then log action
        if (count($contacts) > 0) {
            log_activity('membership job sent expiration warning e-mail(s) to ' . count($contacts) . ' member(s)', 'UNKNOWN');
        }
    }
}

// check if membership contact group exists
$query = "SELECT id FROM contact_groups WHERE id = '" . MEMBERSHIP_CONTACT_GROUP_ID  . "'";
$result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

// if membership contact group exists
if (mysqli_num_rows($result) > 0) {
    // get contacts in membership contact group who's membership is not valid (e.g. membership expired), so they can be removed from group
    $query =
        "SELECT contacts.id
        FROM contacts_contact_groups_xref
        LEFT JOIN contacts ON contacts_contact_groups_xref.contact_id = contacts.id
        WHERE
            (contacts_contact_groups_xref.contact_group_id = '" . MEMBERSHIP_CONTACT_GROUP_ID . "')
            AND
            (
                (contacts.member_id = '')
                OR (contacts.member_id IS NULL)
                OR
                (
                    (contacts.expiration_date < '$current_date')
                    AND (contacts.expiration_date != '0000-00-00')
                    AND (contacts.expiration_date IS NOT NULL)
                )
            )";
    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    $contacts = array();

    // loop through contacts in order to add them to array
    while ($row = mysqli_fetch_assoc($result)) {
        $contacts[] = $row;
    }

    // loop through all contacts who's membership is not valid, so they can be removed from membership contact group
    foreach ($contacts as $contact) {
        $query =
            "DELETE FROM contacts_contact_groups_xref
            WHERE
                (contact_id = '" . $contact['id'] . "')
                AND (contact_group_id = '" . MEMBERSHIP_CONTACT_GROUP_ID . "')";
        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
    }

    // if membership job removed at least one contact, then log action
    if (count($contacts) > 0) {
        log_activity('membership job removed ' . count($contacts) . ' member(s) from membership contact group because membership was no longer valid (e.g. membership expired)', 'UNKNOWN');
    }
}
?>