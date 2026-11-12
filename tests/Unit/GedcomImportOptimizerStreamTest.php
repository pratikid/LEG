<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GedcomImportOptimizer;
use Exception;
use ReflectionClass;
use Tests\TestCase;

final class GedcomImportOptimizerStreamTest extends TestCase
{
    public function test_it_accepts_a_valid_stream_with_a_utf8_bom(): void
    {
        $optimizer = (new ReflectionClass(GedcomImportOptimizer::class))->newInstanceWithoutConstructor();
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, "\xEF\xBB\xBF0 HEAD\r\n0 TRLR\r\n");
        rewind($stream);

        try {
            $result = $optimizer->importGedcomStream($stream, 1);
        } finally {
            fclose($stream);
        }

        $this->assertTrue($result['success']);
        $this->assertSame([
            'individuals' => 0,
            'families' => 0,
            'sources' => 0,
            'notes' => 0,
            'media' => 0,
        ], $result['results']);
    }

    public function test_the_string_import_api_remains_compatible(): void
    {
        $optimizer = (new ReflectionClass(GedcomImportOptimizer::class))->newInstanceWithoutConstructor();

        $result = $optimizer->importGedcomData("0 HEAD\n0 TRLR\n", 1);

        $this->assertTrue($result['success']);
    }

    public function test_it_rejects_malformed_streams_before_importing_records(): void
    {
        $optimizer = (new ReflectionClass(GedcomImportOptimizer::class))->newInstanceWithoutConstructor();
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, "0 HEAD\n0 @I1@ INDI\n1 NAME Should /Not Import/\n");
        rewind($stream);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid GEDCOM format: Missing HEAD or TRLR');

        try {
            $optimizer->importGedcomStream($stream, 1);
        } finally {
            fclose($stream);
        }
    }
}
