import { enableProdMode } from '@angular/core';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import { Http, Response } from '@angular/http';

import { AppModule } from './app/app.module';
import { environment } from './environments/environment';


if (environment.production) {
  enableProdMode();
}


var xhttp = new XMLHttpRequest();

xhttp.onreadystatechange = function () {
  if (this.readyState == 4) {
    if (this.status == 200) {
      platformBrowserDynamic().bootstrapModule(AppModule);
    } else {
      let out = "cannot connect to API Server: `" + environment.server_base + '`';

      document.getElementsByClassName('container')[0].innerHTML = "<pre>" + out + "</pre>";
    }
  }
};

xhttp.open("GET", environment.server_base, true);
xhttp.send();


