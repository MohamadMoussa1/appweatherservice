<?php

namespace App\Services;

use App\Data\WeatherData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

/**
 * Service class for handling weather data retrieval
 * 
 * This service handles communication with the OpenWeather API
 * and transforms the response into a standardized format.
 */
class WeatherService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;

    /**
     * Initialize the service with API key from config
     * 
     * @throws \RuntimeException If API key is not configured
     */
    public function __construct()
    {
        $config = config('services.openweather');
        $this->apiKey = $config['key'] ?? '';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.openweathermap.org/data/2.5', '/');
        $this->timeout = (int) ($config['timeout'] ?? 10);
        
        if (empty($this->apiKey)) {
            throw new \RuntimeException(
                'OpenWeather API key is not configured. Please set OPENWEATHER_API_KEY in your .env file.'
            );
        }
    }

    /**
     * Get weather data for a specific city
     * 
     * @param string $city City name and optional country code (e.g., "London,uk")
     * @return WeatherData
     * @throws \RuntimeException If API request fails or returns invalid data
     */
    public function getWeatherForCity(string $city): WeatherData
    {
        try {
            // Basic input validation
            $city = trim($city);
            if (empty($city)) {
                throw new \InvalidArgumentException('City name cannot be empty');
            }

            // Make the API request
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/weather", [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

            // Handle API errors
            if ($response->failed()) {
                throw $this->createApiException($response);
            }

            $data = $response->json();
            
            // Validate response structure
            if (empty($data['name']) || !isset($data['main']['temp']) || empty($data['weather'][0]['main'])) {
                Log::error('Invalid API response structure', ['response' => $data]);
                throw new \RuntimeException('Received invalid weather data from the API');
            }

            return new WeatherData(
                city: $data['name'],
                temperature: (float) $data['main']['temp'],
                condition: (string) $data['weather'][0]['main'],
                humidity: $data['main']['humidity'] ?? null
            );

        } catch (RequestException $e) {
            // Handle network-related errors
            Log::error('Network error while fetching weather data: ' . $e->getMessage());
            throw new \RuntimeException('Unable to connect to the weather service. Please try again later.');
            
        } catch (\Exception $e) {
            // Log and rethrow other exceptions
            Log::error('Weather service error: ' . $e->getMessage(), [
                'exception' => $e,
                'city' => $city ?? 'unknown'
            ]);
            throw $e;
        }
    }

    /**
     * Create an appropriate exception based on the API response
     * 
     * @param \Illuminate\Http\Client\Response $response
     * @return \RuntimeException
     */
    private function createApiException($response): \RuntimeException
    {
        $status = $response->status();
        $error = $response->json();
        
        $message = match($status) {
            401 => 'Invalid API key. Please check your OpenWeather configuration.',
            404 => 'City not found. Please check the city name and try again.',
            429 => 'API rate limit exceeded. Please try again later.',
            500, 502, 503, 504 => 'Weather service is currently unavailable. Please try again later.',
            default => $error['message'] ?? 'Failed to fetch weather data. Please try again.',
        };
        
        Log::error('Weather API error', [
            'status' => $status,
            'error' => $error,
            'message' => $message
        ]);
        
        return new \RuntimeException($message, $status);
    }
}
