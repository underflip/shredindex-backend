<?php

namespace Underflip\Resorts\Tests\Unit;

use Underflip\Resorts\Models\ResortAttribute;
use Underflip\Resorts\Traits\Filterable;
use Exception;
use PHPUnit\Framework\TestCase;

// Create a mock model class for testing:
class MockResortAttribute extends ResortAttribute
{
    use Filterable;

    public $filterColumn = 'test_value';

    protected $validOperators = ['=', '<', '>', '<=', '>=', '!='];
}

class FilterableTest extends TestCase
{
    public function testGetValidOperators()
    {
        $model = new MockResortAttribute();
        $operators = $model->getValidOperators();
        $this->assertIsArray($operators);
        $this->assertContains('=', $operators);
        $this->assertContains('>', $operators);
        // Add more assertions here based on the operators defined in your trait
    }

    public function testIsValidOperator()
    {
        $model = new MockResortAttribute();

        $this->assertTrue($model->isValidOperator('='));
        $this->assertTrue($model->isValidOperator('>='));
        $this->assertFalse($model->isValidOperator('like'));  // 'like' is not in `$validOperators`
        $this->assertFalse($model->isValidOperator('invalid'));
    }
}
