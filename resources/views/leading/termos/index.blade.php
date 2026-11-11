@extends('layouts.master')

@push('css')

@endpush

@section('conteudo')

<br><br>
<div class="section-full p-t70 p-b40">
            <!-- Career -->
            <div class="container">
                <div class="row">
                    <!-- Left part start -->
                    <div class="col-lg-9 m-b30">
                        <h1 class="m-b20">Termos de Serviço </h1>
                        <div class="dez-separator bg-primary"></div>
                        <p><strong>
                                Bem-vindo ao nosso sistema escolar. Ao utilizar nossos serviços, você concorda com os seguintes termos:
                            </strong>
                        </p>
                        <h2 class="m-t30 m-b10 ">1. Uso do Sistema</h2>
                        <p>O sistema destina-se exclusivamente à comunicação entre a escola, alunos e encarregados de educação. Não é permitido o uso para fins comerciais ou ilegais.</p>

                        <h2 class="m-t30 m-b10 ">2. Responsabilidades</h2>
                        <p>O usuário compromete-se a fornecer informações corretas e a respeitar a política de boa conduta. A escola não se responsabiliza por uso indevido da plataforma.</p>

                        <h2 class="m-t30 m-b10 ">3. Comunicações</h2>
                        <p>Ao aceitar estes termos, você permite que a escola envie mensagens informativas via WhatsApp, incluindo dados de matrícula, propinas e desempenho escolar.</p>

                        <h2 class="m-t30 m-b10 ">4. Alterações</h2>
                        <p>Os termos podem ser atualizados periodicamente. A continuidade no uso do sistema implica aceitação das alterações.</p>

                        <h2 class="m-t30 m-b10 ">4. Segurança dos dados</h2>
                        <p>Tomamos medidas técnicas e organizacionais para proteger suas informações contra perda, acesso não autorizado ou divulgação indevida.</p>
                        <p><p>Para mais informações, consulte nossa <a href="{{ route('politicas') }}">Política de Privacidade</a>.</p></p>
                        
                </div>
            </div>
            <!-- Career END -->
        </div>


@endsection

@push('js')


@endpush
