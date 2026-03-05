<?php

namespace Arnautdev\FilamentDeployIndicator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Arnautdev\FilamentDeployIndicator\FilamentDeployIndicator
 */
class FilamentDeployIndicator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Arnautdev\FilamentDeployIndicator\FilamentDeployIndicator::class;
    }
}
