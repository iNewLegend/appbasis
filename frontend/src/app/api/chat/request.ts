/**
 * @file: app/api/chat/request.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description
 * @todo: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Injectable } from '@angular/core';
import { Logger } from '../../logger';

import { API_Request } from '../request';
import { API_Client_WebSocket } from '../clients/websocket'

import {  API_Chat_Message_Recv } from './model';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Injectable()

export class API_Request_Chat  {
    //----------------------------------------------------------------------
    protected _name = 'chat';
    private logger: Logger;
    
    //----------------------------------------------------------------------

    constructor(private client: API_Client_WebSocket) {  
        // ----
        this.logger = new Logger("API_Request_Chat");
        this.logger.startWith("constructor", { client: client.constructor.name });
    }
    //----------------------------------------------------------------------
    
    public bind(callback, params = {}) {
        this.client.create(this._name, callback, params);
    }
    //----------------------------------------------------------------------

    public message(message: String, callback) {
        this.client.post(this._name, "message", message , callback);
    }
    //----------------------------------------------------------------------

    public hook(method, callbackResult, callbackHook) {
        this.client.customHook(this._name, method, callbackResult, callbackHook);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------