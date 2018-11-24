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
import { Subject, Observable } from 'rxjs';
import { API_Model_Authorization_States } from '../authorization/model';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Client_Http {
    //----------------------------------------------------------------------
    private logger: Logger; 
    private headers: Headers;

    //----------------------------------------------------------------------

    constructor(    
        private http: Http,
        private api: API_Service) {
        // ----
        this.logger = new Logger("API_Client_Http");
        this.logger.debug("constructor", "");;

        this.headers = new Headers();
    }
    //----------------------------------------------------------------------

    private authHeader() {
        // checks if the authGuard is active and if so try put hash header
        if (this.api.getAuthState() >= API_Model_Authorization_States.AUTHORIZED) {
            this.headers.set('hash', this.api.getAuthHash());
        }
    }
    //----------------------------------------------------------------------

    public get(url: string, callback) : Observable<any> {
        this.logger.startWith("get", {
            url: url,
            callback: Boolean(callback),
        });
        // ----
        this.authHeader();

        if (callback) {

            let subject = new Subject();

            // send get
            this.http.get(environment.http_base + url, { headers: this.headers }).map((response: Response) => {
                let data;

                try {
                    data = response.json();
                } catch (e) {
                    data = response.text();
                }

                this.logger.recv("get", {url: url}, data);
                // ----
                // # callback

                subject.next(callback(data));
            }).subscribe(
                result => {
                  // Handle result
                  console.log(result)
                },
                error => {
                    console.log(error);
                    this.api.setAuthState(API_Model_Authorization_States.DISCONNECTED);
                },
                () => {
                  // 'onCompleted' callback.
                  // No errors, route to new page here
                });

            return subject;
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
            return this.http.post(environment.http_base + url, data, { headers: this.headers }).map((response: Response) => {
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