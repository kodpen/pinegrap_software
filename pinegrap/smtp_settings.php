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
validate_area_access($user, 'manager');

include_once('liveform.class.php');

$liveform = new liveform('smtp_settings');


$mailchimp_settings = '';

if (ECOMMERCE) {
    $mailchimp_settings = '<td><ul><li><a href="mailchimp_settings.php">MailChimp Settings</a></li></ul></td>';
}
$terminal_output ='';
if(is_file(dirname(__FILE__) . '/../terminal.php')){
	if (USER_ROLE < 1) {
	   $terminal_output ='
		<td>    
			<ul>
				<li><a href="../terminal.php"> Terminal</a></li>
			</ul>
		</td>
	';
	}

	
}
$subnav='   
    <div id="subnav">   
        <table>
            <tbody>
                <tr>   
                   
                    <td>    
                        <ul>
                            <li><a href="settings.php">Site Settings</a></li>
                        </ul>
                    </td> 
                    ' . $mailchimp_settings . '
                    <td>    
                        <ul>
                            <li><a href="smtp_settings.php">SMTP Settings</a></li>
                        </ul>
                    </td>
					<td>    
						<ul>
							<li><a href="backups.php">Backup Manager</a></li>
						</ul>
					</td>
					<td>
						<ul>
						<li><a href="view_log.php" >Site Log</a></li>
						</ul>
					</td>
					'. $terminal_output .'
                </tr>
            </tbody>
        </table>
    </div>';

