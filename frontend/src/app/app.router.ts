/**
 * @file: app/app.router.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { ModuleWithProviders } from "@angular/core";
import { Routes, RouterModule } from '@angular/router';


import { PageIndexComponent } from './pages/page-index/page-index.component';
import { PageNotFoundComponent } from "./pages/page-not-found/page-not-found.component";
import { PageChatComponent } from "./pages/page-chat/page-chat.component"

import { UpdatesComponent } from './iron/updates/updates.component';

import { 
    API_Guard_Authorization_Unauthorized,
    API_Guard_Authorization_Authorized,
    API_Guard_Authorization_Require,
} from './api/authorization/guard';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export const router: Routes = [
    { path: '404', component: PageNotFoundComponent },

    { path: 'index/:action', component: PageIndexComponent, canActivate: [API_Guard_Authorization_Unauthorized]},

    { path: 'home', component: UpdatesComponent, canActivate: [API_Guard_Authorization_Require] },
    { path: 'chat', component: PageChatComponent, canActivate: [API_Guard_Authorization_Require] },


    { path: '', redirectTo: 'index/register', pathMatch: 'full'},
    { path:'**', redirectTo: 'home' },
];
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export const routes: ModuleWithProviders = RouterModule.forRoot(router, {
     useHash: true, 
     //enableTracing: true 
});
//-----------------------------------------------------------------------------------------------------------------------------------------------------