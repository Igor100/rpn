<?php

class Expression
{
    public function __construct(private string $expression)
    {
        preg_match('/-?\d+\s+-?\d+/', $expression, $matches);
        if ($matches) {
            throw new DomainException('Между числами нет оператора!');
        }
        $openBracket = substr_count($expression, OPEN_BRACKET);
        $closeBracket = substr_count($expression, CLOSE_BRACKET);
        if ($openBracket !== $closeBracket) {
            throw new DomainException('Непарные скобки!');
        }
        $expression = preg_replace('/\s/', '', $expression);
        $expression = str_replace(',', '.', $expression);
        preg_match('/[^\d()+\/*-.^]+/', $expression, $matches);
        if ($matches) {
            throw new DomainException('Ошибка! В строке могут быть только цифры, скобки, и операторы +, -, *, /, ^');
        }
    }

    public function __toString(): string
    {
        return $this->expression;
    }
}
