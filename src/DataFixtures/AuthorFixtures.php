<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Enum\BookStatus;
use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AuthorFixtures.
 */
class AuthorFixtures extends AbstractBaseFixtures
{
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(10, 'authors', function (int $i) {
            $author = new Author();
            $author->setFirstName($this->faker->firstName);
            $author->setName($this->faker->lastName);
            $author->setPseudonym($this->faker->optional()->userName);
            $author->setDescription($this->faker->optional()->text(200));
            $author->setPhotoFilename(sprintf('author_%d.jpg', $i));

            return $author;
        });

        $this->manager->flush();
    }
}
