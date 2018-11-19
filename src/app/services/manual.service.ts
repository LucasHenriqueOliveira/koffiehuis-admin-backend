import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Constants } from '../../app/app-constants';

@Injectable({
  providedIn: 'root'
})
export class ManualService {

  constants: any = Constants;

  constructor(private http: HttpClient) { }

  save(data) {
    return this.http.post(`${this.constants.api}/manual`, data);
  }

  remove(id) {
    return this.http.delete(`${this.constants.api}/manual/${id}`);
  }

  edit(data) {
    return this.http.put(`${this.constants.api}/manual`, data);
  }

  items() {
    return this.http.get(`${this.constants.api}/manual`);
  }

  getOptions() {
    return this.http.get(`${this.constants.api}/manual-options`);
  }

  getItens() {
    return this.http.get(`${this.constants.api}/manual-item`);
  }

  saveLista(data) {
    return this.http.post(`${this.constants.api}/manual-item`, data);
  }

  removeItem(id) {
    return this.http.delete(`${this.constants.api}/manual-item/${id}`);
  }

  editItem(data) {
    return this.http.put(`${this.constants.api}/manual-item`, data);
  }
}
