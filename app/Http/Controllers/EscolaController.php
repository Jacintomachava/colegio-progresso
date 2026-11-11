<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Turma;
use App\Models\Classe;
use App\Models\Periodo;
use App\Models\DisciplinaCurriculo;
use App\Models\Escola;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class EscolaController extends Controller
{

    public function index()
    {
        $escolas = Escola::orderBy('id', 'desc')->get();

         // Retorna a view com os dados carregados
         return view('parametrizacao.escola.index', [
            'escolas' => $escolas,
        ]);

    }

    public function create()
    {
        // Retorna a view com os dados carregados
        return view('parametrizacao.escola.create', [

        ]);

    }

    public function edit($id)
    {
        $escola = Escola::where('id', $id)->first();
        // Retorna a view com os dados carregados
        return view('parametrizacao.escola.edit', [
                'escola' => $escola,
        ]);

    }


    public function store(Request $request){
        
        try {

         DB::beginTransaction();

            $request->validate([
                'nome' => 'required|string|max:255',
                'tipo' => 'nullable|string|max:100',
                'telefone1' => 'nullable|string|max:30',
                'telefone2' => 'nullable|string|max:30',
                'codigo' => 'required|string|max:50',
                'slogan' => 'nullable|string|max:255',
                'endereco' => 'nullable|string|max:255',
                'imagem' => [
                    'nullable',
                    File::image()
                        ->types(['jpg', 'jpeg', 'png', 'webp'])
                        ->max('2mb')
                ]
            ]);

            //Faturacao da Encomenda
            $escola = new Escola();
            $escola->nome = $request->input('nome');
            $escola->tipo = $request->input('tipo');
            $escola->telefone= $request->input('telefone1'); 
            $escola->telefone2 = $request->input('telefone2');
            $escola->codigo = $request->input('codigo'); 
            $escola->slogan  = $request->input('slogan'); 
            $escola->endereco  = $request->input('endereco');
            
            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                // Obter a extensão original do arquivo
                $extension = $file->getClientOriginalExtension();
                // Gerar um nome único para o arquivo mantendo a extensão original
                $fileName = time() . '.' . $extension;
                // Caminho onde a imagem será armazenada
                $path = public_path('logotipo');
                // Salvar a imagem no diretório especificado
                $file->move($path, $fileName);
                // Atualizar o campo avatar no banco de dados
                $escola->logotipo = $fileName;
            }

            if($escola->save()){

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Escola Registado com Sucesso!']);

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
            $escola = Escola::where('id',$request->input('id'))->first();
            $escola->nome = $request->input('nome');
            $escola->tipo = $request->input('tipo');
            $escola->telefone= $request->input('telefone1'); 
            $escola->telefone2 = $request->input('telefone2');
            $escola->codigo = $request->input('codigo'); 
            $escola->slogan  = $request->input('slogan'); 
            $escola->endereco  = $request->input('endereco');
            
            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                // Obter a extensão original do arquivo
                $extension = $file->getClientOriginalExtension();
                // Gerar um nome único para o arquivo mantendo a extensão original
                $fileName = time() . '.' . $extension;
                // Caminho onde a imagem será armazenada
                $path = public_path('logotipo');
                // Salvar a imagem no diretório especificado
                $file->move($path, $fileName);
                // Atualizar o campo avatar no banco de dados
                $escola->logotipo = $fileName;
            }

            if($escola->save()){

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Escola Registado com Sucesso!']);

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
