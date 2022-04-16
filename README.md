# eq-ml

自身の機械学習の勉強のために、PHPで機械学習を行ってみた。

[php-ml](https://php-ml.readthedocs.io/en/latest/)をベースにしたものをPHP8でも動くように手を加えたものを利用している。


直近2週間(14日間)分のデータ(`data/samples.csv`)とその時の事象(`data/samples.csv`)を用いて、モデル(`Neural Network`)を作成している。

そのモデルと直近のデータ(`data/samples.csv`)を用いて予測を行い、結果を`log/YYmmdd.log`に出力する。
