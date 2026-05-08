<!--#!/usr/bin/env php-->
<?php
//
//$covers = [
//    "pride_and_prejudice.jpg" => "https://www.gutenberg.org/cache/epub/1342/pg1342.cover.medium.jpg",
//    "frankenstein.jpg" => "https://www.gutenberg.org/cache/epub/84/pg84.cover.medium.jpg",
//    "dracula.jpg" => "https://www.gutenberg.org/cache/epub/345/pg345.cover.medium.jpg",
//    "jane_eyre.jpg" => "https://www.gutenberg.org/cache/epub/1260/pg1260.cover.medium.jpg",
//    "moby_dick.jpg" => "https://www.gutenberg.org/cache/epub/2701/pg2701.cover.medium.jpg",
//    "sherlock_holmes.jpg" => "https://www.gutenberg.org/cache/epub/1661/pg1661.cover.medium.jpg",
//    "dorian_gray.jpg" => "https://www.gutenberg.org/cache/epub/174/pg174.cover.medium.jpg",
//    "time_machine.jpg" => "https://www.gutenberg.org/cache/epub/35/pg35.cover.medium.jpg",
//    "20k_leagues.jpg" => "https://www.gutenberg.org/cache/epub/164/pg164.cover.medium.jpg",
//    "call_of_the_wild.jpg" => "https://www.gutenberg.org/cache/epub/215/pg215.cover.medium.jpg",
//    "war_of_the_worlds.jpg" => "https://www.gutenberg.org/cache/epub/36/pg36.cover.medium.jpg",
//    "les_miserables.jpg" => "https://www.gutenberg.org/cache/epub/135/pg135.cover.medium.jpg",
//    "crime_and_punishment.jpg" => "https://www.gutenberg.org/cache/epub/2554/pg2554.cover.medium.jpg",
//    "brothers_karamazov.jpg" => "https://www.gutenberg.org/cache/epub/28054/pg28054.cover.medium.jpg",
//    "wuthering_heights.jpg" => "https://www.gutenberg.org/cache/epub/768/pg768.cover.medium.jpg",
//    "scarlet_letter.jpg" => "https://www.gutenberg.org/cache/epub/33/pg33.cover.medium.jpg",
//    "jungle_book.jpg" => "https://www.gutenberg.org/cache/epub/236/pg236.cover.medium.jpg",
//    "monte_cristo.jpg" => "https://www.gutenberg.org/cache/epub/1184/pg1184.cover.medium.jpg",
//    "treasure_island.jpg" => "https://www.gutenberg.org/cache/epub/120/pg120.cover.medium.jpg",
//    "tale_two_cities.jpg" => "https://www.gutenberg.org/cache/epub/98/pg98.cover.medium.jpg"
//];
//
//$targetDir = __DIR__ . '/../public/uploads/covers';
//@mkdir($targetDir, 0777, true);
//
//foreach ($covers as $filename => $url) {
//    $filePath = $targetDir . '/' . $filename;
//    echo "Downloading $filename...\n";
//    file_put_contents($filePath, file_get_contents($url));
//}
//
//echo "All covers downloaded to $targetDir\n"

$dataPath = __DIR__ . '/
../DataFixtures/data/books_data.json';
$targetDir = __DIR__ . '/../public/uploads/covers';

if (!file_exists($dataPath)) {
    die("Błąd: Plik books_data.json nie istnieje. Uruchom najpierw komendę pobierającą dane.\n");
}

$booksData = json_decode(file_get_contents($dataPath), true);
@mkdir($targetDir, 0777, true);

echo "Rozpoczynam pobieranie okładek...\n";

foreach ($booksData as $book) {
    if (empty($book['cover'])) {
        echo "Pominięto: {$book['title']} (brak URL okładki)\n";
        continue;
    }

    $url = $book['cover'];

    // Generujemy nazwę pliku na podstawie URL lub tytułu
    // basename($url) wyciągnie np. 'pan-tadeusz.jpg'
    $filename = basename($url);
    $filePath = $targetDir . '/' . $filename;

    if (file_exists($filePath)) {
        echo "Plik już istnieje: $filename\n";
        continue;
    }

    echo "Pobieranie okładki dla: {$book['title']}...\n";

    // Używamy bezpieczniejszego pobierania
    $imageContent = @file_get_contents($url);

    if ($imageContent !== false) {
        file_put_contents($filePath, $imageContent);
    } else {
        echo "BŁĄD: Nie udało się pobrać okładki z $url\n";
    }
}

echo "\nGotowe! Okładki znajdują się w: $targetDir\n";