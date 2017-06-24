import { Injectable } from '@angular/core';
import { HttpClient } from './http-client';
import { environment } from '../environments/environment';


@Injectable()
export class RegisterService {

  constructor(private http: HttpClient) { }

  register(email: string, password: string, captcha: string, callback) {

    let sendData = JSON.stringify({
      email: email,
      password: password,
      captcha: captcha
    });

    console.log("[auth.register.ts::register]-> " + sendData);

    return this.http.post('register/register', sendData, callback);
  }
}
