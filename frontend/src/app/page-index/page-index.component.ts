/**
 * @file: app/page-index/page.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, OnInit } from "@angular/core";
import { ActivatedRoute } from "@angular/router";

import { API_Service } from "../api/service";
import { API_Model_Authorization_States } from "../api/authorization/model";

import { Logger } from 'app/logger';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: "app-page-index",
    templateUrl: "./page-index.component.html",
    styleUrls: ["./page-index.component.css"],
    providers: []
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class PageIndexComponent implements OnInit {
    //----------------------------------------------------------------------
    private logger: Logger;

    public authStatus = false;
    public pageAction = 'register';

    //---------------------------------------------------------------------------

    constructor(
        private router: ActivatedRoute,
        private api: API_Service,) {
        // ----
        this.logger = new Logger("PageIndexComponent");
        this.logger.debug("constructor", "");
        // ----
   }
    //---------------------------------------------------------------------------

    ngOnInit() {
        this.router.params.subscribe(params => {
            console.log(params);
            this.pageAction = params['action']; // (+) converts string 'id' to a number
         });
    }
    //----------------------------------------------------------------------


}
//-----------------------------------------------------------------------------------------------------------------------------------------------------