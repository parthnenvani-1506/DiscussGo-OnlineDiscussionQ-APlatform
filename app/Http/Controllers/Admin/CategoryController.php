<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('questions')->orderByDesc('questions_count')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'slug'        => 'nullable|string|max:120|unique:categories,slug',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        $category = Category::create([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
        ]);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'create_category',
            'details'  => "Created category '{$category->name}'",
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' created successfully.");
    }

    public function edit(Category $category): View
    {
        $category->loadCount('questions');
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($category->id)],
            'slug'        => ['nullable', 'string', 'max:120', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string|max:500',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        $category->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
        ]);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'update_category',
            'details'  => "Updated category '{$category->name}'",
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' updated successfully.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->questions()->count() > 0) {
            return back()->withErrors(['category' => 'Cannot delete a category that has questions. Reassign them first.']);
        }

        $name = $category->name;

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'delete_category',
            'details'  => "Deleted category '{$name}'",
        ]);

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$name}' deleted.");
    }
}
