

<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InvoiceTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, InvoiceTemplate $template)
    {
        return $user->currentTeam->id === $template->team_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, InvoiceTemplate $template)
    {
        return $user->currentTeam->id === $template->team_id;
    }

    public function delete(User $user, InvoiceTemplate $template)
    {
        return $user->currentTeam->id === $template->team_id;
    }
}