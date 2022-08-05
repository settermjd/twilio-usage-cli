<?php

declare(strict_types=1);

namespace PhoneNumberUsage;

use Twilio\Rest\Api\V2010\Account\Usage\RecordInstance;
use Twilio\Rest\Client;

class TwilioUsage
{
    public const MAX_RESULTS = 20;

    private Client $client;

    private array $allowedCategories = [
        'calls' => 'calls',
        'pfax minutes' => 'pfax-minutes',
        'pfax pages' => 'pfax-pages',
        'phone numbers' => 'phonenumbers',
        'pv' => 'pv',
        'recordings' => 'recordings',
        'sms' => 'sms',
        'total price' => 'totalprice',
        'transcriptions' => 'transcriptions',
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
        int $maxResults = self::MAX_RESULTS,
        string $startDate = null,
        string $endDate = null,
        string $category = null
    ): array
    {
        $options = [];

        if ($startDate !== null) {
            $options['startDate'] = new \DateTimeImmutable($startDate);
        }

        if ($endDate !== null) {
            $options['endDate'] = new \DateTimeImmutable($endDate);
        }

        if ($category !== null && in_array(strtolower($category), array_keys($this->allowedCategories))) {
            $options['category'] = $this->allowedCategories[strtolower($category)];
        }

        return $this->client
            ->usage
            ->records
            ->read($options, $maxResults);
    }
}