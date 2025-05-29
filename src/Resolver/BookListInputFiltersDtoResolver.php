<?php

/**
 * BookListInputFiltersDto resolver.
 */

namespace App\Resolver;

use App\Dto\BookListInputFiltersDto;
use App\Entity\Enum\BookStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * BookListInputFiltersDtoResolver class.
 */
class BookListInputFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Returns the possible value(s).
     *
     * @param Request          $request  HTTP Request
     * @param ArgumentMetadata $argument Argument metadata
     *
     * @return iterable Iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_a($argumentType, BookListInputFiltersDto::class, true)) {
            return [];
        }

        $galleryId = $request->query->get('galleryId');
        $tagId = $request->query->get('tagId');
        $statusId = $request->query->get('statusId', BookStatus::PUBLIC->value);

        return [new BookListInputFiltersDto($galleryId, $tagId, $statusId)];
    }
}
