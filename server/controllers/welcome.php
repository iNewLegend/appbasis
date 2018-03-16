<?php
/**
 * @file    : server/controllers/welcome.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

class Welcome
{
    /**
     * Default method of the controller
     *
     * @return void
     */
    public function index()
    {
        return 'Welcome to AppBasis Server API';
    }

    /**
     * Return projects update(s)
     *
     * @return array
     */
    public function updates()
    {
        /*
        https://blog.wyrihaximus.net/2015/02/reactphp-promises/
        https://github.com/reactphp/cache
        
        TODD: Create Class Core\Cache
        - that will handle situation like this.
        - should have a queue for process
        - should be executed from another thread\flow
        eg:
            function updates(Core\Cache $cache)
            {
            return $cache.takeCare(https://github.com/iNewLegend/AppBasis/commits/master.atom, optional limit);
            }
        */
        $commits = simplexml_load_file('https://github.com/iNewLegend/AppBasis/commits/master.atom');

        $array   = json_decode(json_encode($commits), true);
        $array   = $array['entry'];
        $needle  = [];
        $maximum = 5;
        $i       = 0;

        foreach ($array as $update) {
            if ($i >= $maximum) {
                break;
            }

            $needle[] = [
                'title' => $update['title'],
                'date'  => date('d/m/y H:m', strtotime($update['updated'])),
                'href'  => $update['link']['@attributes']['href'],
            ];
            $i++;
        }

        return $needle;
    }
} // EOF welcome.php
