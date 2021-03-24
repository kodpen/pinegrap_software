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

// get menu id and name that menu item is in, to use for later
$query =
    "SELECT
        menus.id,
        menus.name,
        menu_items.name as menu_item_name,
        menu_items.parent_id
    FROM menus
    LEFT JOIN menu_items ON menus.id = menu_items.menu_id
    WHERE menu_items.id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$menu_id = $row['id'];
$menu_name = $row['name'];
$menu_item_name = $row['menu_item_name'];
$parent_id = $row['parent_id'];

// if user has a user role and if they do not have access to this menu, output error
if (($user['role'] == 3) && (in_array($menu_id, get_items_user_can_edit('menus', $user['id'])) == FALSE)) {
    log_activity("access denied because user does not have access to edit menu item for menu (" . $menu_name . ")", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

include_once('liveform.class.php');
$liveform = new liveform('edit_menu_item', $_REQUEST['id']);

if (!$_POST) {
    // if edit menu item screen has not been submitted already, pre-populate fields with data
    if (isset($_SESSION['software']['liveforms']['edit_menu_item'][$_GET['id']]) == false) {
        // get menu item information
        $query =
            "SELECT
                name,
                sort_order,
                link_page_id,
                link_url,
                link_target,
                security
            FROM menu_items
            WHERE id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        // Assign the values to the fields.
        $liveform->assign_field_value('name', $row['name']);
        $liveform->assign_field_value('link_page_id', $row['link_page_id']);
        $liveform->assign_field_value('link_url', $row['link_url']);
        $liveform->assign_field_value('link_target', $row['link_target']);
        $liveform->assign_field_value('security', $row['security']);
        
        $sort_order = $row['sort_order'];
        
        // get menu item id for menu item directly above this menu item, because this will be the position value
        $query =
            "SELECT id
            FROM menu_items
            WHERE 
                (menu_id = '" . escape($menu_id) . "')
                AND (sort_order < " . $sort_order . ")
                AND (parent_id = '" . escape($parent_id) . "')
            ORDER BY sort_order DESC
            LIMIT 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed. ' . $query);
        $row = mysqli_fetch_assoc($result);
        
        $liveform->assign_field_value('sort_order', $row['id']);
    }
    
    $parent_id_options = get_menu_item_options($menu_id, $_GET['id'], 0, 1, $parent_id);
    
    // get sort order options
    $sort_order_options = array();
    $sort_order_options['Top'] = 'top';
    
    // get all menu items that are in this level
    $query =
        "SELECT
            id,
            name
        FROM menu_items
        WHERE
            (id != '" . escape($_GET['id']) . "')
            AND (menu_id = '" . escape($menu_id) . "')
            AND (parent_id = '" . escape($parent_id) . "')
        ORDER BY sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // loop through all menu items in order to add them to sort order options
    while ($row = mysqli_fetch_assoc($result)) {
        $sort_order_options['Below ' . h($row['name'])] = $row['id'];
    }
    
    $page_options = get_page_options();
    
    $link_target_options = 
        array(
            'Same Window'=>'Same Window',
            'New Window'=>'New Window');
    
    print output_header() . '
    <div id="subnav">
        <h1>' . h($menu_item_name) . '</h1>
        <div class="subheading">Menu: ' . h($menu_name) . '</div>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>Edit Menu Item</h1>
        <div class="subheading">Define the label to display for this menu item and where it links too.</div>
        <form action="edit_menu_item.php" method="post">
            ' . get_token_field() . '
            <input type="hidden" name="from" value="' . h($_GET['from']) . '" />
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
            <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
            <input type="hidden" id="menu_id" name="menu_id" value="' . h($menu_id) . '" />
            <input type="hidden" id="parent_id_status" name="parent_id_status" value="" />
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
                    <td><select name="parent_id" onchange="document.getElementById(\'sort_order\').disabled=true; document.getElementById(\'parent_id_status\').value=\'changed\';">' . $parent_id_options . '</select></td>
                </tr>
                <tr>
                    <td>Position:</td>
                    <td>' . $liveform->output_field(array('type'=>'select', 'id'=>'sort_order', 'name'=>'sort_order', 'options'=>$sort_order_options)) . '</td>
                </tr>
            </table>
            <div class="buttons">
                <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This menu item and all menu items underneath it will be permanently deleted.\')">
            </div>
        </form>
    </div>' .
    output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if menu_item was selected for deletion
    if ($_POST['submit_delete'] == 'Delete') {
        // function for deleting menu items recursively
        function delete_menu_items($menu_item_id)
        {
            // delete menu_item
            $query = "DELETE FROM menu_items WHERE id = '" . escape($menu_item_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $menu_items = array();
            
            // get sub menu items
            $query = "SELECT id FROM menu_items WHERE parent_id = '" . escape($menu_item_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $menu_items = array();
            
            // loop through sub menu items in order to build array
            while ($row = mysqli_fetch_assoc($result)) {
                $menu_items[] = $row;
            }
            
            // loop through sub menu items in order to delete sub menu items
            foreach ($menu_items as $menu_item) {
                delete_menu_items($menu_item['id']);
            }
        }
        
        // delete menu item and all sub menu items
        delete_menu_items($_POST['id']);

        // update last modified for menu
        $query =
            "UPDATE menus
            SET
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($menu_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('menu item (' . $_POST['name'] . ') on menu (' . $menu_name . ') was deleted', $_SESSION['sessionusername']);
        
        include_once('liveform.class.php');
        $liveform_view_menu_items = new liveform('view_menu_items');
        $liveform_view_menu_items->add_notice('The menu item has been deleted.');

        // forward user to view menu items screen
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_menu_items.php?id=' . $menu_id . '&from=' . urlencode($_POST['from']) . '&send_to=' . urlencode($_POST['send_to']));
        
        $liveform->remove_form();
        
    // else menu item was not selected for deletion
    } else {
        $liveform->add_fields_to_session();
        
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is an error, forward user back to edit menu screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_menu_item.php?id=' . $_POST['id'] . '&from=' . urlencode($_POST['from']) . '&send_to=' . urlencode($_POST['send_to']));
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
        
        // If the parent ID was changed, add this menu items sort order to the end of the line
        if ($_POST['parent_id_status'] == 'changed') {
            // Get the sort order for the next menu item
            $query =
                "SELECT sort_order
                FROM menu_items
                WHERE 
                    (menu_id = '" . escape($menu_id) . "')
                    AND (parent_id = '" . escape($_POST['parent_id'])  . "')
                ORDER BY sort_order DESC
                LIMIT 1";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $row = mysqli_fetch_assoc($result);
            $sort_order = $row['sort_order'] + 1;
        } else {
            $sort_order = $_POST['sort_order'];
        }
        
        // update menu item
        $query =
            "UPDATE menu_items
            SET
                name = '" . escape($_POST['name']) . "',
                parent_id = '" . escape($_POST['parent_id']) . "',
                sort_order = '" . escape($sort_order) . "',
                link_page_id = '" . escape($link_page_id) . "',
                link_url = '" . escape($link_url) . "',
                link_target = '" . escape($_POST['link_target']) . "',
                security = '" . escape($_POST['security']) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // update last modified for menu
        $query =
            "UPDATE menus
            SET
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($menu_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        /* begin: update sort orders for menu items */
        $menu_items = array();
        
        // If sort_order is set to top, add it to the array first thing.
        if ($_POST['sort_order'] == 'top') {
            $menu_items[] = $_POST['id'];
        }
        
        // get all menu items other than the menu item that is currently being edited
        $query =
            "SELECT id
            FROM menu_items
            WHERE 
                (menu_id = '" . escape($menu_id) . "')
                AND (parent_id = '" . escape($_POST['parent_id'])  . "')
                AND (id != '" . escape($_POST['id'])  . "')
            ORDER BY sort_order";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        while ($row = mysqli_fetch_assoc($result)) {
            // add this menu item to array
            $menu_items[] = $row['id'];
            
            // if this menu item is the position value, then we need to add the menu item that is being edited to the array
            if ($row['id'] == $_POST['sort_order']) {
                $menu_items[] = $_POST['id'];
            }
        }
        
        $count = 1;
        
        // loop through all menu items in order to update sort order
        foreach ($menu_items as $key => $current_menu_id) {
            // update sort order for menu item
            $query =
                "UPDATE menu_items
                SET sort_order = '$count'
                WHERE id = '" . escape($current_menu_id)  . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $count++;
        }
        
        /* end: update sort orders for menu items */
        
        log_activity('menu item (' . $_POST['name'] . ') on menu (' . $menu_name . ') was modified', $_SESSION['sessionusername']);

        include_once('liveform.class.php');
        $liveform_view_menu_items = new liveform('view_menu_items');
        $liveform_view_menu_items->add_notice('The menu item has been saved.');

        // forward user to view menu items screen
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_menu_items.php?id=' . $menu_id . '&from=' . urlencode($_POST['from']) . '&send_to=' . urlencode($_POST['send_to']));
        
        $liveform->remove_form();
    }
}
?>