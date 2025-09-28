<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WeatherController extends Controller
{
    public function __construct(
        protected WeatherService $weatherService
    ) {}

    /**
     * Get weather information for a specific city
     *
     * @param Request $request The HTTP request
     * @param string $city City name and optional country code (e.g., "London,uk")
     * @return JsonResponse JSON response with weather data or error message
     */
    public function getWeather(Request $request, string $city): JsonResponse
    {
        try {
            // Basic input validation
            $city = trim($city);
            if (empty($city)) {
                throw ValidationException::withMessages([
                    'city' => ['The city parameter is required.']
                ]);
            }

            // Get weather data from service
            $weather = $this->weatherService->getWeatherForCity($city);
            
            // Return successful response
            return response()->json($weather->toArray());
            
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Invalid input',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
            
        } catch (\InvalidArgumentException $e) {
            // Handle invalid arguments
            return response()->json([
                'error' => 'Bad Request',
                'message' => $e->getMessage()
            ], 400);
            
        } catch (\RuntimeException $e) {
            // Handle service errors with appropriate status code
            $status = $e->getCode() >= 400 && $e->getCode() < 600 
                ? $e->getCode() 
                : 500;
                
            return response()->json([
                'error' => $status < 500 ? 'Client Error' : 'Server Error',
                'message' => $e->getMessage()
            ], $status);
            
        } catch (\Exception $e) {
            // Log unexpected errors
            \Illuminate\Support\Facades\Log::error('Unexpected error in WeatherController: ' . $e->getMessage(), [
                'exception' => $e,
                'city' => $city ?? 'unknown'
            ]);
            
            // Return generic error response
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }
}
