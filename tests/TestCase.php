<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $basePath = dirname(__DIR__);
        putenv("APP_BASE_PATH={$basePath}");
        $_ENV['APP_BASE_PATH'] = $basePath;
        $_SERVER['APP_BASE_PATH'] = $basePath;

        return parent::createApplication();
    }
}
