<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display listing of all tags.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $query = Tag::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }

        $tags = $query->orderByDesc('usage_count')->paginate(25)->withQueryString();
        $allTags = Tag::orderBy('name')->get();

        return view('admin.tags.index', compact('tags', 'allTags', 'search'));
    }

    /**
     * Store new tag.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'description' => 'nullable|string|max:300',
        ]);

        $tag = Tag::create([
            'name' => strtolower(trim($request->name)),
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action' => 'create_tag',
            'details' => "Created tag '{$tag->name}'",
        ]);

        return redirect()->route('admin.tags.index')->with('success', "Tag '{$tag->name}' created.");
    }

    /**
     * Update tag.
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('tags')->ignore($tag->id)],
            'description' => 'nullable|string|max:300',
        ]);

        $tag->update([
            'name' => strtolower(trim($request->name)),
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action' => 'update_tag',
            'details' => "Updated tag '{$tag->name}'",
        ]);

        return redirect()->route('admin.tags.index')->with('success', "Tag '{$tag->name}' updated.");
    }

    /**
     * Delete tag.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $adminId = session('admin_id');
        $name = $tag->name;

        // Detach from all questions
        $tag->questions()->detach();
        $tag->delete();

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => 'delete_tag',
            'details' => "Deleted tag '{$name}' and detached from all discussions",
        ]);

        return redirect()->route('admin.tags.index')->with('success', "Tag '{$name}' deleted.");
    }

    /**
     * Merge source tag into target tag.
     */
    public function merge(Request $request): RedirectResponse
    {
        $request->validate([
            'source_tag_id' => 'required|exists:tags,id',
            'target_tag_id' => 'required|exists:tags,id|different:source_tag_id',
        ]);

        $sourceTag = Tag::findOrFail($request->source_tag_id);
        $targetTag = Tag::findOrFail($request->target_tag_id);

        DB::transaction(function () use ($sourceTag, $targetTag) {
            // Get all questions using source tag
            $questionIds = DB::table('question_tag')
                ->where('tag_id', $sourceTag->id)
                ->pluck('question_id');

            foreach ($questionIds as $qId) {
                // Check if target tag is already attached
                $exists = DB::table('question_tag')
                    ->where('question_id', $qId)
                    ->where('tag_id', $targetTag->id)
                    ->exists();

                if (!$exists) {
                    DB::table('question_tag')->insert([
                        'question_id' => $qId,
                        'tag_id' => $targetTag->id,
                    ]);
                }
            }

            // Remove source tag pivots and delete source tag
            DB::table('question_tag')->where('tag_id', $sourceTag->id)->delete();
            $sourceTag->delete();

            // Recalculate target tag usage count
            $newUsage = DB::table('question_tag')->where('tag_id', $targetTag->id)->count();
            $targetTag->update(['usage_count' => $newUsage]);
        });

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action' => 'merge_tag',
            'details' => "Merged tag '{$sourceTag->name}' into '{$targetTag->name}'",
        ]);

        return redirect()->route('admin.tags.index')->with('success', "Tag '{$sourceTag->name}' successfully merged into '{$targetTag->name}'.");
    }
}
