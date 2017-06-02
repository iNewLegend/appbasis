import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { environment } from '../environments/environment';

import 'rxjs/add/operator/map'
import {BehaviorSubject} from 'rxjs/BehaviorSubject';

@Injectable()

export class AuthService {
  public authState:BehaviorSubject<boolean> = new BehaviorSubject<boolean>(false);
 
  constructor(private http: Http) {
    this.try();
  }

  getState() : boolean {
    return this.authState.getValue();
  }

  getHash() : string {
    let hash = localStorage.getItem('hash');
    
    if(hash == null) return '';
    
    return hash;
  }
  
  protected setState(state: boolean) {
    this.authState.next(state);
  }

  protected setHash(hash : string) {
    localStorage.setItem('hash', hash);
  }

  
  try() {
    let hash = this.getHash();
    
    if(typeof hash == 'undefined') return;

    if(this.getHash().length == 40) {
      this.setState(true);

      this.http.get(environment.server_base + 'authorization/index/' + hash)
        .map((response: Response) => {
          let data = response.json();

          this.setState(data.status);
        }).subscribe();
    }
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
          this.setHash(data.hash);
          this.setState(true);
        }

        callback(response);
      }).subscribe();
  }

  logout() {
    this.http.get(environment.server_base + 'authorization/logout/' + this.getHash())
      .map((response: Response) => {
        let data = response.json();

        if (data.code !== undefined && data.code == 'success') {
          this.setHash('');
          this.setState(false);
        }
      }).subscribe();
  }
}
