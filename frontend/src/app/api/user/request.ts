/**
 * @file: app/api/user/request.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from '@angular/core';
import { Logger } from '../../logger';
import { API_Request } from '../request';
import { API_Client_Http } from '../clients/http'
import { API_User_Index_Recv } from '../user/model'
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Request_User extends API_Request {
    protected _name = 'user';
    private logger: Logger;
    //----------------------------------------------------------------------

    constructor(private client: API_Client_Http) {
        super(client);
        // ----
        this.logger = new Logger("API_Request_User");
        this.logger.startWith("constructor", { client: client.constructor.name });
    }
    //----------------------------------------------------------------------

    public index(callback): void {
        this.logger.debug("index", "");

        return this.get("index", function(data: API_User_Index_Recv) {
            data.isactive = Boolean(data.isactive);


            return callback(data);
        });
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------