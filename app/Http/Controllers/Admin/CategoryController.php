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
    /**
     * Display listing of categories in admin panel.
     */
    public function index(): View
    {
        $categories = Category::withCount('questions')->orderByDesc('questions_count')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show create category form.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'slug'        => 'nullable|string|max:120|unique:categories,slug',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:20',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        $imageFilename = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageFilename = 'cat_' . Str::slug($request->name) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('categories'), $imageFilename);
        }

        $category = Category::create([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'icon'        => $request->icon ?? 'bi bi-folder',
            'color'       => $request->color ?? '#2563eb',
            'image'       => $imageFilename,
        ]);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'create_category',
            'details'  => "Created category '{$category->name}'",
        ]);

        return redirect()->route('admin.categories.index')->with('success', "Category '{$category->name}' created successfully.");
    }

    /**
     * Show edit category form.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($category->id)],
            'slug'        => ['nullable', 'string', 'max:120', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:20',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        $data = [
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'icon'        => $request->icon ?? $category->icon,
            'color'       => $request->color ?? $category->color,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && file_exists(public_path('categories/' . $category->image))) {
                unlink(public_path('categories/' . $category->image));
            }
            $file = $request->file('image');
            $imageFilename = 'cat_' . Str::slug($request->name) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('categories'), $imageFilename);
            $data['image'] = $imageFilename;
        }

        $category->update($data);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'update_category',
            'details'  => "Updated category '{$category->name}'",
        ]);

        return redirect()->route('admin.categories.index')->with('success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Delete category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->questions()->count() > 0) {
            return back()->withErrors(['category' => 'Cannot delete category that currently has questions. Reassign them first.']);
        }

        $adminId = session('admin_id');
        $name = $category->name;

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => 'delete_category',
            'details' => "Deleted category '{$name}'",
        ]);

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', "Category '{$name}' deleted.");
    }
}
