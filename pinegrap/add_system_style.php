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
$liveform = new liveform('add_system_style');

// if the form was not just submitted, then prepare to output form
if (!$_POST) {
    // if the form has not been submitted yet, then add fields to session and set default values
    if (isset($_SESSION['software']['liveforms']['add_system_style'][0]) == FALSE) {
        $liveform->add_fields_to_session();

        // If social networking is enabled, then set default value for position field.
        if (SOCIAL_NETWORKING == TRUE) {
            $liveform->assign_field_value('social_networking_position', 'bottom_left');
        }
    }
    
    // get layout name based on the selected layout
    switch ($liveform->get_field_value('layout')) {
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
            
        default:
            $output_layout_name = '2 Column, Sidebar Right';
            $liveform->assign_field_value('layout', 'two_column_sidebar_right');
            break;
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
        // prepare default areas data based on selected layout
        switch ($liveform->get_field_value('layout')) {
            case 'one_column':
                $output_areas_for_javascript =
                    'var areas = {
                        "site_top": {"rows": []},
                        "site_header": {"rows": []},
                        "area_header": {"rows": []},
                        "page_header": {"rows": []},
                        "page_content": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 1
                                        }
                                    ]
                                },
                                
                                {
                                    "cells": [
                                        {
                                            "region_type": "system",
                                            "region_name": ""
                                        }
                                    ]
                                },
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 2
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_footer": {"rows": []},
                        "area_footer": {"rows": []},
                        "site_footer": {"rows": []}
                    };';
                    
                break;
                
            case 'one_column_email':
                $output_areas_for_javascript =
                    'var areas = {
                        "site_top": {"rows": []},
                        "site_header": {"rows": []},
                        "area_header": {"rows": []},
                        "page_header": {"rows": []},
                        "page_content": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 1
                                        }
                                    ]
                                },
                                
                                {
                                    "cells": [
                                        {
                                            "region_type": "system",
                                            "region_name": ""
                                        }
                                    ]
                                },
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 2
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_footer": {"rows": []},
                        "area_footer": {"rows": []},
                        "site_footer": {"rows": []}
                    };';
                
                break;    
                            
            case 'one_column_mobile':
                $output_areas_for_javascript =
                    'var areas = {
                        "site_top": {"rows": []},
                        "site_header": {"rows": []},
                        "area_header": {"rows": []},
                        "page_header": {"rows": []},
                        "page_content": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 1
                                        }
                                    ]
                                },
                                
                                {
                                    "cells": [
                                        {
                                            "region_type": "system",
                                            "region_name": ""
                                        }
                                    ]
                                },
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 2
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_footer": {"rows": []},
                        "area_footer": {"rows": []},
                        "site_footer": {"rows": []}
                    };';
                
                break;
                
            case 'two_column_sidebar_left':
                $output_areas_for_javascript =
                    'var areas = {
                        "site_top": {"rows": []},
                        "site_header": {"rows": []},
                        "area_header": {"rows": []},
                        "page_header": {"rows": []},
                        "page_content": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 1
                                        }
                                    ]
                                },
                                
                                {
                                    "cells": [
                                        {
                                            "region_type": "system",
                                            "region_name": ""
                                        }
                                    ]
                                }
                            ]
                        },
                        "sidebar": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 2
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_footer": {"rows": []},
                        "area_footer": {"rows": []},
                        "site_footer": {"rows": []}
                    };';
                    
                break;
            
            case 'two_column_sidebar_right':
                $output_areas_for_javascript =
                    'var areas = {
                        "site_top": {"rows": []},
                        "site_header": {"rows": []},
                        "area_header": {"rows": []},
                        "page_header": {"rows": []},
                        "page_content": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 1
                                        }
                                    ]
                                },
                                
                                {
                                    "cells": [
                                        {
                                            "region_type": "system",
                                            "region_name": ""
                                        }
                                    ]
                                }
                            ]
                        },
                        "sidebar": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 2
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_footer": {"rows": []},
                        "area_footer": {"rows": []},
                        "site_footer": {"rows": []}
                    };';
                    
                break;
                
            case 'three_column_sidebar_left':
                $output_areas_for_javascript =
                    'var areas = {
                        "site_top": {"rows": []},
                        "site_header": {"rows": []},
                        "area_header": {"rows": []},
                        "page_header": {"rows": []},
                        "sidebar": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 1
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_content_left": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "page",
                                            "region_name": "",
                                            "page_region_number": 2
                                        }
                                    ]
                                },
                                
                                {
                                    "cells": [
                                        {
                                            "region_type": "system",
                                            "region_name": ""
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_content_right": {
                            "rows": [
                                {
                                    "cells": [
                                        {
                                            "region_type": "",
                                            "region_name": ""
                                        }
                                    ]
                                }
                            ]
                        },
                        "page_footer": {"rows": []},
                        "area_footer": {"rows": []},
                        "site_footer": {"rows": []}
                    };';
                    
                break;
        }
        
    // else the areas field does exist, so the form has been submitted, so prepare areas data that was submitted
    } else {
        $output_areas_for_javascript = 'var areas = ' . $liveform->get_field_value('areas') . ';';
    }

    // Create options for social networking position pick list.
    $social_networking_position_options = array(
        'Top Left' => 'top_left',
        'Top Right' => 'top_right',
        'Bottom Left' => 'bottom_left',
        'Bottom Right' => 'bottom_right',
        'Disabled' => 'disabled',
    );
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new page style]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Style Designer</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Create a new System Page Style that can be associated with one or many Pages.</div>
            <form id="style_designer_form" name="style_designer_form" action="add_system_style.php" method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'layout')) . '
                ' . $liveform->output_field(array('type'=>'hidden', 'id'=>'areas', 'name'=>'areas')) . '
                <h2 style="margin-bottom: 1em">New Page Style Name</h2>
                Name: ' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '
                <h2 style="margin-bottom: 1em">Add Cells and Regions to the Page Layout below</h2>
                <table style="margin-bottom: 1em; width: 100%">
                    <tr>
                        <td style="text-align: left; width: 33%; vertical-align: middle">Page Layout: ' . $output_layout_name . '</td>
                        <td style="text-align: center; width: 33%; vertical-align: middle">' . $output_social_networking_position . '</td>
                        <td style="text-align: right; width: 33%; vertical-align: middle">Empty Cell Width: % ' . $liveform->output_field(array('type'=>'text', 'name'=>'empty_cell_width_percentage', 'value'=>'2', 'size'=>'5', 'maxlength'=>'5')) . ' </td>
                    </tr>
                </table>
                <script type="text/javascript">
                    ' . $output_areas_for_javascript . '
                    
                    ' . get_style_designer_regions_for_javascript() . '
                    
                    $(document).ready(function () {
                        initialize_style_designer();
                    });
                </script>
                ' . get_style_designer_content($liveform->get_field_value('layout')) . '
                <h2 style="margin-bottom: 1em">Override the activated Theme</h2>
                <div style="margin-bottom: 1.5em">Theme: ' . $liveform->output_field(array('type' => 'select', 'name' => 'theme_id', 'options' => get_theme_options())) . '</div>
                <h2 style="margin-bottom: 1em">Add Additional Classes to the Body of this Page Style</h2>
                <div style="margin-bottom: 1.5em">Additional Body Classes: ' . $liveform->output_field(array('type'=>'text', 'name'=>'additional_body_classes', 'size'=>'60', 'maxlength'=>'255')) . '&nbsp;&nbsp;<span style="white-space: nowrap">(separate classes with a space, do not include period)</span></div>
                <h2 style="margin-bottom: 1em">Collection</h2>
                <div style="margin-bottom: 1.5em">' . $liveform->output_field(array('type' => 'radio', 'name' => 'collection', 'id' => 'collection_a', 'value' => 'a', 'checked' => 'checked', 'class' => 'radio')) . '<label for="collection_a">Collection A</label>&nbsp; ' . $liveform->output_field(array('type' => 'radio', 'name' => 'collection', 'id' => 'collection_b', 'value' => 'b', 'class' => 'radio')) . '<label for="collection_b">Collection B</label></div>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted, so process the form
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');
    
    // if there is not already an error for the name field, then check if name is already in use
    if ($liveform->check_field_error('name') == FALSE) {
        $query = "SELECT style_id FROM style WHERE style_name = '" . escape($liveform->get_field_value('name')) . "'";
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
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_system_style.php?layout=' . $liveform->get_field_value('layout'));
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

    $sql_field_social_networking_position = "";
    $sql_value_social_networking_position = "";

    // If social networking is enabled, then update position value.
    if (SOCIAL_NETWORKING == TRUE) {
        $sql_field_social_networking_position = "social_networking_position,";
        $sql_value_social_networking_position = "'" . escape($liveform->get_field_value('social_networking_position')) . "',";
    }
    
    // add page style to database
    $query =
        "INSERT INTO style (
            style_name,
            style_type,
            style_layout,
            style_empty_cell_width_percentage,
            style_code,
            " . $sql_field_social_networking_position . "
            theme_id,
            additional_body_classes,
            collection,
            style_user,
            style_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            'system',
            '" . escape($liveform->get_field_value('layout')) . "',
            '" . escape($liveform->get_field_value('empty_cell_width_percentage')) . "',
            '" . escape(get_system_style_code($liveform->get_field_value('layout'), $areas, $liveform->get_field_value('name'), $liveform->get_field_value('empty_cell_width_percentage'), $liveform->get_field_value('additional_body_classes'))) . "',
            " . $sql_value_social_networking_position . "
            '" . escape($liveform->get_field_value('theme_id')) . "',
            '" . escape($liveform->get_field_value('additional_body_classes')) . "',
            '" . escape($liveform->get_field_value('collection')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $style_id = mysqli_insert_id(db::$con);
    
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
                        '$style_id',
                        '" . escape($area_name) . "',
                        '" . escape($row_key + 1) . "',
                        '" . escape($cell_key + 1) . "',
                        '" . escape($cell['region_type']) . "',
                        '" . escape($cell['region_name']) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
    }
    
    log_activity('style (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);
    
    $liveform_view_styles = new liveform('view_styles');
    $liveform_view_styles->add_notice('The style was created successfully.');
    
    // send user to view styles screen
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_styles.php');
    
    $liveform->remove_form();
}
?>