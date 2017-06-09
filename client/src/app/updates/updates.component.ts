import { Component, OnInit } from '@angular/core';
import { Http, Response } from '@angular/http';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-updates',
  templateUrl: './updates.component.html',
  styleUrls: ['./updates.component.css']
})

export class UpdatesComponent implements OnInit {
  updates: IUpdates[];

  constructor(private http: Http) { 
  
  }

  ngOnInit() {
    this.getUpdates()
      .subscribe(updates => this.updates = updates.json());
  }

  getUpdates() {
    return this.http.get(environment.server_base + 'welcome/updates');
  }
}

interface IUpdates
{
  href: string,
  title: string,
  date: string
}