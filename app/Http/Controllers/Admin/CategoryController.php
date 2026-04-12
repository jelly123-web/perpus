<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use HandlesAsyncRequests;

    public function index(): View
    {
        $categories = Category::query()->withCount('books')->orderBy('name')->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        ActivityLogger::log('categories', 'create', 'Menambahkan kategori '.$category->name, ['category_id' => $category->id]);

        return $this->successResponse($request, 'Kategori berhasil ditambahkan.', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
            ],
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
            'description' => ['nullable', 'string'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        ActivityLogger::log('categories', 'update', 'Mengubah kategori '.$category->name, ['category_id' => $category->id]);

        return $this->successResponse($request, 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        ActivityLogger::log('categories', 'delete', 'Menghapus kategori '.$category->name, ['category_id' => $category->id]);
        $category->delete();

        return $this->successResponse($request, 'Kategori berhasil dihapus.');
    }
}
