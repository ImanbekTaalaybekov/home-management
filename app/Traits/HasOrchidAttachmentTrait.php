<?php

namespace App\Traits;

use Orchid\Attachment\Models\Attachment;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasOrchidAttachmentTrait
{

    /**
     * @param Attachment $attachment
     * @param string $collectionName
     * @param bool $onlyOne
     * @return Media
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function attachmentToMedia(Attachment $attachment, string $collectionName, bool $onlyOne = false): Media
    {
        if ($onlyOne === true) {
            $this->clearMediaCollection($collectionName);
        }

        $media = $this->addMediaFromDisk($attachment->physicalPath(), 'public')
            ->preservingOriginal()
            ->toMediaCollection($collectionName);

        // bind attachment
        $media->forceFill(['attachment_id' => $attachment->getKey()])->save();

        return $media;
    }

}