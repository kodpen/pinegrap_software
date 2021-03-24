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

include_once('liveform.class.php');
$liveform = new liveform('edit_system_style', $_REQUEST['id']);
$liveform->assign_field_value('id', $_REQUEST['id']);
$liveform->assign_field_value('send_to', $_REQUEST['send_to']);

// get style information
$query = 
    "SELECT
       style.style_name as name,
       style.style_layout as layout,
       style.style_empty_cell_width_percentage AS empty_cell_width_percentage,
       style.social_networking_position,
       style.theme_id,
       style.additional_body_classes,
       style.collection,
       style.style_timestamp as last_modified_timestamp,
       user.user_username as last_modified_username
   FROM style
   LEFT JOIN user ON style.style_user = user.user_id
   WHERE style.style_id = '" . escape($liveform->get_field_value('id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$name = $row['name'];
$layout = $row['layout'];
$empty_cell_width_percentage = $row['empty_cell_width_percentage'];
$social_networking_position = $row['social_networking_position'];
$theme_id = $row['theme_id'];
$additional_body_classes = $row['additional_body_classes'];
$collection = $row['collection'];
$last_modified_timestamp = $row['last_modified_timestamp'];
$last_modified_username = $row['last_modified_username'];

// if the form was not just submitted, then prepare to output form
if (!$_POST) {
    // if the form has not been submitted yet, then store data in liveform
    if ($liveform->field_in_session('name') == FALSE) {
        $liveform->assign_field_value('name', $name);
        $liveform->assign_field_value('empty_cell_width_percentage', $empty_cell_width_percentage);

        // If social networking is enabled, then set default value for position field.
        if (SOCIAL_NETWORKING == TRUE) {
            $liveform->assign_field_value('social_networking_position', $social_networking_position);
        }

        $liveform->assign_field_value('theme_id', $theme_id);
        $liveform->assign_field_value('additional_body_classes', $additional_body_classes);
        $liveform->assign_field_value('collection', $collection);
    }
    
    // if a last modified username was not found, then set it to be unknown
    if ($last_modified_username == '') {
        $last_modified_username = '[Unknown]';
    }
    
    // get layout name based on the layout
    switch ($layout) {
        case 'one_column':
            $output_layout_name = '1 Column';
            break;
            
        case 'one_column_email':
            $output_layout_name = '1 Column, E-mail';
            break;
                        
        case 'one_column_mobile':
            $output_layout_name = '1 Column, Mobile';
            break;
            
        case 'two_column_sidebar_left':
            $output_layout_name = '2 Column, Sidebar Left';
            break;
        
        case 'two_column_sidebar_right':
            $output_layout_name = '2 Column, Sidebar Right';
            break;
            
        case 'three_column_sidebar_left':
            $output_layout_name = '3 Column, Sidebar Left';
            break;
    }

    $output_additional_body_classes = '';

    // If there is an additional body class, then output additional body classes.
    if ($additional_body_classes != '') {
        $additional_body_classes_array = explode(' ', $additional_body_classes);

        foreach ($additional_body_classes_array as $additional_body_class) {
            $output_additional_body_classes .= ' .' . h($additional_body_class);
        }
    }

    $output_social_networking_position = '';

    // If social networking is enabled, then output position pick list.
    if (SOCIAL_NETWORKING == TRUE) {
        // Create options for social networking position pick list.
        $social_networking_position_options = array(
            'Top Left' => 'top_left',
            'Top Right' => 'top_right',
            'Bottom Left' => 'bottom_left',
            'Bottom Right' => 'bottom_right',
            'Disabled' => 'disabled',
        );

        $output_social_networking_position = 'Social Networking Position: ' . $liveform->output_field(array('type'=>'select', 'name'=>'social_networking_position', 'options'=>$social_networking_position_options, 'style'=>'vertical-align: middle'));
    }
    
    // if the areas field does not exist, then the form has not been submitted yet, so prepare default areas
    if ($liveform->field_in_session('areas') == FALSE) {
        $areas = array();
        
        // prepare areas array differently based on the layout
        switch ($layout) {
            case 'one_column':
                $areas['site_top']['rows'] = array();
                $areas['site_header']['rows'] = array();
                $areas['area_header']['rows'] = array();
                $areas['page_header']['rows'] = array();
                $areas['page_content']['rows'] = array();
                $areas['page_footer']['rows'] = array();
                $areas['area_footer']['rows'] = array();
                $areas['site_footer']['rows'] = array();
                
                break;
                
            case 'one_column_email':
                $areas['site_top']['rows'] = array();
                $areas['site_header']['rows'] = array();
                $areas['area_header']['rows'] = array();
                $areas['page_header']['rows'] = array();
                $areas['page_content']['rows'] = array();
                $areas['page_footer']['rows'] = array();
                $areas['area_footer']['rows'] = array();
                $areas['site_footer']['rows'] = array();
                
                break;

            case 'one_column_mobile':
                $areas['site_top']['rows'] = array();
                $areas['site_header']['rows'] = array();
                $areas['area_header']['rows'] = array();
                $areas['page_header']['rows'] = array();
                $areas['page_content']['rows'] = array();
                $areas['page_footer']['rows'] = array();
                $areas['area_footer']['rows'] = array();
                $areas['site_footer']['rows'] = array();
                
                break;
                
            case 'two_column_sidebar_left':
                $areas['site_top']['rows'] = array();
                $areas['site_header']['rows'] = array();
                $areas['area_header']['rows'] = array();
                $areas['page_header']['rows'] = array();
                $areas['page_content']['rows'] = array();
                $areas['sidebar']['rows'] = array();
                $areas['page_footer']['rows'] = array();
                $areas['area_footer']['rows'] = array();
                $areas['site_footer']['rows'] = array();
                
                break;
            
            case 'two_column_sidebar_right':
                $areas['site_top']['rows'] = array();
                $areas['site_header']['rows'] = array();
                $areas['area_header']['rows'] = array();
                $areas['page_header']['rows'] = array();
                $areas['page_content']['rows'] = array();
                $areas['sidebar']['rows'] = array();
                $areas['page_footer']['rows'] = array();
                $areas['area_footer']['rows'] = array();
                $areas['site_footer']['rows'] = array();
                
                break;
                
            case 'three_column_sidebar_left':
                $areas['site_top']['rows'] = array();
                $areas['site_header']['rows'] = array();
                $areas['area_header']['rows'] = array();
                $areas['page_header']['rows'] = array();
                $areas['sidebar']['rows'] = array();
                $areas['page_content_left']['rows'] = array();
                $areas['page_content_right']['rows'] = array();
                $areas['page_footer']['rows'] = array();
                $areas['area_footer']['rows'] = array();
                $areas['site_footer']['rows'] = array();
                
                break;
        }
        
        // get cells from database in order to prepare to output areas array
        $query =
            "SELECT
                area,
                `row`, # Backticks for reserved word.
                col,
                region_type,
                region_name
            FROM system_style_cells
            WHERE style_id = '" . escape($liveform->get_field_value('id')) . "'
            ORDER BY
                area ASC,
                `row` ASC, # Backticks for reserved word.
                col ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $cells = array();
        
        // loop through the cells in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $cells[] = $row;
        }
        
        // loop through the cells in order to prepare areas array
        foreach ($cells as $cell) {
            $areas[$cell['area']]['rows'][$cell['row'] - 1]['cells'][$cell['col'] - 1]['region_type'] = $cell['region_type'];
            $areas[$cell['area']]['rows'][$cell['row'] - 1]['cells'][$cell['col'] - 1]['region_name'] = $cell['region_name'];
        }

        // create variable that we will use to increment the page region number as we find them
        $page_region_number = 0;
        
        // initialize function that will be responsible for getting area JavaScript
        function get_area_for_javascript($area, $areas)
        {
            global $page_region_number;

            $output_rows = '';
            
            // loop through the rows in this area in order to prepare JavaScript
            foreach ($areas[$area]['rows'] as $row) {
                $output_cells = '';
                
                // loop through the cells in this row in order to prepare JavaScript
                foreach ($row['cells'] as $cell) {
                    // if this is not the first cell, then add a comma
                    if ($output_cells != '') {
                        $output_cells .= ',';
                    }

                    $output_page_region_number = '';

                    // if this cell is a page region, then add page region number
                    if ($cell['region_type'] == 'page') {
                        $page_region_number += 1;

                        $output_page_region_number = ',"page_region_number": ' . $page_region_number;
                    }
                    
                    $output_cells .= '{"region_type": "' . $cell['region_type'] . '","region_name": "' . escape_javascript($cell['region_name']) . '"' . $output_page_region_number . '}';
                }
                
                // if this is not the first row, then add a comma
                if ($output_rows != '') {
                    $output_rows .= ',';
                }
                
                $output_rows .= '{"cells":[' . $output_cells . ']}';
            }
            
            return '"' . $area . '": {"rows": [' . $output_rows . ']}';
        }
        
        // prepare area data for JavaScript
        switch ($layout) {
            case 'one_column':
                $output_areas_for_javascript =
                    'var areas = {
                        ' . get_area_for_javascript('site_top', $areas) . ',
                        ' . get_area_for_javascript('site_header', $areas) . ',
                        ' . get_area_for_javascript('area_header', $areas) . ',
                        ' . get_area_for_javascript('page_header', $areas) . ',
                        ' . get_area_for_javascript('page_content', $areas) . ',
                        ' . get_area_for_javascript('page_footer', $areas) . ',
                        ' . get_area_for_javascript('area_footer', $areas) . ',
                        ' . get_area_for_javascript('site_footer', $areas) . '
                    };';
                        
                break;
                
            case 'one_column_email':
                $output_areas_for_javascript =
                    'var areas = {
                        ' . get_area_for_javascript('site_top', $areas) . ',
                        ' . get_area_for_javascript('site_header', $areas) . ',
                        ' . get_area_for_javascript('area_header', $areas) . ',
                        ' . get_area_for_javascript('page_header', $areas) . ',
                        ' . get_area_for_javascript('page_content', $areas) . ',
                        ' . get_area_for_javascript('page_footer', $areas) . ',
                        ' . get_area_for_javascript('area_footer', $areas) . ',
                        ' . get_area_for_javascript('site_footer', $areas) . '
                    };';
                        
                break;    
                            
            case 'one_column_mobile':
                $output_areas_for_javascript =
                    'var areas = {
                        ' . get_area_for_javascript('site_top', $areas) . ',
                        ' . get_area_for_javascript('site_header', $areas) . ',
                        ' . get_area_for_javascript('area_header', $areas) . ',
                        ' . get_area_for_javascript('page_header', $areas) . ',
                        ' . get_area_for_javascript('page_content', $areas) . ',
                        ' . get_area_for_javascript('page_footer', $areas) . ',
                        ' . get_area_for_javascript('area_footer', $areas) . ',
                        ' . get_area_for_javascript('site_footer', $areas) . '
                    };';
                        
                break;
                
            case 'two_column_sidebar_left':
                $output_areas_for_javascript =
                    'var areas = {
                        ' . get_area_for_javascript('site_top', $areas) . ',
                        ' . get_area_for_javascript('site_header', $areas) . ',
                        ' . get_area_for_javascript('area_header', $areas) . ',
                        ' . get_area_for_javascript('page_header', $areas) . ',
                        ' . get_area_for_javascript('page_content', $areas) . ',
                        ' . get_area_for_javascript('sidebar', $areas) . ',
                        ' . get_area_for_javascript('page_footer', $areas) . ',
                        ' . get_area_for_javascript('area_footer', $areas) . ',
                        ' . get_area_for_javascript('site_footer', $areas) . '
                    };';
                        
                break;
            
            case 'two_column_sidebar_right':
                $output_areas_for_javascript =
                    'var areas = {
                        ' . get_area_for_javascript('site_top', $areas) . ',
                        ' . get_area_for_javascript('site_header', $areas) . ',
                        ' . get_area_for_javascript('area_header', $areas) . ',
                        ' . get_area_for_javascript('page_header', $areas) . ',
                        ' . get_area_for_javascript('page_content', $areas) . ',
                        ' . get_area_for_javascript('sidebar', $areas) . ',
                        ' . get_area_for_javascript('page_footer', $areas) . ',
                        ' . get_area_for_javascript('area_footer', $areas) . ',
                        ' . get_area_for_javascript('site_footer', $areas) . '
                    };';
                        
                break;
                
            case 'three_column_sidebar_left':
                $output_areas_for_javascript =
                    'var areas = {
                        ' . get_area_for_javascript('site_top', $areas) . ',
                        ' . get_area_for_javascript('site_header', $areas) . ',
                        ' . get_area_for_javascript('area_header', $areas) . ',
                        ' . get_area_for_javascript('page_header', $areas) . ',
                        ' . get_area_for_javascript('sidebar', $areas) . ',
                        ' . get_area_for_javascript('page_content_left', $areas) . ',
                        ' . get_area_for_javascript('page_content_right', $areas) . ',
                        ' . get_area_for_javascript('page_footer', $areas) . ',
                        ' . get_area_for_javascript('area_footer', $areas) . ',
                        ' . get_area_for_javascript('site_footer', $areas) . '
                    };';
                        
                break;
        }
        
    // else the areas field does exist, so the form has been submitted, so prepare areas data that was submitted
    } else {
        $output_areas_for_javascript = 'var areas = ' . $liveform->get_field_value('areas') . ';';
    }
    
    // if there is a send to, then set the cancel button URL to the send to
    if ($liveform->get_field_value('send_to') != '') {
        $output_cancel_button_url = h(escape_javascript($liveform->get_field_value('send_to')));
        
    // else there is not a send to, so set the cancel button URL to the view styles screen
    } else {
        $output_cancel_button_url = OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_styles.php';
    }
    
    // Find if style is being used by a folder
    $query =
        "SELECT
            COUNT(folder_id)
        FROM folder
        WHERE
            (folder_style = '" . escape($liveform->get_field_value('id')) . "')
            OR (mobile_style_id = '" . escape($liveform->get_field_value('id')) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $folders_using_style = $row[0];
    
    // Find if style is being used by a page
    $query =
        "SELECT
            COUNT(page_id)
        FROM page
        WHERE
            (page_style = '" . escape($liveform->get_field_value('id')) . "')
            OR (mobile_style_id = '" . escape($liveform->get_field_value('id')) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $pages_using_style = $row[0];

    // if style is being used by either a folder or a page
    if (($folders_using_style > 0) || ($pages_using_style > 0)) {
        // output delete button with alert
        $output_delete_button = '<input type="button" value="Delete" class="delete" onclick="alert(\'You may not delete this page style because it is being used by at least one folder or page.\')" />';
    } else {
        // output regular delete button
        $output_delete_button = '<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This page style will be permanently deleted.\')">';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($name) . '</h1>
            <div class="subheading">Last Modified ' . get_relative_time(array('timestamp' => $last_modified_timestamp)) . ' by ' . h($last_modified_username) . '</div>
        </div>
        <div id="button_bar"><a href="view_system_style_source.php?id=' . h($liveform->get_field_value('id')) . '&amp;send_to=' . h(urlencode(PATH . SOFTWARE_DIRECTORY . '/edit_system_style.php?id=' . $liveform->get_field_value('id') . '&send_to=' . urlencode($liveform->get_field_value('send_to')))) . '">View Source</a></div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Style Designer</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Edit this System Page Style that can be associated with one or many Pages.</div>
            <form id="style_designer_form" name="style_designer_form" action="edit_system_style.php" method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id')) . '
                ' . $liveform->output_field(array('type'=>'hidden', 'id'=>'areas', 'name'=>'areas')) . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'send_to')) . '
                <h2 style="margin-bottom: 1em">Page Style Name</h2>
                Name: ' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '
                <h2 style="margin-bottom: 1em">Add or Remove Cells and Regions in the Page Layout below</h2>
                <table style="margin-bottom: 1em; width: 100%">
                    <tr>
                        <td style="text-align: left; width: 33%; vertical-align: middle">Page Layout: ' . $output_layout_name . '<span class="theme_fold_css" style="padding:0"><br />.' . h($layout) . ' .' . h(get_class_name($name)) . $output_additional_body_classes . '</span></td>
                        <td style="text-align: center; width: 33%; vertical-align: middle">' . $output_social_networking_position . '</td>
                        <td style="text-align: right; width: 33%; vertical-align: middle">Empty Cell Width: ' . $liveform->output_field(array('type'=>'text', 'name'=>'empty_cell_width_percentage', 'size'=>'5', 'maxlength'=>'5')) . ' %</td>
                    </tr>
                </table>
                <script type="text/javascript">
                    ' . $output_areas_for_javascript . '
                    
                    ' . get_style_designer_regions_for_javascript() . '
                    
                    $(document).ready(function () {
                        initialize_style_designer();
                    });
                </script>
                ' . get_style_designer_content($layout) . '
                <h2 style="margin-bottom: 1em">Override the activated Theme</h2>
                <div style="margin-bottom: 1.5em">Theme: ' . $liveform->output_field(array('type' => 'select', 'name' => 'theme_id', 'options' => get_theme_options())) . '</div>
                <h2 style="margin-bottom: 1em">Add Additional Classes to the Body of this Page Style</h2>
                <div style="margin-bottom: 1.5em">Additional Body Classes: ' . $liveform->output_field(array('type'=>'text', 'name'=>'additional_body_classes', 'size'=>'60', 'maxlength'=>'255')) . '&nbsp;&nbsp;<span style="white-space: nowrap">(separate classes with a space, do not include period)</span></div>
                <h2 style="margin-bottom: 1em">Collection</h2>
                <div style="margin-bottom: 1.5em">' . $liveform->output_field(array('type' => 'radio', 'name' => 'collection', 'id' => 'collection_a', 'value' => 'a', 'class' => 'radio')) . '<label for="collection_a">Collection A</label>&nbsp; ' . $liveform->output_field(array('type' => 'radio', 'name' => 'collection', 'id' => 'collection_b', 'value' => 'b', 'class' => 'radio')) . '<label for="collection_b">Collection B</label></div>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'' . $output_cancel_button_url . '\'" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="button" name="duplicate" value="Duplicate" onclick="javascript:window.location.href=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/duplicate_style.php?id=' . h(escape_javascript($liveform->get_field_value('id'))) . get_token_query_string_field() . '\';" class="submit">&nbsp;&nbsp;&nbsp;' . $output_delete_button . '
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted, so process the form
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // if the user selected to delete the style, then delete it
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        // Find if style is being used by a folder
        $query =
            "SELECT
                COUNT(folder_id)
            FROM folder
            WHERE
                (folder_style = '" . escape($liveform->get_field_value('id')) . "')
                OR (mobile_style_id = '" . escape($liveform->get_field_value('id')) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $folders_using_style = $row[0];
        
        // Find if style is being used by a page
        $query =
            "SELECT
                COUNT(page_id)
            FROM page
            WHERE
                (page_style = '" . escape($liveform->get_field_value('id')) . "')
                OR (mobile_style_id = '" . escape($liveform->get_field_value('id')) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $pages_using_style = $row[0];

        // if style is being used by either a folder or a page, output error
        if (($folders_using_style > 0) || ($pages_using_style > 0)) {
            output_error('You may not delete this page style because it is being used by at least one folder or page.');
        
        // else the style is not being used by a folder or page, so delete the style
        } else {
            $query = "DELETE FROM style WHERE style_id = '" . escape($liveform->get_field_value('id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $query = "DELETE FROM system_style_cells WHERE style_id = '" . escape($liveform->get_field_value('id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            db("DELETE FROM preview_styles WHERE style_id = '" . escape($liveform->get_field_value('id')) . "'");
            
            log_activity('style (' . $name . ') was deleted', $_SESSION['sessionusername']);
            $notice = 'The style has been deleted.';
        }
        
    // else the user selected to save the style, so save it
    } else {
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is not already an error for the name field, then check if name is already in use
        if ($liveform->check_field_error('name') == FALSE) {
            $query = "SELECT style_id FROM style WHERE (style_name = '" . escape($liveform->get_field_value('name')) . "') AND (style_id != '" . escape($liveform->get_field_value('id')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if name is already in use by a different style, prepare error
            if (mysqli_num_rows($result) > 0) {
                $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            }
        }
        
        // if an empty cell width percentage was entered and it is not numeric, then add error
        if (($liveform->get_field_value('empty_cell_width_percentage') != '') && (is_numeric($liveform->get_field_value('empty_cell_width_percentage')) == FALSE)) {
            $liveform->mark_error('empty_cell_width_percentage', 'The empty cell width must be a numeric value.');
        }
        
        // if there is an error, forward user back to previous screen
        if ($liveform->check_form_errors() == TRUE) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_system_style.php?id=' . $liveform->get_field_value('id') . '&send_to=' . urlencode($liveform->get_field_value('send_to')));
            exit();
        }
        
        // include JSON library in order to convert JSON string to PHP array
        include_once('JSON.php');
        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        
        // create PHP array from JSON string
        $areas = $json->decode($liveform->get_field_value('areas'));
        
        // if the empty cell width percentage is 0, then set it to empty string
        if ($liveform->get_field_value('empty_cell_width_percentage') == 0) {
            $liveform->assign_field_value('empty_cell_width_percentage', '');
        }

        $sql_social_networking_position = "";

        // If social networking is enabled, then update position value.
        if (SOCIAL_NETWORKING == TRUE) {
            $sql_social_networking_position = "social_networking_position = '" . escape($liveform->get_field_value('social_networking_position')) . "',";
        }
        
        // update page style in database
        $query =
            "UPDATE style
            SET
                style_name = '" . escape($liveform->get_field_value('name')) . "',
                style_empty_cell_width_percentage = '" . escape($liveform->get_field_value('empty_cell_width_percentage')) . "',
                style_code = '" . escape(get_system_style_code($layout, $areas, $liveform->get_field_value('name'), $liveform->get_field_value('empty_cell_width_percentage'), $liveform->get_field_value('additional_body_classes'))) . "',
                " . $sql_social_networking_position . "
                theme_id = '" . escape($liveform->get_field_value('theme_id')) . "',
                additional_body_classes = '" . escape($liveform->get_field_value('additional_body_classes')) . "',
                collection = '" . escape($liveform->get_field_value('collection')) . "',
                style_user = '" . $user['id'] . "',
                style_timestamp = UNIX_TIMESTAMP()
            WHERE style_id = '" . $liveform->get_field_value('id') . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete old system style cells in database
        $query = "DELETE FROM system_style_cells WHERE style_id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through the areas, in order to add cells to database
        foreach ($areas as $area_name => $area) {
            // loop through the rows in this area in order to add cells to database
            foreach ($area['rows'] as $row_key => $row) {
                // loop through the cells in this row in order to add cells to database
                foreach ($row['cells'] as $cell_key => $cell) {
                    // add cell to database
                    $query =
                        "INSERT INTO system_style_cells (
                            style_id,
                            area,
                            `row`, # Backticks for reserved word.
                            col,
                            region_type,
                            region_name)
                        VALUES (
                            '" . $liveform->get_field_value('id') . "',
                            '" . escape($area_name) . "',
                            '" . escape($row_key + 1) . "',
                            '" . escape($cell_key + 1) . "',
                            '" . escape($cell['region_type']) . "',
                            '" . escape($cell['region_name']) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
        }
        
        log_activity('style (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        $notice = 'The style has been saved.';
    }
    
    // if there is a send to set, then forward user to send to
    if ($liveform->get_field_value('send_to') != '') {
        header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
        
    // else there is not a send to set, so forward user to view styles screen
    } else {
        $liveform_view_styles = new liveform('view_styles');
        $liveform_view_styles->add_notice($notice);
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_styles.php');
    }
    
    $liveform->remove_form();
}
?>