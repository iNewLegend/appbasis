
/**
 * @file: app/template/chat/chat-message/chat-message.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, OnInit, Input } from "@angular/core";
import { Logger } from 'app/logger';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: 'app-chat-message',
    templateUrl: './chat-message.component.html',
    styleUrls: ['./chat-message.component.css']
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class ChatMessageComponent implements OnInit {
    //----------------------------------------------------------------------

    @Input('avatar') avatar: String;
    @Input('message') message: String;
    @Input('owner') owner: String;
    @Input('date') date: String;

    //----------------------------------------------------------------------
    private logger: Logger;

    //---------------------------------------------------------------------------

    constructor() {
        // ----
        this.logger = new Logger("ChatMessageComponent");
        this.logger.debug("constructor", "");
        // ----
    }
    //---------------------------------------------------------------------------

    public ngOnInit() {
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------