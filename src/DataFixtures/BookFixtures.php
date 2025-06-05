<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Enum\BookStatus;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class BookFixtures.
 */
class BookFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(20, 'books', function (int $i) {
            $book = new Book();
            $book->setTitle($this->faker->sentence(3));
            $book->setFilename(sprintf('%d.jpg', $i % 10));
            $book->setDescription($this->faker->text(150));
            $book->setStatus(BookStatus::PUBLIC);
            $book->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $book->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            /** @var User $author */
            $author = $this->getRandomReference('users');
            $book->setAuthor($author);

            /** @var array<Tag> $tags */
            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $book->addTag($tag);
            }

            // 🚨 wymuszenie regeneracji sluga (jeśli Gedmo z jakiegoś powodu nie odpala automatycznie)
            $book->setTitle($book->getTitle());

            return $book;
        });

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
