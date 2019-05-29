<?php
declare(strict_types=1);

namespace Gang\WebComponents\Helpers;

use Gang\WebComponents\Configuration;

class File
{
  /**
   * Example:
   * $nameSpace = Habitissimo\Utils\Web\Src\Component\Fragment\Fragment
   * returned = Fragment
   */
  public static function getClassFromNameSpace(string $nameSpace): string
  {
    $t = strrpos($nameSpace, '\\');
    if (!$t) {
      return $nameSpace;
    }
    return substr($nameSpace, $t + 1);
  }

  /**
   * Example:
   * $nameSpace = Habitissimo/Web/Components/Fragment/Fragment.php
   * returned = Fragment
   */
  public static function getClassNameFromFile(string $pathClass): string
  {
    $t = strrpos($pathClass, '/');

    if (!$t) {
      return $pathClass;
    }
    return substr($pathClass, $t + 1, -4);
  }

  /**
   * Example:
   * $fullPath = habitissimo/Navbar/Collapsable/CollapsableNavbar.php
   * $webComponentFolder = habitissimo
   * result = /Navbar/Collapsable/CollapsableNavbar.php
   */
  public static function getRelativePath(string $fullPath, string $webComponentFolder): string
  {
    return substr($fullPath, mb_strlen($webComponentFolder));
  }

  /**
   * Base on a relative path of a file, gets the name of the folder
   * input = Navbar/Collapsable/CollapsableNavbar.php
   * result = Navbar/Collapsable
   */
  public static function getRelativeDir(string $relative_path): string
  {
    return substr($relative_path, 0, (int)strrpos($relative_path, "/"));
  }

  /**
   * input = /Navbar/Collapsable
   * output = \Navbar\Collapsable
   */
  public static function getNameSpaceFromFolder(string $relative_dir): string
  {
    return str_replace(DIRECTORY_SEPARATOR, '\\', $relative_dir);
  }

  /**
   * @param $html
   * @return void
   */
  public static function createErrorFile($html): void
  {
    $date = date("Y-m-d_H:i:s");
    $path = Configuration::$error_file_path;

    if (substr($path, -1) === "/") {
      $path = substr($path, 0, -1);
    }
    if ($path === "" || $path === "/") {
      $f = fopen("../Error_" . $date . ".html", "w");
    } else {
      $f = fopen("../{$path}/Error_" . $date . ".html", "w");
    }
    fwrite($f, $html);
    fclose($f);
  }
}
