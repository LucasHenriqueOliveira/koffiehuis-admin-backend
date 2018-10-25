import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class StatusService {

  private baseUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) { }

  status() {
    return this.http.get(`${this.baseUrl}/status`);
  }

  save(data) {
    return this.http.post(`${this.baseUrl}/status`, data);
  }
}