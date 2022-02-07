<?php

require_once __DIR__ . '/rpn_lib.php';

$inputExpression = '3 * ((-25 - 10 * -2 ^ 2 / 4) * (4 + 5)) / 2';
$errorDescription = getErrorDescription($inputExpression);
if ($errorDescription) {
    return $errorDescription;
}
$stack = [];
$outString = [];
$postfixString = createPostfixString($inputExpression, $stack, $outString);
$value = calcFromOutString($outString);
