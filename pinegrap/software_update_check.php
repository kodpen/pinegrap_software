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

function software_update_check() {

    // Update the config table to remember that the check has been completed today.
    db("UPDATE config SET last_software_update_check_timestamp = UNIX_TIMESTAMP()");

    if (!function_exists('curl_init')) {
        log_activity('daily software update check could not communicate with the software update server, because cURL is not installed, so it is not known if there is a software update available');
        return false;
    }

    $software_update_available = false;

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
            'daily software update check could not communicate with the software update server, so it is not known if there is a software update available. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.');
        return false;
    }

    $response = decode_json($response);
	
	
    if (!isset($response['version'])) {
        log_activity('daily software update check received an invalid response from the software update server, so it is not known if there is a software update available');
        return false;
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

        // update database to remember whether there is a software update available or not
        db("UPDATE config SET software_update_available = '" . $software_update_available . "'");

        // Set value to boolean so we can return it later.
        settype($software_update_available, 'boolean');

    }

    // If messages were included in the response, then deal with them.
    if (isset($response['messages']) == true) {
        $messages = $response['messages'];

        // If the messages data is valid and formed a proper array, then continue.
        if (is_array($messages) == true) {
            // Delete current messages for this site, so we can later add new ones.
            db("DELETE FROM messages");

            $sql_exceptions = "";

            // Loop through new messages in order to add them to this site.
            foreach ($messages as $message) {

                db(
                    "INSERT INTO messages (
                        id,
                        title,
                        frequency,
                        url,
                        width,
                        height)
                    VALUES (
                        '" . e($message['id']) . "',
                        '" . e($message['title']) . "',
                        '" . e($message['frequency']) . "',
                        '" . e($message['url']) . "',
                        '" . e($message['width']) . "',
                        '" . e($message['height']) . "')");

                // Prepare sql exception statement so that we don't delete
                // user records for this message further below.

                if ($sql_exceptions != '') {
                    $sql_exceptions .= " AND ";
                }

                $sql_exceptions .= "(message_id != '" . e($message['id']) . "')";
            }

            $sql_where = "";

            // If there was at least one new message, then prepare where
            // clause to prevent deletion of records for active messages.
            if ($sql_exceptions != '') {
                $sql_where = "WHERE $sql_exceptions";
            }

            // Delete records of users viewing messages for messages
            // that no longer exist, because they are not needed anymore,
            // and we don't want that db table to get huge.
            db("DELETE FROM users_messages_xref $sql_where");
        }
    }

    return $software_update_available;

}