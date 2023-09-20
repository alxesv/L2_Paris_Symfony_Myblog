<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Spipu\Html2Pdf\Html2Pdf;



class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post_front')]
    public function listFront(EntityManagerInterface $entityManager): Response
    {
        $postEntity = $entityManager->getRepository(Post::class);
        $posts = $postEntity->findAll();

        return $this->render('post/post.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/back/post', name: 'app_post')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $postEntity = $entityManager->getRepository(Post::class);
        $posts = $postEntity->findBy(["user" => $this->getUser()], ["createdAt" => "ASC"]);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/back/post/edit/{id}', name: 'app_post_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        $form = $this->createForm(PostType::class, $post);
        //dd($request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/editForm.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/back/post/delete/{id}', name: 'app_post_delete')]
    public function delete(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $postRepository = $entityManager->getRepository(Post::class);
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas.');
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_post');
    }

    #[Route('/post/show/{id}', name: 'app_post_show')]
    public function show(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $postEntity = $entityManager->getRepository(Post::class);
        $post = $postEntity->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas.');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/download/{id}', name: 'app_post_download')]
    public function downloadAsPdf($id, EntityManagerInterface $entityManager): void
    {
        $html2pdf = new Html2Pdf();
        $postEntity = $entityManager->getRepository(Post::class);
        $post = $postEntity->find($id);
        $html = $this->renderView('post/dl.show.html.twig', [
            'post' => $post,
        ]);
        $html2pdf->writeHTML($html);
        $name = $post->getSlug();
        $html2pdf->output("$name.pdf");
    }

    /**
     * @throws \Exception
     */
    #[Route('/back/post/create', name: 'app_post_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $post->setUser($this->getUser());

            $post->setImage("/Users/lucasdeniel/Downloads/MyFlow/Snapchat-1967704983.jpg");

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
