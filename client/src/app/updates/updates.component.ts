import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../http-client';
import { environment } from '../../environments/environment';

interface IUpdates
{
  href: string,
  title: string,
  date: string
}

@Component({
  selector: 'app-updates',
  templateUrl: './updates.component.html',
  styleUrls: ['./updates.component.css'],
})

export class UpdatesComponent implements OnInit {
  updates: IUpdates[];


  constructor(private http: HttpClient) { 
  
  }

  ngOnInit() {
    this.getUpdates()
      .subscribe(updates => {
        try {
          this.updates = updates.json()
        } catch(error) {
          console.log(error);
        }
      });
  }

  getUpdates() {
    return this.http.get('welcome/updates');
  }
}