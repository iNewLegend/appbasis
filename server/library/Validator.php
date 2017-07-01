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
     * @param string $captcha
     * @return mixed
     */
    public function checkCaptcha($captcha)
    {
        $secret = Config::get('captcha_secret_key');

        try {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret'   => $secret,
                'response' => $captcha,
                'remoteip' => $_SERVER['REMOTE_ADDR']
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
     * @return object
     */
    public function validateEmail($email)
    {
        $return = new \stdClass();
        $return->error = true;

        $emailLength = strlen($email);

        if ($emailLength < intval(Config::get('verify_email_min_length'))) {
            $return->message = 'the email is too short';
            return $return;
        }

        if ($emailLength > intval(Config::get('verify_email_max_length'))) {
            $return->message = 'the email is too long';
            return $return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $return->message = 'not valid email';
            return $return;
        }

        $return->error = false;

        return $return;
    }

    /**
     * Validate password
     *
     * @param string $password
     * @return object
     */
    public function validatePassword($password)
    {
        $return = new \stdClass();
        $return->error = true;

        if (strlen($password) < intval(Config::get('verify_password_min_length'))) {
            $return->message = 'the password is too short';
            return $return;
        }

        $zxcvbn = new \ZxcvbnPhp\Zxcvbn();
        $passwordScore = $zxcvbn->passwordStrength('-' . $password)['score'];

        if ($passwordScore < intval(Config::get('password_min_score'))) {
            $return->message = 'The password is too weak';
            return $return;
        }

        $return->error = false;

        return $return;
    }
} // EOF Validator.php