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

$liveform = new liveform('view_pages');
$user = validate_user();
validate_area_access($user, 'user');

$output_auto_dialogs = '';

if (AUTO_DIALOGS && (USER_ROLE < 3)) {
    $output_auto_dialogs =
        '<td>
            <ul>
                <li><a href="view_auto_dialogs.php">Auto Dialogs</a></li>
            </ul>
        </td>';
}

$output_clear_button = '';

// If there is a filter set.
if (isset($_GET['filter']) == true) {
    // Send the filter to the search form.
    $filter = $_GET['filter'];
} else {
    $filter = 'default';
}

$filter_for_links = '&filter=' . $filter;
$output_filter_for_links = h($filter_for_links);

// build filters array
$filters_in_array = 
    array(
        'all_my_pages'=>'All My Pages',
        'all_my_archived_pages'=>'All My Archived Pages',
        'my_home_pages'=>'My Home Pages',
        'my_searchable_pages'=>'My Searchable Pages',
        'my_unsearchable_pages'=>'My Unsearchable Pages',
        'my_sitemap_pages'=>'My Site Map Pages',
        'my_rss_enabled_pages'=>'My RSS Enabled Pages',
        'my_standard_pages'=>'My Standard Pages',
        'my_photo_gallery_pages'=>'My Photo Gallery Pages',
        'my_calendar_pages'=>'My Calendar Pages',
        'my_custom_form_pages'=>'My Custom Form Pages',
        'my_form_view_pages'=>'My Form View Pages',
        'my_commerce_pages'=>'My Commerce Pages',
        'my_account_pages'=>'My Account Pages',
        'my_login_pages'=>'My Login Pages',
        'my_affiliate_pages'=>'My Affiliate Pages',
        'my_miscellaneous_pages'=>'My Miscellaneous Pages',
        'my_public_pages'=>'My Public Access Pages',
        'my_guest_pages'=>'My Guest Access Pages',
        'my_registration_pages'=>'My Registration Access Pages',
        'my_membership_pages'=>'My Member Access Pages',
        'my_private_pages'=>'My Private Access Pages'
    );

// If there is a query then store it in a session
if (isset($_REQUEST['query']) == true) {
    $_SESSION['software']['view_pages']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['view_pages']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['view_pages']['query']) == true) && ($_SESSION['software']['view_pages']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
}

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['pages']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['pages']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    $_SESSION['software']['pages']['order'] = $_REQUEST['order'];
}

// If a screen was passed and it is a positive integer, then use it.
// These checks are necessary in order to avoid SQL errors below for a bogus screen value.
if (
    $_REQUEST['screen']
    and is_numeric($_REQUEST['screen'])
    and $_REQUEST['screen'] > 0
    and $_REQUEST['screen'] == round($_REQUEST['screen'])
) {
    $screen = (int) $_REQUEST['screen'];

// Otherwise, use the default, which is the first screen.
} else {
    $screen = 1;
}

// If the sort session is set to Form Name or Form Enabled and the view filter is not set to custom form set the sort session to default
if ((($_SESSION['software']['pages']['sort'] == 'Form Name') || ($_SESSION['software']['pages']['sort'] == 'Form Enabled')) && ($filter != 'my_custom_form_pages')) {
    $_SESSION['software']['pages']['sort'] = '';
}

// If a theme is being previewed then get the activated theme.
// We will use in several places below.
if ($_SESSION['software']['preview_theme_id']) {
    $activated_theme_id = db_value("SELECT id FROM files WHERE activated_" . $_SESSION['software']['device_type'] . "_theme = '1'");
}

// If the sort is set to Desktop Page Style or Mobile Page Style,
// and the user is previewing a theme that is not the activated theme,
// then reset the sort to the default, because we can't easily sort for preview styles.
if (
    (
        ($_SESSION['software']['pages']['sort'] == 'Desktop Page Style')
        || ($_SESSION['software']['pages']['sort'] == 'Mobile Page Style')
    )
    && ($_SESSION['software']['preview_theme_id'])
    && ($_SESSION['software']['preview_theme_id'] != $activated_theme_id)
) {
    unset($_SESSION['software']['pages']['sort']);
    unset($_SESSION['software']['pages']['order']);
}

