<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\ReceitaDespesa;
use App\Models\Classe;
use App\Models\Disciplina;
use App\Models\AlunoRelacaoPessoa;
use App\Models\SMSDestinatario;
use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Mensalidade;
use App\Models\AnoAcademico;
use App\Services\ReceitaDespesaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{

    /* Esse metodo Verifica o estado Actual de
    Mensalidades nesse estudante para saber se Tem multa ou nao.
    */    
    public static function envioEmail($destinatario, $assunto, $corpo, $anexo1, $anexo2, $nome, $escolaId, $descricao)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        if (!$anoAcademicoAtivo) {
            throw new \Exception('Ano académico ativo não encontrado.');
        }

        $escola = Escola::find($escolaId);
        if (!$escola) {
            throw new \Exception('Escola não encontrada.');
        }

        // Construir os caminhos dos anexos (se existirem)
        $anexo1Path = $anexo1 ? public_path('relatorio/' . $anexo1) : null;
        $anexo2Path = $anexo2 ? public_path('relatorio/' . $anexo2) : null;

        // Validar a existência dos anexos (caso sejam fornecidos)
        if ($anexo1Path && !file_exists($anexo1Path)) {
            throw new \Exception("O anexo {$anexo1Path} não foi encontrado.");
        }

        if ($anexo2Path && !file_exists($anexo2Path)) {
            throw new \Exception("O anexo {$anexo2Path} não foi encontrado.");
        }

        $dados = [
            'email' => $destinatario,
            'nome' => $descricao,
            'nomeEscola' => $escola->nome,
            'assunto' => $assunto,
            'corpo' => $corpo,
            'ano' => $anoAcademicoAtivo->ano,
            'contacto' => trim($escola->telefone . ' ' . ($escola->telefone2 ?? '')),
            'endereco' => $escola->endereco,
            'slogan' => $escola->slogan,
            'logotipo' => $escola->logotipo,
            'anexo1' => $anexo1Path,
            'anexo2' => $anexo2Path,
        ];

        try {
            Mail::send('notificacoes.email.index', ['dados' => $dados], function ($m) use ($dados) {
                $m->from('grm.pled@gmail.com', $dados['assunto']);
                $m->to($dados['email'], $dados['nome'])->subject($dados['assunto']);

                // Anexar o primeiro anexo, se existir
                if ($dados['anexo1'] && file_exists($dados['anexo1'])) {
                    $m->attach($dados['anexo1']);
                }

                // Anexar o segundo anexo, se existir
                if ($dados['anexo2'] && file_exists($dados['anexo2'])) {
                    $m->attach($dados['anexo2']);
                }
            });

            // Excluir os anexos, se existirem
            if ($anexo1Path && file_exists($anexo1Path)) {
                unlink($anexo1Path);
            }

            if ($anexo2Path && file_exists($anexo2Path)) {
                unlink($anexo2Path);
            }

            return true;

        } catch (\Swift_TransportException $e) {
            \Log::error('Erro de transporte ao enviar e-mail: ' . $e->getMessage());
            throw new \Exception('Erro ao conectar ao servidor de e-mail. Tente novamente mais tarde.');
            return false;
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail: ' . $e->getMessage());
            throw new \Exception('O envio do e-mail falhou. Tente novamente mais tarde.');
            return false;
        }
    }

    public static function encarregadoAlunoTemEmail($alunoID)
    {
        return AlunoRelacaoPessoa::where('aluno_id', $alunoID)
            ->where('relacao_id', 1)
            ->whereHas('pessoa', function ($query) {
                $query->whereNotNull('email'); // Verifica se o email não é nulo no relacionamento
            })
            ->exists(); // Retorna true se existir pelo menos um registro
    }

    public static function agendarEmail($dataEnvio, $email, $assunto, $corpo, $anexo1, $anexo2, $destinatario, $sender,$tipo, $escola,$descricao)
    {

        $emailDestinatario = new SMSDestinatario();
        $emailDestinatario->corpo = $corpo;
        $emailDestinatario->descricao = $descricao;
        $emailDestinatario->anexo1 = $anexo1;
        $emailDestinatario->anexo2 = $anexo2;
        $emailDestinatario->assunto = $assunto;
        $emailDestinatario->data_envio = $dataEnvio;
        $emailDestinatario->sender_id = $sender;
        $emailDestinatario->destinatario_pessoa_id = $destinatario;
        $emailDestinatario->tipo = $tipo;
        $emailDestinatario->contacto = $email;
        $emailDestinatario->escola_id = $escola;
        $emailDestinatario->categoria = "Email";
        $emailDestinatario->save();
         
    }

    //Envia SMS Pendentes
    public static function notificacaoEmail()
    {
        // Buscar destinatários pendentes em campanhas ativas
        $destinatarios = SMSDestinatario::where('estado', 'Pendente')->where('categoria', 'Email')->where('data_envio', '<=', now())->get();

        //dd($destinatarios);

        if (count($destinatarios) > 0) {

            // Processar cada destinatário
            foreach ($destinatarios as $destinatario) {

                $envio = self::envioEmail($destinatario->contacto, $destinatario->assunto, $destinatario->corpo, $destinatario->anexo1, $destinatario->anexo2,$destinatario->pessoa->nome, $destinatario->escola_id, $destinatario->descricao);
                if($envio){
                // Atualizar o estado do destinatário
                $destinatario->update(['estado' => 'Enviado']);
                }

                
            }
        }
    }

}
