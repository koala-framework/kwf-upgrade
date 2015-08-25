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
            ->addOption('webcodeLanguage', 'b', InputOption::VALUE_OPTIONAL, 'Language used as id (default looking up in config.ini)')
            ->addOption('targetLanguage', 't', InputOption::VALUE_OPTIONAL, 'Language used for translation (if none specified every containing language is created)')
            ->addOption('trlXmlPath', 'p', InputOption::VALUE_OPTIONAL, 'Path to trl xml file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $trlXmlPath = $input->getOption('trlXmlPath');
        if (!$trlXmlPath) {
            $trlXmlPath = 'trl.xml';
        }
        $webcodeLanguage = $input->getOption('webcodeLanguage');
        if (!$webcodeLanguage) {
            if (!file_exists('config.ini')) {
                $output->writeln('no config.ini found to get webcodeLanguage');
                return;
            }
            $config = parse_ini_file('config.ini');
            $webcodeLanguage = $config['webCodeLanguage'];
        }

        $targetLanguage = $input->getOption('targetLanguage');
        if (!$targetLanguage) {
            $xmlDocument = simplexml_load_string(file_get_contents($trlXmlPath));
            $targetLanguages = array();
            foreach ($xmlDocument->text as $trl) {
                foreach ($trl as $key => $value) {
                    if ($key == 'id' || $key == 'context') continue;
                    if (strpos($key, '_plural') !== false) continue;
                    $targetLanguages[$key] = true;
                }
            }
            $targetLanguages = array_keys($targetLanguages);
        } else {
            $targetLanguages = array($targetLanguage);
        }

        if (!is_dir('trl')) mkdir('trl');
        foreach ($targetLanguages as $targetLanguage) {
            $this->_convertTrlXmlToPo($trlXmlPath, $webcodeLanguage, $targetLanguage, $output);
        }
    }

    private function _convertTrlXmlToPo($trlXmlPath, $webcodeLanguage, $targetLanguage, $output)
    {
        $trlXmlToPoConverter = new TrlXmlToPoConverter();
        $trlXmlToPoConverter->setXmlPath($trlXmlPath);
        $trlXmlToPoConverter->setBaseLanguage($webcodeLanguage);
        if ($targetLanguage) $trlXmlToPoConverter->setTargetLanguage($targetLanguage);
        $trlXmlToPoConverter->convertToPo($output);
        $output->writeln("$targetLanguage.po-File was written to ".getcwd()."/trl/$targetLanguage.po");
        $trlXmlToPoConverter->writePoContent("trl/$targetLanguage.po");
    }
}
