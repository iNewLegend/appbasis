import { Component, OnInit } from '@angular/core';
// TEST
import { HttpClient } from '../http-client';

@Component({
  selector: 'app-user',
  templateUrl: './user.component.html',
  styleUrls: ['./user.component.css']
})
export class UserComponent implements OnInit {

  constructor(private http: HttpClient) {
  }

  test(response: Response) {
    let data;

    try {
      data = response.json();
    } catch(e) {
      data = false;
    }

    console.log(data);
  }

  ngOnInit() {
    this.http.get('user/index', this.test);
  }

}
