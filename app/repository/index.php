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
$allWorkers = $neo4j->readAll();

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

  }
}

//$mongo->create(2, ['name' => 'John'], $collection);
$workersList = $mongo->readAll($collection, true);
//echo '<pre>';
//print_r($workersList);
//echo '</pre>';
//exit();
//$mongo->delete(null, [], $collection, true);

/* *** */

### frontend
require_once('./frontend.php');