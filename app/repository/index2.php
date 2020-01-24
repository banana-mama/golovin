<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//phpinfo();
//exit();


define('DOCROOT', ($_SERVER['DOCUMENT_ROOT'] . '/'));

spl_autoload_register(function ($class) {
  $fullPath = (DOCROOT . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
  if (file_exists($fullPath)) require $fullPath;
});
require_once 'vendor/autoload.php';


### NEO4J TEST
use GraphAware\Neo4j\Client\ClientBuilder;

$client = ClientBuilder::create()
  ->addConnection('bolt', 'bolt://172.19.0.1:7687')
  ->build();

//$client->run('MATCH (n) DETACH DELETE n');

//$query = '
//CREATE (ilya:Person {name: \'Ilya Petukhov\'})
//CREATE (misha:Person {name: \'Mishka\'})
//CREATE (ilya)-[:FRIEND]->(misha)
//';
//$result = $client->run($query);

//$result = $client->run('CREATE (ilya:Person {name: \'Ilya Petukhov\'})');
//$client->run('CREATE (misha:Person {name: \'Mishka\'})');
//$client->run('CREATE (ilya)-[:FRIEND]->(misha)');
//echo '<pre>';
//print_r($result);
//echo '</pre>';

//$client->run('CREATE (ivan:Person {name: \'Ivan\'})');
//
//$client->run('CREATE (ilya)-[:FRIEND]->(ivan)-[:FRIEND]->(misha)');
//$client->run('CREATE (n:Person) SET n += {infos}', ['infos' => ['name' => 'Ales', 'age' => 34]]);

//$result = $client->run('MATCH (ilya:Person) RETURN ilya');
$query = '
          MATCH (ilya {name: \'Ilya Petukhov\'})-[:FRIEND]->(friends)
          RETURN ilya.name, friends.name
          ';
$result = $client->run($query);
echo '<pre>';
print_r($result->size());
echo '</pre>';

$records = $result->getRecords();
$record = $result->getRecord();

//echo '<pre>';
//print_r($records);
//echo '</pre>';

exit();

### REDIS TEST

echo 'REDIS TEST:';

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