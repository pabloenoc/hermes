<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use \Hermes\Feed;

final class FeedTest extends TestCase
{
    private SQLite3 $db;

    protected function setUp(): void
    {
        $this->db = new SQLite3(':memory:');

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS feeds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                url TEXT NOT NULL UNIQUE
            )'
        );

        // Seed data with IDs 1...10
        for ($i = 1; $i <= 10; $i++)
        {
            $this->db->exec("INSERT INTO feeds (url) VALUES ('https://example.com/feed{$i}.xml')");
        }

        Feed::set_database($this->db);
    }

    public function test_find_by_id(): void
    {
        $feed = Feed::find_by_id(4);
        $this->assertIsArray($feed);
    }
}