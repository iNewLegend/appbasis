<?php
/**
 * @file    : models/User.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * Get the user id by email
     *
     * @return int|boolean
     */
    public function getId($email)
    {
        $return = $this->select("id")->where("email", $email)->first();

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
        return $this->select('email', 'password', 'isactive')->where('id', $id)->first();
    }

    /**
     * Check is email is already exist
     *
     * @param string $email
     * @return boolean
     */
    public function isEmailTaken($email)
    {
        if ($this->select("id")->where("email", $email)->first()) {
            return true;
        }

        return false;
    }

    /**
     * Add new user
     *
     * @param string $email
     * @param string $password
     * @param boolean $isactive
     * @return boolean
     */
    public function add($email, $password, $isactive)
    {
        $user = new User;

        $user->email = $email;
        $user->password = $password;
        $user->isactive = true;

        if ($user->save()) {
            return true;
        }

        return false;
    }
} // EOF User.php