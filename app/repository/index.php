<?php

//phpinfo();

### REDIS TEST

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

###

exit();