import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { SnotifyService } from 'ng-snotify';
import { UserService } from 'src/app/services/user.service';

@Component({
  selector: 'app-usuarios',
  templateUrl: './usuarios.component.html',
  styleUrls: ['./usuarios.component.css']
})
export class UsuariosComponent implements OnInit {

  dataTable: any;
  loading = false;
  arrUsers: any = [];

  constructor(private notify: SnotifyService, private User: UserService) { }

  ngOnInit() {
    // this.dataTable = $(this.table.nativeElement);
    // this.dataTable.dataTable();
    this.loading = true;
    this.User.getUsers().subscribe(
      result => {
        this.loading = false;
        this.arrUsers = result;
      },
      error => {
        this.loading = false;
        this.notify.error('Erro ao retornar os usuários', {timeout: 3000, showProgressBar: false });
      }
    );
  }

}
