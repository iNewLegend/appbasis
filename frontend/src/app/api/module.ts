/**
 * @file: app/api/module.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description:
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { NgModule } from '@angular/core';
import { ModuleWithProviders } from '@angular/core';

import { API_Service } from './service'
import { API_Client_Http } from './clients/http';

import { API_Request_Authorization } from './authorization/request';
import { API_Service_Authorization } from './authorization/service';
import { API_Service_User } from './user/service';
import { API_Request_User } from './user/request';

import { 
  API_Guard_Authorization_Unauthorized,
  API_Guard_Authorization_Authorized, 
  API_Guard_Authorization_Require,
} from './authorization/guard';
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@NgModule({
  imports: [
  ],
  declarations: [
  ],
  providers: [
  ],
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class API_Module {
  static forRoot(): ModuleWithProviders {
    return {
      ngModule: API_Module,
      providers: [
        API_Service,
        API_Client_Http,
        ,

        API_Service_Authorization,
        API_Guard_Authorization_Unauthorized,
        API_Guard_Authorization_Authorized,
        API_Guard_Authorization_Require,
        API_Request_Authorization,

        API_Service_User,
        API_Request_User,
        
      ]
    }
  }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------