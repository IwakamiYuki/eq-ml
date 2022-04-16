<?php

use App\MachineLearning\DataSet;
use App\MachineLearning\NeuralNetwork;
use App\MachineLearning\Preprocessor;

require_once __DIR__ . '/vendor/autoload.php';

// 何日後の予測をするか(基本的に1日後以降なのでデフォルト値に1を設定)
$days_later = $argv[1] ?? 1;

$sample_start_on = new DateTime('2022-03-21');
// 実行タイミングの前日までを学習対象としたいが、GitHub Actionsの実行時間が日付が変わった直後の夜中を想定しているので+1dayしておく
$sample_end_on = (new DateTime())->modify('-1 day')->modify('+1 day');

//echo 'train_start_on: ' . $sample_start_on->format('Y-m-d') . PHP_EOL;
//echo 'train_end_on: '. $sample_end_on->format('Y-m-d') . PHP_EOL;
$train_target_on = (clone $sample_end_on)->modify( '+' . $days_later . ' day' );
//echo 'predict: target_on: ' . $train_target_on->format('Y-m-d') . PHP_EOL;

// prepare data
$preprocessor = new Preprocessor($sample_start_on, $sample_end_on);
$data_set = $preprocessor->execute();

$pos_count = 0;
$loop_count = 100;
for ($i = 0; $i < $loop_count; $i++) {
    // train
    $neural_network = new NeuralNetwork($data_set, $days_later);
    $neural_network->train(
        $sample_start_on,
        $sample_end_on,
    );

    // predict test
    $result = $neural_network->predict($sample_end_on);
    echo $train_target_on->format('Y-m-d') . ': ' . $result . PHP_EOL;
    if ($result === '1') {
        $pos_count++;
    }
}
echo '::set-output name=result::'
    . $train_target_on->format('Y-m-d')
    . ','.
    $pos_count/$loop_count . PHP_EOL;
