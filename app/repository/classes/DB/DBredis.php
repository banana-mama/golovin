<?php

namespace classes\DB;

use Redis;
use RedisException;


class DBredis extends DB
{


  /**
   * @var null|Redis $redis
   */
  private $redis = null;


  /**
   * DBredis constructor.
   *
   * @param  integer  $port
   * @param  string   $IP
   */
  public function __construct(int $port, string $IP = '172.19.0.1')
  {
    try {
      $this->redis = new Redis();
      $this->redis->connect($IP, $port);
    } catch (RedisException $e) {
      exit('Ошибка подключения к Redis: "' . $e . '".');
    }
  }


  /**
   * @param  string  $key
   * @param  string  $value
   *
   * @return boolean
   */
  public function create(string $key, string $value): bool
  {
    if ($this->isExists($key)) return false;

    $this->redis->set($key, $value);
    return true;
  }


  /**
   * @param  string  $key
   *
   * @return null|string
   */
  public function read(string $key): ?string
  {
    return ($this->isExists($key) ? $this->redis->get($key) : null);
  }


  /**
   * @param  string  $key
   * @param  string  $value
   *
   * @return boolean
   */
  public function update(string $key, string $value): bool
  {
    if ($this->isExists($key)) {
      $this->delete($key);
      return $this->create($key, $value);
    } return false;
  }


  /**
   * @param  string  $key
   *
   * @return boolean
   */
  public function delete(string $key): bool
  {
    if ($this->isExists($key)) {
      $this->redis->del($key);
      return true;
    } else return false;
  }


  /**
   * @return array
   */
  public function getKeys(): array
  {
    return $this->redis->keys('*');
  }


  /**
   * @param  string  $key
   *
   * @return bool
   */
  protected function isExists(string $key): bool
  {
    return $this->redis->exists($key);
  }


}