$style_join_field = "";
$style_column_heading_label = '';
$output_device_type_toggle = '';

// do some different things based on the device type
switch ($_SESSION['software']['device_type']) {
    case 'desktop':
    default:
        // set style join field to desktop style field
        $style_join_field = "page.page_style";

        // set the style column heading
        $style_column_heading_label = 'Desktop Page Style';

        // output toggle to show mobile
        $output_device_type_toggle = '(<a href="update_device_type.php?device_type=mobile&amp;send_to=' . h(urlencode(get_request_uri())) . get_token_query_string_field() . '" title="Show mobile Page Styles">show mobile</a>)';

        // if the sort is mobile page style, then set it to desktop page style,
        // so that the column will still be sorted correctly
        if ($_SESSION['software']['pages']['sort'] == 'Mobile Page Style') {
            $_SESSION['software']['pages']['sort'] = 'Desktop Page Style';
        }

        break;
    
    case 'mobile':
        // set style join field to mobile style field
        $style_join_field = "page.mobile_style_id";

        // set the style column heading
        $style_column_heading_label = 'Mobile Page Style';

        // output toggle to show desktop
        $output_device_type_toggle = '(<a href="update_device_type.php?device_type=desktop&amp;send_to=' . h(urlencode(get_request_uri())) . get_token_query_string_field() . '" title="Show desktop Page Styles">show desktop</a>)';

        // if the sort is desktop page style, then set it to mobile page style,
        // so that the column will still be sorted correctly
        if ($_SESSION['software']['pages']['sort'] == 'Desktop Page Style') {
            $_SESSION['software']['pages']['sort'] = 'Mobile Page Style';
        }

        break;
}

switch ($_SESSION['software']['pages']['sort']) {
    case 'Name':
        $sort_column = 'page_name';
        break;

    case 'Form Name':
        $sort_column = 'form_name';
        break;

    case 'Form Enabled':
        $sort_column = 'enabled';
        break;

    case 'Folder':
        $sort_column = 'folder_name';
        break;

    case 'Desktop Page Style':
    case 'Mobile Page Style':
        $sort_column = 'style_name';
        break;

    case 'Search':
        $sort_column = 'page_search';
        break;

    case 'Comments':
        $sort_column = 'comments';
        break;

    case 'Page Type':
        $sort_column = 'page_type';
        break;
        
    case 'SEO':
        $sort_column = 'seo_score';
        break;

    case 'Site Map':
        $sort_column = 'sitemap';
        break;

    case 'Last Modified':
        $sort_column = 'page_timestamp';
        break;

    default:
        $sort_column = 'page_timestamp';
        $_SESSION['software']['pages']['sort'] = 'Last Modified';
        break;
}

if ($_SESSION['software']['pages']['order']) {
    $asc_desc = $_SESSION['software']['pages']['order'];
} elseif ($sort_column == 'page_timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['pages']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['pages']['order'] = 'asc';
}

$folders_that_user_has_access_to = array();

// if user is a basic user, then get folders that user has access to
if ($user['role'] == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
}


$output_button_bar = '';

// if the user is at least a manager or has create pages turned on, then output the create page button and button bar
if (($user['role'] < '3') || ($user['create_pages'] == TRUE)) {
    $output_button_bar .=
        '<div id="button_bar">
            <a href="add_page.php">Create Page</a>';

    // If advanced search is enabled and the user is a manager or above then output "Update Search Index" button.
    if ((SEARCH_TYPE == 'advanced') && (USER_ROLE != 3)) {
        $output_button_bar .= ' <a href="update_search_index.php" target="_blank">Update Search Index</a>';
    }

    $output_button_bar .= '</div>';
}

// Set the name heading to the default.
// This may be changed by different filters
$output_name_heading = 'Name';

