@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="row">
    <div class="col-md-8 col-sm-8">
        <div class="calendar-nav">
            <div class="btn-group">
                <a href="{{route('estatisticaFinanceira.index')}}"><button class="btn btn-default @if($escola==0) active @endif">Todas as Escolas</button></a>
                <a href="{{route('estatisticaFinanceira.show',['escola'=>2])}}"><button class="btn btn-default @if($escola==2) active @endif">Escola Lhaysso</button></a>
                <a href="{{route('estatisticaFinanceira.show',['escola'=>1])}}"><button class="btn btn-default @if($escola==1) active @endif">Escola Oriente</button></a>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4">
        @if($escola==0)
            <h3><b>Estatistica de Todas as Escolas</b></h3>
        @elseif($escola==2)
            <h3><b>Estatistica da Escola Lhaysso</b></h3>
        @elseif($escola==1)
            <h3><b>Estatistica da Escola Oriente</b></h3>
        @endif
    </div>
</div>

<br>

<div class="row">
    
     <div class="col-md-3 col-sm-3" >
        <div class="widget-stat card bg-primary card-shadow" style="background-color: green; color: white">
            <div class="card-body p-4">
                <div class="media">
                    <div class="media-body text-white" >
                        <p class="mb-1">Total Matricula</p>
                        <h4 class="text-white">{{ number_format($totalValorMatricula, 2, ',', '.') }}</h4>
                        <div class="progress mb-2 bg-secondary">
                            <div class="progress-bar progress-animated bg-white" style="width: 100%;"></div>
                        </div>
                        <h6>Matriculados: {{ number_format($totalMatriculados, 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-3">
        <div class="widget-stat card bg-success card-shadow" style="background-color: red; color: white">
            <div class="card-body p-4">
                <div class="media">
                    <div class="media-body text-white" >
                        <p class="mb-1">Propinas ate {{$mes}} </p>
                        <h4 class="text-white">{{ number_format($totalPropinaActual, 2, ',', '.') }}</h4>
                        <div class="progress mb-2 bg-primary">
                            <div class="progress-bar progress-animated bg-white" style="width: 100%"></div>
                        </div>
                        <h6>Adia.: {{ number_format($totalPropinaAdiantamento, 2, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-3">
        <div class="widget-stat card bg-warning card-shadow" style="background-color: red; color: white">
            <div class="card-body p-4">
                <div class="media">
                    <div class="media-body text-white" >
                        <p class="mb-1">Pagamentos ({{$mes}})</p>
                        <h4 class="text-white">{{ number_format($pagamentosMesActual, 2, ',', '.') }} </h4>
                        <div class="progress mb-2 bg-primary">
                            <div class="progress-bar progress-animated bg-white" style="width: 100%"></div>
                        </div>
                        <h6>Previsao: {{ number_format($matriculaPendentes+$totalPropinasPendenteAteMesActual, 2, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-3">
        <div class="widget-stat card bg-danger card-shadow" style="background-color: red; color: white">
            <div class="card-body p-4">
                <div class="media">
                    <div class="media-body text-white" >
                        <p class="mb-1">Salarios</p>
                        <h4 class="text-white">{{ number_format($totalSalarioFuncionario, 2, ',', '.') }}</h4>
                        <div class="progress mb-2 bg-primary">
                            <div class="progress-bar progress-animated bg-white" style="width: 100%"></div>
                        </div>
                        <h6>Funcionario: {{ number_format($totalFuncionario, 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-6 col-sm-6" style="background-color: white; padding: 10px">
        <div class="card-header card-no-border">
            <div id="matriculaPieChart" style="width: 100%; height: 300px;"></div>
        </div>
    </div>

    <div class="col-md-6 col-sm-6" style="background-color: white; padding: 10px">
        <div class="iconic-w-wrap number-rotate">
            <div id="graficoMensalidades" style="width: 100%; height: 300px;"></div>
        </div>
    </div>
</div>

<br>
<div class="row">
    <div class="col-md-12 col-sm-12" style="background-color: white; padding: 10px">
        <div class="iconic-w-wrap number-rotate">
            <div id="graficoMensalidadesContagem" style="width: 100%; height: 400px;"></div>
        </div>
    </div>
</div>

@endsection

@push('js')

<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var chartDom = document.getElementById('graficoMensalidades');
        var myChart = echarts.init(chartDom);

        var option = {
            title: {
                text: 'Mensalidades Pagas por Mês',
                left: 'center'
            },
            tooltip: {
                trigger: 'item'
            },
            legend: {
                bottom: '0%',
                left: 'center'
            },
            series: [
                {
                    name: 'Valor Pago',
                    type: 'pie',
                    radius: '50%',
                    data: @json($dadosGrafico),
                    label: {
                        show: true,
                        formatter: function (params) {
                            let valorFormatado = params.value.toLocaleString('de-DE'); // ou 'pt-PT'
                            return `${params.name}: ${valorFormatado} MT (${params.percent}%)`;
                        },
                        //formatter: '{b}: {c} MT ({d}%)',
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
    });
</script>

<script>
    var chartDom = document.getElementById('matriculaPieChart');
    var myChart = echarts.init(chartDom);

    var option = {
        title: {
            text: 'Matrículas - Pagas vs Pendentes',
            left: 'center'
        },
        tooltip: {
            trigger: 'item'
        },
        legend: {
            bottom: '0%',
            left: 'center'
        },
        series: [
            {
                name: 'Matrículas',
                type: 'pie',
                radius: '50%',
                data: [
                    {value: {{ $totalValorMatricula }}, name: 'Pagas'},
                    {value: {{ $matriculaPendentes }}, name: 'Pendentes'}
                ],
                label: {
                    formatter: function (params) {
                        let valor = params.value.toLocaleString('de-DE');
                        return `${params.name}: ${valor} (${params.percent}%)`;
                    },
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
</script>



@endpush
