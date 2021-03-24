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

// Validate the users access
validate_area_access($user, 'administrator');

include_once('liveform.class.php');
$liveform = new liveform('private_label');

// if the form has not been submitted
if (!$_POST) {
    // get private label value from config table in order to set check box value
    $query = "SELECT private_label FROM config";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $private_label = $row['private_label'];
    
    $liveform->assign_field_value('private_label', $private_label);
    
    // assume that the private label table should not be hidden by default until we find out otherwise
    $private_label_table_style = '';
    
    // if private label is disabled, then hide private label table by default
    if ($private_label == 0) {
        $private_label_table_style = '; display: none';
    }
    
    // If LOGO_URL global variable is not set to the default value, display it to the user
    if (LOGO_URL != PATH . SOFTWARE_DIRECTORY . '/images/logo.png') {
        $liveform->assign_field_value('logo_url', LOGO_URL);
    }
    
    // If BACKEND_STYLESHEET_URL global variable is not set to the default value, display it to the user
    if (CONTROL_PANEL_STYLESHEET_URL != PATH . SOFTWARE_DIRECTORY . '/backend.' . ENVIRONMENT_SUFFIX . '.css?v=' . @filemtime(dirname(__FILE__) . '/backend.' . ENVIRONMENT_SUFFIX . '.css')) {
        $liveform->assign_field_value('control_panel_stylesheet_url', CONTROL_PANEL_STYLESHEET_URL);
    }
    
    // if there is a FOOTER_LOGO_URL, then assign field value
    if (defined('FOOTER_LOGO_URL') == true) {
        $liveform->assign_field_value('footer_logo_url', FOOTER_LOGO_URL);
    }
    
    // if there is a FOOTER_LOGO_LINK_URL, then assign field value
    if (defined('FOOTER_LOGO_LINK_URL') == true) {
        $liveform->assign_field_value('footer_logo_link_url', FOOTER_LOGO_LINK_URL);
    }
    
    // if there is a FOOTER_LINK_1_LABEL, then assign field value
    if (defined('FOOTER_LINK_1_LABEL') == true) {
        $liveform->assign_field_value('footer_link_1_label', FOOTER_LINK_1_LABEL);
    }
    
    // if there is a FOOTER_LINK_1_URL, then assign field value
    if (defined('FOOTER_LINK_1_URL') == true) {
        $liveform->assign_field_value('footer_link_1_url', FOOTER_LINK_1_URL);
    }
    
    // if there is a FOOTER_LINK_2_LABEL, then assign field value
    if (defined('FOOTER_LINK_2_LABEL') == true) {
        $liveform->assign_field_value('footer_link_2_label', FOOTER_LINK_2_LABEL);
    }
    
    // if there is a FOOTER_LINK_2_URL, then assign field value
    if (defined('FOOTER_LINK_2_URL') == true) {
        $liveform->assign_field_value('footer_link_2_url', FOOTER_LINK_2_URL);
    }
    
    // if there is a FOOTER_LINK_3_LABEL, then assign field value
    if (defined('FOOTER_LINK_3_LABEL') == true) {
        $liveform->assign_field_value('footer_link_3_label', FOOTER_LINK_3_LABEL);
    }
    
    // if there is a FOOTER_LINK_3_URL, then assign field value
    if (defined('FOOTER_LINK_3_URL') == true) {
        $liveform->assign_field_value('footer_link_3_url', FOOTER_LINK_3_URL);
    }
    
    // if there is a FOOTER_LINK_4_LABEL, then assign field value
    if (defined('FOOTER_LINK_4_LABEL') == true) {
        $liveform->assign_field_value('footer_link_4_label', FOOTER_LINK_4_LABEL);
    }
    
    // if there is a FOOTER_LINK_4_URL, then assign field value
    if (defined('FOOTER_LINK_4_URL') == true) {
        $liveform->assign_field_value('footer_link_4_url', FOOTER_LINK_4_URL);
    }
    
    // if there is a FOOTER_LINK_5_LABEL, then assign field value
    if (defined('FOOTER_LINK_5_LABEL') == true) {
        $liveform->assign_field_value('footer_link_5_label', FOOTER_LINK_5_LABEL);
    }
    
    // if there is a FOOTER_LINK_5_URL, then assign field value
    if (defined('FOOTER_LINK_5_URL') == true) {
        $liveform->assign_field_value('footer_link_5_url', FOOTER_LINK_5_URL);
    }
    
    print
        output_header() . '
        <div id="subnav">
            &nbsp;
        </div>
        <div id="content">
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Private Label Branding</h1>
            <div class="subheading" style="margin-bottom: 1em">Update the branding for the control panel.</div>
            <form name="form" action="private_label.php" method="post" style="margin-bottom: 1em">
                ' . get_token_field() . '
                <script type="text/javascript">
                    function show_or_hide_private_label() {
                        if (document.getElementById("private_label").checked == true) {
                            document.getElementById("private_label_table").style.display = "";
                        } else {
                            document.getElementById("private_label_table").style.display = "none";
                        }
                    }
                </script>
                <div style="margin-bottom: 1em"><label for="private_label">Enable Private Label: </label>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'private_label', 'id'=>'private_label', 'value'=>'1', 'class'=>'checkbox', 'onclick'=>'show_or_hide_private_label()')) . '</div>
                <table id="private_label_table" style="margin-bottom: 1em; margin-left: 1em' . $private_label_table_style . '">
                    <tr>
                        <td>Logo URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'logo_url', 'size'=>'80')) . ' (leave blank to use the default product logo)</td>
                    </tr>
                    <tr>
                        <td>Control Panel Stylesheet URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'control_panel_stylesheet_url', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Footer Logo URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_logo_url', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td>Footer Logo Link URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_logo_link_url', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Footer Link 1 Label:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_1_label', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td>Footer Link 1 URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_1_url', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Footer Link 2 Label:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_2_label', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td>Footer Link 2 URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_2_url', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Footer Link 3 Label:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_3_label', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td>Footer Link 3 URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_3_url', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Footer Link 4 Label:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_4_label', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td>Footer Link 4 URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_4_url', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Footer Link 5 Label:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_5_label', 'size'=>'80')) . '</td>
                    </tr>
                    <tr>
                        <td>Footer Link 5 URL:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'footer_link_5_url', 'size'=>'80')) . '</td>
                    </tr>
                </table>
                <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
            </form>
        </div>
        ' . output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    // update the private label value in the config table
    $query = "UPDATE config SET private_label = '" . escape($_POST['private_label']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $config_file_content = '';
    
    // Open the config file and read its contents
    $fd = @fopen(CONFIG_FILE_PATH, "r");
    // if the config file was able to be opened
    if ($fd) {
        while (!feof ($fd))
        {
            // Read the config file parts into a variable
            $config_file_content .= fgets($fd, 4096);
        }
        fclose ($fd);
    }

    // if private label is enabled, then deal with private label fields
    if ($_POST['private_label'] == 1) {
        // Initialize variables and strip single quotes
        $logo_url = str_replace("'", '', $_POST['logo_url']);
        $control_panel_stylesheet_url = str_replace("'", '', $_POST['control_panel_stylesheet_url']);
        $footer_logo_url = str_replace("'", '', $_POST['footer_logo_url']);
        $footer_logo_link_url = str_replace("'", '', $_POST['footer_logo_link_url']);
        $footer_link_1_label = str_replace("'", '', $_POST['footer_link_1_label']);
        $footer_link_1_url = str_replace("'", '', $_POST['footer_link_1_url']);
        $footer_link_2_label = str_replace("'", '', $_POST['footer_link_2_label']);
        $footer_link_2_url = str_replace("'", '', $_POST['footer_link_2_url']);
        $footer_link_3_label = str_replace("'", '', $_POST['footer_link_3_label']);
        $footer_link_3_url = str_replace("'", '', $_POST['footer_link_3_url']);
        $footer_link_4_label = str_replace("'", '', $_POST['footer_link_4_label']);
        $footer_link_4_url = str_replace("'", '', $_POST['footer_link_4_url']);
        $footer_link_5_label = str_replace("'", '', $_POST['footer_link_5_label']);
        $footer_link_5_url = str_replace("'", '', $_POST['footer_link_5_url']);
        
        // If LOGO_URL is not equal to the post value for $logo_url
        if (LOGO_URL != $logo_url) {
            // If the define statement is found inside the config file content, replace the define statement
            if (preg_match("/define\('LOGO_URL', '(.*?)'\);/si", $config_file_content)) {
                // If the post value for logo_url was not empty
                if ($logo_url != '') {
                    $config_file_content = preg_replace("/define\('LOGO_URL', '(.*?)'\);/si", "define('LOGO_URL', '" . $logo_url . "');", $config_file_content);
                    
                // Else, the post value was empty so remove logo url line from config file
                } else {
                    $config_file_content = preg_replace("/define\('LOGO_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
                }

            // Else if the post value for the logo_url was not empty, then add the define statement after all of the other define statements.
            } else if ($logo_url != '') {
                $config_file_content = str_replace('?>', "define('LOGO_URL', '" . $logo_url . "');\r\n?>", $config_file_content);
            }
        }
            

        
        // If CONTROL_PANEL_STYLESHEET_URL is not equal to the post value for $control_panel_stylesheet_url
        if (CONTROL_PANEL_STYLESHEET_URL != $control_panel_stylesheet_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($control_panel_stylesheet_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('CONTROL_PANEL_STYLESHEET_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('CONTROL_PANEL_STYLESHEET_URL', '(.*?)'\);/si", "define('CONTROL_PANEL_STYLESHEET_URL', '" . $control_panel_stylesheet_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('CONTROL_PANEL_STYLESHEET_URL', '" . $control_panel_stylesheet_url . "');\r\n?>", $config_file_content);
                }
            // Remove the CONTROL_PANEL_STYLESHEET_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('CONTROL_PANEL_STYLESHEET_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LOGO_URL is not equal to the post value for $footer_logo_url
        if (FOOTER_LOGO_URL != $footer_logo_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_logo_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LOGO_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LOGO_URL', '(.*?)'\);/si", "define('FOOTER_LOGO_URL', '" . $footer_logo_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LOGO_URL', '" . $footer_logo_url . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LOGO_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LOGO_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LOGO_LINK_URL is not equal to the post value for $footer_logo_link_url
        if (FOOTER_LOGO_LINK_URL != $footer_logo_link_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_logo_link_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LOGO_LINK_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LOGO_LINK_URL', '(.*?)'\);/si", "define('FOOTER_LOGO_LINK_URL', '" . $footer_logo_link_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LOGO_LINK_URL', '" . $footer_logo_link_url . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LOGO_LINK_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LOGO_LINK_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_1_LABEL is not equal to the post value for $footer_link_1_label
        if (FOOTER_LINK_1_LABEL != $footer_link_1_label) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_1_label != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_1_LABEL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_1_LABEL', '(.*?)'\);/si", "define('FOOTER_LINK_1_LABEL', '" . $footer_link_1_label . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_1_LABEL', '" . $footer_link_1_label . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_1_LABEL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_1_LABEL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_1_URL is not equal to the post value for $footer_link_1_url
        if (FOOTER_LINK_1_URL != $footer_link_1_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_1_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_1_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_1_URL', '(.*?)'\);/si", "define('FOOTER_LINK_1_URL', '" . $footer_link_1_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_1_URL', '" . $footer_link_1_url . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_1_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_1_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_2_LABEL is not equal to the post value for $footer_link_2_label
        if (FOOTER_LINK_2_LABEL != $footer_link_2_label) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_2_label != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_2_LABEL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_2_LABEL', '(.*?)'\);/si", "define('FOOTER_LINK_2_LABEL', '" . $footer_link_2_label . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_2_LABEL', '" . $footer_link_2_label . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_2_LABEL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_2_LABEL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_2_URL is not equal to the post value for $footer_link_2_url
        if (FOOTER_LINK_2_URL != $footer_link_2_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_2_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_2_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_2_URL', '(.*?)'\);/si", "define('FOOTER_LINK_2_URL', '" . $footer_link_2_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_2_URL', '" . $footer_link_2_url . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_2_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_2_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_3_LABEL is not equal to the post value for $footer_link_3_label
        if (FOOTER_LINK_3_LABEL != $footer_link_3_label) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_3_label != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_3_LABEL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_3_LABEL', '(.*?)'\);/si", "define('FOOTER_LINK_3_LABEL', '" . $footer_link_3_label . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_3_LABEL', '" . $footer_link_3_label . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_3_LABEL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_3_LABEL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_3_URL is not equal to the post value for $footer_link_3_url
        if (FOOTER_LINK_3_URL != $footer_link_3_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_3_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_3_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_3_URL', '(.*?)'\);/si", "define('FOOTER_LINK_3_URL', '" . $footer_link_3_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_3_URL', '" . $footer_link_3_url . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_3_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_3_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_4_LABEL is not equal to the post value for $footer_link_4_label
        if (FOOTER_LINK_4_LABEL != $footer_link_4_label) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_4_label != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_4_LABEL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_4_LABEL', '(.*?)'\);/si", "define('FOOTER_LINK_4_LABEL', '" . $footer_link_4_label . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_4_LABEL', '" . $footer_link_4_label . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_4_LABEL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_4_LABEL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_4_URL is not equal to the post value for $footer_link_4_url
        if (FOOTER_LINK_4_URL != $footer_link_4_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_4_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_4_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_4_URL', '(.*?)'\);/si", "define('FOOTER_LINK_4_URL', '" . $footer_link_4_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_4_URL', '" . $footer_link_4_url . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_4_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_4_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_5_LABEL is not equal to the post value for $footer_link_5_label
        if (FOOTER_LINK_5_LABEL != $footer_link_5_label) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_5_label != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_5_LABEL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_5_LABEL', '(.*?)'\);/si", "define('FOOTER_LINK_5_LABEL', '" . $footer_link_5_label . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_5_LABEL', '" . $footer_link_5_label . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_5_LABEL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_5_LABEL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
        // If FOOTER_LINK_5_URL is not equal to the post value for $footer_link_5_url
        if (FOOTER_LINK_5_URL != $footer_link_5_url) {
            // If the post value for control_panel_stylesheet_url is not empty
            if ($footer_link_5_url != '') {
                // If the define statement is found inside the config file content, replace the define statement
                if (preg_match("/define\('FOOTER_LINK_5_URL', '(.*?)'\);/si", $config_file_content)) {
                    $config_file_content = preg_replace("/define\('FOOTER_LINK_5_URL', '(.*?)'\);/si", "define('FOOTER_LINK_5_URL', '" . $footer_link_5_url . "');", $config_file_content);
                // Else, add the define statement after all of the other define statements.
                } else {
                    $config_file_content = str_replace('?>', "define('FOOTER_LINK_5_URL', '" . $footer_link_5_url . "');\r\n?>", $config_file_content);
                }
            // Remove the FOOTER_LINK_5_URL define statement
            } else {
                $config_file_content = preg_replace("/\\r\\ndefine\('FOOTER_LINK_5_URL', '(.*?)'\);/si", '', $config_file_content);
            }
        }
        
    // else private label is disabled, so remove lines from config.php file
    } else {
        $config_file_content = preg_replace("/define\('LOGO_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('CONTROL_PANEL_STYLESHEET_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LOGO_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LOGO_LINK_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_1_LABEL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_1_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_2_LABEL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_2_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_3_LABEL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_3_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_4_LABEL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_4_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_5_LABEL', '(.*?)'\);\r\n/si", '', $config_file_content);
        $config_file_content = preg_replace("/define\('FOOTER_LINK_5_URL', '(.*?)'\);\r\n/si", '', $config_file_content);
    }
    
    // Rewrite the config files contents.
    $handle = fopen(CONFIG_FILE_PATH, 'w');
    if ($fd) {
        fwrite($handle, $config_file_content);
        fclose($handle);
    }
    
    // Send the user back to private_label.php
    $liveform->add_notice('The private label settings have been saved.');
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/private_label.php');
}
?>