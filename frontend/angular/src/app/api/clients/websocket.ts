/**
 * @file: app/api/clients/websocket.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import "rxjs/add/operator/map"

import { Injectable } from "@angular/core";

import { WebSocketSubject } from "rxjs/observable/dom/WebSocketSubject";

import { API_Service } from "../service"
import { API_Model_Authorization_States } from "../authorization/model";

import { Logger } from "../../logger";
import { environment } from "../../../environments/environment";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class API_Hook {
    //----------------------------------------------------------------------
    constructor(    
        public type,
        // public controller should be addded.
        public method,
        public callback) {
        // ----
    }
    // ----
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Client_WebSocket {
    //----------------------------------------------------------------------
    private hooks: API_Hook[] = [];
    private logger: Logger; 
    private socket: WebSocketSubject<any>;

    //----------------------------------------------------------------------

    constructor(private api: API_Service) {
        // ----
        this.logger = new Logger("API_Client_WebSocket");
        this.logger.debug("constructor", "");
    }
    //----------------------------------------------------------------------

    public create(command: String, callback = null, params = null) {
        this.logger.startWith("create", {
            command: command,
            callback: Boolean(callback),
            params: JSON.stringify(params)
        });

        if (this.api.getAuthState() == API_Model_Authorization_States.AUTHORIZED) {
            let hash = this.api.getAuthHash();

            this.logger.debug("create", " since `AUTHORIZED` hash: `" + hash + "` added to post parameters");

            Object.assign(params, { hash: hash });
        }

        // no alert no update
        this.hooks = [];

        this.socket = new WebSocketSubject(environment.websocket_base + command);

        this.socket.subscribe(
            (data) => this.recv(data, callback),
            (err) => console.log(err)
            //() => void
        );
     
        return this.post(command, "auth", params, callback);
    }
    //----------------------------------------------------------------------

    public destroy() {
        this.logger.debug("destroy", "");

        this.hooks = [];

        this.socket.unsubscribe();
    }
    //----------------------------------------------------------------------

    protected recv(data: any = null, callback) {
        this.logger.recv("recv", { callback: Boolean(callback) }, data);

        // part of AppBasis protocol?
        if(data.hasOwnProperty("type") && data.hasOwnProperty("method")) {
            let hookFound = null;

            this.hooks.forEach( (hook: API_Hook) => {
                if (hook.type == data.type && hook.method == data.method) {
                    hookFound = hook;

                    return true;
                }
            });

            if (hookFound) {
                return hookFound.callback(data);
            }
        }
        
        if (callback) {
            callback(data);
        }
    }
    //----------------------------------------------------------------------

    public hook(type, method, callback) {
        this.logger.startWith("hook", {
            type: type,
            method: method,
            callback: Boolean(callback)
        });

        this.hooks.push(new API_Hook(type, method, callback));
    }
    //----------------------------------------------------------------------

    public customHook(controller, method, callbackResult, callBackHook) {
        this.logger.startWith("customHook", {
            controller: controller,
            method: method
        });
        
        this.post(controller, 'hook', {
            method: method
        }, callbackResult);

        this.hook('post', method, callBackHook);
    }
    //----------------------------------------------------------------------

    public send(data: Object) : void {
        this.logger.startWith("send", JSON.stringify(data));

        if (! this.socket) {
            this.logger.debug("send", " error socket is empty");
            return;
        }

        this.socket.next(data);
    }
    
    //----------------------------------------------------------------------

    public get(name: String, method: String, callback = null) : any {
        this.logger.startWith("get", { 
            name: name,
            method: method,
            callback: Boolean(callback)
        });

        // hook a callback in necessary 
        if (callback) {
            this.hook('get', method, callback);
        }

        // protocol
        this.send({
            type: 'get',
            name: name,
            method: method
        })
    }
    //----------------------------------------------------------------------

    public post(name: String, method: String, params: Object, callback = null) : any {
        this.logger.startWith("post", { 
            name: name,
            method: method,
            params: JSON.stringify(params),
            callback: Boolean(callback)
        });

        // hook a callback in necessary 
        if (callback) {
            this.hook('post', method, callback);
        }

        // protocol
        this.send({
            type: 'post',
            name: name,
            method: method,
            params: params
        })
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------