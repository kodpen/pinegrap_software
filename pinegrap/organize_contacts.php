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
validate_contacts_access($user);

// get all contact groups
$query =
    "SELECT
       id,
       name
    FROM contact_groups
    ORDER BY name";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$output_add_to_contact_groups = '';
$output_remove_from_contact_groups = '';

// loop through all contact groups
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $name = $row['name'];
    
    // if user has access to contact group, then include this contact group
    if (validate_contact_group_access($user, $id)) {
        // get number of contacts in contact group
        $number_of_contacts = get_number_of_contacts($id);
        
        $output_add_to_contact_groups .= '<input type="checkbox" name="add_to_contact_groups" id="add_to_contact_group_' . $id . '" value="' . $id . '" class="checkbox" /><label for="add_to_contact_group_' . $id . '"> ' . h($name) . ' (' . number_format($number_of_contacts) . ')</label><br />';
        $output_remove_from_contact_groups .= '<input type="checkbox" name="remove_from_contact_groups" id="remove_from_contact_group_' . $id . '" value="' . $id . '" class="checkbox" /><label for="remove_from_contact_group_' . $id . '"> ' . h($name) . ' (' . number_format($number_of_contacts) . ')</label><br />';
    }
}

print
    '<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>Organize Contacts</title>
            ' . get_generator_meta_tag() . '
            <link rel="stylesheet" type="text/css" href="' . CONTROL_PANEL_STYLESHEET_URL . '" />
            <script type="text/javascript">
                function select_groups()
                {
                    opener.document.form.add_to_contact_groups.value = "";
                    
                    // loop through all add to contact group checkboxes
                    for (i = 0; i < document.form.add_to_contact_groups.length; i++) {
                        // if contact group checkbox is checked, then add contact group to hidden form field on opener
                        if (document.form.add_to_contact_groups[i].checked == true) {
                            // if there is already contact groups in the list of contact groups, then add a comma first
                            if (opener.document.form.add_to_contact_groups.value) {
                                opener.document.form.add_to_contact_groups.value += ",";
                            }
                            
                            opener.document.form.add_to_contact_groups.value += document.form.add_to_contact_groups[i].value;
                        }
                    }
                    
                    opener.document.form.remove_from_contact_groups.value = "";
                    
                    // loop through all remove from contact group checkboxes
                    for (i = 0; i < document.form.remove_from_contact_groups.length; i++) {
                        // if contact group checkbox is checked, then add contact group to hidden form field on opener
                        if (document.form.remove_from_contact_groups[i].checked == true) {
                            // if there is already contact groups in the list of contact groups, then add a comma first
                            if (opener.document.form.remove_from_contact_groups.value) {
                                opener.document.form.remove_from_contact_groups.value += ",";
                            }
                            
                            opener.document.form.remove_from_contact_groups.value += document.form.remove_from_contact_groups[i].value;
                        }
                    }
                    
                    // submit opener form and close this popup window
                    opener.document.form.submit();
                    window.close();
                }
            </script>
        </head>
        <body>
            <div id="content">
                <form name="form">
                    <fieldset style="margin-bottom: 15px">
                        <legend><strong>Add to Contact Groups</strong></legend>
                        <div style="padding: 10px">
                            <div class="scrollable" style="height: 135px">
                                ' . $output_add_to_contact_groups . '
                            </div>
                        </div>
                    </fieldset>
                    <fieldset style="margin-bottom: 15px">
                        <legend><strong>Remove from Contact Groups</strong></legend>
                        <div style="padding: 10px">
                            <div class="scrollable" style="height: 135px">
                                ' . $output_remove_from_contact_groups . '
                            </div>
                        </div>
                    </fieldset>
                    <div class="buttons">
                        <input type="button" value="Organize Contact(s)" class="submit-primary" onclick="select_groups()" />
                    </div>
                </form>
            </div>
        </body>
    </html>';
?>