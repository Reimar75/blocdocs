<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;
use App\Form\EventType;
use App\Entity\Event;
use App\Repository\EventRepository;

#[Route('/calendar')]
class CalendarController extends AbstractController
{
    public function __construct(private EventRepository $eventRepository)
    {
    }

    #[Route('/', name: 'app_calendar')]
    public function index(): Response
    {
        $currentDate = new \DateTime();
        $startDate = (clone $currentDate)->modify('-3 months');
        $endDate = $currentDate;

        if (\date('N') >= 4) {
            $endDate->modify('+7 days');
        }

        $weeks = $this->generateWeeks($startDate, $endDate);
        $events = $this->getEvents($startDate);
        $this->calcData($weeks, $events);

        return $this->render('calendar/index.html.twig', [
            'weeks' => $weeks,
            'events' => $events,
        ]);
    }

    #[Route('/create/{date}', name: 'app_calendar_event_create')]
    public function create(Request $request, $date): Response
    {
        $date = new \DateTime($date);
        $event = (new Event())->setDate($date)->setColor('blue');
        $formAction = $this->generateUrl('app_calendar_event_create_submit');
        $form = $this->getFormEvent($event, $formAction);
        $dateOutput = $this->getDateOutput($date);

        return $this->render('calendar/_eventCreateForm.html.twig', [
            'form' => $form->createView(),
            'dateOutput' => $dateOutput,
        ]);
    }

    #[Route('/create-submit', name: 'app_calendar_event_create_submit')]
    public function createSubmit(Request $request): Response
    {
        $event = new Event();
        $form = $this->getFormEvent($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventRepository->save($event, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/edit/{event}', name: 'app_calendar_event_edit')]
    public function edit(Request $request, Event $event): Response
    {
        $formAction = $this->generateUrl('app_calendar_event_edit_submit', ['event' => $event->getId()]);
        $form = $this->getFormEvent($event, $formAction);

        return $this->render('calendar/_eventEditForm.html.twig', [
            'form' => $form->createView(),
            'formRemove' => $this->getFormRemoveEvent($event)->createView(),
        ]);
    }

    #[Route('/edit-submit/{event}', name: 'app_calendar_event_edit_submit')]
    public function editSubmit(Request $request, Event $event): Response
    {
        $form = $this->getFormEvent($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventRepository->save($event, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/remove-submit/{event}', name: 'app_calendar_event_remove_submit')]
    public function removeSubmit(Request $request, Event $event): Response
    {
        $form = $this->getFormRemoveEvent($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventRepository->remove($event, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    private function getFormEvent(Event $event, string $action = ''): FormInterface
    {
        $options['colors'] = $this->getColors();
        $options['action'] = $action;

        return $this->createForm(EventType::class, $event, $options);
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

    private function generateDays(\DateTime $startDate, \DateTime $endDate): array
    {
        $days = [];

        while ($startDate <= $endDate) {
            $days[] = clone $startDate;
            $startDate->modify('+1 day');
        }

        return $days;
    }

    private function getDateOutput(\DateTime $date): string
    {
        return match ($date->format('Y-m-d')) {
            \date('Y-m-d') => 'heute',
            \date('Y-m-d', \strtotime('-1 day')) => 'gestern',
            default => $date->format('D, d.m.')
        };
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

    private function getFormRemoveEvent(Event $event): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_calendar_event_remove_submit', ['event' => $event->getId()]))
            ->getForm();
    }

    private function calcData(array &$weeks, array $events): void
    {
        $colors = $this->getColors();

        foreach ($weeks as &$week) {
            $week['time'] = 0;
            $week['colors'] = [];

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
                $percentage = ($colorTime / $week['time']) * 100;
                $color['time'] = $colorTime;
                $color['percentage'] = \round($percentage, 2);
            }

            \uksort($week['colors'], function($a, $b) use ($colors) {
                $posA = \array_search($a, $colors);
                $posB = \array_search($b, $colors);
    
                return $posA - $posB;
            });
        }
    }

    private function getColors(): array
    {
        return ['green', 'yellow', 'blue', 'purple', 'red'];
    }
}
