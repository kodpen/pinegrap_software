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

$form = new liveform('mailchimp_settings');

// Get MailChimp settings.
$config = db_item(
    "SELECT
        mailchimp,
        mailchimp_key,
        mailchimp_list_id,
        mailchimp_store_id,
        mailchimp_sync_days,
        mailchimp_sync_limit,
        mailchimp_automation
    FROM config");

// If the form has not been submitted, then output it.
if (!$_POST) {

    // If the form has not been submitted yet then autofill fields.
    if (!$form->field_in_session('mailchimp_key')) {

        $form->set('mailchimp', $config['mailchimp']);
        $form->set('mailchimp_key', $config['mailchimp_key']);
        $form->set('mailchimp_list_id', $config['mailchimp_list_id']);

        if ($config['mailchimp_store_id']) {
            $form->set('mailchimp_store_id', $config['mailchimp_store_id']);
        } else {
            $form->set('mailchimp_store_id', HOSTNAME_SETTING);
        }

        $form->set('mailchimp_sync_days', $config['mailchimp_sync_days']);
        $form->set('mailchimp_sync_limit', $config['mailchimp_sync_limit']);
        $form->set('mailchimp_automation', $config['mailchimp_automation']);
    }

    echo output_header();

    $content = render(array(
        'template' => 'mailchimp_settings.php',
        'form' => $form));

    echo $form->prepare($content);

    echo output_footer();
    
    $form->remove();

// Otherwise the form has been submitted so process it.
} else {

    validate_token_field();
    
    $form->add_fields_to_session();

    $mailchimp = $form->get('mailchimp');
    $mailchimp_key = $form->get('mailchimp_key');
    $mailchimp_list_id = $form->get('mailchimp_list_id');
    $mailchimp_store_id = $form->get('mailchimp_store_id');
    $mailchimp_sync_days = $form->get('mailchimp_sync_days');
    $mailchimp_sync_limit = $form->get('mailchimp_sync_limit');
    $mailchimp_automation = $form->get('mailchimp_automation');

    if ($mailchimp) {
        
        $form->validate_required_field('mailchimp_key', 'API Key is required.');
        $form->validate_required_field('mailchimp_list_id', 'List ID is required.');
        $form->validate_required_field('mailchimp_store_id', 'Store ID is required.');

        // If there is an error, forward user back to previous screen.
        if ($form->check_form_errors()) {
            go($_SERVER['PHP_SELF']);
        }

        require_once(dirname(__FILE__) . '/mailchimp.php');

        // Check that API key is valid.
        $response = mailchimp_request(array(
            'path' => '/ping',
            'key' => $mailchimp_key));

        if ($response['status'] == 'error') {
            $form->mark_error('mailchimp_key', 'Sorry, the API key is not valid. ' .
                h($response['message']));
            go($_SERVER['PHP_SELF']);
        }

        // Check if List ID is valid.
        $response = mailchimp_request(array(
            'path' => '/lists/' . $mailchimp_list_id . '?fields=id',
            'key' => $mailchimp_key));

        if ($response['status'] == 'error') {
            $form->mark_error('mailchimp_list_id', 'Sorry, the List ID is not valid. ' .
                h($response['message']));
            go($_SERVER['PHP_SELF']);
        }

        // Check if store exists.
        $response = mailchimp_request(array(
            'path' => '/ecommerce/stores/' . $mailchimp_store_id . '?fields=id,is_syncing',
            'key' => $mailchimp_key,
            'quiet' => true));

        // If there was an error, and it wasn't just that the store doesn't exist, then show error.
        if ($response['status'] == 'error' and $response['mailchimp_response']['status'] != 404) {
            $form->mark_error('mailchimp_store_id', 'Sorry, the Store ID is not valid. ' .
                h($response['message']));
            go($_SERVER['PHP_SELF']);
        }

        // If the store does not exist, then create a store.
        if ($response['status'] == 'error' and $response['mailchimp_response']['status'] == 404) {

            $store = array();

            $store['id'] = $mailchimp_store_id;
            $store['list_id'] = $mailchimp_list_id;
            $store['name'] = HOSTNAME_SETTING;
            $store['platform'] = 'liveSite';
            $store['domain'] = HOSTNAME_SETTING;

            if ($mailchimp_automation) {
                $store['is_syncing'] = false;
            } else {
                $store['is_syncing'] = true;
            }

            $store['email_address'] = EMAIL_ADDRESS;
            $store['currency_code'] = BASE_CURRENCY_CODE;

            // Create the store.
            $response = mailchimp_request(array(
                'method' => 'post',
                'path' => '/ecommerce/stores',
                'data' => $store,
                'key' => $mailchimp_key));

            if ($response['status'] == 'error') {
                $form->mark_error('mailchimp_store_id', 'Sorry, the store could not be created. ' .
                    h($response['message']));
                go($_SERVER['PHP_SELF']);
            }

        // Otherwise, the store exists, so store data, so we have is_syncing info for below.
        } else {
            $store = $response['mailchimp_response'];
        }

        // If the automation and is_syncing do not match, then update is_syncing for the store.
        if (!$mailchimp_automation != $store['is_syncing']) {

            // is_syncing should have the opposite value of automation.
            $store['is_syncing'] = !$mailchimp_automation;

            $response = mailchimp_request(array(
                'method' => 'patch',
                'path' => '/ecommerce/stores/' . $mailchimp_store_id,
                'data' => $store,
                'key' => $mailchimp_key));

            if ($response['status'] == 'error') {
                $form->mark_error('mailchimp_automation', 'Sorry, automation could not be updated. ' .
                    h($response['message']));
                go($_SERVER['PHP_SELF']);
            }
        }
    }

    db("UPDATE config SET
        mailchimp = '" . e($mailchimp) . "',
        mailchimp_key = '" . e($mailchimp_key) . "',
        mailchimp_list_id = '" . e($mailchimp_list_id) . "',
        mailchimp_store_id = '" . e($mailchimp_store_id) . "',
        mailchimp_sync_days = '" . e($mailchimp_sync_days) . "',
        mailchimp_sync_limit = '" . e($mailchimp_sync_limit) . "',
        mailchimp_automation = '" . e($mailchimp_automation) . "'");

    $message = 'The MailChimp settings have been saved.';

    log_activity($message);

    $form->remove();
    $form = new liveform('mailchimp_settings');
    $form->add_notice($message);

    go($_SERVER['PHP_SELF']);
}