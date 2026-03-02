<?php

namespace App\Http\Controllers;

use App\Lexoffice\LexofficeEventHandler;
use Illuminate\Http\Request;
use Log;

class WebhookController extends Controller
{

    /*
     * We need to subscribe for every event we need in our application,
     * then for every subscribed events we will receive webhook calls.
     * events can be subscribed by events Api provided by lexoffice
     * read more: https://developers.lexoffice.io/docs/#event-subscriptions-endpoint
     * */
	public function lexoffice(Request $request, LexofficeEventHandler $eventHandler){
        Log::channel('lexoffice')->debug("Webhook received: $request->eventType \n".json_encode($request->all()));

        $cacheKey = 'webhook_'.md5("{$request->eventType}_$request->resourceId");

        if (cache()->has($cacheKey)) {
            Log::channel('lexoffice')->debug("Duplicate Webhook received: $request->eventType");

            return;
        }
        cache()->put($cacheKey, now(), 2);

        if ( ! $resourceId = $request->resourceId) {
            Log::channel('lexoffice')->error("Resource ID not found");

            return;
        }

        switch ($request->eventType) {
            case 'voucher.created':
            case 'voucher.changed':
                $eventHandler->voucherUpdatedOrCreated($resourceId);
                break;
            case 'invoice.created':
            case 'invoice.changed':
                $eventHandler->invoiceUpdatedOrCreated($resourceId);
                break;
            case 'voucher.deleted':
            case 'invoice.deleted':
                $eventHandler->voucherOrInvoiceDeleted($resourceId);
                break;
            case 'payment.changed':
                $eventHandler->paymentChanged($resourceId);
                break;
        }
	}
}

