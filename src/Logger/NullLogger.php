<?php

namespace Gang\WebComponents\Logger;

use Psr\Log\LoggerInterface;

class NullLogger implements LoggerInterface
{
  public function emergency($message, array $context = array())
  {
    // TODO: Implement emergency() method.
  }

  public function alert($message, array $context = array())
  {
    // TODO: Implement alert() method.
  }

  public function info($message, array $context = array())
  {
    // TODO: Implement info() method.
  }

  public function critical($message, array $context = array())
  {
    // TODO: Implement critical() method.
  }

  public function debug($message, array $context = array())
  {
    // TODO: Implement debug() method.
  }

  public function error($message, array $context = array())
  {
    // TODO: Implement error() method.
  }

  public function log($level, $message, array $context = array())
  {
    // TODO: Implement log() method.
  }

  public function notice($message, array $context = array())
  {
    // TODO: Implement notice() method.
  }

  public function warning($message, array $context = array())
  {
    // TODO: Implement warning() method.
  }
}
