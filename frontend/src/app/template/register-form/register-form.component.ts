/**
 * @file: app/app.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 * @description: 
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, ViewChild, OnInit, ElementRef } from '@angular/core';
import { Router } from '@angular/router';

import { InvisibleReCaptchaComponent } from 'ngx-captcha';

import { Logger } from '../../logger'
import { Validator } from '../../validator';

import { API_Request_Authorization } from '../../api/authorization/request';
import { API_Authorization_Register_Send, API_Model_Authorization_States } from '../../api/authorization/model';
import { API_Service } from '../../api/service';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

const C_NoticeTime: number = 10000;

enum E_NoticeTypes
{
    E_NOTICE_TYPE_NULL,
    E_NOTICE_TYPE_MSG,
    E_NOTICE_TYPE_ERROR
};
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
  selector: 'app-register-form',
  templateUrl: './register-form.component.html',
  styleUrls: ['./register-form.component.css']
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class RegisterFormComponent implements OnInit {
    //----------------------------------------------------------------------

    private logger: Logger;
    private formData: API_Authorization_Register_Send;    

    public noticeType: string;
    public noticeMsg: string;
    private noticeTimeout: any;
    //----------------------------------------------------------------------

    @ViewChild('captchaElem') captchaElem: InvisibleReCaptchaComponent;
    //----------------------------------------------------------------------

    @ViewChild('formElem') formElem: ElementRef;
    //----------------------------------------------------------------------

    constructor(
        private apiAuthRequest: API_Request_Authorization,
        private router: Router,
        private api: API_Service) {
        // ----
        this.logger = new Logger("SignUpFormComponent");
        this.logger.debug("constructor", "");
        // ----
        this.formData = {
            email: '',
            password: '',
            repassword: '',
            firstName: '',
            lastName: '',
            captcha: ''
        }

        this.clearNotice();
    }
    //----------------------------------------------------------------------

    public ngOnInit() {

    }
    //----------------------------------------------------------------------

    private validateForm(): boolean {
        let frmData = this.formData;

        /* validate first_name and last_name */
        if (frmData.firstName.length <= 0) {
            this.setNotice('info', "Please type first name");
            return false;
        }

        if (frmData.lastName.length <= 0) {
            this.setNotice('info', "Please type last Name");
            return false;
        }

        // validate email
        if (false == Validator.isEmail(frmData.email)) {
            this.setNotice('info', "Please type valid email address");
            return false;
        }

        if (frmData.password.length <= 0) {
            this.setNotice('info', "Please type password");
            return false;
        }

        if (frmData.password != frmData.repassword) {
            this.setNotice('info', "the confirm password is not the same as password");
            return false;
        }

        if (this.api.getAuthState() == API_Model_Authorization_States.VERIFY && ! this.formData.captcha.length) {
            this.logger.debug("validateFrom", " captcha is empty, requesting captcha.")
            this.captchaElem.execute();

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
                        this.setNotice('info', "Please Confirm you are not robot!, Checking ...");
                        
                        setTimeout(() => {
                            if (this.formData.captcha.length > 0) {
                                this.captchaElem.resetCaptcha();
                            }
                            
                            this.captchaElem.execute();
                        }, 1000);
                        break;
                    
                    default:
                    this.setNotice('danger', "You have been blocked due too many attempts");
                }
                break;

                case "badpass":
                switch (data.subcode) {
                    case "weak":
                        this.setNotice('danger', "The password is too weak");
                        break;
                    
                        default:
                        this.setNotice('danger', "Bad password O_o");
                }
                break;

                case 'mail':
                switch (data.subcode) {
                    case "short":
                    this.setNotice('danger', "The email is too short.");
                    break;

                    default:
                    this.setNotice('danger', "Invalid email");
                }
                
                break;
                
                case 'emailtaken':
                    this.setNotice('danger', "Your email address already exist in our database.");
                    break;

                case 'success':
                    this.setNotice('success', "you have been created account successfully.");
                    //this.toastr.success('you have been created account successfully.', 'Success: ');
                    setTimeout(() => {
                        this.router.navigate(['index', 'login']);
                    }, 1000);
                    break;

                    
                default:
                    this.logger.debug("registerResult", " unknown code `" + data.code + "`");
            }
        }
    }
    //----------------------------------------------------------------------

    private handleCaptcha(handle, event = null)  {
        this.logger.startWith('handleCaptcha', { handle: handle, event: event });
    
        switch (handle) {
            case 'success':
            {
                this.formData.captcha = event;

                this.processForm();
            }
            break;
        }
    }
    //----------------------------------------------------------------------    

    private processForm() {
        this.logger.debug("processForm", "");
        // clear all errors before send
        this.clearNotice();

        if (this.validateForm()) {
            this.apiAuthRequest.register(this.formData ,this.registerResult.bind(this))
        }

        // clear all errors after x time
        this.clearNoticeTimeout();

        this.noticeTimeout = setTimeout(() => {
            this.clearNotice();
        }, C_NoticeTime);
    }
    //----------------------------------------------------------------------

    private setNotice(type: string, message: string) {
        this.noticeType = type;
        this.noticeMsg = message;
    }
    //----------------------------------------------------------------------

    private clearNotice() {
        this.noticeType = null;
    }
    //----------------------------------------------------------------------

    private clearNoticeTimeout() {
        clearTimeout(this.noticeTimeout);
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------