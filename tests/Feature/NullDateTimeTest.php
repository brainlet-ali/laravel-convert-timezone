<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Feature;

use Brainlet\LaravelConvertTimezone\Tests\Models\TestModel;
use Brainlet\LaravelConvertTimezone\Tests\TestCase;
use Carbon\Carbon;

class NullDateTimeTest extends TestCase
{
    public function test_handles_null_datetime_fields_gracefully()
    {
        config(['tz.timezone' => 'Asia/Karachi']);
        
        // Create model with some null datetime fields
        $model = TestModel::factory()->create([
            'created_at' => now(),
            'updated_at' => null,  // Null datetime field
        ]);

        // Retrieve the model
        $retrieved = TestModel::find($model->id);
        
        // Assert created_at is converted
        $this->assertInstanceOf(Carbon::class, $retrieved->created_at);
        $this->assertEquals('Asia/Karachi', $retrieved->created_at->timezone->getName());
        
        // Assert updated_at remains null and doesn't throw exception
        $this->assertNull($retrieved->updated_at);
    }
    
    public function test_toArray_handles_null_datetime_fields()
    {
        config(['tz.timezone' => 'America/New_York']);
        
        $model = TestModel::factory()->create([
            'created_at' => '2024-01-15 10:00:00',
            'updated_at' => null,
        ]);
        
        $retrieved = TestModel::find($model->id);
        $array = $retrieved->toArray();
        
        // Assert created_at is converted and formatted
        $this->assertIsString($array['created_at']);
        $this->assertStringContainsString('T', $array['created_at']); // ISO8601 format
        
        // Assert updated_at remains null in array
        $this->assertNull($array['updated_at']);
    }
    
    public function test_model_with_all_null_datetime_fields()
    {
        config(['tz.timezone' => 'Europe/London']);
        
        // Create model with all datetime fields as null
        $model = TestModel::factory()->create([
            'created_at' => null,
            'updated_at' => null,
        ]);
        
        $retrieved = TestModel::find($model->id);
        
        // Assert no exceptions are thrown and nulls are preserved
        $this->assertNull($retrieved->created_at);
        $this->assertNull($retrieved->updated_at);
        
        // Also test toArray
        $array = $retrieved->toArray();
        $this->assertNull($array['created_at']);
        $this->assertNull($array['updated_at']);
    }
    
    public function test_mixed_null_and_valid_datetime_conversion()
    {
        config(['tz.timezone' => 'Australia/Sydney']);
        
        // Create multiple models with mixed null/non-null datetimes
        $model1 = TestModel::factory()->create([
            'created_at' => '2024-01-15 10:00:00',
            'updated_at' => null,
        ]);
        
        $model2 = TestModel::factory()->create([
            'created_at' => null,
            'updated_at' => '2024-01-15 10:00:00',
        ]);
        
        $models = TestModel::whereIn('id', [$model1->id, $model2->id])->get();
        
        // First model: created_at converted, updated_at null
        $this->assertInstanceOf(Carbon::class, $models[0]->created_at);
        $this->assertNull($models[0]->updated_at);
        
        // Second model: created_at null, updated_at converted
        $this->assertNull($models[1]->created_at);
        $this->assertInstanceOf(Carbon::class, $models[1]->updated_at);
        
        // Test collection toArray
        $array = $models->toArray();
        $this->assertIsString($array[0]['created_at']);
        $this->assertNull($array[0]['updated_at']);
        $this->assertNull($array[1]['created_at']);
        $this->assertIsString($array[1]['updated_at']);
    }
    
    public function test_action_returns_null_for_null_input()
    {
        $action = new \Brainlet\LaravelConvertTimezone\Actions\ConvertTimezoneAction();
        
        $result = $action->execute(null, 'Asia/Tokyo');
        
        $this->assertNull($result);
    }
}