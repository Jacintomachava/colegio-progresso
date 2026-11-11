@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="row">
  
    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card project-widget widget-1 title-line">
            <div class="card-header card-no-border pb-0"> 
            <div class="header-top">
                <div> 
                <h3 class="f-w-600">Total Alunos</h3>
                </div>
                <div class="card-header-right-icon">
                <div class="dropdown icon-dropdown d-xxl-none1">
                    <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg>
                        <use href="{{ URL('/public/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                    </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                    </div>
                </div>
                </div>
            </div>
            <div class="widget-middle-content">
                <div class="d-flex align-items-center"> 
                <h2>{{$totalAlunos}}</h2>
                    <span class="f-w-500 txt-warning f-12"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>Ano 2026</span></span>
                </div>
            </div>
            </div>
            <div class="card-body widget-bottom-content">
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card project-widget widget-1 title-line">
            <div class="card-header card-no-border pb-0"> 
            <div class="header-top">
                <div> 
                <h3 class="f-w-600">Total Turmas</h3>
                </div>
                <div class="card-header-right-icon">
                <div class="dropdown icon-dropdown d-xxl-none1">
                    <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg>
                        <use href="{{ URL('/public/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                    </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                    </div>
                </div>
                </div>
            </div>
            <div class="widget-middle-content">
                <div class="d-flex align-items-center"> 
                <h2>{{$totalTurma}}</h2>
                    <span class="f-w-500 txt-warning f-12"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>+100%</span></span>
                </div>
            </div>
            </div>
            <div class="card-body widget-bottom-content">
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card project-widget widget-1 title-line">
            <div class="card-header card-no-border pb-0"> 
            <div class="header-top">
                <div> 
                <h3 class="f-w-600">Total Professor</h3>
                </div>
                <div class="card-header-right-icon">
                <div class="dropdown icon-dropdown d-xxl-none1">
                    <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg>
                        <use href="{{ URL('/public/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                    </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                    </div>
                </div>
                </div>
            </div>
            <div class="widget-middle-content">
                <div class="d-flex align-items-center"> 
                <h2>{{$totalProfessor}}</h2>
                    <span class="f-w-500 txt-warning f-12"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>+100%</span></span>
                </div>
            </div>
            </div>
            <div class="card-body widget-bottom-content">
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-4 col-sm-6">
       <a href="{{route('mensagem.index')}}">  
        <div class="card project-widget widget-1 title-line">
            <div class="card-header card-no-border pb-0"> 
            <div class="header-top">
                <div> 
                <h3 class="f-w-600">Crédito</h3>
                </div>
                <div class="card-header-right-icon">
                <div class="dropdown icon-dropdown d-xxl-none1">
                    <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg>
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                    </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                    </div>
                </div>
                </div>
            </div>
            <div class="widget-middle-content">
                <div class="d-flex align-items-center"> 
                <h2>{{$saldoSMS->saldo}}</h2>
                    <span class="f-w-500 txt-warning f-12"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>{{$mensagem}}</span></span>
                </div>
            </div>
            </div>
            <div class="card-body widget-bottom-content">
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            </div>
        </div>
      </a>  
    </div>

</div>

<div class="row">

<div class="col-md-6 order-md-iii">
    <div class="card title-line">
        <div class="card-header card-no-border">
        <div class="header-top">

            <div class="card-header-right-icon">
            <div class="dropdown icon-dropdown">
                <button class="btn dropdown-toggle" id="earning-avg" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg>
                    <use href="{{ URL('/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                </svg>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earning-avg">
                </div>
            </div>
            </div>
        </div>
        </div>
        <div class="card-body pt-0">
            <div class="report-chart-container"> 
                <div id="matriculaPieChart" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6 order-md-iii">
    <div class="card title-line">
        <div class="card-header card-no-border">
        <div class="header-top">

            <div class="card-header-right-icon">
            <div class="dropdown icon-dropdown">
                <button class="btn dropdown-toggle" id="earning-avg" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg>
                    <use href="{{ URL('/public/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                </svg>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earning-avg">
                </div>
            </div>
            </div>
        </div>
        </div>
        <div class="card-body pt-0">
            <div class="report-chart-container"> 
                <div id="grafico-matriculas" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

</div>

<div class="row">
    <div class="col-md-12 col-sm-12" style="background-color: white; padding: 10px">
        <div class="iconic-w-wrap number-rotate">
            <div id="graficoMensalidadesContagem" style="width: 100%; height: 400px;"></div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalMensalidades" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title" id="tituloModal">
                        Lista de Mensalidades
                    </h5>

                    <small id="subtituloModal"></small>

                </div>

                <div>

                    <a href="#"
                       target="_blank"
                       id="btnImprimir"
                       class="btn btn-primary btn-sm">

                        Imprimir
                    </a>

                </div>

            </div>

            <div class="modal-body">

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Classe</th>
                            <th>Entidade</th>
                            <th>Referencia</th>
                            <th>Multa</th>
                            <th>Valor</th>
                        </tr>
                    </thead>

                    <tbody id="corpoTabelaMensalidades">

                    </tbody>

                </table>

            </div>

        </div>
    </div>