// if the form has not been submitted
if (!$_POST) {


    // if there is a SYSTEM_SMTP_HOSTNAME, then assign field value
    if (defined('SYSTEM_SMTP_HOSTNAME') == true) {
        $liveform->assign_field_value('system_smtp_hostname', SYSTEM_SMTP_HOSTNAME);
    }
    // if there is a SYSTEM_SMTP_USERNAME, then assign field value
    if (defined('SYSTEM_SMTP_USERNAME') == true) {
        $liveform->assign_field_value('system_smtp_username', SYSTEM_SMTP_USERNAME);
    }
    // if there is a SYSTEM_SMTP_PASSWORD, then assign field value
    if (defined('SYSTEM_SMTP_PASSWORD') == true) {
    $liveform->assign_field_value('system_smtp_password', SYSTEM_SMTP_PASSWORD);
    }
    // if there is a SYSTEM_SMTP_PORT, then assign field value
    if (defined('SYSTEM_SMTP_PORT') == true) {
    $liveform->assign_field_value('system_smtp_port', SYSTEM_SMTP_PORT);
    }

    // if there is a EMAIL_CAMPAIGN_JOB, then assign field value
    if (defined('EMAIL_CAMPAIGN_JOB') == true) {
        $liveform->assign_field_value('email_campaign_job', EMAIL_CAMPAIGN_JOB);
    }
    // if there is a EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS, then assign field value
    if (defined('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS') == true) {
        $liveform->assign_field_value('email_campaign_job_number_of_emails', EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS);
    }
    // if there is a CAMPAIGN_SMTP_HOSTNAME, then assign field value
    if (defined('CAMPAIGN_SMTP_HOSTNAME') == true) {
        $liveform->assign_field_value('campaign_smtp_hostname', CAMPAIGN_SMTP_HOSTNAME);
    }
    // if there is a CAMPAIGN_SMTP_USERNAME, then assign field value
    if (defined('CAMPAIGN_SMTP_USERNAME') == true) {
        $liveform->assign_field_value('campaign_smtp_username', CAMPAIGN_SMTP_USERNAME);
    }
    // if there is a CAMPAIGN_SMTP_PASSWORD, then assign field value
    if (defined('CAMPAIGN_SMTP_PASSWORD') == true) {
    $liveform->assign_field_value('campaign_smtp_password', CAMPAIGN_SMTP_PASSWORD);
    }
    // if there is a CAMPAIGN_SMTP_PORT, then assign field value
    if (defined('CAMPAIGN_SMTP_PORT') == true) {
    $liveform->assign_field_value('campaign_smtp_port', CAMPAIGN_SMTP_PORT);
    }

	$system_email_test_output='';
	if (defined('SYSTEM_SMTP_HOSTNAME') == true) {
		$system_email_test_output='<input type="submit" name="send_system_email" id="send_system_email" value="Send System Test Email" class="submit-secondary">';
	}

    print
        output_header() . '
        '.$subnav.'
        <div id="content">
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Smtp Settings</h1>
            <div class="subheading" style="margin-bottom: 1em">Update the Smtp settings for the Software Mail Function.</div>
            <form name="form" action="smtp_settings.php" method="post" style="margin-bottom: 1em">
                ' . get_token_field() . '
               
                <table>
                    <tr>
                        <th colspan="2"><h2>System Smtp Settings</h2></th>
                    </tr>
                    <tr>
                        <td>SMTP Hostname:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'system_smtp_hostname', 'id'=>'system_smtp_hostname', 'size'=>'30')) . '</td>
                    </tr>
                    <tr>
                        <td>SMTP Port:</td>
                        <td>' . $liveform->output_field(array('type'=>'number', 'name'=>'system_smtp_port', 'id'=>'system_smtp_port', 'size'=>'3', 'placeholder'=>'587')) . '(587 is default)</td>
                    </tr>
                    <tr>
                        <td>SMTP Username:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'system_smtp_username', 'id'=>'system_smtp_username', 'size'=>'30', 'autocomplete' => 'new-password')) . '</td>
                    </tr>
                    <tr>
                        <td>SMTP Password:</td>
                        <td>' . $liveform->output_field(array('type'=>'password', 'name'=>'system_smtp_password', 'id'=>'system_smtp_password', 'size'=>'30', 'autocomplete' => 'new-password' )) . '</td>
                    </tr>
					
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                </table>
                <table>

                    <tr>
                        <th colspan="2"><h2>Campaign Smtp Settings</h2></th>
                    </tr>


                    <tr>
                        <td>Enable Email Campaign Job:</td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'email_campaign_job', 'id'=>'email_campaign_job', 'class'=>'checkbox', 'value'=>'1')) . '</td>
                    </tr>
                    <tr>
                        <td>Maximum number of Emails each time. </td>
                        <td>' . $liveform->output_field(array('type'=>'number', 'name'=>'email_campaign_job_number_of_emails', 'id'=>'email_campaign_job_number_of_emails','size'=>'30', 'placeholder'=>'25')) . '(25 is default)</td>
                    </tr>
                    <tr>
                        <td>Campaign SMTP Hostname:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'campaign_smtp_hostname', 'id'=>'campaign_smtp_hostname', 'size'=>'30')) . '</td>
                    </tr>
                    <tr>
                        <td>Campaign SMTP Port:</td>
                        <td>' . $liveform->output_field(array('type'=>'number', 'name'=>'campaign_smtp_port', 'id'=>'campaign_smtp_port', 'size'=>'3', 'placeholder'=>'587')) . '(587 is default)</td>
                    </tr>
                    <tr>
                        <td>Campaign SMTP Username:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'campaign_smtp_username', 'id'=>'campaign_smtp_username', 'size'=>'30', 'autocomplete' => 'new-password')) . '</td>
                    </tr>
                    <tr>
                        <td>Campaign SMTP Password:</td>
                        <td>' . $liveform->output_field(array('type'=>'password', 'name'=>'campaign_smtp_password', 'id'=>'campaign_smtp_password', 'size'=>'30', 'autocomplete' => 'new-password')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                </table>
				
				<div class="buttons">
					<input type="submit" name="submit_save" id="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;' . $system_email_test_output . '&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
				</div>
			</form>
        </div>
        ' . output_footer();
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    // Add liveform notices
    include_once('liveform.class.php');
    $liveform_settings = new liveform('smtp_settings');


	//if this is system email test
    if ($_POST['send_system_email'] == 'Send System Test Email') {
		if(language_ruler() === 'en'){
		
			// e-mail test mail
			email(array(
			    'to' => EMAIL_ADDRESS,
			    'from_name' => ORGANIZATION_NAME,
			    'from_email_address' => EMAIL_ADDRESS,
			    'subject' => 'System SMTP Settings Test',
			    'body' =>"Your System smtp settings work correctly."));
			// Add notice to liveform.
			$liveform_settings->add_notice('We try to send an email to ' . EMAIL_ADDRESS . ', please check mailbox.');

		}elseif(language_ruler() === 'tr'){

			$subject = mb_convert_encoding('Sistem SMTP Ayarlar� Test', $utf_encode,$tr_encode);
			$body = mb_convert_encoding('Sistem SMTP ayarlar�n�z d�zg�n �al���yor.', $utf_encode,$tr_encode);
			$notice = mb_convert_encoding(' adresine eposta g�ndermeye denedik, l�tfen gelen kutunuzu kontrol ediniz.', $utf_encode,$tr_encode);

			// e-mail test mail
			email(array(
			    'to' => EMAIL_ADDRESS,
			    'from_name' => ORGANIZATION_NAME,
			    'from_email_address' => EMAIL_ADDRESS,
			    'subject' => $subject,
			    'body' =>$body));
			// Add notice to liveform.
			$liveform_settings->add_notice(EMAIL_ADDRESS . $notice);

		}
		//log
		log_activity("System smtp test email send to: ".EMAIL_ADDRESS, $_SESSION['sessionusername']);
	}



	//if this is save
    if ($_POST['submit_save'] == 'Save') {
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

		// Initialize variables and strip single quotes
		$system_smtp_hostname = str_replace("'", '', $_POST['system_smtp_hostname']);
		$system_smtp_username = str_replace("'", '', $_POST['system_smtp_username']);
		$system_smtp_password = str_replace("'", '', $_POST['system_smtp_password']);
		$system_smtp_port = str_replace("'", '', $_POST['system_smtp_port']);

		$email_campaign_job = str_replace("'", '', $_POST['email_campaign_job']);

		$email_campaign_job_number_of_emails = str_replace("'", '', $_POST['email_campaign_job_number_of_emails']);
		$campaign_smtp_hostname = str_replace("'", '', $_POST['campaign_smtp_hostname']);
		$campaign_smtp_username = str_replace("'", '', $_POST['campaign_smtp_username']);
		$campaign_smtp_password = str_replace("'", '', $_POST['campaign_smtp_password']);
		$campaign_smtp_port = str_replace("'", '', $_POST['campaign_smtp_port']);

		// If SYSTEM_SMTP_HOSTNAME is not equal to the post value for $system_smtp_hostname
		if (SYSTEM_SMTP_HOSTNAME != $system_smtp_hostname) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('SYSTEM_SMTP_HOSTNAME', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $system_smtp_hostname was not empty
		        if ($system_smtp_hostname != '') {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_HOSTNAME', '(.*?)'\);/si", "define('SYSTEM_SMTP_HOSTNAME', '" . $system_smtp_hostname . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_HOSTNAME', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $system_smtp_hostname was not empty, then add the define statement after all of the other define statements.
		    } else if ($system_smtp_hostname != '') {
		        $config_file_content = str_replace('?>', "define('SYSTEM_SMTP_HOSTNAME', '" . $system_smtp_hostname . "');\r\n?>", $config_file_content);
		    }
		}
		// If SYSTEM_SMTP_USERNAME is not equal to the post value for $system_smtp_username
		if (SYSTEM_SMTP_USERNAME != $system_smtp_username) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('SYSTEM_SMTP_USERNAME', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $system_smtp_username was not empty
		        if ($system_smtp_username != '') {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_USERNAME', '(.*?)'\);/si", "define('SYSTEM_SMTP_USERNAME', '" . $system_smtp_username . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_USERNAME', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $system_smtp_username was not empty, then add the define statement after all of the other define statements.
		    } else if ($system_smtp_username != '') {
		        $config_file_content = str_replace('?>', "define('SYSTEM_SMTP_USERNAME', '" . $system_smtp_username . "');\r\n?>", $config_file_content);
		    }
		}    
		// If SYSTEM_SMTP_PASSWORD is not equal to the post value for $system_smtp_password
		if (SYSTEM_SMTP_PASSWORD != $system_smtp_password) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('SYSTEM_SMTP_PASSWORD', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $system_smtp_password was not empty
		        if ($system_smtp_password != '') {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_PASSWORD', '(.*?)'\);/si", "define('SYSTEM_SMTP_PASSWORD', '" . $system_smtp_password . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_PASSWORD', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $system_smtp_password was not empty, then add the define statement after all of the other define statements.
		    } else if ($system_smtp_password != '') {
		        $config_file_content = str_replace('?>', "define('SYSTEM_SMTP_PASSWORD', '" . $system_smtp_password . "');\r\n?>", $config_file_content);
		    }
		}   
		// If SYSTEM_SMTP_PORT is not equal to the post value for $system_smtp_port
		if (SYSTEM_SMTP_PORT != $system_smtp_port) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('SYSTEM_SMTP_PORT', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $system_smtp_port was not empty
		        if ($system_smtp_port != '') {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_PORT', '(.*?)'\);/si", "define('SYSTEM_SMTP_PORT', '" . $system_smtp_port . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('SYSTEM_SMTP_PORT', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $system_smtp_port was not empty, then add the define statement after all of the other define statements.
		    } else if ($system_smtp_port != '') {
		        $config_file_content = str_replace('?>', "define('SYSTEM_SMTP_PORT', '" . $system_smtp_port . "');\r\n?>", $config_file_content);
		    }
		}

		// If EMAIL_CAMPAIGN_JOB is not equal to the post value for $email_campaign_job
		if (EMAIL_CAMPAIGN_JOB != $email_campaign_job) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('EMAIL_CAMPAIGN_JOB', (.*?)\);/si", $config_file_content)) {
		        // If the post value for $email_campaign_job was 1
		        if ($email_campaign_job == '1') {
		            $config_file_content = preg_replace("/define\('EMAIL_CAMPAIGN_JOB', (.*?)\);/si", "define('EMAIL_CAMPAIGN_JOB', true);", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('EMAIL_CAMPAIGN_JOB', (.*?)\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $email_campaign_job was 1, then add the define statement after all of the other define statements.
		    } else if ($email_campaign_job == '1') {
		        $config_file_content = str_replace('?>', "define('EMAIL_CAMPAIGN_JOB', true);\r\n?>", $config_file_content);
		    }
		}
		// If EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS is not equal to the post value for $email_campaign_job_number_of_emails
		if (EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS != $email_campaign_job_number_of_emails) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $email_campaign_job_number_of_emails was not empty
		        if ($email_campaign_job_number_of_emails != '') {
		            $config_file_content = preg_replace("/define\('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS', '(.*?)'\);/si", "define('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS', '" . $email_campaign_job_number_of_emails . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $email_campaign_job_number_of_emails was not empty, then add the define statement after all of the other define statements.
		    } else if ($email_campaign_job_number_of_emails != '') {
		        $config_file_content = str_replace('?>', "define('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS', '" . $email_campaign_job_number_of_emails . "');\r\n?>", $config_file_content);
		    }
		}
		// If CAMPAIGN_SMTP_HOSTNAME is not equal to the post value for $campaign_smtp_hostname
		if (CAMPAIGN_SMTP_HOSTNAME != $campaign_smtp_hostname) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('CAMPAIGN_SMTP_HOSTNAME', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $campaign_smtp_hostname was not empty
		        if ($campaign_smtp_hostname != '') {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_HOSTNAME', '(.*?)'\);/si", "define('CAMPAIGN_SMTP_HOSTNAME', '" . $campaign_smtp_hostname . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_HOSTNAME', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $campaign_smtp_hostname was not empty, then add the define statement after all of the other define statements.
		    } else if ($campaign_smtp_hostname != '') {
		        $config_file_content = str_replace('?>', "define('CAMPAIGN_SMTP_HOSTNAME', '" . $campaign_smtp_hostname . "');\r\n?>", $config_file_content);
		    }
		}
		// If CAMPAIGN_SMTP_USERNAME is not equal to the post value for $campaign_smtp_username
		if (CAMPAIGN_SMTP_USERNAME != $campaign_smtp_username) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('CAMPAIGN_SMTP_USERNAME', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $campaign_smtp_username was not empty
		        if ($campaign_smtp_username != '') {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_USERNAME', '(.*?)'\);/si", "define('CAMPAIGN_SMTP_USERNAME', '" . $campaign_smtp_username . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_USERNAME', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $campaign_smtp_username was not empty, then add the define statement after all of the other define statements.
		    } else if ($campaign_smtp_username != '') {
		        $config_file_content = str_replace('?>', "define('CAMPAIGN_SMTP_USERNAME', '" . $campaign_smtp_username . "');\r\n?>", $config_file_content);
		    }
		}    
		// If CAMPAIGN_SMTP_PASSWORD is not equal to the post value for $campaign_smtp_password
		if (CAMPAIGN_SMTP_PASSWORD != $campaign_smtp_password) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('CAMPAIGN_SMTP_PASSWORD', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $campaign_smtp_password was not empty
		        if ($campaign_smtp_password != '') {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_PASSWORD', '(.*?)'\);/si", "define('CAMPAIGN_SMTP_PASSWORD', '" . $campaign_smtp_password . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_PASSWORD', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $campaign_smtp_password was not empty, then add the define statement after all of the other define statements.
		    } else if ($campaign_smtp_password != '') {
		        $config_file_content = str_replace('?>', "define('CAMPAIGN_SMTP_PASSWORD', '" . $campaign_smtp_password . "');\r\n?>", $config_file_content);
		    }
		}

		// If CAMPAIGN_SMTP_PORT is not equal to the post value for $campaign_smtp_port
		if (CAMPAIGN_SMTP_PORT != $campaign_smtp_port) {
		    // If the define statement is found inside the config file content, replace the define statement
		    if (preg_match("/define\('CAMPAIGN_SMTP_PORT', '(.*?)'\);/si", $config_file_content)) {
		        // If the post value for $campaign_smtp_port was not empty
		        if ($campaign_smtp_port != '') {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_PORT', '(.*?)'\);/si", "define('CAMPAIGN_SMTP_PORT', '" . $campaign_smtp_port . "');", $config_file_content);
		        // Else, the post value was empty so remove line from config file
		        } else {
		            $config_file_content = preg_replace("/define\('CAMPAIGN_SMTP_PORT', '(.*?)'\);\r\n/si", '', $config_file_content);
		        }
		    // Else if the post value for the $campaign_smtp_port was not empty, then add the define statement after all of the other define statements.
		    } else if ($campaign_smtp_port != '') {
		        $config_file_content = str_replace('?>', "define('CAMPAIGN_SMTP_PORT', '" . $campaign_smtp_port . "');\r\n?>", $config_file_content);
		    }
		}
		
		
		// Rewrite the config files contents.
		$handle = fopen(CONFIG_FILE_PATH, 'w');
		if ($fd) {
		    fwrite($handle, $config_file_content);
		    fclose($handle);
		}
		
		    log_activity("SMTP Settings was modified", $_SESSION['sessionusername']);
		    $notice = 'The smtp settings have been saved.';
		    // Add notice to liveform.
		    $liveform_settings->add_notice($notice);
		
	}




    // Redirect user back here.
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/smtp_settings.php');
}
?>