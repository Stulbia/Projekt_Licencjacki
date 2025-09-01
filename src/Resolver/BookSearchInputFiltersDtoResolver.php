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

//        $tagId = $request->query->get('tagId');
//        $tagId = is_numeric($tagId) ? (int) $tagId : null;

//        $reviewTagId = $request->query->get('reviewTagId');
//        $reviewTagId = is_numeric($reviewTagId) ? (int) $reviewTagId : null;

        $tagId       = array_map('intval', $request->query->all('tag'));
        $reviewTagId = array_map('intval', $request->query->all('reviewTags'));

        $statusRaw = $request->query->get('statusId');
        $bookStatus = null;

        if ($statusRaw !== null) {
            try {
                $bookStatus = BookStatus::from($statusRaw);
            } catch (\ValueError) {
                $bookStatus = null;
            }
        }

        $titlePattern = $request->query->get('title');
        $descriptionPattern = $request->query->get('description');
        $sortBy = $request->query->get('sortBy'); // np. 'rating', 'title'
        $minRating = $request->query->getInt('minRating');
        $author = $request->query->get('authorTerm');

        //$authorId = $request->query->get('author');
        //$authorId = is_numeric($authorId) ? (int) $authorId : null;

        yield new BookSearchInputFiltersDto(
            tagId: $tagId,
            bookStatus: $bookStatus = "",
            titlePattern: $titlePattern,
            descriptionPattern: $descriptionPattern,
            sortBy: $sortBy ?: null,
            minRating: $minRating ?: null,
            reviewTagId: $reviewTagId,
            author: $author
        );
    }
}
