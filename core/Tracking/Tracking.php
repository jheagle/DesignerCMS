<?php

namespace Core\Tracking;

use Core\Database\DbConnect;
use Core\DataTypes\Numbers\Integers\IntDt;
use Core\DataTypes\Strings\StringDt;
use Core\DataTypes\Strings\VarCharDt;
use Core\Entity\Entity;
use Core\Entity\Field;
use InvalidArgumentException;

/**
 * One row of the `tracking` table (resources/designercms.sql): a change to a record, who made it and when. Trackable
 * entities write these themselves (MakeTrackable); read them back with history().
 *
 * @package Core\Tracking
 */
class Tracking extends Entity
{
    public const TABLE = 'tracking';

    /**
     * The app_user column's size.
     */
    public const USER_LENGTH = 65;

    /**
     * Who changes are recorded as, until actingAs() says otherwise.
     */
    private static string $actingUser = '';

    /**
     * The columns, as the tracking table in resources/designercms.sql has them (TrackingTest checks they match).
     *
     * @return Field[]
     */
    protected static function fieldList(): array
    {
        return [
            new Field('id', IntDt::class, null, null, Field::PRIMARY_KEY | Field::AUTO_INCREMENT | Field::NOT_NULL),
            new Field('date_time', StringDt::class, null, null, Field::NOT_NULL | Field::INDEX, 'datetime', false),
            new Field('app_user', VarCharDt::class, '', self::USER_LENGTH, Field::NOT_NULL),
            new Field('action', StringDt::class, null, null, Field::NOT_NULL, 'text', false),
            new Field('acting_class', VarCharDt::class, 'tracking', 100, Field::NOT_NULL | Field::INDEX),
            new Field('class_id', IntDt::class, -1, null, Field::NOT_NULL | Field::INDEX),
        ];
    }

    /**
     * Record changes from now on as made by $user (the signed-in user's name, a job's name...). Empty for nobody.
     *
     * @param string $user At most USER_LENGTH characters.
     *
     * @return void
     *
     * @throws InvalidArgumentException When the name is too long for the column.
     */
    public static function actingAs(string $user): void
    {
        if (mb_strlen($user) > self::USER_LENGTH) {
            throw new InvalidArgumentException('A tracking user name is at most ' . self::USER_LENGTH . ' characters');
        }
        self::$actingUser = $user;
    }

    /**
     * Who changes are being recorded as.
     *
     * @return string
     */
    public static function actingUser(): string
    {
        return self::$actingUser;
    }

    /**
     * Forget the acting user (part of SharedState::reset()).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$actingUser = '';
    }

    /**
     * Write one change: $action (what happened) to row $id of $class, by the acting user.
     *
     * @param DbConnect $db
     * @param string $action
     * @param string $class The changed entity's class.
     * @param int $id The changed row's primary key.
     *
     * @return static The saved row.
     */
    public static function record(DbConnect $db, string $action, string $class, int $id): static
    {
        return (new static($db, [
            'app_user' => self::$actingUser,
            'action' => $action,
            'acting_class' => $class,
            'class_id' => $id,
        ]))->save();
    }

    /**
     * The changes to row $id of $class, newest first.
     *
     * @param DbConnect $db
     * @param string $class
     * @param int $id
     *
     * @return TrackingRecord[]
     */
    public static function history(DbConnect $db, string $class, int $id): array
    {
        return array_map(
            fn(Tracking $row) => $row->toRecord(),
            static::findWhere($db, ['acting_class' => $class, 'class_id' => $id], 'id', true)
        );
    }

    /**
     * This row as a plain record.
     *
     * @return TrackingRecord
     */
    public function toRecord(): TrackingRecord
    {
        return TrackingRecord::fromArray([
            'id' => $this->get('id'),
            'dateTime' => $this->get('date_time'),
            'user' => $this->get('app_user'),
            'action' => $this->get('action'),
            'actingClass' => $this->get('acting_class'),
            'classId' => $this->get('class_id'),
        ]);
    }
}
