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

$output = '';

// initiate object as the base
$object_id = 'base_object';

// get the file id
$file_id = $_GET['file_id'];

// get the module id
$module_id = $_GET['module_id'];

// get the area name from the module id
preg_match('/site_wide|site_border|site_top|site_header|area_border|area_header|page_border|page_wrapper|page_header|page_content_left|page_content_right|page_content|sidebar|page_footer|area_footer|site_footer_border|site_footer|ad_region|menu_region/i', $module_id, $matches);
$area_name = $matches[0];

// set the module id as the last opened module in the session
$_SESSION['software']['theme_designer'][$file_id]['last_opened_module'] = $module_id;

// if this is a ad region, then get the ad region name and module id so that we can get the modules below
if (($area_name == 'ad_region') && ((preg_match('/ad_region_(.*?)_[ad_region_layout|ad_region_background_borders_and_spacing|ad_region_menu|ad_region_menu_item|ad_region_menu_item_hover]/i', $module_id, $matches) > 0))) {
    $object_id = $matches[1];
    $module_id = preg_replace('/ad_region_' . $object_id . '/i', '', $module_id);

// else if this is a menu region, then get the menu name and module id so that we can get the modules below
} elseif (($area_name == 'menu_region') && ((preg_match('/menu_region_(.*?)_[menu_region_layout|menu_region_background_borders_and_spacing|menu_region_menu_item|menu_region_menu_item_hover|menu_region_submenu_background_borders_and_spacing|menu_region_submenu_menu_item|menu_region_submenu_menu_item_hover]/i', $module_id, $matches) > 0))) {
    $object_id = $matches[1];
    $module_id = preg_replace('/menu_region_' . $object_id . '/i', '', $module_id);
    
// else if there is an row and column object, then get the object and module id so that we can get the modules below
} elseif (preg_match('/.*?_(r\d*c\d*)_*?/i', $module_id, $matches) > 0) {
    $object_id = $matches[1];
    $module_id = preg_replace('/' . $area_name . '_' . $object_id . '/i', '', $module_id);

// else just remove the area name from the module id
} else {
    // if the area name is not site wide, then remove it from the module id
    if ($area_name != 'site_wide') {
        $module_id = preg_replace('/' . $area_name . '/i', '', $module_id);
    }
}

// output the module that was passed via the GET method
switch($module_id) {
    case 'site_wide_general':
        $output = output_theme_designer_module($file_id, 'site_wide', $object_id, 'base_module', array('pre_styling', 'site_colors', 'advanced_styling'));
        break;
    
    case 'site_wide_input':
        $output = output_theme_designer_module($file_id, 'site_wide', $object_id, 'input', array('background', 'borders', 'rounded_corners', 'margin', 'padding', 'font', 'text_decoration'));
        break;
    
    case 'site_wide_primary_buttons':
        $output = output_theme_designer_module($file_id, 'site_wide', $object_id, 'primary_buttons', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
    
    case 'site_wide_primary_buttons_hover':
        $output = output_theme_designer_module($file_id, 'site_wide', $object_id, 'primary_buttons_hover', array('background', 'borders', 'rounded_corners', 'shadows', 'font', 'text_decoration'));
        break;
    
    case 'site_wide_secondary_buttons':
        $output = output_theme_designer_module($file_id, 'site_wide', $object_id, 'secondary_buttons', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
        
    case 'site_wide_secondary_buttons_hover':
        $output = output_theme_designer_module($file_id, 'site_wide', $object_id, 'secondary_buttons_hover', array('background', 'borders', 'rounded_corners', 'shadows', 'font', 'text_decoration'));
        break;
        
    case 'site_wide_text':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'text', array('font', 'line_height'));
        break;
        
    case 'body':
        $output = output_theme_designer_module($file_id, 'body', $object_id, 'base_module', array('background'));
        break;
        
    case 'email_border':
        $output = output_theme_designer_module($file_id, 'email_border', $object_id, 'base_module', array('width', 'position', 'background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;

    case 'mobile_border':
        $output = output_theme_designer_module($file_id, 'mobile_border', $object_id, 'base_module', array('width', 'position', 'background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
    
    case '_layout':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'layout', array('width', 'position'));
        break;
    
    case '_background_borders_and_spacing':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'background_borders_and_spacing', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
        
    case 'site_wide_text':
    case '_text':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'text', array('font'));
        break;
        
    case 'site_wide_headings_general':
    case '_headings_general':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'headings_general', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'line_height'));
        break;
    
    case 'site_wide_heading_1':    
    case '_heading_1':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'heading_1', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'line_height'));
        break;
    
    case 'site_wide_heading_2':
    case '_heading_2':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'heading_2', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'line_height'));
        break;
    
    case 'site_wide_heading_3':    
    case '_heading_3':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'heading_3', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'line_height'));
        break;

    case 'site_wide_heading_4':
    case '_heading_4':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'heading_4', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'line_height'));
        break;
    
    case 'site_wide_heading_5':
    case '_heading_5':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'heading_5', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'line_height'));
        break;
    
    case 'site_wide_heading_6':
    case '_heading_6':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'heading_6', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'line_height'));
        break;
    
    case 'site_wide_paragraph':
    case '_paragraph':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'paragraph', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font'));
        break;
    
    case 'site_wide_links':
    case '_links':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'links', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
        
    case 'site_wide_links_hover':
    case '_links_hover':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'links_hover', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
    
    case 'site_wide_image_primary':
    case '_image_primary':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'image_primary', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
        
    case 'site_wide_image_secondary':
    case '_image_secondary':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'image_secondary', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
    
    case '_ad_region_layout':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'layout', array('width', 'height'), 'ad');
        break;
    
    case '_ad_region_background_borders_and_spacing':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'background_borders_and_spacing', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
        
    case '_ad_region_menu':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'menu', array('ad_region_menu_position'));
        break;
        
    case '_ad_region_menu_item':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'menu_item', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
        
    case '_ad_region_menu_item_hover':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'menu_item_hover', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;

    case '_ad_region_previous_and_next_buttons':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'previous_and_next_buttons', array('ad_region_previous_and_next_buttons'));
        break;
        
    case '_menu_region_layout':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'layout', array('width', 'position', 'menu_orientation'));
        break;
    
    case '_menu_region_background_borders_and_spacing':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'background_borders_and_spacing', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
    
    case '_menu_region_menu_item':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'menu_item', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
        
    case '_menu_region_menu_item_hover':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'menu_item_hover', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
    
    case '_menu_region_submenu_background_borders_and_spacing':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'submenu_background_borders_and_spacing', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
    
    case '_menu_region_submenu_menu_item':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'submenu_menu_item', array('width', 'background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
    
    case '_menu_region_submenu_menu_item_hover':
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'submenu_menu_item_hover', array('background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding', 'font', 'text_decoration'));
        break;
        
    default:
        $output = output_theme_designer_module($file_id, $area_name, $object_id, 'base_module', array('width', 'position', 'background', 'borders', 'rounded_corners', 'shadows', 'margin', 'padding'));
        break;
}

