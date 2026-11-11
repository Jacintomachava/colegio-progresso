@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="col-xl-12 order-md-iii">
    <div class="card title-line overflow-hidden member-wrapper">
        <div class="card-header card-no-border">
            <div class="header-top">
                <h2>
                    <img class="img-40 img-fluid m-r-20" src="../assets/images/job-search/2.jpg" alt="">
                    Disciplinas da {{$classe->nome}}
                </h2>
                <div class="card-header-right-icon">
                    <a href="#">
                        <button class="btn btn-pill btn-primary btn-sm" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">Adicionar Disciplina</button>
                    </a>
                </div>
            </div>
        </div>
    </div> <!-- Fechamento da div "card title-line overflow-hidden member-wrapper" -->
</div> <!-- Fechamento da div "col-xl-12 order-md-iii" -->

<div class="col-sm-12" style="margin-top: -2%">
    <div class="card">
        <div class="card-header pb-0 card-no-border">
        </div>
        <div class="card-body">

            <div class="table-responsive custom-scrollbar">
                <table class="display" id="basic-1">
                    <thead>
                        <tr>
                            <th>Disciplinas</th>
                            <th>Tem Exame</th>
                            <th>Faz Exame</th>
                            <th>Ordem</th>
                            <th>Descrição</th>
                            <th>Classe</th>
                            <th>Data</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($curriculos as $curriculo)
                            <tr>
                                <td>{{$curriculo->disciplina->nome}}</td>
                                <td>
                                    @if($curriculo->tem_exame)
                                     <span style="color: green">SIM</span>
                                    @else
                                     <span style="color: red">NÃO</span>
                                    @endif
                                </td>
                                <td>
                                    @if($curriculo->faz_exame)
                                     <span style="color: green">SIM</span>
                                    @else
                                     <span style="color: red">NÃO</span>
                                    @endif
                                </td>
                                <td>{{$curriculo->pauta_ordem}}ª</td>
                                <td>{{$curriculo->disciplina->descricao}}</td>
                                <td>{{$curriculo->classe->nome}}</td>
                                <td>{{$curriculo->updated_at->format('d-M-Y')}}</td>
                                <td>
                                    <a class="btn btn-warning btn-xs activate-btn" href="{{route('curriculo.edit',['curriculoID'=> $curriculo->id])}}">
                                        Editar
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


<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form  id="form">  
        <div class="modal-header">
            <h4 class="modal-title" id="myExtraLargeModal">Seleciona Disciplina para {{$classe->nome}}</h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">

            @csrf

           <input class="form-check-input"  type="hidden" name="classe" value="{{$classe->id}}">

            <div class="row">
                @if($disciplinasNaoAssociadas->isEmpty())
                    <p>Nenhuma disciplina disponível.</p>
                @else
                    @foreach($disciplinasNaoAssociadas as $disciplinasNaoAssociada)
                        <div class="form-check checkbox checkbox-success mb-0 col-md-4">
                            <input class="form-check-input" name="disciplina[]" id="checkbox-primary{{$disciplinasNaoAssociada->id}}" type="checkbox" value="{{$disciplinasNaoAssociada->id}}">
                            <label class="form-check-label" for="checkbox-primary{{$disciplinasNaoAssociada->id}}">{{$disciplinasNaoAssociada->nome}}</label>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="botao_salvar" type="submit" >
                <span id="botao_texto">{{__('Adicionar Disciplina(s)')}}</span>
                <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
            </button>
        </div>
      </form>  
    </div>
    </div>
</div>


@endsection

@push('js')

<script>

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#form").validate({
        // Adicionar regras para cada campo
        rules: {
            "disciplina[]": {
                required: true
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('curriculo.store')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Autenticando...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');

                    // Redireciona ou exibe uma mensagem de erro com base na resposta
                    if(response.status == 1) {

                      Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                      });

                      window.location.reload();

                    } else if(response.status == 0) {

                      Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message,
                      });

                    }
                },
                error: function(errors) {
                    // Habilita o botão e retorna o ícone original em caso de erro
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');

                    // Exibe a mensagem de erro
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: errors.responseJSON.message,
                    });

                }
            });
        }
    });
});

</script>


@endpush
