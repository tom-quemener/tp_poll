<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\User;
use App\Entity\PollVote;
use App\Form\PollType;
use App\Repository\PollRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\PollOption;

/**
 * @Route("/poll")
 */
class PollController extends AbstractController
{
    /**
     * @Route("/", name="poll_index", methods={"GET"})
     */
    public function index(PollRepository $pollRepository): Response
    {   
        //Afficher uniquement les sondages de l'utilisateur connecté
        $user=$this->getUser();
        $id=$user->getId();
        return $this->render('poll/index.html.twig', ['polls' => $pollRepository->findBy(['idUser'=>$id], array('creationDate' => 'DESC'))]);
    }

    /**
     * @Route("/new", name="poll_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {   
        $user=$this->getUser();
        if (!$user){
            return $this->redirectToRoute('app_login');
        }
        $poll = new Poll();
        $id=$user->getId();

        $entityManager = $this->getDoctrine()->getManager();
        $poll->setIdUser($entityManager->getReference(User::class, $id));
        $form = $this->createForm(PollType::class, $poll);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($poll);
            $entityManager->flush();

            return $this->redirectToRoute('poll_index');
        }

        return $this->render('poll/new.html.twig', [
            'poll' => $poll,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="poll_show", methods={"GET","POST"})
     */
    public function show(Poll $poll, Request $request): Response
    {   
        $currentUser=$this->getUser();
        $builder = $this->createFormBuilder();
        $choices = [];
        foreach ($poll->getPollOptions() as $option) {
            $choices[$option->getName()] = $option->getId();
        }
        $builder->add('choice', ChoiceType::class, [
            'label' => 'Votre réponse:',
            'choices' => $choices,
            'expanded' => true
        ]);
        $builder->add('ok', SubmitType::class, [
            'label' => 'Voter'
        ]);
        $form = $builder->getForm();
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();

        //Recupérer l'auteur du sondage
        $user=$poll->getUser();

        if ($form->isSubmitted()) {

            //Récupérer l'id du sondage
            $pollId=$poll->getId();

            //Récupérer l'id de l'utilisateur connecté
            $userId=$currentUser->getId();

            //Récupérer l'id de l'option choisie
            $data = $form->getData();
            $optionId=$data['choice'];
            
            //Creation du vote
            $vote = new PollVote();
            
            $vote->setUser($entityManager->getReference(User::class, $userId));
            $vote->setPoll($entityManager->getReference(Poll::class, $pollId));
            $vote->setOption($entityManager->getReference(PollOption::class, $optionId));
            
            //Ajout du vote en bdd
            $entityManager->persist($vote);
            $entityManager->flush(); 

            return $this->redirectToRoute('poll_show',['id' => $pollId]);
        }

        //Vérification si l'utilisateur connecté à deja voté ou non
        $verifVote = $entityManager->getRepository(PollVote::class)->findBy(['user'=>$currentUser->getId(),'poll'=>$poll->getId()]);

        return $this->render('poll/show.html.twig', [
            'user' => $user,
            'poll' => $poll,
            'verifVote'=>$verifVote,
            'form' => $form->createView()
        ]);

        
    }

    /**
     * @Route("/{id}/edit", name="poll_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Poll $poll): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        //Verification que l'utilisateur connecté est bien l'auteur du sondage
        $currentUser=$this->getUser();
        $verifAuteur = $entityManager->getRepository(Poll::class)->findBy(['idUser'=>$currentUser->getId(),'id'=>$poll->getId()]);
        if (!$verifAuteur){
            return $this->redirectToRoute('poll_index');
        }
        $form = $this->createForm(PollType::class, $poll);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('poll_index', ['id' => $poll->getId()]);
        }

        return $this->render('poll/edit.html.twig', [
            'poll' => $poll,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="poll_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Poll $poll): Response
    {
        if ($this->isCsrfTokenValid('delete'.$poll->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($poll);
            $entityManager->flush();
        }

        return $this->redirectToRoute('poll_index');
    }
}
