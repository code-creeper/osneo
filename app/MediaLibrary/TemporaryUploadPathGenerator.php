<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TemporaryUploadPathGenerator extends \Spatie\MediaLibraryPro\Support\TemporaryUploadPathGenerator
{
    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');

        $prefix = $prefix ? "$prefix/temp" : "temp";

        $key = md5($media->uuid . $media->getKey());

        return $prefix . '/' . $key;
    }
}
