<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::withCount('users')->orderBy('tier')->orderBy('name')->get();
        return view('admin.badges.index', compact('badges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:badges,name',
            'description' => 'required|string|max:300',
            'icon'        => 'required|string|max:50',
            'tier'        => 'required|in:bronze,silver,gold',
            'criteria'    => 'required|string|max:100|unique:badges,criteria',
        ]);

        $badge = Badge::create($request->only('name', 'description', 'icon', 'tier', 'criteria'));

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'create_badge',
            'details'  => "Created badge '{$badge->name}'",
        ]);

        return redirect()->route('admin.badges.index')->with('success', "Badge '{$badge->name}' created.");
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $request->validate([
            'name'        => ['required','string','max:100', Rule::unique('badges')->ignore($badge->id)],
            'description' => 'required|string|max:300',
            'icon'        => 'required|string|max:50',
            'tier'        => 'required|in:bronze,silver,gold',
        ]);

        $badge->update($request->only('name', 'description', 'icon', 'tier'));

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'update_badge',
            'details'  => "Updated badge '{$badge->name}'",
        ]);

        return redirect()->route('admin.badges.index')->with('success', "Badge '{$badge->name}' updated.");
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $name = $badge->name;
        $badge->users()->detach();
        $badge->delete();

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'delete_badge',
            'details'  => "Deleted badge '{$name}'",
        ]);

        return redirect()->route('admin.badges.index')->with('success', "Badge '{$name}' deleted.");
    }
}
