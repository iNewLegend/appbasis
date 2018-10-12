/**
 * @file: app/template/register-from/register-from.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Router } from "@angular/router";
import { Component, ViewChild, OnInit } from "@angular/core";

import { InvisibleReCaptchaComponent } from "ngx-captcha";

import { Logger } from "app/logger";
import { Validator } from "app/validator";


import { API_Service } from "app/api/service";
import { API_Service_Authorization } from "app/api/authorization/service";
import {
    API_Model_Authorization_Send,
    API_Model_Authorization_Recv,
    API_Model_Authorization_States
} from "app/api/authorization/model";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

const C_NoticeTime: number = 10000;
const C_DelayBeforeReroute = 1200;
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: "app-login-form",
    templateUrl: "./login-form.component.html",
    styleUrls: ["./login-form.component.css"],
    providers: []
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class LoginFormComponent implements OnInit {
    //----------------------------------------------------------------------
    private logger: Logger;

    public noticeType: string;
    public noticeMsg: string;
    private noticeTimeout: any;

    private formData: API_Model_Authorization_Send;
    //----------------------------------------------------------------------

    @ViewChild("captchaElem") captchaElem: InvisibleReCaptchaComponent;
    //----------------------------------------------------------------------

    constructor(
        private router: Router,
        private api: API_Service,
        private auth: API_Service_Authorization) {
        // ----
        this.logger = new Logger("LoginFormComponent");
        this.logger.debug("constructor", "");

        this.noticeType = null;

        this.formData = {
            email: "",
            password: "",
            captcha: ""
        }

        this.clearNotice();
    }
    //----------------------------------------------------------------------


    public ngOnInit() {

    }
    //----------------------------------------------------------------------

    private validateForm(): boolean {
        var frmData = this.formData;

        if (false == Validator.isEmail(frmData.email)) {
            this.setNotice("info", "Please type valid email address.");
            return false;
        }

        if (frmData.password.length <= 0) {
            this.setNotice("info", "Please type password.");
            return false;
        }

        if (this.api.getAuthState() == API_Model_Authorization_States.VERIFY && ! this.formData.captcha.length) {
            //this.captchaElem.resetCaptcha();
            this.logger.debug("validateFrom", " captcha is empty, requesting captcha")
            this.logger.debug("validateFrom", " this.captchaElem.execute();")
            this.captchaElem.execute();

            return false;
        }

        return true;
    }
    //----------------------------------------------------------------------

    private loginResult(data: API_Model_Authorization_Recv) {
        this.logger.startWith("loginResult", data);

        switch (data.code) {
            case "block":
            switch (data.subcode) {
                case "verify":
                    this.setNotice("info", "We are Confirm  that you are not robot!, Checking ...");
                    
                    setTimeout(() => {
                        if (this.formData.captcha.length > 0) {
                            this.captchaElem.resetCaptcha();
                            this.logger.debug("loginResul::timeOut", " this.captchaElem.resetCaptcha();")
                        }
                        
                        this.logger.debug("loginResult", " this.captchaElem.execute();")

                        this.captchaElem.execute();
                    }, 1000);

                    
                    break;
                
                default:
                this.setNotice("danger", "You have been blocked due too many attempts");
            }
            break;

            case "mail":
            switch (data.subcode) {
                case "short":
                this.setNotice("danger", "The email is too short.");
                break;

                default:
                this.setNotice("danger", "Invalid email.");
            }

            case "wrong":
                this.setNotice("danger", "Wrong username or password.");
                break;

            case "success":
                this.setNotice("success", "You have been successfully logged in.");

                this.noticeTimeout = setTimeout(() => {
                    this.router.navigate(['feed']);

                }, 400);

                break;
        }
    }
    //----------------------------------------------------------------------

    private handleCaptcha(handle, event = null)  {
        this.logger.startWith("handleCaptcha", { handle: handle, event: event });
    
        switch (handle) {
            case "success":
            {
                this.formData.captcha = event;

                this.setNotice("success", "We recognize you as human, please type valid infomration.");
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
            this.auth.login(this.formData, this.loginResult.bind(this));
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