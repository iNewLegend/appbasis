/**
 * @file: app/api/clients/http.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import 'rxjs/add/operator/map'

import { Injectable } from '@angular/core';
import { Http, Response, Headers } from '@angular/http';

import { Logger } from '../../logger';
import { environment } from '../../../environments/environment';

import { API_Service } from '../service';
import { API_Guard_Authorization } from '../authorization/guard';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Client_Http {
    //----------------------------------------------------------------------
    protected logger: Logger; 
    protected headers: Headers;

    //----------------------------------------------------------------------

    constructor(    
        private http: Http,
        private api: API_Service,
        private authGuard: API_Guard_Authorization) {
        // ----
        this.logger = new Logger("API_Client_Http");
        this.logger.debug("constructor", "");;

        this.headers = new Headers();
    }
    //----------------------------------------------------------------------

    private authHeader() {
        // checks if the authGuard is active and if so try put hash header
        if (this.authGuard.canActiveState) {
            this.headers.set('hash', this.api.getAuthHash());
        }
    }
    //----------------------------------------------------------------------

    public get(url: string, callback) {
        this.logger.startWith("get", {
            url: url,
            callback: Boolean(callback),
        });
        // ----
        this.authHeader();

        if (callback) {
            // send get
            return this.http.get(environment.server_base + url, { headers: this.headers }).map((response: Response) => {
                let data;

                try {
                    data = response.json();
                } catch (e) {
                    data = response.text();
                }

                this.logger.recv("get", {url: url}, data);
                // ----
                // # callback

                return callback(data);
            }).subscribe((error) => {
                //
            });
        } 
        
        throw 'API_Client_Http::get() error: callback is empty';
    }
    //----------------------------------------------------------------------

    public post(url, data, callback) {
        this.logger.startWith("post", {
            url: url,
            data: data,
            callback: Boolean(callback),
        });

        this.authHeader();

        if (callback) {
            return this.http.post(environment.server_base + url, data, { headers: this.headers }).map((response: Response) => {
                let data;

                try {
                    data = response.json();
                } catch (e) {
                    data = response.text();
                }

                this.logger.recv("post", {url: url}, data);
                // ----
                // # callback

                return callback(data);
            }).subscribe((error) => {
                //
            });
        }

        throw 'API_Client_Http::post() error: callback is empty';
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------