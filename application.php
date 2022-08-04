<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use PhoneNumberUsage\Command\TwilioUsageCommand;
use PhoneNumberUsage\TwilioUsage;
use Symfony\Component\Console\Application;
use Twilio\Rest\Client;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$application = new Application();

$usage = new TwilioUsage(
    new Client(
        $_SERVER['TWILIO_ACCOUNT_SID'],
        $_SERVER['TWILIO_AUTH_TOKEN']
    )
);

// ... register commands
$application->add(new TwilioUsageCommand($usage));

$application->run();