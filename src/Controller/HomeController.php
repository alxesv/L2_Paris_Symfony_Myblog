<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function blog(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {

        $tagEntity = $entityManager->getRepository(Tag::class);
        $allTags = $tagEntity->findAll();
        $postEntity = $entityManager->getRepository(Post::class);
        if ($request->query->get('q')) {
            $posts = $postEntity->createQueryBuilder('p')
                ->where('p.title LIKE :title')
                ->setParameter('title', '%' . $request->query->get('q') . '%')
                ->andWhere('p.publicated_at <= :currentDate')
                ->setParameter('currentDate', new \DateTime(timezone: new \DateTimeZone("Europe/Paris")))
                ->orderBy('p.publicated_at', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $posts = $postEntity->createQueryBuilder('p')
                ->where('p.publicated_at <= :currentDate')
                ->setParameter('currentDate', new \DateTime(timezone: new \DateTimeZone("Europe/Paris")))
                ->orderBy('p.publicated_at', 'DESC')
                ->getQuery()
                ->getResult();
        }
        if($request->query->get('tag')){
            $tag = $tagEntity->findOneBy(['name' => $request->query->get('tag')]);
            foreach ($posts as $key => $post) {
                if($post->getTag()->contains($tag)){
                    continue;
                }else{
                    unset($posts[$key]);
                }
            }
        }

        $pagination = $paginator->paginate(
            $posts,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('blog/blog.html.twig', [
            'posts' => $pagination,
            'tags' => $allTags,
        ]);
    }

    #[Route('/comment/{postId}/new', name: 'app_add_comment', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function commentNew(
        #[CurrentUser] User $user,
        Request $request,
        #[MapEntity(mapping: ['postId' => 'id'])] Post $post,
        EntityManagerInterface $entityManager,
    ): Response {
        $comment = new Comment();
        $comment->setAuthor($user);
        $post->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPublicatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('message', 'Commentaire ajouté avec succès');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }else{
            $this->addFlash('error', 'Une erreur est survenue lors de l\'ajout du commentaire');
        }

        return $this->render('post/_comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('post/_form_comment.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }
}
