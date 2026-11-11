@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

  <div class="row g-0">
    <div class="col-xxl-3 col-xl-4 col-md-5 box-col-5">
      <div class="left-sidebar-wrapper card">
        <div class="left-sidebar-chat">
          <div class="input-group">
            <span class="input-group-text">
              Contactos dos Encarregados
            </span>
          </div>
        </div>
        <div class="advance-options"> 
          <ul class="nav border-tab" id="chat-options-tab" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="chats-tab" data-bs-toggle="tab" href="#chats" role="tab" aria-controls="chats" aria-selected="true">Chats</a></li>
            <li class="nav-item"><a class="nav-link" id="contacts-tab" data-bs-toggle="tab" href="#contacts" role="tab" aria-controls="contacts" aria-selected="false">Contacts</a></li>
          </ul>
          <div class="tab-content" id="chat-options-tabContent"> 
            <div class="tab-pane fade show active" id="chats" role="tabpanel" aria-labelledby="chats-tab">
              <div class="common-space"> 
                <p>Recent chats</p>
                <div class="header-top"><a class="btn f-w-500" href="#!"><i class="fa fa-plus"></i></a></div>
              </div>

              <ul class="chats-user">

              @foreach($contatos as $contato)
                <li class="common-space">
                 <a href="{{route('mensagem.show',['contacto'=>$contato->telefone])}}">
                  <div class="chat-time">
                    <div class="active-profile"><img class="img-fluid rounded-circle" src="{{ URL('/assets/images/avtar/11.jpg') }}" alt="user">
                      <div class="status bg-success"></div>
                    </div>
                    <div> <span>{{$contato->telefone}}</span>
                      <p>{{ Str::limit($contato->ultima_conversa, 18) }}</p>
                    </div>
                  </div>
                  <div>
                    <p>
                        @if ($contato->updated_at->isToday())
                            {{ $contato->updated_at->format('H:i') }} ({{ $contato->updated_at->diffForHumans() }})
                        @elseif ($contato->updated_at->isYesterday())
                            Ontem {{ $contato->updated_at->format('H:i') }}
                        @else
                            {{ $contato->updated_at->format('d-M-Y H:i') }}
                        @endif
                    </p>
                  </div>
                 </a>  
                </li>
              @endforeach
              

              </ul>
            </div>
            <div class="tab-pane fade" id="contacts" role="tabpanel" aria-labelledby="contacts-tab">
              <div class="common-space"> 
                <p>Contactos</p>
                <div class="header-top"><a class="btn f-w-500" href="#!"><i class="fa fa-plus"></i></a></div>
              </div>
              <div class="search-contacts">

              </div>
              <div class="contact-wrapper">
              
               @foreach($contatos as $contato)
                <ul class="border-0">
                 <a href="{{route('mensagem.show',['contacto'=>$contato->telefone])}}">
                  <li class="common-space">
                  
                    <div class="chat-time"><img class="img-fluid rounded-circle" src="{{ URL('/assets/images/avtar/3.jpg') }}" alt="user">
                      <div> <span>{{$contato->telefone}}</span>
                        <p>{{$contato->telefone}}</p>
                      </div>
                    </div>
                 
                  </li>
                 </a> 
                </ul>
               @endforeach

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xxl-9 col-xl-8 col-md-7 box-col-7">
      <div class="card right-sidebar-chat">
        <div class="right-sidebar-title">
          <div class="common-space"> 
            <div class="chat-time"> 
              <div class="active-profile"><img class="img-fluid rounded-circle" src="{{ URL('/assets/images/blog/comment.jpg') }}" alt="user">
                <div class="status bg-success"></div>
              </div>
              <div> <span>{{$contacto}}</span>
                <p>Online </p>
              </div>
            </div>
            <div class="d-flex gap-2">
              <div class="contact-edit chat-alert"><i class="icon-info-alt"></i></div>
              <div class="contact-edit chat-alert">
                <svg class="dropdown-toggle" role="menu" data-bs-toggle="dropdown" aria-expanded="false">
                  <use href="{{ URL('/assets/svg/icon-sprite.svg#menubar') }}"></use>
                </svg>
                <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#!">View details</a><a class="dropdown-item" href="#!">
                      Send messages</a><a class="dropdown-item" href="#!">
                      Add to favorites</a></div>
              </div>
            </div>
          </div>
        </div>
        <div class="right-sidebar-Chats"> 
          <div class="msger">
            
            <div class="msger-chat">
              
            @foreach($mensagens as $mensagem)

             @if($mensagem->tipo=="Recebida")
              <div class="msg left-msg">
                <div class="msg-img"></div>
                <div class="msg-bubble">
                  <div class="msg-info">
                    <div class="msg-info-name">{{$mensagem->canal}}({{$mensagem->credito}})</div>
                    <div class="msg-info-time">
                        @if ($mensagem->updated_at->isToday())
                            {{ $mensagem->updated_at->format('H:i') }} ({{ $mensagem->updated_at->diffForHumans() }})
                        @elseif ($mensagem->updated_at->isYesterday())
                            Ontem {{ $mensagem->updated_at->format('H:i') }}
                        @else
                            {{ $mensagem->updated_at->format('d-M-Y H:i') }}
                        @endif
                     </div>
                  </div>
                  <div class="msg-text">{{$mensagem->descricao}}</div>
                </div>
              </div>
             @endif 

             @if($mensagem->tipo=="Enviada")
              <div class="msg right-msg">
                <div class="msg-img"></div>
                <div class="msg-bubble">
                  <div class="msg-info">
                    <div class="msg-info-name">{{$mensagem->canal}}({{$mensagem->credito}})</div>
                    <div class="msg-info-time">
                        @if ($mensagem->updated_at->isToday())
                            {{ $mensagem->updated_at->format('H:i') }} ({{ $mensagem->updated_at->diffForHumans() }})
                        @elseif ($mensagem->updated_at->isYesterday())
                            Ontem {{ $mensagem->updated_at->format('H:i') }}
                        @else
                            {{ $mensagem->updated_at->format('d-M-Y H:i') }}
                        @endif
                    </div>
                  </div>
                  <div class="msg-text">{{$mensagem->descricao}}</div>
                </div>
              </div>
              @endif 
             @endforeach

            </div>
            <form class="msger-inputarea" id="form" enctype="multipart/form-data">

              @csrf

              <div class="dropdown-form dropdown-toggle" role="main" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-plus"></i>
                <div class="chat-icon dropdown-menu dropdown-menu-start">
                  <div class="dropdown-item mb-2">
                    <svg> 
                      <use href="{{ URL('/assets/svg/icon-sprite.svg#camera') }}"></use>
                    </svg>
                  </div>
                  <div class="dropdown-item">
                    <svg> 
                      <use href="{{ URL('/assets/svg/icon-sprite.svg#attchment') }}"></use>
                    </svg>
                  </div>
                </div>
              </div>
              <input  type="hidden" name="telefone" value="{{$contacto}}" >
              <input class="msger-input two uk-textarea" name="mensagem" type="text" value="" placeholder="Escreva aqui..">
              <div class="open-emoji">
                <div class="second-btn uk-button"></div>
              </div>
              <button class="msger-send-btn" type="submit" id="botao_salvar">
                <i id="icon_enviar" class="fa fa-location-arrow"></i>
              </button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>


@endsection

@push('js')

<script>

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#form").validate({
        rules: {
            nome: {
                required: true,
                minlength: 1
            },
            vaga: {
                required: true,
            }
        },
        submitHandler: function(form) {
            // Use FormData para suportar envio de arquivos
            var formData = new FormData(form);

            $.ajax({
                type: "POST",
                url: "{{ route('responder.chat') }}",
                data: formData,
                processData: false, // Não processa os dados, necessário para FormData
                contentType: false, // Define o content type como multipart/form-data
                beforeSend: function() {
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Enviando Mensagem...');
                },
                success: function(response) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Enviando Mensagem');

                    if (response.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                        });
                        window.location.reload();
                    } else if (response.status == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message,
                        });
                    }
                },
                error: function(errors) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Enviando Mensagem');
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: errors.responseJSON.message,
                    });
                }
            });
        }
    });
});


</script>

@endpush
