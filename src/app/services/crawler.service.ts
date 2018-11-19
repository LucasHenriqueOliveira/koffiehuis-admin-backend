import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Constants } from '../../app/app-constants';

@Injectable({
  providedIn: 'root'
})
export class CrawlerService {

  constants: any = Constants;

  constructor(private http: HttpClient) { }

  marcas() {
    return this.http.get('http://fipeapi.appspot.com/api/1/carros/marcas.json');
  }

  save(data) {
    return this.http.post(`${this.constants.api}/crawler`, data);
  }
}
