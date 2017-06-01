<?php
/**
 * file 		: /app/models/Config.php
 * author 	    : czf.leo123@gmail.com
 * todo		    :
 * desc		:
 */

namespace Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Config extends Eloquent
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
     * Avoid getting of the same data from db
     *
     * @var string
     */
    protected $data = [];

    /**
     * Gets a value of settings from config
     *
     * @param  string    $key
     * @return int
     */
    public function get($key)
    {
        if(isset($data[$key])) {
            return $data[$key];
        }

        $return = Config::select('value')->where('setting', $key)->get()->first();

        if($return) {
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