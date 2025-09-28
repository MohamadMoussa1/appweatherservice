<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for weather information
 * 
 * This class provides a consistent structure for weather data
 * and handles proper formatting of the output.
 */
class WeatherData extends Data
{
    public function __construct(
        public string $city,
        public float $temperature,
        public string $condition,
        public ?float $humidity = null,
    ) {}
    
    /**
     * Convert the weather data to an array with formatted values
     * 
     * @return array Formatted weather data
     */
    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'temperature' => round($this->temperature, 1),
            'condition' => $this->condition,
            'humidity' => $this->humidity ? (int) $this->humidity : null,
        ];
    }
}
