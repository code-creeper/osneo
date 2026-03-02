<?php

namespace App\MediaLibrary;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{

    public function model(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScopes();
    }
}
