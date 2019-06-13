/**
 * @file: app/app.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 * @description: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, ViewChild, OnInit, ViewEncapsulation } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { API_Service } from './api/service';
import { API_Service_Authorization } from './api/authorization/service';
import { API_Model_Authorization_States } from 'app/api/authorization/model';
import { Logger } from './logger';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    encapsulation: ViewEncapsulation.None,
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.css'],
    providers: [
    ]
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class AppComponent implements OnInit {
    private logger: Logger;

    public loading = true;
    public disconnected = false;
    //----------------------------------------------------------------------

    constructor(
        private router: Router,
        private api: API_Service,
        private auth: API_Service_Authorization) {
        // ----
        this.logger = new Logger(this);
        this.logger.debug("constructor", "");
        // ----

        this.router.events.subscribe((res) => {
            if (res instanceof NavigationEnd) {
                this.onRouteChanged(this.router.url);
            }
        });
       

        this.router.events.subscribe();

        // Listen to changes of auth state
        api.authState$.subscribe((newAuthState: API_Model_Authorization_States) => this.onAuthChanges(newAuthState));
    }
    //----------------------------------------------------------------------

    public ngOnInit() {
        this.loading = true;
    }
    //----------------------------------------------------------------------

    public onRouteChanged(url: string) {
        this.logger.startWith("onRouteChanged", {url: url});
    }
    //----------------------------------------------------------------------

    public onAuthChanges(newAuthState: API_Model_Authorization_States) {
        switch(newAuthState) {
            case API_Model_Authorization_States.AUTHORIZED:
            case API_Model_Authorization_States.UNAUTHORIZED:
            case API_Model_Authorization_States.VERIFY:
            this.loading = false;
            this.disconnected = false;
            break;

            case API_Model_Authorization_States.PREPARE:
            case API_Model_Authorization_States.DISCONNECTED:
            this.loading = false;
            break;

            default:
            this.loading = true;
        }
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------