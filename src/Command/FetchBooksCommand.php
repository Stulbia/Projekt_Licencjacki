<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(name: 'app:fetch-books')]
class FetchBooksCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = HttpClient::create();
        $output->writeln('Pobieranie listy książek...');
        $response = $client->request('GET', 'https://wolnelektury.pl/api/books/');
        $allBooks = $response->toArray();
        $subset = array_slice($allBooks, 0, 200);

        $finalData = [];

        foreach ($subset as $bookInfo) {
            $output->writeln("Pobieranie detali dla: {$bookInfo['title']}");
            $detailResponse = $client->request('GET', $bookInfo['href']);
            $details = $detailResponse->toArray();

            // Szukamy opisu w 'description'. Jeśli go nie ma, bierzemy 'fragment_data' jako fallback.
            $rawDescription = $details['description'] ?? ($details['fragment_data']['html'] ?? 'Brak opisu.');

            $finalData[] = [
                'title'       => $details['title'],
                'author'      => $details['authors'][0]['name'] ?? 'Anonim',
                'description' => trim(strip_tags($rawDescription)),
                'cover'       => $details['cover'],
                'tags'        => array_filter([
                    $details['genre'] ?? null,
                    $details['kind'] ?? null,
                    $details['epoch'] ?? null,
                ]),
            ];
        }

        file_put_contents(
            __DIR__ . '/../DataFixtures/data/books_data.json',
            json_encode($finalData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $output->writeln('Gotowe! Dane zapisane w DataFixtures/data/books_data.json');
        return Command::SUCCESS;
    }
}
