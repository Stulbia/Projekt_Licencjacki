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

        // Ścieżka do zapisu okładek (zbieżna z Twoim kodem public/uploads/covers)
        $targetDir = __DIR__ . '/../../public/uploads/covers';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $output->writeln('Pobieranie pełnej listy książek...');
        $response = $client->request('GET', 'https://wolnelektury.pl/api/books/');
        $allBooks = $response->toArray();

        // Losowanie 200 pozycji
        shuffle($allBooks);
        $subset = array_slice($allBooks, 0, 200);

        $output->writeln('Inicjowanie asynchronicznego pobierania detali dla 200 losowych książek...');
        $responses = [];
        foreach ($subset as $bookInfo) {
            $responses[] = $client->request('GET', $bookInfo['href']);
        }

        $finalData = [];
        $output->writeln('Przetwarzanie danych oraz pobieranie okładek...');

        foreach ($responses as $detailResponse) {
            try {
                $details = $detailResponse->toArray();
                $title = $details['title'] ?? 'Brak tytułu';
                $rawDescription = $details['description'] ?? ($details['fragment_data']['html'] ?? 'Brak opisu.');

                $coverFilename = null;

                // Jeśli książka ma okładkę, pobieramy ją
                if (!empty($details['cover'])) {
                    $coverUrl = $details['cover'];
                    $coverFilename = basename($coverUrl);
                    $filePath = $targetDir . '/' . $coverFilename;

                    // Pobieramy tylko, jeśli plik jeszcze nie istnieje na dysku
                    if (!file_exists($filePath)) {
                        $output->writeln("Pobieranie okładki dla: <info>{$title}</info>");

                        // Pobieramy obrazek przez HttpClient
                        $imageResponse = $client->request('GET', $coverUrl);
                        if ($imageResponse->getStatusCode() === 200) {
                            file_put_contents($filePath, $imageResponse->getContent());
                        }
                    }
                }

                $finalData[] = [
                    'title'       => $title,
                    'author'      => $details['authors'][0]['name'] ?? 'Anonim',
                    'description' => trim(strip_tags($rawDescription)),
                    'cover'       => $coverFilename, // Zapisujemy tylko nazwę pliku (np. "pan-tadeusz.jpg") do bazy/jsona zamiast całego URL
                    'tags'        => array_filter([
                        $details['genre'] ?? null,
                        $details['kind'] ?? null,
                        $details['epoch'] ?? null,
                    ]),
                ];

            } catch (\Exception $e) {
                // Jeśli pojedyncza książka rzuci błąd, pomiń ją i leć dalej
                continue;
            }
        }

        // Zapis do JSONa
        file_put_contents(
            __DIR__ . '/../DataFixtures/data/books_data.json',
            json_encode($finalData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $output->writeln(sprintf('Gotowe! Dane zapisane, a okładki lecą do public/uploads/covers. Łącznie: %d książek.', count($finalData)));
        return Command::SUCCESS;
    }
}