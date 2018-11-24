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

export class API_Request_Chat extends API_Request  {
    //----------------------------------------------------------------------
    private logger: Logger;
    
    //----------------------------------------------------------------------

    constructor(protected websocket: API_Client_WebSocket) {  
        // ----
        super('chat');
        // ----
        this.logger = new Logger(this);
        this.logger.startWith("constructor", { client: this.constructor.name });
    }
    //----------------------------------------------------------------------
    
    public bind(callback, params = {}) {
        return this.socketBind(callback, params);
    }
    //----------------------------------------------------------------------

    public hook(method, callbackResult, callbackHook) {
        return this.socketHook(method, callbackResult , callbackHook);
    }
    //----------------------------------------------------------------------

    public message(message: String, callback) {
        this.socketPost("message", message , callback);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------