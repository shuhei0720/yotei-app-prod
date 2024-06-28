<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return mixed
     */
    public function update(User $user, Event $event)
    {
        return $user->id === $event->user_id;
    }

    /**
     * Determine whether the user can delete the event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return mixed
     */
    public function delete(User $user, Event $event)
    {
        return $user->id === $event->user_id;
    }
}