// Switch between the subnav filters
switch ($filter) {
    case 'all_my_archived_pages':
        // Change the heading and subheading.
        $heading = 'All My Archived Pages';
        $subheading = 'All archived pages that I can edit &amp; duplicate.';
        break;
    
    case 'my_home_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_home = "yes")';

        // Change the heading and subheading.
        $heading = 'My Home Pages';
        $subheading = 'These pages are rotated as the website\'s home page.';
        break;

    case 'my_searchable_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_search = "1")';
        // Change the heading and subheading.
        $heading = 'My Searchable Pages';
        $subheading = 'Depending on the page\'s access control, these pages can be found using the built-in site search feature.';
        break;

    case 'my_unsearchable_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_search = "0")';
        // Change the heading and subheading.
        $heading = 'My Unsearchable Pages';
        $subheading = 'These pages cannot be found using the built-in site search.';
        break;
        
    case 'my_sitemap_pages':
        // set page access control filter to public so only public pages show up in the results
        $page_access_control_filter = 'public';
        
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }
        
        // Set the query filter
        $where .= ' (page.sitemap = "1")';
        
        // Change the heading and subheading.
        $heading = 'My Site Map Pages';
        $subheading = 'These are Public Pages that appear in the sitemap.xml file so search engines can find them.';
        break;
        
    case 'my_rss_enabled_pages':
        // set page acces control filter to public so only public pages show up in the results
        $page_access_control_filter = 'public';
    
        // Join tables to query
        $join_table = 
            "LEFT JOIN form_list_view_pages ON
                (form_list_view_pages.page_id = page.page_id)
                AND (form_list_view_pages.collection = 'a')
             LEFT JOIN form_fields ON form_fields.page_id = form_list_view_pages.custom_form_page_id";
        
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }
        
        // Set the query filter
        $where .= '(((page.page_type = "form list view") AND (form_fields.rss_field != "")) OR (page.page_type = "calendar view") OR (page.page_type = "catalog") OR (page.page_type = "catalog detail") OR (page.page_type = "order form"))';
        
        // Group the pages based on their id
        $group_column_by = 'GROUP BY (page.page_id)';
            
        // Change the heading and subheading.
        $heading = 'My RSS Enabled Pages';
        $subheading = 'These are Public Pages that are able to broadcast RSS feeds.';
        break;

    case 'my_standard_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "standard")';

        // Change the heading and subheading.
        $heading = 'My Standard Pages';
        $subheading = 'These pages only contain content and do not contain any built-in interactive features.';
        break;

    case 'my_photo_gallery_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "photo gallery")';

        // Change the heading and subheading.
        $heading = 'My Photo Gallery Pages';
        $subheading = 'These pages display a photo gallery slideshow of all photos located in the same folder.';
        break;

    case 'my_calendar_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "calendar event view" OR page_type = "calendar view")';

        // Change the heading and subheading.
        $heading = 'My Calendar Pages';
        $subheading = 'These pages overlay one or more calendars, calendar details, and published events.';
        break;
    case 'my_custom_form_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        $select_column = ',
            custom_form_pages.form_name,
            custom_form_pages.enabled as enabled';

        $join_table = 'LEFT JOIN custom_form_pages ON custom_form_pages.page_id = page.page_id';

        // Set the query filter.
        $where .= ' (page_type = "custom form" OR page_type = "custom form" OR page_type = "custom form confirmation")';

        // Output additional table headings
        $output_form_name_heading = '<th>' . get_column_heading('Form Name', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>';
        $output_form_enabled_heading = '<th>' . get_column_heading('Form Enabled', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>';

        // Change the heading.
        $heading = 'My Custom Form Pages';
        $subheading = 'These pages either gather data through customizable forms or display submitted form confirmations.';
        break;
    case 'my_form_view_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }
        // Set the query filter.
        $where .= ' ((page_type = "form list view") OR (page_type = "form item view") OR (page_type = "form view directory"))';
        // Change the heading.
        $heading = 'My Form View Pages';
        // Change the subheading.
        $subheading = 'These pages display submitted form data in customizable views.';
        break;
    case 'my_commerce_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "order form" OR page_type = "view order" OR page_type = "catalog" OR page_type = "catalog detail" OR page_type = "express order" OR page_type = "shopping cart" OR page_type = "shipping address and arrival" OR page_type = "shipping method" OR page_type = "billing information" OR page_type = "order preview" OR page_type = "order receipt" OR page_type = "update address book")';

        // Change the heading and subheading.
        $heading = 'My Commerce Pages';
        $subheading = 'These pages provide the built-in ecommerce features.';
        break;

    case 'my_account_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "my account" OR page_type = "my account profile" OR page_type = "email preferences" OR page_type = "view order" OR page_type = "update address book" OR page_type = "change password")';

        // Change the heading and subheading.
        $heading = 'My Account Pages';
        $subheading = 'These pages provide the built-in account self-management for all site users.';
        break;

    case 'my_login_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "registration entrance" OR page_type = "registration confirmation" OR page_type = "membership entrance" OR page_type = "membership confirmation" OR page_type = "forgot password" OR page_type = "login" OR page_type = "logout" OR page_type = "login" OR page_type = "set password")';

        // Change the heading and subheading.
        $heading = 'My Login Pages';
        $subheading = 'These pages provide the built-in user account creation and login capabilities for all site users.';
        break;

    case 'my_affiliate_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "affiliate sign up form" OR page_type = "affiliate sign up confirmation" OR page_type = "affiliate welcome")';

        // Change the heading and subheading.
        $heading = 'My Affiliate Pages';
        $subheading = 'These pages provide the built-in affiliate sign up features.';
        break;

    case 'my_miscellaneous_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "email a friend" OR page_type = "error" OR page_type = "folder view" OR page_type = "search results")';

        // Change the heading and subheading.
        $heading = 'My Miscellaneous Pages';
        $subheading = 'These pages provide responses to other site-wide built-in features.';
        break;

    case 'my_public_pages':
        // Set the page access control filter
        $page_access_control_filter = 'public';

        // Change the heading and subheading.
        $heading = 'My Public Access Pages';
        $subheading = 'These pages are visible by any website visitor.';
        break;

    case 'my_guest_pages':
        // Set the page access control filter
        $page_access_control_filter = 'guest';

        // Change the heading and subheading.
        $heading = 'My Guest Access Pages';
        $subheading = 'These pages are visible to any website visitor after they are offered the option to login or register.';
        break;

    case 'my_registration_pages':
        // Set the page access control filter
        $page_access_control_filter = 'registration';

        // Change the heading and subheading.
        $heading = 'My Registration Access Pages';
        $subheading = 'These pages are visible to any website visitor, but only after they login or register.';
        break;

    case 'my_membership_pages':
        // Set the page access control filter
        $page_access_control_filter = 'membership';

        // Change the heading and subheading.
        $heading = 'My Member Access Pages';
        $subheading = 'These pages are visible to any website user, but only after they have either registered with a valid member id, purchased a membership product, completed a membership trial custom form, or logged in as an unexpired member.';
        break;

    case 'my_private_pages':
        // Set the page access control filter
        $page_access_control_filter = 'private';

        // Change the heading and subheading.
        $heading = 'My Private Access Pages';
        $subheading = 'These pages are visible only to website users who have been granted view access to the parent folder, or who have purchased a product that grants private access to the parent folder.';
        break;

    default:
        // Change the heading and subheading.
        $heading = 'All My Pages';
        $subheading = 'All pages that I can edit &amp; duplicate.';
        break;
}

// If the user is previewing a theme that is not the activated theme,
// then output unsortable column heading for style column.
if (
    ($_SESSION['software']['preview_theme_id'])
    && ($_SESSION['software']['preview_theme_id'] != $activated_theme_id)
) {
    $output_style_column_heading = $style_column_heading_label;

// Otherwise output sortable column.
} else {
    $output_style_column_heading = get_column_heading($style_column_heading_label, $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links);
}

// If where is blank
if ($where == '') {
    $where .= 'WHERE ';

// else where is not blank, so add and
} else {
    $where .= 'AND ';
}

// if the filter is not all my archived pages, then add the sql where statement to prevent getting archived pages
if ($filter != 'all_my_archived_pages') {
    $where .= '(folder.folder_archived = "0")';

// else this is the all my archived pages filter, so only get archived pages
} else {
    $where .= '(folder.folder_archived = "1")';
}

$all_pages = 0;
$my_pages = 0;

// Get file's id and folder number from files.
$query =
    "SELECT
       page.page_id,
       page.page_folder
    FROM page
    LEFT JOIN folder ON page.page_folder = folder.folder_id
    LEFT JOIN style ON $style_join_field = style.style_id
    LEFT JOIN user ON page.page_user = user.user_id
    " . $join_table . "
    $where
    " . $group_column_by . "
    ORDER BY $sort_column $asc_desc";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// Loop through the results
while ($row = mysqli_fetch_assoc($result)) {

    // If the folder access control filter is set.
    if ($page_access_control_filter) {

        // Compare the filter to the folder's access control type and set the row if they match.
        if (get_access_control_type($row['page_folder']) == $page_access_control_filter) {

            // Add one to all files.
            $all_pages++;

            // if user has access to file then add one to my files.
            if (check_folder_access_in_array($row['page_folder'], $folders_that_user_has_access_to) == true) {
                $my_pages++;
            }
       }

    // If the filder access control filter is not set.
    } else {
        // Add one to all files.
        $all_pages++;

        // if user has access to file then add one to my files.
        if (check_folder_access_in_array($row['page_folder'], $folders_that_user_has_access_to) == true) {
            $my_pages++;
        }
    }
}

$search_query = mb_strtolower($_SESSION['software']['view_pages']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',', page.page_name, page.page_type, folder.folder_name, style.style_name, user.user_username)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['view_pages']['query'])) {
    // If where is blank
    if ($where == '') {
        $where .= 'WHERE ';

    // else where is not blank, so add and
    } else {
        $where .= 'AND ';
    }

    $where .= "($sql_search) ";
}


