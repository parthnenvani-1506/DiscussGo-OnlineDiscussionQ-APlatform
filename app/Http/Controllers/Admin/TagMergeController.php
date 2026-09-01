<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TagMergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagMergeController extends Controller
{
    public function __construct(protected TagMergeService $tagMergeService) {}

    /** Get AI-grouped duplicate tag clusters */
    public function groups(): JsonResponse
    {
        $groups = $this->tagMergeService->findDuplicateTagGroups();
        return response()->json(['groups' => $groups]);
    }

    /** Execute a tag merge operation */
    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'canonical_id' => 'required|integer|exists:tags,id',
            'merge_ids'    => 'required|array|min:1',
            'merge_ids.*'  => 'integer|exists:tags,id',
        ]);

        $result = $this->tagMergeService->mergeTags(
            (int) $request->canonical_id,
            $request->merge_ids,
            session('admin_id')
        );

        return response()->json($result);
    }
}
