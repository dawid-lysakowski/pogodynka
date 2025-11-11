<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\MeasurementRepository;

class WeatherUtil
{
    public function __construct(private MeasurementRepository $measurementRepository, private LocationRepository $locationRepository)
    {
    }

    public function getWeatherForLocation(Location $location): array
    {
        $measurements = $this->measurementRepository->findByLocation($location);

        return [
            'measurements' => $measurements,
        ];
    }

    public function getWeatherForCountryAndCity(string $countryCode, string $city): array
    {
        $location = $this->locationRepository->findByCountryAndCity($countryCode, $city);

        if (!$location instanceof Location) {
            return [
                'measurements' => [],
                'latest' => null,
            ];
        }

        return $this->getWeatherForLocation($location);
    }
}
