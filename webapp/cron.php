<?php

/**
 * Finds all "offered" lifespans that are no longer valid (the valid until date has elapsed) and
 * declines them.
 */
Database::instance()->exec(
    'UPDATE lifespans SET status = "declined" WHERE status != "accepted" AND valid_until < :current_time',
    array(
        ':current_time' => time()
    )
);

/**
 * Next, we want to find all ongoing disputes whose lifespan has just expired.
 * The dispute should close unsuccessfully.
 */
$expiredLifespans = Database::instance()->exec(
    'SELECT * FROM lifespans
    INNER JOIN disputes ON disputes.dispute_id = lifespans.dispute_id
    WHERE lifespans.status = "accepted"
    AND end_time < :current_time
    AND disputes.status = "ongoing"',
    array(
        ':current_time' => time()
    )
);

foreach($expiredLifespans as $data) {
    $dispute = DBGet::instance()->dispute((int) $data['dispute_id']);
    $dispute->closeUnsuccessfully();
}
