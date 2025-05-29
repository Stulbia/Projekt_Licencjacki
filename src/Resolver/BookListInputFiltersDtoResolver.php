<?php

namespace App\Resolver;

use App\Dto\BookListInputFiltersDto;
use App\Entity\Enum\BookStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class BookListInputFiltersDtoResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== BookListInputFiltersDto::class) {
            return [];
        }

        $tagId = $request->query->has('tagId') && is_numeric($request->query->get('tagId'))
            ? (int) $request->query->get('tagId')
            : null;

        $statusRaw = $request->query->get('statusId', BookStatus::PUBLIC->value);
        $status = BookStatus::tryFrom($statusRaw)?->value ?? BookStatus::PUBLIC->value;

        return [new BookListInputFiltersDto($tagId, $status)];
    }
}
