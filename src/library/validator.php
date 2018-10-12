<?php
/**
 * @file: library/Validator.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Library;

class Validator
{
    /**
     * Function invalidEmail() Checks if mail is invalidEmail return false when valid else return error code
     *
     * @param string $email
     *
     * @return mixed
     */
    public static function invalidEmail($email)
    {
        $config = \Services\Config::get("User");

        $emailLength = strlen($email);

        if ($emailLength < intval($config->verify_email_min_length)) {
            return 'short';
        }

        if ($emailLength > intval($config->verify_email_max_length)) {
            return 'long';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'false';
        }

        return false;
    }

    /**
     * Function: isBadPassword() : Checks if $password is bad password
     *
     * @param string $password
     *
     * @return mixed
     */
    public static function isBadPassword($password)
    {
        $config = \Services\Config::get("User");

        if (strlen($password) < intval($config->verify_password_min_length)) {
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
