@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="col-sm-12">
    <div class="card title-line">
        <div class="card-header bg-dark">
        <h5 class="text-white">Configuracoes de Novo Ciclo Academico</h5>
        <div class="card-header-right dark-color-header">
            <h5 class="text-white"></h5>
        </div>
        </div>
        <div class="card-body bg-dark">
        <div class="d-flex align-items-center gap-3 pills-blogger"> 
           
            <div class="blog-wrapper col-md-12"> 
                <div class="card title-line">
                  <div class="card-header">
                    <h2>CICLO ACADEMICO </h2>
                  </div>
                  <div class="card-block row">
                    <div class="col-sm-12 col-lg-12 col-xl-12">
                      <div class="table-responsive custom-scrollbar">
                        <table class="table table-inverse table-striped">
                          <thead>
                            <tr>
                              <th scope="col">Categoria</th>
                              <th scope="col">Ano Actual</th>
                              <th scope="col">Ciclo em Curso</th>
                              <th scope="col">Estado</th>
                              <th scope="col">Valor</th>
                              <th scope="col">Descricao</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($ciclos  as $ciclo)
                              <tr>
                                <th scope="row">{{$ciclo->tipo}}</th>
                                <td>{{$current_year}}</td>
                                <td>{{$ciclo->anoAcademico->ano}}</td>
                                <td>
                                    @if($ciclo->estado == 1)
                                        ACTIVO
                                    @else
                                        INATIVO
                                    @endif
                                </td>
                                <td>

                                    @if($ciclo->tipo == 'RENOVACAO')

                                       Normal: {{$ciclo->valor_renovacao_normal}} | Gold: {{$ciclo->valor_renovacao_gold}}

                                    @elseif($ciclo->tipo == 'MATRICULA')   

                                       Consulta Tabela Abaixo 

                                    @elseif($ciclo->tipo == 'MENSALIDADE')   

                                       Consulta Tabela Abaixo 


                                    @else
                                        ---
                                    @endif
                                </td>
                                <td>{{$ciclo->descricao}}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
            </div>

            <div class="col-md-2"> 

            </div>
        </div>
        </div>

        <div class="card-footer bg-dark">
          @role('Secretaria')

            <a href="#"
              class="btn btn-success btn-xs"
              id="btnNovoCiclo">
                Novo Ciclo
            </a>

          @endrole

        </div>
    </div>
</div>


<br>
    <h2><center>Confirma as Taxas do Ciclo Academico</center></h2>
<br>

