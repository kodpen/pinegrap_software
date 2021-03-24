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

function optimize_image($file_id) {

    $file = db_item(
        "SELECT
            id,
            name,
            type,
            size,
            optimized
        FROM files
        WHERE id = '" . e($file_id) . "'");

    if (!$file) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, we could not find that file.');
    }

    $file['type'] = mb_strtolower($file['type']);

    if (
        ($file['type'] != 'jpg')
        and ($file['type'] != 'jpeg')
        and ($file['type'] != 'png')
        and ($file['type'] != 'gif')
        and ($file['type'] != 'bmp')
        and ($file['type'] != 'tiff')
    ) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, we don\'t support optimizing that type of file (' . $file['name'] . '). The following types are supported: jpg, jpeg, png, gif, bmp, tiff.');
    }

    if ($file['optimized']) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, that image (' . $file['name'] . ') has already been optimized.');
    }

    if ($file['size'] >= 5000000) {

        $message = 'Sorry, that image (' . $file['name'] . ') is too large (' . convert_bytes_to_string($file['size']) . ').  Image optimization service requires that images be less than 5 MB.';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    $file['path'] = FILE_DIRECTORY_PATH . '/' . $file['name'];

    $request = array();

    // If this is PHP 5.5.0 or greater then use new way to reference file path.
    if (function_exists('curl_file_create')) {
        $request['files'] = curl_file_create($file['path']);

    // Otherwise the PHP version is before 5.5.0, so use old way.
    } else {
        $request['files'] = '@' . $file['path'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/ws.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // If there was a cURL problem, then log and output error.
    if ($curl_errno) {

        $message =
            'Sorry, we could not communicate with the image optimization service. ' .
            'cURL Error Number: ' . $curl_errno . '. ' .
            'cURL Error Message: ' . $curl_error . '. (' . $file['name'] . ')';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);

    }

    $response = decode_json($response);

    // If the service did not optimize the image, then log and output error.
    if (!$response['dest']) {

        $message =
            'Sorry, we received an error from the image optimization service: ' .
            $response['error_long'] . ' (' . $file['name'] . ')';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);

    }

    // Download the new optimized image.
    $optimized_image = file_get_contents($response['dest']);

    // If we could not download the optimized image, then log and output error.
    if (($optimized_image === false) or ($optimized_image == '')) {

        $message =
            'Sorry, we could not download the optimized image from the image optimization service (' . $file['name'] . ').';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);

    }

    $result = file_put_contents($file['path'], $optimized_image);

    // If the new file could not be saved to the file system, then log and output error.
    if ($result === false) {

        $message = 'Sorry, we could not save the optimized image to the file system, so the image has not been optimized (' . $file['name'] . ').';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);

    }

    db(
        "UPDATE files 
        SET 
            size = '" . e(strlen($optimized_image)) . "',
            optimized = '1',
            timestamp = UNIX_TIMESTAMP(), 
            user = '" . USER_ID . "' 
        WHERE id = '" . $file['id'] . "'");

    $message = $file['name'] . ' has been optimized (' . convert_bytes_to_string($response['src_size']) . ' -> ' . convert_bytes_to_string($response['dest_size']) . ', ' . $response['percent'] . '%).';

    log_activity($message);

    return array(
        'status' => 'success',
        'message' => $message);

}