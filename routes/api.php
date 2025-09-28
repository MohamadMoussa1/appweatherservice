<?php

use App\Http\Controllers\Api\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Enjoy building your API!
|
*/

// Weather API v1
Route::prefix('v1')->group(function () {
    /**
     * Get weather for a specific city
     * 
     * @param string $city City name and optional country code (e.g., "London,uk" or "New York,us")
     * @return \Illuminate\Http\JsonResponse
     */
    Route::get('/weather/{city}', [WeatherController::class, 'getWeather'])
        ->where('city', '.+') // Allow any character in city name (will be URL encoded)
        ->name('weather.get');
});
