<?php

declare(strict_types=1);
include_once __DIR__ . '/stubs/Validator.php';
class SageGlassValidationTest extends TestCaseSymconValidation
{
    public function testValidateSageGlass(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateSageGlassModule(): void
    {
        $this->validateModule(__DIR__ . '/../SageGlass');
    }
}