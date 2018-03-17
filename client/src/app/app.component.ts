/**
 * @file: app/app.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 * @description: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, ViewChild, OnInit, ViewEncapsulation } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { ToastContainerDirective, ToastrService, ToastrConfig } from 'ngx-toastr';
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
    private currentRoute: string;
    private welcomeRoute: boolean;

    private logger: Logger;
    private authStates: API_Model_Authorization_States;
    //----------------------------------------------------------------------

    @ViewChild(ToastContainerDirective) toastContainer: ToastContainerDirective;
    //----------------------------------------------------------------------

    constructor(
        private router: Router,
        private toastrService: ToastrService,
        private toastrConfig: ToastrConfig,
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

        toastrConfig.positionClass = 'position';

        // # Bootstrap configuration
        toastrConfig.toastClass = 'alert';
        toastrConfig.iconClasses = {
            error: 'alert-danger',
            info: 'alert-info',
            success: 'alert-success',
            warning: 'alert-warning',
        }

        toastrConfig.timeOut = 5000;
        toastrConfig.extendedTimeOut = 1000;
        toastrConfig.maxOpened = 2;
        toastrConfig.autoDismiss = true;
    }
    //----------------------------------------------------------------------

    ngOnInit() {
        this.toastrService.overlayContainer = this.toastContainer;
    }
    //----------------------------------------------------------------------

    onRouteChanged(url: string) {
        // # try will work only once
        this.auth.try();


        // # set the current url
        this.currentRoute = this.router.url;

        // # avoid this.
        switch (this.currentRoute) {
            case '/welcome':
                this.welcomeRoute = true;
                break;

            default:
                this.welcomeRoute = false;
        }
    }
    //----------------------------------------------------------------------

    getAuthState() {
        return this.api.getAuthState();
    }
    //----------------------------------------------------------------------

    isAuthPreparing() {
        return (this.getAuthState() == API_Model_Authorization_States.PREPARE ? true : false);
    }
    //----------------------------------------------------------------------

    isAuth() {
        return (this.getAuthState() >= API_Model_Authorization_States.AUTHORIZED ? true : false); 
    }
    //----------------------------------------------------------------------

    isWelcomeRoute() {
        return this.welcomeRoute;
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------