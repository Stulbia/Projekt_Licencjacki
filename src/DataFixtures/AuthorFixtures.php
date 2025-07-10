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
//    public function loadData(): void
//    {
//        if (null === $this->manager || null === $this->faker) {
//            return;
//        }
//
//        $this->createMany(10, 'authors', function (int $i) {
//            $author = new Author();
//            $author->setFirstName($this->faker->firstName);
//            $author->setName($this->faker->lastName);
//            $author->setPseudonym($this->faker->optional()->userName);
//            $author->setDescription($this->faker->optional()->text(200));
//            $author->setPhotoFilename(sprintf('author_%d.jpg', $i));
//
//            return $author;
//        });
//
//        $this->manager->flush();
//    }

    public function loadData(): void
    {
        $authors = [
            ['Jane', 'Austen'],
            ['Mary', 'Shelley'],
            ['Bram', 'Stoker'],
            ['H.G.', 'Wells'],
            ['Jules', 'Verne'],
            ['Mark', 'Twain'],
            ['Leo', 'Tolstoy'],
            ['Fyodor', 'Dostoevsky'],
            ['Victor', 'Hugo'],
            ['Emily', 'Bronte']
        ];

        foreach ($authors as $i => [$first, $last]) {
            $author = new Author();
            $author->setFirstName($first);
            $author->setName($last);
            $author->setPseudonym(null);
            $author->setDescription("{$first} {$last} was a notable author of classic literature.");
            $author->setPhotoFilename("author_{$i}.jpg");

            $this->addReference("authors_{$i}", $author);
            $this->manager->persist($author);
        }

        $this->manager->flush();
    }

}
