<?php

namespace Tests\Unit\Core\Tracking;

use Core\Adaptors\SharedState;
use Core\Contracts\Trackable;
use Core\Database\PhpDbConnect;
use Core\Database\Schema;
use Core\DataTypes\Numbers\Integers\IntDt;
use Core\DataTypes\Strings\VarCharDt;
use Core\Entity\Entity;
use Core\Entity\Field;
use Core\Tracking\Tracking;
use Core\Tracking\TrackingRecord;
use Core\Traits\MakeTrackable;
use InvalidArgumentException;
use LogicException;
use PDOException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\UsesTestDatabase;

/**
 * A trackable entity, as a developer's own model class would be, with a hook of its own next to tracking.
 */
class TrackingTestNote extends Entity implements Trackable
{
    use MakeTrackable;

    public const TABLE = 'tracking_test_notes';

    /** @var string[] */
    public array $hooks = [];

    protected static function fieldList(): array
    {
        return [
            new Field('id', IntDt::class, null, null, Field::PRIMARY_KEY | Field::AUTO_INCREMENT | Field::UNSIGNED | Field::NOT_NULL),
            new Field('title', VarCharDt::class, null, 50, Field::NOT_NULL),
            new Field('body', VarCharDt::class, null, 100, 0),
        ];
    }

    protected function afterCreate(): void
    {
        $this->hooks[] = 'created';
    }
}

/**
 * A trackable entity whose key isn't an integer: the tracking table can't hold it.
 */
class TrackingTestTag extends Entity implements Trackable
{
    use MakeTrackable;

    public const TABLE = 'tracking_test_tags';

    protected static function fieldList(): array
    {
        return [new Field('code', VarCharDt::class, null, 10, Field::PRIMARY_KEY | Field::NOT_NULL)];
    }
}

/**
 * Tracking and MakeTrackable against the MySQL test database, with the tracking table made from
 * resources/designercms.sql itself.
 *
 * @package Tests\Unit\Core\Tracking
 */
#[CoversClass(Tracking::class)]
#[CoversClass(TrackingRecord::class)]
#[CoversTrait(MakeTrackable::class)]
#[Group('Unit')]
#[Group('Database')]
class TrackingTest extends TestCase
{
    use UsesTestDatabase;

