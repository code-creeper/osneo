<x-layout>
    <div class="card mb-3">
        <div class="card-header">
            <div class="float-end">
                <a href="{{ route('logs.language.clear') }}" class="btn btn-sm btn-outline-primary"><i
                        class="fal fa-plus"></i> {{ __('Clear') }}</a>
            </div>
            <h5 class="mb-0 ">{{ __('Language Variable Log') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-container">
                @if ($logs === null)
                    <div>
                        {{ __('No open variables!') }}
                    </div>
                @else
                    <table id="table-log" class="table table-sm  table-hover fs--1">
                        <thead>
                        <tr>
                            <th>{{ __('Language') }}</th>
                            <th>{{ __('Variable') }}</th>
                            <th>{{ __('Occurred') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($logs as $log)
                            <tr>
                                <td class="text">{{$log->language}}</td>
                                <td class="text">{{$log->description}}</td>
                                <td class="text">{{$log->occurred}}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                @endif
            </div>
            {{ $logs->links() }}
        </div>
    </div>
</x-layout>
