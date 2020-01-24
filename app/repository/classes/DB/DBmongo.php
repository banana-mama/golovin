<?php

namespace classes\DB;

use stdClass;
use MongoDB\Driver\Query;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\Exception;


class DBmongo extends DB
{


  /**
   * @var string ID_KEY
   */
  const ID_KEY = '_id';

  /**
   * @var null|Manager $manager
   */
  private $manager = null;

  /**
   * @var null|string $database
   */
  private $database = null;

  /**
   * @var null|BulkWrite $bulkWrite
   */
  private $bulkWrite = null;


  /**
   * DBmongo constructor.
   *
   * @param  integer  $port
   * @param  string   $database  Название базы данных.
   * @param  string   $IP
   *
   * @throws
   */
  public function __construct(int $port, string $database, string $IP = '172.19.0.1')
  {
    $this->manager = new Manager('mongodb://root:secret@' . $IP . ':' . $port);
    $this->database = $database;
    $this->bulkWrite = new BulkWrite();

    try {
      $this->getStatistic('admin');
    } catch (Exception $e) {
      exit('Ошибка подключения к mongodb: ' . $e);
    }
  }


  /**
   * @param  string  $ID
   * @param  array   $data
   * @param  string  $collection
   *
   * @return bool
   */
  public function create(string $ID, array $data, string $collection = 'collection'): bool
  {
    $data[self::ID_KEY] = $ID;
    $database = implode('.', [$this->database, $collection]);

    if ($this->isExists($ID, $data, $collection)) return false;

    $this->bulkWrite->insert($data);
    $result = $this->manager->executeBulkWrite($database, $this->bulkWrite);
    return true;
  }


  /**
   * @param  int|null  $ID
   * @param  array     $filter
   * @param  string    $collection
   * @param  boolean   $handleResult
   *
   * @throws
   * @return array[]
   */
  public function read(?int $ID = null, array $filter = [], string $collection = 'collection', bool $handleResult = false): array
  {
    $database = implode('.', [$this->database, $collection]);

    if ($ID) $filter[self::ID_KEY] = $ID;
    $query = new Query($this->handleFilter($filter));
    $result = $this->manager->executeQuery($database, $query);

    $result = $result->toArray();
    return $this->handleResult($result, $handleResult);
  }


  /**
   * @param  array     $data
   * @param  string    $collection
   * @param  int|null  $ID
   * @param  array     $filter
   *
   * @return bool
   */
  public function update(array $data, ?int $ID = null, array $filter = [], string $collection = 'collection'): bool
  {
    if (($ID || $filter) && $data) {
      if ($this->isExists($ID, $filter, $collection)) {
        $database = implode('.', [$this->database, $collection]);

        if ($ID) $filter[self::ID_KEY] = $ID;

        $this->bulkWrite->update($this->handleFilter($filter), $data);
        $result = $this->manager->executeBulkWrite($database, $this->bulkWrite);
        return true;

      }
    }
    return false;
  }


  /**
   * @param  null|integer  $ID
   * @param  array         $filter
   * @param  string        $collection
   * @param  boolean       $force
   *
   * @return bool
   */
  public function delete(?int $ID = null, array $filter = [], string $collection = 'collection', bool $force = false): bool
  {
    if ($ID || $filter || ($force === true)) {
      if ($this->isExists($ID, $filter, $collection) || ($force === true)) {
        $database = implode('.', [$this->database, $collection]);

        if ($ID) $filter[self::ID_KEY] = $ID;

        $this->bulkWrite->delete($this->handleFilter($filter));
        $result = $this->manager->executeBulkWrite($database, $this->bulkWrite);
        return true;

      }
    }
    return false;
  }


  /**
   * @param  string   $collection
   * @param  boolean  $handleResult
   *
   * @return null|stdClass[]
   */
  public function readAll(string $collection = 'collection', bool $handleResult = false): ?array
  {
    return $this->read(null, [], $collection, $handleResult);
  }


  /**
   * @throws
   * @return array
   */
  public function getCollectionsList(): array
  {
    $command = new Command(['listCollections' => 1]);
    $cursor = $this->manager->executeCommand($this->database, $command);
    return array_column($cursor->toArray(), 'name');
  }


  /**
   * @throws
   * @return stdClass
   */
  public function getDatabasesList(): stdClass
  {
    $command = new Command(['listDatabases' => 1]);
    $cursor = $this->manager->executeCommand('admin', $command);
    return current($cursor->toArray());
  }


  /**
   * @param  string  $database
   *
   * @throws
   * @return stdClass
   */
  public function getStatistic(?string $database = null): stdClass
  {
    $database = ($database ?? $this->database);
    $command = new Command(['dbstats' => 1]);
    $cursor = $this->manager->executeCommand($database, $command);
    return current($cursor->toArray());
  }


  /**
   * @param  null|integer  $ID
   * @param  array         $data
   * @param  string        $collection
   *
   * @return bool
   */
  public function isExists(?int $ID = null, array $data = [], string $collection = 'collection'): bool
  {

    if ($ID) {
      $document = $this->read($ID, [], $collection);
      if ($document) return true;
    }

    if ($data) {
      $document = $this->read($ID, $data, $collection);
      if ($document) return true;
    }

    return false;
  }


  /**
   * @param  array  $filter
   *
   * @return array
   */
  private function handleFilter(array $filter): array
  {
    foreach ($filter as $key => &$value) $value = (string)$value;
    return $filter;
  }


  /**
   * @param  array    $result
   * @param  boolean  $handleResult
   *
   * @return array
   */
  private function handleResult(array $result, bool $handleResult = false): array
  {
    foreach ($result as &$item) {
      $item = (array)$item;

      if ($handleResult) {
        if (isset($item[self::ID_KEY])) {
          $item = (['id' => $item[self::ID_KEY]] + $item);
          unset($item[self::ID_KEY]);
        }
      }

    }
    return $result;
  }


}