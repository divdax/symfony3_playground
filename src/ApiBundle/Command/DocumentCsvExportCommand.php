<?php

namespace ApiBundle\Command;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DocumentCsvExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:document-csv-export')
            ->setDescription('Export documents as CSV')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit Response (default: 500, max: 1000)')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Document Type e.g. INVOICE,OFFER')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'e.g. /home/easybill/documents.csv')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start Document Export...');

        $dotenv = new Dotenv();
        $dotenv->load($this->getContainer()->getParameter('kernel.root_dir').'/../.env');

        // Command Options
        $limit = $input->getOption('limit');
        $type = $input->getOption('type');
        $path = $input->getOption('path') ?: __DIR__ . '/documents.csv';

        // Get Documents
        $documents = $this->getContainer()->get('api.document');
        $documents->setLimit($limit);
        $documents->setType($type);
        $documents = $documents->get();
        
        // Export Documents to CSV
        $export = $this->getContainer()->get('document.csv.export');
        $export->setDocuments($documents);
        $export->saveCsv($path);

        $output->writeln('<info>✔ CSV Export completed. Saved ' . count($documents) . ' Documents</info>');
        $output->writeln('File: <comment>' . $path . '</comment>');
    }
}
