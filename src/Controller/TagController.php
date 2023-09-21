<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use App\Form\EditPostType;
use App\Form\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $tagRepository = $entityManager->getRepository(Tag::class);
        $tags = $tagRepository->findAll();


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

        try {
            $entityManager->remove($tag);
            $entityManager->flush();
            $this->addFlash('message', 'Le tag a bien été supprimé !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du tag : ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_tag');
    }

    #[Route('/back/tag/create', name: 'app_tag_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $entityManager->persist($tag);
                $entityManager->flush();

                $this->addFlash('message', 'Le tag a bien été créé !');
            } catch (\Exception $e) {

                $this->addFlash('error', 'Une erreur s\'est produite lors de la création du tag : ' . $e->getMessage());
            }

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

            try {
                $entityManager->flush();
                $this->addFlash('message', 'Le tag a bien été modifié !');
            } catch (\Exception $e) {

                $this->addFlash('error', 'Une erreur s\'est produite lors de la modification du tag : ' . $e->getMessage());
            }
            return $this->redirectToRoute('app_tag');

        }

        return $this->render('tag/edit.html.twig', [
            'form' => $form->createView(),
            'tags' => $tag
        ]);
    }
}
