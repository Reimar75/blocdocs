<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('minutes_to_hours', [$this, 'minutesToHoursFilter']),
        ];
    }

    public function minutesToHoursFilter(int $minutes): string
    {
        $hours = \floor($minutes / 60);
        $minutesRest = ($minutes % 60) / 60;

        if ($minutesRest === 0) {
            return $hours . 'h';
        }

        return $hours . ',' . \rtrim($minutesRest * 100, '0') . 'h';
    }
}
