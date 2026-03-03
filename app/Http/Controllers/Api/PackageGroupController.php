<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackageGroup;
use App\Models\SubscriptionPlan;
use App\Services\PackageGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PackageGroupController extends Controller
{
    public function __construct(
        protected PackageGroupService $packageGroupService
    ) {}

    /**
     * List package groups
     */
    public function index(Request $request): JsonResponse
    {
        $groups = $this->packageGroupService->getActiveGroups($request->team_id);

        return response()->json(['data' => $groups]);
    }

    /**
     * Get a single package group
     */
    public function show(PackageGroup $packageGroup): JsonResponse
    {
        return response()->json([
            'data' => $packageGroup->load(['packages']),
        ]);
    }

    /**
     * Create a new package group
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $group = $this->packageGroupService->createGroup($validated);

        return response()->json(['data' => $group], Response::HTTP_CREATED);
    }

    /**
     * Update a package group
     */
    public function update(Request $request, PackageGroup $packageGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $group = $this->packageGroupService->updateGroup($packageGroup, $validated);

        return response()->json(['data' => $group]);
    }

    /**
     * Delete a package group
     */
    public function destroy(PackageGroup $packageGroup): Response
    {
        $this->packageGroupService->deleteGroup($packageGroup);

        return response()->noContent();
    }

    /**
     * Add a package to a group
     */
    public function addPackage(Request $request, PackageGroup $packageGroup): JsonResponse
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
        $this->packageGroupService->addPackage($packageGroup, $plan, $validated['sort_order'] ?? 0);

        return response()->json([
            'data' => $packageGroup->fresh(['packages']),
            'message' => 'Package added to group.',
        ]);
    }

    /**
     * Remove a package from a group
     */
    public function removePackage(PackageGroup $packageGroup, SubscriptionPlan $plan): JsonResponse
    {
        $this->packageGroupService->removePackage($packageGroup, $plan);

        return response()->json([
            'data' => $packageGroup->fresh(['packages']),
            'message' => 'Package removed from group.',
        ]);
    }

    /**
     * Reorder packages within a group
     */
    public function reorder(Request $request, PackageGroup $packageGroup): JsonResponse
    {
        $validated = $request->validate([
            'plan_ids' => 'required|array|min:1',
            'plan_ids.*' => 'integer|exists:subscription_plans,id',
        ]);

        $this->packageGroupService->reorderPackages($packageGroup, $validated['plan_ids']);

        return response()->json([
            'data' => $packageGroup->fresh(['packages']),
            'message' => 'Packages reordered.',
        ]);
    }
}
