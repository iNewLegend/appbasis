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

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgxCaptchaModule } from 'ngx-captcha';
import { NgxLoadingModule } from 'ngx-loading';

import { MomentModule } from 'ngx-moment';

import { routes } from './app.router';
import { environment } from 'environments/environment';

import { AppComponent } from './app.component';
import { API_Module } from './api/module';

import { HeaderComponent } from './header/header.component';

import { RegisterFormComponent } from 'app/iron/register-form/register-form.component';
import { LoginFormComponent } from 'app/iron/login-form/login-form.component';
import { UpdatesComponent } from 'app/iron/updates/updates.component';
import { ChatMessageComponent } from 'app/iron/chat/chat-message/chat-message.component';

import { PageIndexComponent } from './pages/page-index/page-index.component';
import { PageNotFoundComponent } from './pages/page-not-found/page-not-found.component';
import { PageChatComponent } from './pages/page-chat/page-chat.component';

//-----------------------------------------------------------------------------------------------------------------------------------------------------

@NgModule({
  declarations: [
    AppComponent,
    HeaderComponent,

    RegisterFormComponent,
    LoginFormComponent,
    UpdatesComponent,

    PageIndexComponent,
    PageNotFoundComponent,
    PageChatComponent,

    ChatMessageComponent,
    

  ],
  imports: [
    routes,
    HttpModule,
    BrowserModule,
    BrowserAnimationsModule,
    FormsModule,
    MomentModule,
    NgbModule,
    NgxCaptchaModule.forRoot({
      invisibleCaptchaSiteKey: environment.captcha_key
    }),
    NgxLoadingModule.forRoot({}),
    API_Module.forRoot(),
  ],
  providers: [
  ],
  bootstrap: [AppComponent]
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class AppModule { }
//-----------------------------------------------------------------------------------------------------------------------------------------------------