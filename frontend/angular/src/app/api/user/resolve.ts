/**
 * @file: app/api/user/resolve.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from "@angular/core";
import { Resolve, ActivatedRouteSnapshot } from "@angular/router";

import { Logger } from "../../logger"

import { API_Request_User } from "./request";
import { API_Model_User_Index_Recv } from "./model";
//-----------------------------------------------------------------------------------------------------------------------------------------------------
/*
@Injectable()

export class API_Resolve_User implements Resolve<any> {
    //----------------------------------------------------------------------
    private logger: Logger;

    constructor(    
        private request: API_Request_User) {
        // ----
        this.logger = new Logger(this);
        this.logger.debug("constructor", "");
    }
    //----------------------------------------------------------------------

    public resolve() {
        return this.request.index(function (data: API_Model_User_Index_Recv) {
            return data;
        });
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------*/