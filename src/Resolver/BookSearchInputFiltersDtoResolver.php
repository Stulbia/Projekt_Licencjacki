<?php
/**
 * BookSearchInputFiltersDto resolver.
 */

namespace App\Resolver;

use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Enum\BookStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * BookSearchInputFiltersDtoResolver class.
 */
class BookSearchInputFiltersDtoResolver implements ValueResolverInterface
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

        if (!$argumentType || !is_a($argumentType, BookSearchInputFiltersDto::class, true)) {
            return [];
        }

        $categoryId = $request->query->get('categoryId');
        $tagId = $request->query->get('tagId');
        $statusId = $request->query->get('statusId', BookStatus::PUBLIC->value);
        $titleId = $request->query->get('titleId');
        $descriptionId = $request->query->get('descriptionId');

        return [new BookSearchInputFiltersDto($categoryId, $tagId, $statusId, $titleId, $descriptionId)];
    }
}
