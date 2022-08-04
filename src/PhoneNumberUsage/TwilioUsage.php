<?php

declare(strict_types=1);

namespace PhoneNumberUsage;

use Twilio\Rest\Api\V2010\Account\Usage\RecordInstance;
use Twilio\Rest\Client;

class TwilioUsage
{
    private Client $client;

    private array $allowedCategories = [
        'calls',
        'pfax-minutes',
        'pfax-pages',
        'phonenumbers',
        'pv',
        'recordings',
        'sms',
        'totalprice',
        'transcriptions',
    ];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return RecordInstance[]
     */
    public function __invoke(
        \DateTimeImmutable $startDate = null,
        \DateTimeImmutable $endDate = null,
        string $category = null
    ): array
    {
        $options = [];

        if ($startDate instanceof \DateTimeImmutable) {
            $options['startDate'] = $startDate;
        }

        if ($endDate instanceof \DateTimeImmutable) {
            $options['endDate'] = $endDate;
        }

        if ($category !== null && in_array($category, $this->allowedCategories)) {
            $options['category'] = $category;
        }

        return $this->client
            ->usage
            ->records
            ->read($options, 20);
    }
}