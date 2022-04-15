<?php

namespace App\MachineLearning;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Phpml\Classification\MLPClassifier;
use Phpml\Exception\InvalidArgumentException;
use Phpml\Preprocessing\Normalizer;

class NeuralNetwork
{
    /**
     * @var int 過去2週間分の傾向を見る
     */
    const LEARNING_PERIOD = 14;

    protected MLPClassifier $mlp;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected DataSet $data_set,
        protected int $predict_day_offset = 0,
    )
    {
        $this->mlp = new MLPClassifier(4, [2], ['0', '1']);
    }

    /**
     * @param DateTime $start_on
     * @param DateTime $end_on
     * @return void
     * @throws Exception
     */
    public function train(
        DateTime $start_on,
        DateTime $end_on = new DateTime(),
    )
    {
        $start_on = (clone $start_on)->modify('+' . self::LEARNING_PERIOD . ' day');
        $end_on = (clone $end_on)->modify('-' . ($this->predict_day_offset) . ' day');
//        echo 'train()' . PHP_EOL;
//        echo 'start_on: ' . $start_on->format('Y-m-d') . PHP_EOL;
//        echo 'end_on: ' . $end_on->format('Y-m-d') . PHP_EOL;

        $interval = new DateInterval('P1D');
        $train_date_range = new DatePeriod($start_on, $interval ,$end_on);

        $samples_pos = [];
        $samples_neg = [];

        foreach($train_date_range as $date) {
//            echo 'date: ' . $date->format('Y-m-d') . PHP_EOL;
            $sample = $this->createSample($date);
//            echo 'target date: ' . (clone $date)->modify('+' . $this->predict_day_offset . ' day')->format('Y-m-d') . PHP_EOL;
            $target = $this->data_set->getTargetByDate((clone $date)->modify('+' . $this->predict_day_offset . ' day'));
//            echo implode(', ', $sample) . ' => ' . $target . PHP_EOL;
            if ($target === '1') {
                $samples_pos[] = $sample;
            } else {
                $samples_neg[] = $sample;
            }
        }

        shuffle($samples_pos);
        shuffle($samples_neg);
        $samples = [];
        $targets = [];
        foreach($samples_pos as $key => $sample){
            if (!isset($samples_neg[$key])){
                break;
            }
            $samples[] = $samples_neg[$key];
            $targets[] = '0';
            $samples[] = $sample; // = $samples_pos[$key]
            $targets[] = '1';
        }

        $normalizer = new Normalizer();
        $normalizer->transform($samples);
        $this->mlp->train(
            $samples,
            $targets,
        );
    }

    /**
     * @param DateTime $target_on
     * @return string
     * @throws Exception
     */
    public function predict(
        DateTime $target_on,
    ): string {
        $samples = [$this->createSample($target_on)];
        $normalizer = new Normalizer();
        $normalizer->transform($samples);
        return $this->mlp->predict($samples)[0];
    }

    /**
     * @param DateTime $target_date
     * @return array
     * @throws Exception
     */
    protected function createSample(
        DateTime $target_date,
    ): array {
        // 前日から14日前までを対象とする
        $sample_end_on = (clone $target_date)->modify( '-' . ($this->predict_day_offset - 2) . ' day' );
        $sample_start_on = (clone $sample_end_on)->modify('-' . self::LEARNING_PERIOD . ' day');

        $interval = new DateInterval('P1D');
        $sample_date_range = new DatePeriod($sample_start_on, $interval ,$sample_end_on);
        $sample = [];
        foreach($sample_date_range as $target_sample_date) {
            $sample[] = $this->data_set->getSampleByDate($target_sample_date);
        }
        if (count($sample) !== self::LEARNING_PERIOD) {
            throw new Exception('学習データが不足しています');
        }

        return $sample;
    }
}