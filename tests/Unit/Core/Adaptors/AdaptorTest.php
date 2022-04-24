<?php

namespace Tests\Unit\Core\Adaptors;

use Core\Adaptors\Adaptor;
use Core\Adaptors\Config;
use Core\Adaptors\Lang;
use Tests\Mocks\GenericClass;
use Tests\TestCase;

/**
 * Class AdaptorTest
 *
 * @package Tests\Unit\Core\Adaptors
 *
 * @coversDefaultClass Adaptor
 *
 * @group Unit
 * @group Adaptor
 */
class AdaptorTest extends TestCase
{
    final public function setUp(): void
    {
        parent::setUp();
        Config::reset([]);
    }

    /**
     * Given a configured adaptor class
     * When getting an instance
     * Then a new instance of the configured class will be created.
     *
     * @test
     *
     * @covers ::instantiateResource
     */
    final public function createInstanceOfAnotherClass(): void
    {
        Config::set('adaptors.' . AdaptorClass::class, GenericClass::class);
        $adaptor = (new AdaptorClass())->instantiateResource();
        $this->assertInstanceOf(GenericClass::class, $adaptor->classInstance);
    }

    /**
     * Given an attempt to access a property
     * When the property is configured no public access
     * Then an access denied error will be thrown.
     *
     * @test
     *
     * @covers ::__get
     */
    final public function unableToGetPrivateProperty(): void
    {
        $adaptor = new AdaptorClass();
        $this->expectErrorMessage(
            Lang::get('errors.adaptor.inaccessibleProperty.get', [AdaptorClass::class, 'doNotAccess'])
        );
        $adaptor->doNotAccess;
    }

    /**
     * Given an attempt to set a property
     * When the property is configured with no public access
     * Then an access denied error will be thrown.
     *
     * @test
     *
     * @covers ::__set
     */
    final public function unableToSetPrivateProperty(): void
    {
        $adaptor = new AdaptorClass();
        $this->expectErrorMessage(
            Lang::get('errors.adaptor.inaccessibleProperty.set', [AdaptorClass::class, 'doNotAccess'])
        );
        $adaptor->doNotAccess = false;
    }

    /**
     * Given an attempt to access a property
     * When the property is configured with get disabled
     * Then an access denied error will be thrown.
     *
     * @test
     *
     * @covers ::__get
     */
    final public function unableToGetWhenGetAccessDenied(): void
    {
        $adaptor = new AdaptorClass();
        $adaptor->cannotGetMe = false;
        $this->expectErrorMessage(
            Lang::get('errors.adaptor.inaccessibleProperty.get', [AdaptorClass::class, 'cannotGetMe'])
        );
        $adaptor->cannotGetMe;
    }

    /**
     * Given an attempt to set a property
     * When the property is configured with set disabled
     * Then an access denied error will be thrown.
     *
     * @test
     *
     * @covers ::__set
     */
    final public function unableToSetWhenSetAccessDenied(): void
    {
        $adaptor = new AdaptorClass();
        $this->assertTrue($adaptor->cannotSetMe);
        $this->expectErrorMessage(
            Lang::get('errors.adaptor.inaccessibleProperty.set', [AdaptorClass::class, 'cannotSetMe'])
        );
        $adaptor->cannotSetMe = false;
    }

    /**
     * Given an Adaptor instance
     * When a property is dynamically added
     * Then access to both get and set will be granted on the new property.
     *
     * @test
     *
     * @covers ::__set
     * @covers ::__get
     */
    final public function ableToSetAndGetNewProperty(): void
    {
        $adaptor = new AdaptorClass();
        $adaptor->somethingNew = true;
        $this->assertTrue($adaptor->somethingNew);
        $this->assertArrayHasKey('somethingNew', $adaptor->accessScopes);
        $this->assertEquals(['get' => true, 'set' => true], $adaptor->accessScopes['somethingNew']);
    }

    /**
     * Given a configured class instance with property
     * When accessing the same name property on adaptor
     * Then the public property of the instance will be returned.
     *
     * @test
     *
     * @covers ::__get
     */
    final public function getClassInstancePropertyFromMagicGet(): void
    {
        AdaptorClass::setResource(new GenericClass(['extraProperty' => true]));
        $adaptor = AdaptorClass::instantiate()->build();
        $this->assertTrue($adaptor->extraProperty);
    }

    /**
     * Given a configured class instance with property
     * When setting the same name property on adaptor
     * Then the public property of the instance will be set.
     *
     * @test
     *
     * @covers ::__set
     */
    final public function setClassInstancePropertyFromMagicSet(): void
    {
        AdaptorClass::setResource(new GenericClass(['extraProperty' => true]));
        $adaptor = (new AdaptorClass())->build();
        $this->assertTrue($adaptor->extraProperty);
        $adaptor->extraProperty = false;
        $this->assertFalse($adaptor->extraProperty);
    }

    /**
     * Given dynamic methods assigned
     * When calling the methods directly or on the class instance
     * Then the method will be callable.
     *
     * @test
     *
     * @covers ::__call
     */
    final public function canCallCustomAdaptorMethod(): void
    {
        AdaptorClass::setResource(new GenericClass(['instanceMethod' => fn() => true]));
        $adaptor = (new AdaptorClass())->build();
        $adaptor->baseMethod = fn() => false;
        $this->assertTrue($adaptor->instanceMethod());
        $this->assertFalse($adaptor->baseMethod());
    }

    /**
     * Given dynamic methods assigned
     * When calling the methods directly or on the class instance
     * Then the method will be callable.
     *
     * @test
     *
     * @covers ::__call
     */
    final public function errorWhenMethodIsNotDefined(): void
    {
        $adaptor = new AdaptorClass();
        $adaptor->extraMethod = true;
        $this->expectErrorMessage(Lang::get('errors.adaptor.undefinedMethod', [AdaptorClass::class, 'extraMethod']));
        $adaptor->extraMethod();
    }
}

class AdaptorClass extends Adaptor
{
    protected bool $cannotGetMe = true;
    protected bool $cannotSetMe = true;
    protected bool $doNotAccess = true;

    final public function __construct()
    {
        parent::__construct();
        $this->accessScopes['cannotGetMe'] = ['set' => true];
        $this->accessScopes['cannotSetMe'] = ['get' => true];
    }

    final public function instantiateResource(): self
    {
        $this->classInstance = new $this->castedClass->className();
        return $this;
    }
}