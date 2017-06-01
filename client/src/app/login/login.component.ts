import { Component, OnInit } from '@angular/core';
import { Response } from '@angular/http'

import { Validator } from '../validator';
import { AuthService } from '../auth.service';
import { ToastrService} from 'ngx-toastr';


import { environment } from '../../environments/environment';

interface IFormData {
  email: string;
  password: string;
  captcha: string;
}

const errorTimeout: number = 10000;

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})

export class LoginComponent implements OnInit {
  error: boolean | string;
  captchaState: boolean;
  formData: IFormData;
  timeout: any;
  captchaKey: string;

  constructor(private authService: AuthService, private toastrService: ToastrService) {
    this.error = false;
    this.captchaState = false;

    this.captchaKey = environment.captcha_key;

    this.formData = {
      email: '',
      password: '',
      captcha: ''
    }
  }

  ngOnInit() {
  }

  clearErrors() {
    this.error = false;
  }

  validateForm(): boolean {
    var frmData = this.formData;

    // validate email
    if (false == Validator.isEmail(frmData.email)) {
      this.error = "Please type valid email address";
      return false;
    }

    if (frmData.password.length <= 0) {
      this.error = "Please type password";
      return false;
    }

    if (true == this.captchaState) {
      if (frmData.captcha.length <= 0) {
        this.error = "Captcha is required";
        return false;
      }
    }

    this.clearErrors();

    return true;
  }

  loginResult(response: Response) {
    let data;

    try {
      data = response.json();
    } catch(e) {
      data = false;
    }

    if(data) {
      console.log("[login.component.ts::loginResult] json->");
      console.log(data);

      if (typeof data.code !== 'undefined' && data.code == 'verify') {
        grecaptcha.reset();

        this.captchaState = true;
        this.error = "Please confirm your not a robot";
      } else if(data.hash.length == 40) {
        this.toastrService.success("You have been successfuly logged in.", "Welcome");
      } else {
        console.log("Error: in lgin.comonent.ts unknown logic.")
      }

    } else if (response.text().length > 0) {
      console.log("[login.component.ts::loginResult] text-> " + response.text());
      this.error = response.text();
    } else {
      console.log(response);
    }
  }

  processForm() {
    if (this.validateForm()) {
      this.authService.login(
        this.formData.email,
        this.formData.password,
        this.formData.captcha,
        this.loginResult.bind(this)
      )
    }

    // clear all errors after x time
    clearTimeout(this.timeout);

    this.timeout = setTimeout(() => {
      this.clearErrors();
    }, errorTimeout);
  }

  captchaResolved($event) {
    
  }
}

