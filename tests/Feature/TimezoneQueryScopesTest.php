<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Feature;

use Brainlet\LaravelConvertTimezone\Tests\Models\Post;
use Brainlet\LaravelConvertTimezone\Tests\TestCase;
use Carbon\Carbon;
class TimezoneQueryScopesTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // Set default timezone for tests
        config(['tz.timezone' => 'America/New_York']);
    }

    public function test_where_in_timezone_filters_correctly()
    {
        // Create posts at different UTC times that translate to different dates in NY timezone
        // UTC 2024-01-01 03:00:00 = NY 2023-12-31 22:00:00 (previous day)
        $post1 = Post::create([
            'title' => 'Post 1',
            'body' => 'Body 1',
            'created_at' => Carbon::parse('2024-01-01 03:00:00', 'UTC'),
        ]);

        // UTC 2024-01-01 06:00:00 = NY 2024-01-01 01:00:00 (same day)
        $post2 = Post::create([
            'title' => 'Post 2',
            'body' => 'Body 2',
            'created_at' => Carbon::parse('2024-01-01 06:00:00', 'UTC'),
        ]);

        // UTC 2024-01-02 03:00:00 = NY 2024-01-01 22:00:00 (same day)
        $post3 = Post::create([
            'title' => 'Post 3',
            'body' => 'Body 3',
            'created_at' => Carbon::parse('2024-01-02 03:00:00', 'UTC'),
        ]);

        // Query for posts created on 2024-01-01 in NY timezone
        $results = Post::whereInTimezone('created_at', '>=', '2024-01-01 00:00:00', 'America/New_York')
            ->whereInTimezone('created_at', '<', '2024-01-02 00:00:00', 'America/New_York')
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($post2));
        $this->assertTrue($results->contains($post3));
        $this->assertFalse($results->contains($post1));
    }

    public function test_where_date_in_timezone_filters_by_date_only()
    {
        // Create posts at different UTC times
        $post1 = Post::create([
            'title' => 'Post 1',
            'body' => 'Body 1',
            'created_at' => Carbon::parse('2024-01-01 03:00:00', 'UTC'), // NY: 2023-12-31
        ]);

        $post2 = Post::create([
            'title' => 'Post 2',
            'body' => 'Body 2',
            'created_at' => Carbon::parse('2024-01-01 08:00:00', 'UTC'), // NY: 2024-01-01
        ]);

        $post3 = Post::create([
            'title' => 'Post 3',
            'body' => 'Body 3',
            'created_at' => Carbon::parse('2024-01-02 03:00:00', 'UTC'), // NY: 2024-01-01
        ]);

        // Query for posts created on 2024-01-01 in NY timezone
        $results = Post::whereDateInTimezone('created_at', '2024-01-01', 'America/New_York')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($post2));
        $this->assertTrue($results->contains($post3));
        $this->assertFalse($results->contains($post1));
    }

    public function test_where_time_in_timezone_filters_by_time_only()
    {
        // Create posts at different times
        $post1 = Post::create([
            'title' => 'Post 1',
            'body' => 'Body 1',
            'created_at' => Carbon::parse('2024-01-01 14:30:00', 'UTC'), // NY: 09:30:00
        ]);

        $post2 = Post::create([
            'title' => 'Post 2',
            'body' => 'Body 2',
            'created_at' => Carbon::parse('2024-01-01 15:00:00', 'UTC'), // NY: 10:00:00
        ]);

        $post3 = Post::create([
            'title' => 'Post 3',
            'body' => 'Body 3',
            'created_at' => Carbon::parse('2024-01-01 16:30:00', 'UTC'), // NY: 11:30:00
        ]);

        // Query for posts created between 09:45 and 10:30 in NY timezone
        $results = Post::whereTimeInTimezone('created_at', '>=', '09:45:00', 'America/New_York')
            ->whereTimeInTimezone('created_at', '<=', '10:30:00', 'America/New_York')
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($post2));
    }

    public function test_scopes_work_with_default_timezone_from_config()
    {
        config(['tz.timezone' => 'Asia/Tokyo']);

        // Create post at UTC time
        $post = Post::create([
            'title' => 'Tokyo Post',
            'body' => 'Body',
            'created_at' => Carbon::parse('2024-01-01 15:00:00', 'UTC'), // Tokyo: 2024-01-02 00:00:00
        ]);

        // Query using default timezone (should use Tokyo)
        $results = Post::whereDateInTimezone('created_at', '2024-01-02')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($post));

        // Verify it doesn't match the UTC date
        $results = Post::whereDateInTimezone('created_at', '2024-01-01')->get();
        $this->assertCount(0, $results);
    }

    public function test_between_dates_in_timezone()
    {
        // Create posts across multiple days
        $post1 = Post::create([
            'title' => 'Post 1',
            'body' => 'Body 1',
            'created_at' => Carbon::parse('2024-01-01 06:00:00', 'UTC'), // NY: 2024-01-01 01:00
        ]);

        $post2 = Post::create([
            'title' => 'Post 2',
            'body' => 'Body 2',
            'created_at' => Carbon::parse('2024-01-02 06:00:00', 'UTC'), // NY: 2024-01-02 01:00
        ]);

        $post3 = Post::create([
            'title' => 'Post 3',
            'body' => 'Body 3',
            'created_at' => Carbon::parse('2024-01-03 06:00:00', 'UTC'), // NY: 2024-01-03 01:00
        ]);

        // Query for posts between Jan 1 and Jan 2 (inclusive) in NY timezone
        $results = Post::whereBetweenInTimezone('created_at', ['2024-01-01', '2024-01-02'], 'America/New_York')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($post1));
        $this->assertTrue($results->contains($post2));
        $this->assertFalse($results->contains($post3));
    }

    public function test_where_month_in_timezone()
    {
        // Create posts in different months when considering timezone
        $post1 = Post::create([
            'title' => 'Post 1',
            'body' => 'Body 1',
            'created_at' => Carbon::parse('2024-01-01 04:00:00', 'UTC'), // NY: 2023-12-31 23:00
        ]);

        $post2 = Post::create([
            'title' => 'Post 2',
            'body' => 'Body 2',
            'created_at' => Carbon::parse('2024-01-15 15:00:00', 'UTC'), // NY: 2024-01-15 10:00
        ]);

        $post3 = Post::create([
            'title' => 'Post 3',
            'body' => 'Body 3',
            'created_at' => Carbon::parse('2024-02-01 06:00:00', 'UTC'), // NY: 2024-02-01 01:00
        ]);

        // Query for posts in January in NY timezone
        $results = Post::whereMonthInTimezone('created_at', 1, 'America/New_York')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($post2));
    }

    public function test_where_year_in_timezone()
    {
        // Create posts that fall in different years when considering timezone
        $post1 = Post::create([
            'title' => 'Post 1',
            'body' => 'Body 1',
            'created_at' => Carbon::parse('2024-01-01 04:00:00', 'UTC'), // NY: 2023-12-31 23:00
        ]);

        $post2 = Post::create([
            'title' => 'Post 2',
            'body' => 'Body 2',
            'created_at' => Carbon::parse('2024-01-01 06:00:00', 'UTC'), // NY: 2024-01-01 01:00
        ]);

        // Query for posts in 2023 in NY timezone
        $results = Post::whereYearInTimezone('created_at', 2023, 'America/New_York')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($post1));

        // Query for posts in 2024 in NY timezone
        $results = Post::whereYearInTimezone('created_at', 2024, 'America/New_York')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($post2));
    }

    public function test_invalid_timezone_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone: Invalid/Timezone');

        Post::whereInTimezone('created_at', '>=', '2024-01-01', 'Invalid/Timezone')->get();
    }

    public function test_scope_with_null_values_handles_gracefully()
    {
        // Create a post with null created_at
        $post = new Post();
        $post->title = 'Post with null date';
        $post->body = 'Body';
        $post->created_at = null;
        $post->save();

        // Should not throw error, just exclude null values
        $results = Post::whereDateInTimezone('created_at', '2024-01-01', 'America/New_York')->get();

        $this->assertCount(0, $results);
    }
}