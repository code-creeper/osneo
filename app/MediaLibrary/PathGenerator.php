<?php

namespace App\MediaLibrary;


use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator as BasePathGenerator;

class PathGenerator implements BasePathGenerator
{
    /*
        * Get the path for the given media, relative to the root storage path.
        */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $baseDir = 'media';
        $model = new ($media->model_type);
        $mediaModelBaseDir = $model->mediaDir ?? $model->getTable();
        $mediaModelDir = $media->model_id;
        $mediaDir = $media->getKey();

        unset($model);

        $basePath = "$baseDir/$mediaDir";

        if ($mediaModelBaseDir && $mediaDir){
            $basePath = "$baseDir/$mediaModelBaseDir/$mediaModelDir/$mediaDir";
        }

        return $basePath;
    }
}
