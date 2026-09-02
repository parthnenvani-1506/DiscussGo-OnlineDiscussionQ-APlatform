<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function __construct(
        protected BadgeService $badgeService
    ) {}

    public function index(): View
    {
        $badges         = Badge::withCount('users')->orderBy('tier')->orderBy('name')->get();
        $conditionTypes = BadgeService::CONDITION_TYPES;

        return view('admin.badges.index', compact('badges', 'conditionTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:badges,name',
            'description'     => 'required|string|max:300',
            'icon'            => 'required|string|max:50',
            'tier'            => 'required|in:bronze,silver,gold',
            'condition_type'  => ['required', Rule::in(array_keys(BadgeService::CONDITION_TYPES))],
            'condition_value' => 'required|integer|min:1|max:999999',
        ]);

        // Auto-generate criteria key from name
        $criteria = \Illuminate\Support\Str::snake(strtolower($request->name));
        // Ensure uniqueness
        $base = $criteria;
        $i    = 1;
        while (Badge::where('criteria', $criteria)->exists()) {
            $criteria = $base . '_' . $i++;
        }

        $badge = Badge::create([
            'name'            => $request->name,
            'description'     => $request->description,
            'icon'            => $request->icon,
            'tier'            => $request->tier,
            'criteria'        => $criteria,
            'condition_type'  => $request->condition_type,
            'condition_value' => (int) $request->condition_value,
        ]);

        BadgeService::flushCache();

        // Retroactively award to users who already meet the condition
        $awarded = $this->badgeService->awardRetroactively($badge);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'create_badge',
            'details'  => "Created badge '{$badge->name}' ({$request->condition_type} >= {$request->condition_value}). Retroactively awarded to {$awarded} existing users.",
        ]);

        $msg = "Badge '{$badge->name}' created.";
        if ($awarded > 0) {
            $msg .= " Retroactively awarded to {$awarded} existing user(s).";
        }

        return redirect()->route('admin.badges.index')->with('success', $msg);
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $request->validate([
            'name'            => ['required', 'string', 'max:100', Rule::unique('badges')->ignore($badge->id)],
            'description'     => 'required|string|max:300',
            'icon'            => 'required|string|max:50',
            'tier'            => 'required|in:bronze,silver,gold',
            'condition_type'  => ['required', Rule::in(array_keys(BadgeService::CONDITION_TYPES))],
            'condition_value' => 'required|integer|min:1|max:999999',
        ]);

        $badge->update([
            'name'            => $request->name,
            'description'     => $request->description,
            'icon'            => $request->icon,
            'tier'            => $request->tier,
            'condition_type'  => $request->condition_type,
            'condition_value' => (int) $request->condition_value,
        ]);

        BadgeService::flushCache();

        // Retroactively award to users who now meet the updated condition
        $awarded = $this->badgeService->awardRetroactively($badge);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'update_badge',
            'details'  => "Updated badge '{$badge->name}' ({$request->condition_type} >= {$request->condition_value}). Retroactively awarded to {$awarded} existing users.",
        ]);

        $msg = "Badge '{$badge->name}' updated.";
        if ($awarded > 0) {
            $msg .= " Retroactively awarded to {$awarded} new user(s) who now qualify.";
        }

        return redirect()->route('admin.badges.index')->with('success', $msg);
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $name = $badge->name;
        $badge->users()->detach();
        $badge->delete();

        BadgeService::flushCache();

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'delete_badge',
            'details'  => "Deleted badge '{$name}'",
        ]);

        return redirect()->route('admin.badges.index')
            ->with('success', "Badge '{$name}' deleted.");
    }
}
