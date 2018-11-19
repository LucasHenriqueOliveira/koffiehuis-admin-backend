import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Constants } from '../../app/app-constants';

@Injectable({
  providedIn: 'root'
})
export class UserService {

  constants: any = Constants;

  constructor(private http: HttpClient) { }

  signup(data) {
    return this.http.post(`${this.constants.api}/signup`, data);
  }

  login(data) {
    return this.http.post(`${this.constants.api}/login`, data);
  }

  sendPasswordResetLink(data) {
    return this.http.post(`${this.constants.api}/sendPasswordResetLink`, data);
  }

  changePassword(data) {
    return this.http.post(`${this.constants.api}/resetPassword`, data);
  }

  getUsers() {
    return this.http.get(`${this.constants.api}/user`);
  }
}
