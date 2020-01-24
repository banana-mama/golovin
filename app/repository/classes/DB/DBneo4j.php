<?php

namespace classes\DB;

use GraphAware\Neo4j\Client\ClientBuilder;


class DBneo4j extends DB
{


  /**
   * @var string PROTOCOL
   */
  const PROTOCOL = 'bolt';

  /**
   * @var null|ClientBuilder $client
   */
  private $client = null;

  /**
   * @var string $label
   */
  private $label = null;


  /**
   * DBneo4j constructor.
   *
   * @param  string   $label
   * @param  integer  $port
   * @param  string   $IP
   */
  public function __construct(string $label, int $port, string $IP = '172.19.0.1')
  {
    $this->label = $label;

    $URI = (self::PROTOCOL . '://' . $IP . ':' . $port);
    $this->client = ClientBuilder::create()
      ->addConnection(self::PROTOCOL, $URI)->build();

    // TODO: check connection

  }


  /**
   * @param  array  $data
   *
   * @return bool
   */
  public function create(array $data): bool
  {
    $result = $this->client
      ->run('MERGE (worker:' . $this->label . ' ' . $this->makeMATCH($data) . ')');

    return true;
  }


  /**
   * @param  array  $filter
   *
   * @return array
   */
  public function read(array $filter): array
  {
    $query = '
              MATCH (worker ' . $this->makeMATCH($filter) . ')
              RETURN worker
              ';

    $result = $this->client->run($query);
    $result = $result->getRecords();
    return $result;
  }


  /**
   * @param  array  $filter
   * @param  array  $update
   *
   * @return bool
   */
  public function update(array $filter, array $update): bool
  {
    $query = '
              MATCH (worker ' . $this->makeMATCH($filter) . ')
              SET ' . $this->makeSET('worker', $update) . '
              RETURN worker
              ';

    $result = $this->client->run($query);
    $result = $result->getRecords();
    return true;
  }


  /**
   * @param  array  $filter
   *
   * @return bool
   */
  public function delete(array $filter): bool
  {
    $query = '
              MATCH (worker ' . $this->makeMATCH($filter) . ')
              DETACH DELETE worker
              ';
    return true;
  }


  /**
   * @param  array  $filter
   *
   * @return string
   */
  private function makeMATCH(array $filter): string
  {
    $MATCH = [];
    foreach ($filter as $key => $value) {
      $MATCH[] = ($key . ':\'' . $value . '\'');
    }
    return ('{' . implode(',', $MATCH) . '}');
  }


  /**
   * @param  string  $variable
   * @param  array   $data
   *
   * @return string
   */
  private function makeSET(string $variable, array $data): string
  {
    $SET = [];
    foreach ($data as $key => $value) {
      $SET[] = ($variable . '.' . $key . ' = \'' . $value . '\'');
    }
    return implode(',', $SET);
  }


  /**
   * @return void
   */
  public function deleteAll(): void
  {
    $this->client->run('MATCH (workers) DETACH DELETE workers');
  }


}