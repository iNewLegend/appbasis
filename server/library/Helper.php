<?php
/**
 * @file    : library/Helper.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Library;

class Helper
{
    /**
     * Get the client IP address
     *
     * @return string
     */
    public function getIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }
} // EOF Helper.php