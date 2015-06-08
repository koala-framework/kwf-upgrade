<?php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
require 'TrlXmlToPoConverter.php';

class ConvertTrlXmlToPoCommand extends Command
{
    protected function configure()
    {
        $this->setName('convertTrlXmlToPo')
            ->setDescription('Convert xml-trl file into po-trl file')
            ->addArgument('trlXmlPath', InputArgument::REQUIRED, 'Path to trl xml file')
            ->addOption('outputPath', 'p', InputOption::VALUE_OPTIONAL, 'Filename for trl po file', 'trl.po')
            ->addOption('webcodeLanguage', 'b', InputOption::VALUE_OPTIONAL, 'Language used as id (default is en)', 'en')
            ->addOption('targetLanguage', 't', InputOption::VALUE_OPTIONAL, 'Language used for translation (default is none)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $trlXmlPath = $input->getArgument('trlXmlPath');
        $outputPath = $input->getOption('outputPath');
        $webcodeLanguage = $input->getOption('webcodeLanguage');
        $targetLanguage = $input->getOption('targetLanguage');

        $trlXmlToPoConverter = new TrlXmlToPoConverter();
        $trlXmlToPoConverter->setXmlPath($trlXmlPath);
        $trlXmlToPoConverter->setBaseLanguage($webcodeLanguage);
        if ($targetLanguage) $trlXmlToPoConverter->setTargetLanguage($targetLanguage);
        $trlXmlToPoConverter->convertToPo($output);

        $trlXmlToPoConverter->writePoContent($outputPath);
    }
}
