<?php

namespace Gang\WebComponents\Helpers;

use Gang\WebComponents\Configuration;

class Log
{
  private static $startTimeLog;

  public static function  startLogPerformace()
  {
    Log::$startTimeLog = 0;
    if(Configuration::$log_enable && Configuration::$log_level_performance) {
      Log::$startTimeLog = round(microtime(true) * 1000);
    }
  }

  public static  function endLogPerformance($logger)
  {
    if(Configuration::$log_enable && Configuration::$log_level_performance) {
      $endProcess = round(microtime(true) * 1000) - Log::$startTimeLog;
      $logger->info("Time to render the page: {$endProcess}ms");
    }
  }

  public static function showLibXMLErrors($errors, $logger, $errorCodes, $html) : void
  {
    $writeFile =  false;
    foreach ($errors as $error) {
      if (in_array($error->code,$errorCodes)) {
        if(Configuration::$log_level_info) {
          $logger->info($error->message);
        }
      }else {
        if (Configuration::$log_level_warning){
          if (Configuration::$log_allow_create_file && !$writeFile) {
            Log::createErrorFile($html);
            $writeFile = true;
          }
          $logger->warning(str_replace("\n", "", "libXMLErrorCode: ". $error->code ."; ".$error->message. "; in line: ". $error->line .";"));
        }
      }
    }
  }

  private static function createErrorFile($html) {
    $date = date("Y-m-d_H:i:s");
    $path = Configuration::$log_file_error_path;

    if (substr($path, -1) === "/"){
      $path = substr($path, 0, -1);
    }
    if ($path === "" || $path === "/") {
      $f = fopen("../Error_".$date.".html", "w");
    }else {
      $f = fopen("../{$path}/Error_".$date.".html", "w");
    }
    fwrite($f, $html);
    fclose($f);
  }
}
