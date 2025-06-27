<?php
// src/Security/Voter/AccountVoter.php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccountVoter extends Voter
{
    public const EDIT = 'ACCOUNT_EDIT';
    public const VIEW = 'ACCOUNT_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW], true)
            && $subject instanceof Account;
    }

    /**
     * @param Account $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // anonimowi nie mogą
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
                // tylko właściciel
                return $subject->getUser()->getId() === $user->getId();
        }

        return false;
    }
}