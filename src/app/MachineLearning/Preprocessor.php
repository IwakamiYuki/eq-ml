<?php

namespace App\MachineLearning;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;

class Preprocessor
{
    protected array $samples_key_by_date = [];
    protected array $targets_key_by_date = [];

    public function __construct(
        protected DateTime $start_on,
        protected DateTime $end_on = new DateTime(),
    )
    {
    }

    /**
     * @return DataSet
     * @throws Exception
     */
    public function execute(): DataSet
    {
        $this->initialize();

        $this->loadSamples();
        $this->loadTargets();

        return new DataSet(
            $this->samples_key_by_date,
            $this->targets_key_by_date
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function initialize(): void
    {
        $this->samples_key_by_date = [];
        $this->targets_key_by_date = [];
        $interval = new DateInterval('P1D');
        $date_range = new DatePeriod($this->start_on, $interval ,$this->end_on);
        /** @var DateTime $target_date */
        foreach($date_range as $target_date){
            $this->samples_key_by_date[DataSet::getKey($target_date)] = 0;
            $this->targets_key_by_date[DataSet::getKey($target_date)] = '0';
        }
    }

    /**
     * @throws Exception
     */
    protected function loadSamples(): void
    {
        $filepath = __DIR__ . '/../../../data/samples.csv';
        $fp = fopen($filepath, 'r');
        while (($data = fgetcsv($fp)) !== false) {
            $this->samples_key_by_date[DataSet::getKey($data[0])] = (int)$data[1];
        }
        fclose($fp);
    }
    /**
     * @throws Exception
     */
    protected function loadTargets(): void
    {
        $filepath = __DIR__ . '/../../../data/targets.csv';
        $fp = fopen($filepath, 'r');
        while (($data = fgetcsv($fp)) !== false) {
            $this->targets_key_by_date[DataSet::getKey($data[0])] = (string)$data[1];
        }
        fclose($fp);
    }
}