<?php

namespace Tests\Fakes;

use App\Creditreform\CreditreformApi;

class CreditreformApiFake extends CreditreformApi
{
    public function createDunningStop(string $invoiceId, string $date): mixed
    {
        return json_decode(fixture('Creditreform/create_dunningStop.json'));
    }
}
