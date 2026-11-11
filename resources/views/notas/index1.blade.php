
@extends('layouts.app')

@push('css')


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@endpush

@section('conteudo')


<div class="container">
  <div class="card-body dark-alert">
    <form id="form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
    <input type="hidden" name="trimestre" value="{{ $trimestre }}">
    <input type="hidden" name="disciplina_id" value="{{ $disciplina_id }}">

        <div class="col-md-4">
            <label for="disciplina">Disciplina</label>
            <select name="disciplina" id="disciplina" class="form-select" onchange="this.form.submit()">
                @foreach ($disciplinas as $item)
                    <option value="{{ $item->disciplina->id }}"
                        {{ $disciplina_id == $item->disciplina->id ? 'selected' : '' }}>
                        {{ $item->disciplina->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="trimestre">Trimestre</label>
            <select name="trimestre" id="trimestre" class="form-select" onchange="this.form.submit()">
                <option value="1" {{ $trimestre == 1 ? 'selected' : '' }}>1º Trimestre</option>
                <option value="2" {{ $trimestre == 2 ? 'selected' : '' }}>2º Trimestre</option>
                <option value="3" {{ $trimestre == 3 ? 'selected' : '' }}>3º Trimestre</option>
            </select>
        </div>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Aluno</th>
                <th>G</th>
                <th>1ª</th>
                <th>2ª</th>
                <th>3ª</th>
                <th>4ª</th>
                <th>MM</th>
                <th>AT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alunosMatriculados->unique('aluno_id') as $matricula)
                @php
                    $aluno = $matricula->aluno;
                    $pauta =  $pautas->firstWhere('aluno_id', $aluno->id);
                @endphp

                <tr>
                    <td>{{ $aluno->pessoa->nome }}</td>
                    <td>{{ substr($aluno->pessoa->genero, 0, 1) }}</td>

                    {{-- Testes 1 a 4 --}}
                    @for ($i = 1; $i <= 4; $i++)
                        @php
                            $campo = 'teste'.$i.'Tr'.$trimestre;
                            $valor = $pauta?->$campo;
                        @endphp
                        <td>
                            <input type="number" step="0.01" min="0" max="20"
                                class="form-control"
                                name="notas[{{ $aluno->id }}][{{ $campo }}]"
                                value="{{ $valor > 0 ? $valor : '' }}">
                        </td>
                    @endfor

                    {{-- Média do trimestre --}}
                    @php
                        $mmCampo = 'mmTr'.$trimestre;
                        $mmValor = $pauta?->$mmCampo;
                    @endphp
                    <td>
                        <input type="number" step="0.01" min="0" max="20"
                            class="form-control"
                            name="notas[{{ $aluno->id }}][{{ $mmCampo }}]"
                            value="{{ $mmValor > 0 ? $mmValor : '' }}">
                    </td>

                    {{-- Avaliação Trimestral (AP / AT) --}}
                    @php
                        $apCampo = 'apTr'.$trimestre;
                        $apValor = $pauta?->$apCampo;
                    @endphp
                    <td>
                        <input type="number" step="0.01" min="0" max="20"
                            class="form-control"
                            name="notas[{{ $aluno->id }}][{{ $apCampo }}]"
                            value="{{ $apValor > 0 ? $apValor : '' }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <button type="submit" class="btn btn-primary">Guardar Notas</button>
  </form>
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
        rules: {
            vaga: {
                required: true,
            }
        },
        submitHandler: function(form) {
            // Use FormData para suportar envio de arquivos
            var formData = new FormData(form);

            $.ajax({
                type: "POST",
                url: "{{ route('notas.update') }}",
                data: formData,
                processData: false, // Não processa os dados, necessário para FormData
                contentType: false, // Define o content type como multipart/form-data
                beforeSend: function() {
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Actualizar Utilizador...');
                },
                success: function(response) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');

                    if (response.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                        });
                        window.location.reload();
                    } else if (response.status == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message,
                        });
                    }
                },
                error: function(errors) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');
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
