<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $task = new Task;
        $tasks = $this->entityManager->getRepository(Task::class)->findTasks();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $task = $form->getData();
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'tasks' => $tasks,
        ]);
    }

    #[Route('/{id}', name: 'task')]
    public function show($id): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }
    
        return $this->render('home/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit_task')]
    public function edit($id, Request $request): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $task->setName($form->get('name')->getData());
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->redirectToRoute('home');
        }  
    
        return $this->render('home/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/complete/{id}', name: 'complete_task')]
    public function complete($id): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        if($task){
            $task->setIsCompleted(true);
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            return $this->redirectToRoute('home');
        } else{
            $message = 'C\'est déjà fait';
        }

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }
    
        return $this->render('home/show.html.twig', [
            'task' => $task,
            'message' => $message,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_task')]
    public function delete($id): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        if ($task) {
            $this->entityManager->remove($task);
            $this->entityManager->flush();
            return $this->redirectToRoute('home');
        }

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }
    
        return $this->render('home/show.html.twig', [
            'task' => $task,
        ]);
    }
}
