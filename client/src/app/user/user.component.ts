import { Component, OnInit } from '@angular/core';
//import { Api } from '../api/api';

@Component({
  selector: 'app-user',
  templateUrl: './user.component.html',
  styleUrls: ['./user.component.css']
})
export class UserComponent implements OnInit {

  constructor(/*private api: Api*/) {
  }

  public test(response: Response) {
    let data;

    try {
      data = response.json();
    } catch(e) {
      data = false;
    }

    console.log(data);
  }

  ngOnInit() {
    //this.api.user().index(this.test);
  }

}
