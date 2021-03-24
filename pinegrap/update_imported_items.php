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

// This function is used by the import design and import zip features
// to update all references to items in the content of all imported items.
function update_imported_items($imported_items)
{
    // Loop through the imported items in order to update references to other items,
    // so the references work with our paths and reference the new item names.
    foreach ($imported_items as $item) {
        // If this is an HTML, CSS, or JS item, then update content.
        if (($item['type'] == 'html') || ($item['type'] == 'css') || ($item['type'] == 'js')) {
            $base_url = $item['base_url'];

            // If this is an HTML item then get content from the style.
            if ($item['type'] == 'html') {
                $content = db_value("SELECT style_code FROM style WHERE style_id = '" . $item['style_id'] . "'");

            // Otherwise this is a CSS or JS file, so get content from file.
            } else {
                $content = @file_get_contents(FILE_DIRECTORY_PATH . '/' . $item['name']);
            }

            // If the type is html, then crawl various items in the HTML.
            if ($item['type'] == 'html') {
                // Remove link canonical and og:url tags from the HTML, because they probably
                // won't be correct for this system.
                $content = preg_replace('/<link\s+[^>]*rel\s*=\s*["\']\s*canonical\s*["\'].*?>/is', '', $content);
                $content = preg_replace('/<meta\s+[^>]*property\s*=\s*["\']\s*og:url\s*["\'].*?>/is', '', $content);

                preg_match_all('/<\s*img\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = unhtmlspecialchars(trim($match[1]));
                    $item_url = url_to_absolute($base_url, $old_url);
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }

                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr($old_url, 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $new_url = '{path}' . h(encode_url_path($item_name));

                        // Otherwise a file name was not returned, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = h(get_protocol_relative_url($old_url));
                        }

                        $new_content = preg_replace('/src\s*=\s*["\'].*?["\']/is', 'src="' . $new_url . '"', $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }

                preg_match_all('/<\s*link\s+[^>]*href\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = unhtmlspecialchars(trim($match[1]));
                    $item_url = url_to_absolute($base_url, $old_url);
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }
                    
                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr($old_url, 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $new_url = '{path}' . h(encode_url_path($item_name));

                        // Otherwise an item name was not found, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = h(get_protocol_relative_url($old_url));
                        }

                        $new_content = preg_replace('/href\s*=\s*["\'].*?["\']/is', 'href="' . $new_url . '"', $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }

                preg_match_all('/<\s*script\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = unhtmlspecialchars(trim($match[1]));
                    $item_url = url_to_absolute($base_url, $old_url);
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }

                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr($old_url, 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $new_url = '{path}' . h(encode_url_path($item_name));

                        // Otherwise an item name was not found, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = h(get_protocol_relative_url($old_url));
                        }

                        $new_content = preg_replace('/src\s*=\s*["\'].*?["\']/is', 'src="' . $new_url . '"', $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }

                preg_match_all('/<\s*source\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = unhtmlspecialchars(trim($match[1]));
                    $item_url = url_to_absolute($base_url, $old_url);
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }

                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr($old_url, 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $new_url = '{path}' . h(encode_url_path($item_name));

                        // Otherwise an item name was not found, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = h(get_protocol_relative_url($old_url));
                        }

                        $new_content = preg_replace('/src\s*=\s*["\'].*?["\']/is', 'src="' . $new_url . '"', $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }

                preg_match_all('/<\s*embed\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = unhtmlspecialchars(trim($match[1]));
                    $item_url = url_to_absolute($base_url, $old_url);
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }

                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr($old_url, 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $new_url = '{path}' . h(encode_url_path($item_name));

                        // Otherwise an item name was not found, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = h(get_protocol_relative_url($old_url));
                        }

                        $new_content = preg_replace('/src\s*=\s*["\'].*?["\']/is', 'src="' . $new_url . '"', $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }

                preg_match_all('/<\s*object\s+[^>]*data\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = unhtmlspecialchars(trim($match[1]));
                    $item_url = url_to_absolute($base_url, $old_url);
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }
                    
                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr($old_url, 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $new_url = '{path}' . h(encode_url_path($item_name));

                        // Otherwise an item name was not found, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = h(get_protocol_relative_url($old_url));
                        }

                        $new_content = preg_replace('/data\s*=\s*["\'].*?["\']/is', 'data="' . $new_url . '"', $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }

                preg_match_all('/<\s*a\s+[^>]*href\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $item_url = unhtmlspecialchars(trim($match[1]));

                    // If the anchor only contains a bookmark (e.g. #example),
                    // then don't do anything to this anchor.
                    if (mb_substr($item_url, 0, 1) == '#') {
                        continue;
                    }

                    $item_url = url_to_absolute($base_url, $item_url);
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }

                    // If an item name was found, then update the URL.
                    if ($item_name != '') {
                        $bookmark = parse_url($item_url, PHP_URL_FRAGMENT);

                        // If there is a bookmark, then add a "#" to the beginning
                        // so it is ready to be added to the new URL.
                        if ($bookmark != '') {
                            $bookmark = '#' . $bookmark;
                        }

                        $new_content = preg_replace('/href\s*=\s*["\'].*?["\']/is', 'href="{path}' . h(encode_url_path($item_name) . $bookmark) . '"', $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }
            }

            // If the type is HTML or CSS then crawl for CSS items.
            if (($item['type'] == 'html') || ($item['type'] == 'css')) {
                // Crawl all URL references first.  This includes import statements
                // that use "url" syntax, background images, and etc.

                preg_match_all('/url\(\s*["\']?(.*?)["\']?\s*\)/i', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = $match[1];
                    $item_url = url_to_absolute($base_url, trim($old_url));
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }
                    
                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr(trim($old_url), 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $bookmark = parse_url($item_url, PHP_URL_FRAGMENT);

                            // If there is a bookmark, then add a "#" to the beginning
                            // so it is ready to be added to the new URL.
                            if ($bookmark != '') {
                                $bookmark = '#' . $bookmark;
                            }

                            $new_url = '{path}' . encode_url_path($item_name) . $bookmark;

                        // Otherwise an item name was not found, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = get_protocol_relative_url(trim($old_url));
                        }

                        $new_content = str_replace($old_url, $new_url, $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }

                // Crawl import statements that use @import "example.css" syntax without "url()".

                preg_match_all('/@import\s*["\'](.*?)["\']/i', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = $match[1];
                    $item_url = url_to_absolute($base_url, trim($old_url));
                    $item_url_without_bookmark = strtok($item_url, '#');
                    $item_name = $imported_items[$item_url_without_bookmark]['name'];

                    // If an imported item was not found for the item URL,
                    // then let's remove the query string also and see if we can find
                    // an imported item for this url without the query string.
                    if ($item_name == '') {
                        $item_url_without_query = strtok($item_url_without_bookmark, '?');
                        $item_name = $imported_items[$item_url_without_query]['name'];
                    }
                    
                    // If an item name was found, or the old URL starts with "http://"
                    // then change the URL in the content.
                    if (
                        ($item_name != '')
                        || (mb_substr(trim($old_url), 0, 7) == 'http://')
                    ) {
                        // If an item name was found, then set the new url to the new path.
                        if ($item_name != '') {
                            $new_url = '{path}' . encode_url_path($item_name);

                        // Otherwise an item name was not found, so change http:// in the front
                        // of the URL to a protocol-relative URL (i.e. //example).
                        } else {
                            $new_url = get_protocol_relative_url(trim($old_url));
                        }

                        $new_content = str_replace($old_url, $new_url, $old_content);

                        $content = str_replace($old_content, $new_content, $content);
                    }
                }
            }

            // If the type is HTML or JS then crawl resources in the JS.
            // This means that we look for files in <script> tags, inline JS (e.g. onmouseover="example")
            // and in the JS files themselves.
            if (($item['type'] == 'html') || ($item['type'] == 'js')) {
                // We just look for any resource enclosed in quotes.
                // We have added support for an optional query string part (e.g. ?name=value)
                // that can appear after the file extension.
                preg_match_all('/["\']\s*([^"\']*\.(css|js|gif|jpg|jpeg|png)(\?.*?)?)\s*["\']/i', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $old_content = $match[0];
                    $old_url = $match[1];

                    // If this item has not been crawled already from code up above.
                    // (e.g. not: a href, img src, etc.) then continue to crawl it.
                    // We just want to deal with files in js content.
                    if (mb_strpos($old_url, '{path}') === false) {
                        $item_url = url_to_absolute($base_url, $old_url);
                        $item_name = $imported_items[$item_url]['name'];
                        
                        // If an item name was found, or the old URL starts with "http://"
                        // then change the URL in the content.
                        if (
                            ($item_name != '')
                            || (mb_substr($old_url, 0, 7) == 'http://')
                        ) {
                            // If an item name was found, then set the new url to the new path.
                            if ($item_name != '') {
                                $new_url = '{path}' . encode_url_path($item_name);

                            // Otherwise an item name was not found, so change http:// in the front
                            // of the URL to a protocol-relative URL (i.e. //example).
                            } else {
                                $new_url = get_protocol_relative_url($old_url);
                            }

                            $new_content = str_replace($old_url, $new_url, $old_content);

                            $content = str_replace($old_content, $new_content, $content);
                        }
                    }
                }
            }

            // If this is an HTML item then update content for style.
            if ($item['type'] == 'html') {
                db(
                    "UPDATE style
                    SET
                        style_code = '" . escape($content) . "',
                        style_user = '" . USER_ID . "',
                        style_timestamp = UNIX_TIMESTAMP()
                    WHERE style_id = '" . $item['style_id'] . "'");

            // Otherwise this is a CSS or JS file, so update content in file.
            } else {
                // Update content of the file on the file system.
                @file_put_contents(FILE_DIRECTORY_PATH . '/' . $item['name'], $content);

                // Update file size and last modified info in db.
                db(
                    "UPDATE files
                    SET
                        size = '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $item['name'])) . "',
                        user = '" . USER_ID . "',
                        timestamp = UNIX_TIMESTAMP()
                    WHERE id = '" . $item['file_id'] . "'");
            }
        }
    }
}
?>