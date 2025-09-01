<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Enum\BookStatus;
use App\Entity\Tag;
use App\Entity\Author;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class BookFixtures.
 */
class BookFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
//    public function loadData(): void
//    {
//        if (null === $this->manager || null === $this->faker) {
//            return;
//        }
//
//        $this->createMany(20, 'books', function (int $i) {
//            $book = new Book();
//            $book->setTitle($this->faker->sentence(3));
//            $book->setCoverFilename(sprintf('%d.jpg', $i % 10));
//            $book->setDescription($this->faker->text(150));
//            $book->setStatus(BookStatus::PUBLIC);
//            $book->setCreatedAt(
//                \DateTimeImmutable::createFromMutable(
//                    $this->faker->dateTimeBetween('-100 days', '-1 days')
//                )
//            );
//            $book->setUpdatedAt(
//                \DateTimeImmutable::createFromMutable(
//                    $this->faker->dateTimeBetween('-100 days', '-1 days')
//                )
//            );
//
//            /** @var Author $author */
//            $author = $this->getRandomReference('authors');
//            $book->setAuthor($author);
//
//            /** @var array<Tag> $tags */
//            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 5));
//            foreach ($tags as $tag) {
//                $book->addTag($tag);
//            }
//
//            // regeneracja sluga (jeśli Gedmo nie działa na constructorze)
//            $book->setTitle($book->getTitle());
//
//            return $book;
//        });
//
//        $this->manager->flush();
//    }
//
//

    public function loadData(): void
    {
        if (null === $this->manager) {
            return;
        }

        $dataPath = __DIR__ . '/data/books_data.json';
        $booksData = json_decode(file_get_contents($dataPath), true);

        foreach ($booksData as $i => $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setDescription($data['description']);
            $book->setStatus(BookStatus::PUBLIC);
            $book->setCoverFilename($data['cover']);
            $book->setCreatedAt(new \DateTimeImmutable(sprintf('-%d days', rand(30, 100))));
            $book->setUpdatedAt(new \DateTimeImmutable(sprintf('-%d days', rand(1, 29))));

            // Author handling (find existing or create new)
            $authorName = $data['author'];
            $author = $this->getRandomReference('authors'); // fallback

            foreach ($this->getReferencesByGroup('authors') as $ref) {
                /** @var Author $a */
                $a = $this->getReference($ref);
                if ($a->__toString() === $authorName) {
                    $author = $a;
                    break;
                }
            }

            $book->setAuthor($author);

            // Losowe tagi
            /** @var array<Tag> $tags */
            $tags = $this->getRandomReferences('tags', rand(1, 3));
            foreach ($tags as $tag) {
                $book->addTag($tag);
            }
            $book->setTitle($book->getTitle());
            $this->addReference("books_$i", $book);
            $this->manager->persist($book);
        }

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AuthorFixtures::class,
            TagFixtures::class,
        ];
    }
}
