/**
 * @file: app/app.router.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { ModuleWithProviders } from "@angular/core";
import { Routes, RouterModule } from '@angular/router';
import { WelcomeComponent } from './welcome/welcome.component';
import { RegisterComponent } from './register/register.component';
import { UserComponent } from './user/user.component';
import { API_Guard_Authorization } from './api/authorization/guard';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export const router: Routes = [
    { path: '', redirectTo: 'welcome', pathMatch: 'full'},
    
    { path: 'welcome', component: WelcomeComponent },
    { path: 'register', component: RegisterComponent },
    { path: 'user', component: UserComponent, canActivate: [API_Guard_Authorization]},

    { path:'**', redirectTo: 'welcome' },
];
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export const routes: ModuleWithProviders = RouterModule.forRoot(router);
//-----------------------------------------------------------------------------------------------------------------------------------------------------