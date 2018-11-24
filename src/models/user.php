<?php
/**
 * @file: models/user.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Models;

class User
{
    /**
     * Instance Of Database Module
     *
     * @var \Modules\Database
     */
    private $database;

    /**
     * Function __construct() : Construct User Model
     *
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Database $database)
    {
        $this->database = $database;
    }

    /**
     * Function add() : Add new user
     *
     * @param string       $email
     * @param string       $hashPassword
     * @param string       $firstName
     * @param string       $lastName
     * @param bool|boolean $isActive
     *
     * @return mixed
     */
    public function add(string $email, string $hashPassword, string $firstName, string $lastName, bool $isActive = true)
    {
        $activeString = ($isActive) ? '1' : '0';
        $query        = "INSERT INTO users (isactive, email, password, firstname, lastname, created_at, updated_at) VALUES ('{$activeString}', '{$email}', '{$hashPassword}', '{$firstName}', '{$lastName}', now(), now())";

        $result = $this->database->queryAwait($query);

        if (isset($result->insertId)) {
            return $result->insertId;
        }

        return false;
    }

    /**
     * Function getId() : Return id by email
     *
     * @param  string $email
     *
     * @return mixed
     */
    public function getId(string $email)
    {
        $result = $this->database->queryAwait("SELECT id FROM users WHERE email = '{$email}'");

        if (isset($result->resultRows[0])) {
            return $result->resultRows[0]['id'];
        }

        return false;
    }

    /**
     * Function getEmails() : Get all emails
     *
     * @return mixed
     */
    public function getEmails()
    {
        $result = $this->database->queryAwait("SELECT email FROM users");

        return $result->resultRows;
    }

    /**
     * Function getBase() : Get basic user data by id
     *
     * @param  string $id
     *
     * @return mixed()
     */
    public function getBase(string $id)
    {
        $result = $this->database->queryAwait("SELECT email, password, isactive FROM users WHERE id = '{$id}'");

        if (isset($result->resultRows[0])) {
            $result = $result->resultRows[0];
        }

        return $result;
    }

    /**
     * Function getForEndpoint() : get user data for frontend by id
     *
     * @param  string $id
     *
     * @return mixed
     */
    public function getForEndpoint(string $id)
    {
        $result = $this->database->queryAwait("SELECT id, isactive, email, firstname, lastname, created_at, updated_at FROM users WHERE id = '{$id}'");

        if (isset($result->resultRows[0])) {
            $result = $result->resultRows[0];
        }

        return $result;
    }

    /**
     * Function isEmailTaken() : Checks if email is already taken
     *
     * @param  string  $email
     *
     * @return boolean
     */
    public function isEmailTaken(string $email)
    {
        $result = $this->database->queryAwait("SELECT email FROM users WHERE email = '{$email}'");

        if (isset($result->resultRows[0])) {
            return true;
        }

        return false;
    }

    /**
     * Function getFullName() : Get full name (firstname '<space>' lastname) by id
     * 
     * @param  string $id
     *
     * @return mixed
     */
    public function getFullName(string $id)
    {
        $result = $this->database->queryAwait("SELECT firstname,lastname FROM users WHERE id = '{$id}'");

        if (isset($result->resultRows[0])) {
            return $result->resultRows[0]['firstname'] . ' ' . $result->resultRows[0]['lastname'];
        }

        return __FILE__ . ':' . __LINE__;
    }
} // EOF models/user.php
