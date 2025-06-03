<?php

namespace App\Tests\Integration;

use App\Entity\News;
use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CommentIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCreateAndDeleteCommentWithNews()
    {
        // Step 1: Create News
        $news = new News();
        $news->setTitle('Test News ' . uniqid());
        $news->setContent('This is test content.');
        $news->setShortDescription('This is a short desc');
        $news->setInsertedAt(new \DateTimeImmutable());
        $this->em->persist($news);

        // Step 2: Create Comment attached to News
        $comment = new Comment();
        $comment->setAuthor('Test Author ' . uniqid());
        $comment->setContent('Test comment');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setNews($news);

        if (method_exists($news, 'addComment')) {
            $news->addComment($comment);
        }

        $this->em->persist($comment);
        $this->em->flush();
        $this->em->clear();

        // Step 3: Assert both exist in the database
        $newsRepo = $this->em->getRepository(News::class);
        $commentRepo = $this->em->getRepository(Comment::class);

        // Use unique title/author to always get your test record, not leftover ones
        $savedNews = $newsRepo->findOneBy(['title' => $news->getTitle()]);
        $savedComment = $commentRepo->findOneBy(['author' => $comment->getAuthor()]);

        $this->assertNotNull($savedNews, 'News should exist in DB');
        $this->assertNotNull($savedComment, 'Comment should exist in DB');
        // This will always work now, since only your just-created comment has this author/title
        $this->assertEquals($savedNews->getId(), $savedComment->getNews()->getId(), 'Comment is attached to News');

        // Step 4: Remove News and ensure Comment is deleted via cascade
        $this->em->remove($savedNews);
        $this->em->flush();
        $this->em->clear();

        $deletedNews = $newsRepo->findOneBy(['title' => $news->getTitle()]);
        $deletedComment = $commentRepo->findOneBy(['author' => $comment->getAuthor()]);

        $this->assertNull($deletedNews, 'News should be deleted');
        $this->assertNull($deletedComment, 'Comment should be cascade deleted');
    }


    public function testEmptyCommentContent()
    {
        $news = new News();
        $news->setTitle('Test News Empty');
        $news->setContent('test');
        $news->setShortDescription('short');
        $news->setInsertedAt(new \DateTimeImmutable());
        $this->em->persist($news);

        $comment = new Comment();
        $comment->setAuthor('Author');
        $comment->setContent('');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setNews($news);

        $this->em->persist($comment);
        $this->em->flush();

        $found = $this->em->getRepository(Comment::class)->findOneBy(['author' => 'Author']);
        $this->assertNotNull($found, 'Empty comment content should be saved (unless you have validation)');
    }

    public function testLargeCommentContent()
    {
        $news = new News();
        $news->setTitle('Test News Large');
        $news->setContent('test');
        $news->setShortDescription('short');
        $news->setInsertedAt(new \DateTimeImmutable());
        $this->em->persist($news);

        $largeContent = str_repeat('A', 10000);
        $comment = new Comment();
        $comment->setAuthor('LargeAuthor');
        $comment->setContent($largeContent);
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setNews($news);

        $this->em->persist($comment);
        $this->em->flush();

        $found = $this->em->getRepository(Comment::class)->findOneBy(['author' => 'LargeAuthor']);
        $this->assertEquals(10000, strlen($found->getContent()));
    }

    public function testCommentWithNoNews()
    {
        $comment = new Comment();
        $comment->setAuthor('NoNewsAuthor');
        $comment->setContent('No news');
        $comment->setCreatedAt(new \DateTimeImmutable());
        // Don't setNews!

        $this->em->persist($comment);

        $this->expectException(\Doctrine\DBAL\Exception\NotNullConstraintViolationException::class);
        $this->em->flush();
    }

    public function testMultipleCommentsPerNews()
    {
        $news = new News();
        $news->setTitle('Test Multi');
        $news->setContent('test');
        $news->setShortDescription('short');
        $news->setInsertedAt(new \DateTimeImmutable());
        $this->em->persist($news);

        for ($i = 1; $i <= 5; $i++) {
            $comment = new Comment();
            $comment->setAuthor("MultiAuthor$i");
            $comment->setContent("Content $i");
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setNews($news);
            $this->em->persist($comment);
        }

        $this->em->flush();

        $comments = $this->em->getRepository(Comment::class)->findBy(['news' => $news]);
        $this->assertCount(5, $comments, "There should be 5 comments for the news");
    }


    public function testCommentWithSpecialCharacters()
    {
        $news = new News();
        $news->setTitle('Test Special');
        $news->setContent('test');
        $news->setShortDescription('short');
        $news->setInsertedAt(new \DateTimeImmutable());
        $this->em->persist($news);

        $comment = new Comment();
        $comment->setAuthor('Unicode👾');
        $comment->setContent('Content with emoji 😊 and non-latin ენა.');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setNews($news);

        $this->em->persist($comment);
        $this->em->flush();

        $found = $this->em->getRepository(Comment::class)->findOneBy(['author' => 'Unicode👾']);
        $this->assertNotNull($found);
        $this->assertStringContainsString('😊', $found->getContent());
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->em) {
            $this->em->close();
            unset($this->em);
        }
    }
}
