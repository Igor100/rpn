<?php

class RpnStacker implements StackerInterface
{
    public function getOutString(): array
    {
        return $this->outString;
    }

    public function getStack(): array
    {
        return $this->stack;
    }

    public function __construct(private array $stack = [], private array $outString = [])
    {
    }

    public function addToAndPushFrom(string $operator): void
    {
        if (!$this->stack || $operator === OPEN_BRACKET) {
            $this->stack[] = $operator;
            return;
        }
        $stack = array_reverse($this->stack);
        if ($operator === CLOSE_BRACKET) {
            foreach ($stack as $key => $item) {
                unset($stack[$key]);
                if ($item === OPEN_BRACKET) {
                    $this->stack = array_reverse($stack);
                    return;
                }
                $this->outString[] = $item;
            }
        }
        foreach ($stack as $key => $item) {
            if (in_array($item, RIGHT_ASSOCIATIVE_EXPRESSION) && $item === $operator) {
                break;
            }
            if (PRIORITY[$item] < PRIORITY[$operator]) {
                break;
            }
            $this->outString[] = $item;
            unset($stack[$key]);
        }
        $this->stack = array_reverse($stack);
        $this->stack[] = $operator;
    }
}