<?php

//echo '<pre>';
//print_r($_SERVER);
//echo '</pre>';
//exit();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//phpinfo();
//exit();

### REDIS TEST

echo 'REDIS TEST:1';

try {
  $redis = new Redis();
//  $ip = '127.0.0.1';
//  $ip = '0.0.0.0';
//  $ip = '172.19.0.10';
  $ip = '172.19.0.1';
//  $ip = 'localhost';
  $redis->connect($ip, 6379);
} catch (RedisException $e) {
  exit('Ошибка подключения к redis: ' . $e);
}

echo '<pre>';
print_r($redis->time());
echo '</pre>';

$redis->set('testKey', 'testValue');
$testValue = $redis->get('testKey');

echo '<pre>';
print_r($testValue);
echo '</pre>';

### MONGODB TEST

echo('MONGODB TEST:' . PHP_EOL);

//$ip = '0.0.0.0';
//$ip = '172.19.0.15';
$ip = '172.19.0.1';

$mongoDBmanager = new \MongoDB\Driver\Manager('mongodb://root:secret@' . $ip . ':27017');
//var_dump($mongoDBmanager);
//exit();

$testCommand = new MongoDB\Driver\Command(['ping' => 1]);
try {
  $testCursor = $mongoDBmanager->executeCommand('golovin', $testCommand);
} catch (MongoDB\Driver\Exception $e) {
  exit('Ошибка подключения к mongodb: ' . $e);
}

$testResult = $testCursor->toArray()[0];
echo '<pre>';
print_r($testResult);
echo '</pre>';

$command = new MongoDB\Driver\Command(['listDatabases' => 1]);
$cursor = $mongoDBmanager->executeCommand('admin', $command);
$cursorResult = $cursor->toArray()[0];
echo '<pre>';
print_r($cursorResult);
echo '</pre>';

$bulk = new \MongoDB\Driver\BulkWrite();

# write
//$document = ['_id' => 1337, 'id' => 1488, 'name' => 'testName'];
//$ID1 = $bulk->insert($document);
//$result = $mongoDBmanager->executeBulkWrite('db.collection', $bulk);
//echo '<pre>';
//print_r($result);
//echo '</pre>';

# get
//$filter = ['id' => 2];
$filter = [];
$query = new MongoDB\Driver\Query([], []);
$result = $mongoDBmanager->executeQuery('db.collection', $query);
echo '<pre>';
print_r($result->toArray());
echo '</pre>';

# delete
//$bulk->delete(['_id' => 228]);
//$result = $mongoDBmanager->executeBulkWrite('db.collection', $bulk);

###

exit();