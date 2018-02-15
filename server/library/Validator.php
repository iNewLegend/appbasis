<?php
/**
 * @file    : library/Validator.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Library;

use Models\Config;

class Validator
{
    /**
     * Verifies a google captcha code
     *
     * @param string $ip
     * @param string $captcha
     * @return mixed
     */
    public function checkCaptcha($ip, $captcha)
    {
        $secret = Config::get('captcha_secret_key');

        try {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret'   => $secret,
                'response' => $captcha,
                'remoteip' => $ip,
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

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
    public function checkEmail($email)
    {        
        $emailLength = strlen($email);

        if ($emailLength < intval(Config::get('verify_email_min_length'))) {
            return 'short';
        }

        if ($emailLength > intval(Config::get('verify_email_max_length'))) {
            return 'long';
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'false';
        }

        return false;
    }

     /**
      * Validate password
      *
      * @param string $password
      * @return mixed
      */
    public function validatePassword($password)
    {
        if (strlen($password) < intval(Config::get('verify_password_min_length'))) {
            return 'short';
        }

        $zxcvbn = new \ZxcvbnPhp\Zxcvbn();
        $passwordScore = $zxcvbn->passwordStrength('-' . $password)['score'];

        if ($passwordScore < intval(Config::get('password_min_score'))) {
            return 'weak';
        }

        return false;
    }
} // EOF Validator.php