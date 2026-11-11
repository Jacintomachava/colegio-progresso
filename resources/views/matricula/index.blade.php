@extends('layouts.app')

@push('css')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- Tagify CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
<!-- Tagify JS -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

@endpush

@section('conteudo')

<div class="col-xl-12 order-md-iii">
    <div class="card title-line overflow-hidden member-wrapper">
        <div class="card-header card-no-border">
            <div class="header-top">
                <h2>
                    <img class="img-40 img-fluid m-r-20" src="{{ URL('/public/assets/images/job-search/2.jpg') }}" alt="">
                    Matricula/Renovação de Ciclo {{$anoAcademicoAtivo->ano}}
                </h2>
                <div class="card-header-right-icon">
                    <a href="{{route('matricula.create')}}">
                        <button class="btn btn-pill btn-primary btn-sm">Fazer Matricula</button>
                    </a>
                    @if($renovacao==1)
                        <a href="#" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                            <button class="btn btn-pill btn-success btn-sm">Renovar Matricula</button>
                        </a>
                    @endif
                    @if($renovacao==1)
                        <a href="#" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg-1">
                            <button class="btn btn-pill btn-warning btn-sm">Atribuir Turma</button>
                        </a>
                    @endif
                        <a href="#" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                            <button class="btn btn-pill btn-success btn-sm">Renovar Matricula</button>
                        </a>
                        <a href="{{route('atribuir.turma')}}" >
                            <button class="btn btn-pill btn-warning btn-sm">Atribuir Turma</button>
                        </a>
                        <a href="{{route('relatorio.matriculado')}}" target="_blank" >
                            <button class="btn btn-pill btn-secondary btn-sm">Imprimir</button>
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
                            <th></th>
                            <th>Codigo</th>
                            <th>Nome</th>
                            <th>Classe</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($alunosMatriculados as $alunosMatriculado)
                            <tr>
                                <td></td>
                                <td>
                                  <a href="{{route('aluno.show',['codigo'=>$alunosMatriculado->aluno->codigo])}}" >
                                    {{$alunosMatriculado->aluno->codigo}}
                                  </a>
                                </td>
                                <td data-container="body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ $alunosMatriculado->aluno->pessoa->nome }}">
                                    {{ \Illuminate\Support\Str::limit($alunosMatriculado->aluno->pessoa->nome, 25) }}
                                </td>
                                <td>{{$alunosMatriculado->classe->nome}}</td>
                                <td>

                                    @if($alunosMatriculado->renovacao==1)
                                        <span class="btn btn-info btn-xs activate-btn" >
                                             Renovação
                                        </span>
                                    @else
                                        <span class="btn btn-warning btn-xs activate-btn" >
                                             N. Ingresso
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($alunosMatriculado->valor_total, 2, ',', '.') }}
                                </td>    

                                <td>{{$alunosMatriculado->updated_at->format('d-M-Y')}}</td>
                                <td>

                                    @if($alunosMatriculado->pagou==0)
                                        <a class="btn btn-warning btn-xs activate-btn" target="_blank" href="{{route('matricula.facturaPDF',['factura'=>$alunosMatriculado->pagamento->referencia])}}">
                                             Factura
                                        </a>
                                    @endif

                                    @if($alunosMatriculado->pagou==1)
                                        <a class="btn btn-success btn-xs activate-btn" target="_blank" href="{{route('matricula.reciboPDF',['factura'=>$alunosMatriculado->pagamento->referencia])}}">
                                            Recibo
                                        </a>
                                    @else

                                    @endif
                                        
                                       {{--

                                        <a class="btn btn-primary btn-xs activate-btn" target="_blank" href="{{route('propina.factura',['alunoCodigo'=>$alunosMatriculado->aluno->codigo])}}" >
                                            Propinas
                                        </a>

                                       --}}


                                        <a class="btn btn-secondary btn-xs activate-btn" href="{{route('cocurricular.edit',['alunoCodigo'=>$alunosMatriculado->aluno->codigo])}}" >
                                            Co-Curr.
                                        </a>
                                        <a class="btn btn-warning btn-xs activate-btn" href="{{route('matricula.edit',['codigo'=>$alunosMatriculado->id])}}" >
                                            Editar
                                        </a>
                                        @if($alunosMatriculado->pagou==1)
                                            <a target="_blank" href="{{route('matricula.reciboPDF',['factura'=>$alunosMatriculado->pagamento->referencia])}}" class="btn btn-success btn-xs" href="#!">PAGO</a>   
                                        @else
                                            <a href="{{route('pagamentoMatricula.index',['codigo'=>$alunosMatriculado->id])}}" class="btn btn-danger btn-xs" href="#!">PAGAR</a> 
                                            {{-- <a href="{{route('pagamentoMatriculaMpesa.index',['codigo'=>$alunosMatriculado->codigo])}}" class="btn btn-danger btn-xs" href="#!">Mpesa</a> --}}
                                        @endif

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
            <h4 class="modal-title" id="myExtraLargeModal">Renovação da Matricula</h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">

            @csrf
            
            <input class="form-control" type="hidden" name="anoAcademico" value="{{$anoAcademicoActual}}" placeholder="Renovação">

            <div class="row">
                <div class="col-12">
                  <label class="form-label" for="passwordwizard">Aluno<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="aluno" id="aluno">
                        <option value="">Seleciona o Aluno</option>
                        @foreach($alunosNaoMatriculados as $aluno)
                        <option value="{{$aluno->id}}">{{$aluno->pessoa->nome}} - {{$aluno->codigo}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12">
                    <label for="contactos" class="form-label">{{ __('Disciplina Co-Curriculares') }} <span class="txt-danger">*</span></label>
                    <select class="form-select contactos1" name="cocurriculares[]"  multiple="multiple" >
                        @foreach ($disciplinas as $disciplina)
                            <option value="{{ $disciplina->id }}"
                                {{ in_array($disciplina->id, old('cocurriculares', [])) ? 'selected' : '' }}>
                                {{ $disciplina->nome }} 
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                  <label class="form-label" for="passwordwizard">Telefone de Encarregado Para receber SMS</label>
                  <input class="form-control" type="text" name="telefone" placeholder="84123456789">
                </div>

            </div>

            <br>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="botao_salvar" type="submit" >
                <span id="botao_texto">{{__('Renovação da Matricula')}}</span>
                <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
            </button>
        </div>
      </form>  
    </div>
    </div>
</div>



@endsection

@push('js')

<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


<script>
    $(document).ready(function() {
        $('.contactos').select2({
            placeholder: "{{ __('Seleciona Alunos Irmaos') }}",
            allowClear: true
        });
    });
    $(document).ready(function() {
        $('.contactos1').select2({
            placeholder: "{{ __('Seleciona Actividades Co-Curricular') }}",
            allowClear: true
        });
    });
</script>

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
            aluno: {
                required: true,
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('renovacao.store')}}",
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alunoSelect = new Choices('#aluno', {
            searchPlaceholderValue: "Digite para pesquisar...",
            shouldSort: false,  // Mantém a ordem dos alunos no dropdown
        });
    });
</script>

@endpush
