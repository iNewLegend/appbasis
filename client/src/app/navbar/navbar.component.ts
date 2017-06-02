import { Component, OnInit } from '@angular/core';
import { AuthService } from '../auth.service';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css']
})
export class NavbarComponent implements OnInit {
  navbarCollapse: boolean;
  dropDown: boolean;

  constructor(private authService: AuthService) { 
    this.navbarCollapse = false;
    this.dropDown = false;
  }

  ngOnInit() {
  }

  logout() {
    this.authService.logout();
  }

  getAuthState() {
    return this.authService.getState();
  }
}
