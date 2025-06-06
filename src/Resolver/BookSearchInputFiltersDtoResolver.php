<?php

namespace App\Resolver;

use App\Dto\BookSearchInputFiltersDto;
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
        $tagId = is_numeric($tagId) ? (int)$tagId : null;

        $statusRaw = $request->query->get('statusId');
        $bookStatus = $statusRaw ?: 'PUBLIC';

        $titlePattern = $request->query->get('title');
        $descriptionPattern = $request->query->get('description');
        $sortBy = $request->query->get('sortBy'); // np. 'rating', 'title'
        $minRating = $request->query->getInt('minRating');


        yield new BookSearchInputFiltersDto(
            tagId: $tagId,
            bookStatus: $bookStatus,
            titlePattern: $titlePattern,
            descriptionPattern: $descriptionPattern,
            sortBy: $sortBy ?: null,
            minRating: $minRating ?: null
        );
    }
}
