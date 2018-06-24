<?php

namespace AppBundle\Command;

use AppBundle\Entity\LibCim10;
use AppBundle\Services\CsvToArray;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        // Name and description for app/console command
        $this
            ->setName('import:csv')
            ->setDescription('Import values from CSV file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($output);

        // Showing when the script is over
        $now = new \DateTime();
        $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');
    }

    /**
     * @param OutputInterface $output
     */
    protected function import(OutputInterface $output)
    {
        // Getting php array of data from CSV
        $data = $this->get();

        // Getting doctrine manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        // Turning off doctrine default logs queries for saving memory
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($data);
        $batchSize = 500;
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        // Processing on each row of data
        foreach ($data as $row) {

            $libCim10 = $em->getRepository(LibCim10::class)
                ->findOneByCim10code(trim($row['cim10code']));

            // If the user doest not exist we create one
            if (!is_object($libCim10)) {
                $libCim10 = new LibCim10();
                $libCim10->setCim10code(trim($row['cim10code']));
            }

            // Updating info
            $libCim10->setLibCourt(trim($row['libCourt']));
            $libCim10->setLibLong(trim($row['libLong']));
            $libCim10->setUtile(trim($row['utile']));

            $em->persist($libCim10);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {

                $em->flush();
                // Detaches all objects from Doctrine for memory save
                $em->clear();

                // Advancing for progress display on console
                $progress->advance($batchSize);

                $now = new \DateTime();
                $output->writeln(' of values imported ... | ' . $now->format('d-m-Y G:i:s'));

            }

            $i++;

        }

        // Flushing and clear data on queue
        $em->flush();
        $em->clear();

        // Ending the progress bar process
        $progress->finish();
    }

    /**
     * @return array|bool
     */
    protected function get()
    {
        // Getting the CSV from filesystem
        $fileName = 'web/dev/sources/CIM10 final.csv';

        // Using service for converting CSV to PHP Array
        $converter = new CsvToArray();
        $data = $converter->convert($fileName, ';');

        return $data;
    }

}