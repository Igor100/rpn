<?php

require_once __DIR__ . '/../first_lesson_sp/operator_const.php';
require_once __DIR__ . '/Expression.php';
require_once __DIR__ . '/RpnCalculator.php';

$inputExpression = new Expression('3 * ((-25 - 10 * -2 ^ 2 / 4) * (4 + 5)) / 2');
$rpnCalculator = new RpnCalculator($inputExpression);
if ($rpnCalculator->getPostfix()) {
    echo PHP_EOL;
    echo 'Строка в постфиксной записи (~ - это унарный минус): ' . $rpnCalculator->getPostfix() . PHP_EOL . PHP_EOL;
    echo 'Результат вычисления постфиксной записи: ' . $rpnCalculator->calculate() . PHP_EOL . PHP_EOL;
} else {
    echo $rpnCalculator->calculate() . PHP_EOL;
}
