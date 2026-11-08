<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\Project;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function provinces(): JsonResponse
    {
        $provinces = Province::orderBy('name')->get(['id', 'name', 'slug']);
        return response()->json($provinces);
    }

    public function cities(Request $request): JsonResponse
    {
        $query = City::query();
        if ($request->has('province_id')) {
            $query->where('province_id', $request->province_id);
        }
        $cities = $query->orderBy('name')->get(['id', 'province_id', 'name', 'slug']);
        return response()->json($cities);
    }

    public function districts(Request $request): JsonResponse
    {
        $query = District::query();
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        $districts = $query->orderBy('name')->get(['id', 'city_id', 'name', 'slug']);
        return response()->json($districts);
    }

    public function projects(Request $request): JsonResponse
    {
        $query = Project::query();
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        $projects = $query->orderBy('name')->get([
            'id', 'city_id', 'district_id', 'name', 'type', 'developer', 'address', 'total_floors'
        ]);
        return response()->json($projects);
    }
}
