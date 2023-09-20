<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(EntityManagerInterface $entityManager, Request $request): Response
    {

        $postEntity = $entityManager->getRepository(Post::class);
        if ($request->query->get('q')) {
            $posts = $postEntity->createQueryBuilder('p')
                ->where('p.title LIKE :title')
                ->setParameter('title', '%' . $request->query->get('q') . '%')
                ->getQuery()
                ->getResult();
        } else {
            $posts = $postEntity->findAll();
        }
        return $this->render('blog/blog.html.twig', [
            'posts' => $posts,
        ]);
    }
}
