<?php
/**
 * @file    : release.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

require 'server/vendor/autoload.php';

function _exec($input)
{
    global $logger;

    $logger->debug($input);
    
    $result = [];

    exec($input, $result);

    if(is_array($result)) {
        $logger->debug("RESULT: " . PHP_EOL . implode(PHP_EOL, $result));
    } else if(! empty($result)) {
        $logger->debug("RESULT: " . $result);
    }
    

    return $result;
}

function getNextVersion()
{
    global $logger;
    
    $versions = [0];
    $files = glob('release/AppBasis-v-*-.{tar.gz}', GLOB_BRACE);

    foreach($files as $file) {
        $parts = explode("-", $file);
        
        if(is_numeric($parts[2])) {

            $versions [] = intval($parts[2]);
        }
    }
    
    $max = max($versions);

    if(count($versions) != 1) {
        $max++;
    }

    $logger->debug("RESULT: " . $max);

    return $max;
}

function main() 
{
    global $logger;

    $logger->info("start");

    if(! file_exists("tmp")) {
        $logger->info("making tmp folder.");
        mkdir("tmp");
    }

    if(! file_exists("release")) {
        $logger->info("making release folder.");
        mkdir("release");
    }

    _exec("rsync -av --progress ./ ./tmp --exclude release --exclude tmp --exclude .phpintel --exclude .vscode --exclude .git --exclude .gitignore --exclude *.swp --exclude client/node_modules --exclude server/vendor --exclude server/composer.lock");  

    $nextVersion = getNextVersion();


    _exec("tar czvf release/AppBasis-v-$nextVersion-.tar.gz tmp/*");

    //_exec("rm tmp -rf");
}

$logger = new Core\Logger(__FILE__);

exit(main());
