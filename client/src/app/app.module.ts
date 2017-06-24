import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';

import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';


import { RecaptchaModule } from 'ng-recaptcha';
import { RecaptchaFormsModule } from 'ng-recaptcha/forms';
import { ToastrModule , ToastContainerModule } from 'ngx-toastr';

import { AppComponent } from './app.component';
import { NavbarComponent } from './navbar/navbar.component';
import { UpdatesComponent } from './updates/updates.component';
import { WelcomeComponent } from './welcome/welcome.component';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login.component';
import { UserComponent } from './user/user.component';

import { AuthService } from './auth.service';
import { AuthGuard } from './auth.guard';

import { HttpClient } from './http-client';
import { routes } from './app.router';

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
    BrowserModule,
    BrowserAnimationsModule,
    FormsModule,
    HttpModule,
    routes,
    RecaptchaModule.forRoot(),
    RecaptchaFormsModule,
    ToastrModule.forRoot(),
    ToastContainerModule.forRoot(),
  ],
  providers: [
    AuthService,
    AuthGuard,
    HttpClient,
  ],
  bootstrap: [AppComponent]
})

export class AppModule { }

