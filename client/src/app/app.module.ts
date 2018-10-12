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
import { MomentModule } from 'ngx-moment';

import { routes } from './app.router';
import { environment } from 'environments/environment';

import { AppComponent } from './app.component';
import { API_Module } from './api/module';

import { HeaderComponent } from './template/header/header.component';

import { RegisterFormComponent } from 'app/template/register-form/register-form.component';
import { LoginFormComponent } from 'app/template/login-form/login-form.component';

import { PageIndexComponent } from './page-index/page-index.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { PageFeedComponent } from './page-feed/page-feed.component';

import { UpdatesComponent } from './template/updates/updates.component';
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChatMessageComponent } from './template/chat/chat-message/chat-message.component';
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
    PageFeedComponent,
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
    API_Module.forRoot(),
  ],
  providers: [
  ],
  bootstrap: [AppComponent]
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class AppModule { }
//-----------------------------------------------------------------------------------------------------------------------------------------------------