<?php

namespace App\Command;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:city',
    description: 'Show weather for a location identified by country code and city name',
)]
final class WeatherCityCommand extends Command
{
    public function __construct(private LocationRepository $locationRepository, private WeatherUtil $weatherUtil)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('city', InputArgument::REQUIRED, 'City name')
            ->addArgument('country', InputArgument::REQUIRED, 'Country code')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $city = (string) $input->getArgument('city');
        $country = (string) $input->getArgument('country');

        $location = $this->locationRepository->findByCountryAndCity($country, $city);
        if (!$location instanceof Location) {
            $io->error(sprintf('Location not found for %s, %s', $city, $country));
            return Command::FAILURE;
        }

        $data = $this->weatherUtil->getWeatherForLocation($location);
        $measurements = $data['measurements'] ?? [];

        $io->writeln('');
        $io->writeln(sprintf('Location: %s', $location->getCity()));

        $rows = array_map(fn($m) => [
            $m->getDate() ? $m->getDate()->format('Y-m-d') : '',
            $m->getCelsius(),
        ], $measurements);

        $io->table(['Date', 'Temperature'], $rows);

        return Command::SUCCESS;
    }
}
