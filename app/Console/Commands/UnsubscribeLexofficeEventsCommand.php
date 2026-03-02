<?php

namespace App\Console\Commands;

use App\Helpers\GeneralHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use LexofficeApi;

class UnsubscribeLexofficeEventsCommand extends Command
{
    protected $signature = 'lexoffice:unsubscribe-events {url?} {--all}';

    protected $description = 'Unsubscribe to all lexoffice subscribe-able events';

    public function handle(): void
    {
        $url = $this->argument('url') ?? config('app.url');
        $url = str($url)->trim('/')->value();

        if(!$url){
            $this->error("Url not defined");
            exit();
        }

        $deleteAllEvents = $this->option('all');

        $subscribedEventsPayload = LexofficeApi::get_events_all();
        $subscribedEvents = GeneralHelper::objectToArray($subscribedEventsPayload->content);

        // get events subscribed by the given url
        $subscribedEventsByUrl = Arr::where(
            $subscribedEvents,
            fn(array $event) => str($event['callbackUrl'])->contains($url)
        );

        $eventsToDelete = $deleteAllEvents ? $subscribedEvents : $subscribedEventsByUrl;

        foreach ($eventsToDelete as $event) {
            $eventUrl = str($event['callbackUrl'])->before('/webhook')->value();

            $response = LexofficeApi::delete_event($event['subscriptionId']);
            if ($response) {
                $this->info("Event unsubscribed: " .$event['eventType']. " for URL $eventUrl");
            } else {
                $this->error("Failed to unsubscribe event: " .$event['eventType']. " for URL $eventUrl");
            }
        }
    }
}
