<?php

namespace App\Resolver;

use App\Dto\BookListInputFiltersDto;
use App\Entity\Enum\BookStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Resolver for BookListInputFiltersDto.
 */
class BookListInputFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Resolves query parameters into a BookListInputFiltersDto.
     *
     * @param Request          $request  The HTTP request
     * @param ArgumentMetadata $argument The argument metadata
     *
     * @return iterable<BookListInputFiltersDto>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_a($argumentType, BookListInputFiltersDto::class, true)) {
            return [];
        }

        $tagId = $request->query->get('tagId');
        $statusRaw = $request->query->get('statusId', BookStatus::PUBLIC->value);

        $status = BookStatus::tryFrom($statusRaw);

        return [new BookListInputFiltersDto($tagId, $status)];
    }
}
