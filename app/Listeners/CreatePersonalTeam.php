<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\TeamManagementService;
use Illuminate\Auth\Events\Registered;

class CreatePersonalTeam
{
    public function __construct(protected TeamManagementService $teamManagementService) {}

    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $this->teamManagementService->assignUserToDefaultTeam($user);
    }
}
