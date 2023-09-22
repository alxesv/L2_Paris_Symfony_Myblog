<?php

// src/Security/UserVoter.php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    const DELETE = 'USER_DELETE';
    const EDIT = 'USER_EDIT';
    const SHOW = 'USER_SHOW';
    const RESET_PASSWORD = 'USER_RESET_PASSWORD';
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::DELETE, self::EDIT, self::SHOW, self::RESET_PASSWORD])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match($attribute) {
            self::DELETE, self::EDIT, self::SHOW, self::RESET_PASSWORD => $user === $subject,
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}
