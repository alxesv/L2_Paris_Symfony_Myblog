<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use App\Form\EditPostType;
use App\Form\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TagController extends AbstractController
{
    #[Route('/back/tag', name: 'app_tag')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $postEntity = $entityManager->getRepository(Tag::class);
        $tags = $postEntity->findAll();

        return $this->render('tag/index.html.twig', [
            'tags' => $tags,

        ]);
    }

    #[Route('/back/tag/delete/{id}', name: 'app_tag_delete')]
    public function delete(EntityManagerInterface $entityManager, $id): Response
    {
        $postRepository = $entityManager->getRepository(Tag::class);
        $tag = $postRepository->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Le tag n\'existe pas.');
        }

        $entityManager->remove($tag);
        $entityManager->flush();

        return $this->redirectToRoute('app_tag');
    }

    #[Route('/back/tag/create', name: 'app_tag_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($tag);
            $entityManager->flush();

            return $this->redirectToRoute('app_tag');
        }

        return $this->render('tag/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/back/tag/edit/{id}', name: 'app_tag_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $tag = $entityManager->getRepository(Tag::class)->find($id);
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
            return $this->redirectToRoute('app_tag');

        }

        return $this->render('tag/edit.html.twig', [
            'form' => $form->createView(),
            'tags' => $tag
        ]);
    }
}
