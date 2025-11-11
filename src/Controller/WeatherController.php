<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Location;
use App\Service\WeatherUtil;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

final class WeatherController extends AbstractController
{
    #[Route('/weather/{city}/{country?}', name: 'app_weather')]
    public function city(
        #[MapEntity(mapping: ['city' => 'city', 'country' => 'country'])]
        Location $location, 
        WeatherUtil $util,
    ): Response
    {

        $data = $util->getWeatherForLocation($location);
        //$data = $util->getWeatherForCountryAndCity($location->getCountry(), $location->getCity());

        return $this->render('weather/city.html.twig', [
            'controller_name' => 'WeatherController',
            'location' => $location,
            'measurements' => $data['measurements'] ?? [],
        ]);
    }
}
