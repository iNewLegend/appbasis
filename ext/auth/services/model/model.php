<?php

/**
 * @file: ext/auth/services/model/model.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Services\Auth\Model;

class EnumCheckReturn 
{
    const EMPTY_HASH = 0;
    
    const INVALID_HASH = 1;
    const INVALID_IP = 2;
    const INVALID_CRC = 3;
    
    const SESSION_EXPIRED = 4;

    const FAIL_ON_BLOCK_CHECK = 5;

    const SUCCESS = 9;    
}

class EnumLoginReturn 
{
    const FAIL_ON_BLOCK_CHECK = 0;

    const INVALID_EMAIL = 1;

    const WRONG_EMAIL = 2;
    const WRONG_PASSWORD = 3;

    const USER_INACTIVE = 4;

    const SYSTEM_ERROR = 5;
}

class EnumRegisterReturn
{
    const FAIL_ON_BLOCK_CHECK = 0;
    
    const INVALID_EMAIL = 1;

    const BAD_PASSWORD = 2;

    const EMAIL_TAKEN = 3;

    const SYSTEM_ERROR = 5;

    const SUCCESS = 9;
}  // EOF ext/auth/services/model/model.php
