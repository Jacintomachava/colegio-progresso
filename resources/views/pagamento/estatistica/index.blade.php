@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="row">
    <div class="col-md-3 col-sm-3" >
        <div class="widget-stat card bg-primary card-shadow" style="background-color: green; color: white">
            <div class="card-body p-4">
                <div class="media">
                    <div class="media-body text-white" >
                        <p class="mb-1">Receitas</p>
                        <h4 class="text-white">{{ number_format($totalReceita, 2, ',', '.') }}</h4>
                        <div class="progress mb-2 bg-secondary">
                            <div class="progress-bar progress-animated bg-white" style="width: 100%;"></div>
                        </div>
                        <h6>100%</h6>
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
                        <p class="mb-1">Despesas</p>
                        <h4 class="text-white">{{ number_format($totalDespesa, 2, ',', '.') }}</h4>
                        <div class="progress mb-2 bg-primary">
                            <div class="progress-bar progress-animated bg-white" style="width: 100%"></div>
                        </div>
                        <h6>100%</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-3">
        <div class="widget-stat card bg-purple card-shadow" style="background-color: blue; color: white">
            <div class="card-body p-4">
                <div class="media">
                    <div class="media-body text-white" >
                        <p class="mb-1">Por Receber</p>
                        <h4 class="text-white">{{ number_format($totalReceitaPendente, 2, ',', '.') }}</h4>
                        <div class="progress mb-2 bg-primary">
                            <div class="progress-bar progress-animated bg-white" style="width: 50%"></div>
                        </div>
                        <h6>100%</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-3">
        <div class="widget-stat card bg-warning card-shadow" style="background-color: orange; color: white">
            <div class="card-body p-4">
                <div class="media">
                    <div class="media-body text-white" >
                        <p class="mb-1">Por Pagar</p>
                        <h4 class="text-white">{{ number_format($totalDespesaPendente, 2, ',', '.') }}</h4>
                        <div class="progress mb-2 bg-secondary">
                            <div class="progress-bar progress-animated bg-white" style="width: 50%"></div>
                        </div>
                        <h6>100%</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-6 col-sm-6" style="background-color: white; padding: 10px">
        <div class="card-header card-no-border">
            <span class="stat-w-title">Receitas</span>
            <div id="receitas-chart" style="width: 600px; height: 300px;"></div>
        </div>
    </div>

    <div class="col-md-6 col-sm-6" style="background-color: white; padding: 10px">
        <div class="iconic-w-wrap number-rotate">
            <span class="stat-w-title">Despesas</span>
            <div id="despesas-chart" style="width: 600px; height: 300px;"></div>
        </div>
    </div>
</div>

<br>
<div class="row">

    <div class="col-md-12 col-sm-12" style="background-color: white; padding: 10px">
        <div class="iconic-w-wrap number-rotate">
            <div id="chart" style="width: 100%; height: 300px;"></div>
        </div>
    </div>
</div>

@endsection

@push('js')

<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<script>
        // Dados vindos do Laravel
        const despesas = @json($despesas);
        const receitas = @json($receitas);

        // Gráfico de Despesas
        const despesasChart = echarts.init(document.getElementById('despesas-chart'));
        despesasChart.setOption({
            title: {
                left: 'center',
            },
            tooltip: {
                trigger: 'item',
            },
            legend: {
                bottom: '0%',
            },
            series: [
                {
                    name: 'Despesas',
                    type: 'pie',
                    radius: '38%',
                    label: {
                        show: true, // Exibe os rótulos
                        formatter: '{b}: {c} ({d}%)', // Formato: Nome: Valor (Percentual)
                    },
                    data: despesas,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)',
                        },
                    },
                },
            ],
        });

        // Gráfico de Receitas
        const receitasChart = echarts.init(document.getElementById('receitas-chart'));
        receitasChart.setOption({
            title: {
                left: 'center',
            },
            tooltip: {
                trigger: 'item',
            },
            legend: {
                bottom: '0%',
            },
            series: [
                {
                    name: 'Receitas',
                    type: 'pie',
                    radius: '38%',
                    label: {
                        show: true, // Exibe os rótulos
                        formatter: '{b}: {c} ({d}%)', // Formato: Nome: Valor (Percentual)
                    },
                    data: receitas,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)',
                        },
                    },
                },
            ],
        });
    </script>


  <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dados do gráfico enviados pelo backend
        const chartData = @json($chartData);

        // Inicializa o gráfico ECharts
        const chart = echarts.init(document.getElementById('chart'));

        const option = {
            title: {
                text: 'Pagamentos por Mês',
                subtext: 'Cancelados, Pagos e Não Pagos',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['Cancelado', 'Pago', 'Não Pago'],
                bottom: 0
            },
            xAxis: {
                type: 'category',
                data: chartData.meses
            },
            yAxis: {
                type: 'value',
                name: 'Valor Total'
            },
            series: [
                {
                    name: 'Cancelado',
                    type: 'line',
                    data: chartData.cancelado
                },
                {
                    name: 'Pago',
                    type: 'line',
                    data: chartData.pago
                },
                {
                    name: 'Não Pago',
                    type: 'line',
                    data: chartData.nao_pago
                }
            ]
        };

        chart.setOption(option);
    });
  </script>

@endpush
