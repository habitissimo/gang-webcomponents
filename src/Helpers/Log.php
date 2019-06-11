<?php

namespace Gang\WebComponents\Helpers;

use Gang\WebComponents\Configuration;

class Log
{
  private static $startTimeLog;

  public static function  startLogPerformace()
  {
    Log::$startTimeLog = 0;
    Log::$startTimeLog = round(microtime(true) * 1000);
  }

  public static  function endLogPerformance($logger)
  {
    $endProcess = round(microtime(true) * 1000) - Log::$startTimeLog;
    $logger->info("Time to render the page: {$endProcess}ms");
  }

  public static function showLibXMLErrors($errors, $logger, $errorCodes, $html) : void
  {
    $writeFile =  false;
    foreach ($errors as $error) {
      if (in_array($error->code,$errorCodes)) {
        $logger->debug($error->message);
      }else {
        if (Configuration::$allow_create_error_file && !$writeFile) {
          File::createErrorFile($html);
          $writeFile = true;
        }
        if($error->code === 76) {
          $foundVoidElement =  false;
          foreach (Dom::$voidElements as $voidElement){
            if (strpos($error->message, $voidElement) !== false) {
              $foundVoidElement = true;
              break;
            }
          }

          if (!$foundVoidElement){
            $logger->warning(str_replace("\n", "", "libXMLErrorCode: ". $error->code ."; ".$error->message. "; in line: ". $error->line .";"));
          }

        } else {
          $logger->warning(str_replace("\n", "", "libXMLErrorCode: ". $error->code ."; ".$error->message. "; in line: ". $error->line .";"));
        }
      }
    }
  }

}