print $output;

// this function outputs a module for the theme designer based on what was passed into the function
function output_theme_designer_module($file_id, $area, $object, $module, $css_rules = array(), $region_type = '')
{
    include_once('liveform.class.php');
    $liveform = new liveform('theme_designer');
    
    $object_row = '';
    $object_column = '';
    
    // if the object is the base object, then set the object's row and column to 0
    if ($object == 'base_object') {
        $object_row = 0;
        $object_column = 0;
    
    // else break the object's row and column out of the object name
    } else {
        preg_match('/r(\d*).*?/i', $object, $matches);
        $object_row = $matches[1];
        
        preg_match('/c(\d*).*?/i', $object, $matches);
        $object_column = $matches[1];
    }
    
    $module_for_sql = '';
    
    // if the module is not the base module, then set the module for sql to the module
    if ($module != 'base_module') {
        $module_for_sql = $module;
    }
    
    $properties = array();
    
    // if there is preview properties in the session, then set the properties array to them
    if ((is_array($_SESSION['software']['theme_designer'][$file_id]['preview_properties'][$area][$object][$module]) == TRUE) && (empty($_SESSION['software']['theme_designer'][$file_id]['preview_properties'][$area][$object][$module]) == FALSE)) {
        $properties = $_SESSION['software']['theme_designer'][$file_id]['preview_properties'][$area][$object][$module];
        
    // else get the module's properties and values from the database
    } else {
        $where = '';
        
        // if this is a ad region then set the sql where to get the ad region properties
        if ($area == 'ad_region') {
            $query = 
                "SELECT
                    property,
                    value
                FROM system_theme_css_rules
                WHERE 
                    (file_id = '" . escape($file_id) . "') 
                    AND (region_type = 'ad') 
                    AND (region_name = '" . escape($object) . "')";
            
        // else if this is a menu region, then get the menu region properties
        } elseif ($area == 'menu_region') {
            $query = 
                "SELECT
                    property,
                    value
                FROM system_theme_css_rules
                WHERE 
                    (file_id = '" . escape($file_id) . "') 
                    AND (region_type = 'menu') 
                    AND (region_name = '" . escape($object) . "')";
        
        // else get the properties normally
        } else {
            $query = 
                "SELECT
                    property,
                    value
                FROM system_theme_css_rules
                WHERE 
                    (file_id = '" . escape($file_id) . "') 
                    AND (area = '" . escape($area) . "') 
                    AND (`row` = '" . escape($object_row) . "') 
                    AND (col = '" . escape($object_column) . "') 
                    AND (module = '" . escape($module_for_sql) . "') 
                    ";
        }
        
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while($row = mysqli_fetch_assoc($result)) {
            $properties[$row['property']] = $row['value'];
        }
    }
    
    $output = '';
    
    // loop through the css rules array, and output any css rules that need to be outputted
    foreach($css_rules as $css_rule) {
        switch($css_rule) {
            case 'advanced_styling':
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][advanced_styling]', $properties['advanced_styling']);
                
                $output .= 
                    '<tr>
                        <td>Advanced Styling:</td>
                        <td>
                            <span onclick="open_advanced_styling()" id="advanced_styling_button">CSS</span>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>$area . '[' . $object . '][' . $module . '][advanced_styling]', 'id'=>'advanced_styling_textarea', 'value'=>'', 'style'=>'display: none')) . '
                            <div id="advanced_styling" style="display: none">
                                <div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'textarea', 'id'=>'advanced_styling_textarea_temp')) . '</div>
                                <div><input type="button" value="Update" class="submit-primary" onclick="$(\'#advanced_styling\').dialog(\'close\')" /></div>
                            </div>
                        </td>
                    </tr>';
                break;
            
            case 'pre_styling':
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][pre_styling]', $properties['pre_styling']);
                
                $output .= 
                    '<tr>
                        <td>Pre Styling:</td>
                        <td>
                            <span onclick="open_pre_styling()" id="advanced_styling_button">CSS</span>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>$area . '[' . $object . '][' . $module . '][pre_styling]', 'id'=>'pre_styling_textarea', 'value'=>'', 'style'=>'display: none')) . '
                            <div id="pre_styling" style="display: none">
                                <div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'textarea', 'id'=>'pre_styling_textarea_temp')) . '</div>
                                <div><input type="button" value="Update" class="submit-primary" onclick="$(\'#pre_styling\').dialog(\'close\')" /></div>
                            </div>
                        </td>
                    </tr>';
            break;
            
            case 'ad_region_menu_position':
                // fill in the fields with the data values from the database
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][position]', $properties['position']);
                
                $output .= 
                    '<tr>
                        <td>Position:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][position]', 'options'=>array('Top Left' => 'top_left', 'Top Right' => 'top_right', 'Bottom Left' => 'bottom_left', 'Bottom Right' => 'bottom_right'))) . '</td>
                    </tr>';
                break;

            case 'ad_region_previous_and_next_buttons':
                // Fill in the fields with the data values from the database.
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][previous_and_next_buttons_toggle]', $properties['previous_and_next_buttons_toggle']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][previous_and_next_buttons_horizontal_offset]', $properties['previous_and_next_buttons_horizontal_offset']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][previous_and_next_buttons_vertical_offset]', $properties['previous_and_next_buttons_vertical_offset']);
                
                $previous_and_next_buttons_option_row_style = ' style="display: none"';
                
                // If previous & next buttons are enabled, then display the previous & next button options.
                if ($properties['previous_and_next_buttons_toggle'] == 1) {
                    $previous_and_next_buttons_option_row_style = '';
                }
                
                $output .= 
                    '<tr>
                        <td><label for="' . $area . '_' . $object . '_' . $module . '_previous_and_next_buttons_toggle">Show Buttons:<label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>$area . '_' . $object . '_' . $module . '_previous_and_next_buttons_toggle', 'name'=>$area . '[' . $object . '][' . $module . '][previous_and_next_buttons_toggle]', 'value'=>'1', 'onclick'=>'show_or_hide_css_rule_previous_and_next_buttons_options(\'' . $area . '_' . $object . '_' . $module . '\', this)', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_previous_and_next_buttons_horizontal_offset_row"' . $previous_and_next_buttons_option_row_style . '>
                        <td style="padding-left: 2em">Horizontal Offset:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][previous_and_next_buttons_horizontal_offset]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_previous_and_next_buttons_vertical_offset_row"' . $previous_and_next_buttons_option_row_style . '>
                        <td style="padding-left: 2em">Vertical Offset:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][previous_and_next_buttons_vertical_offset]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>';
                break;
            
            case 'borders':
                // fill in the fields with the data values from the database
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][borders_toggle]', $properties['borders_toggle']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][border_size]', $properties['border_size']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][border_color]', $properties['border_color']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][border_style]', $properties['border_style']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][border_position]', $properties['border_position']);
                
                $border_options_row_style = ' style="display: none;"';
                
                // if the border's toggle is on, then show the border options
                if ($liveform->get_field_value($area . '[' . $object . '][' . $module . '][borders_toggle]') == 1) {
                    $border_options_row_style = '';
                }
                
                // if there is a color, then set it as the value for the color picker and field
                if ($properties['border_color'] != '') {
                    $border_color_label = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][border_color]');
                    $border_color_value = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][border_color]');
                
                } else {
                    $border_color_label = 'Inherit';
                    $border_color_value = '#FFFFFF';
                }
                
                $output .= 
                    '<tr>
                        <td><label for="' . $area . '_' . $object . '_' . $module . '_borders_toggle">Borders:<label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>$area . '_' . $object . '_' . $module . '_borders_toggle', 'name'=>$area . '[' . $object . '][' . $module . '][borders_toggle]', 'value'=>'1', 'onclick'=>'show_or_hide_css_rule_border_options(\'' . $area . '_' . $object . '_' . $module . '\', this)', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_border_size_row"' . $border_options_row_style . '>
                        <td style="padding-left: 2em">Size:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][border_size]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_border_color_row"' . $border_options_row_style . '>
                        <td style="padding-left: 2em">Color:</td>
                        <td>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][border_color]', 'maxlength'=>'7', 'size'=>'7', 'value'=>'')) . '<span class="color_picker_toggle" style="background-color: ' . h($border_color_value) . '"></span>&nbsp;<span class="text_beside_input">' . h($border_color_label) . '</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_border_style_row"' . $border_options_row_style . '>
                        <td style="padding-left: 2em">Style:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][border_style]', 'options'=>array('Solid' => 'solid', 'Dashed' => 'dashed', 'Dotted' => 'dotted', 'Double' => 'double', 'Groove' => 'groove', 'Hidden' => 'hidden', 'Inset' => 'inset', 'Inherit' => 'inherit', 'Outset' => 'outset', 'Ridge' => 'ridge'))) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_border_position_row"' . $border_options_row_style . '>
                        <td style="padding-left: 2em">Position:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][border_position]', 'options'=>array('All' => 'all', 'Top' => 'top', 'Right' => 'right', 'Bottom' => 'bottom', 'Left' => 'left'))) . '</td>
                    </tr>';
                break;
                
            case 'background':
                // fill in the fields with the data values from the database
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][background_type]', $properties['background_type']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][background_color]', $properties['background_color']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][background_image]', $properties['background_image']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][background_horizontal_position]', $properties['background_horizontal_position']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][background_vertical_position]', $properties['background_vertical_position']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][background_repeat]', $properties['background_repeat']);
                
                $background_color_row_style = ' style="display: none;"';
                $background_image_row_style = ' style="display: none;"';
                $background_horizontal_position_row_style = ' style="display: none;"';
                $background_vertical_position_row_style = ' style="display: none;"';
                $background_repeat_row_style = ' style="display: none;"';
                
                // if the background type is set to solid color, then display the solid color options
                if ($liveform->get_field_value($area . '[' . $object . '][' . $module . '][background_type]') == 'solid_color') {
                    $background_color_row_style = '';
                
                // else if the background type is set to image, then output the image options
                } elseif ($liveform->get_field_value($area . '[' . $object . '][' . $module . '][background_type]') == 'image') {
                    $background_color_row_style = '';
                    $background_image_row_style = '';
                    $background_horizontal_position_row_style = '';
                    $background_vertical_position_row_style = '';
                    $background_repeat_row_style = '';
                }
                
                // if there is a color, then set it as the value for the color picker and field
                if ($properties['background_color'] != '') {
                    $background_color_label = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][background_color]');
                    $background_color_value = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][background_color]');
                
                } else {
                    $background_color_label = 'Inherit';
                    $background_color_value = '#FFFFFF';
                }
                
                $output .= 
                    '<tr>
                        <td>Background:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][background_type]', 'options'=>array('' => '', 'Solid Color' => 'solid_color', 'Image' => 'image'), 'onchange'=>'show_or_hide_background_options(this.options[this.selectedIndex].value, \'' . $area . '_' . $object . '_' . $module . '\')')) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_background_color_row"' . $background_color_row_style . '>
                        <td style="padding-left: 2em">Color:</td>
                        <td>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][background_color]', 'maxlength'=>'7', 'size'=>'7', 'value'=>'')) . '<span class="color_picker_toggle" style="background-color: ' . h($background_color_value) . '"></span>&nbsp;<span class="text_beside_input">' . h($background_color_label) . '</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_background_image_row"' . $background_image_row_style . '>
                        <td style="padding-left: 2em">File:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][background_image]', 'options'=>select_files_for_theme_designer())) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_background_horizontal_position_row"' . $background_horizontal_position_row_style . '>
                        <td style="padding-left: 2em">Horizontal Position:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][background_horizontal_position]', 'options'=>array('Left' => 'left', 'Center' => 'center', 'Right' => 'right'))) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_background_vertical_position_row"' . $background_vertical_position_row_style . '>
                        <td style="padding-left: 2em">Vertical Position:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][background_vertical_position]', 'options'=>array('Top' => 'top', 'Center' => 'center', 'Bottom' => 'bottom'))) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_background_repeat_row"' . $background_repeat_row_style . '>
                        <td style="padding-left: 2em">Repeat:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][background_repeat]', 'options'=>array('Yes' => '', 'Horizontal' => 'repeat_x', 'Vertical' => 'repeat_y', 'No' => 'no_repeat'))) . '</td>
                    </tr>
                    ';
                break;
            
            case 'font':
                // fill in the fields with the data values from the database
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_family]', $properties['font_family']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_color]', $properties['font_color']);

                    // if the font size is not blank, then get it's value
                    if ($properties['font_size'] != '') {
                        $font_size = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['font_size']);
                        
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_size][amount]', $font_size);
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_size][unit]', preg_replace('/' . $font_size . '(.*?)/i', '$1', $properties['font_size']));
                    
                    // else default the field to blank
                    } else {
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_size][amount]', '');
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_size][unit]', '');
                    }
                    
                    $output_font_size_row = 
                        '<tr>
                            <td>Font Size:</td>
                            <td>' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][font_size][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][font_size][unit]', 'options'=>array('%' => '%', 'px' => 'px', 'em' => 'em'))) . '</td>
                        </tr>';
                
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_style]', $properties['font_style']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][font_weight]', $properties['font_weight']);
                
                // if there is a color, then set it as the value for the color picker and field
                if ($properties['font_color'] != '') {
                    $font_color_label = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][font_color]');
                    $font_color_value = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][font_color]');
                
                } else {
                    $font_color_label = 'Inherit';
                    $font_color_value = '#FFFFFF';
                }
                
                $output .= 
                    '<tr>
                        <td>Font Family:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][font_family]', 'options'=>array('' => '',
