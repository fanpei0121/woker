<?php

use Workerman\Worker;
use Workerman\Lib\Timer;
require_once __DIR__ . '/Workerman/Autoloader.php';

$worker = new worker('websocket://0.0.0.0:12345');

$worker->onConnect = function ($connection){
  Timer::add(10, function() use ($connection){
      if(!isset($connection->name)) {
          $connection->close('auth timeout and close');
      }
  }, null, false);
};

$worker->onMessage = function($connection, $data){
  if(!isset($connection->name)){
      $data = json_decode($data, true);
      if(!isset($data['name']) || !isset($data['password'])){
          return $connection->close('auth fail and close');
      }
      //mysql
      $connection->name = $data['name'];
      return broadcast($connection->name. ' login');
  }
    broadcast($connection->name. " said: {$data}");
};

function broadcast($msg){
    global $worker;
    foreach($worker->connections as $connection){
        if(!isset($connection->name)){
            continue;
        }
        $connection->send($msg);
    }
}


// 运行worker
Worker::runAll();