<?php
/**
 * @file    : models/Session.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * Indicates if the model should be timestamped
     *
     * @return bool
     */
    public $timestamps = false;

    /**
     * Get Session by hash
     *
     * @param string $hash
     * @return array|boolean
     */
    public function getByHash($hash)
    {
        $session = $this->where('hash', $hash)->get()->first();
  
        if (! $session) {
            return false;
        }

        return $session->toArray();
    }

    /**
     * Add new session
     *
     * @param int $id
     * @param string $hash
     * @param string $expire
     * @param string $ip
     * @param string $agent
     * @param string $crc
     * @return boolean
     */
    public function add($id, $hash, $expire, $ip, $agent, $crc)
    {
        $session = new Session();

        $session->uid = $id;
        $session->hash = $hash;
        $session->expiredate = $expire;
        $session->ip = $ip;
        $session->agent = $agent;
        $session->cookie_crc = $crc;

        if ($session->save()) {
            return true;
        }
        
        return false;
    }

    /**
     * Delete Session by hash
     *
     * @param string $hash
     * @return boolean
     */
    public function deleteByHash($hash)
    {
        $session = $this->where('hash', $hash)->get()->first();
        
        if($session) {
            return $session->delete();
        }

        return false;
    }
    
} // EOF Session.php