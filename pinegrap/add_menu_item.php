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

// get menu id and name that we will add the menu item to, to use for later
$query =
    "SELECT
        name
    FROM menus
    WHERE id = '" . escape($_REQUEST['menu_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$menu_id = $_REQUEST['menu_id'];
$menu_name = $row['name'];

// if user has a user role and if they do not have access to this menu, then user does not have access to edit region, so output error
if (($user['role'] == 3) && (in_array($_REQUEST['menu_id'], get_items_user_can_edit('menus', $user['id'])) == FALSE)) {
    log_activity("access denied because user does not have access to create a menu item for menu (" . $menu_name . ")", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('add_menu_item');

if (!$_POST) {
    // Setup picklist option variables
    $parent_id_options = get_menu_item_options($_REQUEST['menu_id']);
    $page_options = get_page_options();
    $link_target_options = 
        array(
            'Same Window'=>'Same Window',
            'New Window'=>'New Window');

    if (!$liveform->output_errors()) {
        $liveform->remove_form();
    }
    
    $output = output_header() . '
    <div id="subnav">
        <h1>[new menu item]</h1>
        <div class="subheading">Menu: ' . h($menu_name) . '</div>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>Create Menu Item</h1>
        <div class="subheading">Create a new menu item within this shared menu.</div>
        <form action="add_menu_item.php" method="post">
            ' . get_token_field() . '
            <input type="hidden" name="from" value="' . h($_GET['from']) . '" />
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
            <input type="hidden" id="menu_id" name="menu_id" value="' . $menu_id . '" />
            <table class="field">
                <tr>
                    <th colspan="2"><h2>Menu Item Name</h2></th>
                </tr>
                <tr>
                    <td style="width: 20%">Menu Item Name:</td>
                    <td style="width: 80%">' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'maxlength'=>'100')) . '</td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Link Menu Item To</h2></th>
                </tr>
                <tr>
                    <td>Page:</td>
                    <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'link_page_id', 'options'=>$page_options)) . '</td>
                </tr>
                <tr>
                    <td style="padding-left: 2em; white-space: nowrap"><label for="security">Enable Security:</label></td>
                    <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'security', 'id'=>'security', 'value'=>'1', 'class'=>'checkbox')) . '&nbsp; (only show Menu Item if Visitor has access to view Page)</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-left: 9px">- or -</td>
                </tr>
                <tr>
                    <td>URL:</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'link_url', 'size'=>'60', 'maxlength'=>'255')) . '</td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Target Window for Menu Item Link</h2></th>
                </tr>
                <tr>
                    <td>Link Target:</td>
                    <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'link_target', 'options'=>$link_target_options)) . '</td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Parent Menu Item</h2></th>
                </tr>
                <tr>
                    <td>Parent Menu Item:</td>
                    <td><select name="parent_id">' . $parent_id_options . '</select></td>
                </tr>
            </table>
            <div class="buttons">
                <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
            </div>
        </form>
    </div>' .
    output_footer();

    print $output;
    
    $liveform->unmark_errors();
    $liveform->clear_notices();

} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');
    
    // if there is an error, forward user back to edit menu screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_menu_item.php?menu_id=' . $menu_id . '&from=' . urlencode($_POST['from']) . '&send_to=' . urlencode($_POST['send_to']));
        exit();
    }
    
    // If there is a link page id, blank out the link_url field!
    if ($_POST['link_page_id']) {
        $link_page_id = $_POST['link_page_id'];
        $link_url = '';
    // Else, there was not a page id, so save the URL fields value instead
    } else {
        $link_url = $_POST['link_url'];
        $link_page_id = '';
    }
    
    // Get the sort order for the next menu item
    $query = "SELECT sort_order
             FROM menu_items
             WHERE 
                 (menu_id = '" . escape($menu_id) . "')
                 AND (parent_id = '" . escape($_POST['parent_id'])  . "')
             ORDER BY sort_order DESC
             LIMIT 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $row = mysqli_fetch_assoc($result);
    $new_sort_order = $row['sort_order'] + 1;
    
    // create menu item
    $query =
        "INSERT into menu_items
            (name,
            menu_id,
            parent_id,
            sort_order,
            link_page_id,
            link_url,
            link_target,
            security,
            created_user_id, 
            created_timestamp,
            last_modified_user_id, 
            last_modified_timestamp)
        VALUES
            ('" . escape($_POST['name']) . "',
            '" . escape($menu_id) . "',
            '" . escape($_POST['parent_id']) . "',
            '" . $new_sort_order . "',
            '" . escape($link_page_id) . "',
            '" . escape($link_url) . "',
            '" . escape($_POST['link_target']) . "',
            '" . escape($_POST['security']) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('menu item (' . $_POST['name'] . ') on menu (' . $menu_name . ') was created', $_SESSION['sessionusername']);
    
    // update last modified for menu
    $query =
        "UPDATE menus
        SET
            last_modified_user_id = '" . $user['id'] . "',
            last_modified_timestamp = UNIX_TIMESTAMP()
        WHERE id = '" . escape($menu_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    include_once('liveform.class.php');
    $liveform_view_menu_items = new liveform('view_menu_items');
    $liveform_view_menu_items->add_notice('The menu item has been created.');

    // forward user to view menu items screen
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_menu_items.php?id=' . $menu_id . '&from=' . urlencode($_POST['from']) . '&send_to=' . urlencode($_POST['send_to']));
}
?>