<?php

namespace App\Command;

use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fix-book-slugs',
    description: 'Generuje brakujące slug-i dla książek.',
)]
class FixBookSlugsCommand extends Command
{
    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $books = $this->bookRepository->findAll();
        $count = 0;

        foreach ($books as $book) {
            if (!$book->getSlug()) {
                // trigger slug generation
                $book->setTitle($book->getTitle());
                $this->em->persist($book);
                $count++;
            }
        }

        $this->em->flush();

        $output->writeln("Zaktualizowano $count książek.");
        return Command::SUCCESS;
    }
}
