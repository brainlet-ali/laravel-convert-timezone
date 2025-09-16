<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Feature;

use Brainlet\LaravelConvertTimezone\Tests\TestCase;
use Brainlet\LaravelConvertTimezone\TimezoneQueryBuilder;
use Brainlet\LaravelConvertTimezone\Facades\TimezoneQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TimezoneQueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test table without Eloquent
        Schema::create('raw_posts', function ($table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->datetime('created_at');
            $table->datetime('updated_at');
        });

        // Set default timezone for tests
        config(['tz.timezone' => 'America/New_York']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('raw_posts');
        parent::tearDown();
    }

    public function test_where_in_timezone_with_raw_query_builder()
    {
        // Insert test data directly
        DB::table('raw_posts')->insert([
            [
                'title' => 'Post 1',
                'body' => 'Body 1',
                'created_at' => '2024-01-01 03:00:00', // UTC time
                'updated_at' => '2024-01-01 03:00:00',
            ],
            [
                'title' => 'Post 2',
                'body' => 'Body 2',
                'created_at' => '2024-01-01 06:00:00', // UTC time
                'updated_at' => '2024-01-01 06:00:00',
            ],
        ]);

        // Use TimezoneQueryBuilder to filter
        $query = DB::table('raw_posts');
        TimezoneQueryBuilder::whereInTimezone($query, 'created_at', '>=', '2024-01-01 00:00:00', 'America/New_York');
        TimezoneQueryBuilder::whereInTimezone($query, 'created_at', '<', '2024-01-02 00:00:00', 'America/New_York');
        
        $results = $query->get();

        // Should only get Post 2 (06:00 UTC = 01:00 NY, which is on Jan 1)
        // Post 1 (03:00 UTC = Dec 31 22:00 NY) should be excluded
        $this->assertCount(1, $results);
        $this->assertEquals('Post 2', $results->first()->title);
    }

    public function test_where_date_in_timezone_with_raw_query()
    {
        DB::table('raw_posts')->insert([
            [
                'title' => 'Post 1',
                'body' => 'Body 1',
                'created_at' => '2024-01-01 04:00:00', // NY: 2023-12-31
                'updated_at' => '2024-01-01 04:00:00',
            ],
            [
                'title' => 'Post 2',
                'body' => 'Body 2',
                'created_at' => '2024-01-01 08:00:00', // NY: 2024-01-01
                'updated_at' => '2024-01-01 08:00:00',
            ],
        ]);

        $query = DB::table('raw_posts');
        TimezoneQueryBuilder::whereDateInTimezone($query, 'created_at', '2024-01-01', 'America/New_York');
        
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Post 2', $results->first()->title);
    }

    public function test_where_between_in_timezone_with_raw_query()
    {
        DB::table('raw_posts')->insert([
            [
                'title' => 'Post 1',
                'body' => 'Body 1',
                'created_at' => '2024-01-01 06:00:00', // NY: 2024-01-01 01:00
                'updated_at' => '2024-01-01 06:00:00',
            ],
            [
                'title' => 'Post 2',
                'body' => 'Body 2',
                'created_at' => '2024-01-02 06:00:00', // NY: 2024-01-02 01:00
                'updated_at' => '2024-01-02 06:00:00',
            ],
            [
                'title' => 'Post 3',
                'body' => 'Body 3',
                'created_at' => '2024-01-03 06:00:00', // NY: 2024-01-03 01:00
                'updated_at' => '2024-01-03 06:00:00',
            ],
        ]);

        $query = DB::table('raw_posts');
        TimezoneQueryBuilder::whereBetweenInTimezone($query, 'created_at', ['2024-01-01', '2024-01-02'], 'America/New_York');
        
        $results = $query->get();

        $this->assertCount(2, $results);
        $titles = $results->pluck('title')->toArray();
        $this->assertContains('Post 1', $titles);
        $this->assertContains('Post 2', $titles);
        $this->assertNotContains('Post 3', $titles);
    }

    public function test_facade_works_with_raw_queries()
    {
        DB::table('raw_posts')->insert([
            [
                'title' => 'January Post',
                'body' => 'Body',
                'created_at' => '2024-01-15 15:00:00', // NY: 2024-01-15
                'updated_at' => '2024-01-15 15:00:00',
            ],
            [
                'title' => 'February Post',
                'body' => 'Body',
                'created_at' => '2024-02-15 15:00:00', // NY: 2024-02-15
                'updated_at' => '2024-02-15 15:00:00',
            ],
        ]);

        // Use facade
        $query = DB::table('raw_posts');
        TimezoneQuery::whereMonthInTimezone($query, 'created_at', 1, 'America/New_York');
        
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('January Post', $results->first()->title);
    }

    public function test_convert_from_utc_helper()
    {
        $utcTime = '2024-01-01 05:00:00';
        
        $nyTime = TimezoneQueryBuilder::convertFromUTC($utcTime, 'America/New_York');
        $tokyoTime = TimezoneQueryBuilder::convertFromUTC($utcTime, 'Asia/Tokyo');
        
        $this->assertEquals('2024-01-01 00:00:00', $nyTime->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-01-01 14:00:00', $tokyoTime->format('Y-m-d H:i:s'));
        $this->assertEquals('America/New_York', $nyTime->timezone->getName());
        $this->assertEquals('Asia/Tokyo', $tokyoTime->timezone->getName());
    }

    public function test_convert_to_utc_helper()
    {
        $nyTime = '2024-01-01 00:00:00';
        
        $utcTime = TimezoneQueryBuilder::convertToUTC($nyTime, 'America/New_York');
        
        $this->assertEquals('2024-01-01 05:00:00', $utcTime->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $utcTime->timezone->getName());
    }

    public function test_complex_query_with_joins()
    {
        // Create users table
        Schema::create('raw_users', function ($table) {
            $table->id();
            $table->string('name');
            $table->datetime('created_at');
        });

        // Insert test data
        DB::table('raw_users')->insert([
            ['id' => 1, 'name' => 'John', 'created_at' => '2024-01-01 00:00:00'],
            ['id' => 2, 'name' => 'Jane', 'created_at' => '2024-01-01 00:00:00'],
        ]);

        DB::table('raw_posts')->insert([
            [
                'id' => 1,
                'title' => 'John Post',
                'body' => 'Body',
                'created_at' => '2024-01-15 06:00:00', // NY: Jan 15
                'updated_at' => '2024-01-15 06:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Jane Post',
                'body' => 'Body',
                'created_at' => '2024-02-15 06:00:00', // NY: Feb 15
                'updated_at' => '2024-02-15 06:00:00',
            ],
        ]);

        // Add user_id column and update
        Schema::table('raw_posts', function ($table) {
            $table->unsignedBigInteger('user_id')->nullable();
        });
        
        DB::table('raw_posts')->where('id', 1)->update(['user_id' => 1]);
        DB::table('raw_posts')->where('id', 2)->update(['user_id' => 2]);

        // Complex query with join
        $query = DB::table('raw_users')
            ->join('raw_posts', 'raw_users.id', '=', 'raw_posts.user_id')
            ->select('raw_users.name', 'raw_posts.title', 'raw_posts.created_at');

        // Apply timezone filter on joined table
        TimezoneQueryBuilder::whereMonthInTimezone($query, 'raw_posts.created_at', 1, 'America/New_York');
        
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John', $results->first()->name);
        $this->assertEquals('John Post', $results->first()->title);

        // Clean up
        Schema::dropIfExists('raw_users');
    }

    public function test_invalid_timezone_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone: Invalid/Timezone');

        $query = DB::table('raw_posts');
        TimezoneQueryBuilder::whereInTimezone($query, 'created_at', '>=', '2024-01-01', 'Invalid/Timezone');
    }

    public function test_null_values_are_handled_gracefully()
    {
        $nullTime = null;
        
        $result = TimezoneQueryBuilder::convertFromUTC($nullTime, 'America/New_York');
        
        $this->assertNull($result);
    }

    public function test_uses_config_default_when_timezone_not_specified()
    {
        config(['tz.timezone' => 'Asia/Tokyo']);

        DB::table('raw_posts')->insert([
            [
                'title' => 'Tokyo Post',
                'body' => 'Body',
                'created_at' => '2024-01-01 15:00:00', // Tokyo: 2024-01-02
                'updated_at' => '2024-01-01 15:00:00',
            ],
        ]);

        $query = DB::table('raw_posts');
        TimezoneQueryBuilder::whereDateInTimezone($query, 'created_at', '2024-01-02'); // No timezone specified
        
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Tokyo Post', $results->first()->title);
    }
}