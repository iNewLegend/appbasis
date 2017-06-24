import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs/Observable';

import { eAuthStates, AuthService } from './auth.service';
import 'rxjs/add/observable/of';


@Injectable()
export class AuthGuard implements CanActivate {
  public canActiveState: boolean;
  

  constructor(private authService: AuthService,
    private router: Router) {
    this.canActiveState = false;


  }

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    this.canActiveState = true;

    let authState: eAuthStates = this.authService.getState();

    if(authState == eAuthStates.NONE) {
      return this.authService.try().catch((err) => {
        console.log(err);
        this.router.navigate(['/welcome']);
        return Observable.of(false);
      });
    } 
    
    if(authState == eAuthStates.AUTHORIZED) {
      return true;
    }

    return false;
  }
}
