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

require_once(dirname(__FILE__) . '/get_sitemap_info.php');

$sitemap_info = get_sitemap_info();

header('Content-type: text/xml');
echo $sitemap_info['content'];