<?php
// src/Command/CreateUserCommand.php
namespace App\Command;

use App\Service\MatchCalculatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateCommand extends Command
{
    protected static $defaultName = 'referee:regenerate';
    public static string $unparsedFilesDir = "/var/www/files/replayData/unparsed";
    public static string $processedFilesDir = "/var/www/files/replayData/processed";


    public function __construct(
        protected MatchCalculatorService $matchCalculatorService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Regenerates db from replay files.');

        $this
            ->addArgument('verbose', InputArgument::OPTIONAL, "Output progress")
            ->addArgument('parseHbrs', InputArgument::OPTIONAL, 'Parse from hbrs instead of using ready jsons')
            ->addArgument('reparseHbrs', InputArgument::OPTIONAL, 'Reparse all hbrs instead of checking if it has been already parsed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = scandir(self::$unparsedFilesDir);
        array_shift($files);
        array_shift($files);

        $processed = scandir(self::$processedFilesDir);

        $counter = 0;
        $out = [];

        if ($input->getArgument("parseHbrs") === "true") {
            foreach ($files as $file) {
                $counter++;
                if ($input->getArgument("reparseHbrs") !== "true" && in_array($file.'.bin.json', $processed)) {
                    continue;
                }

                exec("node /var/www/parser/haxball/replay.js convert /var/www/files/replayData/unparsed/$file /var/www/files/replayData/preprocessed/$file.bin", $out);
                exec("python3 /var/www/parser/test.py /var/www/files/replayData/preprocessed/$file.bin", $out);
                if ($input->getArgument("verbose") === "true") {
                    echo "$counter/" . count($files) . "\n";
                }
            }
        }
        $counter = 0;
        $files = scandir(self::$processedFilesDir);
        array_shift($files);
        array_shift($files);
        foreach ($files as $file) {
            $start = microtime(true);
            $this->matchCalculatorService->process($this->matchCalculatorService->getDataFromFile($file));
            if ($input->getArgument("verbose") === "true") {
                echo "Calculating: $counter/" . count($files) . " - " . microtime(true) - $start ."\n";
                $counter++;
            }
        }

        return Command::SUCCESS;
    }
}