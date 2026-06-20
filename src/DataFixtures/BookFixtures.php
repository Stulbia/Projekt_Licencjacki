<?php

//
// namespace App\DataFixtures;
//
// use App\Entity\Book;
// use App\Entity\Enum\BookStatus;
// use App\Entity\Tag;
// use App\Entity\Author;
// use Doctrine\Common\DataFixtures\DependentFixtureInterface;
//
// /**
// * Class BookFixtures.
// */
// class BookFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
// {
//    public function loadData(): void
//    {
//        if (null === $this->manager) {
//            return;
//        }
//
//        $dataPath = __DIR__ . '/data/books_data.json';
//        $booksData = json_decode(file_get_contents($dataPath), true);
//
//        foreach ($booksData as $i => $data) {
//            $book = new Book();
//            $book->setTitle($data['title']);
//            $book->setDescription($data['description']);
//            $book->setStatus(BookStatus::PUBLIC);
//            $book->setCoverFilename($data['cover']);
//            $book->setCreatedAt(new \DateTimeImmutable(sprintf('-%d days', rand(30, 100))));
//            $book->setUpdatedAt(new \DateTimeImmutable(sprintf('-%d days', rand(1, 29))));
//
//            // Author handling (find existing or create new)
//            $authorName = $data['author'];
//            $author = $this->getRandomReference('authors'); // fallback
//
//            foreach ($this->getReferencesByGroup('authors') as $ref) {
//                /** @var Author $a */
//                $a = $this->getReference($ref);
//                if ($a->__toString() === $authorName) {
//                    $author = $a;
//                    break;
//                }
//            }
//
//            $book->setAuthor($author);
//
//            // Losowe tagi
//            /** @var array<Tag> $tags */
//            $tags = $this->getRandomReferences('tags', rand(1, 3));
//            foreach ($tags as $tag) {
//                $book->addTag($tag);
//            }
//            $book->setTitle($book->getTitle());
//            $this->addReference("books_$i", $book);
//            $this->manager->persist($book);
//        }
//
//        $this->manager->flush();
//    }
//
//    public function getDependencies(): array
//    {
//        return [
//            AuthorFixtures::class,
//            TagFixtures::class,
//        ];
//    }
// }

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Enum\BookStatus;
use App\Entity\Tag;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class BookFixtures.
 */
class BookFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Cache dla autorów i tagów, aby nie tworzyć duplikatów.
     */
    private array $authorsCache = [];
    private array $tagsCache = [];

    /**
     * Load data.
     */
    public function loadData(): void
    {
        if (null === $this->manager) {
            return;
        }

        $dataPath = __DIR__.'/data/books_data.json';

        if (!file_exists($dataPath)) {
            return; // Możesz rzucić wyjątek, jeśli plik jest wymagany
        }

        $booksData = json_decode(file_get_contents($dataPath), true);

        foreach ($booksData as $i => $data) {
            $book = new Book();
            $book->setTitle($data['title']);

            // Ograniczamy opis do 1000 znaków na wypadek bardzo długich fragmentów z API
            $description = strip_tags($data['description']);
            $book->setDescription(mb_substr($description, 0, 1000));

            $book->setStatus(BookStatus::PUBLIC);

            // Zapisujemy tylko nazwę pliku (np. 'pan-tadeusz.jpg') zamiast całego URL
            if (!empty($data['cover'])) {
                $book->setCoverFilename(basename($data['cover']));
            }

            $book->setCreatedAt(new DateTimeImmutable(sprintf('-%d days', rand(30, 100))));
            $book->setUpdatedAt(new DateTimeImmutable(sprintf('-%d days', rand(1, 29))));

            // --- Obsługa Autora ---
            $authorName = $data['author'] ?? 'Anonim';
            $book->setAuthor($this->getOrCreateAuthor($authorName));

            // --- Obsługa Tagów z API (gatunki/epoki) ---
            if (isset($data['tags']) && is_array($data['tags'])) {
                foreach ($data['tags'] as $tagName) {
                    if (!empty($tagName)) {
                        $book->addTag($this->getOrCreateTag($tagName));
                    }
                }
            }

            // --- Dodatkowe losowe tagi (z Twoich TagFixtures) ---
            /** @var array<Tag> $randomTags */
            $randomTags = $this->getRandomReferences('tags', rand(1, 2));
            foreach ($randomTags as $tag) {
                $book->addTag($tag);
            }

            $this->addReference("books_$i", $book);
            $this->manager->persist($book);
        }

        $this->manager->flush();
    }

    /**
     * Pobiera autora z cache lub tworzy nowego.
     */
    private function getOrCreateAuthor(string $fullName): Author
    {
        if (isset($this->authorsCache[$fullName])) {
            return $this->authorsCache[$fullName];
        }

        $author = new Author();
        $parts = explode(' ', $fullName);

        if (count($parts) > 1) {
            $author->setName(array_pop($parts)); // Ostatni człon to nazwisko
            $author->setFirstName(implode(' ', $parts)); // Reszta to imiona
        } else {
            $author->setName($fullName);
            $author->setFirstName('');
        }

        $author->setDescription('Autor pobrany z API Wolne Lektury.');

        $this->manager->persist($author);
        $this->authorsCache[$fullName] = $author;

        return $author;
    }

    /**
     * Pobiera tag z cache lub tworzy nowy.
     */
    private function getOrCreateTag(string $name): Tag
    {
        // Normalizacja nazwy (np. "Powieść" zamiast "powieść")
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

        if (isset($this->tagsCache[$name])) {
            return $this->tagsCache[$name];
        }

        $tag = new Tag();
        $tag->setTitle($name);

        $this->manager->persist($tag);
        $this->tagsCache[$name] = $tag;

        return $tag;
    }

    /**
     * Zależności.
     */
    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
        ];
    }
}
