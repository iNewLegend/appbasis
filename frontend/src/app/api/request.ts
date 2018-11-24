/**
 * @file: app/api/request.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { API_Client_WebSocket } from './clients/websocket'
import { API_Client_Http } from './clients/http';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class API_Request {
    protected controller: String;
    protected websocket: API_Client_WebSocket;
    protected http: API_Client_Http;
    //----------------------------------------------------------------------

    protected constructor(controller: String) {
        this.controller = controller;
    }
    //----------------------------------------------------------------------

    protected socketBind(callback = null, params = {}) {
        return this.websocket.create(this.controller, callback, params);
    }
    //----------------------------------------------------------------------
    
    protected socketHook(method, callbackResult, callbackHook) {
        return this.websocket.customHook(this.controller, method, callbackResult, callbackHook);
    }
    //----------------------------------------------------------------------

    protected socketGet(method: String, callback = null) {
        return this.websocket.get(this.controller, method, callback);
    }
    //----------------------------------------------------------------------

    protected socketPost(method: String, data, callback = null) {
        return this.websocket.post(this.controller, method, data, callback);
    }
    //----------------------------------------------------------------------

    protected httpSet(http: API_Client_Http) {
        this.http = http;
    }
    //----------------------------------------------------------------------

    protected httpGet(method: String, callback = null) {
        return this.http.get(this.controller + '/' + method, callback);
    }
    //----------------------------------------------------------------------

    protected httpPost(method: String, data, callback = null) {
        return this.http.post(this.controller + '/' + method, data, callback);
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------