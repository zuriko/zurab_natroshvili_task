<?php

namespace App\Command;

use App\Repository\NewsRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsCommand(
    name: 'app:weekly-news-report',
    description: 'Send weekly email with top 10 news',
)]
class WeeklyNewsReportCommand extends Command
{
    private NewsRepository $newsRepository;
    private MailerInterface $mailer;
    private Environment $twig;
    private string $reportRecipient;

    public function __construct(
        NewsRepository $newsRepository,
        MailerInterface $mailer,
        Environment $twig,
        string $reportRecipient
    ) {
        parent::__construct();
        $this->newsRepository = $newsRepository;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->reportRecipient = $reportRecipient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newsList = $this->newsRepository->findBy([], ['insertedAt' => 'DESC'], 10);

        $htmlContent = $this->twig->render('emails/weekly_news.html.twig', [
            'newsList' => $newsList,
        ]);

        $email = (new Email())
            ->from('noreply@example.com')
            ->to($this->reportRecipient)
            ->subject('Weekly News Summary')
            ->html($htmlContent);

        $this->mailer->send($email);

        $output->writeln('✅ Weekly summary sent.' . $htmlContent);
        return Command::SUCCESS;
    }
}
