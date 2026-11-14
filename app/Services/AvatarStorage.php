<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;

class AvatarStorage
{
    public function store(UploadedFile $file): string
    {
        try {
            $image = ImageManager::gd()->read($file->getRealPath())->cover(256, 256)->toJpeg(85);
        } catch (DecoderException $exception) {
            throw ValidationException::withMessages(['avatar' => 'The image could not be read. Choose another picture.']);
        }
        $path = 'avatars/'.Str::uuid().'.jpg';
        if (! Storage::disk('public')->put($path, (string) $image)) {
            throw ValidationException::withMessages(['avatar' => 'The picture could not be saved. Please try again.']);
        }

        return $path;
    }
}
