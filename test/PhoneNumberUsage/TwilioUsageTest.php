<?php

declare(strict_types=1);

namespace PhoneNumberUsageTest;

use PhoneNumberUsage\TwilioUsage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Twilio\Deserialize;
use Twilio\Rest\Api\V2010\Account\Usage\RecordInstance;
use Twilio\Rest\Api\V2010\Account\Usage\RecordList;
use Twilio\Rest\Api\V2010\Account\UsageList;
use Twilio\Rest\Client;
use Twilio\Version;

class TwilioUsageTest extends TestCase
{
    use ProphecyTrait;

    public function testCanRetrieveAllRecords()
    {
        $version = $this->prophesize(Version::class);
        $usageRecord = new RecordInstance(
            $version->reveal(),
            [
                'accountSid' => 'AC98e9ac842e79bd12c9da461bcc65b05d',
                'apiVersion' => '2010-04-01',
                'asOf' => '2019-06-24T22:32:49+00:00',
                'category' => 'sms-inbound-shortcode',
                'count' => '0',
                'countUnit' => 'messages',
                'description' => 'Short Code Inbound SMS',
                'endDate' => Deserialize::dateTime('2022-04-20'),
                'price' => '0',
                'priceUnit' => 'usd',
                'startDate' => Deserialize::dateTime('2022-04-02'),
                'subresourceUris' => [
                    "all_time" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/AllTime.json?Category=sms-inbound-shortcode",
                    "daily" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/Daily.json?Category=sms-inbound-shortcode",
                    "last_month" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/LastMonth.json?Category=sms-inbound-shortcode",
                    "monthly" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/Monthly.json?Category=sms-inbound-shortcode",
                    "this_month" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/ThisMonth.json?Category=sms-inbound-shortcode",
                    "today" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/Today.json?Category=sms-inbound-shortcode",
                    "yearly" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/Yearly.json?Category=sms-inbound-shortcode",
                    "yesterday" => "/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/Yesterday.json?Category=sms-inbound-shortcode"
                ],
                'uri' => '/2010-04-01/Accounts/ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/Usage/Records/LastMonth?Category=sms-inbound-shortcode&StartDate=2015-08-01&EndDate=2015-08-31',
                'usage' => '0',
                'usageUnit' => 'messages',
            ],
            'AC98e9ac842e79bd12c9da461bcc65b05d'
        );

        /** @var ObjectProphecy|RecordList $recordList */
        $recordList = $this->prophesize(RecordList::class);
        $recordList
            ->read([], 20)
            ->willReturn([$usageRecord]);

        /** @var ObjectProphecy|UsageList $usageList */
        $usageList = $this->prophesize(UsageList::class);
        $usageList->records = $recordList;

        /** @var ObjectProphecy|Client $client */
        $client = $this->prophesize(Client::class);
        $client->usage = $usageList;

        $cli = new TwilioUsage($client->reveal());

        $this->assertSame([$usageRecord], $cli());
    }
}
