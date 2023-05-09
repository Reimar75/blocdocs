<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\EventRepository;
use App\Repository\BlockRepository;
use App\Repository\CommentRepository;

class CalendarController extends AbstractController
{
    public function __construct(protected EventRepository $eventRepository, protected TranslatorInterface $translator, protected BlockRepository $blockRepository, protected CommentRepository $commentRepository)
    {
    }

    #[Route('/calendar', name: 'app_calendar')]
    public function index(): Response
    {
        $currentDate = new \DateTime();
        $startDate = (clone $currentDate)->modify('-3 months');
        $endDate = $currentDate;

        if (\date('N') >= 6) {
            $endDate->modify('+7 days');
        }

        $weeks = $this->generateWeeks($startDate, $endDate);
        $events = $this->getEvents($startDate);
        $blocks = $this->blockRepository->findByDateWithin($startDate, $endDate);
        $comments = $this->commentRepository->findByDateWithin($startDate, $endDate);
        $this->aggregateData($weeks, $events, $blocks, $comments);

        return $this->render('calendar/index.html.twig', [
            'weeks' => $weeks,
            'events' => $events,
            'blocks' => $blocks,
        ]);
    }

    private function generateWeeks(\DateTime $startDate, \DateTime $endDate): array
    {
        $weeks = [];
        $startDate->modify('next Monday');

        while ($startDate <= $endDate) {
            $weekStart = clone $startDate;
            $weekEnd = (clone $startDate)->modify('this week +6 days');
            $days = $this->generateDays(clone $weekStart, clone $weekEnd);

            $weeks[] = [
                'start' => $weekStart,
                'end' => $weekEnd,
                'days' => $days,
            ];

            $startDate->modify('+1 week');
        }

        return \array_reverse($weeks);
    }

    private function getEvents(\DateTime $dateFrom): array
    {
        $events = [];

        foreach ($this->eventRepository->findAll() as $event) {
            $date = $event->getDate()->format('Y-m-d');
            $events[$date][] = $event ?? [];
        }

        return $events;
    }

    private function generateDays(\DateTime $startDate, \DateTime $endDate): array
    {
        $days = [];

        while ($startDate <= $endDate) {
            $days[] = clone $startDate;
            $startDate->modify('+1 day');
        }

        return $days;
    }

    protected function getDateOutput(\DateTime $date): string
    {
        return match ($date->format('Y-m-d')) {
            \date('Y-m-d') => $this->translator->trans('base.time.output.today'),
            \date('Y-m-d', \strtotime('-2 day')) => $this->translator->trans('base.time.output.before_yesterday'),
            \date('Y-m-d', \strtotime('-1 day')) => $this->translator->trans('base.time.output.yesterday'),
            \date('Y-m-d', \strtotime('+1 day')) => $this->translator->trans('base.time.output.tomorrow'),
            \date('Y-m-d', \strtotime('+2 day')) => $this->translator->trans('base.time.output.after_tomorrow'),
            default => $date->format('D, d.m.')
        };
    }

    private function aggregateData(array &$weeks, array $events, array $blocks, array $comments): void
    {
        $colors = $this->getColors();

        foreach ($weeks as &$week) {
            $week['time'] = 0;
            $week['colors'] = [];
            $week['blocks'] = [];
            $week['comments'] = [];

            foreach ($week['days'] as $day) {
                $date = $day->format('Y-m-d');
                $eventsForDay = $events[$date] ?? [];

                foreach ($eventsForDay as $event) {
                    $week['time'] += $event->getTime();
                    $week['colors'][$event->getColor()]['time'] = ($week['colors'][$event->getColor()]['time'] ?? 0) + $event->getTime();
                }
            }

            foreach ($week['colors'] as &$color) {
                $colorTime = $color['time'];
                $percentage = $week['time'] > 0 ? ($colorTime / $week['time']) * 100 : 100;
                $color['time'] = $colorTime;
                $color['percentage'] = \round($percentage, 2);
            }

            \uksort($week['colors'], function($a, $b) use ($colors) {
                $posA = \array_search($a, $colors);
                $posB = \array_search($b, $colors);
    
                return $posA - $posB;
            });

            foreach ($blocks as $block) {                
                $isWithinTheWeek = $block->getDateFrom() >= $week['start'] && $block->getDateTo() <= $week['end'];
                $startsInWeek = $block->getDateFrom() >= $week['start'] && $block->getDateFrom() <= $week['end'] && !$isWithinTheWeek;
                $endsInWeek = $block->getDateTo() >= $week['start'] && $block->getDateTo() <= $week['end'] && !$isWithinTheWeek;
                $overlapsTheWeek = $block->getDateFrom() < $week['start'] && $block->getDateTo() > $week['end'];                

                if ($isWithinTheWeek || $startsInWeek || $endsInWeek || $overlapsTheWeek) {
                    $week['blocks'][] = [
                        'block' => $block,
                        'starts' => $startsInWeek ? $block->getDateFrom()->format('w') : false,
                        'ends' => $endsInWeek ? $block->getDateTo()->format('w') : false,
                        'overlaps' => $overlapsTheWeek,
                        'within' => $isWithinTheWeek ? ['starts' => $block->getDateFrom()->format('N'), 'ends' => $block->getDateTo()->format('N')] : false,
                    ];
                }                
            }   

            foreach ($comments as $comment) {
                if ($comment->getDate() >= $week['start'] && $comment->getDate() <= $week['end']) {
                    $week['comments'][$comment->getDate()->format('Y-m-d')] = $comment;
                }
            }
        }        
    }

    protected function getColors(): array
    {
        return ['green', 'yellow', 'blue', 'purple', 'red'];
    }
}
