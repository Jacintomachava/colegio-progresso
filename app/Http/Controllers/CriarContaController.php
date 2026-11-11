<?php

namespace App\Http\Controllers;

use App\Models\DocumentoAssociacao;
use App\Models\Associacao;
use App\Models\DocumentoPessoa;
use App\Models\DocumentoClube;
use App\Models\Pessoa;
use App\Models\Clube;
use App\Models\Arbitro;
use App\Models\Provincia;
use App\Models\User;
use App\Models\RepresentanteAssociacao;
use App\Models\RepresentanteClube;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CriarContaController extends Controller
{
    //
    public function associacao()
    {
        $provincias = Provincia::all();

        return view('preRegisto.associacao',['provincias'=>$provincias]);
    }

    public function clube()
    {
        $provincias = Provincia::all();
        $associacoes = Associacao::all(); 

        return view('preRegisto.clube',['provincias'=>$provincias,'associacoes'=>$associacoes]);
    }

    public function arbitro()
    {
        $provincias = Provincia::all();
        $associacoes = Associacao::all();

        return view('preRegisto.arbitro',['provincias'=>$provincias,'associacoes'=>$associacoes]);
    }

    public function registarArbitro(Request $request)
    {
        DB::beginTransaction();

        try {

            // Cria Utilizador da Associacao
            $pessoa = new Pessoa();
            $pessoa->nome = $request->input('nome');
            $pessoa->cognome = $request->input('nome');
            $pessoa->data_nascimento = $request->input('data_nascimento');
            $pessoa->tipo_documento = $request->input('tipo_documento');
            $pessoa->numero_documento = $request->input('numero_documento');
            $pessoa->genero = $request->input('genero');
            $pessoa->endereco = $request->input('endereco');
            $pessoa->telefone = $request->input('telefone');
            $pessoa->email = $request->input('email');
            $pessoa->nivel_academico = $request->input('nivel_academico');
            $pessoa->profissao = $request->input('profissao');
            $pessoa->local_trabalho = $request->input('local_trabalho');
            $pessoa->nacionalidade_id = 1; // $request->input('logotipo');
            $pessoa->tipo_id = 6;

            if ($pessoa->save()) {

                $arbitro = new Arbitro();
                $arbitro->pessoa_id = $pessoa->id;
                $arbitro->associacao_id = $request->input('associacao');

                if($arbitro->save()){

                    DB::commit();
                    return response()->json(['status' => 1, 'message' => 'Arbitro criado com sucesso!']);
                }

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


    public function registarClube(Request $request)
    {
        DB::beginTransaction();

        try {

            // Cria uma nova pessoa com base nos dados do request
            $clube = new Clube();
            $clube->nome = $request->input('nome');
            $clube->sigla = $request->input('sigla');
            $clube->endereco = $request->input('endereco');
            $clube->provincia_id = $request->input('provincia');
            $clube->email = $request->input('email');
            $clube->telefone = $request->input('telefone');
            $clube->associacao_id = $request->input('associacao');
            
            if ($request->hasFile('logotipo')) {
                $avatar = $request->file('logotipo');
                $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                $avatar->move('public/logotipo/', $filename1);
                $clube->simbolo = $filename1;
            }
            
            // Salva o departamento no banco de dados
            if ($clube->save()) {

                // Cria Presidente Associacao
                $pessoaPresidente = new Pessoa();
                $pessoaPresidente->nome = $request->input('nome_presidente');
                $pessoaPresidente->cognome = $request->input('nome_presidente');
                $pessoaPresidente->telefone = $request->input('telefone_presidente');
                $pessoaPresidente->email = $request->input('email_presidente');
                //$pessoaPresidente->provincia_id = $request->input('provincia');
                $pessoaPresidente->tipo_documento = $request->input('tipo_documento_presidente');
                $pessoaPresidente->numero_documento = $request->input('numero_documento_presidente');
                $pessoaPresidente->nacionalidade_id = 1; //$request->input('logotipo');
                $pessoaPresidente->tipo_id = 2;

                if ($request->hasFile('foto_presidente')) {
                    $avatar = $request->file('foto_presidente');
                    $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                    $avatar->move('public/avatar/', $filename1);
                    $pessoaPresidente->avatar = $filename1;
                }

                // Cria Secretario da Associacao
                $pessoaSecretario = new Pessoa();
                $pessoaSecretario->nome = $request->input('nome_secretario');
                $pessoaSecretario->cognome = $request->input('nome_secretario');
                $pessoaSecretario->telefone = $request->input('telefone_secretario');
                $pessoaSecretario->email = $request->input('email_secretario');
                //$pessoaSecretario->provincia_id = $request->input('provincia');
                $pessoaSecretario->tipo_documento = $request->input('tipo_documento_secretario');
                $pessoaSecretario->numero_documento = $request->input('numero_documento_secretario');
                $pessoaSecretario->nacionalidade_id = 1;
                $pessoaSecretario->tipo_id = 2;

                if ($request->hasFile('foto_secretario')) {
                    $avatar = $request->file('foto_secretario');
                    $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                    $avatar->move('public/avatar/', $filename1);
                    $pessoaSecretario->avatar = $filename1;
                }

                // Cria Utilizador da Associacao
                $pessoaUtilizador = new Pessoa();
                $pessoaUtilizador->nome = $request->input('nome_utilizador');
                $pessoaUtilizador->cognome = $request->input('nome_utilizador');
                $pessoaUtilizador->telefone = $request->input('telefone_utilizador');
                $pessoaUtilizador->email = $request->input('email_utilizador');
                //$pessoaUtilizador->provincia_id = $request->input('provincia');
                $pessoaUtilizador->tipo_documento = $request->input('tipo_documento_utilizador');
                $pessoaUtilizador->numero_documento = $request->input('numero_documento_utilizador');
                $pessoaUtilizador->nacionalidade_id = 1; // $request->input('logotipo');
                $pessoaUtilizador->tipo_id = 2;

                if ($request->hasFile('foto_utilizador')) {
                    $avatar = $request->file('foto_utilizador');
                    $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                    $avatar->move('public/avatar/', $filename1);
                    $pessoaUtilizador->avatar = $filename1;
                }

                if($pessoaPresidente->save() && $pessoaSecretario->save() && $pessoaUtilizador->save()){

                    // Cria uma nova Utilizador
                    $user = new User();
                    $user->nome = $request->input('nome_utilizador');
                    $user->email = $request->input('email_utilizador');
                    $user->telefone = $request->input('telefone_utilizador');
                    $user->codigo = rand(100000, 999999); 
                    $user->password = Hash::make($request->input('telefone_utilizador')); 
                    $user->associacao_ou_clube_id = $clube->id; 
                    $user->pessoa_id = $pessoaUtilizador->id; 
                    $user->tipo_id = 2; 

                    if($user->save()){

                        // Criar Documentos Associacao BR
                        $documentoClubeBr = new DocumentoClube();
                        $documentoClubeBr->tipo_documento = 'BR';
                        $documentoClubeBr->numero_documento = '';
                        $documentoClubeBr->estado = 'Pendente';
                        $documentoClubeBr->clube_id = $clube->id;
                        $documentoClubeBr->user_id = $user->id;

                        if ($request->hasFile('br')) {
                            $avatar = $request->file('br');
                            $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                            $avatar->move('public/avatar/', $filename1);
                            $documentoClubeBr->anexo = $filename1;
                        }

                        // Criar Documentos Associacao Certidao
                        $documentoClubeCertidao = new DocumentoClube();
                        $documentoClubeCertidao->tipo_documento = 'Certidão';
                        $documentoClubeCertidao->numero_documento = '';
                        $documentoClubeCertidao->estado = 'Pendente';
                        $documentoClubeCertidao->clube_id = $clube->id;
                        $documentoClubeCertidao->user_id = $user->id;

                        if ($request->hasFile('certidao')) {
                            $avatar = $request->file('certidao');
                            $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                            $avatar->move('public/avatar/', $filename1);
                            $documentoClubeCertidao->anexo = $filename1;
                        }

                        if($documentoClubeBr->save() && $documentoClubeCertidao->save()){

                            $representantePresidente = new RepresentanteClube();
                            $representantePresidente->cargo = 'Presidente';
                            $representantePresidente->clube_id =  $clube->id;
                            $representantePresidente->pessoa_id = $pessoaPresidente->id;
                            $representantePresidente->user_id = $user->id;

                            $representanteSecretario = new RepresentanteClube();
                            $representanteSecretario->cargo = 'Secretario';
                            $representanteSecretario->clube_id =  $clube->id;
                            $representanteSecretario->pessoa_id = $pessoaSecretario->id;
                            $representanteSecretario->user_id = $user->id;

                            if($representantePresidente->save() && $representanteSecretario->save()){

                                DB::commit();
                                return response()->json(['status' => 1, 'message' => 'Associacao criado com sucesso!']);

                            }
    
                        }

                    }
                    
                }

            } else {
                DB::rollBack();
                return response()->json(['status' => 0, 'message' => 'Houve um problema ao criar o Evento. Por favor, tente novamente.']);
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

    public function registarAssociacao(Request $request)
    {
        DB::beginTransaction();

        try {

            // Cria uma nova pessoa com base nos dados do request
            $associacao = new Associacao();
            $associacao->nome = $request->input('nome');
            $associacao->sigla = $request->input('sigla');
            $associacao->endereco = $request->input('endereco');
            $associacao->provincia_id = $request->input('provincia');
            $associacao->email = $request->input('email');
            $associacao->telefone = $request->input('telefone');
            $associacao->nome_banco = $request->input('nome_banco');
            $associacao->numero_banco = $request->input('numero_banco');
            
            if ($request->hasFile('logotipo')) {
                $avatar = $request->file('logotipo');
                $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                $avatar->move('public/logotipo/', $filename1);
                $associacao->simbolo = $filename1;
            }
            
            // Salva o departamento no banco de dados
            if ($associacao->save()) {

                // Cria Presidente Associacao
                $pessoaPresidente = new Pessoa();
                $pessoaPresidente->nome = $request->input('nome_presidente');
                $pessoaPresidente->cognome = $request->input('nome_presidente');
                $pessoaPresidente->telefone = $request->input('telefone_presidente');
                $pessoaPresidente->email = $request->input('email_presidente');
                $pessoaPresidente->provincia_id = $request->input('provincia');
                $pessoaPresidente->tipo_documento = $request->input('tipo_documento_presidente');
                $pessoaPresidente->numero_documento = $request->input('numero_documento_presidente');
                $pessoaPresidente->nacionalidade_id = 1; //$request->input('logotipo');
                $pessoaPresidente->tipo_id = 2;

                if ($request->hasFile('foto_presidente')) {
                    $avatar = $request->file('foto_presidente');
                    $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                    $avatar->move('public/avatar/', $filename1);
                    $pessoaPresidente->avatar = $filename1;
                }

                // Cria Secretario da Associacao
                $pessoaSecretario = new Pessoa();
                $pessoaSecretario->nome = $request->input('nome_secretario');
                $pessoaSecretario->cognome = $request->input('nome_secretario');
                $pessoaSecretario->telefone = $request->input('telefone_secretario');
                $pessoaSecretario->email = $request->input('email_secretario');
                $pessoaSecretario->provincia_id = $request->input('provincia');
                $pessoaSecretario->tipo_documento = $request->input('tipo_documento_secretario');
                $pessoaSecretario->numero_documento = $request->input('numero_documento_secretario');
                $pessoaSecretario->nacionalidade_id = 1;
                $pessoaSecretario->tipo_id = 2;

                if ($request->hasFile('foto_secretario')) {
                    $avatar = $request->file('foto_secretario');
                    $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                    $avatar->move('public/avatar/', $filename1);
                    $pessoaSecretario->avatar = $filename1;
                }

                // Cria Utilizador da Associacao
                $pessoaUtilizador = new Pessoa();
                $pessoaUtilizador->nome = $request->input('nome_utilizador');
                $pessoaUtilizador->cognome = $request->input('nome_utilizador');
                $pessoaUtilizador->telefone = $request->input('telefone_utilizador');
                $pessoaUtilizador->email = $request->input('email_utilizador');
                $pessoaUtilizador->provincia_id = $request->input('provincia');
                $pessoaUtilizador->tipo_documento = $request->input('tipo_documento_utilizador');
                $pessoaUtilizador->numero_documento = $request->input('numero_documento_utilizador');
                $pessoaUtilizador->nacionalidade_id = 1; // $request->input('logotipo');
                $pessoaUtilizador->tipo_id = 2;

                if ($request->hasFile('foto_utilizador')) {
                    $avatar = $request->file('foto_utilizador');
                    $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                    $avatar->move('public/avatar/', $filename1);
                    $pessoaUtilizador->avatar = $filename1;
                }

                if($pessoaPresidente->save() && $pessoaSecretario->save() && $pessoaUtilizador->save()){

                    // Cria uma nova Utilizador
                    $user = new User();
                    $user->nome = $request->input('nome_utilizador');
                    $user->email = $request->input('email_utilizador');
                    $user->telefone = $request->input('telefone_utilizador');
                    $user->codigo = rand(100000, 999999); 
                    $user->password = Hash::make($request->input('telefone_utilizador')); 
                    $user->associacao_ou_clube_id = $associacao->id; 
                    $user->pessoa_id = $pessoaUtilizador->id; 
                    $user->tipo_id = 2; 

                    if($user->save()){

                        // Criar Documentos Associacao BR
                        $documentoAssociacaoBr = new DocumentoAssociacao();
                        $documentoAssociacaoBr->tipo_documento = 'BR';
                        $documentoAssociacaoBr->numero_documento = '';
                        $documentoAssociacaoBr->estado = 'Pendente';
                        $documentoAssociacaoBr->associacao_id = $associacao->id;
                        $documentoAssociacaoBr->user_id = $user->id;

                        if ($request->hasFile('br')) {
                            $avatar = $request->file('br');
                            $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                            $avatar->move('public/avatar/', $filename1);
                            $documentoAssociacaoBr->anexo = $filename1;
                        }

                        // Criar Documentos Associacao Certidao
                        $documentoAssociacaoCertidao = new DocumentoAssociacao();
                        $documentoAssociacaoCertidao->tipo_documento = 'Certidão';
                        $documentoAssociacaoCertidao->numero_documento = '';
                        $documentoAssociacaoCertidao->estado = 'Pendente';
                        $documentoAssociacaoCertidao->associacao_id = $associacao->id;
                        $documentoAssociacaoCertidao->user_id = $user->id;

                        if ($request->hasFile('certidao')) {
                            $avatar = $request->file('certidao');
                            $filename1 = rand(100000, 999999).time().'.'.$avatar->getClientOriginalExtension();
                            $avatar->move('public/avatar/', $filename1);
                            $documentoAssociacaoCertidao->anexo = $filename1;
                        }

                        if($documentoAssociacaoBr->save() && $documentoAssociacaoCertidao->save()){

                            $representantePresidente = new RepresentanteAssociacao();
                            $representantePresidente->cargo = 'Presidente';
                            $representantePresidente->associacao_id =  $associacao->id;
                            $representantePresidente->pessoa_id = $pessoaPresidente->id;
                            $representantePresidente->user_id = $user->id;

                            $representanteSecretario = new RepresentanteAssociacao();
                            $representanteSecretario->cargo = 'Secretario';
                            $representanteSecretario->associacao_id =  $associacao->id;
                            $representanteSecretario->pessoa_id = $pessoaSecretario->id;
                            $representanteSecretario->user_id = $user->id;

                            if($representantePresidente->save() && $representanteSecretario->save()){

                                DB::commit();
                                return response()->json(['status' => 1, 'message' => 'Associacao criado com sucesso!']);

                            }
    
                        }

                    }
                    
                }

            } else {
                DB::rollBack();
                return response()->json(['status' => 0, 'message' => 'Houve um problema ao criar o Evento. Por favor, tente novamente.']);
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
