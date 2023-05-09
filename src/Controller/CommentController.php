<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;
use App\Form\CommentType;
use App\Entity\Comment;

class CommentController extends CalendarController
{
    #[Route('/calendar/comment/create/date/{date}', name: 'app_calendar_comment_create')]
    public function create($date): Response
    {        
        $date = new \DateTime($date);
        $comment = (new Comment())->setDate($date);
        $formAction = $this->generateUrl('app_calendar_comment_create_submit');
        $form = $this->getForm($comment, $formAction);        

        return $this->render('calendar/_commentForm.html.twig', [
            'form' => $form->createView(),
            'dateOutput' => $this->getDateOutput($date),
        ]);
    }

    #[Route('/calendar/comment/create/submit', name: 'app_calendar_comment_create_submit')]
    public function createSubmit(Request $request): Response
    {
        $comment = new Comment();
        $form = $this->getForm($comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentRepository->save($comment, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/calendar/comment/edit/{comment}', name: 'app_calendar_comment_edit')]
    public function edit(Request $request, Comment $comment): Response
    {
        $formAction = $this->generateUrl('app_calendar_comment_edit_submit', ['comment' => $comment->getId()]);
        $form = $this->getForm($comment, $formAction);

        return $this->render('calendar/_commentForm.html.twig', [
            'form' => $form->createView(),
            'formRemove' => $this->getFormRemove($comment)->createView(),
            'dateOutput' => $this->getDateOutput($comment->getDate()),
        ]);
    }

    #[Route('/calendar/comment/edit/submit/{comment}', name: 'app_calendar_comment_edit_submit')]
    public function editSubmit(Request $request, Comment $comment): Response
    {
        $form = $this->getForm($comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentRepository->save($comment, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/calendar/comment/remote/submit/{comment}', name: 'app_calendar_comment_remove_submit')]
    public function removeSubmit(Request $request, Comment $comment): Response
    {
        $form = $this->getFormRemove($comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentRepository->remove($comment, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    private function getForm(Comment $comment, string $action = ''): FormInterface
    {        
        $options['action'] = $action;

        return $this->createForm(CommentType::class, $comment, $options);
    }    

    private function getFormRemove(Comment $comment): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_calendar_comment_remove_submit', ['comment' => $comment->getId()]))
            ->getForm();
    }
}