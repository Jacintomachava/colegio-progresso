@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="col-sm-12">
    <div class="card title-line">
        <div class="card-header bg-dark">
        <h5 class="text-white">Configuracoes de Saldo a Notificar</h5>
        <div class="card-header-right dark-color-header">
            <h5 class="text-white"></h5>
        </div>
        </div>
        <div class="card-body bg-dark">
        <div class="d-flex align-items-center gap-3 pills-blogger"> 
            <div class="blog-wrapper col-md-3"> 
            <div class="customers d-inline-block avatar-group">
                <ul>
                  <li class="d-inline-block">
                      <center>
                        Saldo Actual <br><br> {{$saldo->saldo}}MT  <br><br>
                        <hr style="color: white; font-size: 40pt">
                        Notifica-me Quando Atingir<br><br>
                      <b>{{$saldo->saldo_limite}}MT</b></center>
                  </li>
                </ul>
             </div>
            </div>

            <div class="col-md-8"> 
                <div class="card title-line">
                  <div class="card-header">
                    <h2>Contactos a Notificar</h2>
                  </div>
                  <div class="card-block row">
                    <div class="col-sm-12 col-lg-12 col-xl-12">
                      <div class="table-responsive custom-scrollbar">
                        <table class="table table-inverse table-striped">
                          <thead>
                            <tr>
                              <th scope="col">#</th>
                              <th scope="col">Nome</th>
                              <th scope="col">Contacto</th>
                              <th scope="col">Data registo</th>
                              <th scope="col">Apagar</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($contactos as $contacto)
                              <tr>
                                <th scope="row"></th>
                                <td>{{$contacto->nome}}</td>
                                <td>{{$contacto->contacto_anotificar}}</td>
                                <td>{{$contacto->updated_at->format('d-M-Y')}}</td>
                                <td>
                                  <a href="#"
                                    class="btn btn-danger btn-xs apagar-contacto"
                                    data-id="{{ $contacto->id }}">
                                      Apagar
                                  </a>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
            <div class="col-md-1"> 

            </div>
        </div>
        </div>
        <div class="card-footer bg-dark">
          @role('Secretaria')

            <a href="#"
              class="btn btn-success btn-xs"
              id="btnSaldoAviso">
                Saldo de Aviso
            </a>

            <a href="#"
              class="btn btn-warning btn-xs"
              id="btnContactoNotificar">
                Contactos a Notificar
            </a>

          @endrole

        </div>
    </div>
</div>



<div class="modal fade" id="modalContactoNotificar" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Contacto a Notificar
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form id="formContactoNotificar">

                @csrf

                <div class="modal-body">

                    <div class="mb-3">

                        <label>Nome</label>

                        <input type="text"
                               name="nome"
                               id="nomeContacto"
                               class="form-control"
                               required>

                    </div>

                    <div class="mb-3">

                        <label>Contacto</label>

                        <input type="text"
                               name="contacto"
                               id="numeroContacto"
                               class="form-control"
                               placeholder="84xxxxxxx"
                               maxlength="9"
                               required>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="btn btn-warning"
                            id="btnGuardarContacto">
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


@endsection

@push('js')

  <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

  <script>

    $('#btnSaldoAviso').click(function(e) {

        e.preventDefault();

        $('#modalSaldoAviso').modal('show');

    });

    $('#formSaldoAviso').submit(function(e) {

      e.preventDefault();

      let botao = $('#btnGuardarSaldo');

      botao.prop('disabled', true).text('A guardar...');

      $.ajax({

          url: "{{ route('saldo.limite.update') }}",
          type: "POST",

          data: {
              _token: "{{ csrf_token() }}",
              saldo_limite: $('#inputSaldoLimite').val()
          },

          success: function(response) {

              botao.prop('disabled', false)
                  .text('Guardar');

              if (response.status == 1) {

                  $('#saldoLimite').html(
                      response.saldo_limite + ' MT'
                  );

                  $('#modalSaldoAviso').modal('hide');

                  Swal.fire({
                      icon: 'success',
                      title: 'Sucesso',
                      text: response.message
                  });

                  location.reload();

              }

          },

          error: function(xhr) {

              botao.prop('disabled', false)
                  .text('Guardar');

              let mensagem = 'Erro ao atualizar saldo de aviso.';

              if (xhr.status === 422) {
                  mensagem = Object.values(xhr.responseJSON.errors)
                      .flat()
                      .join('<br>');
              }

              Swal.fire({
                  icon: 'error',
                  title: 'Erro',
                  html: mensagem
              });

          }

      });

  });

  $('#btnContactoNotificar').click(function(e) {

      e.preventDefault();

      $('#formContactoNotificar')[0].reset();

      $('#modalContactoNotificar').modal('show');

  });

$(document)
    .off('submit', '#formContactoNotificar')
    .on('submit', '#formContactoNotificar', function(e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        let botao = $('#btnGuardarContacto');

        // Proteção adicional contra envio duplo
        if (botao.data('enviando')) {
            return false;
        }

        botao.data('enviando', true)
             .prop('disabled', true)
             .text('A guardar...');

        $.ajax({

            url: "{{ route('saldo.contacto.store') }}",
            type: "POST",

            data: {
                _token: "{{ csrf_token() }}",
                nome: $('#nomeContacto').val(),
                contacto: $('#numeroContacto').val()
            },

            success: function(response) {

                if (response.status == 1) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: response.message
                    }).then(() => {
                        location.reload();
                    });

                } else {

                    botao.data('enviando', false)
                         .prop('disabled', false)
                         .text('Guardar');
                }
            },

            error: function(xhr) {

                botao.data('enviando', false)
                     .prop('disabled', false)
                     .text('Guardar');

                let mensagem = 'Erro ao guardar contacto.';

                if (
                    xhr.status === 422 &&
                    xhr.responseJSON &&
                    xhr.responseJSON.errors
                ) {
                    mensagem = Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join('<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    html: mensagem
                });
            }

        });

        return false;
    });

    $(document).on('click', '.apagar-contacto', function(e) {

      e.preventDefault();

      let id = $(this).data('id');

      Swal.fire({

          title: 'Apagar contacto?',
          text: 'Este contacto deixará de receber notificações de saldo.',
          icon: 'warning',

          showCancelButton: true,

          confirmButtonText: 'Sim, apagar',
          cancelButtonText: 'Cancelar'

      }).then((result) => {

          if (result.isConfirmed) {

              $.ajax({

                  url: "{{ url('/saldo/contacto') }}/" + id,

                  type: "DELETE",

                  data: {
                      _token: "{{ csrf_token() }}"
                  },

                  success: function(response) {

                      if (response.status == 1) {

                          Swal.fire({
                              icon: 'success',
                              title: 'Sucesso',
                              text: response.message
                          }).then(() => {

                              location.reload();

                          });

                      }

                  },

                  error: function() {

                      Swal.fire({
                          icon: 'error',
                          title: 'Erro',
                          text: 'Não foi possível apagar o contacto.'
                      });

                  }

              });

          }

      });

  });

  </script>


@endpush
