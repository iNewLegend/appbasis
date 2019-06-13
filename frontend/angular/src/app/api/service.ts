/**
 * @file: app/api/services/authorization.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 * @description: a share between the services.
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import 'rxjs/add/operator/map'
import 'rxjs/add/operator/catch';

import { Injectable } from '@angular/core';

import { Observable } from 'rxjs/'
import { BehaviorSubject } from 'rxjs/BehaviorSubject';

import { API_Model_Authorization_States } from './authorization/model';
import { Logger } from '../logger'
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Service {
    protected logger: Logger; 
    
    private authState: BehaviorSubject<API_Model_Authorization_States> = new BehaviorSubject<API_Model_Authorization_States>(API_Model_Authorization_States.NONE);
    //----------------------------------------------------------------------

    authState$: Observable<API_Model_Authorization_States>;

    constructor() {
        this.logger = new Logger(this);
        this.logger.debug("constructor", "");

        this.authState$ = this.authState.asObservable();
    }
    //----------------------------------------------------------------------

    setAuthHash(hash: string) {
        this.logger.startWith("setHash", { hash: hash });
        // ----
        localStorage.setItem('hash', hash);
    }
    //----------------------------------------------------------------------

    getAuthHash(): string {
        let hash = localStorage.getItem('hash');
        // ----
        //console.log('API_Service::getAuthHash() <<- hash: `' + hash  +'`');
        // ----
        if (hash == null) return '';

        return hash;
    }
    //----------------------------------------------------------------------

    getAuthState(): API_Model_Authorization_States {
        let state = this.authState.getValue();
        // ----
        //console.log('API_Service::getAuthState() <<- API_Authorization_States::' + API_Authorization_States[state]);
        // ----
        return state;
    }
    //----------------------------------------------------------------------

    setAuthState(state: API_Model_Authorization_States) {
        this.logger.startWith("setAuthState", { state: "API_Authorization_States::" + API_Model_Authorization_States[state] })
        // ----
        this.authState.next(state);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------