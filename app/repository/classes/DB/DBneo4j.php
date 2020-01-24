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
   * DBneo4j constructor.
   *
   * @param  integer  $port
   * @param  string   $database  Название базы данных.
   * @param  string   $IP
   */
  public function __construct(int $port, string $IP = '172.19.0.1')
  {

    $URI = (self::PROTOCOL . '://' . $IP . ':' . $port);
    $this->client = ClientBuilder::create()
      ->addConnection(self::PROTOCOL, $URI)->build();

    // TODO: check connection

  }


  //


}