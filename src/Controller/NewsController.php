<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsForm;
use App\Entity\Comment;
use App\Form\CommentForm;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

use Knp\Component\Pager\PaginatorInterface;

use App\Repository\CommentRepository;

#[Route('/admin/news')]
final class NewsController extends AbstractController
{

    private int $pageNumber = 5;

    #[Route('/', name: 'admin_news_index')]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $entityManager
            ->getRepository(News::class)
            ->createQueryBuilder('n')
            ->orderBy('n.insertedAt', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // page number from the URL, defaults to 1
            $this->pageNumber
        );

        return $this->render('news/index.html.twig', [
            'news' => $pagination,
        ]);
    }

    #[Route('/new', name: 'admin_news_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $news = new News();
        $form = $this->createForm(NewsForm::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('news_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Image upload failed.');
                }

                $news->setImage($newFilename);
            }
            $entityManager->persist($news);

            $entityManager->flush();

            return $this->redirectToRoute('admin_news_index');
        }




        return $this->render('news/new.html.twig', [
            'news' => $news,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_news_show', methods: ['GET'])]
    public function show(Request $request, News $news, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $comment->setNews($news);
        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('admin_news_show', ['id' => $news->getId()]);
        }

        return $this->render('news/show.html.twig', [
            'news' => $news,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_news_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, News $news, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(NewsForm::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('news_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Image upload failed.');
                }

                $news->setImage($newFilename);
            }
            // If no new file, the old image remains.

            $entityManager->flush();

            return $this->redirectToRoute('admin_news_index');
        }


        // Get all comments for this news
        $comments = $news->getComments();

        return $this->render('news/edit.html.twig', [
            'news' => $news,
            'form' => $form,
            'comments' => $comments,
        ]);
    }


    #[Route('/{id}', name: 'admin_news_delete', methods: ['POST'])]
    public function delete(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $news->getId(), $request->request->get('_token'))) {
            // Only remove the News entity—comments will be removed automatically!
            $entityManager->remove($news);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_news_index');
    }



}
