<?php

namespace App\Policies;

use App\Models\MotivationLetter;
use App\Models\MotivationTemplate;
use App\Models\User;

/**
 * Who can do what to motivation templates. The brief grants every admin
 * (developer / owner / admin) full control; keeping it policy-driven
 * means we can tighten this later without touching every controller.
 */
class MotivationTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, MotivationTemplate|MotivationLetter|null $subject = null): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, MotivationTemplate|MotivationLetter|null $subject = null): bool
    {
        if ($subject instanceof MotivationLetter) {
            return $user->isAdmin() && ! $subject->isFinal();
        }

        return $user->isAdmin();
    }

    public function publish(User $user, ?MotivationTemplate $template = null): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, ?MotivationTemplate $template = null): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, MotivationTemplate $template): bool
    {
        // Templates are never fully deleted once published versions are in
        // use — only archived — but a never-published template can go.
        return $user->isAdmin()
            && $template->status !== MotivationTemplate::STATUS_PUBLISHED
            && $template->versions()->whereNotNull('published_at')->doesntExist();
    }

    public function generateLetter(User $user, MotivationTemplate $template): bool
    {
        return $user->isAdmin();
    }

    public function editLetter(User $user, MotivationLetter $letter): bool
    {
        return $user->isAdmin() && ! $letter->isFinal();
    }
}
