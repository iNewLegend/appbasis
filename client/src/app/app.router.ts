import { ModuleWithProviders } from "@angular/core";
import { Routes, RouterModule } from '@angular/router';

import { WelcomeComponent } from './welcome/welcome.component';
import { RegisterComponent } from './register/register.component';

export const router: Routes = [
    { path: '', redirectTo: 'welcome', pathMatch: 'full'},
    
    { path: 'welcome', component: WelcomeComponent },
    { path: 'register', component: RegisterComponent },

    { path:'**', redirectTo: 'welcome' },
];

export const routes: ModuleWithProviders = RouterModule.forRoot(router);
