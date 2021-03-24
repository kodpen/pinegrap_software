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

$mode = $_GET['mode'];
//user redirect automaticly after if software update success
//auto upgrage redirections, db updates and notice user from welcome page install page redirect it to welcome and show notice. 
if($mode == 'autoupgrade'){
    $liveform_welcome = new liveform('welcome');

    log_activity("Software Updated Successfull", $_SESSION['sessionusername']);
    // Add notice to liveform.
    $liveform_welcome->add_notice('Software Update Success.');
	// update database to remember whether there is a software update available or not
    db("UPDATE config SET software_update_available = 0");
    // Redirect user upgrade.
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/install/index.php?automated_upgrade=true');
    exit();
}

require(dirname(__FILE__) . '/software_update_check.php');
$software_update_available = software_update_check();
// if a software update check was just completed, then set constant to that value
if (isset($software_update_available)) {
    if ($software_update_available) {
        define('SOFTWARE_UPDATE_AVAILABLE', TRUE);
    } else {
        define('SOFTWARE_UPDATE_AVAILABLE', FALSE);
    }
}


include_once('liveform.class.php');
$liveform = new liveform('software_update');

if(!function_exists('curl_init')){
	$liveform->mark_error('Update','Software update check could not communicate with the software update server, because cURL is not installed, so it is not known if there is a software update available.');
}

$request = array();
$request['hostname'] = HOSTNAME_SETTING;
$request['url'] = URL_SCHEME . HOSTNAME_SETTING . PATH;
$request['version'] = VERSION;
$request['edition'] = EDITION;
$request['uname'] = php_uname();
$request['os'] = PHP_OS;
$request['web_server'] = $_SERVER['SERVER_SOFTWARE'];
$request['php_version'] = phpversion();
$request['mysql_version'] = db("SELECT VERSION()");
$request['installer'] = INSTALLER;
$request['private_label'] = PRIVATE_LABEL;
$data = encode_json($request);
$API = '59593DS72233483322T669223344';
$REQUEST ='version';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.kodpen.com/api2?API='.$API.'&REQUEST='.$REQUEST);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)));

// if there is a proxy address, then send cURL request through proxy
if (PROXY_ADDRESS != '') {
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
}

$response = curl_exec($ch);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    log_activity(
        'software update check could not communicate with the software update server, so it is not known if there is a software update available. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.');
    
	include_once('liveform.class.php');
	$liveform = new liveform('settings');
	$liveform->mark_error('update', 'software update check could not communicate with the software update server, so it is not known if there is a software update available. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.');
	header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/settings.php');
	exit();
}

$response = decode_json($response);

if (!isset($response['version'])) {
    log_activity('software update check received an invalid response from the software update server, so it is not known if there is a software update available');
	include_once('liveform.class.php');
	$liveform = new liveform('settings');
	$liveform->mark_error('update', 'software update check received an invalid response from the software update server, so it is not known if there is a software update available');
	header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/settings.php');
	exit();
}

// If the software update check is not disabled in the config.php file,
// then continue to determine if there is a software update.
if (
    (defined('SOFTWARE_UPDATE_CHECK') == FALSE)
    || (SOFTWARE_UPDATE_CHECK == TRUE)
) {
    // figure out if new version is greater than old version
    
    $new_version = trim($response['version']);
    $new_version_parts = explode('.', $new_version);
    
    $old_version = VERSION;
    $old_version_parts = explode('.', $old_version);
    
    // assume that new version is not greater than old version, until we find out otherwise
    $new_version_is_greater_than_old_version = FALSE;

    // if the major number of the new version is greater than the major number of the old version,
    // then the new version is greater than the old version
    if ($new_version_parts[0] > $old_version_parts[0]) {
        $new_version_is_greater_than_old_version = TRUE;
        
    // else if the major number of the new version is equal to the major number of the old version,
    // then continue to check
    } elseif ($new_version_parts[0] == $old_version_parts[0]) {
        // if the minor number of the new version is greater than the minor number of the old version,
        // then the new version is greater than the old version
        if ($new_version_parts[1] > $old_version_parts[1]) {
            $new_version_is_greater_than_old_version = TRUE;
            
        // else if the minor number of the new version is equal to the minor number of the old version,
        // then continue to check
        } elseif ($new_version_parts[1] == $old_version_parts[1]) {
            // if the maintenance number of the new version is greater than the maintenance number of the old version,
            // then the new version is greater than the old version
            if ($new_version_parts[2] > $old_version_parts[2]) {
                $new_version_is_greater_than_old_version = TRUE;
            }
        }
    }

    // assume that there is not an available software update until we find out otherwise
    $software_update_available = 0;
    
    // if the new version is greater than the old version, then there is an available software update
    if ($new_version_is_greater_than_old_version == TRUE) {
        $software_update_available = 1;
    }

}
if($software_update_available == 0){
	$liveform->remove_form();
	include_once('liveform.class.php');
	$liveform = new liveform('settings');
	$liveform->add_notice('There is no update available.');
	header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/settings.php');
	exit();
}


