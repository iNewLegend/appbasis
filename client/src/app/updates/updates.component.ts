/**
 * @file: app/updates/updates.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 * @description: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, ViewChild, OnInit, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { ToastContainerDirective, ToastrService, ToastrConfig } from 'ngx-toastr';
import { API_Service } from '../api/service';
import { API_Request_Welcome } from '../api/welcome/request';
import { API_Model_Welcome_Updates } from '../api/welcome/model';
import { Logger } from '../logger';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: 'app-updates',
    templateUrl: './updates.component.html',
    styleUrls: ['./updates.component.css'],
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class UpdatesComponent implements OnInit {
    private logger: Logger;
    private updates: API_Model_Welcome_Updates[];
    //----------------------------------------------------------------------

    constructor(private welcomeRequest: API_Request_Welcome) {
        // ----
        this.logger = new Logger("RegisterComponent");
        this.logger.debug("constructor", "");
    }
    //----------------------------------------------------------------------

    ngOnInit() {
        this.welcomeRequest.updates(function (data) {
            this.updates = data;
        }.bind(this));
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------