<?php
/**
 * This file sends the latest firmware to IoT Weather Station devices if available.
 */

require dirname(__FILE__).'/../vendor/autoload.php';
Dotenv\Dotenv::create(__DIR__.'/../')->load();      // Load .env file

header('Content-Type: application/json');

// Check for required headers
if (!$_SERVER['HTTP_X_ESP32_VERSION']) {
    http_response_code(400);
    echo json_encode((object)['error' => true, 'message' => 'Missing version information in HTTP Headers']);
    exit(1);
}
$current_version = $_SERVER['HTTP_X_ESP32_VERSION'];
if (!$_SERVER['HTTP_X_ESP32_FREE_SPACE']) {
    http_response_code(400);
    echo json_encode((object)['error' => true, 'message' => 'Missing free space calculation']);
    exit(1);
}
$free_space = (int)$_SERVER['HTTP_X_ESP32_FREE_SPACE'];


// Look for updates in filesystem
$firmware_path = dirname(__FILE__).'/firmware';
$paths = scandir($firmware_path, SCANDIR_SORT_DESCENDING);
$update_files = array_filter($paths, function($e) {
    if ($e[0] == '.') return false; // ignore hidden files
    return preg_match('/[a-zA-Z0-9-_.]+\.bin$/', $e);
});
$latest_filename = $firmware_path.'/'.$update_files[0];
$latest_version = str_replace('.bin','',basename($latest_filename));

// Check if any files are present
if (empty($update_files)) {
    http_response_code(404);
    echo json_encode((object)['error'=>true, 'message'=>'No updates available']);
    exit(1);
}


// Check if current version is latest
if (!version_cmp($current_version, $latest_version)) {
    http_response_code(304);
    echo json_encode((object)['latest_version'=>$latest_version, 'has_updates'=>false]);
    exit(0);
}

// Is there room for the newest version?
$update_size = filesize($latest_filename);
if ($update_size > $free_space) {
    http_response_code(400);
    echo json_encode((object)['error'=>true, 'message'=>'Not enough space left on device']);
    exit(1);
}

// Set headers and prepare to send update file
header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
header('Content-Type: application/octet-stream', true);
header('Content-Disposition: attachment; filename='.basename($latest_filename));
header('Content-Length: '.$update_size);
header('X-MD5: '.md5_file($latest_filename));
readfile($latest_filename);


/**
 * Determine if the version from this server is newer than the version from the device
 * @param $from_device string current version on device
 * @param $from_server string latest version available for update
 * @return bool true if the version on the server is newer and an update can occur
 */
function version_cmp($from_device, $from_server) {
    if (preg_match('/-dev/', $from_device)) // dont update any version containing -dev
        return false;
    return strcmp($from_device, $from_server) < 0;
}