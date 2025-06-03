<?php

namespace App\Tests\Integration;

use App\Entity\News;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Comment;

class NewsIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $em;


    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM comment');
        $conn->executeStatement('DELETE FROM news');
        $conn->executeStatement('DELETE FROM category');
    }



    protected function tearDown(): void
    {
        parent::tearDown();
    }



    public function testCreateNewsWithCategoryPersists(): void
    {
        $category = new Category();
        $category->setTitle('Integration Category');
        $this->em->persist($category);

        $news = new News();
        $news->setTitle('Integration Test News');
        $news->setShortDescription('Integration Short Desc');
        $news->setContent('Integration Content');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($category);

        $this->em->persist($news);
        $this->em->flush();

        // Fetch from DB
        $repo = $this->em->getRepository(News::class);
        $foundNews = $repo->findOneBy(['title' => 'Integration Test News']);

        $this->assertNotNull($foundNews);
        $this->assertEquals('Integration Short Desc', $foundNews->getShortDescription());
        $this->assertGreaterThanOrEqual(1, count($foundNews->getCategories()));
    }

    public function testNewsCanBeUpdated(): void
    {
        // Create category & news
        $category = new Category();
        $category->setTitle('Update Test');
        $this->em->persist($category);

        $news = new News();
        $news->setTitle('To Update');
        $news->setShortDescription('Before update');
        $news->setContent('Before');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($category);
        $this->em->persist($news);
        $this->em->flush();

        // Update
        $news->setTitle('Updated Title');
        $news->setShortDescription('Updated desc');
        $news->setContent('After');
        $this->em->flush();

        $found = $this->em->getRepository(News::class)->find($news->getId());
        $this->assertEquals('Updated Title', $found->getTitle());
        $this->assertEquals('Updated desc', $found->getShortDescription());
        $this->assertEquals('After', $found->getContent());
    }

    public function testNewsCanBeDeleted(): void
    {
        $category = new Category();
        $category->setTitle('Delete Cat');
        $this->em->persist($category);

        $news = new News();
        $news->setTitle('Delete Me');
        $news->setShortDescription('Desc');
        $news->setContent('Content');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($category);
        $this->em->persist($news);
        $this->em->flush();

        $id = $news->getId();
        $this->em->remove($news);
        $this->em->flush();

        $found = $this->em->getRepository(News::class)->find($id);
        $this->assertNull($found, "News should be deleted from DB");
    }


    public function testNewsMustHaveRequiredFields(): void
    {
        $this->expectException(\Doctrine\DBAL\Exception\NotNullConstraintViolationException::class);

        $news = new News();
        // $news->setTitle('Title'); // Intentionally missing
        $news->setShortDescription('No Title');
        $news->setContent('No Title');
        $news->setInsertedAt(new \DateTimeImmutable());

        $this->em->persist($news);
        $this->em->flush(); // This will trigger the exception
    }


    public function testNewsCanHaveMultipleCategories(): void
    {
        $cat1 = new Category();
        $cat1->setTitle('Cat 1');
        $cat2 = new Category();
        $cat2->setTitle('Cat 2');
        $this->em->persist($cat1);
        $this->em->persist($cat2);

        $news = new News();
        $news->setTitle('Multi Cat');
        $news->setShortDescription('Multi');
        $news->setContent('Multi');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($cat1);
        $news->addCategory($cat2);

        $this->em->persist($news);
        $this->em->flush();

        $found = $this->em->getRepository(News::class)->find($news->getId());
        $this->assertCount(2, $found->getCategories());
    }



    public function testNewsCanHaveCommentsAttached(): void
    {
        $category = new Category();
        $category->setTitle('Comment Cat');
        $this->em->persist($category);

        $news = new News();
        $news->setTitle('With Comment');
        $news->setShortDescription('Short');
        $news->setContent('Content');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($category);
        $this->em->persist($news);

        $comment = new \App\Entity\Comment();
        $comment->setAuthor('Tester');
        $comment->setContent('Integration test comment');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setNews($news);

        // Make sure news knows about the comment too (if not done automatically)
        if (method_exists($news, 'addComment')) {
            $news->addComment($comment);
        }
        $this->em->persist($comment);

        $this->em->flush();

        // Refetch news from the DB
        $foundNews = $this->em->getRepository(News::class)->find($news->getId());

        $this->assertGreaterThanOrEqual(1, count($foundNews->getComments()));
    }



    public function testFindLatestNewsReturnsCorrectOrder(): void
    {
        $now = new \DateTimeImmutable();

        for ($i = 1; $i <= 3; $i++) {
            $news = new News();
            $news->setTitle('News ' . $i);
            $news->setShortDescription('S' . $i);
            $news->setContent('C' . $i);
            $news->setInsertedAt($now->modify("-$i day"));
            $this->em->persist($news);
        }
        $this->em->flush();

        $repo = $this->em->getRepository(News::class);
        $newsList = $repo->findBy([], ['insertedAt' => 'DESC']);

        $this->assertGreaterThanOrEqual(2, count($newsList));
        $this->assertTrue($newsList[0]->getInsertedAt() > $newsList[1]->getInsertedAt());
    }


    public function testNewsWithLongTitleAndContent(): void
    {
        $category = new Category();
        $category->setTitle('Long Cat');
        $this->em->persist($category);

        $news = new News();
        $news->setTitle(str_repeat('A', 255));
        $news->setShortDescription(str_repeat('S', 255));
        $news->setContent(str_repeat('C', 5000));
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($category);

        $this->em->persist($news);
        $this->em->flush();

        $found = $this->em->getRepository(News::class)->findOneBy(['title' => str_repeat('A', 255)]);
        $this->assertNotNull($found);
    }

    public function testBulkInsertAndFetchNews(): void
    {
        $category = new Category();
        $category->setTitle('Bulk Cat');
        $this->em->persist($category);

        // Insert 100 news items
        for ($i = 1; $i <= 100; $i++) {
            $news = new News();
            $news->setTitle("Bulk News $i");
            $news->setShortDescription("Short $i");
            $news->setContent("Content $i");
            $news->setInsertedAt(new \DateTimeImmutable());
            $news->addCategory($category);
            $this->em->persist($news);
        }
        $this->em->flush();
        $this->em->clear();

        $allNews = $this->em->getRepository(News::class)->findBy([], null, 100);
        $this->assertCount(100, $allNews);
    }
    public function testMassInsertCommentsOnNews(): void
    {
        $news = new News();
        $news->setTitle('Viral News');
        $news->setShortDescription('Viral');
        $news->setContent('Goes viral');
        $news->setInsertedAt(new \DateTimeImmutable());
        $this->em->persist($news);

        for ($i = 1; $i <= 50; $i++) {
            $comment = new Comment();
            $comment->setAuthor("User $i");
            $comment->setContent("Comment $i");
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setNews($news);
            $this->em->persist($comment);
            $news->addComment($comment);
        }

        $this->em->flush();
        $this->em->clear();

        $foundNews = $this->em->getRepository(News::class)->findOneBy(['title' => 'Viral News']);
        $this->assertGreaterThanOrEqual(50, count($foundNews->getComments()));
    }


    public function testDeletingNewsRemovesComments(): void
    {
        $news = new News();
        $news->setTitle('To Be Deleted');
        $news->setShortDescription('Delete');
        $news->setContent('Delete test');
        $news->setInsertedAt(new \DateTimeImmutable());
        $this->em->persist($news);

        $comment = new Comment();
        $comment->setAuthor("EdgeUser");
        $comment->setContent("Should vanish");
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setNews($news);
        $this->em->persist($comment);

        $this->em->flush();
        $this->em->clear(); // <--- Force clear EM

        // --- Re-fetch News from DB ---
        $repo = $this->em->getRepository(News::class);
        $news = $repo->findOneBy(['title' => 'To Be Deleted']);

        $this->em->remove($news);
        $this->em->flush();
        $this->em->clear();

        $conn = $this->em->getConnection();
        $rows = $conn->fetchAllAssociative('SELECT * FROM comment');

        $count = $conn->fetchOne('SELECT COUNT(*) FROM comment WHERE author = "EdgeUser"');
        $this->assertEquals(0, $count, "There should be no comments left in the DB after deleting news.");
    }


}
