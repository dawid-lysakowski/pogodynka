<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\MeasurementRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WeatherController extends AbstractController
{
    #[Route('/weather/{city}/{country?}', name: 'app_weather')]
    public function city(string $city, ?string $country, LocationRepository $locationRepository, MeasurementRepository $repository): Response
    {
        $qb = $locationRepository->createQueryBuilder('l')
            ->where('LOWER(l.city) = LOWER(:city)')
            ->setParameter('city', $city);

        if ($country) {
            $qb->andWhere('LOWER(l.country) = LOWER(:country)')
                ->setParameter('country', $country);
        }

        $location = $qb->getQuery()->getOneOrNullResult();

        if (!$location instanceof Location) {
            throw new NotFoundHttpException(sprintf('Location "%s"%s not found.', $city, $country ? " (".$country.")" : ''));
        }

        $measurements = $repository->findByLocation($location);

        return $this->render('weather/city.html.twig', [
            'controller_name' => 'WeatherController',
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}
