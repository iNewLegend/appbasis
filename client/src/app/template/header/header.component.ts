/**
 * @file: app/template/header.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, OnInit } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';

import { API_Service } from "app/api/service";
import { API_Service_User } from "app/api/user/service";
import { API_Model_Authorization_States } from "../../api/authorization/model";
import { API_Service_Authorization } from '../../api/authorization/service';

import { Logger } from 'app/logger';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class HeaderComponent implements OnInit {
  private route: string;
  private logger: Logger;

  public authState: API_Model_Authorization_States;

  public headerClass = '';

  public isCollapsedNavbar = true;
  public isCollapsedName = false;

  //----------------------------------------------------------------------

  constructor(
    private router: Router,
    private api: API_Service,
    private auth: API_Service_Authorization,
    private user: API_Service_User) {
    // ----
    this.logger = new Logger("PageIndexComponent");
    this.logger.debug("constructor", "");

    // Listen to changes of auth state
    api.authState$.subscribe((newAuthState: API_Model_Authorization_States) => this.onAuthChanges(newAuthState));
  }
  //----------------------------------------------------------------------

  public ngOnInit() {
    this.route = this.router.url;

    this.router.events.subscribe((res) => {
      if (res instanceof NavigationEnd) {
        this.onRouteChanged(this.router.url);
      }
    });
  }
  //----------------------------------------------------------------------

  public onRouteChanged(url: string) {
    this.logger.startWith("onRouteChanged", { url: url });

    // # set the current url
    this.route = this.router.url;
  }
  //----------------------------------------------------------------------

  public onAuthChanges(newAuthState: API_Model_Authorization_States) {
    this.authState = newAuthState;
  }
  //----------------------------------------------------------------------

  public getName() {
    return this.user.getEmail();
  }
  //----------------------------------------------------------------------

  public logout() {
    this.logger.debug("logout", "");
    this.auth.logout(function () {
      this.logger.debug("logout", "")
      // called only on success
      this.logger.debug("logout", " successfully logged out")

      this.router.navigate(['index/login']);
    }.bind(this));
  }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------