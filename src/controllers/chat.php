<?php

/**
 * @file: controllers/chat.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

class Chat
{
    /**
     * Instance of Ip Module
     *
     * @var \Modules\Ip
     */
    private $ip;

    /**
     * Instance of Object Core
     *
     * @var \Core\OObject
     */
    private $object;

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Instance of User Model
     *
     * @var \Models\User
     */
    private $user;

    /**
     * Session array that comes from guard.
     *
     * @var array
     */
    private $session;

    /**
     * Store all the messages
     * @var array
     */
    private static $messages = [
        [
            'avatar'  => 'https://api.adorable.io/avatars/200/1',
            'date'    => '2018-11-5 06:30:00',
            'message' => 'Welcome to AppBasis',
            'owner'   => 'Leonid Vinikov',
        ],
    ];

    /**
     * Function __construct() : Construct User Controller
     *
     * @param \Modules\Ip     $ip
     * @param \Core\OObject   $object
     * @param \Modules\Logger $logger
     * @param \Services\Auth  $auth
     */
    public function __construct(\Modules\Ip $ip, \Core\OObject $object, \Modules\Logger $logger, \Services\Auth $auth)
    {
        $this->userGuard = new \Guards\User($ip, $object, $auth);

        if ($this->userGuard) {
            $this->userGuard->run();
            $this->session = $this->userGuard->getSession();

        }

        $this->ip     = $ip;
        $this->object = $object;

        $this->logger = $logger;

        $this->user = new \Models\User(\Services\Database\Pool::get());
    }

    /**
     * Function index() : Default method of Controller
     *
     * @return string
     */
    public function index()
    {
        return __FILE__ . ':' . __LINE__;
    }

    /**
     * Function hook() : Used to create custom hook
     * @internal used as default by \Core\Handler
     *
     * @param string $method
     *
     * @return array
     */
    public function hook(string $method)
    {
        $this->logger->debug("method: `{$method}`");

        // override `fake` message
        $fake = [
            'code'     => 'success',
            'messages' => [
                [
                    'avatar'  => 'http://www.artsjournal.com/curves/wp-content/uploads/2018/05/matrix-3109378_1280-200x200.jpg',
                    'date'    => '2018-09-25 23:00:00',
                    'message' => 'Welcome to AppBasis Matrix',
                    'owner'   => '[ System ]',
                ],
            ],
        ];

        // add history
        foreach (self::$messages as $message) {
            $fake['messages'][] = $message;
        }

        return $fake;
    }

    /**
     * Function message() : Called when clients send message.
     *
     * @param string message
     *
     * @return array
     */
    public function message($message)
    {
        $this->logger->debug("message: `{$message}`");

        $uid = $this->session['uid'];

        $owner = $uid ? $this->user->getFullName($uid) : $this->ip->id;

        if (!$uid) {
            $uid = $owner;
        }

        if ($message == '/clear') {
            self::$messages = [];

            self::$messages[] = [
                'avatar'  => 'http://www.artsjournal.com/curves/wp-content/uploads/2018/05/matrix-3109378_1280-200x200.jpg',
                'message' => $owner . " used `{$message}` command.",
                'date'    => date("Y-m-d H:i:s"),
                'owner'   => '[ System ]',
            ];

            \Friends\React\WebSocket::postToAll('newmessage', ['clear' => 'true']);

            $newMessage = end(self::$messages);
        } else {
            $newMessage = [
                'avatar'  => 'https://api.adorable.io/avatars/200/' . $uid,
                'message' => $message,
                'date'    => date("Y-m-d H:i:s"),
                'owner'   => $owner,
            ];

            // if not auto message we save it to history.
            if (!strstr($message, '[AutoMessage]')) {
                self::$messages[] = $newMessage;
            }

        }

        \Friends\React\WebSocket::postToAll('newmessage', $newMessage);

        return ['code' => 'success'];
    }
} // EOF controllers/chat.php
