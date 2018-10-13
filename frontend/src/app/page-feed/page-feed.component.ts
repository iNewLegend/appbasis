/**
 * @file: app/page-feed/page-feed.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, OnInit } from "@angular/core";

import { Logger } from "app/logger"
import { API_Request_User } from "../api/user/request";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: "app-page-feed",
    templateUrl: "./page-feed.component.html",
    styleUrls: ["./page-feed.component.css"],
    providers: []
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class PageFeedComponent implements OnInit {
    //----------------------------------------------------------------------
    private logger: Logger;

    constructor(
        /*private user: API_Request_User*/) {
        // ----
        this.logger = new Logger("PageFeedComponent");
        this.logger.debug("constructor", "");
        // ----
        //user.index(function(response) {
         //   console.log(response);
        //});
    }
    //----------------------------------------------------------------------

    public ngOnInit() {

    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------