<?php 

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bin/new_refresh.php';

final class RefreshTest extends TestCase
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

        // seed data
        for ($i = 0; $i < 15; $i++)
        {
            $this->db->exec(
                "INSERT INTO feeds (url) VALUES ('https://example.com/feed{$i}.xml')"
            );
        }

    }

    public function test_get_all_feeds(): void 
    {
        $feeds = get_all_feeds($this->db);
        $this->assertCount(15, $feeds);
    }
}