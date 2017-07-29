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

    /**
     * Return the current http request
     *
     * @return mixed
     */
    public function getRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

        if ($content = $request->getContent()) {
            $request->request->replace(json_decode($content, true));
        }

        return $request->request;
    }
} // EOF Helper.php