/**
 * @file: app/api/user/request.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from '@angular/core';

import { API_Request } from '../request';
import { API_Client_Http } from '../clients/http'
import { API_Model_User_Index_Recv } from '../user/model'

import { Logger } from '../../logger';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Request_User extends API_Request {
    private logger: Logger;
    //----------------------------------------------------------------------

    constructor(protected http: API_Client_Http) {
        // ----
        super('user');
        // ----
        this.logger = new Logger("API_Request_User");
        this.logger.startWith("constructor", { client: this.constructor.name });
    }
    //----------------------------------------------------------------------

    public index(callback) {
        this.logger.debug("index", "");

        return this.httpGet("index", function(data: API_Model_User_Index_Recv) {
            data.isactive = Boolean(data.isactive);
            
            return callback(data);
        });
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------