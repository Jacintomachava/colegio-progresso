@extends('layouts.app')



@push('css')

<style>

.progresso-step{display:none}.progresso-step.active{display:block}

.progresso-camera{width:100%;max-height:260px;background:#171717;border-radius:8px;object-fit:cover}

.progresso-preview{width:100%;max-height:260px;object-fit:contain;border:1px solid #ddd;border-radius:8px}

.progresso-section{border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:15px}

.progresso-step .is-invalid{border-color:#dc3545}.progresso-step .invalid-feedback{display:block}.progresso-section h5{margin-bottom:15px}.progresso-nav .btn{margin-right:6px;margin-bottom:6px}

</style>

@endpush



@section('conteudo')

<div class="container-fluid">

 <div class="card height-equal title-line">

  <div class="card-header"><h3>Matrícula — Colégio Progresso</h3><p class="text-muted mb-2">Ficha de inscrição 2027</p>

   <div class="alert alert-light border mb-0" id="resumoValores" aria-live="polite">

    <div class="row g-2"><div class="col-md-3"><small>Matrícula</small><div class="fw-bold" id="valorMatricula">—</div></div>

    <div class="col-md-3"><small>Actividades mensais</small><div class="fw-bold" id="valorActividades">0,00 MT</div></div>

    <div class="col-md-3"><small>Mensalidade curricular</small><div class="fw-bold" id="valorPropina">—</div></div>

    <div class="col-md-3"><small>Total inicial estimado</small><div class="fw-bold text-primary" id="valorTotal">—</div></div></div>

    <small class="text-muted">Total inicial = matrícula + primeira propina + primeiro mês das actividades, antes de eventuais regras de vencimento. O valor definitivo será calculado no servidor.</small>

   </div></div>

  <div class="card-body">

   <div class="progresso-nav mb-3" id="progressoNav">

    <button type="button" class="btn btn-primary btn-sm" data-go="0">1. Aluno e fotografia</button>

    <button type="button" class="btn btn-outline-primary btn-sm" data-go="1">2. Família</button>

    <button type="button" class="btn btn-outline-primary btn-sm" data-go="2">3. Saúde e segurança</button>

    <button type="button" class="btn btn-outline-primary btn-sm" data-go="3">4. Actividades e confirmação</button>

   </div>

   <div id="progressoErros" class="alert alert-danger d-none"></div>

   <form id="msform" novalidate method="POST" action="{{ route('progresso.matriculas.store') }}" enctype="multipart/form-data">

    @csrf

    <section class="progresso-step active" data-step="0">

     <div class="progresso-section"><h5>Dados do aluno</h5><div class="row g-3">

      <div class="col-md-4"><label class="form-label">Nome completo *</label><input name="nomealuno" class="form-control" required value="{{ old('nomealuno') }}"></div>

      <div class="col-md-4"><label class="form-label">Género *</label><select name="genero" class="form-select" required><option value="">Seleccione</option><option value="Masculino">Masculino</option><option value="Femenino">Feminino</option></select></div>

      <div class="col-md-4"><label class="form-label">Nascimento *</label><input type="date" name="data_nascimento" class="form-control" required></div>

      <div class="col-md-4"><label class="form-label">Nacionalidade</label><select name="nacionalidade" class="form-select">

      @foreach(($nacionalidades ?? []) as $nacionalidade)

       <option value="{{ $nacionalidade->nome }}" @selected(old('nacionalidade', 'Moçambique') === $nacionalidade->nome)>{{ $nacionalidade->nome }}</option>

      @endforeach

     </select><small class="text-muted">Por defeito: Moçambicana.</small></div>

      <div class="col-md-4"><label class="form-label">Província *</label><select name="provincia" class="form-select" required><option value="">Seleccione</option>@foreach(($provincias ?? []) as $provincia)<option value="{{ $provincia->id }}">{{ $provincia->nome }}</option>@endforeach</select></div>

      <div class="col-md-4"><label class="form-label">Classe pretendida *</label><select name="classe" id="classe" class="form-select" required><option value="">Seleccione</option>@foreach(($classes ?? []) as $classe)<option value="{{ $classe->id }}">{{ $classe->nome }}</option>@endforeach</select><small class="text-muted">A turma será atribuída posteriormente.</small></div>

      <div class="col-md-4"><label class="form-label">Tipo de documento</label><select name="tipo_documento" class="form-select"><option value="">Seleccione</option><option value="B.I">B.I</option><option value="Cédula">Cédula</option><option value="Boletim de Nascimento">Boletim de Nascimento</option><option value="Passaporte">Passaporte</option></select></div>

      <div class="col-md-4"><label class="form-label">Número do documento</label><input name="numero_documento" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Data de emissão</label><input type="date" name="data_emissao" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Residência / bairro *</label><input name="endereco" class="form-control" required></div>

      <div class="col-md-4"><label class="form-label">Escola anterior</label><input name="escola_anterior" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Com quem vive a criança</label><input name="vive_com" class="form-control"></div>

     </div></div>

     <div class="progresso-section"><h5>Fotografia do aluno</h5><div class="row g-3">

      <div class="col-md-6"><video id="video" class="progresso-camera" autoplay playsinline muted></video><div class="mt-2 d-flex flex-wrap gap-2"><button type="button" id="iniciarCamera" class="btn btn-outline-primary">Activar câmara</button><button type="button" id="capture" class="btn btn-primary">Tirar foto</button><button type="button" id="pararCamera" class="btn btn-outline-secondary">Desligar câmara</button></div></div>

      <div class="col-md-6"><canvas id="canvas" class="d-none"></canvas><img id="fotoPreview" class="progresso-preview d-none" alt="Pré-visualização da fotografia"><div class="mt-2"><label for="fotoFicheiro" class="form-label">Ou escolher fotografia do computador</label><input id="fotoFicheiro" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"><small class="text-muted">A imagem escolhida substitui a fotografia capturada.</small></div></div>

     </div><input type="hidden" id="imagem" name="imagem"><div id="cameraAviso" class="small text-muted mt-2">A câmara só será activada quando clicar no botão. Requer HTTPS ou localhost.</div></div>

    </section>

    <section class="progresso-step" data-step="1"><div class="row g-3">

     @foreach(['pai'=>'Pai','mae'=>'Mãe','encarregado'=>'Encarregado de educação'] as $key=>$label)

     <div class="col-12">
        <div class="progresso-section"><h5>{{ $label }} {{ $key === 'encarregado' ? '*' : '(opcional)' }}</h5>
     <div class="row g-3">

      <div class="col-md-4">
        <label class="form-label">Nome {{ $label }}</label><input name="familia[{{ $key }}][nome]" class="form-control" {{ $key === 'encarregado' ? 'required' : '' }}>
      </div>

      @if($key === 'encarregado')

      <div class="col-md-4"><label class="form-label">Parentesco</label><select name="familia[encarregado][parentesco]" class="form-select"><option value="">Seleccione</option><option value="Pai">Pai</option><option value="Mãe">Mãe</option><option value="Avô/Avó">Avô/Avó</option><option value="Tio/Tia">Tio/Tia</option><option value="Tutor">Tutor</option><option value="Outro">Outro</option></select></div>

      @endif

      
      <div class="col-md-4">
          <label class="form-label">
              Nacionalidade {{ $label }}
          </label>

          <select
              name="familia[{{ $key }}][nacionalidade]"
              class="form-select"
          >
              <option value="">Seleccione a nacionalidade</option>

              @foreach(($nacionalidades ?? []) as $nacionalidade)
                  <option
                      value="{{ $nacionalidade->nome }}"
                      @selected(old("familia.$key.nacionalidade", 'Moçambique') === $nacionalidade->nome)
                  >
                      {{ $nacionalidade->nome }}
                  </option>
              @endforeach

          </select>
      </div>


      <div class="col-md-4"><label class="form-label">Formação académica</label><input name="familia[{{ $key }}][formacao]" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Profissão</label><input name="familia[{{ $key }}][profissao]" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Local de trabalho</label><input name="familia[{{ $key }}][local_trabalho]" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Telefone</label><input name="familia[{{ $key }}][telefone]" class="form-control" {{ $key === 'encarregado' ? 'required' : '' }}></div>

      <div class="col-md-4"><label class="form-label">Telefone alternativo</label><input name="familia[{{ $key }}][telefone2]" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="familia[{{ $key }}][email]" class="form-control"></div>

      <div class="col-md-4"><label class="form-label">Residência</label><input name="familia[{{ $key }}][residencia]" class="form-control" {{ $key === 'encarregado' ? 'required' : '' }}></div>

     </div></div></div>

     @endforeach

    </div></section>

    <section class="progresso-step" data-step="2"><div class="progresso-section"><h5>Saúde</h5><div class="row g-3">

     @foreach(['condicao'=>'Tem alguma condição de saúde/necessidade de acompanhamento?','medicacao'=>'Toma medicação regularmente?','alergia'=>'Tem alergias ou restrição alimentar?'] as $key=>$label)

     <div class="col-md-4"><label class="form-label">{{ $label }}</label><select name="saude[{{ $key }}]" class="form-select"><option value="0">Não</option><option value="1">Sim</option></select><label class="form-label mt-2">Especificar nome do medicamento/frequência</label> Especificar especifique os alimentos ou substâncias a evitar <textarea name="saude[{{ $key }}_descricao]" class="form-control" rows="2"></textarea></div>

     @endforeach

     <div class="col-md-4"><label class="form-label">Atestado médico apresentado?</label><select name="saude[atestado]" class="form-select"><option value="0">Não</option><option value="1">Sim</option></select></div>

     <div class="col-md-8"><label class="form-label">Unidade de saúde preferencial em caso de emergência</label><input name="saude[unidade_preferencial]" class="form-control"></div>

    </div></div><div class="progresso-section"><h5>Emergência e pessoas autorizadas a recolher o aluno</h5><div class="row g-3">

     <div class="col-md-6"><label class="form-label">Contacto de emergência — nome</label><input name="emergencia_nome" class="form-control" required></div>

     <div class="col-md-6"><label class="form-label">Contacto de emergência — telefone</label><input name="emergencia_telefone" class="form-control" required></div>

     @for($i=0;$i<2;$i++)

     <div class="col-md-6"><label class="form-label">Pessoa autorizada {{ $i+1 }} — nome</label><input name="autorizados[{{ $i }}][nome]" class="form-control"></div>

     <div class="col-md-6"><label class="form-label">Pessoa autorizada {{ $i+1 }} — telefone</label><input name="autorizados[{{ $i }}][telefone]" class="form-control"></div>

     @endfor

    </div></div></section>

    <section class="progresso-step" data-step="3"><div class="progresso-section"><h5>Pagamento e actividades opcionais</h5><div class="row g-3">

     <div class="col-md-6"><label class="form-label">Modalidade de pagamento *</label><select name="modalidade_pagamento" class="form-select" required><option value="MENSAL">Mensal</option><option value="TRES_PRESTACOES">Três prestações</option><option value="ANUAL">Anual</option></select></div>

     <div class="col-md-6">
        <label class="form-label">Bolsa de estudo (em %)</label>
          <input name="bolsa_percentual" id="bolsaPercentual" class="form-control" value="0">
          <small class="text-muted">A bolsa será aplicada às propinas curriculares, não à matrícula ou às actividades, salvo configuração futura.</small>
     </div>

     <div class="col-12"><p class="mb-2">Serviços e aulas extras (opcionais). Valores mensais configurados na base de dados.</p>

      <div class="row g-2">

       @forelse(($atividades ?? []) as $atividade)

       <div class="col-md-6"><label class="border rounded p-3 d-block"><input type="checkbox" name="atividades[]" value="{{ $atividade->id }}" data-valor="{{ $atividade->valor_mensal }}" class="form-check-input me-2 actividade-check">{{ $atividade->nome }} — {{ $atividade->tipo }} <span class="text-muted">({{ number_format($atividade->valor_mensal,2,',','.') }} MT/{{$atividade->periodicidade}})</span></label></div>

       @empty

       <div class="col-12"><div class="alert alert-warning">Não existem actividades disponíveis. Confirme o carregamento de $atividades no Controller.</div></div>

       @endforelse

      </div>

     </div>

     <div class="col-12"><div class="alert alert-info">Ao guardar, a matrícula e a respectiva cobrança são registadas. A factura será emitida posteriormente pela secretaria.</div></div>

    </div></div></section>

    <div class="d-flex justify-content-between mt-3"><button type="button" id="backbtn" class="btn btn-outline-secondary" disabled>Voltar</button><div><button type="button" id="nextbtn" class="btn btn-primary">Próximo</button><button type="submit" id="botao_salvar" class="btn btn-success d-none">Matricular</button></div></div>

   </form>

  </div>

 </div>

</div>

@endsection



@push('js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

<script>

// Requer jQuery e jquery.validate carregados antes deste bloco.

$(function(){

 const form=$('#msform');

 if(!$.fn.validate){console.error('Falta carregar jquery.validate.min.js');return;}

 $.validator.addMethod('telefoneMZ', function(value, element) { return this.optional(element) || /^(?:\+?258)?[0-9]{9}$/.test(value.replace(/\s+/g, '')); }, 'Informe um telefone válido de 9 dígitos (ou +258).');

 form.validate({

  ignore: [],

  onsubmit: false,

  errorElement:'div',errorClass:'invalid-feedback',

  highlight:function(el){$(el).addClass('is-invalid');},

  unhighlight:function(el){$(el).removeClass('is-invalid');},

  errorPlacement:function(error,element){error.insertAfter(element);},

  rules:{

   nomealuno:{required:true,minlength:3},genero:{required:true},data_nascimento:{required:true},

   provincia:{required:true},classe:{required:true},endereco:{required:true,minlength:3},

   'familia[encarregado][nome]':{required:true,minlength:3},

   'familia[encarregado][telefone]':{required:true,telefoneMZ:true},

   'familia[encarregado][residencia]':{required:true,minlength:3},

   emergencia_nome:{required:true,minlength:3},emergencia_telefone:{required:true,telefoneMZ:true}

  },

  messages:{required:'Este campo é obrigatório.',nomealuno:{required:'Informe o nome do aluno.'}}

 });

});

(function(){

 'use strict';

 const form=document.getElementById('msform');

 const steps=[...document.querySelectorAll('.progresso-step')];

 const nav=[...document.querySelectorAll('#progressoNav [data-go]')];

 const back=document.getElementById('backbtn'), next=document.getElementById('nextbtn'), save=document.getElementById('botao_salvar');

 const video=document.getElementById('video'), canvas=document.getElementById('canvas'), preview=document.getElementById('fotoPreview');

 const imagem=document.getElementById('imagem'), ficheiro=document.getElementById('fotoFicheiro'), aviso=document.getElementById('cameraAviso');

 let current=0, stream=null;
 let enviando=false, matriculaRegistada=false;

 const tabelaValores=@json($valoresClasses ?? []);

 const moeda=v=>new Intl.NumberFormat('pt-MZ',{minimumFractionDigits:2,maximumFractionDigits:2}).format(v)+' MT';

 function actualizarValores(){

   const classeId=Number(document.getElementById('classe').value);

   const config=tabelaValores.find(v=>Number(v.classe_id)===classeId);

   const bolsa=Number(document.getElementById('bolsaPercentual').value)||0;

   const extras=[...document.querySelectorAll('.actividade-check:checked')].reduce((a,e)=>a+(Number(e.dataset.valor)||0),0);

   const matricula=config?Number(config.valor_matricula):null;

   const propina=config?Number(config.valor_propina)*(1-bolsa/100):null;

   document.getElementById('valorMatricula').textContent=matricula===null?'Configure a classe':moeda(matricula);

   document.getElementById('valorPropina').textContent=propina===null?'—':moeda(propina);

   document.getElementById('valorActividades').textContent=moeda(extras);

   document.getElementById('valorTotal').textContent=matricula===null?'—':moeda(matricula+propina+extras);

 }

 document.getElementById('classe').addEventListener('change',actualizarValores);

 document.getElementById('bolsaPercentual').addEventListener('change',actualizarValores);

 document.querySelectorAll('.actividade-check').forEach(el=>el.addEventListener('change',actualizarValores));

 actualizarValores();



 function showStep(n){current=n;steps.forEach((s,i)=>s.classList.toggle('active',i===n));nav.forEach((b,i)=>{b.classList.toggle('btn-primary',i===n);b.classList.toggle('btn-outline-primary',i!==n)});back.disabled=n===0;next.classList.toggle('d-none',n===steps.length-1);save.classList.toggle('d-none',n!==steps.length-1);}

 function validateStep(n){

   const $step=$(steps[n]);

   if(!$.fn.validate){alert('jQuery Validate não foi carregado.');return false;}

   let valido=true, primeiro=null;

   $step.find(':input').not(':button,:submit,:hidden').each(function(){

     if(!$(this).valid()){valido=false;if(!primeiro)primeiro=this;}

   });

   if(primeiro){primeiro.focus();}

   return valido;

 }

 back.addEventListener('click',()=>showStep(Math.max(0,current-1)));

 next.addEventListener('click',()=>{if(validateStep(current))showStep(Math.min(steps.length-1,current+1))});

 nav.forEach(b=>b.addEventListener('click',()=>{const target=Number(b.dataset.go);if(target<=current){showStep(target);return}for(let i=current;i<target;i++){if(!validateStep(i)){showStep(i);return}}showStep(target)}));

 async function startCamera(){if(!navigator.mediaDevices?.getUserMedia){aviso.textContent='Câmara indisponível. Use HTTPS/localhost ou escolha uma fotografia.';return}try{stopCamera();stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'},audio:false});video.srcObject=stream;await video.play();aviso.textContent='Câmara activa. Clique em Tirar foto.'}catch(e){aviso.textContent='Não foi possível activar a câmara. Verifique a permissão ou escolha uma fotografia.'}}

 function stopCamera(){if(stream){stream.getTracks().forEach(t=>t.stop());stream=null}video.srcObject=null}

 document.getElementById('iniciarCamera').addEventListener('click',startCamera);

 document.getElementById('pararCamera').addEventListener('click',stopCamera);

 document.getElementById('capture').addEventListener('click',()=>{if(!stream||!video.videoWidth){aviso.textContent='Active a câmara antes de tirar a fotografia.';return}const w=Math.min(video.videoWidth,1000),h=Math.round(video.videoHeight*w/video.videoWidth);canvas.width=w;canvas.height=h;canvas.getContext('2d').drawImage(video,0,0,w,h);imagem.value=canvas.toDataURL('image/jpeg',0.82);ficheiro.value='';preview.src=imagem.value;preview.classList.remove('d-none');aviso.textContent='Fotografia capturada com sucesso.';stopCamera()});

 ficheiro.addEventListener('change',()=>{const file=ficheiro.files[0];if(!file)return;if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>5*1024*1024){ficheiro.value='';aviso.textContent='Escolha JPG, PNG ou WEBP com até 5 MB.';return}imagem.value='';const reader=new FileReader();reader.onload=()=>{preview.src=reader.result;preview.classList.remove('d-none')};reader.readAsDataURL(file);stopCamera()});

 window.addEventListener('pagehide',stopCamera);

 form.addEventListener('submit', async function(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  if (enviando || matriculaRegistada) return;

  for (let i = 0; i < steps.length; i++) {
    if (!validateStep(i)) {
      showStep(i);
      return;
    }
  }

  enviando = true;
  const erros = document.getElementById('progressoErros');
  erros.classList.add('d-none');
  save.disabled = true;
  save.textContent = 'A registar...';

  try {
    const payload = new FormData(form);
    if (ficheiro.files.length) {
      payload.delete('imagem');
      payload.append('foto', ficheiro.files[0]);
    }

    const response = await fetch(form.action, {
      method: 'POST',
      body: payload,
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const result = await response.json();
    if (!response.ok || result.status !== 1) {
      const mensagem = result.errors
        ? Object.values(result.errors).flat().join('\n')
        : (result.message || 'Erro ao registar a matrícula.');
      throw new Error(mensagem);
    }

    matriculaRegistada = true;
    const confirmacao = window.Swal
      ? await Swal.fire({
          icon: 'success',
          title: 'Matrícula registada!',
          text: result.message,
          showCancelButton: true,
          confirmButtonText: 'Emitir Factura',
          cancelButtonText: 'Mais tarde',
          allowOutsideClick: false
        })
      : { isConfirmed: confirm('Matrícula registada. Emitir factura?') };

    if (confirmacao.isConfirmed && result.factura_url) {
      window.location.href = result.factura_url;
    } else if (result.redirect) {
      window.location.href = result.redirect;
    } else {
      window.location.reload();
    }
  } catch (err) {
    erros.textContent = err.message;
    erros.classList.remove('d-none');
    if (window.Swal) {
      await Swal.fire({
        icon: 'error',
        title: 'Não foi possível guardar',
        text: err.message
      });
    }
  } finally {
    enviando = false;
    if (!matriculaRegistada) {
      save.disabled = false;
      save.textContent = 'Matricular';
    }
  }
}, {capture: true});

})();

</script>

@endpush
