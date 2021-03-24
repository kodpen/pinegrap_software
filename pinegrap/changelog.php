<?php
include('init.php');
$user = validate_user();
validate_area_access($user, 'manager');
//remove old change log file, we dont need it anymore we get changelogs from www.kodpen.com/pinegrap/changelog .
if(file_exists('_changeLog.php')){
	unlink('_changeLog.php');
}

// Add pinegrap notices
include_once('liveform.class.php');
$liveform = new liveform('changelog');


    if (!function_exists('curl_init')) {
        $liveform->mark_error('curl','software changelog check could not communicate with the software update server, because cURL is not installed,');
		header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/changelog.php');
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
	$REQUEST ='changelog';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.kodpen.com/api?API='.$API.'&REQUEST='.$REQUEST);
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
		$liveform->mark_error('curl', 'Cannot comminicate with server. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.');
		header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY .'/changelog.php');
    }

    $response = decode_json($response);

?>


<!DOCTYPE html>
<html lang="'. .'">
	<head>
	    <meta charset="utf-8">
	    <title>Pinegrap Change Log</title>
		<?php echo get_generator_meta_tag(); ?>
	    <?php echo output_control_panel_header_includes(); ?>
	</head>
	<body class="changeLog" style="">
		<div id="content">
			<?php 
				$liveform->output_errors();
				$liveform->output_notices();
			?>
				<? if(language_ruler() === 'en'): ?>
					<? foreach ($response[en] as $key => $value): ?>
					    <? echo '<h2>' . $key . '</h2><p>' . $value . '</p>'; ?>
					<? endforeach; ?>
				<? elseif(language_ruler() === 'tr'): ?>
					<? foreach ($response[tr] as $key => $value): ?>
					   <? echo '<h2>' . $key . '</h2><p>' . $value . '</p>'; ?>
					<? endforeach; ?>
				<? endif ?>
			
		</div>
	</body>
</html>