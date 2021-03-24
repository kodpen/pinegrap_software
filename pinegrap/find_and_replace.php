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

ini_set('max_execution_time', '9999');
include('init.php');
$user = validate_user();
validate_area_access($user, 'designer');

include_once('liveform.class.php');
$liveform = new liveform('find_and_replace');

// If the form has not been submitted, then output it.
if (!$_POST) {
    echo
        output_header() . '
        <div id="subnav">
            ' . get_design_subnav() . '
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Find &amp; Replace</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Enter the text that you want to mass-find and replace throughout many types of items in the system.</div>
            <ul>
                <li style="color: red; font-weight: bold">Warning: This feature can be dangerous.  You can accidentally lose a large amount of content.  Make sure to have someone create a backup of your database first.  Use at your own risk.</li>
                <li>Updates Page Styles, Page Regions, Common Regions, Designer Regions, Dynamic Regions, Products, Product Groups, Submitted Forms, Comments, and Ads.</li>
            </ul>            
            <form method="post">
                ' . get_token_field() . '
                <table class="field" style="width: 100%">
                    <tr>
                        <td style="vertical-align: top">Find:</td>
                        <td style="width: 100%">' .
                            $liveform->output_field(array(
                                'type' => 'textarea',
                                'name' => 'find',
                                'style' => 'width: 99%; height: 100px')) . '
                            <div style="padding: .5em 0em">' .
                                $liveform->output_field(array(
                                    'type' => 'checkbox',
                                    'id' => 'case_sensitive',
                                    'name' => 'case_sensitive',
                                    'value' => '1',
                                    'class' => 'checkbox')) . '<label for="case_sensitive"> Case-sensitive</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Replace:</td>
                        <td>' .
                            $liveform->output_field(array(
                                'type' => 'textarea',
                                'name' => 'replace',
                                'style' => 'width: 99%; height: 100px')) . '
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_button" value="Find &amp; Replace" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted so process it.
} else {
    validate_token_field();
    
    // Don't trim white space from the beginning and end of find and replace content.
    $liveform->add_fields_to_session(array('trim' => false));
    
    $liveform->validate_required_field('find', 'Find is required.');
    
    if ($liveform->check_form_errors() == true) {
        go($_SERVER['PHP_SELF']);
    }

    $find = $liveform->get_field_value('find');
    $case_sensitive = $liveform->get_field_value('case_sensitive');
    $replace = $liveform->get_field_value('replace');

    $tables = array();

    $tables[] = array(
        'name' => 'style',
        'id_column' => 'style_id',
        'columns' => array('style_code'));

    $tables[] = array(
        'name' => 'pregion',
        'id_column' => 'pregion_id',
        'columns' => array('pregion_content'));

    $tables[] = array(
        'name' => 'cregion',
        'id_column' => 'cregion_id',
        'columns' => array('cregion_content'));

    $tables[] = array(
        'name' => 'dregion',
        'id_column' => 'dregion_id',
        'columns' => array('dregion_code'));

    $tables[] = array(
        'name' => 'products',
        'id_column' => 'id',
        'columns' => array(
            'short_description',
            'full_description',
            'details'));

    $tables[] = array(
        'name' => 'product_groups',
        'id_column' => 'id',
        'columns' => array(
            'short_description',
            'full_description',
            'details'));

    $tables[] = array(
        'name' => 'form_data',
        'id_column' => 'id',
        'columns' => array('data'));

    $tables[] = array(
        'name' => 'comments',
        'id_column' => 'id',
        'columns' => array('message'));

    $tables[] = array(
        'name' => 'ads',
        'id_column' => 'id',
        'columns' => array('content'));

    $number_of_items = 0;
    $number_of_replacements = 0;

    foreach ($tables as $table) {
        // Prepare SQL for columns.

        $sql_select = "";
        $sql_where = "";

        foreach ($table['columns'] as $column) {
            if ($sql_select != '') {
                $sql_select .= ", ";
                $sql_where .= " OR ";
            }

            $sql_select .= $column;

            if ($case_sensitive == 1) {
                $sql_where .= "($column LIKE BINARY '%" . e(escape_like($find)) . "%')";
            } else {
                $sql_where .= "(LOWER($column) LIKE '%" . e(escape_like(mb_strtolower($find))) . "%')";
            }
        }

        // Get all items in this table that match the find.
        $items = db_items(
            "SELECT
                " . $table['id_column'] . ",
                $sql_select
            FROM " . $table['name'] . "
            WHERE $sql_where");

        // Loop through the items in order to replace content.
        foreach ($items as $item) {
            // Assume that there was not a replacement for this item,
            // until we find out otherwise.
            $replacement = false;

            // Loop through the columns in order to replace content in each column.
            foreach ($table['columns'] as $column) {
                if ($case_sensitive == 1) {
                    $item[$column] = str_replace($find, $replace, $item[$column], $count);
                } else {
                    $item[$column] = str_ireplace($find, $replace, $item[$column], $count);
                }

                // If there was at least one replacement, then remember that.
                if ($count > 0) {
                    $replacement = true;
                    $number_of_replacements += $count;
                }
            }

            // If there was a replacement for this item, then update content for item.
            if ($replacement == true) {
                // Prepare SQL to update columns for this item.
                $sql_set = "";

                foreach ($table['columns'] as $column) {
                    if ($sql_set != '') {
                        $sql_set .= ", ";
                    }

                    $sql_set .= "$column = '" . e($item[$column]) . "'";
                }

                // Update content for item.
                db(
                    "UPDATE " . $table['name'] . "
                    SET $sql_set
                    WHERE " . $table['id_column'] . " = '" . $item[$table['id_column']] . "'");
                
                $number_of_items++;
            }
        }
    }

    // If no match was found, then output error.
    if ($number_of_replacements == 0) {
        $liveform->mark_error('find', 'Sorry, no matches were found.  Please try entering different text to find.');
        go($_SERVER['PHP_SELF']);
    }

    if ($number_of_replacements == 1) {
        $match_plural_suffix = '';
        $match_verb = 'was';

    }  else {
        $match_plural_suffix = 'es';
        $match_verb = 'were';
    }

    if ($number_of_items == 1) {
        $item_plural_suffix = '';

    }  else {
        $item_plural_suffix = 's';
    }

    $message = number_format($number_of_replacements) . ' match' . $match_plural_suffix . ' ' . $match_verb . ' found and replaced in ' . number_format($number_of_items) . ' item' . $item_plural_suffix . '.';

    log_activity($message, $_SESSION['sessionusername']);
    
    $liveform->add_notice(h($message));

    go($_SERVER['PHP_SELF']);
}
?>