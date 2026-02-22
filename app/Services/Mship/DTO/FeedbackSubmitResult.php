<?php

namespace App\Services\Mship\DTO;

class FeedbackSubmitResult
{
    /**
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        public string $status,
        public array $errors = [],
        public ?string $message = null
    ) {}

    public static function success(): self
    {
        return new self('success');
    }

    public static function selfFeedbackError(): self
    {
        return new self('self_feedback_error', [], 'You cannot leave feedback about yourself');
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function validationFailed(array $errors): self
    {
        return new self('validation_failed', $errors);
    }

    public static function targetResolutionFailed(): self
    {
        return new self('target_resolution_failed', [], "Sorry, we can't process your feedback at the moment. Please check back later.");
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }
}
