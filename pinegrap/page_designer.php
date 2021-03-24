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

$software_title = '';

if (PRIVATE_LABEL == FALSE) {
    $software_title = 'Pinegrap - ';
}

if ($_SESSION['software']['page_designer']['cursor_positions']) {
    $output_cursor_positions = encode_json($_SESSION['software']['page_designer']['cursor_positions']);
} else {
    $output_cursor_positions = '{}';
}

echo
    '<!DOCTYPE html>
    <html lang="en" class="page_designer">
        <head>
            <meta charset="utf-8">
            <title>' . $software_title . h($_SERVER['HTTP_HOST']) . '</title>
            ' . get_generator_meta_tag() . '
            ' . output_control_panel_header_includes() . '
            ' . get_codemirror_includes() . '
        </head>
        <body class="page_designer">
            <div class="panels"></div>
            <script>
                init_page_designer({
                    initial_panels: [
                        {
                            type: "preview",
                            url: "' . escape_javascript($_GET['url']) . '"
                        },

                        {
                            type: "code"
                        },

                        {
                            type: "tool"
                        }
                    ],
                    preview_panel_width: ' . intval($_SESSION['software']['page_designer']['preview_panel_width']) . ',
                    code_panel_width: ' . intval($_SESSION['software']['page_designer']['code_panel_width']) . ',
                    tool_panel_width: ' . intval($_SESSION['software']['page_designer']['tool_panel_width']) . ',
                    starting_item_type: "' . escape_javascript($_GET['type']) . '",
                    starting_item_id: "' . escape_javascript($_GET['id']) . '",
                    cursor_positions: ' . $output_cursor_positions . ',
                    query: "' . escape_javascript($_SESSION['software']['page_designer']['query']) . '"
                })
            </script>
        </body>
    </html>';