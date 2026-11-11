<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Disciplina;
use App\Models\DisciplinaCurriculo;
use App\Models\Matricula;
use App\Models\Curriculo;
use App\Models\AnoAcademico;
use App\Models\Pauta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CurriculoController extends Controller
{
    //
    public function index()
    {

        $classes = Classe::orderBy('id', 'desc')->get();
        $curriculos = Curriculo::all();

        // Retorna a view com os dados carregados
        return view('parametrizacao.curriculo.index', [
            'classes' => $classes,
            'curriculos' => $curriculos,
        ]);

    }

    public function show($classeID)
    {

        $curriculos = DisciplinaCurriculo::where('classe_id', $classeID)->get();
        $classe = Classe::where('id', $classeID)->first();
        $disciplinasNaoAssociadas = Disciplina::whereNotIn('id', function ($query) use ($classeID) {
                        $query->select('disciplina_id')
                            ->from('disciplinas_curriculos')
                            ->where('classe_id', $classeID);
                        })->get();

        // Retorna a view com os dados carregados
        return view('parametrizacao.curriculo.show', [
            'curriculos' => $curriculos,
            'classe'=>$classe,
            'disciplinasNaoAssociadas'=>$disciplinasNaoAssociadas,
        ]);

    }

    public function editClasse($curricloID)
    {
        $curriculo = Curriculo::where('id', $curricloID)->first();
        $classes = Classe::all();

        // Retorna a view com os dados carregados
        return view('parametrizacao.curriculo.editClasse', [
            'curriculo' => $curriculo,
            'classes' => $classes,
        ]);
    }

    public function edit($curricloID)
    {
        $curriculo = DisciplinaCurriculo::where('id', $curricloID)->first();
        $classes = Classe::all();
        $disciplinas = Disciplina::all();

        // Retorna a view com os dados carregados
        return view('parametrizacao.curriculo.edit', [
            'curriculo' => $curriculo,
            'classes' => $classes,
            'disciplinas' => $disciplinas,
        ]);

    }

    public function updateClasse(Request $request)
    {

       try {

            DB::beginTransaction();

            $curriculo = Curriculo::where('id', $request->input('curriculoID'))->first();
            $classe = Classe::where('id', $curriculo->classe_id)->first();

            $curriculo->estado = $request->input('estado');
            $curriculo->classe_id = $request->input('classe');
            $curriculo->tem_exame = $request->input('tem_exame');
            $curriculo->descricao = $request->input('descricao');

            //Ativar Exame nas Classes Actual
            $classe->tem_exame = $request->input('tem_exame');

            if($curriculo->save() && $classe->save()){

              DB::commit();
              return response()->json(['status' => 1, 'message' => 'Curriculo actualizado com sucesso!']);

            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function update(Request $request)
    {

       try {

            DB::beginTransaction();

            $curriculo = DisciplinaCurriculo::where('id', $request->input('curriculoID'))->first();
            $curriculo->estado = $request->input('estado');
            $curriculo->disciplina_id = $request->input('disciplina');
            $curriculo->classe_id = $request->input('classe');
            $curriculo->pauta_ordem = $request->input('ordem_pauta');
            $curriculo->tem_exame = $request->input('tem_exame');
            $curriculo->faz_exame = $request->input('faz_exame');

            if($curriculo->save()){

              DB::commit();
              return response()->json(['status' => 1, 'message' => 'Curriculo actualizado com sucesso!']);

            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function store(Request $request) {

        try {
            DB::beginTransaction();
    
            $disciplinas = $request->input('disciplina', []);
            $classeId = $request->input('classe');

            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
            //Buscar Alunos Que nao pagaram Matricula
            $matriculas = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)->where('classe_id',$classeId)->get();

            foreach ($matriculas as $matricula) {

                foreach ($disciplinas as $disciplinaId) {

                    $pauta = new Pauta();
                    $pauta->ano_academico_id = $anoAcademicoAtivo->id;
                    $pauta->disciplina_id = $disciplinaId;
                    $pauta->classe_id = $classeId;
                    $pauta->aluno_id = $matricula->aluno_id;
                    $pauta->turma_id = $matricula->turma_id;
                    $pauta->escola_id = $matricula->escola_id;
                    $pauta->save();
                }
            }

            foreach ($disciplinas as $disciplinaId) {
                Curriculo::create([
                    'disciplina_id' => $disciplinaId,
                    'classe_id' => $classeId,
                ]);
            }


            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Disciplinas registradas com sucesso!']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }

    }
    
}
