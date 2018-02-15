/**
 * @file: app/app.module.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';

import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { RecaptchaModule } from 'ng-recaptcha';
import { RecaptchaFormsModule } from 'ng-recaptcha/forms';

import { ToastrModule, ToastContainerModule } from 'ngx-toastr';

import { AppComponent } from './app.component';
import { NavbarComponent } from './navbar/navbar.component';
import { UpdatesComponent } from './updates/updates.component';
import { WelcomeComponent } from './welcome/welcome.component';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login.component';
  import { UserComponent } from './user/user.component';
import { API_Module } from './api/module';
import { routes } from './app.router';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    UpdatesComponent,
    WelcomeComponent,
    RegisterComponent,
    LoginComponent,
    UserComponent
  ],
  imports: [
    routes,
    HttpModule,
    BrowserModule,
    BrowserAnimationsModule,
    FormsModule,
    RecaptchaModule.forRoot(),
    RecaptchaFormsModule,
    ToastrModule.forRoot(),
    ToastContainerModule.forRoot(),
    API_Module.forRoot(),
  ],
  providers: [
  ],
  bootstrap: [AppComponent]
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class AppModule { }
//-----------------------------------------------------------------------------------------------------------------------------------------------------