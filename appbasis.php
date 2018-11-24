<?php

/**
 * @file: appbasis.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
require 'vendor/autoload.php';

// sig_handler, tick use required as of PHP 4.3.0
declare(ticks = 1);

class AppBasis
{
    /**
     * Instance Of Logger Module
     * 
     * @var \Modules\Logger
     */
    static $logger;

    static $command;
    static $self;

    static private $heartbeat =  true; 

    public static function signalHandler(int $signal)
    {
        self::$logger->notice("signal: `{$signal}`");

        switch ($signal) {
            case SIGINT:
            {
                self::$logger->notice('setting `self::$heartbeat = false;`');

                self::$heartbeat = false;

                self::$logger->notice('stopping global loop');
                
                \Core\Auxiliary::getLoop()->stop();

                return;
            }                
        }

        \Core\Auxiliary::shutdown($signal, "unknown signal: `{$signal}", __CLASS__, __FILE__);
    }

    public static function getNextVersion()
    {
        $versions = [0];
        $files = glob('release/AppBasis-v-*-.{tar.gz}', GLOB_BRACE);

        foreach ($files as $file) {
            $parts = explode("-", $file);

            if (is_numeric($parts[2])) {
                $versions[] = intval($parts[2]);
            }
        }

        $max = max($versions);

        if (count($versions) != 1) {
            $max++;
        }

        self::$logger->debug("RESULT: " . $max);

        return $max;
    }

    public static function backup()
    {
        if (!file_exists("backups")) {
            self::$logger->debug("making backups folder.");
            mkdir("backups");
        }

        if (!file_exists("tmp")) {
            self::$logger->debug("making tmp folder.");
            mkdir("tmp");
        }

        self::$logger->debug("collecting data.");
        \Library\Helper::exec("rsync -av --progress ./ ./tmp --exclude backups --exclude release --exclude tmp --exclude .phpintel --exclude .vscode --exclude .git --exclude .gitignore --exclude *.swp --exclude '*/node_modules' --exclude vendor --exclude composer.lock");

        $date = date("Ymd-His");
        $file = "backups/AppBasis-{$date}.tar.gz";

        \Library\Helper::exec("tar czf {$file} tmp/*");

        self::$logger->debug("your file is ready at: `{$file}`");
        self::$logger->debug('deleting temp');

        \Library\Helper::exec("rm -rf tmp/");
    }

    public static function template(\Modules\Command $command)
    {
        $method = '';
        $params = $command->getParameters();

        if (!empty($params)) {
            $method = array_shift($params);
        }

        $appCommand = new Modules\Command("Template/{$method}/");
        $appCommand->setParameters($params);

        // Create new app, pass needed service in it , will be called later by runCommand.

        $app = new \Core\Core(self::$logger, new \Modules\Ip('127.0.0.1'), self::class, [
            \Services\Template::class => [self::$logger, $command],
        ]);

        $output = $app->executeCommand($appCommand);
    }

    public static function plugin(string $plugin)
    {
        $lowercasePlugin = strtolower($plugin);

        self::$logger->notice("attempting to run plugin: `{$plugin}`");

        $plugin_startup_file = "ext/{$plugin}/{$lowercasePlugin}.php";

        self::$logger->debug("checking if file exist: `{$plugin_startup_file}`");

        if (!file_exists($plugin_startup_file)) {
            self::$logger->error("plugin startup file:` {$plugin_startup_file}` does not exist");

            \Core\Auxiliary::shutdown(0, "plugin start file doest not exist", __class__, __FUNCTION__);
            
            return;
        }

        self::$logger->debug("loading: `{$plugin_startup_file}`");

        if (!require($plugin_startup_file)) {
            self::$logger->error("unable to load:` {$plugin_startup_file}`");

            return;
        }

        $pluginClass = $plugin . '_Plugin';

        self::$logger->debug("checking if class: `{$pluginClass}` is available");

        if (! class_exists($pluginClass)) {
            self::$logger->error("unable to load plugin class: `{$pluginClass}` does not available");
            return false;
        }

        self::$logger->debug("crating object from class: `{$pluginClass}`");

        $pluginObject = new $pluginClass(self::$logger);

        if (! $pluginObject) {
            self::$logger->error("unable to create plugin object: `{$pluginClass}`");
            return false;
        }

        self::$logger->debug("checking if manual_load() function is available`");

        if (! method_exists($pluginObject, 'manual_load')) {
            self::$logger->error("unable to load the plugin: `{$plugin}::manual_load()` does not available");
            return false;
        }

        if (! $pluginObject->manual_load()) {
            self::$logger->error("unable to load the plugin: `{$plugin}`");
            return false;
        }

        self::$logger->info("plugin: `{$plugin}` loaded successfully");


        $pluginObject->unload();

        return true;
    }

    /**
     * Function main() : <description>
     * @param  string           $self       [description]
     * @param  \Modules\Logger  $logger     [description]
     * @param  \Modules\Command $command    [description]
     * @return int                          [description]
     */
    public static function main(string $self, \Modules\Logger $logger, \Modules\Command $command)
    {
        pcntl_signal(SIGINT, "AppBasis::signalHandler");

        // register hearbeat
        \Core\Auxiliary::boot(null, self::$heartbeat);

        self::$logger = $logger;

        self::$logger->notice("start with command: `{$command}`");

        $cool = 'php ' . $self . ' ';
        $commands = [
            '[ SYNTAX ]' => $cool . '<command> <method> <param1> <param2> [ etc ... ]',
            '[ Command ]' => '[ Description ]',
            '-------------------------------------',
            'welcome' => 'Show this screen',
            'reload' => 'Reload server vendor',
            'server' => 'Run server',
            'update' => 'Update core',
            'backup' => 'Create self backup',
            'release' => 'Create release',
            'template' => [$cool . 'template index' => 'Create new template'],
            'plugin' => [$cool . 'plugin <name>' => 'Run Plugin'],
        ];

        switch ($command->getName()) {
            case '':
                {
                    self::$logger->warning('assuming empty command using backup command as welcome for empty command');
                }

            case 'welcome':
                {
                    self::$logger->info("commands", ['json' => $commands, 'depth' => 2]);
                }
                break;

            case 'server':
                {
                    $startup = \Core\Auxiliary::auto(true, true);

                    \Core\Auxiliary::attachFriend(\Friends\React\Http::class, 51190);
                    \Core\Auxiliary::attachFriend(\Friends\React\WebSocket::class, 51192);

                    self::$logger->debugJson($startup, "auto");

                    while (\Core\Auxiliary::heartbeat()) {
                        \Core\Auxiliary::runLoop();
                    }
                }
                break;

            case 'backup':
                {
                    self::backup();
                }
                break;

            case 'tmpl':
            case 'template':
                {
                    \Core\Auxiliary::auto(false);

                    self::template($command);
                }
            // no break here since reload is needed

            case 'reload':
                {
                    \Library\Helper::exec("composer dump-autoload");
                }
                break;

            // should be a service for plugin case
            case 'plugin':
                {
                    if ($command->noParameters()) {
                        self::$logger->info("syntax: {$cool} plugin <name>");
                        break;
                    }

                    self::plugin($command->getParameters()[0]);

                }
                break;

            case 'release':
                {
                    if (!file_exists("tmp")) {
                        self::$logger->info("making tmp folder.");
                        mkdir("tmp");
                    }

                    if (!file_exists("release")) {
                        self::$logger->info("making release folder.");
                        mkdir("release");
                    }

                    \Library\Helper::exec("rsync -av  ./ ./tmp --exclude release --exclude tmp --exclude .phpintel --exclude .vscode --exclude .git --exclude .gitignore --exclude *.swp --exclude client/node_modules --exclude server/vendor --exclude server/composer.lock");

                    $nextVersion = self::getNextVersion();

                    \Library\Helper::exec("tar czvf release/AppBasis-v-$nextVersion-.tar.gz tmp/*");
                }
                break;


            default:
                self::$logger->critical("unknown command `{$command->getName()}`");
        }
    }
}

# main;

$self = array_shift($argv);

$command = '';
$params = [];

if (!empty($argv)) {
    $command = array_shift($argv);
}

exit(AppBasis::main(
    $self,
    new \Modules\Logger(AppBasis::class),
    new \Modules\Command($command, $argv)
));
