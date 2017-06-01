import { Component, OnInit } from '@angular/core';
import { Response } from '@angular/http';
import { Router } from '@angular/router';

import { ToastrService } from 'ngx-toastr';

import { Validator } from '../validator';
import { environment } from '../../environments/environment';

import { RegisterService } from '../register.service';

interface IFormData {
  email: string;
  password: string;
  repassword: string;
  captcha: string;
}

const errorTimeout: number = 10000;

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})

export class RegisterComponent implements OnInit {
  error: boolean | string;
  timeout: any;

  captchaKey: string;
  repassword: string;
  formData: IFormData;

  constructor(private registerService: RegisterService,
   private toastrService: ToastrService,
   private router: Router) {
    this.captchaKey = environment.captcha_key;

    this.clearForm();
    this.clearErrors();
  }

  ngOnInit() {

  }

  clearErrors() {
    this.error = false;
  }

  clearForm() {
    
    this.formData = {
      email: '',
      password: '',
      repassword: '',
      captcha: ''
    }
    /*
    [REVIEW LATER]
    Sometimes anuglar is executed before recaptcha     
    */
    if (typeof grecaptcha !== 'undefined') {
      grecaptcha.reset();
    }
  }

  validateForm(): boolean {
    let frmData = this.formData;

    // validate email
    if (false == Validator.isEmail(frmData.email)) {
      this.error = "Please type valid email address";
      return false;
    }

    if (frmData.password.length <= 0) {
      this.error = "Please type password";
      return false;
    }

    if (frmData.password != frmData.repassword) {
      this.error = "the confrim password is not the same as password";
      return false;
    }

    if (frmData.captcha.length <= 0) {
      this.error = "Captcha is required";
      return false;
    }

    return true;
  }

  registerResult(response: Response) {
    let data;

    try {
      data = response.json();
    } catch(e) {
      data = false;
    }

    if(data) {
      console.log("[register.component.ts::loginResult] json->");
      console.log(data);

      switch(data.code)
      {
        case 'verify':
          this.error = "You have to verify that's your not a robot";
          grecaptcha.reset();
        break;
        
        case 'success':
          this.toastrService.success('you have been created account successfuly.', 'Success: ');
          this.router.navigateByUrl('welcome');
        break; 

        default:
         console.log(response);
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
      this.registerService.register(
        this.formData.email,
        this.formData.password,
        this.formData.captcha,
        this.registerResult.bind(this)
      )
    }

    // clear all errors after x time
    clearTimeout(this.timeout);

    this.timeout = setTimeout(() => {
      this.clearErrors();
    }, errorTimeout);
  }
}
