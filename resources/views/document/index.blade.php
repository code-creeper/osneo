<x-layout>
    <livewire:documents-manager :ticket-id="$ticket->id"/>

    <div class="row">
        <div class="col-xl">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4">{{ __('Sorted Documents Stats') }}</h4>
                    <div dir="ltr">
                        <div class="toolbar">
                        </div>
                        <div id="chart" class="apex-charts" data-colors="#6c757d"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('styles')
        <link href="{{ asset('vendor/jstree/themes/default/style.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/media-library.css') }}" rel="stylesheet" type="text/css"/>
    @endsection

    @section('scripts')
        <script src="{{ asset('assets/js/vendor/apexcharts.min.js') }}"></script>
        <script>
            //Chart Info
            let data = {
                annotations: {
                    yaxis: [{
                        y: 250,
                        borderColor: "#008ffb",
                        label: {
                            show: !0,
                            text: "Ziel pro Monat",
                            style: {
                                color: "#fff",
                                background: "#008ffb"
                            }
                        }
                    }, {
                        y: 15,
                        borderColor: "#feb019",
                        label: {
                            show: !0,
                            text: "Ziel pro Tag",
                            style: {
                                color: "#fff",
                                background: "#feb019"
                            }
                        }
                    }],
                },
                chart: {
                    type: "area",
                    height: 350
                },
                dataLabels: {
                    enabled: !1
                },
                series: [{
                    name: "{{ __('Month') }}",
                    data: {{ $month }}
                }, {
                    name: "{{ __('Week') }}",
                    data: {{ $week }}
                }, {
                    name: "{{ __('Day') }}",
                    data: {{ $day }}
                }],
                markers: {
                    size: 0,
                    style: "hollow"
                },
                xaxis: {
                    type: "datetime",
                    min: new Date("31 Jul 2020").getTime()
                },
                tooltip: {
                    x: {
                        format: "dd MMM yyyy"
                    }
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: .7,
                        opacityTo: .9,
                        stops: [0, 100]
                    }
                }
            }
            let chart = new ApexCharts(document.querySelector("#chart"), data);
            chart.render();
        </script>

        <script src="{{ asset('vendor/jstree/jstree.min.js') }}"></script>
    @endsection

    @section('livewire-scripts')
        <script>
            let $tree = $("body").find("#folders_tree");

            Livewire.on('updateTree', () => {
                $tree.jstree();
            });

            $tree.on("select_node.jstree", function (e, data) {
                let $node = $('#'+ data.node.id);
                let filters = $node.data('filters');
                Livewire.dispatch('treeChanged', {filters: filters})
            });
        </script>
    @endsection
</x-layout>
