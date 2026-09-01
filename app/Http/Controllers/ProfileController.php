<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Question;
use App\Models\ReputationTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display authenticated user's own profile.
     */
    public function show(): View
    {
        $user = auth()->user();
        return $this->renderProfile($user, isOwner: true);
    }

    /**
     * Display public profile of another user.
     */
    public function showPublic(int $id): View
    {
        $user = User::where('is_suspended', false)->findOrFail($id);
        $isOwner = auth()->check() && auth()->id() === $user->id;

        return $this->renderProfile($user, isOwner: $isOwner);
    }

    /**
     * Render the profile view with all necessary eager-loaded stats and relationships.
     */
    protected function renderProfile(User $user, bool $isOwner): View
    {
        $user->load(['badges']);

        $questions = $user->questions()
            ->with(['category', 'tags', 'acceptedAnswer'])
            ->latest()
            ->paginate(10, ['*'], 'questions_page');

        $answers = $user->answers()
            ->with(['question'])
            ->latest()
            ->paginate(10, ['*'], 'answers_page');

        $reputations = $user->reputationTransactions()
            ->latest()
            ->take(20)
            ->get();

        $stats = [
            'questions_count' => $user->questions()->count(),
            'answers_count' => $user->answers()->count(),
            'accepted_answers_count' => $user->answers()->where('is_accepted', true)->count(),
            'total_reputation' => $user->reputation,
            'badges_count' => $user->badges()->count(),
        ];

        return view('profile.show', compact('user', 'isOwner', 'questions', 'answers', 'reputations', 'stats'));
    }

    /**
     * Show the profile edit form.
     */
    public function edit(): View
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'user_name' => ['required', 'string', 'min:3', 'max:50', Rule::unique('users')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $data = [
            'user_name' => $request->user_name,
            'bio' => $request->bio,
            'city' => $request->city,
        ];

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $fileName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('profiles'), $fileName);
            $data['profile_image'] = $fileName;
        }

        $user->update($data);

        return redirect()->route('profile.show')->with('success', 'Your profile details have been updated.');
    }
}
