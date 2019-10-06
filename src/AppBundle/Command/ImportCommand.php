<?php

namespace AppBundle\Command;

use AppBundle\Entity\CTCAEGrade;
use AppBundle\Entity\CTCAESoc;
use AppBundle\Entity\CTCAETerm;
use AppBundle\Entity\LibCim10;
use AppBundle\Services\CsvToArray;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportCommand extends ContainerAwareCommand
{
    /**
     * @var CsvToArray
     */
    private $csvToArray;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(?string $name = null, CsvToArray $csvToArray, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->csvToArray = $csvToArray;
        $this->container = $container;
    }

    protected function configure()
    {
        // Name and description for app/console command
        $this
            ->setName('import:csv')
            ->addArgument('csv', InputArgument::REQUIRED, "le fichier csv")
            ->addArgument('type', InputArgument::OPTIONAL, "type d'import")
            ->setDescription('Import values from CSV file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($input, $output);

        // Showing when the script is over
        $now = new \DateTime();
        $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function import(InputInterface $input, OutputInterface $output)
    {
        // Getting php array of data from CSV
        $data = $this->get($input->getArgument('csv'));

        // Getting doctrine manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        // Turning off doctrine default logs queries for saving memory
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($data);
        $batchSize = 1;
        $i = 1;

        $type = $input->getArgument('type');

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        if ($type == 'LibCim10') {
            // Processing on each row of data
            foreach ($data as $row) {

                $libCim10 = $em->getRepository(LibCim10::class)
                    ->findOneByCim10code(trim($row['cim10code']));

                // If the u ser doest not exist we create one
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
        }

        if ($type == 'CTCAE') {

            foreach ($data as $row) {
                $soc = trim($row["CTCAE v4.0 SOC"]);
                $CTCAESoc = $em->getRepository(CTCAESoc::class)->findOneBy(["nom" => $soc]);

                if(!$CTCAESoc instanceof CTCAESoc) {
                    $CTCAESoc = new CTCAESoc();
                    $CTCAESoc->setNom($soc);
                    $em->persist($CTCAESoc);
                    $em->flush();
                }

                $termCode = trim($row["MedDRA v12.0 Code"]);
                $term = trim($row["CTCAE v4.0 Term"]);
                $termDefintion = trim($row["CTCAE v4.0 AE Term Definition"]);

                $CTCAETerm = $em->getRepository(CTCAETerm::class)->findOneBy(["code" => $termCode]);

                if(!$CTCAETerm instanceof CTCAETerm) {
                    $CTCAETerm = new CTCAETerm();
                    $CTCAETerm->setCode($termCode);
                    $CTCAETerm->setNom($term);
                    $CTCAETerm->setDefinition($termDefintion);
                    $CTCAETerm->setSoc($CTCAESoc);
                    $em->persist($CTCAETerm);
                    $em->flush();
                }

                $CTCAETerm->setCode($termCode);
                $CTCAETerm->setNom($term);
                $CTCAETerm->setDefinition($termDefintion);
                $CTCAETerm->setSoc($CTCAESoc);

                $grades = [];
                $grades[1] = trim($row["Grade 1"]);
                $grades[2] = trim($row["Grade 2"]);
                $grades[3] = trim($row["Grade 3"]);
                $grades[4] = trim($row["Grade 4"]);
                $grades[5] = trim($row["Grade 5"]);

                foreach ($grades as $key => $grade) {

                    $CTCAEGrade = $em->getRepository(CTCAEGrade::class)->findOneBy(["nom" => $grade, "grade" => $key, "term" => $CTCAETerm]);

                    if($CTCAEGrade) {
                        $CTCAEGrade->setNom($grade);
                        $CTCAEGrade->setGrade($key);
                        $CTCAEGrade->setTerm($CTCAETerm);
                    } else {
                        $CTCAEGrade = new CTCAEGrade();
                        $CTCAEGrade->setNom($grade);
                        $CTCAEGrade->setGrade($key);
                        $CTCAEGrade->setTerm($CTCAETerm);
                        $em->persist($CTCAEGrade);
                    }

                }

                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear();
                    $progress->advance($batchSize);

                    $now = new \DateTime();
                    $output->writeln(' of values imported ... | ' . $now->format('d-m-Y G:i:s'));
                }
                $i++;
            }
        }

        // Flushing and clear data on queue
        $em->flush();
        $em->clear();

        // Ending the progress bar process
        $progress->finish();
    }

    /**
     * @param $file
     * @return array|bool
     */
    protected function get($file)
    {

        // Getting the CSV from filesystem
        $fileName =$this->getContainer()->get("kernel")->getRootDir() . '/../bdd/'.$file;
        $data = $this->csvToArray->convert($fileName, ';');

        return $data;
    }

}