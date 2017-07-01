<?php
/**
 * @file    : core/Controller.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

use Symfony\Component\HttpFoundation\Request;

class Controller
{
    /**
     * Return the current http request
     *
     * @return mixed
     */
    public function getRequest()
    {
        $request = Request::createFromGlobals();

        if ($content = $request->getContent()) {
            $request->request->replace(json_decode($content, true));
        }

        return $request->request;
    }
} // EOF Controller.php