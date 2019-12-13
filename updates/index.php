<?php
/**
 * This file sends the latest firmware to IoT Weather Station devices if available.
 *
 * Optionally, provide a current_version parameter to check if a version is the latest
 */

require dirname(__FILE__).'/../vendor/autoload.php';
Dotenv\Dotenv::create(__DIR__.'/../')->load();      // Load .env file

// Check API Key
header('Content-Type: application/json');
if (empty($_SERVER['HTTP_X_API_KEY']) || getenv('API_KEY') != $_SERVER['HTTP_X_API_KEY']) {
    echo json_encode((object)['error'=>true, 'message'=>'Invalid or missing X-API-KEY header']);
    http_response_code(401);
    exit(2);
}


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


// If optional current_version supplied, check if it is the latest
if ($_GET['current_version'] && $_GET['current_version'] == $latest_version) {
    http_response_code(304);
    echo json_encode((object)['latest_version'=>$latest_version, 'has_updates'=>false]);
    exit(0);
}

// Set headers and prepare to send update file
header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
header('Content-Type: application/octet-stream', true);
header('Content-Disposition: attachment; filename='.basename($latest_filename));
header('Content-Length: '.filesize($latest_filename));
header('X-MD5: '.md5_file($latest_filename));
readfile($latest_filename);