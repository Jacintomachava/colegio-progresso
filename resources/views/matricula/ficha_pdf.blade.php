<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Ficha de Matrícula - {{ $matricula->codigo }}</title>
<style>
@page { margin: 28px 35px 42px; }
body { font-family: DejaVu Sans, sans-serif; color: #20312a; font-size: 9px; line-height: 1.4; }
.header { border-bottom: 3px solid #13794b; padding-bottom: 12px; margin-bottom: 15px; }
.school { font-size: 19px; font-weight: bold; color: #12663f; }
.subtitle { color: #53645a; font-size: 10px; }
.doc-title { margin-top: 12px; font-size: 15px; font-weight: bold; }
.photo { float: right; border: 1px solid #cbd6cf; width: 88px; height: 106px; text-align: center; padding: 3px; }
.photo img { width: 84px; height: 100px; object-fit: cover; }
.tag { display: inline-block; border: 1px solid #bed6c7; background: #f0f8f3; padding: 4px 8px; margin: 6px 5px 0 0; }
h2 { color: #12663f; font-size: 11px; border-bottom: 1px solid #bcd9c7; padding-bottom: 5px; margin: 16px 0 8px; text-transform: uppercase; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
td, th { border: 1px solid #dbe4df; padding: 6px 7px; vertical-align: top; word-wrap: break-word; }
th { background: #eff6f1; text-align: left; font-size: 8px; color: #53645a; }
.label { display: block; color: #69776e; font-size: 8px; margin-bottom: 2px; }
.value { font-weight: bold; font-size: 9px; }
.muted { color: #7b877f; }
.note { background: #f4f7f5; padding: 10px; border-left: 3px solid #13794b; }
.sign td { border: none; text-align: center; padding-top: 35px; }
.footer { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 8px; color: #849087; border-top: 1px solid #dbe4df; padding-top: 5px; }
.page-break { page-break-before: always; }
.clear { clear: both; }
</style>
</head>
<body>
@php
    $show = fn($v) => filled($v) ? $v : '—';
    $date = fn($v) => filled($v) ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '—';
    $yesNo = fn($v) => is_null($v) ? 'Não informado' : ((int)$v === 1 ? 'Sim' : 'Não');
    $pai = $familia->get('PAI');
    $mae = $familia->get('MAE');
    $enc = $familia->get('ENCARREGADO');
    $tipo = ($detalhes->tipo_inscricao ?? '') === 'NOVA' ? 'Nova inscrição' : 'Renovação';
@endphp

<div class="header">
    <div class="photo">
        @if($foto)<img src="{{ $foto }}" alt="Fotografia">@else<br><br><span class="muted">FOTOGRAFIA</span>@endif
    </div>
 
    <div class="school">
        {{ mb_strtoupper($escola->nome ?? 'COLÉGIO PROGRESSO') }}
    </div>
    <div class="subtitle">Secretaria • Registo académico</div>
    <div class="doc-title">FICHA DE INSCRIÇÃO / MATRÍCULA</div>
    <div class="tag">Ano lectivo: {{ $show($matricula->ano_lectivo) }}</div>
    <div class="tag">Matrícula: {{ $matricula->codigo }}</div>
    <div class="tag">{{ $tipo }}</div>
    <div class="clear"></div>
</div>
<h2>01 · Identificação do aluno</h2>
<table>
<tr><td colspan="3"><span class="label">Nome completo</span><span class="value">{{ $show($matricula->aluno_nome) }}</span></td><td><span class="label">Código do aluno</span><span class="value">{{ $show($matricula->codigo_aluno) }}</span></td></tr>
<tr><td><span class="label">Nascimento</span><span class="value">{{ $date($matricula->data_nascimento) }}</span></td><td><span class="label">Sexo</span><span class="value">{{ $show($matricula->genero) }}</span></td><td><span class="label">Nacionalidade</span><span class="value">{{ $show($detalhes->nacionalidade ?? null) }}</span></td><td><span class="label">Classe</span><span class="value">{{ $show($matricula->classe_nome) }}</span></td></tr>
<tr><td><span class="label">Documento</span><span class="value">{{ $show($matricula->tipo_documento) }}</span></td><td colspan="2"><span class="label">Número</span><span class="value">{{ $show($matricula->numero_documento) }}</span></td><td><span class="label">Data de emissão</span><span class="value">{{ $date($matricula->data_emissao_documento) }}</span></td></tr>
<tr><td colspan="4"><span class="label">Residência</span><span class="value">{{ $show($matricula->endereco) }}</span></td></tr>
</table>
<h2>02 · Filiação</h2>
<table>
<tr><th width="50%">PAI</th><th width="50%">MÃE</th></tr>
@foreach(['nome'=>'Nome completo','nacionalidade'=>'Nacionalidade','formacao'=>'Formação académica','profissao'=>'Profissão','local_trabalho'=>'Local de trabalho','telefone'=>'Telemóvel','email'=>'Email','residencia'=>'Residência'] as $campo=>$rotulo)
<tr><td><span class="label">{{ $rotulo }}</span><span class="value">{{ $show($pai->$campo ?? null) }}</span></td><td><span class="label">{{ $rotulo }}</span><span class="value">{{ $show($mae->$campo ?? null) }}</span></td></tr>
@endforeach
</table>
<h2>03 · Encarregado de educação</h2>
<table>
<tr><td colspan="2"><span class="label">Nome</span><span class="value">{{ $show($enc->nome ?? null) }}</span></td><td><span class="label">Grau de parentesco</span><span class="value">{{ $show($enc->parentesco ?? null) }}</span></td></tr>
<tr><td><span class="label">Nacionalidade</span><span class="value">{{ $show($enc->nacionalidade ?? null) }}</span></td><td><span class="label">Formação</span><span class="value">{{ $show($enc->formacao ?? null) }}</span></td><td><span class="label">Profissão</span><span class="value">{{ $show($enc->profissao ?? null) }}</span></td></tr>
<tr><td><span class="label">Telemóvel principal</span><span class="value">{{ $show($enc->telefone ?? null) }}</span></td><td><span class="label">Telemóvel alternativo</span><span class="value">{{ $show($enc->telefone2 ?? null) }}</span></td><td><span class="label">Email</span><span class="value">{{ $show($enc->email ?? null) }}</span></td></tr>
<tr><td colspan="2"><span class="label">Residência</span><span class="value">{{ $show($enc->residencia ?? null) }}</span></td><td><span class="label">Local de trabalho</span><span class="value">{{ $show($enc->local_trabalho ?? null) }}</span></td></tr>
</table>
<h2>04 · Outras informações</h2>
<table>
<tr><td><span class="label">Com quem vive</span><span class="value">{{ $show($detalhes->vive_com ?? null) }}</span></td><td><span class="label">Escola anterior</span><span class="value">{{ $show($detalhes->escola_anterior ?? null) }}</span></td></tr>
</table>
<div class="page-break"></div>
<h2>05 · Saúde e segurança</h2>
<table>
<tr><td width="35%">Condição de saúde / acompanhamento</td><td width="13%">{{ $yesNo($saude->condicao ?? null) }}</td><td>{{ $show($saude->condicao_descricao ?? null) }}</td></tr>
<tr><td>Medicação regular</td><td>{{ $yesNo($saude->medicacao ?? null) }}</td><td>{{ $show($saude->medicacao_descricao ?? null) }}</td></tr>
<tr><td>Alergias ou restrições alimentares</td><td>{{ $yesNo($saude->alergia ?? null) }}</td><td>{{ $show($saude->alergia_descricao ?? null) }}</td></tr>
<tr><td>Atestado médico apresentado</td><td colspan="2">{{ $yesNo($saude->atestado ?? null) }}</td></tr>
<tr><td>Unidade de saúde preferencial</td><td colspan="2">{{ $show($saude->unidade_preferencial ?? null) }}</td></tr>
</table>
<h2>06 · Emergência e recolha do aluno</h2>
<table>
<tr><td><span class="label">Contacto alternativo de emergência</span><span class="value">{{ $show($detalhes->emergencia_nome ?? null) }}</span></td><td><span class="label">Telemóvel</span><span class="value">{{ $show($detalhes->emergencia_telefone ?? null) }}</span></td></tr>
</table>
<table style="margin-top: 8px;"><tr><th>Pessoas autorizadas a recolher o aluno</th><th>Telemóvel</th></tr>
@forelse($autorizados as $autorizado)
<tr><td>{{ $show($autorizado->nome) }}</td><td>{{ $show($autorizado->telefone) }}</td></tr>
@empty
<tr><td colspan="2">Nenhuma pessoa adicional indicada.</td></tr>
@endforelse
</table>
<h2>07 · Condições financeiras e serviços</h2>
<table>
<tr><td><span class="label">Modalidade de pagamento</span><span class="value">{{ match($detalhes->modalidade_pagamento ?? '') { 'MENSAL'=>'Mensal', 'TRES_PRESTACOES'=>'3 prestações', 'ANUAL'=>'Anual', default=>'—' } }}</span></td><td><span class="label">Bolsa registada</span><span class="value">{{ number_format((float)($matricula->bolsa_percentual ?? 0), 0) }}%</span></td></tr>
<tr><td colspan="2"><span class="label">Serviços / actividades seleccionados</span><span class="value">{{ $atividades->pluck('nome')->join(', ') ?: 'Nenhuma actividade seleccionada' }}</span></td></tr>
</table>
<h2>08 · Protecção de dados e declaração</h2>
<div class="note">Os dados pessoais recolhidos destinam-se à gestão escolar, ao acompanhamento, à segurança e ao bem-estar do aluno. O Colégio compromete-se a tratar estas informações com confidencialidade, restringindo o acesso ao pessoal autorizado e respeitando as obrigações legais aplicáveis.</div>
<p style="margin-top: 14px;">Declaro que as informações prestadas nesta ficha são verdadeiras e completas, comprometendo-me a comunicar ao Colégio quaisquer alterações relevantes, especialmente as relacionadas com os contactos, a saúde, a segurança e o acompanhamento do aluno.</p>
<table class="sign"><tr><td>_________________________________<br>Encarregado de Educação<br><br>Data: ____ / ____ / ________</td><td>_________________________________<br>Director(a) da Escola<br><br>Data: ____ / ____ / ________</td></tr></table>
<div class="footer">{{ $escola->nome ?? 'Colégio Progresso' }} · Ficha de matrícula {{ $matricula->codigo }} · Documento gerado em {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
