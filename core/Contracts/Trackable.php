<?php

namespace Core\Contracts;

use Core\Tracking\TrackingRecord;

/**
 * Interface Trackable identifies entities that record every change to their rows (MakeTrackable does it).
 *
 * @package Core\Contracts
 */
interface Trackable
{
    /**
     * The changes to this row, newest first.
     *
     * @return TrackingRecord[]
     */
    public function history(): array;
}