// get all pages
$query =
    "SELECT
       page.page_id,
       page.page_name,
       page.page_folder,
       folder.folder_name,
       folder.folder_access_control_type,
       style.style_name,
       page.page_search,
       page.page_home,
       page.page_type,
       page.comments,
       page.seo_score,
       page.sitemap,
       user.user_username,
       page.page_timestamp" . $select_column . "
    FROM page
    LEFT JOIN folder ON page.page_folder = folder.folder_id
    LEFT JOIN style ON $style_join_field = style.style_id
    LEFT JOIN user ON page.page_user = user.user_id
    " . $join_table . "
    $where
    " . $group_column_by . "
    ORDER BY $sort_column $asc_desc";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

while ($row = mysqli_fetch_assoc($result)) {
    // if user has access to page then add page to pages array
    if (check_folder_access_in_array($row['page_folder'], $folders_that_user_has_access_to) == true) {

        // If there is a folder access control filter.
        if ($page_access_control_filter) {

            // Compare the filter to the pages folder's access control type and set the row if they match.
            if (get_access_control_type($row['page_folder']) == $page_access_control_filter) {
                $pages[] = $row;
            }

        // Else If the folder access control filter is not set.
        } else {
            // Set the row.
            $pages[] = $row;
        }
    }
}

// if there is at least one result to display
if ($pages) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($pages);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_pages.php?screen=' . $previous . $output_filter_for_links . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_pages.php?screen=\' + this.options[this.selectedIndex].value) + \'' . h(escape_javascript($filter_for_links)) . '\'">';

        // build HTML output for links to screens
        for ($i = 1; $i <= $number_of_screens; $i++) {
            // if this number is the current screen, then select option
            if ($i == $screen) {
                $selected = ' selected="selected"';
            // else this number is not the current screen, so do not select option
            } else {
                $selected = '';
            }

            $output_screen_links .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
        }

        $output_screen_links .= '</select>';
    }

    // build Next button if necessary
    $next = $screen + 1;
    // if next screen is less than or equal to the total number of screens, output next link
    if ($next <= $number_of_screens) {
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_pages.php?screen=' . $next . $output_filter_for_links . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the pages array
    $last_index = count($pages) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $query_string_from = '';

        // if page type is a certain page type, then prepare from
        switch ($pages[$key]['page_type']) {
            case 'view order':
            case 'custom form':
            case 'custom form confirmation':
            case 'calendar event view':
            case 'catalog detail':
            case 'shipping address and arrival':
            case 'shipping method':
            case 'logout':
                $query_string_from = '?from=control_panel';
                break;
        }

        $output_link_url = h(escape_javascript(PATH)) . h(escape_javascript(encode_url_path($pages[$key]['page_name']))) . $query_string_from;

        $output_style_name = '';

        // If the user is previewing a theme, and it is not the activated theme,
        // then get preview style if one exists.
        if (
            ($_SESSION['software']['preview_theme_id'])
            && ($_SESSION['software']['preview_theme_id'] != $activated_theme_id)
        ) {
            $style_name = db_value(
                "SELECT style.style_name
                FROM preview_styles
                LEFT JOIN style ON preview_styles.style_id = style.style_id
                WHERE
                    (preview_styles.page_id = '" . $pages[$key]['page_id'] . "')
                    AND (preview_styles.theme_id = '" . escape($_SESSION['software']['preview_theme_id']) . "')
                    AND (device_type = '" . escape($_SESSION['software']['device_type']) . "')");

            // If a preview style was found, then prepare to output style name.
            if ($style_name != '') {
                $output_style_name = '[P] ' . h($style_name);
            }
        }

        // If a style has not been found yet, then that means user is not previewing themes
        // or there is no preview style, so just output activated style.
        if ($output_style_name == '') {
            $output_no_mobile_style_warning_class = '';

            // if the page has a style specifically defined for it, then prepare to output style name
            if ($pages[$key]['style_name'] != '') {
                $output_style_name = h($pages[$key]['style_name']);

            // else the page does not have a style specifically defined for it, so get inherited style name
            } else {
                // get inherited style id
                $style_id = get_style($pages[$key]['page_folder'], $_SESSION['software']['device_type']);

                // if the device type is set to mobile and a mobile style id could not be found, then get desktop style id
                // because we fallback to a desktop style when a mobile style cannot be found for a page
                if (
                    ($_SESSION['software']['device_type'] == 'mobile')
                    && ($style_id == 0)
                ) {
                    $style_id = get_style($pages[$key]['page_folder'], 'desktop');

                    $output_no_mobile_style_warning_class = ' no_mobile_style_warning';
                }

                // if a style was found, then get style name
                if ($style_id != 0) {
                    // get inherited style name
                    $query = "SELECT style_name FROM style WHERE style_id = '$style_id'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);

                    $output_style_name = 'Default: ' . h($row['style_name']);
                }
            }

            // If a theme is being previewed, then add an "[A]" prefix before style name,
            // to show that it is the activated style.
            if ($_SESSION['software']['preview_theme_id']) {
                $output_style_name = '[A] ' . $output_style_name;
            }
        }

        $output_search_check_mark = '';

        // if page is searchable, then prepare to output check mark image
        if ($pages[$key]['page_search'] == 1) {
            $output_search_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        }

        $output_home_icon = '';
        $output_comments_check_mark = '';

        // if page is a home page, then prepare to output home icon
        if ($pages[$key]['page_home'] == 'yes') {
            $output_home_icon = '<img src="images/icon_home_page.gif" width="16" height="14" border="0" align="absbottom" class="icon_home_page" alt="" />&nbsp;';
        }
        
        // if page has comments enabled, then prepare to output check mark image
        if ($pages[$key]['comments'] == '1') {
            $output_comments_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        }
        
        $output_sitemap_check_mark = '';
        
        // if page has sitemap enabled, then prepare to output check mark image
        if ($pages[$key]['sitemap'] == '1') {
            $output_sitemap_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        }

        // If custom form filter is on.
        if ($filter == 'my_custom_form_pages') {

            // if page is a home page, then prepare to output check mark image
            if ($pages[$key]['enabled'] == 1) {
                $output_form_enabled_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
            }

            // output form rows
            $output_form_name_row = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($pages[$key]['form_name']) . '</td>';
            $output_form_enabled_row = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center;">' . $output_form_enabled_mark . '</td>';
        }

        $output_edit_url = 'edit_page.php?id=' . $pages[$key]['page_id'];

        $output_rows .=
            '<tr>
                <td class="selectall"><input type="checkbox" name="pages[]" value="' . $pages[$key]['page_id'] . '" class="checkbox" /></td>
                <td class="chart_label pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_home_icon . h($pages[$key]['page_name']) . '</td>
                ' . $output_form_name_row . '
                ' . $output_form_enabled_row . '
                <td class="pointer ' . $pages[$key]['folder_access_control_type'] . '" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($pages[$key]['folder_name']) . '</td>
                <td class="pointer' . $output_no_mobile_style_warning_class . '" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_style_name . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h(get_page_type_name($pages[$key]['page_type'])) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center;">' . $output_search_check_mark . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center;">' . $output_comments_check_mark . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center;">' . $output_sitemap_check_mark . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_edit_url . '\'">' . get_relative_time(array('timestamp' => $pages[$key]['page_timestamp'])) . ' by ' . h($pages[$key]['user_username']) . '</td>
            </tr>';
    }
}

