/**
 * @file: app/navbar/navbar.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { API_Service } from '../api/service';
import { API_Model_Authorization_States } from '../api/authorization/model'
import { API_Service_Authorization } from '../api/authorization/service'
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: 'app-navbar',
    templateUrl: './navbar.component.html',
    styleUrls: ['./navbar.component.css']
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class NavbarComponent implements OnInit {
    private navbarCollapse: boolean;
    private dropDown: boolean;
    private authorized: boolean;
    //----------------------------------------------------------------------

    constructor(
        private toastr: ToastrService,
        private api: API_Service,
        private auth: API_Service_Authorization) {
        // ----
        this.navbarCollapse = false;
        this.dropDown = false;

        // Listen to changes in api
        api.authState$.subscribe((newAuthState: API_Model_Authorization_States) => this.onAuthChanges(newAuthState));
    }
    //----------------------------------------------------------------------

    public ngOnInit() {
    }
    //----------------------------------------------------------------------

    private onAuthChanges(newAuthState: API_Model_Authorization_States) {
        if(newAuthState == API_Model_Authorization_States.AUTHORIZED) {
            this.authorized = true;
            return;
        }

        this.authorized = false;
        
    }
    //----------------------------------------------------------------------

    private logout() {
        this.auth.logout(function () {
            // called only on success
            this.toastr.info('you have been successfully logged out.', 'Thank you');
        }.bind(this));
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------