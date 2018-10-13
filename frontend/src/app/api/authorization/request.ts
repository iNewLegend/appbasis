/**
 * @file: app/api/authorization/request.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from '@angular/core';
import { Logger } from '../../logger';

import { API_Request } from '../request';
import { API_Client_Http } from '../clients/http'
import { API_Model_Authorization_Send } from './model'
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Request_Authorization extends API_Request {
    //----------------------------------------------------------------------
    protected _name = 'authorization';
    private logger: Logger;
    
    //----------------------------------------------------------------------

    constructor(private client: API_Client_Http) {  
        super(client);
        // ----
        this.logger = new Logger("API_Request_Authorization");
        this.logger.startWith("constructor", { client: client.constructor.name });
    }
    //----------------------------------------------------------------------
    
    public check(hash, callback) {
        this.logger.startWith("check", { hash: hash });

        return this.get("check/" + hash, callback);
    }
    //----------------------------------------------------------------------

    public login(data: API_Model_Authorization_Send, callback) {
        this.logger.debug("login", "");

        return this.post('login', JSON.stringify(data), callback);
    }
    //----------------------------------------------------------------------
    
    public register(data: API_Model_Authorization_Send, callback) {
        this.logger.debug("register", "");

        return this.post('register', JSON.stringify(data), callback);
    }
    //----------------------------------------------------------------------

    public logout(hash, callback) {
        this.logger.debug("logout", "");

        return this.get("logout/" + hash, callback);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------