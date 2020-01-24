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


### Redis
$redis = new DBredis(6379);

### Mongo
$mongo = new DBmongo(27017, 'golovin');
$collection = 'workers';

### Neo4j
$neo4j = new \classes\DB\DBneo4j(7687);

### frontend
require_once('./frontend.php');