print
    output_header() . '
    <script>
        function update(){
            $status = "";
            var update_btn = $("#update");
            var Progress = $("progress");
            var LogBox = $(".logbox");
            update_btn.text("Updating...").addClass("disabled").removeClass("ready");
            Progress.removeAttr("value");
            LogBox.empty().append("Updater Starting...").addClass("software_notice").removeClass("software_error");
            // Use AJAX to get various card info.
            $.ajax({
                contentType: "application/json",
                url: "api.php",
                data: JSON.stringify({
                    action: "software_update",
                    token:software_token ,
                    step: "check"
                }),
                type: "POST",
                success: function(response) {
                    // Check the values in console
                    $status = response.status;
                    if($status == "success"){
                        Progress.attr("value","40");
                        LogBox.empty().append(response.message).addClass("software_notice");;
                        $.ajax({
                            contentType: "application/json",
                            url: "api.php",
                            data: JSON.stringify({
                                action: "software_update",
                                token:software_token ,
                                step: "download"
                            }),
                            type: "POST",
                            success: function(response) {
                                $status = response.status;
                                if($status == "success"){
                                    Progress.attr("value","60");
                                    LogBox.empty().append(response.message).addClass("software_notice");
                                    $.ajax({
                                        contentType: "application/json",
                                        url: "api.php",
                                        data: JSON.stringify({
                                            action: "software_update",
                                            token:software_token ,
                                            step: "replace"
                                        }),
                                        type: "POST",
                                        success: function(response) {
                                            $status = response.status;
                                            if($status == "success"){
                                                Progress.attr("value","100");
                                                LogBox.empty().append(response.message).addClass("software_notice");
                                                window.location.replace("?mode=autoupgrade");
                                            }else{
                                                Progress.attr("value","0");
                                                LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
                                                update_btn.text("Retry Update").removeClass("disabled").addClass("ready");
                                            }
                                        }
                                    });
                                }else{
                                    Progress.attr("value","0");
                                    LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
                                    update_btn.text("Retry Update").removeClass("disabled").addClass("ready");
                                }
                            }
                        });
                    }else{
                        Progress.attr("value","0");
                        LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
                        update_btn.text("Retry Update").removeClass("disabled").addClass("ready");
                    }
                }
            });
        }
        $(function(){
            $("#update").click(function(){
                if ($(this).hasClass("ready")) {
                    update();
                }
            });
        });
    </script>
    <div id="subnav"><div style="text-align:center"><i class="material-icons " style="font-size:80px">play_for_work</i></div></div>  
    <div id="content">
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>Software Updater</h1>
        <div class="subheading" style="margin-bottom: 1em">Pinegrap Software Update. Warning! You may want to backup custom or edited software files (no need if the software is not modified). You may need upgrade from pinegrap/install after update (auto redirect).</div>
			<table class="field">
				<tr>
					<td>Current software version:</td>
					<td>'. $old_version . '</td>
				</tr> 
				<tr>
					<td>Upgradeable,Updateable software version:</td>
					<td>'. $new_version . '</td>
				</tr>
				<tr>
					<td></td>
					<td></td>
				</tr>
			</table>
            <progress  style="width:100%;" max="100" value="0"></progress><br/>
            <div class="logbox"></div>
			<div class="buttons">
				<a href="#!"  id="update" class="submit-primary ready" >Update</a>&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
			</div>
    </div>
    ' . output_footer();
$liveform->remove_form();
?> 