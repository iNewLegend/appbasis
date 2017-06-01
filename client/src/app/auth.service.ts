import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { environment } from '../environments/environment';

import 'rxjs/add/operator/map'
import {BehaviorSubject} from 'rxjs/BehaviorSubject';

@Injectable()

export class AuthService {
  public authState:BehaviorSubject<boolean> = new BehaviorSubject<boolean>(false);
  protected hash: string;

  constructor(private http: Http) {
    this.hash = '';
  }

  getState() : boolean {
    return this.authState.getValue();
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
        } catch(e) {
          data = false;
        }

        if(data && typeof data.hash !== 'undefined') {
          // user successfuly authorized
          this.hash = data.hash;
          this.authState.next(true);
        }

        callback(response);
      }).subscribe();
  }
}
