/**
 * @file: api/authorization/guard.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description: 
 * @todo: 
 * @see: https://codecraft.tv/courses/angular/routing/router-guards/
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from "@angular/core";
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from "@angular/router";

import { API_Service } from "../service";
import { API_Service_Authorization } from "../authorization/service";
import { API_Model_Authorization_States } from "../authorization/model";

import { Logger } from "app/logger";
import { Observable } from "rxjs";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Guard_Authorization_Require implements CanActivate {
    //----------------------------------------------------------------------
    private logger: Logger;
    
    constructor(private auth: API_Service_Authorization) {
        // ----
        this.logger = new Logger(this);
        this.logger.debug("API_Guard_Authorization_Require", "");
    }
    //----------------------------------------------------------------------

    public canActivate(next: ActivatedRouteSnapshot,state: RouterStateSnapshot): Observable<boolean> {
        return this.auth.passThrough();
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Guard_Authorization_Authorized implements CanActivate {
    //----------------------------------------------------------------------
    private logger: Logger;
    
    constructor(
        private api: API_Service,
        private auth: API_Service_Authorization,
        private router: Router) {
        // ----
        this.logger = new Logger(this);
        this.logger.debug("constructor", "");
    }
    //----------------------------------------------------------------------

    public canActivate(next: ActivatedRouteSnapshot,state: RouterStateSnapshot): Promise<boolean> {
        return this.auth.checkAuthentication();
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Guard_Authorization_Unauthorized implements CanActivate {
    //----------------------------------------------------------------------
    private logger: Logger;
    
    constructor(
        private api: API_Service,
        private auth: API_Service_Authorization,
        private router: Router) {
        // ----
        this.logger = new Logger(this);
        this.logger.debug("constructor", "");
    }
    //----------------------------------------------------------------------

    public canActivate(next: ActivatedRouteSnapshot,state: RouterStateSnapshot): Promise<boolean> {
        
        let promise = this.auth.checkAuthentication(true);

        promise.then((function (status: Boolean) {
            if (this.router.url == '/') {
                let authState = this.api.getAuthState();
                
                if(authState == API_Model_Authorization_States.AUTHORIZED) {
                    this.router.navigate(['chat']);
                    //alert();
                } else if(authState == API_Model_Authorization_States.UNAUTHORIZED) {
                    //this.router.navigate(['index/register']);
                    //alert();
                }
            }
        }).bind(this));

        return promise;
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------
