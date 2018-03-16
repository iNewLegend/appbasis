/**
 * @file: app/login/login.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo: avoid console.log
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Logger } from "../logger";
import { environment } from "../../environments/environment";
import { Component, OnInit } from "@angular/core";
import { ToastrService } from "ngx-toastr";
import { Validator } from "../validator";
import { API_Service } from "app/api/service";
import { API_Service_Authorization } from "../api/authorization/service";
import { API_Request_Authorization } from "../api/authorization/request";
import {
    API_Model_Authorization_Send,
    API_Model_Authorization_Recv,
    API_Model_Authorization_States
} from "../api/authorization/model";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

const errorTimeout: number = 10000;
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
    selector: "app-login",
    templateUrl: "./login.component.html",
    styleUrls: ["./login.component.css"],
    providers: []
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class LoginComponent implements OnInit {
    //----------------------------------------------------------------------
    private logger: Logger;
    private timeout: any;
    private error: boolean | string;

    private formData: API_Model_Authorization_Send;
    private captchaState: boolean;

    private captchaKey: string;
    //----------------------------------------------------------------------

    constructor(
        private auth: API_Service_Authorization,
        private api: API_Service,
        private toastr: ToastrService) {
        // ----
        this.logger = new Logger("LoginComponent");
        this.logger.debug("constructor", "");

        this.error = false;
        this.captchaState = false;

        this.captchaKey = environment.captcha_key;

        this.formData = {
            email: "",
            password: "",
            captcha: ""
        }

        // Listen to changes in api
        api.authState$.subscribe((newAuthState: API_Model_Authorization_States) => this.onAuthChanges(newAuthState));
    }
    //----------------------------------------------------------------------

    private onAuthChanges(newAuthState: API_Model_Authorization_States) {
        if (newAuthState == API_Model_Authorization_States.VERIFY) {

            this.captchaState = true;
            this.error = "Please confirm your not a robot.";
        }

    }
    //----------------------------------------------------------------------

    public ngOnInit() {
    }
    //----------------------------------------------------------------------

    private clearErrors() {
        this.error = false;
    }
    //----------------------------------------------------------------------

    private validateForm(): boolean {
        var frmData = this.formData;

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
    //----------------------------------------------------------------------

    private loginResult(result: API_Model_Authorization_Recv) {
        this.logger.startWith("loginResult", result);

        switch (result.code) {
            case "success":
                this.toastr.success("You have been successfully logged in.", "Welcome");
                break;

            case "mail":
                switch (result.subcode) {
                    case 'short':
                        this.error = "Wrong username or password.";
                        break;

                    default:
                        this.error ="You have entered invalid email address."
                        break;
                }
                break;

            

            case "block":
                switch (result.subcode) {
                    case "verify":
                        grecaptcha.reset();

                        this.captchaState = true;
                        this.error = "Please confirm your not a robot.";
                        break;
                    
                    default:
                        //this.toastr.error("You have been blocked", "Error");
                        this.error = "You have been blocked.";
                }
                break;

            case "wrong":
                this.error = "Wrong username or password.";
                break;
        }
    }
    //----------------------------------------------------------------------

    private processForm() {
        if (this.validateForm()) {
            this.auth.login(this.formData, this.loginResult.bind(this));
        }
        // # clear all errors after x time
        // ----
        clearTimeout(this.timeout);
        // ----
        this.timeout = setTimeout(() => {
            this.clearErrors();
        }, errorTimeout);
    }
    //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------