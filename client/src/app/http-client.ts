import { Injectable } from '@angular/core';
import { Http, Response , RequestOptions, Headers} from '@angular/http';
import { Router } from '@angular/router';

import { AuthService } from './auth.service';
import { AuthGuard } from './auth.guard';

import { environment } from '../environments/environment';

import 'rxjs/add/operator/map'

/**
 * @todo: for active authGuard add token hash sending.
 */

@Injectable()

export class HttpClient {
  headers: Headers;
  
  constructor(private http: Http,
   private router: Router,
   private authGuard: AuthGuard,
   private authService: AuthService) {
    this.headers = new Headers();
  }
  
  get(url, callback = null) {

    if(this.authGuard.canActiveState) {
      this.headers.set('hash', this.authService.getHash());
    }

    if(callback) {
      this.http.get(environment.server_base + url, {headers : this.headers}).map((response: Response) => {
        console.log(response);

        callback(response);
      }).subscribe();
    }

    return this.http.get(environment.server_base + url, {headers : this.headers});
  }

  post(url, data, callback = null) {
    if(this.authGuard.canActiveState) {
      this.headers.set('hash', this.authService.getHash());
    }    

    if(callback) {
      return this.http.post(environment.server_base + url, data, {headers: this.headers}).map((response: Response) => {
        console.log(response);

        callback(response);
      }).subscribe();  
    }
    
    this.http.post(environment.server_base + url, data, {headers: this.headers});
  }
}