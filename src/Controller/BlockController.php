<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\BlockType;
use App\Entity\Block;

class BlockController extends CalendarController
{
    #[Route('/calendar/block/create', name: 'app_calendar_block_create')]
    public function create(): Response
    {
        $date = new \DateTime();
        $block = (new Block())->setColor('blue')->setDateFrom($date)->setDateTo($date);
        $formAction = $this->generateUrl('app_calendar_block_create_submit');
        $form = $this->getForm($block, $formAction);        

        return $this->render('calendar/_blockForm.html.twig', [
            'form' => $form->createView(),            
        ]);
    }

    #[Route('/calendar/block/create/submit', name: 'app_calendar_block_create_submit')]
    public function createSubmit(Request $request): Response
    {
        $block = new Block();
        $form = $this->getForm($block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blockRepository->save($block, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/calendar/block/edit/{block}', name: 'app_calendar_block_edit')]
    public function edit(Request $request, Block $block): Response
    {
        $formAction = $this->generateUrl('app_calendar_block_edit_submit', ['block' => $block->getId()]);
        $form = $this->getForm($block, $formAction);

        return $this->render('calendar/_blockForm.html.twig', [
            'form' => $form->createView(),
            'formRemove' => $this->getFormRemove($block)->createView(),
        ]);
    }

    #[Route('/calendar/block/edit/submit/{block}', name: 'app_calendar_block_edit_submit')]
    public function editSubmit(Request $request, Block $block): Response
    {
        $form = $this->getForm($block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blockRepository->save($block, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    #[Route('/calendar/block/remote/submit/{block}', name: 'app_calendar_block_remove_submit')]
    public function removeSubmit(Request $request, Block $block): Response
    {
        $form = $this->getFormRemove($block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blockRepository->remove($block, true);
        } else {
            \dd($form->getErrors());
        }

        return $this->redirectToRoute('app_calendar');
    }

    private function getForm(Block $block, string $action = ''): FormInterface
    {
        $options['colors'] = $this->getColors();
        $options['action'] = $action;

        return $this->createForm(BlockType::class, $block, $options);
    }    

    private function getFormRemove(Block $block): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_calendar_block_remove_submit', ['block' => $block->getId()]))
            ->getForm();
    }
}