<?php

declare(strict_types=1);

namespace PhoneNumberUsage\Command;

use PhoneNumberUsage\TwilioUsage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twilio\Deserialize;
use Twilio\Rest\Accounts;
use Twilio\Rest\Api\V2010\Account\Usage\RecordInstance;
use Twilio\Rest\Client;
use Twilio\Version;

class TwilioUsageCommand extends Command
{
    protected static $defaultName = 'twilio:show-usage';

    protected static $defaultDescription = "Lists a Twilio account's usage details.";

    protected string $helpMessage = <<<EOF
The command lists a Twilio account's usage details.
It supports the ability to list account usage for SMS, MMS, and voice calls within a given date range, and filter by usage categories (daily, monthly, today, yesterday, etc).
EOF;

    private TwilioUsage $twilioUsage;
    private array $rows = [];

    public function __construct(TwilioUsage $twilioUsage)
    {
        parent::__construct();

        $this->rows;

        $this->twilioUsage = $twilioUsage;
    }

    protected function configure(): void
    {
        $this->setHelp($this->helpMessage);

        $this->addOption(
            'start-date',
            's',
            InputOption::VALUE_OPTIONAL,
            "The usage range's start date"
        );
        $this->addOption(
            'end-date',
            'e',
            InputOption::VALUE_OPTIONAL,
            "The usage range's end date"
        );
        $this->addOption(
            'category',
            'c',
            InputOption::VALUE_OPTIONAL,
            "The usage range's category"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $records = $this->twilioUsage->__invoke(
            $input->getOption('start-date') ?? null,
            $input->getOption('end-date') ?? null,
            $input->getOption('category') ?? null
        );

        foreach ($records as $record) {
            $this->rows[] = [
                $record->asOf,
                $record->category,
                $record->price,
                $record->priceUnit,
            ];
        }

        $output->writeln("Twilio usage statistics");


        $table = new Table($output);
        $table
            ->setHeaders(['Date', 'Category', 'Price', 'Currency'])
            ->setRows($this->rows);
        $table->render();

        return Command::SUCCESS;
    }

    protected function getFilterNotification(string $startDate, string $endDate, string $category): string
    {
        return '';
    }
}