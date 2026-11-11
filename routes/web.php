<?php

use App\Http\Controllers\AlunoController;
use App\Http\Controllers\AlunoMatriculaController;
use App\Http\Controllers\AlunoPautaController;
use App\Http\Controllers\AlunoPropinaController;
use App\Http\Controllers\AnoAcademicoController;
use App\Http\Controllers\Auth\AuthUserController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\CriarContaController;
use App\Http\Controllers\CurriculoController;
use App\Http\Controllers\DashbordController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\EscolaController;
use App\Http\Controllers\EstatisticaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\MensagemController;
use App\Http\Controllers\MesController;
use App\Http\Controllers\MetodoPagamentoController;
use App\Http\Controllers\NotasController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\ProfessorTurmaController;
use App\Http\Controllers\PropinaController;
use App\Http\Controllers\ReceitaDespesaController;
use App\Http\Controllers\SecretariaPautaController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\TaxaController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\TurmaProfessorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TempleteSMSController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\ApiEntidadeReferenciaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [IndexController::class, 'index'])->name('index');

Route::get('/', [IndexController::class, 'index'])->name('raiz');

Route::get('/api/turma/{classeId}/{escolaId}', [TurmaController::class, 'apiTurma']);

Route::get('/politica-de-privacidade', function () {
    return view('leading.politicas.index');
})->name('politicas');

Route::get('/logar', function () {
    return view('login');
})->name('logar');

Route::get('/termos-de-servico', function () {
    return view('leading.termos.index');
})->name('termos');

// Endpoint Para Validacao de Documento no BIM ENtidade e Referencia
Route::get('/api/v1/validacao/bim', [ApiEntidadeReferenciaController::class, 'validarDocumento']);
// Endpoint Para Registo de Documento no BIM ENtidade e Referencia
Route::get('/api/v1/registo/bim', [ApiEntidadeReferenciaController::class, 'registoPagamento']);

// Autenticacao
Route::post('/autenticar', [AuthUserController::class, 'login'])->name('autenticacao');
Route::get('/matricula/factura/{factura}', [MatriculaController::class, 'facturaPDF'])->name('matricula.facturaPDF');
Route::get('/propina/factura/{alunoCodigo}', [PropinaController::class, 'facturaPDF'])->name('propina.factura');
Route::get('/matricula/recibo/{factura}', [MatriculaController::class, 'reciboPDF'])->name('matricula.reciboPDF');
Route::get('/propina/recibo/{alunoCodigo}', [PropinaController::class, 'reciboPDF'])->name('propina.recibo');
Route::get('/recibo/propina/{referencia}', [PropinaController::class, 'reciboPropinaPDF'])->name('propinaRecibo.recibo');
//Ciclo Academico
Route::get('/ciclo/academico', [MatriculaController::class, 'ciclo'])->name('ciclo.academico');
Route::post('/ciclo/academico/update', [MatriculaController::class, 'cicloUpdate'])->name('ciclo.ano.store');

// Recuperar Senha
Route::get('/recuperar/senha', [UserController::class, 'recuperarSenhaIndex'])->name('recuperarSenha.index');
Route::post('/nova/senha', [UserController::class, 'recuperarSenhaTelefone'])->name('recuperarSenha.telefone');
Route::get('/repor/senha/{user_id}', [UserController::class, 'reporSenha'])->name('repor.senha');

// Pauta
Route::get('/aluno/pauta/{alunoCodigo}', [AlunoController::class, 'alunoPautaPDF'])->name('aluno.pautaPDF');

Auth::routes();

// WhatsApp
Route::get('/webhook-whatsapp/{codigo}', [WhatsAppController::class, 'store'])->name('webhook.whatsapp');
Route::get('/listar/reclamacoes', [WhatsAppController::class, 'reclamacoes'])->name('listar.reclamacoes');

// Responder WhatsApp
Route::post('/reclamacao/responder', [WhatsAppController::class, 'responder'])->name('reclamacao.responder');
Route::post('/responder/chat', [WhatsAppController::class, 'responderChat'])->name('responder.chat');

