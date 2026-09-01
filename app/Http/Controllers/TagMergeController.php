<?php

namespace App\Http\Controllers;

use App\Services\TagMergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagMergeController extends Controller
{
    public function __construct(protected TagMergeService $tagMergeService) {}

    /** Check a typed tag name for exact/near duplicates */
    public function checkTag(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50']);
        return response()->json($this->tagMergeService->checkTag($request->name));
    }

    /** Check a typed category name for similar existing categories */
    public function checkCategory(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100']);
        return response()->json($this->tagMergeService->checkCategory($request->name));
    }
}
