<?php


abstract class DB
{


  /**
   * @var null|mixed $DB
   */
  private $DB = null;


  /**
   * DB constructor.
   *
   * @param  integer  $port
   * @param  string   $IP
   */
  abstract function __construct(int $port, string $IP = '172.19.0.1');


  abstract function create();
  abstract function update();
  abstract function get();


}