    private PhpDbConnect $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = $this->connectTestDatabase();
        $this->db->alter('DROP TABLE IF EXISTS tracking');
        $this->db->alter(self::trackingTableSql());
        $this->db->alter('CREATE TEMPORARY TABLE tracking_test_notes (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(50) NOT NULL,
            body VARCHAR(100) NULL
        )');
        $this->db->alter('CREATE TEMPORARY TABLE tracking_test_tags (code VARCHAR(10) NOT NULL PRIMARY KEY)');
    }

    public function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->alter('DROP TABLE IF EXISTS tracking');
        }
        $this->disconnectTestDatabase();
        parent::tearDown();
    }

    /**
     * The CREATE TABLE for tracking, as resources/designercms.sql has it.
     *
     * @return string
     */
    private static function trackingTableSql(): string
    {
        $sql = file_get_contents(dirname(__DIR__, 4) . '/resources/designercms.sql');
        preg_match('/CREATE TABLE IF NOT EXISTS `tracking` \(.*?\) ENGINE=[^;]*;/s', $sql, $match);

        return $match[0] ?? throw new LogicException('No tracking table in resources/designercms.sql');
    }

    private function note(array $values): TrackingTestNote
    {
        return new TrackingTestNote($this->db, $values);
    }

    /**
     * @param TrackingRecord[] $history
     *
     * @return string[]
     */
    private static function actions(array $history): array
    {
        return array_map(fn(TrackingRecord $record) => $record->action, $history);
    }

    /**
     * Given the tracking table from the SQL file
     * When Schema reads it
     * Then it has the same columns as Tracking declares: names in order, DataTypes, defaults and flags.
     */
    #[Test]
    final public function fieldsMatchTheTableInTheSqlFile(): void
    {
        $describe = fn(Field $field) => [
            get_class($field->getDataType()),
            $field->getDefault(),
            $field->getAttributes(),
            $field->isTypeSupported(),
            $field->isTypeSupported() ? null : $field->getSqlType(),
        ];
        $this->assertSame(
            array_map($describe, (new Schema($this->db))->fields('tracking')),
            array_map($describe, Tracking::fields())
        );
    }

    /**
     * Given a trackable note and an acting user
     * When it is created, changed and deleted
     * Then each change is recorded, newest first, by that user, for that class and id; an update names the columns
     * changed but never their values; and the note's own hook still runs.
     */
    #[Test]
    final public function createUpdateAndDeleteAreRecorded(): void
    {
        Tracking::actingAs('joshua');
        $note = $this->note(['title' => 'Plan', 'body' => 'secret'])->save();
        $note->title = 'Plans';
        $note->body = 'still secret';
        $note->save();
        $note->delete();

        $history = $note->history();
        $this->assertSame(['deleted', 'updated: title, body', 'created'], self::actions($history));
        $created = $history[2];
        $this->assertSame('joshua', $created->user);
        $this->assertSame(TrackingTestNote::class, $created->actingClass);
        $this->assertSame($note->id(), $created->classId);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $created->dateTime);
        $this->assertSame(['created'], $note->hooks, 'a model\'s own afterCreate() still runs');
        foreach ($history as $record) {
            $this->assertStringNotContainsString('secret', $record->action, 'values are never recorded');
        }
    }

    /**
     * Given a saved note
     * When it is saved again without a change
     * Then nothing more is recorded.
     */
    #[Test]
    final public function anUnchangedSaveRecordsNothing(): void
    {
        $note = $this->note(['title' => 'Plan'])->save();
        $note->save();
        $this->assertSame(['created'], self::actions($note->history()));
    }

    /**
     * Given two notes
     * When each changes
     * Then each history holds only its own changes.
     */
    #[Test]
    final public function historyIsPerRow(): void
    {
        $first = $this->note(['title' => 'One'])->save();
        $second = $this->note(['title' => 'Two'])->save();
        $second->body = 'more';
        $second->save();
        $this->assertSame(['created'], self::actions($first->history()));
        $this->assertSame(['updated: body', 'created'], self::actions($second->history()));
    }

    /**
     * Given no acting user, then one, then a reset
     * When changes are recorded
     * Then the user is empty, then the name; and SharedState::reset() forgets it.
     */
    #[Test]
    final public function theActingUserIsRecordedAndReset(): void
    {
        $note = $this->note(['title' => 'Plan'])->save();
        Tracking::actingAs('reviewer');
        $note->title = 'Plans';
        $note->save();
        $this->assertSame(['reviewer', ''], array_map(fn(TrackingRecord $record) => $record->user, $note->history()));
        SharedState::reset();
        $this->assertSame('', Tracking::actingUser());
    }

    /**
     * Given a name longer than the app_user column
     * When it is set as the acting user
     * Then it is refused (it would fail every later write instead).
     */
    #[Test]
    final public function aTooLongUserIsRefused(): void
    {
        Tracking::actingAs(str_repeat('a', Tracking::USER_LENGTH));
        $this->expectException(InvalidArgumentException::class);
        Tracking::actingAs(str_repeat('a', Tracking::USER_LENGTH + 1));
    }

    /**
     * Given the tracking table is gone
     * When a note is created, or a saved one changed
     * Then the write fails and is undone: no change without its record, and the entity is as it was before.
     */
    #[Test]
    final public function aChangeIsUndoneWhenItsRecordCantBeWritten(): void
    {
        $saved = $this->note(['title' => 'Kept'])->save();
        $this->db->alter('DROP TABLE tracking');

        $new = $this->note(['title' => 'Lost']);
        try {
            $new->save();
            $this->fail('the insert should fail without a tracking table');
        } catch (PDOException) {
        }
        $this->assertNull($new->id(), 'still a new entity');
        $this->assertSame([], TrackingTestNote::findWhere($this->db, ['title' => 'Lost']), 'the insert was rolled back');

        $saved->title = 'Changed';
        try {
            $saved->save();
            $this->fail('the update should fail without a tracking table');
        } catch (PDOException) {
        }
        $this->assertSame('Kept', TrackingTestNote::find($this->db, $saved->id())->title, 'the update was rolled back');
        $this->assertSame(['title'], $saved->changedColumns(), 'the entity still knows its change is unsaved');

        $this->db->alter(self::trackingTableSql());
        $saved->save();
        $this->assertSame(['updated: title'], self::actions($saved->history()), 'saved once tracking works again');
    }

    /**
     * Given a trackable entity with a text key
     * When it is saved
     * Then it is refused with a clear error, and nothing is written.
     */
    #[Test]
    final public function aNonIntegerKeyIsRefused(): void
    {
        try {
            (new TrackingTestTag($this->db, ['code' => 'php']))->save();
            $this->fail('a text key should be refused');
        } catch (LogicException $e) {
            $this->assertStringContainsString('integer primary key', $e->getMessage());
        }
        $this->assertNull(TrackingTestTag::find($this->db, 'php'));
    }
}
