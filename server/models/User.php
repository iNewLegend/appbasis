<?php
/**
 * @file    : server/models/User.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Get the user id by email
     *
     * @return int|boolean
     */
    public function getId($email)
    {
        $return = User::select("id")->where("email", $email)->first();

        if ($return) {
            $return = $return->toArray();
        }

        if (! isset($return['id'])) {
            return false;
        }

        return $return['id'];
    }

    /**
     * Gets basic user data for a given ID and returns an array
     *
     * @param int $id
     * @return array
     */
    public function getBase($id)
    {
        return User::select('email', 'password', 'isactive')->where('id', $id)->first()->toArray();
    }

    /**
     * check is email is already exist
     *
     * @param string $email
     * @return boolean
     */
    public function isEmailTaken($email)
    {
        if (User::select("id")->where("email", $email)->first()) {
            return true;
        }

        return false;
    }
} // EOF User.php