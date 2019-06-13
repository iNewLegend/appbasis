<?php
/**
 * @file: library/Validator.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Library\Auth;

class Validator
{
    /**
     * Function invalidEmail() Checks if mail is invalidEmail
     * return false when valid else return error code
     *
     * @param string $email
     *
     * @return bool|string (short|long|invalid)
     */
    public static function invalidEmail($email)
    {
        /** @var \Config\Auth $config */
        $config = \Services\Config::get("Auth");

        $emailLength = strlen($email);

        if ($emailLength < intval($config->email_min_length)) {
            return 'short';
        }

        if ($emailLength > intval($config->email_max_length)) {
            return 'long';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'invalid';
        }

        return false;
    }

    /**
     * Function: isBadPassword() : Checks if $password is bad password
     * return false when valid else return error code
     *
     * @param string $password
     *
     * @return bool|string (short|weak)
     */
    public static function isBadPassword($password)
    {
        /** @var \Config\Auth $config */
        $config = \Services\Config::get("Auth");

        if (strlen($password) < intval($config->password_min_length)) {
            return 'short';
        }

        $zxcvbn        = new \ZxcvbnPhp\Zxcvbn();
        $passwordScore = $zxcvbn->passwordStrength('-' . $password);
        $passwordScore = $passwordScore['score'];

        if ($passwordScore < intval($config->password_min_score)) {
            return 'weak';
        }

        return false;
    }
} // EOF libraryu/validator.php
