<div class="card" style="height: 800px" wire:ignore>
    <div class="card-body mb-3 p-3">
        <div id='calendar' x-data="{
            init(){
                let config = $wire.config;
                let sources = $wire.eventSources;
                config.eventSources = [];

                sources.forEach(source => {
                    let sourceObject = {
                        events: function (fetchInfo, successCallback, failureCallback) {
                            $wire.$call(source.eventsCallback, fetchInfo)
                            .then(results => {
                                successCallback(results);
                            });
                        }
                    };

                    Object.assign(sourceObject, source.options);

                    config.eventSources.push(sourceObject);
                });

                let calendar = new FullCalendar.Calendar($el, config);
                calendar.render();
            }
        }"></div>
    </div>
</div>

@assets
<script src="{{ asset('js/fullcalendar.js') }}"></script>
<script src="{{ asset('js/fullcalendar-locales-all.js') }}"></script>
<link href="{{ asset('css/fullcalendar.css') }}" rel="stylesheet"/>
@endassets

