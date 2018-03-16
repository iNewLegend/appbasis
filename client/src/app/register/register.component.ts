/**
 * @file: app/app.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 * @description: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component,  OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';

import { environment } from '../../environments/environment';
import { Logger } from '../logger'
import { Validator } from '../validator';

import { API_Service } from '../api/service';
import { API_Request_Authorization } from '../api/authorization/request';
import { API_Authorization_Register_Send } from '../api/authorization/model';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

const errorTimeout: number = 10000;
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: 'app-register',
    templateUrl: './register.component.html',
    styleUrls: ['./register.component.css'],
    providers: []
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class RegisterComponent implements OnInit {
    //----------------------------------------------------------------------
    private logger: Logger;
    private error: boolean | string;
    private timeout: any;

    private captchaKey: string;
    private repassword: string;
 
    private formData: API_Authorization_Register_Send;    
    //----------------------------------------------------------------------

    constructor(
        private toastrService: ToastrService,
        private router: Router,
        private authRequest: API_Request_Authorization) {
        // ----
        this.logger = new Logger("RegisterComponent");
        this.logger.debug("constructor", "");
        
        // ----
        this.captchaKey = environment.captcha_key;
        
        this.clearForm();
        this.clearErrors();
    }
    //----------------------------------------------------------------------

    public ngOnInit() {

    }
    //----------------------------------------------------------------------

    private clearErrors() {
        this.error = false;
    }
    //----------------------------------------------------------------------

    private clearForm() {

        this.formData = {
            email: '',
            password: '',
            repassword: '',
            captcha: ''
        }

        /*
        CHECK IT LATER:
        Sometimes angular is executed before recaptcha     
        - should i leave it like that ?
        */

        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
    }
    //----------------------------------------------------------------------

    private validateForm(): boolean {
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
    //----------------------------------------------------------------------

    private registerResult(data: any) {
        this.logger.startWith("registerResult", data);

        if (data) {
            switch (data.code) {
                case "block":
                switch (data.subcode) {
                    case "verify":
                        grecaptcha.reset();

                        this.error = "Please confirm your not a robot.";
                        break;
                    
                    default:
                        this.error = "You have been blocked.";
                }
                break;

                case "badpass":
                switch (data.subcode) {
                    case "weak":
                        this.error ="The password is too weak.";
                        break;
                    
                        default:
                            this.error = "Bad password";
                }
                break;

                case 'verify':
                    this.error = "You have to verify that's your not a robot";
                    grecaptcha.reset();
                    break;
                
                case 'emailtaken':
                    this.error = "Your email address already exist in our database.";
                    //grecaptcha.reset();
                    break;

                case 'success':
                    this.toastrService.success('you have been created account successfuly.', 'Success: ');
                    this.router.navigateByUrl('welcome');
                    break;

                    
                default:
                    //this.logger.alert
                    this.logger.debug("registerResult", " unknown code `" + data.code + "`");
            }
        }
    }
    //----------------------------------------------------------------------

    private processForm() {
        if (this.validateForm()) {
            this.authRequest.register(this.formData ,this.registerResult.bind(this))
        }

        // clear all errors after x time
        clearTimeout(this.timeout);

        this.timeout = setTimeout(() => {
            this.clearErrors();
        }, errorTimeout);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------