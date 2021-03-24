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

// Used directly after a user logs in, in order to connect user to order, so we have someone to
// email for abandon cart emails.

function connect_user_to_order() {

    // If there is no order in session, or user is not logged in, or user is ghosting, then we don't
    // need to do anything.
    if (
        !$_SESSION['ecommerce']['order_id']
        or !isset($_SESSION['sessionusername'])
        or $_SESSION['software']['ghost']
    ) {
        return;
    }

    $order = db_item(
        "SELECT id, user_id, contact_id FROM orders
        WHERE (id = '" . e($_SESSION['ecommerce']['order_id']) . "') AND (status = 'incomplete')");

    // If the order was not found, then we don't need to do anything.
    if (!$order) {
        return;
    }

    // If the order already has a user or contact, then we don't want to change that.
    if ($order['user_id'] or $order['contact_id']) {
        return;
    }

    // Get user id and contact id.
    $user = db_item(
        "SELECT
            user_id AS id,
            user_contact AS contact_id
        FROM user
        WHERE user_username = '" . e($_SESSION['sessionusername']) . "'");

    if (!$user) {
        return;
    }

    // Update user and contact for order.
    db(
        "UPDATE orders
        SET
            user_id = '" . e($user['id']) . "',
            contact_id = '" . e($user['contact_id']) . "'
        WHERE id = '" . e($_SESSION['ecommerce']['order_id']) . "'");
}