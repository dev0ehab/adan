<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function countries(): JsonResponse
    {
        $countries = Country::withCount('governorates')->orderBy('name')->get();

        return response()->json(['data' => $countries]);
    }

    public function governorates(Country $country): JsonResponse
    {
        $governorates = $country->governorates()->withCount('cities')->orderBy('name')->get();

        return response()->json(['data' => $governorates]);
    }

    public function cities(Governorate $governorate): JsonResponse
    {
        $cities = $governorate->cities()->withCount('regions')->orderBy('name')->get();

        return response()->json(['data' => $cities]);
    }

    public function regions(City $city): JsonResponse
    {
        $regions = $city->regions()->orderBy('name')->get();

        return response()->json(['data' => $regions]);
    }
}
