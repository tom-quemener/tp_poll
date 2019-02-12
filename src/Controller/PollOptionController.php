<?php

namespace App\Controller;

use App\Entity\PollOption;
use App\Form\PollOptionType;
use App\Repository\PollOptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Poll;

/**
 * @Route("/poll_options")
 */
class PollOptionController extends AbstractController
{
    /**
     * @Route("/list/{poll}", name="poll_option_index", methods={"GET"})
     */
    public function index(Poll $poll, PollOptionRepository $pollOptionRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        //Verification que l'utilisateur connecté est bien l'auteur du sondage
        $currentUser=$this->getUser();
        $verifAuteur = $entityManager->getRepository(Poll::class)->findBy(['idUser'=>$currentUser->getId(),'id'=>$poll->getId()]);
        if (!$verifAuteur){
            return $this->redirectToRoute('poll_index');
        }
        return $this->render('poll_option/index.html.twig', [
            'poll_options' => $pollOptionRepository->findBy(['poll' => $poll->getId()]),
            'poll' => $poll
        ]);
    }

    /**
     * @Route("/{poll}/new", name="poll_option_new", methods={"GET","POST"})
     */
    public function new(Poll $poll, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        //Verification que l'utilisateur connecté est bien l'auteur du sondage
        $currentUser=$this->getUser();
        $verifAuteur = $entityManager->getRepository(Poll::class)->findBy(['idUser'=>$currentUser->getId(),'id'=>$poll->getId()]);
        if (!$verifAuteur){
            return $this->redirectToRoute('poll_index');
        }
        $pollOption = new PollOption();
        $pollOption->setPoll($poll);
        $form = $this->createForm(PollOptionType::class, $pollOption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pollOption);
            $entityManager->flush();

            return $this->redirectToRoute('poll_option_index', [
                'poll' => $poll->getId()
            ]);
        }

        return $this->render('poll_option/new.html.twig', [
            'poll' => $poll,
            'poll_option' => $pollOption,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="poll_option_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,Poll $poll, PollOption $pollOption): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        //Verification que l'utilisateur connecté est bien l'auteur du sondage
        $currentUser=$this->getUser();
        $verifAuteur = $entityManager->getRepository(Poll::class)->findBy(['idUser'=>$currentUser->getId(),'id'=>$poll->getId()]);
        if (!$verifAuteur){
            return $this->redirectToRoute('poll_index');
        }
        $form = $this->createForm(PollOptionType::class, $pollOption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('poll_option_index', ['poll' => $pollOption->getPoll()->getId()]);
        }

        return $this->render('poll_option/edit.html.twig', [
            'poll' => $pollOption->getPoll(),
            'poll_option' => $pollOption,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="poll_option_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PollOption $pollOption): Response
    {
        $poll = $pollOption->getPoll();
        if ($this->isCsrfTokenValid('delete'.$pollOption->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pollOption);
            $entityManager->flush();
        }

        return $this->redirectToRoute('poll_option_index', [
            'poll' => $poll->getId()
        ]);
    }
}
