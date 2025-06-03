<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\News;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-database',
    description: 'Seeds the database with demo categories, news, and comments',
)]
class SeedDatabaseCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Categories
        $category1 = new Category();
        $category1->setTitle('Politics');
        $category2 = new Category();
        $category2->setTitle('Tech');
        $category3 = new Category();
        $category3->setTitle('World');
        $category4 = new Category();
        $category4->setTitle('Health');
        $category5 = new Category();
        $category5->setTitle('Sports');
        $this->em->persist($category1);
        $this->em->persist($category2);
        $this->em->persist($category3);
        $this->em->persist($category4);
        $this->em->persist($category5);

        // News
        $news1 = new News();
        $news1->setTitle('Elections 2025 Announced');
        $news1->setShortDescription('Official announcement of the 2025 elections.');
        $news1->setContent('The government has set the date for the next elections...');
        $news1->setInsertedAt(new \DateTimeImmutable('-4 days'));
        $news1->setImage('');
        $news1->addCategory($category1);
        $news1->addCategory($category3);

        $news2 = new News();
        $news2->setTitle('Symfony 7 Released!');
        $news2->setShortDescription('Symfony 7 is out with new features.');
        $news2->setContent('Symfony 7 brings AssetMapper and other improvements...');
        $news2->setInsertedAt(new \DateTimeImmutable('-2 days'));
        $news2->setImage('');
        $news2->addCategory($category2);
        $news2->addCategory($category3);

        $news3 = new News();
        $news3->setTitle('Health Benefits of Walking');
        $news3->setShortDescription('Daily walking boosts your health.');
        $news3->setContent('Doctors recommend walking at least 30 minutes a day...');
        $news3->setInsertedAt(new \DateTimeImmutable('-1 days'));
        $news3->setImage('');
        $news3->addCategory($category4);

        $news4 = new News();
        $news4->setTitle('Georgia Wins Football Match');
        $news4->setShortDescription('Historic win for Georgia in the qualifiers.');
        $news4->setContent('Fans celebrate as Georgia wins against Spain...');
        $news4->setInsertedAt(new \DateTimeImmutable('-1 days'));
        $news4->setImage('');
        $news4->addCategory($category5);
        $news4->addCategory($category3);

        $news5 = new News();
        $news5->setTitle('AI Changing the World');
        $news5->setShortDescription('Artificial Intelligence trends in 2025.');
        $news5->setContent('AI is transforming industries and daily life...');
        $news5->setInsertedAt(new \DateTimeImmutable('-3 days'));
        $news5->setImage('');
        $news5->addCategory($category2);
        $news5->addCategory($category3);

        $this->em->persist($news1);
        $this->em->persist($news2);
        $this->em->persist($news3);
        $this->em->persist($news4);
        $this->em->persist($news5);

        // Comments
        $comment1 = new Comment();
        $comment1->setAuthor('John Doe');
        $comment1->setContent('Looking forward to the elections!');
        $comment1->setCreatedAt(new \DateTimeImmutable('-3 days'));
        $comment1->setNews($news1);

        $comment2 = new Comment();
        $comment2->setAuthor('Jane Smith');
        $comment2->setContent('Great improvements in Symfony 7.');
        $comment2->setCreatedAt(new \DateTimeImmutable('-1 days'));
        $comment2->setNews($news2);

        $comment3 = new Comment();
        $comment3->setAuthor('Alice');
        $comment3->setContent('Thanks for the health tips!');
        $comment3->setCreatedAt(new \DateTimeImmutable('-10 hours'));
        $comment3->setNews($news3);

        $comment4 = new Comment();
        $comment4->setAuthor('Nika');
        $comment4->setContent('What a match! Go Georgia!');
        $comment4->setCreatedAt(new \DateTimeImmutable('-5 hours'));
        $comment4->setNews($news4);

        $comment5 = new Comment();
        $comment5->setAuthor('Zurab');
        $comment5->setContent('Excited to see where AI takes us.');
        $comment5->setCreatedAt(new \DateTimeImmutable('-8 hours'));
        $comment5->setNews($news5);

        $comment6 = new Comment();
        $comment6->setAuthor('John Doe');
        $comment6->setContent('This was a close game.');
        $comment6->setCreatedAt(new \DateTimeImmutable('-4 hours'));
        $comment6->setNews($news4);

        $comment7 = new Comment();
        $comment7->setAuthor('Mariam');
        $comment7->setContent('Interesting article!');
        $comment7->setCreatedAt(new \DateTimeImmutable('-2 hours'));
        $comment7->setNews($news5);

        $this->em->persist($comment1);
        $this->em->persist($comment2);
        $this->em->persist($comment3);
        $this->em->persist($comment4);
        $this->em->persist($comment5);
        $this->em->persist($comment6);
        $this->em->persist($comment7);

        $this->em->flush();

        $io->success('Seeded database with demo categories, news, and comments!');
        return Command::SUCCESS;
    }

}
