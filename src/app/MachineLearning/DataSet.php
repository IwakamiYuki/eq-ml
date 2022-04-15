<?php

namespace App\MachineLearning;

use DateTime;
use Exception;

class DataSet
{
    /**
     * @param array $samples_key_by_date
     * @param array $targets_key_by_date
     */
    public function __construct(
        protected array $samples_key_by_date,
        protected array $targets_key_by_date,
    )
    {
    }

    /**
     * @param DateTime|string $date
     * @return int
     * @throws Exception
     */
    public function getSampleByDate(DateTime|string $date): int
    {
        return $this->samples_key_by_date[self::getKey($date)] ?? 0;
    }

    /**
     * @param DateTime|string $date
     * @return string
     * @throws Exception
     */
    public function getTargetByDate(DateTime|string $date): string
    {
        return $this->targets_key_by_date[self::getKey($date)];
    }

    /**
     * @param DateTime|string $datetime
     * @return string
     * @throws Exception
     */
    public static function getKey(DateTime|string $datetime): string
    {
        if (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }
        return $datetime->format('Y-m-d');
    }
}