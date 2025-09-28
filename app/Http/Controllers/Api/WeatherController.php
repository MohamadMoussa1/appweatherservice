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
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Handle HTTP client errors (network issues, timeouts, etc.)
            $status = $e->response ? $e->response->status() : 503;
            $message = match($status) {
                401 => 'Invalid API key. Please check your OpenWeather configuration.',
                404 => 'City not found. Please check the city name and try again.',
                429 => 'API rate limit exceeded. Please try again later.',
                500, 502, 503, 504 => 'Weather service is currently unavailable. Please try again later.',
                default => 'Unable to connect to the weather service. Please try again.'
            };

            \Illuminate\Support\Facades\Log::error('Weather API request failed', [
                'status' => $status,
                'message' => $e->getMessage(),
                'city' => $city ?? 'unknown'
            ]);

            return response()->json([
                'error' => $status < 500 ? 'Client Error' : 'Service Unavailable',
                'message' => $message
            ], $status >= 400 && $status < 600 ? $status : 503);
            
        } catch (\JsonException $e) {
            // Handle JSON parsing errors
            \Illuminate\Support\Facades\Log::error('Failed to parse weather API response', [
                'message' => $e->getMessage(),
                'city' => $city ?? 'unknown'
            ]);

            return response()->json([
                'error' => 'Invalid Response',
                'message' => 'Received invalid data from the weather service.'
            ], 502);
            
        } catch (\InvalidArgumentException $e) {
            // Handle invalid arguments
            return response()->json([
                'error' => 'Bad Request',
                'message' => $e->getMessage()
            ], 400);
            
        } catch (\RuntimeException $e) {
            // Handle service errors with appropriate status code
            $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            \Illuminate\Support\Facades\Log::error('Weather service error', [
                'status' => $status,
                'message' => $e->getMessage(),
                'city' => $city ?? 'unknown'
            ]);
            
            return response()->json([
                'error' => $status < 500 ? 'Client Error' : 'Server Error',
                'message' => $e->getMessage()
            ], $status);
            
        } catch (\Exception $e) {
            // Log unexpected errors with stack trace
            \Illuminate\Support\Facades\Log::error('Unexpected error in WeatherController: ' . $e->getMessage(), [
                'exception' => $e,
                'city' => $city ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return generic error response
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }
}
