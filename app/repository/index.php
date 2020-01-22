<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//phpinfo();
//exit();

### REDIS TEST

echo 'REDIS TEST:1';

try {
  $redis = new Redis();
  $redis->connect('172.19.0.10', 6379);
} catch (RedisException $e) {
  exit('Ошибка подключения к redis: ' . $e);
}

echo '<pre>';
print_r($redis->time());
echo '</pre>';

$redis->set('testKey','testValue');
$testValue = $redis->get('testKey');

echo '<pre>';
print_r($testValue);
echo '</pre>';

### MONGODB TEST

echo ('MONGODB TEST:' . PHP_EOL);

$mongoDBmanager = new \MongoDB\Driver\Manager('mongodb://root:secret@172.19.0.15:27017');

$testCommand = new MongoDB\Driver\Command(['ping' => 1]);
try {
  $testCursor = $mongoDBmanager->executeCommand('golovin', $testCommand);
} catch(MongoDB\Driver\Exception $e) {
  exit('Ошибка подключения к mongodb: ' . $e);
}

$test = $testCursor->toArray()[0];
echo '<pre>';
print_r($test);
echo '</pre>';

$command = new MongoDB\Driver\Command(['listDatabases' => 1]);
$cursor = $mongoDBmanager->executeCommand('admin', $command);
echo '<pre>';
print_r($cursor);
echo '</pre>';

###

exit();