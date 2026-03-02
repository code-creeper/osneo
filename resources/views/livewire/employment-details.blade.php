<div class="row">
    <x-loader/>
    <x-errors/>

    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-success">
            <div class="card-body">
                <h6 class="text-uppercase mt-0">Zeitraum</h6>
                <h5 class="mt-3 mb-3">{{ $employment_start->format('d. F Y') }} - {{ $employment_end->format('d. F Y') }}</h5>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="table-responsive">
            <table class="table mb-5 w-100 nowrap">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    @foreach($overview as $year => $months)
                        <th scope="col" colspan="{{count($months)}}">{{$year}}</th>
                    @endforeach
                </tr>
                <tr>
                    <th scope="col">#</th>
                    @foreach($overview as $year => $months)
                        @foreach($months as $month => $value)
                            <th scope="col">{{$month}}</th>
                        @endforeach
                    @endforeach
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row">Überstunden</th>
                    @foreach($overview as $year => $months)
                        @foreach($months as $month => $value)
                            <td>{{formatHrs($value['overtime'], true)}}</td>
                        @endforeach
                    @endforeach
                </tr>
                <tr>
                    <th scope="row">Auszahlung</th>
                    @foreach($overview as $year => $months)
                        @foreach($months as $month => $value)
                            <td>{{formatHrs($value['payouts'], true)}}</td>
                        @endforeach
                    @endforeach
                </tr>
                <tr>
                    <th scope="row">Summe</th>
                    @foreach($overview as $year => $months)
                        @foreach($months as $month => $value)
                            <td>{{formatHrs($value['total'], true)}}</td>
                        @endforeach
                    @endforeach
                </tr>
                <tr>
                    <th scope="row">Gesamt</th>
                    @foreach($overview as $year => $months)
                        @foreach($months as $month => $value)
                            <td>{{formatHrs($value['grandTotal'], true)}}</td>
                        @endforeach
                    @endforeach
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col">
        <div class="table-responsive">
            <table class="table mb-0">
                @foreach($details as $year => $months)
                    @foreach($months as $month => $days)
                        <thead>
                        @if($month == 'January')
                            <tr>
                                <th scope="col" colspan="4">{{$year}}</th>
                            </tr>
                        @endif

                        <tr>
                            <th scope="col" colspan="4">{{$month}}</th>
                        </tr>

                        <tr>
                            <th scope="col">Datum</th>
                            <th scope="col">IST</th>
                            <th scope="col">SOLL</th>
                            <th scope="col">Überstunden</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $value)
                                <tr>
                                    <td>{{ $value['date']->format('d. m Y') }}</td>
                                    <td>
                                        @if($value['date']->isOffDay())
                                            Frei
                                        @elseif($value['date']->isHoliday())
                                            Feiertag
                                        @elseif($value['date']->isWeekend())
                                            Wochenende
                                        @elseif($value['leaves'])
                                            {{ $value['leaves']->reason_name }}
                                        @else
                                            {{ formatHrs($value['target_hours'], true) }}
                                        @endif
                                    </td>
                                    <td>{{ formatHrs($value['working_hours'], true) }}</td>
                                    <td>{{ formatHrs($value['overtime'], true) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                  @endforeach
            </table>
        </div>
    </div>
</div>
