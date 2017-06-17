<?php
/**
 * @file    : /server/core/Controller.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Core;

use Symfony\Component\HttpFoundation\Request;

class Controller
{
    public function getRequest()
    {
        $request = Request::createFromGlobals();

        if ($content = $request->getContent()) {
            $request->request->replace(json_decode($content, true));
        }

        return $request->request;
    }
} // EOF Controller.php