/**
 * @file: app/api/services/authorization.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from "@angular/core";
import { Logger } from '../../logger';

import { API_Service } from "../service";
import { API_Client_Http } from "../clients/http";
import { API_Request_Authorization } from "../authorization/request";
import { API_Model_Authorization_States, API_Model_Authorization_Send, API_Model_Authorization_Recv } from "../authorization/model";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Service_Authorization {
    //----------------------------------------------------------------------
    private logger: Logger;
    private http: API_Client_Http;
    private result: any;

    //----------------------------------------------------------------------

    constructor(
        private api: API_Service,
        private request: API_Request_Authorization) {
        // ----
        this.logger = new Logger("API_Service_Authorization");
        this.logger.debug("constructor", "");
    }
    //----------------------------------------------------------------------

    private setStatus(status: boolean) {
        // # ASk: Should i have this function at all ?
        // ----
        this.logger.startWith("setStatus", { status: status });
        status ? this.api.setAuthState(API_Model_Authorization_States.AUTHORIZED) : this.api.setAuthState(API_Model_Authorization_States.UNAUTHORIZED);
    }

    //----------------------------------------------------------------------

    private getResult() {
        return this.result;
    }

    //----------------------------------------------------------------------

    private setResult(result: any) {
        this.result = result;
    }
    //----------------------------------------------------------------------

    public try() {
        // # TODO
        // Check this function.
        // ----
        this.logger.debug("try", "");

        if (this.api.getAuthState() != API_Model_Authorization_States.NONE || this.api.getAuthState() == API_Model_Authorization_States.AUTHORIZED) {
            return true;
        }

        this.api.setAuthState(API_Model_Authorization_States.PREPARE);

        let hash = this.api.getAuthHash();

        return this.request.check(hash, function (data) {
            this.setResult(data);

            switch (data.code) {
                case "success":
                    this.api.setAuthHash(hash);
                    this.setStatus(true);

                    return;

                case "block":
                    if (data.subcode == 'verify') {
                        this.api.setAuthState(API_Model_Authorization_States.VERIFY);
                    }

                    return;
            }
            // # fail2auth
            // ---
            this.api.setAuthHash("");
            this.setStatus(false);

        }.bind(this));
    }
    //----------------------------------------------------------------------

    public login(data: API_Model_Authorization_Send, callback): void {
        this.logger.startWith("login", {});

        this.request.login(data, function (data) {
            this.setResult(data);   

            if (data.code != null) {
                if (data.code == "success") {
                    this.api.setAuthHash(data.hash);
                    this.setStatus(true);
                }

                // callback only if code avialable
                callback(data);
            };
        }.bind(this));
    }
    //----------------------------------------------------------------------

    public logout(callback) {
        this.request.logout(this.api.getAuthHash(), function (data) {
            // if success logout 
            if(data.code != null) {
                if(data.code == "success") {
                    this.api.setAuthHash("");
                    this.setStatus(false);

                    callback(data);
                }
            }
        }.bind(this));
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------