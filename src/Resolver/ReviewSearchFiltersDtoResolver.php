<?php

namespace App\Resolver;

use App\Dto\ReviewSearchFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\RequestStack;

class ReviewSearchFiltersDtoResolver implements ArgumentValueResolverInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === ReviewSearchFiltersDto::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield ReviewSearchFiltersDto::fromRequest($this->requestStack->getCurrentRequest());
    }
}
