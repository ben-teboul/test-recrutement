<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
        $routes->add('game_api', '/game/api')->controller([$this, 'apiGame']);
    }

    /**
     * Get 20 unique random winners, between 1 and 1000, not in an array of losers
     */
    public function apiGame(): JsonResponse
    {
        $losers = [
            2, 56, 345, 674, 234, 764, 543, 123, 324, 9, 78, 12, 94, 12, 50, 5, 13
        ];

        $winners = [];

        $i = 0;
        do {
            $i++;
            foreach ($losers as $loser) {
                if ($i === $loser) {
                    continue 2;
                }
            }
            foreach ($winners as $winner) {
                if ($i === $winner) {
                    continue 2;
                }
            }
            $winners[] = $i;
        } while (count($winners) < 200 && $i < 1000);
        // Shuffle the array
        shuffle($winners);
        $winners = array_slice($winners, 0, 20);
        return new JsonResponse(['winners' => $winners]);
    }
}
