/**
 * @file: app/template/updates/updates.component.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

import { Component, OnInit } from "@angular/core";
import { ActivatedRoute } from "@angular/router";

import { API_Model_Welcome_Updates } from "app/api/welcome/model"

import { Logger } from 'app/logger';
import { API_Request_Welcome } from "../../api/welcome/request";
//-----------------------------------------------------------------------------------------------------------------------------------------------------

@Component({
  selector: 'app-updates',
  templateUrl: './updates.component.html',
  styleUrls: ['./updates.component.css'],
  providers: [ API_Request_Welcome ]
})
//-----------------------------------------------------------------------------------------------------------------------------------------------------



export class UpdatesComponent implements OnInit {
  //----------------------------------------------------------------------
  private logger: Logger;

  public updates: API_Model_Welcome_Updates [];

  //---------------------------------------------------------------------------

  constructor(
    private welcome: API_Request_Welcome) {
    // ----
    this.logger = new Logger("UpdatesComponent");
    this.logger.debug("constructor", "");
    // ----
  }
  //---------------------------------------------------------------------------

  public ngOnInit() {
    this.welcome.updates((function (data) {
      this.updates = data;
    }.bind(this)))
  }
  //----------------------------------------------------------------------
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------