// Rotas que exigem Autenticacao
Route::middleware(['auth'])->group(function () {
    Route::get('/ano/academico', [AnoAcademicoController::class, 'index'])->name('ano.academico');
    Route::get('/criar/anoAcademico', [AnoAcademicoController::class, 'create'])->name('create.academico');

    // Pre-Registo
    Route::get('/conta/associacao', [CriarContaController::class, 'associacao'])->name('conta.associacao');
    // Parametrizacao
    // AnoAcademica
    Route::get('/ano/academico', [AnoAcademicoController::class, 'index'])->name('ano.academico');
    Route::get('/criar/anoAcademico', [AnoAcademicoController::class, 'create'])->name('create.academico');
    // Route::post('/anos/academico', [AnoAcademicoController::class, 'store'])->name('academico1.store');
    Route::post('/academico1/store', [AcademicoController::class, 'store'])->name('academico1.store');
    Route::get('/edit/taxa/{id}', [AnoAcademicoController::class, 'edit'])->name('taxa.edit');
    Route::post('/anos/academico', [AnoAcademicoController::class, 'update'])->name('academico.update');
    Route::post('/activar/anos-academicos/{id}', [AnoAcademicoController::class, 'ativar'])->name('anos.academicos.ativar');

    // Turma
    Route::get('/turma', [TurmaController::class, 'index'])->name('turma.index');
    Route::get('/criar/turma', [TurmaController::class, 'create'])->name('turma.create');
    Route::post('/gravar/turma', [TurmaController::class, 'store'])->name('turma.store');
    Route::get('/alunos/turma/{turma}', [TurmaController::class, 'apiAlunoSemTurma'])->name('alunos.turma');
    Route::get('/atribuir/turma', [TurmaController::class, 'atribuirTurma'])->name('atribuir.turma');
    Route::get('/alunos/turma/{turma}', [TurmaController::class, 'alunosTurma'])->name('alunos.turma');
    Route::post('/atribuir/turma', [TurmaController::class, 'atribuirTurmaAluno'])->name('atribuirTurma.store');
    Route::get('/editar/turma/{turmaID}', [TurmaController::class, 'edit'])->name('turma.edit');
    Route::post('/update/turma', [TurmaController::class, 'update'])->name('turma.update');

    // Taxa
    Route::get('/taxa', [TaxaController::class, 'index'])->name('taxa.index');
    Route::get('/criar/taxa', [TaxaController::class, 'create'])->name('taxa.create');
    Route::post('/gravar/taxa', [TaxaController::class, 'store'])->name('taxa.store');

    // Disciplinas
    Route::get('/disciplinas', [DisciplinaController::class, 'index'])->name('disciplina.index');
    Route::get('/criar/disciplina', [DisciplinaController::class, 'create'])->name('disciplina.create');
    Route::post('/gravar/disciplina', [DisciplinaController::class, 'store'])->name('disciplina.store');

    // Classes
    Route::get('/classes', [ClasseController::class, 'index'])->name('classe.index');

    // Mes
    Route::get('/metodo/pagamento', [MetodoPagamentoController::class, 'index'])->name('metodo.index');

    // Metodo Pagamento
    Route::get('/mes', [MesController::class, 'index'])->name('mes.index');

    // Curriculo
    Route::get('/curriculo', [CurriculoController::class, 'index'])->name('curriculo.index');
    Route::get('/curriculo/{classeID}', [CurriculoController::class, 'show'])->name('curriculo.show');
    Route::get('/editar/curriculo/{curriculoID}', [CurriculoController::class, 'edit'])->name('curriculo.edit');
    Route::post('/curriculo', [CurriculoController::class, 'store'])->name('curriculo.store');
    Route::post('/curriculo/update', [CurriculoController::class, 'update'])->name('curriculo.update');
    // Editar Classe de Curriculo
    Route::get('/curriculo/classe/{classeID}', [CurriculoController::class, 'editClasse'])->name('curriculoClasse.edit');
    Route::post('/curriculo/classe', [CurriculoController::class, 'updateClasse'])->name('curriculoClasse.update');

    // SMS
    Route::get('/notificacoes', [SMSController::class, 'index'])->name('sms.index');

    // Matriculas
    Route::get('/matriculas', [MatriculaController::class, 'index'])->name('matricula.index');
    Route::get('/matricula', [MatriculaController::class, 'create'])->name('matricula.create');
    Route::post('/actualizar/matricula', [MatriculaController::class, 'update'])->name('matricula.update');
    Route::get('/matricula/{codigo}', [MatriculaController::class, 'edit'])->name('matricula.edit');
    Route::post('/matricula', [MatriculaController::class, 'store'])->name('matricula.store');
    Route::post('/renovacao', [MatriculaController::class, 'renovacao'])->name('renovacao.store');
    Route::get('/pagamento/matricula/{codigo}', [MatriculaController::class, 'pagamentoMatricula'])->name('pagamentoMatricula.index');
    Route::post('/pagar/matricula', [MatriculaController::class, 'pagarMatricula'])->name('pagarMatricula.store');
    Route::get('/relatorio/matriculados', [MatriculaController::class, 'relatorioMatriculados'])->name('relatorio.matriculado');
    Route::post('/transferencia/aluno', [MatriculaController::class, 'transferencia'])->name('transferencia.aluno');
    Route::get('/alunos/transferidos', [MatriculaController::class, 'alunosTransferidos'])->name('alunos.transferidas');

    //Mensagens Periodicas
    Route::get('/mensagem/templete', [TempleteSMSController::class, 'create'])->name('SMSperidica.create');
    Route::post('/mensagem/templete', [TempleteSMSController::class, 'store'])->name('SMSperidica.store');
    Route::put('/mensagens-periodicas/{id}/toggle', [TempleteSMSController::class, 'toggleEstado'])->name('mensagens.toggle');

    //Envio de Referencia por SMS
    Route::get('/matricula/aluno/{codigo}', [MatriculaController::class, 'referenciaSMS'])->name('matriculaReferenciaSMS.index');

    Route::get('/mpesa/matricula/{codigo}', [MatriculaController::class, 'pagamentoMatriculaMpesa'])->name('pagamentoMatriculaMpesa.index');
    Route::post('/pagar/matricula/mpesa', [MatriculaController::class, 'pagarMatriculaMpesa'])->name('pagarMatriculaMpesa.store');

    Route::post('/aluno/mudar-foto', [AlunoController::class, 'mudarFoto']);

    // Alunos
    Route::get('/alunos', [AlunoController::class, 'index'])->name('aluno.index');
     Route::get('/alunos/pautas', [AlunoController::class, 'indexPautas'])->name('aluno.indexPautas');
    Route::get('/aluno/{codigo}', [AlunoController::class, 'show'])->name('aluno.show');
    Route::post('/aluno/mudar-classe', [AlunoController::class, 'mudarClasse'])->name('mudar.classe');
    Route::get('/editar/aluno/{codigo}', [AlunoController::class, 'edit'])->name('aluno.edit');
    Route::get('/home/aluno', [AlunoController::class, 'home'])->name('home.aluno');
    Route::get('/aluno/cartao/{codigo}', [AlunoController::class, 'cartao'])->name('aluno.cartao');
    Route::post('/editar/aluno', [AlunoController::class, 'update'])->name('aluno.update');
    Route::get('/retirar/aluno/{alunoID}', [AlunoController::class, 'retirarAluno'])->name('retirar.aluno');
    Route::get('/retirar/pauta/{alunoID}', [AlunoController::class, 'retirarPauta'])->name('retirar.aluno');
    Route::get('/mudar/telefone/{codigo}', [AlunoController::class, 'mudarTelefone'])->name('mudar.telefone');
    Route::post('/mudar/telefone', [AlunoController::class, 'mudarTelefoneEncarregado'])->name('mudarEncarregado.telefone');

    // Propinas
    Route::get('/propina', [PropinaController::class, 'index'])->name('propina.index');
    Route::get('/propina/{alunoID}', [PropinaController::class, 'show'])->name('propina.show');
    Route::get('/aluno/propina/{alunoID}', [PropinaController::class, 'showAluno'])->name('propina.aluno');
    Route::post('/propina/factura', [PropinaController::class, 'store'])->name('propina.store');
    Route::get('/pagar/propina/{codigo}', [PropinaController::class, 'pagarPropina'])->name('pagarPropina.index');
    Route::get('/corrigir/propina/{codigo}', [PropinaController::class, 'corrigirPropina'])->name('corrigirPropina.index');
    Route::post('/pagar/propina', [PropinaController::class, 'pagarPropinaStore'])->name('pagarPropina.store');
    Route::post('/corrigir/propina', [PropinaController::class, 'corrigirPropinaStore'])->name('corrigirPropina.store');
    Route::get('/relatorio/propinas', [PropinaController::class, 'relatorioPropinas'])->name('relatorio.propinas');
    Route::post('/propina/cancelar/pagamento/{referencia}', [PropinaController::class, 'cancelarPagamentoPropina'])->name('cancelarPagamento.propina');
    Route::get('/referencia/propina/{codigo}', [PropinaController::class, 'referenciaPropina'])->name('referencia.propina');

    //Envio de Referencia por SMS
    Route::get('/mensalidade/sms/{codigo}/{mesId}', [PropinaController::class, 'referenciaSMS'])->name('propinaReferenciaSMS.index');

    Route::get('/propina/mpesa/{codigo}', [PropinaController::class, 'pagarPropinaMpesa'])->name('pagarPropinaMpesa.index');
    Route::post('/pagar/propina/mpesa', [PropinaController::class, 'pagarPropinaMpesaStore'])->name('pagarPropinaMpesa.store');

    // Pauta
    Route::get('/pauta', [SecretariaPautaController::class, 'index'])->name('pauta.index');
    Route::get('/pauta/{alunoID}', [SecretariaPautaController::class, 'show'])->name('pauta.show');
    Route::post('/finalizar/pauta/{turmaID}', [SecretariaPautaController::class, 'finalizarPauta'])->name('finalizar.pauta');
    Route::post('/buscar/pauta', [SecretariaPautaController::class, 'buscar'])->name('pauta.buscar');
    Route::get('/pauta/aluno/{codigo}/{ano}', [SecretariaPautaController::class, 'pautaAlunoPDF'])->name('pauta.alunoPDF');
    Route::get('/deck/{turma}/{disciplina}', [AlunoPautaController::class, 'deckPDF'])->name('deck.professor');
    Route::get('/pauta/turma/{turma}/{ano}', [AlunoPautaController::class, 'pautaTurma'])->name('pauta.turma');
    Route::get('/pautaPDF/turma/{turma}/{ano}', [AlunoPautaController::class, 'pautaTurmaPDF'])->name('pautaPDF.turma');
    Route::get('/pautas/turmas/{turma}/{ano}', [AlunoPautaController::class, 'pautasTurmas'])->name('pautas.turmas');
    //Pauta Exame
    Route::get('/pauta/exame/{classe}/{ano}', [AlunoPautaController::class, 'pautaExame'])->name('pauta.exame');
    Route::get('/pautaPDF/exame/{classe}/{ano}', [AlunoPautaController::class, 'pautaPDFExame'])->name('pautaPDF.exame');
    Route::post('/pauta/exame', [AlunoPautaController::class, 'salvarNotasExame'])->name('salvar.exame');
    Route::get('/pauta/exame/{classe}/{disciplina}/{ano}', [AlunoPautaController::class, 'putasExames'])->name('pautas.exames');
    Route::get('/exame/{codigo}/{ano}', [AlunoPautaController::class, 'putaExameAluno'])->name('pautaExame.aluno');
    Route::get('/aluno/exame/{codigo}/{ano}', [AlunoPautaController::class, 'putaExameAlunoPDF'])->name('pautaExamePDF.aluno');
    Route::post('/pauta/validar/{classe}/{ano}', [AlunoPautaController::class, 'validarPauta'])->name('pauta.validar');
    Route::post('/pauta/invalidar/{classe}/{ano}', [AlunoPautaController::class, 'InvalidarPauta'])->name('pauta.invalidar');

    // Professor
    Route::get('/home/professor', [ProfessorController::class, 'home'])->name('home.professor');
    Route::get('/professores', [ProfessorController::class, 'index'])->name('professor.index');
    Route::get('/professor', [ProfessorController::class, 'create'])->name('professor.create');
    Route::post('/professor', [ProfessorController::class, 'store'])->name('professor.store');
    Route::post('/actualizar/professor', [ProfessorController::class, 'update'])->name('professor.update');
    Route::get('/professores/{codigo}', [ProfessorController::class, 'edit'])->name('professor.edit');
    Route::get('/meu/arquivo', [ProfessorController::class, 'arquivo'])->name('meu.arquivo');

    // Professor Turma
    Route::get('/professor/turma', [ProfessorTurmaController::class, 'index'])->name('professorTurma.index');
    Route::post('/professor/turmas', [ProfessorTurmaController::class, 'store'])->name('professorTurma.store');
    Route::get('/professor/turma/{turmaID}', [ProfessorTurmaController::class, 'show'])->name('professorTurma.show');
    Route::post('/retirar/disciplina/{turmaProfessorDisciplinaID}', [ProfessorTurmaController::class, 'retirarDisciplinaProfessor'])->name('retirar.disciplina');
    Route::post('/professor/turma/disciplina', [ProfessorTurmaController::class, 'storeDisciplina'])->name('professor.disciplinas');
    Route::post('/retirar/turma/{turmaProfessorID}', [ProfessorTurmaController::class, 'retirarTurmaProfessor'])->name('retirar.turma');
        Route::get('/retirar/professor/{professorID}', [ProfessorController::class, 'retirarProfessor'])->name('retirar.professor');

    // Lancar Notas
    Route::get('/turma/{turmaID}/{ano}', [NotasController::class, 'show'])->name('notas.show');
    Route::post('/buscar/disciplina', [NotasController::class, 'buscar'])->name('notas.buscar');
    Route::post('/lancar/notas', [NotasController::class, 'store'])->name('notas.store');
    Route::get('/notas/{classe}/{disciplina}/{trimestre}/{ano}', [NotasController::class, 'notas'])->name('lancar.nota');
    Route::get('/nota/{classe}/{disciplina}/{trimestre}/{ano}', [NotasController::class, 'notas1'])->name('lancar.notas');
    Route::post('/lancar/teste', [NotasController::class, 'update'])->name('notas.update');
    

    // Funcionarios
    Route::get('/funcionarios', [FuncionarioController::class, 'index'])->name('funcionario.index');
    Route::get('/funcionario', [FuncionarioController::class, 'create'])->name('funcionario.create');
    Route::get('/funcionario/{codigo}', [FuncionarioController::class, 'show'])->name('funcionario.show');
    Route::get('/edit/funcionario/{codigo}', [FuncionarioController::class, 'edit'])->name('funcionario.edit');
    Route::post('/funcionario', [FuncionarioController::class, 'store'])->name('funcionario.store');
    Route::post('/editar/funcionario', [FuncionarioController::class, 'update'])->name('funcionario.update');
    Route::get('/retirar/funcionario/{funcionarioID}', [FuncionarioController::class, 'retirarFuncionario'])->name('retirar.funcionrio');
    Route::post('/funcionario/atualizar-avatar',[FuncionarioController::class, 'atualizarAvatar'])->name('funcionario.avatar.update');

    // Financas
    Route::get('/receitas/despesas', [ReceitaDespesaController::class, 'index'])->name('receitaDespesa.index');

    // User
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/{codigo}', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/users/editar', [UserController::class, 'update'])->name('user.update');
    // Alterar Senha
    Route::get('/alterar/senha', [UserController::class, 'senhaIndex'])->name('senha.index');
    Route::post('/editar/senha', [UserController::class, 'senhaUpdate'])->name('senha.update');

    // Mudar Escola
    Route::get('/mudar/escola', [UserController::class, 'mudarEscola'])->name('mudarEscola.index');
    Route::post('/editar/escola', [UserController::class, 'escolaUpdate'])->name('mudarEscola.update');
    // Minha Conta
    Route::get('/minha/conta', [UserController::class, 'minhaConta'])->name('minhaConta.index');
    Route::post('/editar/conta', [UserController::class, 'minhaContaUpdate'])->name('minhaContaUpdate.update');

    // Escola
    Route::get('/escolas', [EscolaController::class, 'index'])->name('escola.index');
    Route::get('/escola', [EscolaController::class, 'create'])->name('escola.create');
    Route::get('/escola/{codigo}', [EscolaController::class, 'edit'])->name('escola.edit');
    Route::post('/escola/editar', [EscolaController::class, 'update'])->name('escola.update');
    Route::post('/escola', [EscolaController::class, 'store'])->name('escola.store');

    // Pagamentos
    Route::post('/cancelar/factura/{factura}', [PagamentoController::class, 'update'])->name('cancelarFactura.update');
    Route::get('/pagamentos', [PagamentoController::class, 'index'])->name('pagamento.index');
    Route::get('/estatistica/pagamento', [PagamentoController::class, 'show'])->name('pagamento.show');
    Route::get('/todos/pagamentos', [PagamentoController::class, 'indexPagamento'])->name('indexPagamento.index');

    // Estatistica
    Route::get('/estatistica/financeira', [EstatisticaController::class, 'index'])->name('estatisticaFinanceira.index');
    Route::get('/estatistica/{escola}', [EstatisticaController::class, 'show'])->name('estatisticaFinanceira.show');

    // Role Aluno
    Route::get('/aluno/matricula/aluno', [AlunoMatriculaController::class, 'index'])->name('alunoMatricula.index');
    Route::get('/meus/dados', [AlunoMatriculaController::class, 'show'])->name('matriculaAluno.show');
    Route::get('/cocurricular/{alunoCodigo}', [AlunoMatriculaController::class, 'edit'])->name('cocurricular.edit');
    Route::post('/renovacao/cocurricular/', [AlunoMatriculaController::class, 'update'])->name('cocurricular.update');

    Route::get('/minhas/propinas', [AlunoPropinaController::class, 'show'])->name('propinaAluno.show');

    Route::get('/minha/pauta', [AlunoPautaController::class, 'show'])->name('pautaAluno.show');

    // Role Professor
    Route::get('/minhas/turmas', [TurmaProfessorController::class, 'index'])->name('minhasTurmas.index');
    Route::get('/minhas/turma', [TurmaProfessorController::class, 'notas'])->name('minhasTurmas.notas');
    Route::get('/arquivo/pauta/{ano}', [TurmaProfessorController::class, 'arquivoPauta'])->name('arquivo.pautas');

    // Dashbord
    Route::get('/home', [DashbordController::class, 'index'])->name('home.index');

    Route::get('/mensalidades/detalhes',[DashbordController::class, 'detalhesMensalidades']);
    Route::get('/mensalidades/imprimir',[DashbordController::class, 'imprimirMensalidades']);

    // Mensagem
    Route::get('/mensagem', [MensagemController::class, 'index'])->name('mensagem.index');
    Route::get('/mensagem/{contacto}', [MensagemController::class, 'show'])->name('mensagem.show');
    Route::post('/mensagem', [MensagemController::class, 'store'])->name('mensagem.store');
    Route::post('/enviar/mensagem', [MensagemController::class, 'storeSMS'])->name('mensagem.storeSMS');
    Route::get('/escrever/mensagem', [MensagemController::class, 'create'])->name('mensagem.create');
    Route::get('/notificacao/saldo', [MensagemController::class, 'notificacaoSaldo'])->name('notificacao.saldo');
    Route::post('/comprar/credito', [MensagemController::class, 'storeCompraSMS'])->name('mensagem.storeCompraSMS');
    Route::post('/saldo/limite',[MensagemController::class, 'atualizarLimite'])->name('saldo.limite.update');
    Route::post('/saldo/contacto',[MensagemController::class, 'guardarContacto'])->name('saldo.contacto.store');
    Route::delete('/saldo/contacto/{id}',[MensagemController::class, 'apagarContacto'])->name('saldo.contacto.destroy');

});
