import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Constants } from '../../app/app-constants';

@Injectable({
  providedIn: 'root'
})
export class UsoService {

  constants: any = Constants;

  constructor(private http: HttpClient) { }

  get() {
    return this.http.get(`${this.constants.api}/uso`);
  }

  save(data) {
    return this.http.post(`${this.constants.api}/uso`, data);
  }

  remove(id) {
    return this.http.delete(`${this.constants.api}/uso/${id}`);
  }

  edit(data) {
    return this.http.put(`${this.constants.api}/uso`, data);
  }
}
