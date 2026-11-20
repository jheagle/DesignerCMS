<?php

namespace Core\Tracking;

use Core\Objects\DataTransferObject;

/**
 * One recorded change, as Tracking::history() returns it.
 *
 * @package Core\Tracking
 */
class TrackingRecord extends DataTransferObject
{
    public int $id = 0;

    /**
     * When, as MySQL gives it (Y-m-d H:i:s).
     */
    public string $dateTime = '';

    /**
     * Who: the acting user at the time (Tracking::actingAs()); empty for nobody.
     */
    public string $user = '';

    /**
     * What: created, deleted, or "updated: " and the columns changed.
     */
    public string $action = '';

    /**
     * The changed entity's class.
     */
    public string $actingClass = '';

    /**
     * The changed row's primary key.
     */
    public int $classId = -1;
}
