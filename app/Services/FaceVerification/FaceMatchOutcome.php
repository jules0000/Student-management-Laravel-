<?php

namespace App\Services\FaceVerification;

final class FaceMatchOutcome
{
    public function __construct(
        public bool $verified,
        public ?float $distance,
        public ?string $errorMessage,
    ) {}

    public static function failure(string $message): self
    {
        return new self(false, null, $message);
    }
}
