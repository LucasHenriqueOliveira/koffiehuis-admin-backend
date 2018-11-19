import { Component, OnInit } from '@angular/core';
import { SnotifyService } from 'ng-snotify';
import { VeiculoService } from 'src/app/services/veiculo.service';

@Component({
  selector: 'app-carros',
  templateUrl: './carros.component.html',
  styleUrls: ['./carros.component.css']
})
export class CarrosComponent implements OnInit {

  loading = false;
  arrCarros: any = [];

  constructor(private notify: SnotifyService, private Veiculo: VeiculoService) { }

  ngOnInit() {
    this.loading = true;
    this.Veiculo.getCars().subscribe(
      result => {
        this.loading = false;
        this.arrCarros = result;
      },
      error => {
        this.loading = false;
        this.notify.error('Erro ao retornar os veículos', {timeout: 3000, showProgressBar: false });
      }
    );
  }

}
