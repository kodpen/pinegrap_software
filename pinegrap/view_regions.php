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

$liveform = new liveform('view_regions');

$number_of_results = 0;

$filter = $_GET['filter'];

$filter_for_links = '&filter=' . $filter;
$output_filter_for_links = h($filter_for_links);

switch ($filter) {
    case 'all_ad_regions':
        
        // if sort was set, update session
        if (isset($_REQUEST['sort'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_ad_regions']['sort'] = $_REQUEST['sort'];

            // clear order
            $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'] = '';
        }
        
        // if order was set, update session
        if (isset($_REQUEST['order'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'] = $_REQUEST['order'];
        }
        
        // If there is a query then store it in a session
        if (isset($_REQUEST['query']) == true) {
            $_SESSION['software']['design']['view_regions']['all_ad_regions']['query'] = $_REQUEST['query'];
        }

        // If the user clicked on the clear button
        if (isset($_GET['clear']) == true) {
            $_SESSION['software']['design']['view_regions']['all_ad_regions']['query'] = '';
        }

        // if there is a search query, then prepare to output clear button
        if ((isset($_SESSION['software']['design']['view_regions']['all_ad_regions']['query']) == true) && ($_SESSION['software']['design']['view_regions']['all_ad_regions']['query'] != '')) {
            $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
        }
        
        // output value for the search field
        $output_search_value = $_SESSION['software']['design']['view_regions']['all_ad_regions']['query'];
        
        // Set the heading and subheading.
        $heading = 'All Ad Regions';
        $subheading = 'All ad regions that can be added to any page style and display rotating ads created by any site manager.';
        
        // Add the create button to the button bar.
        $button_bar_button = '<a href="add_ad_region.php">Create Ad Region</a>';
        
        // Set the sort option
        switch($_SESSION['software']['design']['view_regions']['all_ad_regions']['sort']) {
            case 'Name':
                $sort_column = 'name';
                break;
            case 'Display Type':
                $sort_column = 'display_type';
                break;
            case 'Created':
                $sort_column = 'created_timestamp';
                break;
            case 'Last Modified':
                $sort_column = 'last_modified_timestamp';
                break;
            default:
                $sort_column = 'last_modified_timestamp';
                $_SESSION['software']['design']['view_regions']['all_ad_regions']['sort'] = 'Last Modified';
        }
        
        if ($_SESSION['software']['design']['view_regions']['all_ad_regions']['order']) {
            $asc_desc = $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'];
        } elseif ($sort_column == 'last_modified_timestamp') {
            $asc_desc = 'desc';
            $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'] = 'desc';
        } else {
            $asc_desc = 'asc';
            $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'] = 'asc';
        }
        
        // get total number of ad regions
        $query = "SELECT COUNT(id) FROM ad_regions";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $all_regions = $row[0];
        
        // Output table headings
        $output_table = '
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['design']['view_regions']['all_ad_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Display Type', $_SESSION['software']['design']['view_regions']['all_ad_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'], $output_filter_for_links) . '</th>
                <th>Duration</th>
                <th style="text-align: center;">Autoplay</th>
                <th>Interval</th>
                <th style="text-align: center;"">Continuous</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['design']['view_regions']['all_ad_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_regions']['all_ad_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_ad_regions']['order'], $output_filter_for_links) . '</th>
            </tr>';

        $search_query = mb_strtolower($_SESSION['software']['design']['view_regions']['all_ad_regions']['query']);

        // create where clause for sql
        $sql_search = "(LOWER(CONCAT_WS(',', name, display_type, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape($search_query) . "%')";

        if (isset($_SESSION['software']['design']['view_regions']['all_ad_regions']['query'])) {
            // Get only the results the user wanted in the search.
            $where .= "WHERE $sql_search";
        }
        
        $query = 
            "SELECT 
                id,
                name,
                display_type,
                transition_duration,
                slideshow,
                slideshow_interval,
                slideshow_continuous,
                created_user.user_username as created_username,
                ad_regions.created_timestamp,
                last_modified_user.user_username as last_modified_username,
                ad_regions.last_modified_timestamp
            FROM ad_regions
            LEFT JOIN user as created_user ON ad_regions.created_user_id = created_user.user_id
            LEFT JOIN user as last_modified_user ON ad_regions.last_modified_user_id = last_modified_user.user_id
            $where
            ORDER BY " . $sort_column . " " . $asc_desc;
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        while ($row = mysqli_fetch_array($result)){
            $id = $row['id'];
            $name = $row['name'];
            $display_type = $row['display_type'];
            $transition_duration = $row['transition_duration'];
            $slideshow = $row['slideshow'];
            $slideshow_interval = $row['slideshow_interval'];
            $slideshow_continuous = $row['slideshow_continuous'];
            $created_timestamp = $row['created_timestamp'];
            $last_modified_timestamp = $row['last_modified_timestamp'];
            
            if (isset($row['created_username']) == TRUE) {
                $created_username = $row['created_username'];
            } else {
                $created_username = '[Unknown]';
            }
            
            if (isset($row['last_modified_username']) == TRUE) {
                $last_modified_username = $row['last_modified_username'];
            } else {
                $last_modified_username = '[Unknown]';
            }
            
            // output link url
            $output_link_url = 'edit_ad_region.php?id=' . $id;
            
            // Increment the amount of results
            $number_of_results++;
            
            // if the display type is static then set output value
            if ($display_type == 'static') {
                $output_display_type = 'Static';
                $output_transition_duration = '';
                $output_slideshow = '';
                $output_slideshow_interval = '';
                $output_slideshow_continuous = '';
                
            // else the display type is dynamic so set output value
            } else {
                $output_display_type = 'Dynamic';
                
                // if transition duration is 0, then set to empty string
                if ($transition_duration == 0) {
                    $output_transition_duration = '';
                    
                // else transition duration is not 0, so set to value
                } else {
                    $output_transition_duration = number_format($transition_duration);
                }
                
                $output_slideshow = '';
                $output_slideshow_interval = '';
                $output_slideshow_continuous = '';
                
                // if slideshow is enabled, then prepare to output slideshow values
                if ($slideshow == 1) {
                    $output_slideshow = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
                    $output_slideshow_interval = $slideshow_interval;

                    // If the slideshow is continuous, then output check mark.
                    if ($slideshow_continuous == 1) {
                        $output_slideshow_continuous = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
                    }
                }
            }
            
            // Output table.
            $output_table .= '
                <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <td class="chart_label">' . h($name) . '</td>
                    <td>' . $output_display_type . '</td>
                    <td>' . $output_transition_duration . '</td>
                    <td nowrap style="text-align: center">' . $output_slideshow . '</td>
                    <td>' . $output_slideshow_interval . '</td>
                    <td nowrap style="text-align: center">' . $output_slideshow_continuous . '</td>
                    <td>' . get_relative_time(array('timestamp' => $created_timestamp)) . ' by ' . h($created_username) . '</td>
                    <td>' . get_relative_time(array('timestamp' => $last_modified_timestamp)) . ' by ' . h($last_modified_username) . '</td>
                </tr>';
        }
        break;
        
    case 'all_login_regions':
        
        // if sort was set, update session
        if (isset($_REQUEST['sort'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'] = $_REQUEST['sort'];

            // clear order
            $_SESSION['software']['design']['view_regions']['all_login_regions']['order'] = '';
        }
        
        // if order was set, update session
        if (isset($_REQUEST['order'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_login_regions']['order'] = $_REQUEST['order'];
        }
        
        // If there is a query then store it in a session
        if (isset($_REQUEST['query']) == true) {
            $_SESSION['software']['design']['view_regions']['all_login_regions']['query'] = $_REQUEST['query'];
        }

        // If the user clicked on the clear button
        if (isset($_GET['clear']) == true) {
            $_SESSION['software']['design']['view_regions']['all_login_regions']['query'] = '';
        }

        // if there is a search query, then prepare to output clear button
        if ((isset($_SESSION['software']['design']['view_regions']['all_login_regions']['query']) == true) && ($_SESSION['software']['design']['view_regions']['all_login_regions']['query'] != '')) {
            $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
        }
        
        // output value for the search field
        $output_search_value = $_SESSION['software']['design']['view_regions']['all_login_regions']['query'];
        
        // Set the heading and subheading.
        $heading = 'All Login Regions';
        $subheading = 'All login regions that add the login process to any page.';
        
        // Add the create button to the button bar.
        $button_bar_button = '<a href="add_login_region.php">Create Login Region</a>';
        
        // Set the sort option
        switch($_SESSION['software']['design']['view_regions']['all_login_regions']['sort']) {
            case 'Name':
                $sort_column = 'name';
                break;
            case 'Not Logged In Header':
                $sort_column = 'not_logged_in_header';
                break;
            case 'Show Login Form':
                $sort_column = 'login_form';
                break;
            case 'Not Logged In Footer':
                $sort_column = 'not_logged_in_footer';
                break;
            case 'Logged In Header':
                $sort_column = 'logged_in_header';
                break;
            case 'Logged In Footer':
                $sort_column = 'logged_in_footer';
                break;
            case 'Created':
                $sort_column = 'created_timestamp';
                break;
            case 'Last Modified':
                $sort_column = 'last_modified_timestamp';
                break;
            default:
                $sort_column = 'last_modified_timestamp';
                $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'] = 'Last Modified';
        }
        
        if ($_SESSION['software']['design']['view_regions']['all_login_regions']['order']) {
            $asc_desc = $_SESSION['software']['design']['view_regions']['all_login_regions']['order'];
        } elseif ($sort_column == 'last_modified_timestamp') {
            $asc_desc = 'desc';
            $_SESSION['software']['design']['view_regions']['all_login_regions']['order'] = 'desc';
        } else {
            $asc_desc = 'asc';
            $_SESSION['software']['design']['view_regions']['all_login_regions']['order'] = 'asc';
        }
        
        // get total number of login regions
        $query = "SELECT COUNT(id) FROM login_regions";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $all_regions = $row[0];
        
        // Output table headings
        $output_table = '            
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Not Logged In Header', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
                <th style="text-align: center; white-space: nowrap">' . get_column_heading('Show Login Form', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Not Logged In Footer', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Logged In Header', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Logged In Footer', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_regions']['all_login_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_login_regions']['order'], $output_filter_for_links) . '</th>
            </tr>
            ';

        
        $search_query = mb_strtolower($_SESSION['software']['design']['view_regions']['all_login_regions']['query']);

        // create where clause for sql
        $sql_search = "(LOWER(CONCAT_WS(',', name, not_logged_in_header, not_logged_in_footer, logged_in_header, logged_in_footer, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape($search_query) . "%')";

        if (isset($_SESSION['software']['design']['view_regions']['all_login_regions']['query'])) {
            // Get only the results the user wanted in the search.
            $where .= "WHERE $sql_search";
        }
        
        $query = 
            "SELECT 
                id,
                name,
                not_logged_in_header,
                login_form,
                not_logged_in_footer,
                logged_in_header,
                logged_in_footer,
                created_timestamp,
                last_modified_timestamp,
                created_user.user_username as created_username,
                last_modified_user.user_username as last_modified_username
            FROM login_regions
            LEFT JOIN user as created_user ON login_regions.created_user_id = created_user.user_id
            LEFT JOIN user as last_modified_user ON login_regions.last_modified_user_id = last_modified_user.user_id
            $where
            ORDER BY " . $sort_column . " " . $asc_desc;
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        while ($row = mysqli_fetch_array($result)){
            $id = $row['id'];
            $name = $row['name'];
            $not_logged_in_header = $row['not_logged_in_header'];
            $login_form = $row['login_form'];
            $not_logged_in_footer = $row['not_logged_in_footer'];
            $logged_in_header = $row['logged_in_header'];
            $logged_in_footer = $row['logged_in_footer'];
            $created_timestamp = $row['created_timestamp'];
            $last_modified_timestamp = $row['last_modified_timestamp'];
            
            if (isset($row['created_username']) == TRUE) {
                $created_username = $row['created_username'];
            } else {
                $created_username = '[Unknown]';
            }
            
            if (isset($row['last_modified_username']) == TRUE) {
                $last_modified_username = $row['last_modified_username'];
            } else {
                $last_modified_username = '[Unknown]';
            }
            
            // if not_logged_in_header is longer than 50 characters
            if (mb_strlen($not_logged_in_header) > 50) {
                $not_logged_in_header = h(mb_substr($not_logged_in_header, 0, 50) . '...');
            }
            
            // if login form is enabled, then output check mark
            if ($login_form == 1) {
                $login_form = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
                
            // else login form is disabled, so do not output check mark
            } else {
                $login_form = '';
            }
            
            // if not_logged_in_footer is longer than 50 characters
            if (mb_strlen($not_logged_in_footer) > 50) {
                $not_logged_in_footer = h(mb_substr($not_logged_in_footer, 0, 50) . '...');
            }
            
            // if logged_in_header is longer than 50 characters
            if (mb_strlen($logged_in_header) > 50) {
                $logged_in_header = h(mb_substr($logged_in_header, 0, 50) . '...');
            }
            
            // if logged_in_footer is longer than 50 characters
            if (mb_strlen($logged_in_footer) > 100) {
                $logged_in_footer = h(mb_substr($logged_in_footer, 0, 50) . '...');
            }
            
            // output link url
            $output_link_url = 'edit_login_region.php?id=' . $id;
            
            // Increment the amount of results
            $number_of_results++;
            
            // Output table.
            $output_table .= '
                <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <td class="chart_label">' . h($name) . '</td>
                    <td>' . $not_logged_in_header . '</td>
                    <td style="text-align: center">' . $login_form . '</td>
                    <td>' . $not_logged_in_footer . '</td>
                    <td>' . $logged_in_header . '</td>
                    <td>' . $logged_in_footer . '</td>
                    <td>' . get_relative_time(array('timestamp' => $created_timestamp)) . ' by ' . h($created_username) . '</td>
                    <td>' . get_relative_time(array('timestamp' => $last_modified_timestamp)) . ' by ' . h($last_modified_username) . '</td>
                </tr>';
        }
        break;
    case 'all_dynamic_regions':
        
        // if sort was set, update session
        if (isset($_REQUEST['sort'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['sort'] = $_REQUEST['sort'];

            // clear order
            $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'] = '';
        }
        
        // if order was set, update session
        if (isset($_REQUEST['order'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'] = $_REQUEST['order'];
        }
        
        // If there is a query then store it in a session
        if (isset($_REQUEST['query']) == true) {
            $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['query'] = $_REQUEST['query'];
        }

        // If the user clicked on the clear button
        if (isset($_GET['clear']) == true) {
            $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['query'] = '';
        }

        // if there is a search query, then prepare to output clear button
        if ((isset($_SESSION['software']['design']['view_regions']['all_dynamic_regions']['query']) == true) && ($_SESSION['software']['design']['view_regions']['all_dynamic_regions']['query'] != '')) {
            $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
        }
        
        // output value for the search field
        $output_search_value = $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['query'];
        
        // Set the heading and subheading.
        $heading = 'All Dynamic Regions';
        $subheading = 'All dynamic regions that contain PHP code and can be added to any page style.';
        
        // Add the create button to the button bar.
        $button_bar_button = '<a href="add_dynamic_region.php">Create Dynamic Region</a>';
        
        // output dynamic regions
        // if user's role is above a designer role then output dynamic region area
        if ($user['role'] < 1) {
            // Set the sort option
            switch($_SESSION['software']['design']['view_regions']['all_dynamic_regions']['sort']) {
                case 'Name':
                    $sort_column = 'dregion_name';
                    break;
                case 'Code Preview':
                    $sort_column = 'dregion_code';
                    break;
                case 'Last Modified':
                    $sort_column = 'dregion_timestamp';
                    break;
                default:
                    $sort_column = 'dregion_timestamp';
                    $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['sort'] = 'Last Modified';
            }
            
            if ($_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order']) {
                $asc_desc = $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'];
            } elseif ($sort_column == 'dregion_timestamp') {
                $asc_desc = 'desc';
                $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'] = 'desc';
            } else {
                $asc_desc = 'asc';
                $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'] = 'asc';
            }
            
            // get total number of dynamic regions
            $query = "SELECT COUNT(dregion_id) FROM dregion";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            $all_regions = $row[0];
            
            // Output table headings
            $output_table .= '
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Code Preview', $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'], $output_filter_for_links) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_dynamic_regions']['order'], $output_filter_for_links) . '</th>
            </tr>
            ';
            
            $search_query = mb_strtolower($_SESSION['software']['design']['view_regions']['all_dynamic_regions']['query']);
            
            // create where clause for sql
            $sql_search = "(LOWER(CONCAT_WS(',', dregion_name, dregion_code, last_modified_user.user_username)) LIKE '%" . escape($search_query) . "%')";
            
            if (isset($_SESSION['software']['design']['view_regions']['all_dynamic_regions']['query'])) {
                // Get only the results the user wanted in the search.
                $where .= "WHERE $sql_search";
            }
            
            $query = 
                "SELECT 
                    dregion_id,
                    dregion_name,
                    dregion_code,
                    dregion_timestamp,
                    last_modified_user.user_username as user_username
                FROM dregion
                LEFT JOIN user as last_modified_user ON dregion.dregion_user = last_modified_user.user_id
                $where
                ORDER BY $sort_column $asc_desc";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            
            while ($row = mysqli_fetch_array($result)){
                
                if (isset($row['user_username']) == TRUE) {
                    $user_username = $row['user_username'];
                } else {
                    $user_username = '[Unknown]';
                }
                
                // get preview of content
                $code_preview = h(mb_substr($row['dregion_code'], 0, 50));
                
                // Output link url
                $output_link_url = 'edit_dynamic_region.php?id='.$row['dregion_id'];
                
                // Increment the amount of results
                $number_of_results++;
                
                // Output table
                $output_table .= '
                <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <td class="chart_label">' . h($row['dregion_name']) . '</td>
                    <td>'.$code_preview.'</td>
                    <td>'. get_relative_time(array('timestamp' => $row['dregion_timestamp'])) .' by '.h($user_username).'</td>
                </tr>';
            }
        }
        break;

    case 'all_designer_regions':
        
        // if sort was set, update session
        if (isset($_REQUEST['sort'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_designer_regions']['sort'] = $_REQUEST['sort'];

            // clear order
            $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'] = '';
        }
        
        // if order was set, update session
        if (isset($_REQUEST['order'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'] = $_REQUEST['order'];
        }
        
        // If there is a query then store it in a session
        if (isset($_REQUEST['query']) == true) {
            $_SESSION['software']['design']['view_regions']['all_designer_regions']['query'] = $_REQUEST['query'];
        }

        // If the user clicked on the clear button
        if (isset($_GET['clear']) == true) {
            $_SESSION['software']['design']['view_regions']['all_designer_regions']['query'] = '';
        }

        // if there is a search query, then prepare to output clear button
        if ((isset($_SESSION['software']['design']['view_regions']['all_designer_regions']['query']) == true) && ($_SESSION['software']['design']['view_regions']['all_designer_regions']['query'] != '')) {
            $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
        }
        
        // output value for the search field
        $output_search_value = $_SESSION['software']['design']['view_regions']['all_designer_regions']['query'];
        
        // Set the heading and subheading.
        $heading = 'All Designer Regions';
        $subheading =
            'All designer regions of shared content that can be added to any page style and updated during page editing by any Site Designer.';
        
        // Add the create button to the button bar.
        $button_bar_button = '<a href="add_designer_region.php">Create Designer Region</a>';
        
        // Set the sort option
        switch($_SESSION['software']['design']['view_regions']['all_designer_regions']['sort']) {
            case 'Name':
                $sort_column = 'cregion_name';
                break;
            case 'Content Preview':
                $sort_column = 'cregion_content';
                break;
            case 'Last Modified':
                $sort_column = 'cregion_timestamp';
                break;
            default:
                $sort_column = 'cregion_timestamp';
                $_SESSION['software']['design']['view_regions']['all_designer_regions']['sort'] = 'Last Modified';
        }

        if ($_SESSION['software']['design']['view_regions']['all_designer_regions']['order']) {
            $asc_desc = $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'];
        } elseif ($sort_column == 'cregion_timestamp') {
            $asc_desc = 'desc';
            $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'] = 'desc';
        } else {
            $asc_desc = 'asc';
            $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'] = 'asc';
        }
        
        // Get total number of designer regions.
        $query = "SELECT COUNT(cregion_id) FROM cregion WHERE cregion_designer_type = 'yes'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $all_regions = $row[0];
        
        // Output table headings
        $output_table = '
        <tr>
            <th>' . get_column_heading('Name', $_SESSION['software']['design']['view_regions']['all_designer_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'], $output_filter_for_links) . '</th>
            <th>' . get_column_heading('Content Preview', $_SESSION['software']['design']['view_regions']['all_designer_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'], $output_filter_for_links) . '</th>
            <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_regions']['all_designer_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_designer_regions']['order'], $output_filter_for_links) . '</th>
        </tr>';
        
        $search_query = mb_strtolower($_SESSION['software']['design']['view_regions']['all_designer_regions']['query']);

        // create where clause for sql
        $sql_search = "(LOWER(CONCAT_WS(',', cregion_name, cregion_content, last_modified_user.user_username)) LIKE '%" . escape($search_query) . "%')";

        if (isset($_SESSION['software']['design']['view_regions']['all_designer_regions']['query'])) {
            // Get only the results the user wanted in the search.
            $where = "WHERE $sql_search AND cregion_designer_type = 'yes'";
        } else {
            // And fetch only designer regions
            $where = "WHERE cregion_designer_type = 'yes'";
        }

        // Query to get the database information
        $query = 
            "SELECT 
                cregion_id,
                cregion_name,
                cregion_content,
                cregion_timestamp,
                cregion_designer_type,
                last_modified_user.user_username as user_username
            FROM cregion
            LEFT JOIN user as last_modified_user ON cregion.cregion_user = last_modified_user.user_id
            $where
            ORDER BY $sort_column $asc_desc";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        while ($row = mysqli_fetch_array($result)){
            
            if (isset($row['user_username']) == TRUE) {
                $user_username = $row['user_username'];
            } else {
                $user_username = '[Unknown]';
            }
            
            $content_preview = h(mb_substr($row['cregion_content'], 0, 50));

            $output_link_url = 'edit_designer_region.php?id=' . $row['cregion_id'];
            
            // Increment the amount of results
            $number_of_results++;
            
            // Output table
            $output_table .= '
                <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <td class="chart_label">' . h($row['cregion_name']) . '</td>
                    <td>' . $content_preview . '</td>
                    <td>' . get_relative_time(array('timestamp' => $row['cregion_timestamp'])) .' by '. h($user_username) .'</td>
                </tr>';
        }
        break;

    case 'all_common_regions':
    default:
        
        // if sort was set, update session
        if (isset($_REQUEST['sort'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_common_regions']['sort'] = $_REQUEST['sort'];

            // clear order
            $_SESSION['software']['design']['view_regions']['all_common_regions']['order'] = '';
        }
        
        // if order was set, update session
        if (isset($_REQUEST['order'])) {
            // store sort in session
            $_SESSION['software']['design']['view_regions']['all_common_regions']['order'] = $_REQUEST['order'];
        }
        
        // If there is a query then store it in a session
        if (isset($_REQUEST['query']) == true) {
            $_SESSION['software']['design']['view_regions']['all_common_regions']['query'] = $_REQUEST['query'];
        }

        // If the user clicked on the clear button
        if (isset($_GET['clear']) == true) {
            $_SESSION['software']['design']['view_regions']['all_common_regions']['query'] = '';
        }

        // if there is a search query, then prepare to output clear button
        if ((isset($_SESSION['software']['design']['view_regions']['all_common_regions']['query']) == true) && ($_SESSION['software']['design']['view_regions']['all_common_regions']['query'] != '')) {
            $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
        }
        
        // output value for the search field
        $output_search_value = $_SESSION['software']['design']['view_regions']['all_common_regions']['query'];
        
        // Set the heading and subheading.
        $heading = 'All Common Regions';
        $subheading =
            'All common regions of shared content that can be added to any page style and updated during page editing by any site manager.';
        
        // Add the create button to the button bar.
        $button_bar_button = '<a href="add_common_region.php">Create Common Region</a>';
        
        // Set the sort option
        switch($_SESSION['software']['design']['view_regions']['all_common_regions']['sort']) {
            case 'Name':
                $sort_column = 'cregion_name';
                break;
            case 'Content Preview':
                $sort_column = 'cregion_content';
                break;
            case 'Last Modified':
                $sort_column = 'cregion_timestamp';
                break;
            default:
                $sort_column = 'cregion_timestamp';
                $_SESSION['software']['design']['view_regions']['all_common_regions']['sort'] = 'Last Modified';
        }

        if ($_SESSION['software']['design']['view_regions']['all_common_regions']['order']) {
            $asc_desc = $_SESSION['software']['design']['view_regions']['all_common_regions']['order'];
        } elseif ($sort_column == 'cregion_timestamp') {
            $asc_desc = 'desc';
            $_SESSION['software']['design']['view_regions']['all_common_regions']['order'] = 'desc';
        } else {
            $asc_desc = 'asc';
            $_SESSION['software']['design']['view_regions']['all_common_regions']['order'] = 'asc';
        }
        
        // Get total number of common regions.
        $query = "SELECT COUNT(cregion_id) FROM cregion WHERE cregion_designer_type = 'no'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $all_regions = $row[0];
        
        // Output table headings
        $output_table = '
        <tr>
            <th>' . get_column_heading('Name', $_SESSION['software']['design']['view_regions']['all_common_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_common_regions']['order'], $output_filter_for_links) . '</th>
            <th>' . get_column_heading('Content Preview', $_SESSION['software']['design']['view_regions']['all_common_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_common_regions']['order'], $output_filter_for_links) . '</th>
            <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_regions']['all_common_regions']['sort'], $_SESSION['software']['design']['view_regions']['all_common_regions']['order'], $output_filter_for_links) . '</th>
        </tr>';
        
        $search_query = mb_strtolower($_SESSION['software']['design']['view_regions']['all_common_regions']['query']);

        // create where clause for sql
        $sql_search = "(LOWER(CONCAT_WS(',', cregion_name, cregion_content, last_modified_user.user_username)) LIKE '%" . escape($search_query) . "%')";

        if (isset($_SESSION['software']['design']['view_regions']['all_common_regions']['query'])) {
            // Get only the results the user wanted in the search.
            $where = "WHERE $sql_search AND cregion_designer_type = 'no'";
        } else {
            // And fetch only designer regions
            $where = "WHERE cregion_designer_type = 'no'";
        }

        // Query to get the database information
        $query = 
            "SELECT
                cregion_id,
                cregion_name,
                cregion_content,
                cregion_timestamp,
                cregion_designer_type,
                last_modified_user.user_username as user_username
            FROM cregion
            LEFT JOIN user as last_modified_user ON cregion.cregion_user = last_modified_user.user_id
            $where
            ORDER BY $sort_column $asc_desc";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        while ($row = mysqli_fetch_array($result)){

            if (isset($row['user_username']) == TRUE) {
                $user_username = $row['user_username'];
            } else {
                $user_username = '[Unknown]';
            }
            
            $content_preview = h(mb_substr($row['cregion_content'], 0, 50));
            
            $output_link_url = 'edit_common_region.php?id=' . $row['cregion_id'];
            
            // Increment the amount of results
            $number_of_results++;
            
            // Output table
            $output_table .= '
                <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <td class="chart_label">' . h($row['cregion_name']) . '</td>
                    <td>' . $content_preview . '</td>
                    <td>' . get_relative_time(array('timestamp' => $row['cregion_timestamp'])) .' by '. h($user_username) .'</td>
                </tr>';
        }
        break;
}

echo
    output_header() . '
    <div id="subnav">
        ' . get_design_subnav() . '
    </div>
    <div id="button_bar">
        ' . $button_bar_button . '
    </div>
    <div id="content">
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>' . $heading . '</h1>
        <div class="subheading">' . $subheading . '</div>
        <form action="view_regions.php" method="get" class="search_form">
            <input type="hidden" name="filter" value="' . h($filter) . '">
            <input type="text" name="query" value="' . h($output_search_value) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_regions) . ' Total
        </div>
        <table class="chart">
            ' . $output_table . '
        </table>
    </div> ' .
    output_footer();

$liveform->remove_form();