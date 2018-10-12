
/**
 * @file: app/page-index/page.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, OnInit, ViewChild, ElementRef, OnDestroy, AfterViewInit, AfterContentInit } from "@angular/core";


import { API_Service } from "../api/service";
import { API_Request_Chat } from "app/api/chat/request"
import { API_Chat_Message_Recv } from 'app/api/chat/model'

import { Logger } from 'app/logger';
import { API_Client_WebSocket } from "../api/clients/websocket";

import { ChatMessageComponent } from "app/template/chat/chat-message/chat-message.component"
//-----------------------------------------------------------------------------------------------------------------------------------------------------

declare var window: any;

var audioCtx = new (window.AudioContext || window.webkitAudioContext || window.audioContext);

/**
 * @see: https://github.com/damavrom/Important-Sound/blob/master/alarm.html
 */
function beep(duration, frequency, volume, type = null, callback = null) 
{
    var oscillator = audioCtx.createOscillator();
    var gainNode = audioCtx.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioCtx.destination);

    if (volume){gainNode.gain.value = volume;};
    if (frequency){oscillator.frequency.value = frequency;}
    if (type){oscillator.type = type;}
    if (callback){oscillator.onended = callback;}

    oscillator.start();
    setTimeout(function(){oscillator.stop()}, (duration ? duration : 500));
};
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: 'app-page-chat',
    templateUrl: './page-chat.component.html',
    styleUrls: ['./page-chat.component.css'],
    providers: [ 
        API_Client_WebSocket,
        API_Request_Chat,
        ChatMessageComponent
    ]
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class PageChatComponent implements OnInit {
    //----------------------------------------------------------------------
    @ViewChild('myMessages') myMessages:ElementRef
    //----------------------------------------------------------------------

    private logger: Logger;
    private messages: Array<API_Chat_Message_Recv> = [];
    private message: String;

    private recvOnce = false;
    //---------------------------------------------------------------------------

    constructor(
        private chat: API_Request_Chat,
        private api: API_Service) {
        // ----
        this.logger = new Logger("PageChatComponent");
        this.logger.debug("constructor", "");
    }
    //---------------------------------------------------------------------------

    public ngOnInit() {
        this.logger.debug("ngOnInit", "");

        this.chat.bind(this.onRecv.bind(this));
    }
    //---------------------------------------------------------------------------

    private onRecv(data) {   
        this.logger.startWith("onRecv", {data: JSON.stringify(data)});

        if (! this.recvOnce) {
            this.recvOnce = true;
            
            // initial recv
            this.chat.hook('newmessage', this.onHookResult.bind(this), this.onMessage.bind(this));
        }
    }
    //----------------------------------------------------------------------

    private onHookResult(data) {
        this.logger.startWith('onHookResult', {data: JSON.stringify(data)});

        for (let message of data.messages) {
            this.messages.push(message);
        }

        this.chat.message("[AutoMessage] Hey i just created new session.", this.onMessageSendResult.bind(this));
        
        this.myMessages.nativeElement.scrollTop = this.myMessages.nativeElement.scrollHeight;
    }
    //----------------------------------------------------------------------

    public onMessageResult(data)
    {
        this.logger.startWith("onMessageResult", {data: JSON.stringify(data)});
    }
    //----------------------------------------------------------------------

    public onMessageSendResult(data)
    {
        this.logger.startWith("onMessageSendResult", {data: JSON.stringify(data)});
    }
    //----------------------------------------------------------------------

    public onMessage(message: API_Chat_Message_Recv) {
        this.logger.startWith("onMessage", message);

        if (message.clear) {
            // clear command
            
            this.messages = [];
        } else {
            this.messages.push(message);
            
            setTimeout(() => {
                this.myMessages.nativeElement.scrollTop = this.myMessages.nativeElement.scrollHeight
            }, 200);
        }
  
        beep(35, 2000, 0.1);
    }
    //----------------------------------------------------------------------

    public sendMessage()
    {
        this.chat.message(this.message, this.onMessageSendResult.bind(this));
        
        this.message = '';
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------