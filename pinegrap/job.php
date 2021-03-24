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

// This is the general job that handles misc. things that need to run often.
// It should be run by a cron job.  Currently it does the following:
//
// - Publishing scheduled comments
// - Creating order abandoned auto campaigns
// - Unpublishing old calendar events so they don't appear on the calendar
//     or in search results.

require('init.php');

// Get all scheduled comments that need to be published.
$comments = db_items(
    "SELECT
        id,
        page_id,
        item_id,
        item_type
    FROM comments
    WHERE
        (publish_date_and_time != '0000-00-00 00:00:00')
        AND (publish_date_and_time <= NOW())
        AND (published = 0)
    ORDER BY publish_date_and_time");

// Loop through the comments in order to publish them.
foreach ($comments as $comment) {
    // Publish the comment and clear the scheduled info.
    // We update the created timestamp so that the comment appears in logical order.
    db(
        "UPDATE comments
        SET
            published = '1',
            publish_date_and_time = '',
            publish_cancel = '0',
            created_timestamp = UNIX_TIMESTAMP()
        WHERE id = '" . $comment['id'] . "'");

    // If this comment is for a submitted form on a form item view,
    // then update submitted form info.
    if ($comment['item_type'] == 'submitted_form') {

        // Get the number of views so we do not lose that data when we delete record below.
        $number_of_views = db_value(
            "SELECT number_of_views
            FROM submitted_form_info
            WHERE
                (submitted_form_id = '" . $comment['item_id'] . "')
                AND (page_id = '" . $comment['page_id'] . "')");

        // Get the number of published comments for this submitted form and page.
        $number_of_comments = db_value(
            "SELECT COUNT(*)
            FROM comments
            WHERE
                (page_id = '" . $comment['page_id'] . "')
                AND (item_id = '" . $comment['item_id'] . "')
                AND (item_type = 'submitted_form')
                AND (published = '1')");
        
        // Delete the current record if one exists.
        db(
            "DELETE FROM submitted_form_info
            WHERE
                (submitted_form_id = '" . $comment['item_id'] . "')
                AND (page_id = '" . $comment['page_id'] . "')");
        
        // Insert new record.
        db(
            "INSERT INTO submitted_form_info (
                submitted_form_id,
                page_id,
                number_of_views,
                number_of_comments,
                newest_comment_id)
             VALUES (
                '" . $comment['item_id'] . "',
                '" . $comment['page_id'] . "',
                '$number_of_views',
                '$number_of_comments',
                '" . $comment['id'] . "')");
    }

    send_comment_email_to_administrators($comment['id']);

    // If this comment is for a submitted form on a form item view,
    // then send comment email to custom form submitter.
    if ($comment['item_type'] == 'submitted_form') {
        send_comment_email_to_custom_form_submitter($comment['id']);
    }

    send_comment_email_to_watchers($comment['id']);
}

$number_of_comments = count($comments);

// If at least one comment was published, then log activity.
if ($number_of_comments) {

    $plural_suffix = '';

    if ($number_of_comments > 1) {
        $plural_suffix = 's';
    }

    log_activity('general job published ' . number_format($number_of_comments) . ' scheduled comment' . $plural_suffix, 'UNKNOWN');

}


// If commerce is enabled and there is at least one enabled campaign profile
// that creates auto campaigns for abandoned orders, then check if any orders
// have been abandoned.

if (
    ECOMMERCE
    and db_value(
        "SELECT id
        FROM email_campaign_profiles
        WHERE (enabled = '1') AND (action = 'order_abandoned')
        LIMIT 1")
) {

    $six_hours_ago_timestamp = time() - 21600;

    $week_ago_timestamp = time() - 604800;

    // Get all incomplete orders that have a contact that are at least 6 hours old,
    // but not more than a week old, and where auto campaigns have not already
    // been added for it, and where the order has at least one enabled order item.
    // The week old check makes sure that we don't create a ton of campaigns for very old
    // orders the moment after an abandoned order profile is created.

    $orders = db_items(
        "SELECT
            orders.id,
            orders.contact_id,
            contacts.email_address AS contact_email_address,
            orders.order_date
        FROM orders
        LEFT JOIN contacts ON orders.contact_id = contacts.id
        WHERE
            (orders.status = 'incomplete')
            AND (orders.contact_id != '0')
            AND (orders.order_date <= '$six_hours_ago_timestamp')
            AND (orders.order_date >= '$week_ago_timestamp')
            AND (contacts.id IS NOT NULL)
            AND (contacts.email_address != '')
            AND NOT EXISTS (
                SELECT 1
                FROM email_campaigns
                WHERE email_campaigns.order_id = orders.id
                LIMIT 1)
            AND EXISTS (
                SELECT 1
                FROM order_items
                LEFT JOIN products ON order_items.product_id = products.id
                WHERE
                    (order_items.order_id = orders.id)
                    AND (products.enabled = '1')
                LIMIT 1)");

    foreach ($orders as $order) {

        // If an order abandoned auto campaign has already been created in the last
        // week for this same customer (for a different order), then don't create
        // a campaign for this order, because we don't want to annoy the customer.
        // We check for both a matching contact id and email address, because
        // the customer might have created multiple orders without being logged in,
        // which would result in multiple contacts being created.
        if (
            db_value(
                "SELECT email_campaigns.id
                FROM email_campaigns
                LEFT JOIN email_recipients ON email_campaigns.id = email_recipients.email_campaign_id
                WHERE
                    (email_campaigns.action = 'order_abandoned')
                    AND (email_campaigns.created_timestamp >= '$week_ago_timestamp')
                    AND (email_campaigns.status != 'cancelled')
                    AND
                    (
                        (email_recipients.contact_id = '" . $order['contact_id'] . "')
                        OR (email_recipients.email_address = '" . e($order['contact_email_address']) . "')
                    )
                LIMIT 1")
        ) {
            continue;
        }

        // If this customer has completed a different order after this order,
        // was created, then don't create a campaign for this order,
        // because the other order that was completed was likely related/a duplicate
        // of this order, and we don't want to annoy the customer.
        if (
            db_value(
                "SELECT orders.id
                FROM orders
                LEFT JOIN contacts ON orders.contact_id = contacts.id
                WHERE
                    (orders.status != 'incomplete')
                    AND (orders.order_date > '" . $order['order_date'] . "')
                    AND
                    (
                        (orders.contact_id = '" . $order['contact_id'] . "')
                        OR (contacts.email_address = '" . e($order['contact_email_address']) . "')
                    )
                LIMIT 1")
        ) {
            continue;
        }

        create_auto_email_campaigns(array(
            'action' => 'order_abandoned',
            'order_id' => $order['id'],
            'contact_id' => $order['contact_id']));

    }

}

// Check to see if there are any old calendar events that need to be unpublished,
// so that they do not appear on the calendar anymore or in the search.

unpublish_events();

function unpublish_events() {

    // If calendars is disabled in the site settings, then abort.
    if (!CALENDARS) {
        return;
    }

    // Get the last time that this check was run so we can determine if we do it now.
    // We only unpublish events once per day even though the general job
    // might run more often, like once every 5 minutes, so that the cron job
    // do not cause unnecessary load on the server.

    $unpublish_event_timestamp = db_value("SELECT unpublish_event_timestamp FROM config");

    $current_timestamp = time();

    // If we have already performed this check in the last day, then abort.
    if ($unpublish_event_timestamp > ($current_timestamp - 86400)) {
        return;
    }

    // Get all events that are currently published and that might need to be unpublished.
    $events = db_items(
        "SELECT id
        FROM calendar_events
        WHERE
            (published = '1')
            AND (unpublish_days != '0')");

    foreach ($events as $event) {

        $event = get_calendar_event($event['id'], 0);

        // If the event is a recurring event, then get info for the last recurrence.
        // because we need to figure out if the last recurrence is old enough.
        if ($event['recurrence']) {
            $event = get_calendar_event($event['id'], $event['total_recurrence_number']);
        }

        // Calculate when the event should be allowed to be unpublished.
        $unpublish_timestamp = strtotime($event['end_date_and_time']) + ($event['unpublish_days'] * 86400);

        // If the event is old enough, then unpublish it.
        if ($current_timestamp >= $unpublish_timestamp) {

            db(
                "UPDATE calendar_events
                SET
                    published = '0',
                    last_modified_user_id = '" . USER_ID . "',
                    last_modified_timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . $event['id'] . "'");

            log_activity('calendar event (' . $event['name'] . ') was unpublished by general job because event was old');

        }

    }

    // Remember that we just checked if events needed to be unpublished,
    // so that we don't check again for 24 hours.
    db("UPDATE config SET unpublish_event_timestamp = UNIX_TIMESTAMP()");
}

// If MailChimp and commerce is enabled, then sync product groups, products, and orders with
// MailChimp.
if (MAILCHIMP and ECOMMERCE) {
    require_once(dirname(__FILE__) . '/mailchimp.php');
    mailchimp_sync();
}