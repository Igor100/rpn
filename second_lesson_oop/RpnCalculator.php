<?php

require_once __DIR__ . '/CalculatorInterface.php';
require_once __DIR__ . '/StackerInterface.php';

class RpnCalculator implements CalculatorInterface
{
    private array $stack = [];

    private array $outString = [];

    public function __construct(private string $expression)
    {
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    public function calculate(): float
    {
        $stack = [];
        foreach ($this->outString as $item) {
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
            $stack[] = $this->calc($left, $right, $item);
        }
        return $stack[0];
    }

    private function calc($left, $right, $operator): float
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

    public function getPostfix(): string
    {
        $stacker = new RpnStacker($this->stack, $this->outString);
        $this->createOutString($this->expression, $stacker);
        return implode(' ', $this->outString);
    }

    private function createOutString(string $expression, StackerInterface $stacker)
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
                    $this->outString[] = (float)$number;
                    $number = null;
                }
                continue;
            }

            if (in_array($item, array_keys(PRIORITY))) {
                if ($item === OPEN_BRACKET && is_numeric($left)) {
                    $stacker->addToAndPushFrom(MULTIPLICATION);
                }
                $stacker->addToAndPushFrom($item);
                if ($item === CLOSE_BRACKET && (is_numeric($right) || $right === OPEN_BRACKET)) {
                    $stacker->addToAndPushFrom(MULTIPLICATION);
                }
            }
        }
        while ($this->stack) {
            $this->outString[] = array_pop($this->stack);
        }
    }
}
