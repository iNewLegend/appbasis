import { Component, ViewChild, OnInit, ViewEncapsulation } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';

import { ToastContainerDirective, ToastrService, ToastrConfig } from 'ngx-toastr';

import { AuthGuard } from './auth.guard';
import { AuthService, eAuthStates } from './auth.service';


@Component({
  encapsulation: ViewEncapsulation.None,
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})

export class AppComponent implements OnInit {
  currentRoute: string;

  isWelcomeRoute: boolean;

  @ViewChild(ToastContainerDirective) toastContainer: ToastContainerDirective;

  constructor(
    private authGuard: AuthGuard,
    private authService: AuthService,
    private toastrService: ToastrService,
    private toastrConfig: ToastrConfig,
    private router: Router) {


    // route
    this.currentRoute = this.router.url;

    this.router.events.subscribe((res) => {
      if (res instanceof NavigationEnd) {
        this.onRouteChanged(this.router.url);
      }
    });

    this.router.events.subscribe();

    // toastr configure
    toastrConfig.positionClass = 'position';
    // move to bootstrap style
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

  ngOnInit() {
    this.toastrService.overlayContainer = this.toastContainer;
  }

  onRouteChanged(url: string) {
    if (this.authService.getState() == eAuthStates.NONE) {
      this.authService.try().subscribe();
    }

    this.currentRoute = this.router.url;

    switch (this.currentRoute) {
      case '/welcome':
        this.isWelcomeRoute = true;
        break;

      default:
        this.isWelcomeRoute = false;
    }
  }

  getAuthState() {
    return this.authService.getState();
  }

  getAuthStatus() {
    return this.authService.getStatus();
  }
}
