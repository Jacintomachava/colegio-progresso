@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="col-sm-12">
    <div class="card title-line">
        <div class="card-header bg-dark">
        <h5 class="text-white">{{$funcionario->pessoa->nome}}</h5>
        <div class="card-header-right dark-color-header">
            <h5 class="text-white">{{$funcionario->codigo}} </h5>
        </div>
        </div>
        <div class="card-body bg-dark">
        <div class="d-flex align-items-center gap-3 pills-blogger"> 
            <div class="blog-wrapper col-md-3"> 
            <div class="customers d-inline-block avatar-group">
                <ul>
                <li class="d-inline-block">
                  @if($funcionario->escola->logotipo=='avatar.png')
                    <img class="img-100 b-r-8" src="{{ URL('/logotipo/'. session('logotipo') ) }}" alt="user profile">
                  @else
                     
                    <img class="img-100 b-r-8" src="{{ URL('/logotipo/'.$funcionario->escola->logotipo) }}" alt="user profile">
                  @endif
                </li>
                <li class="d-inline-block">
                  @if($funcionario->avatar=='avatar.png')
                    <img class="img-80 b-r-30" src="{{ URL('/avatar/avatar.png') }}" alt="user profile">
                  @else
                   <img class="img-80 b-r-30" src="{{ URL('/avatar/'.$funcionario->avatar) }}" alt="user profile">
                  @endif
                </li>
                </ul>
             </div>
            </div>
            <div class="col-md-4"> 
            <b>Nome de Aluno:</b><span style="padding-left: 3%"> {{$funcionario->pessoa->nome}}</span><br>
            <b>Tipo Documento:</b><span style="padding-left: 3%"> {{$funcionario->pessoa->tipo_documento}}</span><br>
            <b>Provincia:</b><span style="padding-left: 3%"> {{$funcionario->provincia->nome}}</span><br>
            <b>Inicio Funções:</b><span style="padding-left: 3%"> {{ \Carbon\Carbon::parse($funcionario->data_inicio_funcoes)->format('d-M-Y') }} </span><br>
            <b>Nivel Academico:</b><span style="padding-left: 3%"> {{ $funcionario->pessoa->nivel_academico }} </span><br>
            <b>Cargo:</b><span style="padding-left: 3%"> {{ $funcionario->cargo }} </span><br>
            <b>Salário:</b><span style="padding-left: 3%"> {{ number_format($funcionario->salario, 2, ',', '.') }} </span><br>
            <b>Telefone:</b><span style="padding-left: 3%"> {{ $funcionario->pessoa->telefone }} </span><br>


            </div>
            <div class="col-md-5"> 
            <b>Data Nascimento:</b><span style="padding-left: 3%"> {{ \Carbon\Carbon::parse($funcionario->data_nascimento)->format('d-M-Y') }}</span><br>
            <b>Numero de Documento:</b><span style="padding-left: 3%">{{$funcionario->pessoa->numero_documento}}</span><br>
            <b>Genero:</b><span style="padding-left: 3%">{{$funcionario->pessoa->genero}}</span><br>
            <b>Data Registo:</b><span style="padding-left: 3%">{{ \Carbon\Carbon::parse($funcionario->created_at)->format('d-M-Y') }}</span><br>
            <b>Área:</b><span style="padding-left: 3%">{{ $funcionario->area }}</span><br>
            <b>NUIT:</b><span style="padding-left: 3%">{{ $funcionario->pessoa->nuit }}</span><br>
            <b>Endereco:</b><span style="padding-left: 3%">{{ $funcionario->pessoa->endereco }}</span><br>
            <b>Email:</b><span style="padding-left: 3%"> {{ $funcionario->pessoa->email }} </span><br>

            </div>
        </div>
        </div>
        <div class="card-footer bg-dark">
          @role('Secretaria')
            <a href="#"
              class="btn btn-success btn-xs mudar-foto"
              data-id="{{ $funcionario->codigo }}"
              data-foto="{{$funcionario->avatar }}">
              Mudar Foto
            </a>
          @endrole

        </div>
    </div>
</div>



<div class="modal fade" id="modalMudarFoto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Mudar Foto</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <form id="formMudarFoto" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">

                    <input type="hidden"
                           name="codigo"
                           id="foto_codigo">

                    <div class="text-center mb-3">
                        <img id="previewFoto"
                             src=""
                             width="120"
                             height="120"
                             style="object-fit: cover; border-radius: 50%;">
                    </div>

                    <div class="form-group">
                        <label>Nova Foto</label>

                        <input type="file"
                               class="form-control"
                               name="avatar"
                               id="avatar"
                               accept="image/jpeg,image/png,image/webp"
                               required>
                    </div>

                    <div id="erroFoto"
                         class="alert alert-danger mt-3 d-none">
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
                            id="btnSalvarFoto">
                        Atualizar Foto
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


@endsection

@push('js')


<script>

$(document).on('click', '.mudar-foto', function (e) {

    e.preventDefault();

    let codigo = $(this).data('id');
    let foto = $(this).data('foto');

    $('#foto_codigo').val(codigo);
    $('#avatar').val('');

    if (foto) {
        $('#previewFoto').attr(
            'src',
            "{{ url('/avatar') }}/" + foto
        );
    } else {
        $('#previewFoto').attr('src', '');
    }

    $('#erroFoto').addClass('d-none').html('');

    $('#modalMudarFoto').modal('show');
});

$('#avatar').on('change', function () {

    let file = this.files[0];

    if (file) {
        let reader = new FileReader();

        reader.onload = function (e) {
            $('#previewFoto').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    }
});

$('#formMudarFoto').on('submit', function (e) {

    e.preventDefault();

    let formData = new FormData(this);

    $('#btnSalvarFoto')
        .prop('disabled', true)
        .text('A atualizar...');

    $.ajax({
        url: "{{ route('funcionario.avatar.update') }}",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function (response) {

            $('#btnSalvarFoto')
                .prop('disabled', false)
                .text('Atualizar Foto');

            if (response.status == 1) {

                let codigo = $('#foto_codigo').val();

                $('#avatar-funcionario-' + codigo)
                    .attr('src', response.avatar_url + '?t=' + new Date().getTime());

                $('.mudar-foto[data-id="' + codigo + '"]')
                    .data('foto', response.avatar);

                $('#modalMudarFoto').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: response.message
                });

                location.reload();
            }
        },

        error: function (xhr) {

            $('#btnSalvarFoto')
                .prop('disabled', false)
                .text('Atualizar Foto');

            let mensagem = 'Erro ao atualizar a foto.';

            if (xhr.status === 422 && xhr.responseJSON.errors) {

                let errors = xhr.responseJSON.errors;

                mensagem = Object.values(errors)
                    .flat()
                    .join('<br>');
            }

            $('#erroFoto')
                .removeClass('d-none')
                .html(mensagem);
        }
    });
});

</script>

@endpush
