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
//$collection = 'workers';

### Neo4j
$neo4j = new DBneo4j('Worker', 7687);
$allWorkers = $neo4j->readAll();

### frontend
require_once('./frontend.php');