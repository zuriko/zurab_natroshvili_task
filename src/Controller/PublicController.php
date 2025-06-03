<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\News;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NewsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Comment;
use App\Form\CommentForm;
use Doctrine\ORM\EntityManagerInterface;

class PublicController extends AbstractController
{
    private NewsRepository $newsRepository;

    private int $pageNumber = 10;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    #[Route('/', name: 'public_homepage')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // Fetch all categories
        $categories = $categoryRepository->findAll();

        return $this->render('public/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id}', name: 'public_category')]
    public function category(Category $category, PaginatorInterface $paginator, Request $request): Response
    {
        $newsQuery = $this->newsRepository->createQueryBuilder('n')
            ->join('n.categories', 'c')
            ->where('c = :category')
            ->setParameter('category', $category)
            ->orderBy('n.insertedAt', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $newsQuery,
            $request->query->getInt('page', 1),
            $this->pageNumber
        );

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'pagination' => $pagination,
        ]);
    }


    #[Route('/news/{id}', name: 'public_news_detail')]
    public function newsDetail(
        News $news,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // List all comments for this news
        $comments = $news->getComments();

        // Add comment form
        $comment = new Comment();
        $comment->setNews($news);
        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $em->persist($comment);
            $em->flush();

            // Redirect to avoid double submit
            return $this->redirectToRoute('public_news_detail', ['id' => $news->getId()]);
        }

        return $this->render('public/news_detail.html.twig', [
            'news' => $news,
            'comments' => $comments,
            'commentForm' => $form->createView(),
        ]);
    }
}