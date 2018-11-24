<?php
/**
 * @file: library/helper.php
 * @todo: fix doc.
 * @author: Name <email@email.com>
 */

namespace Library;

use Core\Auxiliary;

class Helper
{
    /**
     * Function humanReadableSize() : return size in human readable foromat (EG 1.5GB)
     * 
     * @param  [type]  $size
     * @param  [type]  $format
     * @param  integer $round
     *
     * @return string
     */
    public static function humanReadableSize($size, $format = null, $round = 3)
    {
        $mod = 1024;
        if (is_null($format)) {
            $format = '%.2f%s';
        }

        $units = explode(' ', 'B Kb Mb Gb Tb');

        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }

        if (0 === $i) {
            $format = preg_replace('/(%.[\d]+f)/', '%d', $format);
        }

        return sprintf($format, round($size, $round), $units[$i]);
    }

    // test this function
    public static function getArraySizeInBytes(array $array)
    {
        $serializedFoo = serialize($array);

        if (function_exists('mb_strlen')) {
            $size = mb_strlen($serializedFoo, '8bit');
        } else {
            $size = strlen($serializedFoo);
        }
        
        return $size;
    }

    /**
     * Function humanReadableTimeLeft() : Return timeleft from $start in human readable format.
     *
     * @param  [type] $start
     * @param  [type] $format
     * @param  [type] $lng
     *
     * @return string
     */
    public static function humanReadableTimeLeft($start, $format = null, $lng = null)
    {
        $duration = microtime(true) - $start;
        $hours    = (int) ($duration / 60 / 60);
        $minutes  = (int) ($duration / 60) - $hours * 60;
        $seconds  = $duration - $hours * 60 * 60 - $minutes * 60;

        return number_format((float) $seconds, 2, '.', '');
    }

    /**
     * Function basePath() : Return project base path
     *
     * @return String
     */
    public static function basePath()
    {
        # todo: think what is better readable or optimized in this situation or achieve it both;
        $return = explode('/', __DIR__);

        // remove `library`
        array_pop($return);

        return implode('/', $return);
    }

    /**
     * Function globalPath() : Return project global path
     *
     * @return String
     */
    public static function globalPath()
    {
        return dirname(dirname(__DIR__));
    }

    /**
     * Function exec() : Exec System command
     * 
     * @param  string               $cmd
     * @param  \Modules\Logger|null $logger
     * 
     * @return string ???
     */
    public static function exec(string $cmd, \Modules\Logger $logger = null)
    {
        if ($logger == null) {
            $logger = \Core\Auxiliary::getGlobalLogger();
        }

        if ($logger) {
            $logger->info($cmd);
        }

        $output = [];
        $return = null;

        $output = \shell_exec($cmd);

        // #todo: rethink.
        echo $output;

        return $output;

    }
} // EOF library/helper.php