<div class="col-sm-12" style="margin-top: 0%"> 
    <div class="card title-line">
        <div class="card-block row">
        <div class="col-sm-12 col-lg-12 col-xl-12">
            <div class="table-responsive custom-scrollbar">
                <table class="table">
                    <tbody>

                        <tr class="table-dark">
                            <th colspan="8"><center>Taxa Curricular</center></th>
                        </tr>
                        <tr class="table-dark">
                            <th scope="col">Ciclos</th>
                            <th scope="col">Classes</th>
                            <th scope="col">Matricula Normal</th>
                            <th scope="col">Matricula Gold</th>
                            <th scope="col">P. Normal (Meio Dia)</th>
                            <th scope="col">P. Normal (Todo Dia)</th>
                            <th scope="col">P. Gold (Meio Dia)</th>
                            <th scope="col">P. Gold (Todo Dia)</th>
                        </tr>
                        @foreach($taxas as $taxa)
                            <tr>
                                <td>{{$taxa->ciclo->nome}}</td>
                                <td>{{$taxa->ciclo->classes}}</td>
                                <td>{{number_format($taxa->matricula_normal, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->matricula_gold, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_meio_dia_normal, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_todo_dia_normal, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_meio_dia_gold, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_todo_dia_gold, 2, ',', '.')}} </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <table class="table">
                    <tbody>

                        <tr class="table-dark">
                            <th colspan="8"><center>Taxa Co-Curricular</center></th>
                        </tr>
                        <tr class="table-dark">
                            <th scope="col">Ciclos</th>
                            <th scope="col">Aulas</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Valor Propina</th>
                        </tr>
                        @foreach($disciplinas as $disciplina)
                            <tr>
                                <td >Todos Ciclos</td>
                                <td>{{$disciplina->nome}}</td>
                                <td>Propinas</td>
                                <td>{{number_format($disciplina->propina, 2, ',', '.')}} </td>
                            </tr>
                        @endforeach
     
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalNovoCiclo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Nova Configuração do Ciclo</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <form id="formNovoCiclo">
                @csrf

                <div class="modal-body">


                    {{-- ESTADO --}}
                    <div class="mb-3">
                        <label class="form-label">
                            ESTADO
                        </label>

                        <select name="estado"
                                id="estadoCiclo"
                                class="form-control"
                                required>

                            <option value="1">
                                ACTIVO
                            </option>

                            <option value="0">
                                INATIVO
                            </option>

                        </select>
                    </div>


                    {{-- TIPO --}}
                    <div class="mb-3">
                        <label class="form-label">
                            TIPO
                        </label>

                        <select name="tipo"
                                id="tipoCiclo"
                                class="form-control"
                                required>

                            <option value="">
                                Selecione
                            </option>

                            <option value="MATRICULA">
                                MATRICULA
                            </option>

                            <option value="RENOVACAO">
                                RENOVACAO
                            </option>

                            <option value="MENSALIDADE">
                                MENSALIDADE
                            </option>

                            <option value="VER PAUTA">
                                VER PAUTA
                            </option>

                            <option value="GERAR PAUTA">
                                GERAR PAUTA
                            </option>

                        </select>
                    </div>

                    {{-- CICLO --}}
                    <div class="mb-3">
                        <label class="form-label">
                            CICLO EM CURSO
                        </label>

                        <select name="ano_academico_id"
                                id="anoAcademico"
                                class="form-control"
                                required>

                            <option value="">
                                Selecione o ciclo
                            </option>

                            @foreach($anos as $ano)
                                <option value="{{ $ano->id }}">
                                    {{ $ano->ano }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- VALOR - SOMENTE RENOVAÇÃO --}}
                    <div id="divValorRenovacao" style="display:none;">

                        <div class="row">

                            <div class="col-md-6">
                                <label>Renovação Normal</label>

                                <div class="input-group">
                                    <input type="number"
                                        name="valor_renovacao_normal"
                                        id="valorRenovacaoNormal"
                                        class="form-control"
                                        step="0.01"
                                        min="0">

                                    <span class="input-group-text">MT</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label>Renovação Gold</label>

                                <div class="input-group">
                                    <input type="number"
                                        name="valor_renovacao_gold"
                                        id="valorRenovacaoGold"
                                        class="form-control"
                                        step="0.01"
                                        min="0">

                                    <span class="input-group-text">MT</span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            id="btnGuardarCiclo">
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

$(document)
    .off('click', '#btnNovoCiclo')
    .on('click', '#btnNovoCiclo', function(e) {

        e.preventDefault();

        $('#formNovoCiclo')[0].reset();

        $('#divValorRenovacao').hide();

        $('#valorRenovacao')
            .prop('required', false)
            .val('');

        $('#modalNovoCiclo').modal('show');
});

$(document)
.off('change', '#tipoCiclo')
.on('change', '#tipoCiclo', function () {

    if ($(this).val() === 'RENOVACAO') {

        $('#divValorRenovacao').slideDown();

        $('#valorRenovacaoNormal').prop('required', true);
        $('#valorRenovacaoGold').prop('required', true);

    } else {

        $('#divValorRenovacao').slideUp();

        $('#valorRenovacaoNormal')
            .prop('required', false)
            .val('');

        $('#valorRenovacaoGold')
            .prop('required', false)
            .val('');
    }
});


$(document)
.off('submit', '#formNovoCiclo')
.on('submit', '#formNovoCiclo', function(e) {

    e.preventDefault();
    e.stopImmediatePropagation();

    let botao = $('#btnGuardarCiclo');

    if (botao.data('enviando')) {
        return false;
    }

    botao
        .data('enviando', true)
        .prop('disabled', true)
        .text('A guardar...');

    $.ajax({

        url: "{{ route('ciclo.ano.store') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",
            ano_academico_id: $('#anoAcademico').val(),
            tipo: $('#tipoCiclo').val(),
            valor_renovacao_normal: $('#valorRenovacaoNormal').val(),
            valor_renovacao_gold: $('#valorRenovacaoGold').val(),
            estado: $('#estadoCiclo').val()
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

                botao
                    .data('enviando', false)
                    .prop('disabled', false)
                    .text('Guardar');
            }
        },

        error: function(xhr) {

            botao
                .data('enviando', false)
                .prop('disabled', false)
                .text('Guardar');

            let mensagem =
                'Não foi possível guardar a configuração.';

            if (
                xhr.status === 422 &&
                xhr.responseJSON &&
                xhr.responseJSON.errors
            ) {

                mensagem = Object.values(
                    xhr.responseJSON.errors
                )
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


</script>

@endpush
