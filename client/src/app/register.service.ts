import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { environment } from '../environments/environment';


@Injectable()
export class RegisterService {

  constructor(private http: Http) { }

  register(email: string, password: string, captcha: string, callback: (response: Response) => any) {

    let sendData = JSON.stringify({
      email: email,
      password: password,
      captcha: captcha
    });

    console.log("[auth.register.ts::register]-> " + sendData);

    return this.http.post(environment.server_base + 'register/register', sendData)
      .map((response: Response) => {
        console.log(response);

        callback(response);
      }).subscribe();
  }
}
