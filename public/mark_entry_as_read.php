<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entry_guid']))
{
    $stmt = $db->prepare('UPDATE entries SET is_read = 1 WHERE guid = :guid');
    $stmt->bindValue(':guid', $_POST['entry_guid'], SQLITE3_TEXT);
    $stmt->execute();

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

?>

<form method="post" style="margin-block:0.25rem;">
    <input type="hidden" name="entry_guid" value="<?= $entry['guid'] ?>">
    <button type="submit" title="Mark as read" class="btn-text" onclick="return confirm('Mark entry as read?')">
        mark as read
    </button>
</form>