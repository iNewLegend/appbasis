<?php
/**
* file 		: /app/core/Controller.php
* author 	: czf.leo123@gmail.com
* todo		:
*/

namespace Core;

class Controller
{
    /**
     * The models that will be loaded with their instance 
     *
     * @var array
     */
    protected static $loadedModels = [];

    /**
     * Create library instance.
     *
     * @param  string    $library
     * @throws Exception
     * @return object
     */
    public static function Library($library)
    {
        $libraryPath = __DIR__ . '/../library/' . $library . '.php';

        if(! file_exists($libraryPath)) {
            throw new Exception("library: '$library' not found");

        }

        require_once($libraryPath);

        $library = 'Library\\' . $library;

        return new $library;
    }

    /**
     * Create model instance.
     *
     * @param  string    $model
     * @throws Exception
     * @return object
     */
    public static function Model($model)
    {
        if(class_exists($model, false)) {
            return self::$loadedModels[$model];
        }

        $modelPath = __DIR__ . '/../models/' . $model . '.php';

        if(! file_exists($modelPath)) {
            throw new Exception("model: '$model' not found");
        }

        $model = 'Models\\' . $model;

        require_once($modelPath);

        self::$loadedModels[$model] = new $model;

        return self::$loadedModels[$model];
    }
} // EOF Controller.php