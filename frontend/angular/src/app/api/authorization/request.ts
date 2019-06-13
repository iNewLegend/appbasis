/**
 * @file: app/api/authorization/request.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from '@angular/core';

import { API_Request } from '../request';
import { API_Client_Http } from '../clients/http'
import { API_Model_Authorization_Send } from './model'

import { Logger } from '../../logger';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Request_Authorization extends API_Request {
    //----------------------------------------------------------------------
    private logger: Logger;
    
    //----------------------------------------------------------------------

    constructor(protected http: API_Client_Http) {  
        // ----
        super('authorization');
        // ----
        this.logger = new Logger("API_Request_Authorization");
        this.logger.startWith("constructor", { client: this.constructor.name });
    }
    //----------------------------------------------------------------------
    
    public check(hash, callback) {
        this.logger.startWith("check", { hash: hash });

        return this.httpGet("check/" + hash, callback);
    }
    //----------------------------------------------------------------------

    public login(data: API_Model_Authorization_Send, callback) {
        this.logger.debug("login", "");

        return this.httpPost('login', data, callback);
    }
    //----------------------------------------------------------------------
    
    public register(data: API_Model_Authorization_Send, callback) {
        this.logger.debug("register", "");

        return this.httpPost('register', data, callback);
    }
    //----------------------------------------------------------------------

    public logout(hash, callback) {
        this.logger.debug("logout", "");

        return this.httpGet("logout/" + hash, callback);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------