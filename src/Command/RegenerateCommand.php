<?php
// src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateCommand extends Command
{
// the name of the command (the part after "bin/console")
    protected static $defaultName = 'referee:regenerate';
    protected static $unparsedFilesDir = "/var/www/files/replayData/unparsed";
    protected static $processedFilesDir = "/var/www/files/replayData/processed";

    private bool $parseHbrs;

    public function __construct(bool $parseHbrs = false)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        $this->parseHbrs = $parseHbrs;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Regenerates db from replay files.');

        $this
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
                echo "$counter/" . count($files) . "\n";
            }
        }

        return Command::SUCCESS;
    }
}