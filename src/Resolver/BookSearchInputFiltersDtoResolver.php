<?php

namespace App\Resolver;

use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Enum\BookStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Resolver for BookSearchInputFiltersDto.
 */
class BookSearchInputFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Resolves query parameters into a BookSearchInputFiltersDto.
     *
     * @param Request          $request  HTTP request
     * @param ArgumentMetadata $argument Metadata of the controller argument
     *
     * @return iterable<BookSearchInputFiltersDto>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_a($argumentType, BookSearchInputFiltersDto::class, true)) {
            return [];
        }

        $tagId = $request->query->get('tagId');
        $statusRaw = $request->query->get('statusId', BookStatus::PUBLIC->value);
        $status = BookStatus::tryFrom($statusRaw);

        $titlePattern = $request->query->get('title');
        $descriptionPattern = $request->query->get('description');

        return [new BookSearchInputFiltersDto($tagId, $status, $titlePattern, $descriptionPattern)];
    }
}
