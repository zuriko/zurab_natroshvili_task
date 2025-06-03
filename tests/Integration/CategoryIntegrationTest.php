<?php

namespace App\Tests\Integration;

use App\Entity\Category;
use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        if ($this->em) {
            $this->em->close();
            unset($this->em);
        }
        parent::tearDown();
    }

    public function testCategoryCreation(): void
    {
        $category = new Category();
        $category->setTitle('New Category');
        $this->em->persist($category);
        $this->em->flush();

        $found = $this->em->getRepository(Category::class)->findOneBy(['title' => 'New Category']);
        $this->assertNotNull($found, "Category should be saved and found");
    }

    public function testCategoryWithNullTitle(): void
    {
        $category = new Category();
        // Don't set title, so it stays null!
        $this->em->persist($category);

        $this->expectException(\Doctrine\DBAL\Exception\NotNullConstraintViolationException::class);
        $this->em->flush();
    }

    public function testAssignCategoriesToNews(): void
    {
        $cat1 = new Category();
        $cat1->setTitle('Cat1');
        $cat2 = new Category();
        $cat2->setTitle('Cat2');
        $this->em->persist($cat1);
        $this->em->persist($cat2);

        $news = new News();
        $news->setTitle('Test News');
        $news->setShortDescription('desc');
        $news->setContent('content');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($cat1);
        $news->addCategory($cat2);
        $this->em->persist($news);
        $this->em->flush();

        $found = $this->em->getRepository(News::class)->find($news->getId());
        $this->assertCount(2, $found->getCategories(), "News should have 2 categories");
    }

    public function testRemoveCategoryFromNews(): void
    {
        $cat = new Category();
        $cat->setTitle('To Remove');
        $this->em->persist($cat);

        $news = new News();
        $news->setTitle('Test News2');
        $news->setShortDescription('desc');
        $news->setContent('content');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($cat);
        $this->em->persist($news);
        $this->em->flush();

        // Remove the category from News
        $news->removeCategory($cat);
        $this->em->flush();

        $updated = $this->em->getRepository(News::class)->find($news->getId());
        $this->assertCount(0, $updated->getCategories(), "Category should be removed from News");
    }

    public function testDeleteCategoryNotDeletesNews(): void
    {
        $cat = new Category();
        $cat->setTitle('CatToDelete');
        $this->em->persist($cat);

        $news = new News();
        $news->setTitle('Cat News');
        $news->setShortDescription('desc');
        $news->setContent('content');
        $news->setInsertedAt(new \DateTimeImmutable());
        $news->addCategory($cat);
        $this->em->persist($news);
        $this->em->flush();

        $this->em->remove($cat);
        $this->em->flush();

        $foundNews = $this->em->getRepository(News::class)->find($news->getId());
        $this->assertNotNull($foundNews, "News should NOT be deleted when category is removed");
        $this->assertCount(0, $foundNews->getCategories(), "Categories collection should be empty after deleting category");
    }
}
