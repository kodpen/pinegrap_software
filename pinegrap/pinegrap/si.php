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

echo output_header_secure();

echo render(array(
    'template' => 'si.php',
    'disk_usage' => db("SELECT SUM(size) FROM files")));

echo output_footer_secure();