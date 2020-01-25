<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DOCROOT', ($_SERVER['DOCUMENT_ROOT'] . '/'));

spl_autoload_register(function ($class) {
  $fullPath = (DOCROOT . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
  if (file_exists($fullPath)) require $fullPath;
});

require_once 'vendor/autoload.php';

use classes\DB\DBredis;
use classes\DB\DBmongo;
use classes\DB\DBneo4j;


### Redis
$redis = new DBredis(6379);

$host = '172.19.0.1';
$database = 'app';
$user = 'root';
$password = 'secret';
$charset = 'utf8';

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, ### как будем обрабатывать ошибки
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false ### где "подготавливаем" запросы (PDO или натив)
];

$dsn = "mysql:host=$host;dbname=$database;charset=$charset";
$pdo = new PDO($dsn, $user, $password, $options);

function updateRedisCache($redis, $pdo) {
  foreach ($redis->getKeys() as $key) {
    $redis->delete($key);
  }
  $query = 'SELECT * FROM `handbook`;';
  $handbook = $pdo->query($query)->fetchAll(PDO::FETCH_UNIQUE);
  foreach ($handbook as $id => $data) {
    $redis->create(('desc__' . $data['key']), $data['description']);
  }
}

### Mongo
$mongo = new DBmongo(27017, 'golovin');
$collection = 'workers';

### Neo4j
$neo4j = new DBneo4j('Worker', 7687);
$relations = ['Подчиняется' => 'OBEYS', 'Коллега для' => 'WORKS_WITH'];

/**
 * Бизнес-логика
 */

if ($_POST) {
  preg_match('@^(?P<DB>\w+)__(?P<type>.*)$@', $_POST['type'], $regExpResult);
  switch ($regExpResult['DB']) {

    ### mongoDB
    case 'mongo':
      switch ($regExpResult['type']) {

        case 'update-field':
          $worker = current($mongo->read($_POST['id'], [], $collection));
          $worker[$_POST['new_key']] = $_POST['value'];
          if ($_POST['old_key'] !== $_POST['new_key']) unset($worker[$_POST['old_key']]);
          $mongo->update($worker, $_POST['id'], [], $collection);
          break;

        case 'create-field':
          $worker = current($mongo->read($_POST['id'], [], $collection));
          $worker[$_POST['new_key']] = $_POST['value'];
          $mongo->update($worker, $_POST['id'], [], $collection);
          break;

        case 'delete-field':
          $worker = current($mongo->read($_POST['id'], [], $collection));
          if ($_POST['old_key'] !== 'id') {
            unset($worker[$_POST['old_key']]);
            $mongo->update($worker, $_POST['id'], [], $collection);
          }
          break;

        case 'create':
          $mongo->create($_POST['id'], [], $collection);
          break;

        case 'delete':
          $mongo->delete($_POST['id'], [], $collection);
          break;

      }
      break;

    case 'neo4j':
      switch ($regExpResult['type']) {

        case 'create':

          $workerFromData = array_filter(explode('|', $_POST['workerFrom']));
          $workerFrom = ['id' => $workerFromData[0]];
          if (isset($workerFromData[1]) && $workerFromData[1]) $workerFrom['name'] = $workerFromData[1];

          $workerToData = array_filter(explode('|', $_POST['workerTo']));
          $workerTo = ['id' => $workerToData[0]];
          if (isset($workerToData[1]) && $workerToData[1]) $workerTo['name'] = $workerToData[1];

          $neo4j->createRelation($workerFrom, $workerTo, $_POST['relation']);
          break;

        case 'delete':
          $neo4j->deleteRelation(['id' => $_POST['from-id']], ['id' => $_POST['to-id']], $_POST['relation-type']);
          break;

      }
      break;

    case 'redis':
      switch ($regExpResult['type']) {

        case 'create':
          $redis->create($_POST['key'], $_POST['value']);
          break;

        case 'update':
          $redis->update($_POST['key'], $_POST['value']);
          break;

        case 'delete':
          $redis->delete($_POST['key']);
          break;

      }
      break;

    case 'mysql':
      switch ($regExpResult['type']) {

        case 'create':
          $query = 'INSERT INTO `handbook` (`key`, `description`) VALUES(\'' . $_POST['key'] . '\', \'' . $_POST['value'] . '\');';
          $handbook = $pdo->query($query)->fetch();
          updateRedisCache($redis, $pdo);
          break;

        case 'update':
          $query = ('UPDATE `handbook` SET `description` = \'' . $_POST['value'] . '\' WHERE `key` = \'' . $_POST['key'] . '\';');
          $handbook = $pdo->query($query)->fetch();
          updateRedisCache($redis, $pdo);
          break;

        case 'delete':
          $query = ('DELETE FROM `handbook` WHERE `key` = \'' . $_POST['key'] . '\';');
          $handbook = $pdo->query($query)->fetch();
          updateRedisCache($redis, $pdo);
          break;

      }
      break;

  }
}

$workersList = $mongo->readAll($collection, true);
$workersRelations = $neo4j->readAllRelations();


//foreach ($redis->getKeys() as $key) {
//  $redis->delete($key);
//}
$query = 'SELECT * FROM `handbook`;';
$handbook = $pdo->query($query)->fetchAll(PDO::FETCH_UNIQUE);
//updateRedisCache($redis, $pdo);
//foreach ($handbook as $id => $data) {
//  $redis->create(('desc__' . $data['key']), $data['description']);
//}
//echo '<pre>';
//print_r($handbook);
//echo '</pre>';
//exit();

$descriptions = $redis->getKeys();

/* *** */

### frontend
require_once('./frontend.php');