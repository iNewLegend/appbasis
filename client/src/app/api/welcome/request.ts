/**
 * @file: app/api/welcome/request.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from '@angular/core';
import { Logger } from '../../logger';
import { API_Request } from '../request';
import { API_Client_Http } from '../clients/http'
import { API_Model_Welcome_Updates } from '../welcome/model'
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Request_Welcome extends API_Request {
    protected _name = 'welcome';
    private logger: Logger;
    //----------------------------------------------------------------------

    constructor(private client: API_Client_Http) {
        super(client);
        // ----
        this.logger = new Logger("API_Request_Welcome");
        this.logger.startWith("constructor", { client: client.constructor.name });
    }
    //----------------------------------------------------------------------

    public updates(callback): void {
        this.logger.debug("updates", "");

        return this.get("updates", function (data: API_Model_Welcome_Updates[]) {
            for (let i in data) {
                data[i].date = new Date(data[i].date);
            }

            return callback(data);
        });
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------