$output_delete_selected_button = '';

// if the user is at least a manager or has access to delete pages, then output the delete selected button
if (($user['role'] < '3') || ($user['delete_pages'] == TRUE)) {
    $output_delete_selected_button = '&nbsp;&nbsp;&nbsp;<input type="button" value="Delete Selected" class="delete" onclick="edit_pages(\'delete\')" />';
}

echo
    output_header() . '
  
    <div id="subnav">
        <table>
            <tbody>
                <tr>   
                   <td>
                        <ul>
                            <li><a href="view_pages.php">My Pages</a></li>
                        </ul>
                    </td>
                    <td>
                        <ul>
                            <li><a href="view_short_links.php">My Short Links</a></li>
                        </ul>
                    </td>
                    ' . $output_auto_dialogs . '
                    <td>
                        <ul>
                            <li><a href="view_comments.php">Comments</a></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    ' . $output_button_bar . '
    <div id="content">
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>' . $heading . '</h1>
        <div class="subheading">' . $subheading . '</div>
        <form id="search_form" action="view_pages.php" method="get" class="search_form">
            <input type="hidden" name="filter" value="' . h($filter) . '" />
            Show: <select name="filter" onchange="submit_form(\'search_form\')">' . get_filter_options($filters_in_array, $filter) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['view_pages']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing ' . number_format($number_of_results) . ' of ' . number_format($my_pages) . ' I can access. ' . number_format($all_pages) . ' Total
        </div>
        <form name="form" action="edit_pages.php" method="post">
            ' . get_token_field() . '
            <input type="hidden" name="action" />
            <input type="hidden" name="move_to_folder" />
            <input type="hidden" name="edit_page_style" />
            <input type="hidden" name="edit_mobile_style_id" />
            <input type="hidden" name="edit_site_search" />
            <input type="hidden" name="edit_sitemap" />
            <input type="hidden" name="send_to" value="' . h(get_request_uri()) . '" />
            <table class="chart">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
                    <th>' . get_column_heading($output_name_heading, $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                    ' . $output_form_name_heading . '
                    ' . $output_form_enabled_heading . '
                    <th>' . get_column_heading('Folder', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                    <th>' . $output_style_column_heading . ' ' . $output_device_type_toggle . '</th>
                    <th>' . get_column_heading('Page Type', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                    <th  style="text-align: center;">' . get_column_heading('Search', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                    <th style="text-align: center;">' . get_column_heading('Comments', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                    <th style="text-align: center;">' . get_column_heading('Site Map', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                    <th>' . get_column_heading('Last Modified', $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="pagination">
                ' . $output_screen_links . '
            </div>
            <div class="buttons">
                <input type="button" value="Modify Selected" class="submit-secondary" onclick="window.open(\'edit_pages.php\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=600,height=525\'); edit_pages(\'edit\')" />' . $output_delete_selected_button . '
            </div>
        </form>
    </div>' .
    output_footer();

$liveform->remove_form('view_pages');