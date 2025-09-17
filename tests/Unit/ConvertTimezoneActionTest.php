<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Unit;

use Brainlet\LaravelConvertTimezone\Actions\ConvertTimezoneAction;
use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Brainlet\LaravelConvertTimezone\Tests\TestCase;
use Carbon\Carbon;

class ConvertTimezoneActionTest extends TestCase
{
    private ConvertTimezoneAction $action;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ConvertTimezoneAction();
    }
    
    public function test_throws_invalid_timezone_exception_for_bad_timezone()
    {
        $this->expectException(InvalidTimezone::class);
        $this->expectExceptionMessage('The timezone `invalid-timezone` is invalid.');
        
        $this->action->execute('2024-01-15 10:00:00', 'invalid-timezone');
    }
    
    public function test_throws_carbon_exception_for_invalid_date_format()
    {
        // This should throw Carbon's exception, not InvalidTimezone
        $this->expectException(\Carbon\Exceptions\InvalidFormatException::class);
        
        $this->action->execute('not-a-valid-date', 'UTC');
    }
    
    public function test_converts_valid_date_with_valid_timezone()
    {
        $result = $this->action->execute('2024-01-15 10:00:00', 'America/New_York');
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('America/New_York', $result->timezone->getName());
    }
    
    public function test_handles_carbon_instance_input()
    {
        $carbon = Carbon::parse('2024-01-15 10:00:00', 'UTC');
        
        $result = $this->action->execute($carbon, 'Asia/Tokyo');
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('Asia/Tokyo', $result->timezone->getName());
    }
    
    public function test_returns_null_for_null_input()
    {
        $result = $this->action->execute(null, 'UTC');
        
        $this->assertNull($result);
    }
}