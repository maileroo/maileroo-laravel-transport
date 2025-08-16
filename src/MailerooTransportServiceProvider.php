<?php

namespace Maileroo\LaravelTransport;

use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Maileroo\MailerooClient;

class MailerooTransportServiceProvider extends ServiceProvider {

    public function register(): void {
        $this->mergeConfigFrom(__DIR__ . '/../config/maileroo.php', 'maileroo');
    }

    public function boot(MailManager $manager): void {

        $this->publishes([
            __DIR__ . '/../config/maileroo.php' => config_path('maileroo.php'),
        ], 'maileroo-config');

        $mailers = config('mail.mailers', []);

        if (!array_key_exists('maileroo', $mailers)) {

            $mailers['maileroo'] = [
                'transport' => 'maileroo',
            ];

            config(['mail.mailers' => $mailers]);

        }

        $manager->extend('maileroo', function () {

            $cfg = config('maileroo');

            $apiKey = (string)($cfg['api_key'] ?? '');
            $timeout = (int)($cfg['timeout'] ?? 30);

            $client = new MailerooClient($apiKey, $timeout);

            return new MailerooTransport($client, [
                'tracking' => $cfg['tracking'] ?? null,
                'tags' => $cfg['tags'] ?? [],
            ]);

        });

    }

}