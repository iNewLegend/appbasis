<?php
/**
 * @file: controllers/welcome.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

class Welcome
{
    /**
     * Instance of Main Config
     * 
     * @var \Config\Main
     */
    private $config;

    /**
     * Function __construct() : Construct Welcome Controller
     *
     */
    public function __construct()
    {
        $this->config =  \Services\Config::get('main');

    }

    /**
     * Function index() : Default method of the controller
     *
     * @return array
     */
    public function index()
    {
        return [
            'message' => 'Welcome to AppBasis Server API',
            'version' => $this->config->version
        ];
    }

    /**
     * Function updates() : Return project updates
     * @link https://github.com/iNewLegend/AppBasis/commits/master.atom
     * @todo make async, add cache
     *
     * @return array
     */
    public function updates()
    {
        // #badcode: blocking here.

        $commits = \simplexml_load_file('https://github.com/iNewLegend/AppBasis/commits/master.atom');

        $array   = \json_decode(json_encode($commits), true);
        $array   = $array['entry'];
        $needle  = [];
        $maximum = 200;
        $i       = 0;

        foreach ($array as $update) {
            if ($i >= $maximum) {
                break;
            }

            $needle[] = [
                'title'  => str_replace('  ', '', $update['title']),
                'date'   => date('Y-m-d H:m:s', strtotime($update['updated'])),
                'href'   => $update['link']['@attributes']['href'],
                'commit' => substr(explode('/', $update['id'])[1], 0, 7),
            ];
            $i++;
        }

        return $needle;
    }
} // EOF controllers/welcome.php
