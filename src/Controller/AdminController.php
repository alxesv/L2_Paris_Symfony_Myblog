<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\passwordResetType;
use App\Form\UserType;
use ContainerQZrESGT\getChangePasswordFormTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[Route('{id}/show', name: 'app_admin_show', methods: ['GET', 'POST'])]
    public function show(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/show.html.twig', [
        ]);
    }
    #[Route('{id}/edit', name: 'app_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/edit.html.twig', [
            'user' => $this->getUser(),
            'form' => $form,
        ]);
    }
    #[Route('{id}/password_reset', name: 'app_admin_reset_pass', methods: ['GET', 'POST'])]
    public function passwordReset(Request $request, User $user, EntityManagerInterface $entityManager,  UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(passwordResetType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $newHashedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData());
            $user->setPassword($newHashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/_password_reset.html.twig', [
            'user' => $this->getUser(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}