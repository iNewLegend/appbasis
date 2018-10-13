/**
 * @file: app/api/authorization/service.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from "@angular/core";
import { Logger } from '../../logger';

import { API_Service } from "../service";
import { API_Request_Authorization } from "../authorization/request";
import { API_Model_Authorization_States, API_Model_Authorization_Send } from "../authorization/model";
import { Subject, Observable } from 'rxjs';

//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Service_Authorization {
    //----------------------------------------------------------------------
    private logger: Logger;
    private result: any;

    //----------------------------------------------------------------------

    constructor(
        private api: API_Service,
        private request: API_Request_Authorization) {
        // ----
        this.logger = new Logger("API_Service_Authorization");
        this.logger.debug("constructor", "");
    }

    private setStatus(status: boolean) {
        // # Should i have this function at all ?
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

    public passThrough() : Observable<boolean>
    {
        let subject = new Subject<boolean>();
        let hash = this.api.getAuthHash();

        this.request.check(hash, ( function(data) {
          this.setResult(data);
        
            switch (data.code) {
                case "success":
                    this.api.setAuthHash(hash);
                    this.setStatus(true);

                    break;

                case "block":
                    if (data.subcode == 'verify') {
                        this.api.setAuthState(API_Model_Authorization_States.VERIFY);
                    }

                default:
                // # fail2auth
                // ---
                this.api.setAuthHash("");
                this.setStatus(false);
            }

            subject.next(true);

        }).bind(this));

        return subject;
    }
    
    public checkAuthentication(invertReturn = false) : Promise<boolean>   {
        this.logger.startWith("checkAuthentication", { invertReturn: invertReturn });

        let promise = new Promise<boolean>((resolve) => {
            if (this.api.getAuthState() == API_Model_Authorization_States.UNAUTHORIZED) {
                resolve(invertReturn);
            } else if (this.api.getAuthState() == API_Model_Authorization_States.AUTHORIZED) {
                resolve(! invertReturn);
            } else {
                this.api.setAuthState(API_Model_Authorization_States.PREPARE);
        
                let hash = this.api.getAuthHash()

                this.request.check(hash, function (data) {
                    this.setResult(data);
        
                    switch (data.code) {
                        case "success":
                            this.api.setAuthHash(hash);
                            this.setStatus(true);
                        
                            resolve(! invertReturn);
                            return;
        
                        case "block":
                            if (data.subcode == 'verify') {
                                this.api.setAuthState(API_Model_Authorization_States.VERIFY);
        
                                break;
                            }
                    }
                    // # fail2auth
                    // ---
                    this.api.setAuthHash("");
                    this.setStatus(false);
                
                    resolve(invertReturn);
                }.bind(this));
            }
        });

        return promise;
    }
    //----------------------------------------------------------------------

    public login(data: API_Model_Authorization_Send, callback): void {
        this.logger.startWith("login", data);
        this.logger.debug("login", " callback: `" + Boolean(callback) + "`")

        this.request.login(data, function (data) {
            this.setResult(data);   

            if (data.code != null) {
                if (data.code == "success") {
                    this.api.setAuthHash(data.hash);
                    this.setStatus(true);
                }

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