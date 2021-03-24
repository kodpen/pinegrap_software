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
$liveform = new liveform('view_comments');


// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['comments']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['comments']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    // store sort in session
    $_SESSION['software']['comments']['order'] = $_REQUEST['order'];
}

$number_of_results = 0;
$output_clear_button = '';

// If there is a query then store it in a session
if (isset($_REQUEST['query']) == true) {
    $_SESSION['software']['comments']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['comments']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['comments']['query']) == true) && ($_SESSION['software']['comments']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch($_SESSION['software']['comments']['sort'])
{
    case 'Name':
        $sort_column = 'name';
        break;

    case 'Published':
        $sort_column = 'published';
        break;
    case 'Featured':
        $sort_column = 'featured';
        break;
    case 'Cancel if Added First':
        $sort_column = 'publish_cancel';
        break;
    case 'Submitted':
        $sort_column = 'created_timestamp';
        break;
    default:
        $sort_column = 'created_timestamp';
        $_SESSION['software']['comments']['sort'] = 'Submitted';
}

if ($_SESSION['software']['comments']['order']) {
    $asc_desc = $_SESSION['software']['comments']['order'];
} elseif ($sort_column == 'created_timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['comments']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['comments']['order'] = 'asc';
}



// get total number of styles
$query = "SELECT COUNT(page_id) FROM comments";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_comments = $row[0];


$search_query = mb_strtolower($_SESSION['software']['comments']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',',  comments.name,comments.message,user.user_username)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['comments']['query'])) {
    // Get only the results the user wanted in the search.
    $where .= "WHERE $sql_search";
}





// get comment information
$query = 
    "SELECT
        comments.page_id,
        comments.item_id,
        comments.item_type,
        comments.name,
        comments.message,
        comments.id,
        files.id as file_id,
        files.name as file_name,
        files.size as file_size,
        comments.published,
        comments.publish_date_and_time,
        comments.publish_cancel,
        comments.featured,
        page.page_type,
        page.page_folder,
        page.comments_submitter_email_page_id,
        page.comments_watcher_email_page_id,
        user.user_username as created_username,
        comments.created_timestamp
    FROM comments
    LEFT JOIN files ON comments.file_id = files.id
    LEFT JOIN page ON page.page_id = comments.page_id
    LEFT JOIN user ON comments.created_user_id = user.user_id
    $where
    ORDER BY $sort_column $asc_desc";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$comments = array();
// Loop through the results
while ($row = mysqli_fetch_assoc($result)) {
    $comments[] = $row;
}

// loop through styles in order to prepare to output them
foreach ($comments as $comment) {

    $output_link_url = 'edit_comment.php?id=' . $comment['id'];

    // if comment published, then prepare to output check mark image
    $output_published_check_mark='';
    if ($comment['published'] == '1') {
        $output_published_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    }   

    // if comment featured, then prepare to output check mark image
    $output_featured_check_mark='';
    if ($comment['featured'] == '1') {
        $output_featured_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    }   

    // if publish cancel, then prepare to output check mark image
    $output_publishcancel_check_mark='';
    if ($comment['publish_cancel'] == '1') {
        $output_publishcancel_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    }  

    // if the last modified username was found, then prepare to output it
    if ($comment['created_username']) {
        $created_username = $comment['created_username'];
        
    // else the last modified username was not found, so prepare placeholder
    } else {
        $created_username = '[Unknown]';
    }
    $output_date_and_time ='';
    if($comment['publish_date_and_time'] > 0){
        $output_date_and_time =h($comment['publish_date_and_time']);
    }
        
    
    $number_of_results++;

    $output_rows .= '
        <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label" >' . h($comment['name']) . '</td>
            <td style="text-align: center;">' . $output_published_check_mark . '</td>
            <td style="text-align: center;">' . $output_featured_check_mark . '</td>
            <td>'.$output_date_and_time.'</td>
            <td style="text-align: center;">' . $output_publishcancel_check_mark . '</td>
            <td class="chart_label" >' . h($comment['message']) . '</td>
            <td>' . get_relative_time(array('timestamp' => $comment['created_timestamp'])) . '  by ' . h($created_username) . '</td>
            
        </tr>';
}

$output_auto_dialogs = '';
if (AUTO_DIALOGS && (USER_ROLE < 3)) {
    $output_auto_dialogs =
        '<td>
            <ul>
                <li><a href="view_auto_dialogs.php">Auto Dialogs</a></li>
            </ul>
        </td>';
}

print 
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

<div id="content">
    ' . $liveform->output_errors() . '
    ' . $liveform->output_notices() . '
    <a href="#" id="help_link">Help</a>
    <h1>Comments</h1>
    <div class="subheading">All site-wide comments and reviews that I can edit.</div>
    <form action="view_comments.php" method="get" class="search_form">
        <input type="text" name="query" value="' . h($_SESSION['software']['comments']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
    </form>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($all_comments) . ' Total
    </div>
    <form action="delete_comment.php" method="post" class="disable_shortcut">
        ' . get_token_field() . '
            <table class="chart">
                <tr>
                    <th nowrap>' . get_column_heading('Name', $_SESSION['software']['comments']['sort'], $_SESSION['software']['comments']['order']) . '</th>
                    <th nowrap>' . get_column_heading('Published', $_SESSION['software']['comments']['sort'], $_SESSION['software']['comments']['order']) . '</th>
                    <th nowrap>' . get_column_heading('Featured', $_SESSION['software']['comments']['sort'], $_SESSION['software']['comments']['order']) . '</th>
                    <th>At a Scheduled Time</th>
                    <th nowrap>' . get_column_heading('Cancel if Added First', $_SESSION['software']['comments']['sort'], $_SESSION['software']['comments']['order']) . '</th>
                    <th>Message</th>
                    <th nowrap>' . get_column_heading('Submitted', $_SESSION['software']['comments']['sort'], $_SESSION['software']['comments']['order']) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="buttons">
                <input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
            </div>
        </form>
    </div>' .
    output_footer();
    $liveform->remove_form('view_comments');