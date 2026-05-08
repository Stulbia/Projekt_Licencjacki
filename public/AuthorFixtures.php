<?php

//namespace public;
//
//use App\DataFixtures\AbstractBaseFixtures;
//use App\Entity\Author;
//
///**
// * Class AuthorFixtures.
// */
//class AuthorFixtures extends AbstractBaseFixtures
//{
//
//    public function loadData(): void
//    {
//        $authors = [
//            ['Jane', 'Austen'],
//            ['Mary', 'Shelley'],
//            ['Bram', 'Stoker'],
//            ['H.G.', 'Wells'],
//            ['Jules', 'Verne'],
//            ['Mark', 'Twain'],
//            ['Leo', 'Tolstoy'],
//            ['Fyodor', 'Dostoevsky'],
//            ['Victor', 'Hugo'],
//            ['Emily', 'Bronte']
//        ];
//
//        foreach ($authors as $i => [$first, $last]) {
//            $author = new Author();
//            $author->setFirstName($first);
//            $author->setName($last);
//            $author->setPseudonym(null);
//            $author->setDescription("{$first} {$last} was a notable author of classic literature.");
//            $author->setPhotoFilename("author_{$i}.jpg");
//
//            $this->addReference("authors_{$i}", $author);
//            $this->manager->persist($author);
//        }
//
//        $this->manager->flush();
//    }
//
//}
