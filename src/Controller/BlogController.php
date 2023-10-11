<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Poste;
use App\Form\BookingType;
use App\Form\CommentType;
use App\Form\LikeType;
use App\Repository\CommentRepository;
use App\Repository\LikeRepository;
use App\Repository\PosteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(PosteRepository $posteRepository): Response
    {
        return $this->render('blog/index.html.twig', [
            'postes' => $posteRepository->findAll(),
        ]);
    }

    #[Route('/blog/{id}/like/{commentId}', name: 'app_blog_like_comment', methods: ['GET'])]
    public function likeComment(CommentRepository $commentRepository, EntityManagerInterface $entityManager, Security $security, int $commentId, Poste $poste, LikeRepository $likeRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $like = new Like();
        $user = $security->getUser();
        $currentComment = $commentRepository->find($commentId);
        if (!($likeRepository->findBy(
            ['user' => $user,
                'comment' => $currentComment]
        ))) {
            $like->setUser($user);
            $like->setComment($currentComment);
            $like->setLiker(false);

            $entityManager->persist($like);
            $entityManager->flush();
        } elseif ($likeRepository->findBy(
            ['user' => $user,
                'comment' => $currentComment,
                'liker' => true]
        )) {
            $currentLike = $likeRepository->findOneBy(
                ['user' => $user,
                    'comment' => $currentComment,
                    'liker' => true]
            );

            $entityManager->remove($currentLike);
            $entityManager->flush();

            $like->setUser($user);
            $like->setComment($currentComment);
            $like->setLiker(false);

            $entityManager->persist($like);
            $entityManager->flush();
        } else {
            $currentLike = $likeRepository->findOneBy(
                ['user' => $user,
                    'comment' => $currentComment]
            );

            $entityManager->remove($currentLike);
            $entityManager->flush();
        }
        return $this->redirectToRoute("app_postes_detail", ["id" => $poste->getId(), "slug" => $poste->getSlug()]);
    }

    #[Route('/blog/{id}/dislike/{commentId}', name: 'app_blog_dislike_comment', methods: ['GET'])]
    public function dislikeComment(CommentRepository $commentRepository, EntityManagerInterface $entityManager, Security $security, int $commentId, Poste $poste, LikeRepository $likeRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $like = new Like();
        $user = $security->getUser();
        $currentComment = $commentRepository->find($commentId);
        if (!($likeRepository->findBy(
            ['user' => $user,
                'comment' => $currentComment]
        ))) {
            $like->setUser($user);
            $like->setComment($currentComment);
            $like->setLiker(true);

            $entityManager->persist($like);
            $entityManager->flush();
        } elseif ($likeRepository->findBy(
            ['user' => $user,
                'comment' => $currentComment,
                'liker' => false]
        )) {
            $currentLike = $likeRepository->findOneBy(
                ['user' => $user,
                    'comment' => $currentComment,
                    'liker' => false]
            );

            $entityManager->remove($currentLike);
            $entityManager->flush();

            $like->setUser($user);
            $like->setComment($currentComment);
            $like->setLiker(true);

            $entityManager->persist($like);
            $entityManager->flush();
        } else {
            $currentLike = $likeRepository->findOneBy(
                ['user' => $user,
                    'comment' => $currentComment]
            );

            $entityManager->remove($currentLike);
            $entityManager->flush();
        }


        return $this->redirectToRoute("app_postes_detail", ["id" => $poste->getId(), "slug" => $poste->getSlug()]);
    }

    #[Route('/blog/{id}-{slug}', name: 'app_postes_detail', methods: ['GET', 'POST'])]
    public function show(Poste $poste, $id, $slug, Request $request, EntityManagerInterface $entityManager, LikeRepository $likeRepository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser())
                ->setPost($poste);
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_postes_detail',[
                'id' => $id,
                'slug' => $slug
            ]);
        }
        //$likes = $likeRepository->findBy(['comment' => $comment]);
// Compter le nombre de likes et dislikes pour chaque commentaire
        $likesCounts = [];
        foreach ($poste->getComments() as $comments) {
            $likes = $likeRepository->findBy(['comment' => $comments]);
            $likeCount = 0;
            $dislikeCount = 0;
            foreach ($likes as $like) {
                if ($like->isLiker()) {
                    $dislikeCount++;
                } else {
                    $likeCount++;
                }
            }
            $likesCounts[$comments->getId()] = ['likes' => $likeCount, 'dislikes' => $dislikeCount];
        }

        return $this->render('blog/poste_detail.html.twig', [
            'poste' => $poste,
            'form' =>$form->createView(),
            'comment' => $comment,
            'id' => $id,
            'slug' => $slug,
            'likesCounts' => $likesCounts
        ]);
    }
}
