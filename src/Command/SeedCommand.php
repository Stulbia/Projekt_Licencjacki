<?php
namespace App\Command;

use App\DataFixtures\AppFixtures; // zmień na Twoją klasę fixtures
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed',
    description: 'Ładuje dane początkowe do bazy danych jak fixtures z doctrine nie chca dzialac.'
)]
class SeedCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private AppFixtures $fixtures
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fixtures->load($this->em); // uruchamiamy ręcznie Twoje fixture
        $output->writeln('Fixtures załadowane');
        return Command::SUCCESS;
    }
}
