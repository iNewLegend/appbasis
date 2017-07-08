import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Router } from '@angular/router';

import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/'

import { environment } from '../environments/environment';

import 'rxjs/add/operator/map'
import 'rxjs/add/operator/catch';

export enum eAuthStates {  
  PREPARE,
  NONE,
  UNAUTHORIZED, 
  AUTHORIZED
}

@Injectable()

export class AuthService {
  public authState: BehaviorSubject<eAuthStates> = new BehaviorSubject<eAuthStates>(eAuthStates.NONE);

  constructor(private http: Http, private router: Router) {

  }

  getState(): eAuthStates {
    return this.authState.getValue();
  }

  getHash(): string {
    let hash = localStorage.getItem('hash');

    if (hash == null) return '';

    return hash;
  }

  getStatus(): boolean {
    if (this.getState() == eAuthStates.AUTHORIZED)
      return true;

    return false;
  }

  protected setState(state: eAuthStates) {
    this.authState.next(state);
  }

  protected setHash(hash: string) {
    localStorage.setItem('hash', hash);
  }

  protected setStatus(status: boolean) {
    status ? this.setState(eAuthStates.AUTHORIZED) : this.setState(eAuthStates.UNAUTHORIZED);
  }


  public try(): Observable<Boolean> {
    if(this.getStatus()) {
      return Observable.of(true);
    }
  
    let hash = this.getHash();

    console.log("[auth.service.ts::try]: " + hash);

    this.setState(eAuthStates.PREPARE)

    return this.http.get(environment.server_base + 'authorization/check/' + hash)
      .map((response: Response) => {
        console.log("[auth.service.ts::try:recv]->");
        console.log(response);

        let data;
        try {
          data = response.json();
        } catch (error) {
          data = false;
        }

        if (data) {
          if (typeof data.code !== 'undefined') {
            if (data.code == 'success') {
              this.setStatus(true);
              console.log("[auth.service.ts::try:recv]: success");
              return true;
            }
            this.setHash('');
            this.setStatus(false);
          }
        }

        throw data;
      });
  }

  login(email: string, password: string, captcha: string, callback: (response: Response) => any) {
    let sendData = JSON.stringify({
      email: email,
      password: password,
      captcha: captcha
    });

    console.log("[auth.service.ts::login:send]->");
    console.log(sendData);

    return this.http.post(environment.server_base + 'authorization/login', sendData)
      .map((response: Response) => {
        console.log("[auth.service.ts::login:recv]->");
        console.log(response);

        let data;
        try {
          data = response.json();
        } catch (e) {
          data = false;
        }

        if (data && typeof data.hash !== 'undefined') {
          if (data.code == 'success') {
            if (data.hash.length == 40) {
              this.setHash(data.hash);
              this.setStatus(true);
            }
          }
        }

        callback(response);
      }).subscribe();
  }

  logout() {
    var hash = this.getHash();

    console.log("[auth.service.ts::logout:send]: " + hash);

    return this.http.get(environment.server_base + 'authorization/logout/' + hash)
      .map((response: Response) => {
        console.log("[auth.service.ts::logout:recv]->");
        console.log(response);

        let data = response.json();

        if (typeof data.code !== 'undefined') {
          console.log("[auth.service.ts::logout:recv:json]->");
          console.log(data);

          this.setHash('');
          this.setState(eAuthStates.UNAUTHORIZED);

          this.router.navigate(['welcome']);
        }
      })
  }
}
