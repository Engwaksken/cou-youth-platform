<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LifeGroup;
use App\Models\LifeGroupMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LifeGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LifeGroup::query()
            ->where('is_active', true)
            ->withCount(['members' => fn ($builder) => $builder->where('status', 'active')]);

        if ($request->filled('organisation_unit_id')) {
            $query->where('organisation_unit_id', $request->integer('organisation_unit_id'));
        }

        return response()->json($query->paginate(12));
    }

    public function join(Request $request, LifeGroup $lifeGroup): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $member = DB::transaction(function () use ($lifeGroup, $userId): LifeGroupMember {
            $lockedGroup = LifeGroup::query()->whereKey($lifeGroup->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedGroup->is_active, 404);

            $existing = LifeGroupMember::query()
                ->where('life_group_id', $lockedGroup->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing?->status === 'active') {
                return $existing;
            }

            if ($lockedGroup->member_limit) {
                $active = LifeGroupMember::query()
                    ->where('life_group_id', $lockedGroup->id)
                    ->where('status', 'active')
                    ->count();

                if ($active >= $lockedGroup->member_limit) {
                    throw ValidationException::withMessages([
                        'life_group' => 'This Life Group has reached its member limit.',
                    ]);
                }
            }

            return LifeGroupMember::query()->updateOrCreate(
                ['life_group_id' => $lockedGroup->id, 'user_id' => $userId],
                ['role' => 'member', 'status' => 'active'],
            );
        });

        return response()->json([
            'message' => 'You have joined the Life Group.',
            'data' => $member,
        ]);
    }
}
