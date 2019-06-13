<?php
/**
 * @file: library/helper.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Library;

class Helper
{
    /**
     * Function humanReadableSize() : return size in human readable foromat (EG 1.5GB)
     * 
     * @param int       $size
     * @param string    $format
     * @param int       $round
     *
     * @return string
     */
    public static function humanReadableSize(int $size, string $format = null, $round = 3)
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

    /**
     * Function humanReadableTimeLeft() : Return timeleft from $start in human readable format.
     *
     * @param mixed $starttime
     *
     * @return string
     */
    public static function humanReadableTimeLeft($starttime)
    {
        $duration = microtime(true) - $starttime;
        $hours    = (int) ($duration / 60 / 60);
        $minutes  = (int) ($duration / 60) - $hours * 60;
        $seconds  = $duration - $hours * 60 * 60 - $minutes * 60;

        return number_format((float) $seconds, 2, '.', '');
    }

    /**
     * Function basePath() : Return project base path
     *
     * @return string
     */
    public static function basePath()
    {
        // # TODO: think what is better readable or optimized in this situation or achieve it both;
        $return = explode('/', __DIR__);

        // remove `library`
        array_pop($return);

        return implode('/', $return);
    }

    /**
     * Function globalPath() : Return project global path
     *
     * @return string
     */
    public static function globalPath()
    {
        return dirname(dirname(__DIR__));
    }

    /**
     * Function exec() : Exec System command
     * 
     * @param string            $cmd
     * @param \Modules\Logger   $logger
     * 
     * @return string|null
     */
    public static function exec(string $cmd, \Modules\Logger $logger = null)
    {
        if ($logger == null) {
            $logger = \Core\Auxiliary::getGlobalLogger();
        }

        if ($logger) {
            $logger->info($cmd);
        }

        $output = \shell_exec($cmd);

        // # TODO: rethink.
        echo $output;

        return $output;

    }
} // EOF library/helper.php
