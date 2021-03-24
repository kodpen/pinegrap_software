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
validate_area_access($user, 'designer');

$form = new liveform('generate_layout');

$page = db_item("SELECT page_name AS name FROM page WHERE page_id = '" . e($_GET['page_id']) . "'");

require('generate_layout_content.php');

$form->set('layout', "\n" . generate_layout_content($_GET['page_id']));

echo output_header();

require('templates/generate_layout.php');

echo output_footer();

$form->remove();