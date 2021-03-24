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

validate_area_access($user, 'user');

validate_token_field();

$liveform_edit_page = new liveform('edit_page');

require_once(dirname(__FILE__) . '/duplicate_page_f.php');

$page['id'] = $_GET['id'];

$response = duplicate_page(array('page' => $page));

if ($response['status'] == 'error') {
    output_error(h($response['message']));
}

$new_page = $response['page'];

// add notice that page has been saved
$liveform_edit_page->add_notice('The page has been duplicated. You are now editing the duplicate.');
                
header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $new_page['id'] . '&from=' . $_GET['from']);