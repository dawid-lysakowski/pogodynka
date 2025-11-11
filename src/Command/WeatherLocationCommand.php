<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use App\Entity\Location;

#[AsCommand(
    name: 'weather:location',
    description: 'Show weather for a location identified by id',
)]
class WeatherLocationCommand extends Command
{
    public function __construct(private LocationRepository $locationRepository, private WeatherUtil $weatherUtil)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Location id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $locationId = $input->getArgument('id');

        $location = $this->locationRepository->find($locationId);
        if (!$location instanceof Location) {
            $io->error(sprintf('Location with id "%s" not found.', $locationId));
            return Command::FAILURE;
        }

        $data = $this->weatherUtil->getWeatherForLocation($location);
        $measurements = $data['measurements'] ?? [];

        $io->writeln('');
        $io->writeln(sprintf('Location: %s', $location->getCity()));
        $io->table(['Date', 'Temperature'], array_map(fn($m) => [
            $m->getDate() ? $m->getDate()->format('Y-m-d') : '',
            $m->getCelsius(),
        ], $measurements));

        return Command::SUCCESS;
    }
}
