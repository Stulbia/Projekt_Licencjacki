<?php

/**
 * Book Status enum.
 */

namespace App\Entity\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Enum BookStatus.
 */
enum BookStatus: string implements TranslatableInterface
{
    case PUBLIC = 'PUBLIC';
    case PRIVATE = 'PRIVATE';

    /**
     * Get translated label.
     *
     *
     * @param TranslatorInterface $translator
     * @param string|null $locale
     * @return string Translated label
     */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('status.' . strtolower($this->name));
    }

    /**
     * Get list of available choices.
     *
     * @return array<string, string> Choices for forms
     */
    public static function choices(): array
    {
        return array_combine(
            array_map(fn(self $status) => $status->name, self::cases()),
            array_map(fn(self $status) => $status->value, self::cases())
        );
    }
}
