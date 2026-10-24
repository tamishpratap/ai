<?php

namespace App\Services\AI;

use App\Contracts\AI\ImageGenerationProvider;
use App\Exceptions\ImageGenerationException;
use App\Models\AiImageGeneration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageGenerationService
{
    public function __construct(private readonly ImageGenerationProvider $provider) {}

    public function generate(AiImageGeneration $generation): AiImageGeneration
    {
        $result = $this->provider->generate(
            $generation->prompt,
            $generation->aspect_ratio,
            $generation->quality,
        );

        $extension = match ($result->mimeType) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'png',
        };
        $path = trim(config('image-generation.directory'), '/')
            .'/'.$generation->member_id.'/'.Str::uuid().'.'.$extension;
        $disk = Storage::disk(config('image-generation.disk'));

        if (! $disk->put($path, $result->contents)) {
            throw new ImageGenerationException('The generated image could not be saved. Please try again.');
        }

        $generation->update([
            'provider' => $this->provider->name(),
            'model' => $this->provider->model(),
            'storage_disk' => config('image-generation.disk'),
            'storage_path' => $path,
            'mime_type' => $result->mimeType,
            'status' => AiImageGeneration::STATUS_COMPLETED,
            'metadata' => $result->metadata,
            'completed_at' => now(),
        ]);

        return $generation->fresh();
    }

    public function delete(AiImageGeneration $generation): void
    {
        if ($generation->storage_path) {
            Storage::disk($generation->storage_disk)->delete($generation->storage_path);
        }

        $generation->delete();
    }
}
