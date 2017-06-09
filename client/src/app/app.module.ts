import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule , RequestMethod, RequestOptions, Headers} from '@angular/http';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';


import { RecaptchaModule } from 'ng-recaptcha';
import { RecaptchaFormsModule } from 'ng-recaptcha/forms';
import { ToastrModule , ToastContainerModule } from 'ngx-toastr';

import { AuthService } from './auth.service';
import { RegisterService } from './register.service';

import { routes } from './app.router';

import { AppComponent } from './app.component';
import { NavbarComponent } from './navbar/navbar.component';
import { UpdatesComponent } from './updates/updates.component';
import { WelcomeComponent } from './welcome/welcome.component';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login.component';

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    UpdatesComponent,
    WelcomeComponent,
    RegisterComponent,
    LoginComponent
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
    ToastContainerModule.forRoot()
  ],
  providers: [
    AuthService,
    RegisterService

  ],
  bootstrap: [AppComponent]
})

export class AppModule { }

