<?php

namespace App\DataFixtures;

use App\Entity\ReviewTag;

class ReviewTagFixtures extends AbstractBaseFixtures
{
//    public function loadData(): void
//    {
//        $this->createMany(10, 'review_tags', function (int $i) {
//            $tag = new ReviewTag();
//            $tag->setName($this->faker->unique()->word);
//
//            return $tag;
//        });

        public function loadData(): void
    {
        $myTags = [
            'Wciągająca',
            'Inspirująca',
            'Pouczająca',
            'Zabawna',
            'Wzruszająca',
            'Trzymająca w napięciu',
            'Mroczna',
            'Relaksująca',
            'Przygodowa',
            'Emocjonująca',
            'Skłaniająca do refleksji',
            'Motywująca',
            'Kontrowersyjna',
            'Tajemnicza',
            'Dynamiczna',
            'Romantyczna',
            'Realistyczna',
            'Fantastyczna',
            'Trudna w odbiorze',
            'Łatwa do czytania'
        ];
        // Jako pierwszy argument podajemy liczbę elementów w tablicy
        $this->createMany(count($myTags), 'review_tags', function (int $i) use ($myTags) {
            $tag = new ReviewTag();

            // Pobieramy słowo z listy na podstawie aktualnego indeksu $i
            $tag->setName($myTags[$i]);

            return $tag;
        });





        $this->manager->flush();
    }
}
