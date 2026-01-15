<?php

// CREATE FEED

// $errors = [];

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_feed_url']))
// {
//     $url = trim($_POST['new_feed_url']);
//     // VALIDATIONS
//     // 1. URL cannot be blank
//     // 2. URL must be unique
//     // 3. URL is a valid URL string

//     // INSERT feed INTO feeds 

//     // INSERT entries INTRO entries

// }


// CREATE FEED
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_feed_url']))
{


    $url = trim($_POST['new_feed_url']);

    if ($url === '') {
        $errors[] = 'URL cannot be blank';
    } else if(!filter_var($url, FILTER_VALIDATE_URL)) {
        $errors[] = 'URL is not valid URL.';
    } // TODO: Show error if feed already exists (check by URL)

    if (empty($errors))
    {
    // Prep with Feed.php
        try {
            $feed = Feed::load($url);
            $feed_title = $feed->title;
            $feed_format = $feed->item ? 'rss' : 'atom';

            $stmt = $db->prepare('INSERT OR IGNORE INTO feeds (url, title, format) VALUES (:url, :title, :format)');
            $stmt->bindValue(':url', $url, SQLITE3_TEXT);
            $stmt->bindValue(':title', $feed_title, SQLITE3_TEXT);
            $stmt->bindValue(':format', $feed_format, SQLITE3_TEXT);
            $stmt->execute();

        // Save feed entries to db...
            $feed_id = $db->lastInsertRowID();

        // Atom format first
            if ($feed_format === 'atom') {
                foreach($feed->entry as $entry) {
                    $title = $entry->title;
            $published_date = (int)$entry->timestamp; // TODO: fix if empty
            $guid = $entry->id;
            $url = $entry->link['href'];

            $stmt = $db->prepare('INSERT OR IGNORE INTO entries (feed_id, title, published_date, guid, url) VALUES (:feed_id, :title, :published_date, :guid, :url)');
            $stmt->bindValue(':feed_id', $feed_id, SQLITE3_INTEGER);
            $stmt->bindValue(':title', $title, SQLITE3_TEXT);
            $stmt->bindValue(':published_date', $published_date, SQLITE3_INTEGER);
            $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);
            $stmt->bindValue(':url', $url, SQLITE3_TEXT);
            $stmt->execute();
        }
    } elseif ($feed_format === 'rss') {
        foreach($feed->item as $item) {
            $title = $item->title;
            $published_date = strtotime($item->pubDate);
            $guid = $item->guid;
            $url = $item->link;

            $stmt = $db->prepare('INSERT OR IGNORE INTO entries (feed_id, title, published_date, guid, url) VALUES (:feed_id, :title, :published_date, :guid, :url)');
            $stmt->bindValue(':feed_id', $feed_id, SQLITE3_INTEGER);
            $stmt->bindValue(':title', $title, SQLITE3_TEXT);
            $stmt->bindValue(':published_date', $published_date, SQLITE3_INTEGER);
            $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);
            $stmt->bindValue(':url', $url, SQLITE3_TEXT);
            $stmt->execute();
        }
        
    }



    header('Location: ' . $_SERVER['REQUEST_URI']);     
    exit;

} catch (Throwable $e) {
    $errors[] = 'URL is not a valid RSS or Atom feed.';
        // log error?
}


}

}


?>

<!-- Display form errors -->
    <?php if (!empty($errors)): ?>
        <div class="" style="color: var(--color-error); padding-left: 1rem;">
            <p>This feed could not be added to your library.</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= $error ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="flex" style="padding: 1rem; gap: 0.5rem;">
        <input
        type="url"
        name="new_feed_url"
        placeholder="https://example.com/feed.xml"
        style="flex: 1;"
        required
        aria-invalid="<?= empty($errors) ? 'false' : 'true' ?>"
        value="<?= empty($errors)? '' : $_POST['new_feed_url'] ?>"
        >
        <button type="submit" class="btn-submit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
            </svg>
        </button>
    </form>