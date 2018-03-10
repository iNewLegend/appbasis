<?php
/**
 * @file    : library/Validator.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Library;

use Core;

class Validator
{
    /**
     * Verifies a google captcha code
     *
     * @param string $ip
     * @param string $captcha
     * @return mixed
     */
    public static function checkCaptcha($ip, $captcha)
    {
        $secret = Core\Config::get("Main")->captcha_secret_key;

        try {
            $url  = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret'   => $secret,
                'response' => $captcha,
                'remoteip' => $ip,
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ],
            ];

            $context = stream_context_create($options);
            $result  = file_get_contents($url, false, $context);

            return json_decode($result)->success;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate email
     *
     * @param string $email
     * @return mixed
     */
    public static function checkEmail($email)
    {
        $config = Core\Config::get("User");

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
     * checks if $password is bad password
     *
     * @param string $password
     * @return mixed
     */
    public static function isBadPassword($password)
    {
        $config = Core\Config::get("User");

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
} // EOF Validator.php
