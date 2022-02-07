<?php

interface StackerInterface
{
    public function addToAndPushFrom(string $operator): void;
}
