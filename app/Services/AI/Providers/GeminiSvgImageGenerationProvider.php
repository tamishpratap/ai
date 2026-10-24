<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\ImageGenerationProvider;
use App\Data\AI\GeneratedImageData;
use App\Exceptions\ImageGenerationException;
use DOMDocument;
use DOMElement;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GeminiSvgImageGenerationProvider implements ImageGenerationProvider
{
    private const ALLOWED_ELEMENTS = [
        'svg', 'g', 'defs', 'linearGradient', 'radialGradient', 'stop', 'clipPath',
        'mask', 'filter', 'feGaussianBlur', 'feDropShadow', 'feOffset', 'feBlend',
        'feColorMatrix', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'path', 'text', 'tspan', 'title', 'desc',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'xmlns', 'viewBox', 'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'fill', 'fill-opacity', 'stroke',
        'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-opacity', 'opacity',
        'transform', 'id', 'offset', 'stop-color', 'stop-opacity', 'gradientUnits',
        'gradientTransform', 'font-family', 'font-size', 'font-weight', 'text-anchor',
        'dominant-baseline', 'letter-spacing', 'clip-path', 'mask', 'filter', 'stdDeviation',
        'dx', 'dy', 'result', 'in', 'in2', 'mode', 'values', 'type',
    ];

    public function generate(string $prompt, string $aspectRatio, string $quality): GeneratedImageData
    {
        $key = config('ai.providers.gemini-image.key');
        if (blank($key) || blank($this->model())) {
            throw new ImageGenerationException('Free Gemini image generation is not configured.');
        }

        [$width, $height] = $this->dimensions($aspectRatio);
        $detail = match ($quality) {
            'high' => 'rich, highly detailed and layered',
            'medium' => 'polished and moderately detailed',
            default => 'clean, efficient and visually clear',
        };

        try {
            $response = Http::baseUrl(rtrim(config('gemini.base_url'), '/'))
                ->acceptJson()
                ->asJson()
                ->withHeaders(['x-goog-api-key' => $key])
                ->connectTimeout(10)
                ->timeout(config('image-generation.timeout'))
                ->post('v1beta/models/'.rawurlencode($this->model()).':generateContent', [
                    'systemInstruction' => ['parts' => [['text' => $this->systemInstruction()]]],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [['text' => "Create a {$detail} SVG illustration for this request: {$prompt}\nCanvas: {$width}x{$height} ({$aspectRatio})."]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.85,
                        'maxOutputTokens' => 8192,
                    ],
                ]);

            $response->throw();
            $text = collect($response->json('candidates.0.content.parts', []))
                ->pluck('text')->filter()->implode('');

            if (blank($text)) {
                throw new RuntimeException('Gemini returned an empty SVG response.');
            }

            return new GeneratedImageData(
                contents: $this->sanitizeSvg($text, $width, $height),
                mimeType: 'image/svg+xml',
                metadata: [
                    'provider' => $this->name(),
                    'model' => $this->model(),
                    'mode' => 'free-svg',
                    'input_tokens' => $response->json('usageMetadata.promptTokenCount'),
                    'output_tokens' => $response->json('usageMetadata.candidatesTokenCount'),
                ],
            );
        } catch (ImageGenerationException $exception) {
            throw $exception;
        } catch (RequestException $exception) {
            $this->logFailure($exception);
            $status = $exception->response?->status();

            throw new ImageGenerationException(match ($status) {
                401, 403 => 'The Google AI Studio key is invalid or does not have Gemini access.',
                429 => 'The free Gemini request limit is currently exhausted. Please wait for the quota to reset.',
                404 => 'The configured free Gemini model is unavailable.',
                default => 'Gemini could not create the vector image. Please try another prompt.',
            }, 0, $exception);
        } catch (ConnectionException $exception) {
            $this->logFailure($exception);

            throw new ImageGenerationException('Google AI Studio is temporarily unreachable.', 0, $exception);
        } catch (Throwable $exception) {
            $this->logFailure($exception);

            throw ImageGenerationException::providerUnavailable($exception);
        }
    }

    public function name(): string
    {
        return 'gemini-svg';
    }

    public function model(): string
    {
        return (string) config('image-generation.providers.gemini-svg.model');
    }

    private function systemInstruction(): string
    {
        return <<<'PROMPT'
You are an expert vector illustrator. Return exactly one complete SVG document and no markdown or explanation.
Use only SVG shapes, paths, gradients, filters, and text with inline presentation attributes. Never use scripts,
event handlers, CSS style blocks, foreignObject, embedded HTML, external URLs, href, images, animation, or data URIs.
Make the composition professional, balanced, colorful, and faithful to the user's description.
PROMPT;
    }

    private function dimensions(string $ratio): array
    {
        return match ($ratio) {
            '3:2' => [1200, 800],
            '2:3' => [800, 1200],
            '16:9' => [1280, 720],
            '9:16' => [720, 1280],
            default => [1024, 1024],
        };
    }

    private function sanitizeSvg(string $response, int $width, int $height): string
    {
        $start = stripos($response, '<svg');
        $end = strripos($response, '</svg>');
        if ($start === false || $end === false) {
            throw new ImageGenerationException('Gemini did not return a valid vector image. Please try again.');
        }

        $svg = substr($response, $start, $end + 6 - $start);
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $document->documentElement || $document->documentElement->localName !== 'svg') {
            throw new ImageGenerationException('Gemini returned malformed vector artwork. Please try again.');
        }

        $nodes = [];
        foreach ($document->getElementsByTagName('*') as $node) {
            $nodes[] = $node;
        }

        foreach (array_reverse($nodes) as $node) {
            if (! in_array($node->localName, self::ALLOWED_ELEMENTS, true)) {
                $node->parentNode?->removeChild($node);

                continue;
            }

            $this->sanitizeAttributes($node);
        }

        $root = $document->documentElement;
        $root->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $root->setAttribute('viewBox', "0 0 {$width} {$height}");
        $root->setAttribute('width', (string) $width);
        $root->setAttribute('height', (string) $height);

        return $document->saveXML($root) ?: throw new ImageGenerationException('The vector image could not be saved.');
    }

    private function sanitizeAttributes(DOMElement $node): void
    {
        $attributes = [];
        foreach ($node->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $name) {
            $value = $node->getAttribute($name);
            $isAllowed = in_array($name, self::ALLOWED_ATTRIBUTES, true)
                && ! preg_match('/(?:javascript:|data:|https?:|\/\/)/i', $value)
                && (! str_contains($value, 'url(') || preg_match('/^url\(#[A-Za-z0-9_-]+\)$/', $value));

            if (! $isAllowed) {
                $node->removeAttribute($name);
            }
        }
    }

    private function logFailure(Throwable $exception): void
    {
        Log::warning('Free Gemini SVG generation failed', [
            'exception' => $exception::class,
            'model' => $this->model(),
        ]);
    }
}
