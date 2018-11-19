import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Constants } from '../../app/app-constants';

@Injectable({
  providedIn: 'root'
})
export class StatusService {

  constants: any = Constants;

  constructor(private http: HttpClient) { }

  status() {
    return this.http.get(`${this.constants.api}/status`);
  }

  save(data) {
    return this.http.post(`${this.constants.api}/status`, data);
  }

  remove(id) {
    return this.http.delete(`${this.constants.api}/status/${id}`);
  }

  edit(data) {
    return this.http.put(`${this.constants.api}/status`, data);
  }
}
