<?php

namespace App\Console\Commands;

use App\Helpers\GeneralHelper;
use LexofficeApi;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SubscribeLexofficeEventsCommand extends Command
{
    protected $signature = 'lexoffice:subscribe-events {url?}';

    protected $description = 'Subscribe to all lexoffice subscribe-able events';

    public function handle(): void
    {
        $url = $this->argument('url') ?? config('app.url');
        $url = str($url)->trim('/')->value();

        if(!$url){
            $this->error("Url not defined");
        }

        $callbackUrl = "$url/webhook/lexoffice";

        $subscribedEventsPayload = LexofficeApi::get_events_all();
        $subscribedEvents = GeneralHelper::objectToArray($subscribedEventsPayload->content);

        // get events subscribed by the given url
        $subscribedEventsByUrl = Arr::where(
            $subscribedEvents,
            fn(array $event) => str($event['callbackUrl'])->contains($url)
        );

        $subscribedEventsTypes = Arr::pluck($subscribedEventsByUrl, 'eventType');

        $eventsNotSubscribed = array_diff(config('lexoffice.events'), $subscribedEventsTypes);

        foreach ($eventsNotSubscribed as $event){
            $response = LexofficeApi::create_event($event, $callbackUrl);
            if ($response){
                $this->info("Event subscribed: $event for URL $url");
            } else {
                $this->error("Failed to subscribe event: $event for URL $url");
            }
        }
    }
}
