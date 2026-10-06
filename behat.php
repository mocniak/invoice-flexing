<?php
declare(strict_types=1);
use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('default'))
                    ->withContexts(
                        \App\Tests\Behat\InvoiceContext::class,
                    ),
            )
            ->withExtension(
                new \Behat\Config\Extension(
                    SymfonyExtension::class,
                    [
                        'kernel' => [
                            'environment' => 'test',
                        ],
                        'bootstrap' => 'tests/bootstrap.php',
                    ],
                ),
            ),
    );
