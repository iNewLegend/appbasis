/**
 * @file: api/authorization/guard.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description: 
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';

import { API_Service_Authorization } from '../authorization/service';
import { API_Service } from '../service';
import { API_Model_Authorization_States } from './model';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Guard_Authorization implements CanActivate {
    //----------------------------------------------------------------------
    public canActiveState: boolean;

    //----------------------------------------------------------------------

    constructor(
        private api: API_Service,
        /*private router: Router*/) {
        // ----
        this.canActiveState = false;
    }
    //----------------------------------------------------------------------

    public canActivate(next: ActivatedRouteSnapshot,state: RouterStateSnapshot): boolean {
        this.canActiveState = true;

        if(this.api.getAuthState() == API_Model_Authorization_States.AUTHORIZED) {
            return true;
        }

        return false;
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------