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
    public currentRoute: string;
    private logger: Logger;

    constructor(
        private router: Router,
        private api: API_Service,
        private auth: API_Service_Authorization) {
        // ----
        this.logger = new Logger("AppComponent");
        this.logger.debug("constructor", "");
        // ----
        this.currentRoute = this.router.url;

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
        
    }
    //----------------------------------------------------------------------

    public onRouteChanged(url: string) {
        this.logger.startWith("onRouteChanged", {url: url});

        // # set the current url
        this.currentRoute = this.router.url;
    }
    //----------------------------------------------------------------------

    public onAuthChanges(newAuthState: API_Model_Authorization_States) {

    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------