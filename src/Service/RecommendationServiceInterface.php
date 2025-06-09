<?php

namespace App\Service;

use App\Entity\User;

interface RecommendationServiceInterface
{
/**
* @param User|null $user
* @return array Recommended books
*/
    public function getRecommendationsFor(?User $user): array;
}
