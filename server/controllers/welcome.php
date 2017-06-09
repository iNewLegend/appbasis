<?php
/**
 * @file    : /app/core/controllers/welcome.php
 * @author  : <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Controllers;

class Welcome extends \Core\Controller
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

    public function updates()
    {
        $commits = simplexml_load_file('https://github.com/iNewLegend/AppBasis/commits/master.atom');

        $array = json_decode(json_encode($commits),TRUE);
        $array = $array['entry'];
        $needle = [];
        $maximum = 5;
        $i = 0;

        foreach($array as $update) {
            if($i >= $maximum) break;

            $needle [] = [
                'title' => $update['title'],
                'date'  => date('d/m/y H:m', strtotime($update['updated'])),
                'href'  => $update['link']['@attributes']['href']
            ];
            $i++;
        }

        return $needle;
    }

} // EOF welcome.php