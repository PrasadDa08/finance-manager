<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $defaultCategories = Category::where('user_id', null)->get();
        $userCategories = auth('sanctum')->user()->categories()->with('user')->orderBy('id', 'desc')->get();

        $categories = $defaultCategories->merge($userCategories);

        return response()->json([
            'success' => true,
            'message' => 'All Categories fetched successfully',
            'data' => $categories,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'icon' => 'string|nullable|max:50',
            'color' => 'string|nullable|max:20',
            'is_default' => 'boolean'
        ]);

        $user = auth('sanctum')->user();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'is_default' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Category added successfully",
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $user = auth('sanctum')->user();

        $isOwner = $user->id === $category->user_id;
        $isDefault = $category->is_default || $category->user_id === null;

        if (!$isOwner && !$isDefault) {
            return response()->json([
                'success' => false,
                'message' => "Category dosen't belongs to you",
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category fetched successfully',
            'data' => $category->load('user'),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'icon' => 'string|nullable|max:50',
            'color' => 'string|nullable|max:20',
        ]);

        $user = auth('sanctum')->user();

        if ($user->id !== $category->user_id) {
            return response()->json([
                'success' => false,
                'message' => "Category dosen't belongs to you",
            ], 403);
        }

        if ($category->is_default || $category->user_id === null) {
            return response()->json([
                'success' => false,
                "message" => "System categories can not be edited",
            ], 403);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->fresh()->load('user'),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $user = auth('sanctum')->user();

        if ($category->is_default || $category->user_id === null) {
            return response()->json([
                'success' => false,
                "message" => "System categories can not be deleted",
            ], 403);
        }

        if ($user->id !== $category->user_id) {
            return response()->json([
                'success' => false,
                'message' => "Category dosen't belongs to you",
            ], 403);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => "Category deleted successfully",
        ], 200);
    }
}
