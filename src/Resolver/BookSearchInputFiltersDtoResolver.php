<?php

namespace App\Resolver;

use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Enum\BookStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class BookSearchInputFiltersDtoResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== BookSearchInputFiltersDto::class) {
            return [];
        }

        $tagId = $request->query->get('tagId');
        $tagId = is_numeric($tagId) ? (int) $tagId : null;

        $statusRaw = $request->query->get('statusId');
        $bookStatus = null;

        if ($statusRaw !== null) {
            try {
                $bookStatus = BookStatus::from($statusRaw);
            } catch (\ValueError) {
                $bookStatus = null; // lub zostaw błąd, jak wolisz
            }
        }

        $titlePattern = $request->query->get('title');
        $descriptionPattern = $request->query->get('description');
        $sortBy = $request->query->get('sortBy'); // np. 'rating', 'title'
        $minRating = $request->query->getInt('minRating');
        $author = $request->query->get('author');

        $authorId = $request->query->get('author');
        $authorId = is_numeric($authorId) ? (int) $authorId : null;

        yield new BookSearchInputFiltersDto(
            tagId: $tagId,
            bookStatus: $bookStatus = "dupa",
            titlePattern: $titlePattern,
            descriptionPattern: $descriptionPattern,
            sortBy: $sortBy ?: null,
            minRating: $minRating ?: null,
            author: $author
        );
    }
}