</div>


@endsection

@push('js')

<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>

<script>

    var chartDom = document.getElementById('graficoMensalidadesContagem');
    var myChart = echarts.init(chartDom);

    var option = {
        title: {
            text: 'Mensalidades Pagas vs Pendentes (Contagem)',
            left: 'center'
        },

        tooltip: {
            trigger: 'axis'
        },

        legend: {
            bottom: 0,
            data: ['Pagas', 'Pendentes']
        },

        xAxis: {
            type: 'category',
            data: @json($labelsMeses),
            axisLabel: {
                rotate: 45
            }
        },

        yAxis: {
            type: 'value',
            name: 'Quantidade'
        },

        series: [
            {
                name: 'Pagas',
                type: 'bar',
                data: @json($mensalidadesPagas),
                itemStyle: {
                    color: '#4CAF50'
                }
            },

            {
                name: 'Pendentes',
                type: 'bar',
                data: @json($mensalidadesPendentes),
                itemStyle: {
                    color: '#F44336'
                }
            }
        ]
    };

    myChart.setOption(option);

    // EVENTO CLICK
    myChart.on('click', function(params) {

        let mes = params.name;
        let tipo = params.seriesName;

        // TITULO
        $('#tituloModal').html(
            `<h1>Mensalidades ${tipo} - ${mes}</h1>`
        );

        // BOTAO IMPRIMIR
        $('#btnImprimir').attr(
            'href',
            `/mensalidades/imprimir?mes=${mes}&tipo=${tipo}`
        );

        function formatarMoeda(valor) {

            return Number(valor)
                .toFixed(2)
                .replace('.', ',')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        $.ajax({

        url: '/mensalidades/detalhes',

        type: 'GET',

        data: {
            mes: mes,
            tipo: tipo
        },
        

        success: function(response) {

            let html = '';

            $('#subtituloModal').html(
                `<h2>Total de registros: ${response.length}</h2>`
            );

                response.forEach(function(item) {

                    html += `
                        <tr>
                            <td>${item.aluno}</td>

                            <td>${item.classe}</td>

                            <td>90028</td>

                            <td>
                                ${item.pagamento}
                            </td>

                            <td>${item.multa}%</td>

                            <td>
                                ${formatarMoeda(item.valor)}
                            </td>
                        </tr>
                    `;
                });

                $('#corpoTabelaMensalidades').html(html);

                $('#modalMensalidades').modal('show');
            }
        });

    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var chart = echarts.init(document.getElementById('grafico-matriculas'));

        // Dados do Blade
        var dados = @json($dadosGraficoMatriculas);

        // Convertendo para o formato do ECharts
        var chartData = dados.map(item => ({
            name: item.categoria,  // Ex: "1ª Classe - Manhã"
            value: item.total      // Ex: 20 matriculados
        }));

        var options = {
            title: {
                text: '',
                left: 'center'
            },
            tooltip: {
                trigger: 'item'
            },
            legend: {
                orient: 'horizontal', // Legenda na horizontal
                left: 'left'
            },
            series: [{
                name: 'Matrículas',
                type: 'pie',
                radius: '39%',
                data: chartData,
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                },
                label: {
                    formatter: '{b}: {c} ({d}%)'
                }
            }]
        };

        chart.setOption(options);
    });
</script>

<script>
    var chartDom = document.getElementById('matriculaPieChart');
    var myChart = echarts.init(chartDom);

    var option = {
        title: {
            text: 'Matrículas - Pagas vs Pendentes (Ano 2026)',
            left: 'center'
        },
        tooltip: {
            trigger: 'item'
        },
        legend: {
            bottom: '5%',
            left: 'center'
        },
        series: [
            {
                name: 'Matrículas',
                type: 'pie',
                radius: '39%',
                data: [
                    {
                        value: {{ $numeroMatriculaPagas }},
                        name: 'Pagas',
                        itemStyle: {
                            color: '#4CAF50' // verde
                        }
                    },
                    {
                        value: {{ $numeroMatriculaPendentes }},
                        name: 'Pendentes',
                        itemStyle: {
                            color: '#F44336' // vermelho
                        }
                    }
                ],
                label: {
                    show: true,
                    formatter: '{b}: {c} MT ({d}%)',
                    fontSize: 14
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };

    myChart.setOption(option);
</script>


@endpush
