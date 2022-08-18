<?php

declare(strict_types=1);

namespace PhoneNumberUsage\Command;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;
use PhoneNumberUsage\TwilioUsage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TwilioUsageCommand extends Command
{
    protected static $defaultName = 'twilio:show-usage';

    protected static $defaultDescription = "Lists a Twilio account's usage details.";

    protected string $helpMessage
        = <<<EOF
The command lists a Twilio account's usage details.
It supports the ability to list account usage for SMS, MMS, and voice calls within a given date range, and filter by usage categories (daily, monthly, today, yesterday, etc).
EOF;

    private TwilioUsage $twilioUsage;
    private array $rows = [];
    private NumberFormatter $formatter;
    private IntlMoneyFormatter $moneyFormatter;

    public function __construct(TwilioUsage $twilioUsage)
    {
        parent::__construct();

        $this->twilioUsage = $twilioUsage;
        $currencies = new ISOCurrencies();
        $this->formatter = new NumberFormatter(
            'en_US', NumberFormatter::CURRENCY
        );
        $this->moneyFormatter = new IntlMoneyFormatter(
            $this->formatter, $currencies
        );
    }

    protected function configure(): void
    {
        $this->setHelp($this->helpMessage);

        $this->addOption(
            'limit-records',
            'l',
            InputOption::VALUE_OPTIONAL,
            'The record limit'
        );
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

    protected function execute(InputInterface $input, OutputInterface $output
    ): int {
        $outputStyle = new OutputFormatterStyle('#56be4e', null, ['bold']);
        $output->getFormatter()->setStyle('fire', $outputStyle);

        $startDate = $input->getOption('start-date') ?: null;
        $endDate = $input->getOption('end-date') ?: null;
        $category = $input->getOption('category') ?: null;
        $limitRecords = (int)$input->getOption('limit-records') ?: 20;

        $totalCost = $this->getUsageData(
            $limitRecords, $startDate, $endDate, $category
        );

        $total = new Money((int)round($totalCost * 100), new Currency('USD'));

        $this->rows[] = new TableSeparator();
        $this->rows[] = [
            new TableCell(
                sprintf(
                    'Total records: %d. Total cost: %s.',
                    count($this->rows) - 1,
                    $this->moneyFormatter->format($total)
                ),
                ['colspan' => 4]
            )
        ];

        $output->writeln("");
        $output->writeln("<fire>Twilio Account Usage Statistics</>\n");

        $filterNotice = $this->getFilterNotification($startDate, $endDate, $category);
        if ($filterNotice) {
            $output->writeln($filterNotice . "\n");
        }


        $table = new Table($output);
        $table
            ->setHeaders(['Date', 'Category', 'Price', 'Currency'])
            ->setRows($this->rows);
        $table->render();

        return Command::SUCCESS;
    }

    public function getUsageData(
        int $limitRecords,
        string $startDate = null,
        string $endDate = null,
        string $category = null
    ): string|float {
        $records = $this->twilioUsage->__invoke(
            $limitRecords, $startDate, $endDate, $category
        );

        $totalCost = 0.00;
        foreach ($records as $record) {
            $this->rows[] = [
                $record->asOf,
                $record->category,
                $this->formatter->formatCurrency(
                    (float)$record->price,
                    strtoupper($record->priceUnit)
                ),
                strtoupper($record->priceUnit),
            ];

            $totalCost += $record->price;
        }

        return $totalCost;
    }

    protected function getFilterNotification(string $startDate = null,
        string $endDate = null, string $category = null
    ): string {
        $output = "";

        if ($startDate === null && $endDate === null && $category === null) {
            return $output;
        }

        $filterOptions = [];

        if ($startDate !== null) {
            $filterOptions[] = sprintf("start date: '%s'", $startDate);
        }

        if ($endDate !== null) {
            $filterOptions[] = sprintf("end date: '%s'", $endDate);
        }

        if ($category !== null) {
            $filterOptions[] = sprintf("category: '%s'", $category);
        }

        return "Filtering by: " . implode(" / ", $filterOptions);
    }
}