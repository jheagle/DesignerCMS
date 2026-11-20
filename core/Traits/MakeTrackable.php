<?php

namespace Core\Traits;

use Core\Entity\Entity;
use Core\Tracking\Tracking;
use LogicException;

/**
 * Records every change to an Entity's rows in the tracking table: created, "updated: " with the columns changed (names
 * only, never values, which can be personal data), and deleted, by the acting user (Tracking::actingAs()). When a
 * row's key changes, its history moves to the new key. Each row is
 * written in the same transaction as the change, so there is never a change without its record, or a record without
 * its change.
 *
 *     class Order extends OrderBase implements Trackable
 *     {
 *         use MakeTrackable;
 *     }
 *
 * For an Entity with an integer primary key (the tracking table's class_id is an INT).
 *
 * @package Core\Traits
 */
trait MakeTrackable
{
    /**
     * The changes to this row, newest first.
     *
     * @return \Core\Tracking\TrackingRecord[]
     */
    public function history(): array
    {
        return Tracking::history($this->db, static::class, $this->trackedId());
    }

    /**
     * Record the change (Entity calls this after every write).
     *
     * @param string $action
     * @param string[] $columns
     *
     * @return void
     */
    protected function onChange(string $action, array $columns = []): void
    {
        parent::onChange($action, $columns);
        if ($action === Entity::UPDATED && is_int($this->persistedKey) && $this->persistedKey !== $this->id()) {
            // The key changed: the row's earlier history goes with it
            Tracking::moveHistory($this->db, static::class, $this->persistedKey, $this->trackedId());
        }
        $text = $action === Entity::UPDATED && $columns !== [] ? $action . ': ' . implode(', ', $columns) : $action;
        Tracking::record($this->db, $text, static::class, $this->trackedId());
    }

    /**
     * This row's key as the tracking table holds it.
     *
     * @return int
     *
     * @throws LogicException When the key isn't an integer, or the row isn't saved yet.
     */
    private function trackedId(): int
    {
        $id = $this->id();
        if (!is_int($id)) {
            throw new LogicException(static::class . ': tracking needs a saved row with an integer primary key');
        }

        return $id;
    }
}
