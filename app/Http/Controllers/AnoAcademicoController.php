<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Turma;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnoAcademicoController extends Controller
{
    //
    public function index()
    {
        $anosAcademicos = AnoAcademico::orderBy('id', 'desc')->get();
        $taxas = Taxa::orderBy('id', 'desc')->get();
        $turmas = Turma::orderBy('id', 'desc')->get();

         // Retorna a view com os dados carregados
         return view('parametrizacao.anoAcademico.index', [
            'anosAcademicos' => $anosAcademicos,
            'taxas' => $taxas,
            'turmas' => $turmas,
        ]);

    }

    public function create()
    {
        // Retorna a view com os dados carregados
        return view('parametrizacao.anoAcademico.create', [
        ]);

    }

    public function edit($anoAcademicoID)
    {
        $anoAcademico = AnoAcademico::where('id', $anoAcademicoID)->first();
        // Retorna a view com os dados carregados
        return view('parametrizacao.anoAcademico.edit', [
            'anoAcademico' => $anoAcademico,
        ]);

    }

    public function ativar($id)
    {

        try {
            // Desativar todos os anos acadêmicos
            AnoAcademico::query()->update(['estado' => 0]);

            // Ativar o ano acadêmico selecionado
            $anoAcademico = AnoAcademico::findOrFail($id);
            $anoAcademico->estado = 1;
            $anoAcademico->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Ano acadêmico ativado com sucesso!'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao ativar o ano acadêmico.'
            ]);
        }
    }

    public function store(Request $request){
        
        try {

         DB::beginTransaction();

            //Faturacao da Encomenda
            $anoAcademico = new AnoAcademico();
            $anoAcademico->nome = $request->input('nome');
            $anoAcademico->ano = $request->input('ano');
            $anoAcademico->data_inicio_renovacao = $request->input('data_inicio_renovacao'); 
            $anoAcademico->data_fim_renovacao = $request->input('data_fim_renovacao'); 
            $anoAcademico->data_fim_lancamento_pauta = $request->input('data_fim_lancamento_pauta'); 
            $anoAcademico->data_fim_matricula = $request->input('data_fim_matricula'); 
            $anoAcademico->sms = $request->input('sms');
            $anoAcademico->trimestre = $request->input('trimestre');
            $anoAcademico->pauta_finalizada = $request->input('resultado');
            $anoAcademico->estado = false; 

            if($anoAcademico->save()){

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Ano Lectivo Registado com Sucesso!']);

            }
    

        } catch (\Exception $e) {

            DB::rollBack();
            //$errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request){
        
        try {

         DB::beginTransaction();

            //Faturacao da Encomenda
            $anoAcademico =AnoAcademico::where('id',$request->input('id'))->first();
            $anoAcademico->nome = $request->input('nome');
            $anoAcademico->ano = $request->input('ano');
            $anoAcademico->data_inicio_renovacao = $request->input('data_inicio_renovacao'); 
            $anoAcademico->data_fim_renovacao = $request->input('data_fim_renovacao'); 
            $anoAcademico->data_fim_lancamento_pauta = $request->input('data_fim_lancamento_pauta'); 
            $anoAcademico->data_fim_matricula = $request->input('data_fim_matricula'); 
            $anoAcademico->estado = true; 
            $anoAcademico->sms = $request->input('sms');
            $anoAcademico->pauta_finalizada = $request->input('resultado');
            $anoAcademico->trimestre = $request->input('trimestre');

            if($anoAcademico->save()){

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Ano Lectivo Actualizado com Sucesso!']);

            }
    

        } catch (\Exception $e) {

            DB::rollBack();
            //$errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
