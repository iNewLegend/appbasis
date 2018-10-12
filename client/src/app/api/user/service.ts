/**
 * @file: app/api/user/service.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from "@angular/core";
import { Logger } from '../../logger';

import { API_Service } from "../service";
import { API_User_Index_Recv } from "./model"
import { API_Request_User } from "./request"
import { API_Service_Authorization } from "../authorization/service";
import { API_Model_Authorization_States } from "../../api/authorization/model";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Service_User {
    //----------------------------------------------------------------------
    private logger: Logger;
    private data: API_User_Index_Recv;

    //----------------------------------------------------------------------

    constructor(
        private api: API_Service,
        private auth: API_Service_Authorization,
        private request: API_Request_User) {
        // ----
        this.logger = new Logger("API_Service_User");
        this.logger.debug("constructor", "");
        // ----
        api.authState$.subscribe((newAuthState: API_Model_Authorization_States) => this.onAuthChanges(newAuthState));

        this.data = new API_User_Index_Recv();
    }

    //----------------------------------------------------------------------

    public onAuthChanges(newAuthState: API_Model_Authorization_States) {
        if (newAuthState == API_Model_Authorization_States.AUTHORIZED) {
            this.request.index(this.indexResult.bind(this));
        }
    }
    //----------------------------------------------------------------------


    public indexResult(data: API_User_Index_Recv) {
        this.logger.startWith("indexResult", data);

        this.data = data;
    }
    //----------------------------------------------------------------------

    public getAll(): API_User_Index_Recv {
        return this.data;
    }
    //----------------------------------------------------------------------

    public getFirstName() : String {
        return this.data.firstname;
    }
    //----------------------------------------------------------------------

    public getFullName() : String {
        return this.data.firstname + " " + this.data.lastname;
    }
    
    //----------------------------------------------------------------------

    public getEmail() : String {
        return this.data.email;
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------