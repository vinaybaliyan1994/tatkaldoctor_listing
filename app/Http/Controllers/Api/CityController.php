<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    // Legacy controller — not registered in routes. Swagger annotation removed to avoid duplicate path.
    public function byCountry(string $countryCode): JsonResponse
    {
        $countryCode = strtoupper($countryCode);

        $country = MasterCountry::find($countryCode);

        if (! $country) {
            return response()->json([
                'success' => false,
                'message' => "Country code \"{$countryCode}\" not found.",
            ], 404);
        }

        $cities = MasterCity::where('country_code', $countryCode)
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'country_code']);

        return response()->json([
            'success' => true,
            'data'    => [
                'country' => [
                    'code' => $country->code,
                    'name' => $country->name,
                ],
                'cities' => $cities,
                'total'  => $cities->count(),
            ],
        ]);
    }
}
