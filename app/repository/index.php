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

### MONGO
$mongo = new DBmongo(27017, 'golovin');
$collection = 'workers';
echo '<pre>';
print_r($mongo->readAll($collection));
echo '</pre>';
echo '<pre>';
print_r($mongo->getStatistic());
echo '</pre>';
echo '<pre>';
print_r($mongo->getCollectionsList());
echo '</pre>';