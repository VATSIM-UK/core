<?php

namespace App\Services\Training\DTO;

class ExamResubmissionDecision
{
    public function __construct(
        public bool $shouldResubmit,
        public bool $isObservationExam
    ) {}

    public static function skip(): self
    {
        return new self(false, false);
    }

    public static function forExamType(bool $isObservationExam): self
    {
        return new self(true, $isObservationExam);
    }
}
