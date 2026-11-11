<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\AlunoController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\ApiEntidadeReferenciaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//WhatsApp
Route::get('/webhook-whatsapp', [WhatsAppController::class, 'verificar']);
Route::post('/webhook-whatsapp', [WhatsAppController::class, 'store']);

//Route::get('/turma/{classeId}/{escolaId}', [TurmaController::class, 'apiTurma']);
Route::get('/classe/{turmaId}', [TurmaController::class, 'apiDisciplina']);
Route::get('/buscar/alunos', [AlunoController::class, 'apiBuscarAlunos']);



/*
Route::prefix('v1')->group(function () {
    // Endpoint Para Validacao de Documento no BIM ENtidade e Referencia
    Route::get('/validacao/bim', [ApiEntidadeReferenciaController::class, 'validarDocumento']);
    // Endpoint Para Registo de Documento no BIM ENtidade e Referencia
    Route::get('/registo/bim', [ApiEntidadeReferenciaController::class, 'registoPagamento']);
});
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
Route::prefix('v1')->group(function () {
    // Endpoint Para Validacao de Documento no BIM ENtidade e Referencia
    Route::get('/validacao/bim', [ApiEntidadeReferenciaController::class, 'validarDocumento']);
    // Endpoint Para Registo de Documento no BIM ENtidade e Referencia
    Route::get('/registo/bim', [ApiEntidadeReferenciaController::class, 'registoPagamento']);

}); */




