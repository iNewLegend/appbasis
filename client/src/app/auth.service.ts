import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { environment } from '../environments/environment';

import 'rxjs/add/operator/map'
import { BehaviorSubject } from 'rxjs/BehaviorSubject';

enum eAuthStates {
  NONE,
  UNAUTHORIZED,
  AUTHORIZED
}

@Injectable()

export class AuthService {
  public authState: BehaviorSubject<eAuthStates> = new BehaviorSubject<eAuthStates>(eAuthStates.NONE);


  constructor(private http: Http) {
    this.try();
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
    if (true === status) {
      this.setState(eAuthStates.AUTHORIZED);
    } else {
      this.setState(eAuthStates.UNAUTHORIZED);
    }
  }


  try() {
    let hash = this.getHash();

    console.log("[auth.service.ts::try] -> " + hash);

    this.http.get(environment.server_base + 'authorization/index/' + hash)
      .map((response: Response) => {
        let data = response.json();

        this.setStatus(data.status);
        console.log("[auth.service.ts::try] result -> " + data.status);
      }).subscribe(success => {
      }, error => {
        console.log("[auth.service.ts::try] -> http -> error");
        console.log(error);
      });
  }

  login(email: string, password: string, captcha: string, callback: (response: Response) => any) {
    let sendData = JSON.stringify({
      email: email,
      password: password,
      captcha: captcha
    });

    console.log("[auth.service.ts::login]-> " + sendData);

    return this.http.post(environment.server_base + 'authorization/login', sendData)
      .map((response: Response) => {
        console.log(response);

        /*
        REVIEW: Code repeated in backgrond (nonsense) 
        */
        let data;
        try {
          data = response.json();
        } catch (e) {
          data = false;
        }

        if (data && typeof data.hash !== 'undefined') {
          // user successfuly authorized
          this.setHash(data.hash);
          this.setStatus(true);
        }

        callback(response);
      }).subscribe();
  }

  logout() {
    return this.http.get(environment.server_base + 'authorization/logout/' + this.getHash())
      .map((response: Response) => {
        let data = response.json();

        if (data.code !== undefined && data.code == 'success') {
          this.setHash('');
          this.setState(eAuthStates.UNAUTHORIZED);
        }
      })
  }
}
