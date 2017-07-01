<?php
/**
 * @file    : models/Config.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    /**
     * Indicates if the model should be timestamped
     *
     * @return bool
     */
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'config';

    /**
     * A container for saving data from database
     *
     * @var string
     * @uses as cache
     */
    protected $data = [];

    /**
     * Gets a value of each requested key from the databse or data cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }

        $return = Config::select('value')->where('setting', $key)->get()->first();

        if ($return) {
            $data[$key] = $return->toArray()['value'];

            return $data[$key];
        }

        return null;
    }

    /**
     * Gets a value of settings from config
     *
     * @param  string    $key
     * @return int
     */
    public function __get($key)
    {
        return Config::get($key);
    }
} // EOF Config.php