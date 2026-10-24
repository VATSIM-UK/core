<?php

declare(strict_types=1);

namespace App\Enums;

enum CalendarViewer: string
{
    case Student = 'student';
    case Instructor = 'instructor';
}
