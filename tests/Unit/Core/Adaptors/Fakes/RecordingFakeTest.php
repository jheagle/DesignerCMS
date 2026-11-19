<?php

namespace Tests\Unit\Core\Adaptors\Fakes;

use Core\Adaptors\Fakes\RecordingFake;
use Core\Adaptors\Vendor\Curl\Client;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The fake any adaptor can swap in with fake(): it records calls, answers as told, and checks what happened.
 *
 * @package Tests\Unit\Core\Adaptors\Fakes
 */
#[CoversClass(RecordingFake::class)]
#[Group('Unit')]
#[Group('Adaptor')]
class RecordingFakeTest extends TestCase
{
    /**
     * Given a fake told what to return
     * When methods are called
     * Then each call is recorded, and answered with the value, the callable's result, or null.
     */
    #[Test]
    final public function recordsAndAnswersCalls(): void
    {
        $fake = new RecordingFake(['status' => 200, 'double' => fn(int $n) => $n * 2]);
        $this->assertSame(200, $fake->status());
        $this->assertSame(8, $fake->double(4));
        $this->assertNull($fake->anythingElse('x'));
        $this->assertSame(
            [
                ['method' => 'status', 'arguments' => []],
                ['method' => 'double', 'arguments' => [4]],
                ['method' => 'anythingElse', 'arguments' => ['x']],
            ],
            $fake->calls()
        );
        $this->assertCount(1, $fake->calls('double', fn(int $n) => $n === 4));
    }

    /**
     * Given some calls
     * When checking them
     * Then passing checks pass, counting and filtering as asked.
     */
    #[Test]
    final public function checksPass(): void
    {
        $fake = new RecordingFake();
        $fake->send('a');
        $fake->send('b');
        $fake->assertCalled('send')
            ->assertCalled('send', times: 2)
            ->assertCalled('send', fn(string $body) => $body === 'b', 1)
            ->assertNotCalled('delete')
            ->assertNotCalled('send', fn(string $body) => $body === 'c');
        (new RecordingFake())->assertNothingCalled();
    }

    /**
     * Given a check that doesn't hold
     * When it runs
     * Then it fails as a PHPUnit assertion, naming what was called.
     */
    #[Test]
    final public function failedCheckIsATestFailure(): void
    {
        $fake = new RecordingFake();
        $fake->send('a');
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Expected delete() to be called; it was not (calls: send())');
        $fake->assertCalled('delete');
    }

    /**
     * Given a wrong call count
     * When checking it
     * Then the failure says how many calls there were.
     */
    #[Test]
    final public function failedCountSaysTheCount(): void
    {
        $fake = new RecordingFake();
        $fake->send('a');
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Expected send() to be called 2 times; it was 1');
        $fake->assertCalled('send', times: 2);
    }

    /**
     * Given an adaptor that builds instances (the Curl client)
     * When it is faked
     * Then built instances wrap the fake, and calls through the adaptor (on an instance or static) are recorded.
     */
    #[Test]
    final public function anyAdaptorCanBeFaked(): void
    {
        $fake = Client::fake(['getConfig' => ['base_uri' => 'https://example.test']]);
        $client = Client::instantiate()->build();
        $this->assertSame($fake, $client->classInstance);
        $this->assertSame(['base_uri' => 'https://example.test'], $client->getConfig());
        $this->assertSame(['base_uri' => 'https://example.test'], Client::getConfig());
        $fake->assertCalled('getConfig', times: 2);
    }
}
