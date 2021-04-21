<?php

namespace App\Service;

use App\Entity\User;

class AccessService
{
    public function hasAccess($user, $testedAccess)
    {
    	/** @var User $user */
		foreach ($user->getAccesses() as $access) {
            if ($testedAccess === $access->getCode()) return true;
        }
        return false;
    }
}