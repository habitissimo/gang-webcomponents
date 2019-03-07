<?php
declare(strict_types=1);
namespace Gang\WebComponents\Helpers;

class File
{
    /**
     * Example:
     * $nameSpace = Habitissimo\Utils\Web\Src\Component\Fragment\Fragment
     * returned = Fragment
     */
    public static function getClassFromNameSpace(string $nameSpace) : string
    {
        $t = strrpos($nameSpace, '\\');
        return substr($nameSpace, $t + 1);
    }

    /**
     * Example:
     * $fullPath = habitissimo/Navbar/Collapsable/CollapsableNavbar.php
     * $webComponentFolder = habitissimo
     * result = /Navbar/Collapsable/CollapsableNavbar.php
     */
    public static function getRelativePath(string $fullPath, string $webComponentFolder) : string
    {
        return substr($fullPath, mb_strlen($webComponentFolder));
    }

    /**
     * Base on a relative path of a file, gets the name of the folder
     * input = Navbar/Collapsable/CollapsableNavbar.php
     * result = Navbar/Collapsable
     */
    public static function getRelativeDir(string $relative_path) : string
    {
        return substr($relative_path, 0, (int)strrpos($relative_path, "/"));
    }

    /**
     * input = /Navbar/Collapsable
     * output = \Navbar\Collapsable
     */
    public static function getNameSpaceFromFolder(string $relative_dir) : string
    {
        return str_replace(DIRECTORY_SEPARATOR, '\\', $relative_dir);
    }
}
