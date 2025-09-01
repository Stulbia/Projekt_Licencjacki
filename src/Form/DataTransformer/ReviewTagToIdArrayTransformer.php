<?php

namespace App\Form\DataTransformer;

use App\Entity\ReviewTag;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ReviewTagToIdArrayTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if ($value instanceof Collection) {
            return $value->map(fn(ReviewTag $tag) => $tag->getId())->toArray();
        }

        return [];
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!is_array($value)) {
            return new ArrayCollection();
        }

        // Normally you'd fetch the tags from the database here
        // This is simplified — we'll inject a tag repository if needed
        return new ArrayCollection($value);
    }
}
