import { Component, OnInit } from '@angular/core';
import { SnotifyService } from 'ng-snotify';
import { DashboardService } from 'src/app/services/dashboard.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {

  loading = false;
  arrData: any = [];

  constructor(private notify: SnotifyService, private Dashboard: DashboardService) { }

  ngOnInit() {
    this.loading = true;
    this.Dashboard.get().subscribe(
      result => {
        this.loading = false;
        this.arrData = result;
      },
      error => {
        this.loading = false;
        this.notify.error('Erro ao retornar os dados', {timeout: 3000, showProgressBar: false });
      }
    );
  }

}
