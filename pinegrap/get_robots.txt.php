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

$content = 'Sitemap: ' . URL_SCHEME . HOSTNAME_SETTING . PATH . 'sitemap.xml';

// get additional robots.txt content
$query = "SELECT additional_robots_content FROM config";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$additional_robots_content = $row['additional_robots_content'];

// if there is additional robots content then add it to the content
if ($additional_robots_content != '') {
    $content .=
        "\r\n" .
        "\r\n" .
        $additional_robots_content;
}

header('Content-type: text/plain');
print $content;
?>