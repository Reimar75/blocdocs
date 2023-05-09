<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;
use App\Form\EventType;
use App\Entity\Event;

class EventController extends CalendarController
{
    #[Route('/calendar/event/create/date/{date}', name: 'app_calendar_event_create')]
    public function create($date): Response
    {
        $date = new \DateTime($date);
        $event = (new Event())->setDate($date)->setColor('blue');
        $formAction = $this->generateUrl('app_calendar_event_create_submit');
        $form = $this->getForm($event, $formAction);        

        return $this->render('calendar/_eventCreateForm.html.twig', [
            'form' => $form->createView(),
            'dateOutput' => $this->getDateOutput($date),
        ]);
    }

    #[Route('/calendar/event/create/submit', name: 'app_calendar_event_create_submit')]
    public function createSubmit(Request $request): Response
    {
        $event = new Event();
        $form = $this->getForm($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventRepository->save($event, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/calendar/event/edit/{event}', name: 'app_calendar_event_edit')]
    public function edit(Request $request, Event $event): Response
    {
        $formAction = $this->generateUrl('app_calendar_event_edit_submit', ['event' => $event->getId()]);
        $form = $this->getForm($event, $formAction);

        return $this->render('calendar/_eventEditForm.html.twig', [
            'form' => $form->createView(),
            'formRemove' => $this->getFormRemove($event)->createView(),
        ]);
    }

    #[Route('/calendar/event/edit/submit/{event}', name: 'app_calendar_event_edit_submit')]
    public function editSubmit(Request $request, Event $event): Response
    {
        $form = $this->getForm($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventRepository->save($event, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/calendar/event/remote/submit/{event}', name: 'app_calendar_event_remove_submit')]
    public function removeSubmit(Request $request, Event $event): Response
    {
        $form = $this->getFormRemove($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventRepository->remove($event, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    private function getForm(Event $event, string $action = ''): FormInterface
    {
        $options['colors'] = $this->getColors();
        $options['action'] = $action;

        return $this->createForm(EventType::class, $event, $options);
    }    

    private function getFormRemove(Event $event): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_calendar_event_remove_submit', ['event' => $event->getId()]))
            ->getForm();
    }
}
