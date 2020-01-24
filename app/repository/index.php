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

  }
}

$workersList = $mongo->readAll($collection, true);
$workersRelations = $neo4j->readAllRelations();
$descriptions = $redis->getKeys();

/* *** */

### frontend
require_once('./frontend.php');