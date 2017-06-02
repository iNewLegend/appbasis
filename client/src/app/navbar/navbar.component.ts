import { Component, OnInit } from '@angular/core';
import { AuthService } from '../auth.service';

import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css']
})
export class NavbarComponent implements OnInit {
  navbarCollapse: boolean;
  dropDown: boolean;

  constructor(private authService: AuthService, private toastrService: ToastrService) { 
    this.navbarCollapse = false;
    this.dropDown = false;
  }

  ngOnInit() {
  }

  logout() {
    this.authService.logout().subscribe(success => {
      this.toastrService.info('you have been successfully logged out.', 'Thank you');
    })
  }

  getAuthStatus() {
    return this.authService.getStatus();
  }
}
