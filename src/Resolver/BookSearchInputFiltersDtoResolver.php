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
        $statusRaw = $request->query->get('statusId', BookStatus::PUBLIC->value);
        $status = BookStatus::tryFrom($statusRaw)?->value ?? BookStatus::PUBLIC->value;

        $titlePattern = $request->query->get('title');
        $descriptionPattern = $request->query->get('description');

        return [new BookSearchInputFiltersDto($tagId, $status, $titlePattern, $descriptionPattern)];
    }
}