'Arial' => 'Arial,sans-serif',
'Arial Black' => '\'Arial Black\',Gadget,sans-serif',
'Arial Narrow' => '\'Arial Narrow\',sans-serif',
'Century Gothic' => '\'Century Gothic\',sans-serif',
'Comic Sans MS' => '\'Comic Sans MS\',fantasy',
'Courier New' => '\'Courier New\',Courier,monospace',
'Garamond' => 'Garamond,serif',
'Georgia' => 'Georgia,serif',
'Gill Sans' => '\'Gill Sans\',sans-serif',
'Helvetica' => 'Helvetica,sans-serif',
'Impact' => 'Impact, Charcoal,sans-serif',
'Lucida Console' => '\'Lucida Console\',Monaco,monospace',
'Lucida Grande' => '\'Lucida Grande\',sans-serif',
'Lucida Sans Unicode' => '\'Lucida Sans Unicode\',\'Lucida Grande\',sans-serif',
'MS Sans Serif' => '\'MS Sans Serif\',Geneva,sans-serif',
'MS Serif' => '\'MS Serif\',\'New York\',sans-serif',
'Palatino Linotype' => '\'Palatino Linotype\',\'Book Antiqua\',Palatino,serif',
'Tahoma' => 'Tahoma,Geneva,sans-serif',
'Times New Roman' => '\'Times New Roman\',Times,serif',
'Trebuchet MS' => '\'Trebuchet MS\',Helvetica,sans-serif',
'Verdana' => 'Verdana,Geneva,sans-serif',
'-Abel-' => '\'-Abel-\',\'Abel\',sans-serif',
'-Aclonica-' => '\'-Aclonica-\',\'Aclonica\',sans-serif',
'-Actor-' => '\'-Actor-\',\'Actor\',sans-serif',
'-Alice-' => '\'-Alice-\',\'Alice\',serif',
'-Allan-' => '\'-Allan-\',\'Allan\',cursive',
'-Allerta Stencil-' => '\'-Allerta Stencil-\',\'Allerta Stencil\',sans-serif',
'-Allerta-' => '\'-Allerta-\',\'Allerta\',sans-serif',
'-Amaranth-' => '\'-Amaranth-\',\'Amaranth\',sans-serif',
'-Andika-' => '\'-Andika-\',\'Andika\',sans-serif',
'-Annie Use Your Telescope-' => '\'-Annie Use Your Telescope-\',\'Annie Use Your Telescope\',cursive',
'-Anton-' => '\'-Anton-\',\'Anton\',sans-serif',
'-Architects Daughter-' => '\'-Architects Daughter-\',\'Architects Daughter\',cursive',
'-Arimo-' => '\'-Arimo-\',\'Arimo\',sans-serif',
'-Arvo-' => '\'-Arvo-\',\'Arvo\',serif',
'-Bangers-' => '\'-Bangers-\',\'Bangers\',cursive',
'-Bentham-' => '\'-Bentham-\',\'Bentham\',serif',
'-Bevan-' => '\'-Bevan-\',\'Bevan\',serif',
'-Bitter-' => '\'-Bitter-\',\'Bitter\',serif',
'-Bowlby One SC-' => '\'-Bowlby One SC-\',\'Bowlby One SC\',cursive',
'-Cabin Condensed-' => '\'-Cabin Condensed-\',\'Cabin Condensed\',sans-serif',
'-Cabin Sketch-' => '\'-Cabin Sketch-\',\'Cabin Sketch\',cursive',
'-Cabin-' => '\'-Cabin-\',\'Cabin\',sans-serif',
'-Calligraffitti-' => '\'-Calligraffitti-\',\'Calligraffitti\',cursive',
'-Cantarell-' => '\'-Cantarell-\',\'Cantarell\',sans-serif',
'-Cardo-' => '\'-Cardo-\',\'Cardo\',serif',
'-Carme-' => '\'-Carme-\',\'Carme\',sans-serif',
'-Carter One-' => '\'-Carter One-\',\'Carter One\',cursive',
'-Changa One-' => '\'-Changa One-\',\'Changa One\',cursive',
'-Cherry Cream Soda-' => '\'-Cherry Cream Soda-\',\'Cherry Cream Soda\',cursive',
'-Chewy-' => '\'-Chewy-\',\'Chewy\',cursive',
'-Chivo-' => '\'-Chivo-\',\'Chivo\',sans-serif',
'-Comfortaa-' => '\'-Comfortaa-\',\'Comfortaa\',cursive',
'-Coming Soon-' => '\'-Coming Soon-\',\'Coming Soon\',cursive',
'-Copse-' => '\'-Copse-\',\'Copse\',serif',
'-Cousine-' => '\'-Cousine-\',\'Cousine\',sans-serif',
'-Covered By Your Grace-' => '\'-Covered By Your Grace-\',\'Covered By Your Grace\',cursive',
'-Crafty Girls-' => '\'-Crafty Girls-\',\'Crafty Girls\',cursive',
'-Crimson Text-' => '\'-Crimson Text-\',\'Crimson Text\',serif',
'-Crushed-' => '\'-Crushed-\',\'Crushed\',cursive',
'-Cuprum-' => '\'-Cuprum-\',\'Cuprum\',sans-serif',
'-Dancing Script-' => '\'-Dancing Script-\',\'Dancing Script\',cursive',
'-Delius-' => '\'-Delius-\',\'Delius\',cursive',
'-Didact Gothic-' => '\'-Didact Gothic-\',\'Didact Gothic\',sans-serif',
'-Droid Sans Mono-' => '\'-Droid Sans Mono-\',\'Droid Sans Mono\',sans-serif',
'-Droid Sans-' => '\'-Droid Sans-\',\'Droid Sans\',sans-serif',
'-Droid Serif-' => '\'-Droid Serif-\',\'Droid Serif\',serif',
'-EB Garamond-' => '\'-EB Garamond-\',\'EB Garamond\',serif',
'-Fontdiner Swanky-' => '\'-Fontdiner Swanky-\',\'Fontdiner Swanky\',cursive',
'-Francois One-' => '\'-Francois One-\',\'Francois One\',sans-serif',
'-Geo-' => '\'-Geo-\',\'Geo\',sans-serif',
'-Gloria Hallelujah-' => '\'-Gloria Hallelujah-\',\'Gloria Hallelujah\',cursive',
'-Goudy Bookletter 1911-' => '\'-Goudy Bookletter 1911-\',\'Goudy Bookletter 1911\',serif',
'-Gruppo-' => '\'-Gruppo-\',\'Gruppo\',sans-serif',
'-Hammersmith One-' => '\'-Hammersmith One-\',\'Hammersmith One\',sans-serif',
'-Homemade Apple-' => '\'-Homemade Apple-\',\'Homemade Apple\',cursive',
'-IM Fell DW Pica-' => '\'-IM Fell DW Pica-\',\'IM Fell DW Pica\',serif',
'-IM Fell English-' => '\'-IM Fell English-\',\'IM Fell English\',serif',
'-Inconsolata-' => '\'-Inconsolata-\',\'Inconsolata\',sans-serif',
'-Indie Flower-' => '\'-Indie Flower-\',\'Indie Flower\',cursive',
'-Istok Web-' => '\'-Istok Web-\',\'Istok Web\',sans-serif',
'-Josefin Sans-' => '\'-Josefin Sans-\',\'Josefin Sans\',sans-serif',
'-Josefin Slab-' => '\'-Josefin Slab-\',\'Josefin Slab\',serif',
'-Just Another Hand-' => '\'-Just Another Hand-\',\'Just Another Hand\',cursive',
'-Kameron-' => '\'-Kameron-\',\'Kameron\',serif',
'-Kranky-' => '\'-Kranky-\',\'Kranky\',cursive',
'-Kreon-' => '\'-Kreon-\',\'Kreon\',serif',
'-Kristi-' => '\'-Kristi-\',\'Kristi\',cursive',
'-Lato-' => '\'-Lato-\',\'Lato\',sans-serif',
'-Leckerli One-' => '\'-Leckerli One-\',\'Leckerli One\',cursive',
'-Lobster Two-' => '\'-Lobster Two-\',\'Lobster Two\',cursive',
'-Lobster-' => '\'-Lobster-\',\'Lobster\',cursive',
'-Love Ya Like A Sister-' => '\'-Love Ya Like A Sister-\',\'Love Ya Like A Sister\',cursive',
'-Luckiest Guy-' => '\'-Luckiest Guy-\',\'Luckiest Guy\',cursive',
'-Maiden Orange-' => '\'-Maiden Orange-\',\'Maiden Orange\',cursive',
'-Mako-' => '\'-Mako-\',\'Mako\',sans-serif',
'-Marck Script-' => '\'-Marck Script-\',\'Marck Script\',cursive',
'-Marmelad-' => '\'-Marmelad-\',\'Marmelad\',sans-serif',
'-Marvel-' => '\'-Marvel-\',\'Marvel\',sans-serif',
'-Maven Pro-' => '\'-Maven Pro-\',\'Maven Pro\',sans-serif',
'-MedievalSharp-' => '\'-MedievalSharp-\',\'MedievalSharp\',cursive',
'-Merienda One-' => '\'-Merienda One-\',\'Merienda One\',cursive',
'-Merriweather-' => '\'-Merriweather-\',\'Merriweather\',serif',
'-Metrophobic-' => '\'-Metrophobic-\',\'Metrophobic\',sans-serif',
'-Michroma-' => '\'-Michroma-\',\'Michroma\',sans-serif',
'-Miltonian Tattoo-' => '\'-Miltonian Tattoo-\',\'Miltonian Tattoo\',cursive',
'-Molengo-' => '\'-Molengo-\',\'Molengo\',sans-serif',
'-Mountains of Christmas-' => '\'-Mountains of Christmas-\',\'Mountains of Christmas\',cursive',
'-Muli-' => '\'-Muli-\',\'Muli\',sans-serif',
'-Neucha-' => '\'-Neucha-\',\'Neucha\',cursive',
'-Neuton-' => '\'-Neuton-\',\'Neuton\',serif',
'-News Cycle-' => '\'-News Cycle-\',\'News Cycle\',sans-serif',
'-Nobile-' => '\'-Nobile-\',\'Nobile\',sans-serif',
'-Nothing You Could Do-' => '\'-Nothing You Could Do-\',\'Nothing You Could Do\',cursive',
'-Nunito-' => '\'-Nunito-\',\'Nunito\',sans-serif',
'-Old Standard TT-' => '\'-Old Standard TT-\',\'Old Standard TT\',serif',
'-Open Sans Condensed-' => '\'-Open Sans Condensed-\',\'Open Sans Condensed\',sans-serif',
'-Open Sans-' => '\'-Open Sans-\',\'Open Sans\',sans-serif',
'-Orbitron-' => '\'-Orbitron-\',\'Orbitron\',sans-serif',
'-Oswald-' => '\'-Oswald-\',\'Oswald\',sans-serif',
'-Pacifico-' => '\'-Pacifico-\',\'Pacifico\',cursive',
'-Patrick Hand-' => '\'-Patrick Hand-\',\'Patrick Hand\',cursive',
'-Paytone One-' => '\'-Paytone One-\',\'Paytone One\',sans-serif',
'-Permanent Marker-' => '\'-Permanent Marker-\',\'Permanent Marker\',cursive',
'-Philosopher-' => '\'-Philosopher-\',\'Philosopher\',sans-serif',
'-Play-' => '\'-Play-\',\'Play\',sans-serif',
'-Playfair Display-' => '\'-Playfair Display-\',\'Playfair Display\',serif',
'-Podkova-' => '\'-Podkova-\',\'Podkova\',serif',
'-Poly-' => '\'-Poly-\',\'Poly\',serif',
'-PT Sans Caption-' => '\'-PT Sans Caption-\',\'PT Sans Caption\',sans-serif',
'-PT Sans Narrow-' => '\'-PT Sans Narrow-\',\'PT Sans Narrow\',sans-serif',
'-PT Sans-' => '\'-PT Sans-\',\'PT Sans\',sans-serif',
'-PT Serif Caption-' => '\'-PT Serif Caption-\',\'PT Serif Caption\',serif',
'-PT Serif-' => '\'-PT Serif-\',\'PT Serif\',serif',
'-Puritan-' => '\'-Puritan-\',\'Puritan\',sans-serif',
'-Quattrocento Sans-' => '\'-Quattrocento Sans-\',\'Quattrocento Sans\',sans-serif',
'-Quattrocento-' => '\'-Quattrocento-\',\'Quattrocento\',serif',
'-Questrial-' => '\'-Questrial-\',\'Questrial\',sans-serif',
'-Raleway-' => '\'-Raleway-\',\'Raleway\',cursive',
'-Rancho-' => '\'-Rancho-\',\'Rancho\',cursive',
'-Redressed-' => '\'-Redressed-\',\'Redressed\',cursive',
'-Reenie Beanie-' => '\'-Reenie Beanie-\',\'Reenie Beanie\',cursive',
'-Rochester-' => '\'-Rochester-\',\'Rochester\',cursive',
'-Rock Salt-' => '\'-Rock Salt-\',\'Rock Salt\',cursive',
'-Rokkitt-' => '\'-Rokkitt-\',\'Rokkitt\',serif',
'-Rosario-' => '\'-Rosario-\',\'Rosario\',sans-serif',
'-Salsa-' => '\'-Salsa-\',\'Salsa\',cursive',
'-Schoolbell-' => '\'-Schoolbell-\',\'Schoolbell\',cursive',
'-Shadows Into Light-' => '\'-Shadows Into Light-\',\'Shadows Into Light\',cursive',
'-Shanti-' => '\'-Shanti-\',\'Shanti\',sans-serif',
'-Signika-' => '\'-Signika-\',\'Signika\',sans-serif',
'-Six Caps-' => '\'-Six Caps-\',\'Six Caps\',sans-serif',
'-Slackey-' => '\'-Slackey-\',\'Slackey\',cursive',
'-Special Elite-' => '\'-Special Elite-\',\'Special Elite\',cursive',
'-Sue Ellen Francisco-' => '\'-Sue Ellen Francisco-\',\'Sue Ellen Francisco\',cursive',
'-Sunshiney-' => '\'-Sunshiney-\',\'Sunshiney\',cursive',
'-Syncopate-' => '\'-Syncopate-\',\'Syncopate\',sans-serif',
'-Tangerine-' => '\'-Tangerine-\',\'Tangerine\',cursive',
'-Terminal Dosis-' => '\'-Terminal Dosis-\',\'Terminal Dosis\',sans-serif',
'-The Girl Next Door-' => '\'-The Girl Next Door-\',\'The Girl Next Door\',cursive',
'-Tinos-' => '\'-Tinos-\',\'Tinos\',serif',
'-Ubuntu Condensed-' => '\'-Ubuntu Condensed-\',\'Ubuntu Condensed\',sans-serif',
'-Ubuntu-' => '\'-Ubuntu-\',\'Ubuntu\',sans-serif',
'-Unkempt-' => '\'-Unkempt-\',\'Unkempt\',cursive',
'-Varela Round-' => '\'-Varela Round-\',\'Varela Round\',sans-serif',
'-Volkhov-' => '\'-Volkhov-\',\'Volkhov\',serif',
'-Vollkorn-' => '\'-Vollkorn-\',\'Vollkorn\',serif',
'-Waiting for the Sunrise-' => '\'-Waiting for the Sunrise-\',\'Waiting for the Sunrise\',cursive',
'-Walter Turncoat-' => '\'-Walter Turncoat-\',\'Walter Turncoat\',cursive',
'-Yanone Kaffeesatz-' => '\'-Yanone Kaffeesatz-\',\'Yanone Kaffeesatz\',sans-serif',
'-Yellowtail-' => '\'-Yellowtail-\',\'Yellowtail\',cursive'))) . '</td>
                    </tr>
                    <tr>
                        <td>Color:</td>
                        <td>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][font_color]', 'maxlength'=>'7', 'size'=>'7', 'value'=>'')) . '<span class="color_picker_toggle" style="background-color: ' . h($font_color_value) . '"></span>&nbsp;<span class="text_beside_input">' . h($font_color_label) . '</span></td>
                    </tr>
                    ' . $output_font_size_row . '
                    <tr>
                        <td>Font Style:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][font_style]', 'options'=>array('' => '', 'Italic' => 'italic', 'Normal' => 'normal', 'Oblique' => 'oblique'))) . '</td>
                    </tr>
                    <tr>
                        <td>Font Weight:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][font_weight]', 'options'=>array('' => '', 'Bold' => 'bold', 'Bolder' => 'bolder', 'Lighter' => 'lighter', 'Normal' => 'normal'))) . '</td>
                    </tr>';
                break;
            
            case 'height':
                // if the height is not blank, then get it's value
                if ($properties['height'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['height']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][height][amount]', $amount);
                    
                    // if this is not an ad region, the get set the unit of measure value
                    if ($region_type != 'ad') {
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][height][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['height']));
                    }
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][height][amount]', '');
                    
                    // if this is not an ad region, the get set the unit of measure value
                    if ($region_type != 'ad') {
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][height][unit]', '');
                    }
                }
                
                $unit_of_measure = '';
                
                // if this is an ad region then output 'px' next to the input field
                if ($region_type == 'ad') {
                    $unit_of_measure = '<span class="text_beside_input">px</span>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][height][unit]', 'value'=>'px'));
                    
                // else output the UOM picklist
                } else {
                    $unit_of_measure = $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][height][unit]', 'options'=>array('%' => '%', 'px' => 'px', 'em' => 'em')));
                }
                
                $output .= 
                    '<tr>
                        <td>Height:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][height][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $unit_of_measure . '</td>
                    </tr>';
                break;
            
            case 'menu_orientation':
                // get the field's value
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][menu_orientation]', $properties['menu_orientation']);
                
                $output .= 
                    '<tr>
                        <td>Menu Orientation:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][menu_orientation]', 'options'=>array('Horizontal' => 'horizontal', 'Vertical' => 'vertical'))) . '</td>
                    </tr>';
                break;
            
            case 'line_height':
                // if the line height is not blank, then get it's value
                if ($properties['line_height'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['line_height']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][line_height][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][line_height][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['line_height']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][line_height][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][line_height][unit]', '');
                }
                
                $output .= 
                    '<tr>
                        <td>Line Height:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][line_height][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][line_height][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>';
                break;
            
            case 'margin':
                // if the margin top is not blank, then get it's value
                if ($properties['margin_top'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['margin_top']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_top][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_top][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['margin_top']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_top][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_top][unit]', '');
                }
                
                // if the margin right is not blank, then get it's value
                if ($properties['margin_right'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['margin_right']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_right][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_right][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['margin_right']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_right][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_right][unit]', '');
                }
                
                // if the margin bottom is not blank, then get it's value
                if ($properties['margin_bottom'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['margin_bottom']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_bottom][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_bottom][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['margin_bottom']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_bottom][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_bottom][unit]', '');
                }
                
                // if the margin left is not blank, then get it's value
                if ($properties['margin_left'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['margin_left']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_left][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_left][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['margin_left']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_left][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][margin_left][unit]', '');
                }
                
                $output .= 
                    '<tr>
                        <td>Margin:</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Top:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][margin_top][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][margin_top][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Right:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][margin_right][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][margin_right][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Bottom:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][margin_bottom][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][margin_bottom][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Left:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][margin_left][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][margin_left][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>';
                break;
                
            case 'padding':
                // if the padding top is not blank, then get it's value
                if ($properties['padding_top'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['padding_top']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_top][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_top][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['padding_top']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_top][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_top][unit]', '');
                }
                
                // if the padding right is not blank, then get it's value
                if ($properties['padding_right'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['padding_right']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_right][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_right][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['padding_right']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_right][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_right][unit]', '');
                }
                
                // if the padding bottom is not blank, then get it's value
                if ($properties['padding_bottom'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['padding_bottom']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_bottom][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_bottom][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['padding_bottom']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_bottom][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_bottom][unit]', '');
                }
                
                // if the padding left is not blank, then get it's value
                if ($properties['padding_left'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['padding_left']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_left][amount]', $amount);
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_left][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['padding_left']));
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_left][amount]', '');
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][padding_left][unit]', '');
                }
                
                $output .= 
                    '<tr>
                        <td>Padding:</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Top:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][padding_top][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][padding_top][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Right:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][padding_right][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][padding_right][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Bottom:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][padding_bottom][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][padding_bottom][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2em">Left:</td>
                        <td style="white-space: nowrap">' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][padding_left][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][padding_left][unit]', 'options'=>array('px' => 'px', 'em' => 'em'))) . '</td>
                    </tr>';
                break;
            
            case 'position':
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][position]', $properties['position']);
                
                $output .= 
                    '<tr>
                        <td>Position:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][position]', 'options'=>array('' => '', 'Left' => 'left', 'Center' => 'center', 'Right' => 'right'))) . '</td>
                    </tr>';
                break;
            
            case 'rounded_corners':
                // get the field values
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][rounded_corners_toggle]', $properties['rounded_corners_toggle']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][rounded_corner_top_left]', $properties['rounded_corner_top_left']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][rounded_corner_top_right]', $properties['rounded_corner_top_right']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][rounded_corner_bottom_left]', $properties['rounded_corner_bottom_left']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][rounded_corner_bottom_right]', $properties['rounded_corner_bottom_right']);
                
                $rounded_corners_option_row_style = ' style="display: none"';
                
                // if the rounded corners are on, then display the rounded corners options
                if ($properties['rounded_corners_toggle'] == 1) {
                    $rounded_corners_option_row_style = '';
                }
                
                $output .= 
                    '<tr>
                        <td><label for="' . $area . '_' . $object . '_' . $module . '_rounded_corners_toggle">Rounded Corners:<label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>$area . '_' . $object . '_' . $module . '_rounded_corners_toggle', 'name'=>$area . '[' . $object . '][' . $module . '][rounded_corners_toggle]', 'value'=>'1', 'onclick'=>'show_or_hide_css_rule_rounded_corner_options(\'' . $area . '_' . $object . '_' . $module . '\', this)', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_rounded_corner_top_left_row"' . $rounded_corners_option_row_style . '>
                        <td style="padding-left: 2em">Top Left:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][rounded_corner_top_left]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr><tr id="' . $area . '_' . $object . '_' . $module . '_rounded_corner_top_right_row"' . $rounded_corners_option_row_style . '>
                        <td style="padding-left: 2em">Top Right:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][rounded_corner_top_right]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_rounded_corner_bottom_left_row"' . $rounded_corners_option_row_style . '>
                        <td style="padding-left: 2em">Bottom Left:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][rounded_corner_bottom_left]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_rounded_corner_bottom_right_row"' . $rounded_corners_option_row_style . '>
                        <td style="padding-left: 2em">Bottom Right:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][rounded_corner_bottom_right]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>';
                break;
            
            case 'shadows':
                // get the field values
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][shadows_toggle]', $properties['shadows_toggle']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][shadow_horizontal_offset]', $properties['shadow_horizontal_offset']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][shadow_vertical_offset]', $properties['shadow_vertical_offset']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][shadow_blur_radius]', $properties['shadow_blur_radius']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][shadow_color]', $properties['shadow_color']);
                
                $shadow_option_row_style = ' style="display: none"';
                
                // if the rounded corners are on, then display the rounded corners options
                if ($properties['shadows_toggle'] == 1) {
                    $shadow_option_row_style = '';
                }
                
                // if there is a color, then set it as the value for the color picker and field
                if ($properties['shadow_color'] != '') {
                    $shadow_color_label = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][shadow_color]');
                    $shadow_color_value = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][shadow_color]');
                
                } else {
                    $shadow_color_label = 'Inherit';
                    $shadow_color_value = '#FFFFFF';
                }
                
                $output .= 
                    '<tr>
                        <td><label for="' . $area . '_' . $object . '_' . $module . '_shadows_toggle">Shadows:<label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>$area . '_' . $object . '_' . $module . '_shadows_toggle', 'name'=>$area . '[' . $object . '][' . $module . '][shadows_toggle]', 'value'=>'1', 'onclick'=>'show_or_hide_css_rule_shadow_options(\'' . $area . '_' . $object . '_' . $module . '\', this)', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_shadow_horizontal_offset_row"' . $shadow_option_row_style . '>
                        <td style="padding-left: 2em">Horizontal Offset:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][shadow_horizontal_offset]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_shadow_vertical_offset_row"' . $shadow_option_row_style . '>
                        <td style="padding-left: 2em">Vertical Offset:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][shadow_vertical_offset]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_shadow_blur_radius_row"' . $shadow_option_row_style . '>
                        <td style="padding-left: 2em">Blur Radius:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>$area . '[' . $object . '][' . $module . '][shadow_blur_radius]', 'value'=>'', 'size'=>'3')) . '&nbsp;<span class="text_beside_input">px</span></td>
                    </tr>
                    <tr id="' . $area . '_' . $object . '_' . $module . '_shadow_color_row"' . $shadow_option_row_style . '>
                        <td style="padding-left: 2em">Color:</td>
                        <td>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][shadow_color]', 'maxlength'=>'7', 'size'=>'7', 'value'=>'')) . '<span class="color_picker_toggle" style="background-color: ' . h($shadow_color_value) . '"></span>&nbsp;<span class="text_beside_input">' . h($shadow_color_label) . '</span></td>
                    </tr>';
                break;
            
            case 'site_colors':
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][primary_color]', $properties['primary_color']);
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][secondary_color]', $properties['secondary_color']);
                
                // if the primary color is not blank, then set the color value
                if ($properties['primary_color'] != '') {
                    $primary_color_label = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][primary_color]');
                    $primary_color_value = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][primary_color]');
                
                } else {
                    $primary_color_label = 'Inherit';
                    $primary_color_value = '#FFFFFF';
                }
                
                // if the secondary color is not blank, then set the color value
                if ($properties['secondary_color'] != '') {
                    $secondary_color_label = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][secondary_color]');
                    $secondary_color_value = '#' . $liveform->get_field_value($area . '[' . $object . '][' . $module . '][secondary_color]');
                
                } else {
                    $secondary_color_label = 'Inherit';
                    $secondary_color_value = '#FFFFFF';
                }
                
                $output .= 
                    '<tr>
                        <td>Primary Color:</td>
                        <td>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][primary_color]', 'maxlength'=>'7', 'size'=>'7', 'value'=>'')) . '<span class="color_picker_toggle" style="background-color: ' . h($primary_color_value) . '"></span>&nbsp;<span class="text_beside_input">' . h($primary_color_label) . '</span></td>
                    </tr>
                    <tr>
                        <td>Secondary Color:</td>
                        <td>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][secondary_color]', 'maxlength'=>'7', 'size'=>'7', 'value'=>'')) . '<span class="color_picker_toggle" style="background-color: ' . h($secondary_color_value) . '"></span>&nbsp;<span class="text_beside_input">' . h($secondary_color_label) . '</span></td>
                    </tr>';
                break;
            
            case 'text_decoration':
                $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][text_decoration]', $properties['text_decoration']);
                
                $output .= 
                    '<tr>
                        <td>Text Decoration:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][text_decoration]', 'options'=>array('' => '', 'None' => 'none', 'Underline' => 'underline', 'Overline' => 'overline', 'Line Through' => 'line-through', 'Inherit' => 'inherit'))) . '</td>
                    </tr>';
                break;
            
            case 'width':
                // if the width is not blank, then get it's value
                if ($properties['width'] != '') {
                    $amount = preg_replace('/(.*?)\%|px|em/i', '$1', $properties['width']);
                    
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][width][amount]', $amount);
                    
                    // if this is not an ad region, the get set the unit of measure value
                    if ($region_type != 'ad') {
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][width][unit]', preg_replace('/' . $amount . '(.*?)/i', '$1', $properties['width']));
                    }
                
                // else default the field to blank
                } else {
                    $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][width][amount]', '');
                    
                    // if this is not an ad region, the get set the unit of measure value
                    if ($region_type != 'ad') {
                        $liveform->assign_field_value($area . '[' . $object . '][' . $module . '][width][unit]', '');
                    }
                }
                
                $unit_of_measure = '';
                
                // if this is an ad region then output 'px' next to the input field
                if ($region_type == 'ad') {
                    $unit_of_measure = '<span class="text_beside_input">px</span>' . $liveform->output_field(array('type'=>'hidden', 'name'=>$area . '[' . $object . '][' . $module . '][width][unit]', 'value'=>'px'));
                    
                // else output the UOM picklist
                } else {
                    $unit_of_measure = $liveform->output_field(array('type'=>'select', 'name'=>$area . '[' . $object . '][' . $module . '][width][unit]', 'options'=>array('%' => '%', 'px' => 'px', 'em' => 'em')));
                }
                
                $output .= 
                    '<tr>
                        <td>Width:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=> $area . '[' . $object . '][' . $module . '][width][amount]', 'value'=>'', 'size'=>'3')) . '&nbsp;' . $unit_of_measure . '</td>
                    </tr>';
                break;
        }
    }
    
    return '<table class="field">' . $output . '</table>';
}

// outputs the form's <select> file list for selecting a file (value is file id)
function select_files_for_theme_designer()
{
    $files = array();
    
    // get image list
    $query =
        "SELECT
            files.name,
            files.folder
        FROM files
        LEFT JOIN folder ON files.folder = folder.folder_id
        WHERE
            (
                (files.type = 'gif')
                || (files.type = 'jpg')
                || (files.type = 'jpeg')
                || (files.type = 'png')
                || (files.type = 'tif')
                || (files.type = 'tiff')
            )
            AND (files.design = '1')
            AND (folder.folder_archived = '0')
        ORDER BY files.name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // loop through all files and check their access control so they can be added to array
    while ($row = mysqli_fetch_assoc($result)) {
        if (check_folder_access_in_array($row['folder'], $folders_that_user_has_access_to) == true) {
            $files[] = $row;
        }
    }
    
    $output = array();
    
    $output[''] = '';
    
    // loop through all files so their options can be outputted
    foreach ($files as $file) {
        // output option
        $output[h($file['name'])] = h($file['name']);
    }
    
    return $output;
}

?>
