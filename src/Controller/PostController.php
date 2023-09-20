<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use App\Form\EditPostType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Spipu\Html2Pdf\Html2Pdf;




class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post_front')]
    public function listFront(EntityManagerInterface $entityManager): Response
    {
        $postRepository = $entityManager->getRepository(Post::class);

        // Créer une requête personnalisée pour sélectionner les articles publiés
        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->where('p.publicated_at <= :currentDate')
            ->setParameter('currentDate', new \DateTime(timezone: new \DateTimeZone("Europe/Paris")));

        // Exécutez la requête
        $posts = $queryBuilder->getQuery()->getResult();

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
        $tags = $entityManager->getRepository(Tag::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $post->setUser($this->getUser());

            $imageFile = $form->get('image')->getData();

            if ($imageFile instanceof UploadedFile) {
                // Générer un nom de fichier unique en utilisant l'horodatage et une partie aléatoire
                $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion des erreurs en cas de problème lors du téléchargement
                }

                $post->setImage($newFilename);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
            'tags' => $tags,
        ]);
    }

    #[Route('/back/post/edit/{id}', name: 'app_post_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        $form = $this->createForm(EditPostType::class, $post);
        $tags = $entityManager->getRepository(Tag::class)->findAll();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile =$form->get('image')->getData();
            $previousImagePath = $post->getImage();

            if ($imageFile instanceof UploadedFile) {
                // Générer un nom de fichier unique en utilisant l'horodatage et une partie aléatoire
                $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );

                    if ($previousImagePath) {
                        $oldImagePath = $this->getParameter('brochures_directory') . '/' . $previousImagePath;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                } catch (FileException $e) {
                    // Gestion des erreurs en cas de problème lors du téléchargement
                }

                $post->setImage($newFilename);
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/editForm.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'tags' => $tags
        ]);
    }
}