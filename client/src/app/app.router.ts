import { ModuleWithProviders } from "@angular/core";
import { Routes, RouterModule } from '@angular/router';

import { WelcomeComponent } from './welcome/welcome.component';
import { RegisterComponent } from './register/register.component';
import { UserComponent } from './user/user.component';

import { AuthGuard } from './auth.guard';

export const router: Routes = [
    { path: '', redirectTo: 'welcome', pathMatch: 'full'},
    
    { path: 'welcome', component: WelcomeComponent },
    { path: 'register', component: RegisterComponent },
    { path: 'user', component: UserComponent, canActivate: [AuthGuard]},

    { path:'**', redirectTo: 'welcome' },
];

export const routes: ModuleWithProviders = RouterModule.forRoot(router);
