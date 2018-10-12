/**
 * @file: app/app.router.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { ModuleWithProviders } from "@angular/core";
import { Routes, RouterModule } from '@angular/router';


import { PageIndexComponent } from './page-index/page-index.component';
import { PageNotFoundComponent } from "./page-not-found/page-not-found.component";
import { PageFeedComponent } from "./page-feed/page-feed.component";
import { PageChatComponent } from "./page-chat/page-chat.component"

import { 
    API_Guard_Authorization_Unauthorized,
    API_Guard_Authorization_Authorized,
    API_Guard_Authorization_Require,
} from './api/authorization/guard';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export const router: Routes = [
    { path: '404', component: PageNotFoundComponent },

    { path: 'index/:action', component: PageIndexComponent, canActivate: [API_Guard_Authorization_Unauthorized]},

    { path: 'feed', component: PageFeedComponent, canActivate: [API_Guard_Authorization_Authorized] },
    { path: 'chat', component: PageChatComponent, canActivate: [API_Guard_Authorization_Require] },


    { path: '', redirectTo: 'index/register', pathMatch: 'full'},
    { path:'**', redirectTo: '404' },
];
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export const routes: ModuleWithProviders = RouterModule.forRoot(router, {
     useHash: true, 
     //enableTracing: true 
});
//-----------------------------------------------------------------------------------------------------------------------------------------------------