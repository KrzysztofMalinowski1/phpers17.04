<?php

declare(strict_types=1);

namespace App\Tests\functional\Helpers;

class FileContentHelper
{
    public static function json(string $dirName, string $jsonName): string
    {
        return file_get_contents(__DIR__."/Files/{$dirName}/{$jsonName}.json");
    }
}
