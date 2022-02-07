<?php

const UNARY_MINUS = '~';
const OPEN_BRACKET = '(';
const CLOSE_BRACKET = ')';
const MINUS = '-';
const PLUS = '+';
const DIVISION = '/';
const MULTIPLICATION = '*';
const EXPONENTIATION = '^';

const PRIORITY = [
    OPEN_BRACKET => 0,
    CLOSE_BRACKET => null,
    PLUS => 2,
    MINUS => 2,
    MULTIPLICATION => 3,
    DIVISION => 3,
    EXPONENTIATION => 4,
    UNARY_MINUS => 5
];

const RIGHT_ASSOCIATIVE_EXPRESSION = [
    EXPONENTIATION, UNARY_MINUS
];

function getErrorDescription(string $expression): string
{
    preg_match('/-?\d+\s+-?\d+/', $expression, $matches);
    if ($matches) {
        return 'Между числами нет оператора!';
    }
    $openBracket = substr_count($expression, OPEN_BRACKET);
    $closeBracket = substr_count($expression, CLOSE_BRACKET);
    if ($openBracket !== $closeBracket) {
        return 'Непарные скобки!';
    }
    $expression = preg_replace('/\s/', '', $expression);
    $expression = str_replace(',', '.', $expression);
    preg_match('/[^\d()+\/*-.^]+/', $expression, $matches);
    if ($matches) {
        return 'Ошибка! В строке могут быть только цифры, скобки, и операторы +, -, *, /, ^';
    }
    return '';
}

function createPostfixString(string $expression, array &$stack, array &$outString): string
{
    $length = strlen($expression) - 1;
    $number = null;
    for ($i = 0; $i <= $length; $i++) {
        $item = $expression[$i];
        $left = $i === 0 ? null : $expression[$i - 1];
        $right = $i === $length ? null : $expression[$i + 1];
        if ($item === '-') {
            $arr = [PLUS, MULTIPLICATION, EXPONENTIATION, MINUS, DIVISION, OPEN_BRACKET];
            if ($left === null || in_array($left, $arr)) {
                $item = UNARY_MINUS;
            }
        }
        if (is_numeric($item) || $item === '.') {
            if ($item === '.') {
                if ($left === null || $right === null || !is_numeric($left) || !is_numeric($right)) {
                    throw new DomainException('Неверное дробное выражение!');
                }
            }
            $number .= $item;
            if (!is_numeric($right)) {
                $outString[] = (float)$number;
                $number = null;
            }
            continue;
        }

        if (in_array($item, array_keys(PRIORITY))) {
            if ($item === OPEN_BRACKET && is_numeric($left)) {
                addToStackAndPushFromStack(MULTIPLICATION, $stack, $outString);
            }
            addToStackAndPushFromStack($item, $stack, $outString);
            if ($item === CLOSE_BRACKET && (is_numeric($right) || $right === OPEN_BRACKET)) {
                addToStackAndPushFromStack(MULTIPLICATION, $stack, $outString);
            }
        }
    }
    while ($stack) {
        $outString[] = array_pop($stack);
    }
    return implode(' ', $outString);
}


function addToStackAndPushFromStack(string $operator, array &$stack, array &$outString): void
{
    if (!$stack || $operator === OPEN_BRACKET) {
        $stack[] = $operator;
        return;
    }
    $stackLocal = array_reverse($stack);
    if ($operator === CLOSE_BRACKET) {
        foreach ($stackLocal as $key => $item) {
            unset($stackLocal[$key]);
            if ($item === OPEN_BRACKET) {
                $stack = array_reverse($stackLocal);
                return;
            }
            $outString[] = $item;
        }
    }
    foreach ($stackLocal as $key => $item) {
        if (in_array($item, RIGHT_ASSOCIATIVE_EXPRESSION) && $item === $operator) {
            break;
        }
        if (PRIORITY[$item] < PRIORITY[$operator]) {
            break;
        }
        $outString[] = $item;
        unset($stackLocal[$key]);
    }
    $stack = array_reverse($stackLocal);
    //$stack[] = $operator;
    array_push($stack, $operator);
}

function calcFromOutString(array $outString): float
{
    $stack = [];
    foreach ($outString as $item) {
        if (is_float($item)) {
            $stack[] = $item;
            continue;
        }
        if ($item === UNARY_MINUS) {
            $last = array_pop($stack);
            if (!is_numeric($last)) {
                throw new DomainException('Неверное выражение!');
            }
            $stack[] = 0 - $last;
            continue;
        }
        $right = array_pop($stack) ?? null;
        $left = array_pop($stack) ?? null;
        if ($right === null || $left === null) {
            throw new DomainException('Неверное выражение!');
        }
        $stack[] = calculate($left, $right, $item);
    }
    return $stack[0];
}

function calculate($left, $right, $operator)
{
    switch ($operator) {
        case MINUS:
            return $left - $right;
        case PLUS:
            return $left + $right;
        case MULTIPLICATION:
            return $left * $right;
        case EXPONENTIATION:
            return $left ** $right;
        case DIVISION:
            if ($right == 0) {
                throw new DomainException('Деление на ноль!');
            }
            return $left / $right;
        default:
            throw new DomainException('Неизвестный оператор ' . $operator);
    }
}
