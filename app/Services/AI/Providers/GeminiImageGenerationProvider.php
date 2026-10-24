<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\ImageGenerationProvider;
use App\Data\AI\GeneratedImageData;
use App\Exceptions\ImageGenerationException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Image;
use Throwable;

class GeminiImageGenerationProvider implements ImageGenerationProvider
{
    public function generate(string $prompt, string $aspectRatio, string $quality): GeneratedImageData
    {
        if (blank(config('ai.providers.gemini-image.key')) || blank($this->model())) {
            throw new ImageGenerationException('Image generation is not configured. Please contact support.');
        }

        try {
            $response = Image::of($prompt)
                ->size($aspectRatio)
                ->quality($quality)
                ->timeout(config('image-generation.timeout'))
                ->generate('gemini-image', $this->model());

            if ($response->count() < 1) {
                throw new ImageGenerationException('The AI did not return an image. Please refine your prompt and try again.');
            }

            $image = $response->firstImage();

            return new GeneratedImageData(
                contents: $image->content(),
                mimeType: $image->mime ?: 'image/png',
                metadata: [
                    'provider' => $response->meta->provider,
                    'model' => $response->meta->model,
                    'input_tokens' => $response->usage->promptTokens,
                    'output_tokens' => $response->usage->completionTokens,
                ],
            );
        } catch (ImageGenerationException $exception) {
            throw $exception;
        } catch (RateLimitedException $exception) {
            $this->logFailure($exception);

            throw new ImageGenerationException(
                'Gemini image quota is unavailable or exhausted. Enable billing for this API project, check its quota, or wait for the limit to reset.',
                0,
                $exception,
            );
        } catch (InsufficientCreditsException $exception) {
            $this->logFailure($exception);

            throw new ImageGenerationException(
                'Gemini image generation requires available paid quota. Please enable billing or add credits to the API project.',
                0,
                $exception,
            );
        } catch (ProviderOverloadedException $exception) {
            $this->logFailure($exception);

            throw new ImageGenerationException(
                'Gemini image generation is temporarily overloaded. Please try again in a few minutes.',
                0,
                $exception,
            );
        } catch (RequestException $exception) {
            $this->logFailure($exception);
            $status = $exception->response?->status();

            throw new ImageGenerationException(match ($status) {
                401, 403 => 'The Gemini image API key is invalid or does not have access to this model.',
                404 => 'The configured Gemini image model is not available for this API project.',
                default => 'Gemini rejected the image request. Please review the prompt and try again.',
            }, 0, $exception);
        } catch (Throwable $exception) {
            $this->logFailure($exception);

            throw ImageGenerationException::providerUnavailable($exception);
        }
    }

    public function name(): string
    {
        return 'gemini';
    }

    public function model(): string
    {
        return (string) config('image-generation.providers.gemini.model');
    }

    private function logFailure(Throwable $exception): void
    {
        Log::warning('Gemini image generation failed', [
            'exception' => $exception::class,
            'model' => $this->model(),
        ]);
    }
}
