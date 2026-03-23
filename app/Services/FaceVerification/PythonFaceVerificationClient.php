<?php

namespace App\Services\FaceVerification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PythonFaceVerificationClient
{
    public function __construct(
        private string $baseUrl,
        private string $secret,
        private int $timeoutSeconds,
    ) {}

    public function verify(string $referenceBinary, string $probeAbsolutePath): FaceMatchOutcome
    {
        $probeContents = @file_get_contents($probeAbsolutePath);
        if ($probeContents === false || $probeContents === '') {
            return FaceMatchOutcome::failure('Unable to read attendance image.');
        }

        $request = Http::timeout($this->timeoutSeconds)
            ->attach('reference', $referenceBinary, 'reference.jpg')
            ->attach('probe', $probeContents, 'probe.jpg');

        if ($this->secret !== '') {
            $request = $request->withHeaders([
                'X-Face-Verify-Secret' => $this->secret,
            ]);
        }

        try {
            $response = $request->post($this->baseUrl.'/verify');
        } catch (\Throwable $e) {
            Log::warning('Face verify service unreachable', ['message' => $e->getMessage()]);

            return FaceMatchOutcome::failure(
                'Face verification service is unavailable. Start the Python service (see python-face-service/README.md).'
            );
        }

        if ($response->status() === 401) {
            return FaceMatchOutcome::failure('Face verification misconfigured (secret mismatch).');
        }

        if (! $response->successful()) {
            $detail = $response->json('detail');
            if (is_array($detail)) {
                $message = implode(' ', $detail);
            } else {
                $message = is_string($detail) ? $detail : ($response->body() ?: 'Face verification failed.');
            }

            return FaceMatchOutcome::failure($message);
        }

        $data = $response->json();
        $verified = (bool) ($data['verified'] ?? false);
        $distance = isset($data['distance']) ? (float) $data['distance'] : null;

        return new FaceMatchOutcome($verified, $distance, null);
    }
}
