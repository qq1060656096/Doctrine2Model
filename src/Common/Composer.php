<?php
namespace Zwei\BriefDB\Common;


/**
 * composer常用方法
 *
 * Class Composer
 * @package Zwei\BriefDB\Common
 */
class Composer
{
    /**
     * 获取composer vendor所在目录
     * @return string|null 目录成功返回字符串,失败返回null
     */
    public static function getComposerVendorDir()
    {
        try {
            $class          = new \ReflectionClass("\\Composer\\Autoload\\ClassLoader");
            $file_name      = $class->getFileName();
            $vendor_dir     = dirname(dirname(dirname($file_name)));
        } catch (\Exception $e){
            $vendor_dir     = null;
        }
        return $vendor_dir;
    }
}