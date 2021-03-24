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

// Find and replace multiple keywords in some content.

function find_replace($request) {

    $content = $request['content'];
    $keywords = $request['keywords'];

    foreach ($keywords as $keyword) {
        $content = str_replace($keyword['find'], $keyword['replace'], $content);
    }

    return $content;
}