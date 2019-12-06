<?php

// Initialize
require_once __DIR__.'/vendor/autoload.php'; // Autoload installed libraries
Dotenv\Dotenv::create(__DIR__)->load();      // Load .env file
header("Content-Type: application/json");  // Notify browser we will be using JSON format

if (getenv('TIMEZONE'))
  date_default_timezone_set(getenv('TIMEZONE'));

// Database
$dbcfg = (object)[
  'host' => getenv('DB_HOST'),
  'name' => getenv('DB_NAME'),
  'user' => getenv('DB_USER'),
  'pass' => getenv('DB_PASS'),
];
try {
  $db = new PDO("mysql:host={$dbcfg->host};dbname={$dbcfg->name}", $dbcfg->user, $dbcfg->pass);
} catch (PDOException $e) {
  error_log($e->getMessage());
  error_response("Database error", 500);
}

// Table config
$table = 'weather';
$fields = ['temperature', 'pressure', 'humidity'];



// Handle requests to POST the current weather
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  // Check API Key
  if (empty($_SERVER['HTTP_X_API_KEY']) || getenv('API_KEY') != $_SERVER['HTTP_X_API_KEY'])
    error_response('Invalid or missing X-API-KEY header', 401); 

  // Get/Check POSTed content for validity
  $fields_str = implode(', ', $fields);
  $data = [];
  foreach ($fields as $field) {
    if (!isset($_POST[$field])) error_response("Fields {$fields_str} are required.");
    $data[$field] = $_POST[$field];
  }

  $sql = "INSERT INTO weather ({$fields_str}) VALUES (:".implode(",:", $fields).")";
  $statement = $db->prepare($sql);
  $statement->execute($data);
  
}

// Get the current weather
$query = $db->query('SELECT * FROM weather ORDER BY `timestamp` DESC LIMIT 1;');
if ($query->rowCount() == 0) {
  http_response_code(204);
  exit();
}
$row = $query->fetchObject();
$row->time_zone = date_default_timezone_get();
$row->local_time = date("c",strtotime($row->timestamp." UTC"));
echo json_encode($row);
exit();


function error_response($message, $code=400) {
  http_response_code($code);
  die(json_encode((object)['error'=>true,'message'=>$message]));
}
