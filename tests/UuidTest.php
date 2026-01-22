<?php

declare(strict_types=1);

namespace Tests;

use Michalsn\CodeIgniterUuid\Config\Uuid as UuidConfig;
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;
use Michalsn\CodeIgniterUuid\Exceptions\CodeIgniterUuidException;
use Michalsn\CodeIgniterUuid\Uuid;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV3;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV5;
use Symfony\Component\Uid\UuidV6;
use Symfony\Component\Uid\UuidV7;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class UuidTest extends TestCase
{
    private Uuid $uuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uuid = new Uuid();
    }

    public function testGenerateDefaultVersion()
    {
        $uuid = $this->uuid->generate();

        $this->assertInstanceOf(UuidV7::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testGenerateV1()
    {
        $uuid = $this->uuid->generate('v1');

        $this->assertInstanceOf(UuidV1::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testGenerateV3()
    {
        $uuid = $this->uuid->generate('v3');

        $this->assertInstanceOf(UuidV3::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testGenerateV4()
    {
        $uuid = $this->uuid->generate('v4');

        $this->assertInstanceOf(UuidV4::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testGenerateV5()
    {
        $uuid = $this->uuid->generate('v5');

        $this->assertInstanceOf(UuidV5::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testGenerateV6()
    {
        $uuid = $this->uuid->generate('v6');

        $this->assertInstanceOf(UuidV6::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testGenerateV7()
    {
        $uuid = $this->uuid->generate('v7');

        $this->assertInstanceOf(UuidV7::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testGenerateWithUnsupportedVersion()
    {
        $this->expectException(CodeIgniterUuidException::class);
        $this->expectExceptionMessage('Unsupported UUID version: "v8".');

        $this->uuid->generate('v8');
    }

    public function testGenerateWithCustomConfig()
    {
        $config                 = new UuidConfig();
        $config->defaultVersion = UuidVersion::V4;
        $customUuid             = new Uuid($config);

        $uuid = $customUuid->generate();

        $this->assertInstanceOf(UuidV4::class, $uuid->unwrap());
    }

    public function testGenerateWithEnum()
    {
        $uuid = $this->uuid->generate(UuidVersion::V4);

        $this->assertInstanceOf(UuidV4::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testUuidToString()
    {
        $uuid       = $this->uuid->generate('v4');
        $uuidString = $uuid->toRfc4122();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuidString);
    }

    public function testUuidToBytes()
    {
        $uuid      = $this->uuid->generate('v4');
        $uuidBytes = $uuid->toBinary();

        $this->assertSame(16, strlen($uuidBytes));
    }

    public function testUuidFromString()
    {
        $uuid       = $this->uuid->generate('v4');
        $uuidString = $uuid->toRfc4122();

        $recreatedUuid = $this->uuid->fromString($uuidString);

        $this->assertSame($uuidString, $recreatedUuid->toRfc4122());
    }

    public function testMultipleUuidsAreUnique()
    {
        $uuid1 = $this->uuid->generate('v4');
        $uuid2 = $this->uuid->generate('v4');
        $uuid3 = $this->uuid->generate('v4');

        $this->assertNotSame($uuid1->toRfc4122(), $uuid2->toRfc4122());
        $this->assertNotSame($uuid2->toRfc4122(), $uuid3->toRfc4122());
        $this->assertNotSame($uuid1->toRfc4122(), $uuid3->toRfc4122());
    }

    public function testV3IsDeterministic()
    {
        $uuid1 = $this->uuid->generate('v3');
        $uuid2 = $this->uuid->generate('v3');

        // V3 should be deterministic with same namespace and name
        $this->assertSame($uuid1->toRfc4122(), $uuid2->toRfc4122());
    }

    public function testV5IsDeterministic()
    {
        $uuid1 = $this->uuid->generate('v5');
        $uuid2 = $this->uuid->generate('v5');

        // V5 should be deterministic with same namespace and name
        $this->assertSame($uuid1->toRfc4122(), $uuid2->toRfc4122());
    }

    public function testV7IsOrderedByTime()
    {
        $uuid1 = $this->uuid->generate('v7');
        $uuid2 = $this->uuid->generate('v7');

        // V7 UUIDs should be ordered by time
        $this->assertLessThanOrEqual($uuid2->toRfc4122(), $uuid1->toRfc4122());
    }

    public function testServiceUuid()
    {
        $uuidService = service('uuid');
        $this->assertInstanceOf(Uuid::class, $uuidService);
        $uuid = $uuidService->generate('v4');
        $this->assertInstanceOf(UuidV4::class, $uuid->unwrap());
    }

    public function testFromValueWithString()
    {
        $uuid       = $this->uuid->generate('v4');
        $uuidString = $uuid->toRfc4122();

        $result = $this->uuid->fromValue($uuidString);

        $this->assertSame($uuidString, $result->toRfc4122());
    }

    public function testFromValueWithBytes()
    {
        $uuid      = $this->uuid->generate('v4');
        $uuidBytes = $uuid->toBinary();

        $result = $this->uuid->fromValue($uuidBytes);

        $this->assertSame($uuid->toRfc4122(), $result->toRfc4122());
    }

    public function testFromValueWithStringWithoutHyphens()
    {
        $uuid       = $this->uuid->generate('v4');
        $uuidString = str_replace('-', '', $uuid->toRfc4122());

        $result = $this->uuid->fromValue($uuidString);

        $this->assertSame($uuid->toRfc4122(), $result->toRfc4122());
    }

    public function testGenerateUlid()
    {
        $ulid = $this->uuid->generate('ulid');

        $this->assertInstanceOf(Ulid::class, $ulid->unwrap());
        $this->assertSame(26, strlen($ulid->toBase32()));
    }

    public function testGenerateUlidWithEnum()
    {
        $ulid = $this->uuid->generate(UuidVersion::ULID);

        $this->assertInstanceOf(Ulid::class, $ulid->unwrap());
        $this->assertSame(26, strlen($ulid->toBase32()));
    }

    public function testUlidIsOrderedByTime()
    {
        $ulid1 = $this->uuid->generate('ulid');
        $ulid2 = $this->uuid->generate('ulid');

        // ULIDs should be ordered by time (lexicographically sortable)
        $this->assertLessThanOrEqual($ulid2->toBase32(), $ulid1->toBase32());
    }

    public function testMultipleUlidsAreUnique()
    {
        $ulid1 = $this->uuid->generate('ulid');
        $ulid2 = $this->uuid->generate('ulid');
        $ulid3 = $this->uuid->generate('ulid');

        $this->assertNotSame($ulid1->toBase32(), $ulid2->toBase32());
        $this->assertNotSame($ulid2->toBase32(), $ulid3->toBase32());
        $this->assertNotSame($ulid1->toBase32(), $ulid3->toBase32());
    }

    public function testUlidToRfc4122()
    {
        $ulid = $this->uuid->generate('ulid');

        // ULID can also be represented as RFC4122 UUID format
        $this->assertSame(36, strlen($ulid->toRfc4122()));
    }

    public function testIsValidWithValidUuid()
    {
        $uuid = $this->uuid->generate('v4');

        $this->assertTrue($this->uuid->isValid($uuid->toRfc4122()));
    }

    public function testIsValidWithValidUuidWithoutHyphens()
    {
        $uuid       = $this->uuid->generate('v4');
        $uuidString = str_replace('-', '', $uuid->toRfc4122());

        $this->assertTrue($this->uuid->isValid($uuidString));
    }

    public function testIsValidWithValidUlid()
    {
        $ulid = $this->uuid->generate('ulid');

        $this->assertTrue($this->uuid->isValid($ulid->toBase32()));
    }

    public function testIsValidWithInvalidString()
    {
        $this->assertFalse($this->uuid->isValid('not-a-uuid'));
        $this->assertFalse($this->uuid->isValid(''));
        $this->assertFalse($this->uuid->isValid('12345'));
        $this->assertFalse($this->uuid->isValid('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'));
    }

    public function testIsValidWithAllUuidVersions()
    {
        foreach (['v1', 'v3', 'v4', 'v5', 'v6', 'v7'] as $version) {
            $uuid = $this->uuid->generate($version);
            $this->assertTrue($this->uuid->isValid($uuid->toRfc4122()), "UUID {$version} should be valid");
        }
    }

    //
    // Backward compatibility tests (Ramsey UUID method aliases)
    //

    public function testBackwardCompatibilityToString()
    {
        $uuid = $this->uuid->generate('v4');

        // toString() should work as alias for toRfc4122()
        $this->assertSame($uuid->toRfc4122(), $uuid->toString());
    }

    public function testBackwardCompatibilityGetBytes()
    {
        $uuid = $this->uuid->generate('v4');

        // getBytes() should work as alias for toBinary()
        $this->assertSame($uuid->toBinary(), $uuid->getBytes());
        $this->assertSame(16, strlen($uuid->getBytes()));
    }

    public function testBackwardCompatibilityGetHex()
    {
        $uuid = $this->uuid->generate('v4');

        // getHex() should work as alias for toHex()
        $this->assertSame($uuid->toHex(), $uuid->getHex());
    }

    public function testWrapperStringCast()
    {
        $uuid = $this->uuid->generate('v4');

        // Casting to string should return RFC4122 format
        $this->assertSame($uuid->toRfc4122(), (string) $uuid);
    }

    public function testWrapperEquals()
    {
        $uuid1 = $this->uuid->generate('v4');
        $uuid2 = $this->uuid->fromString($uuid1->toRfc4122());

        $this->assertTrue($uuid1->equals($uuid2));
        $this->assertTrue($uuid2->equals($uuid1));
    }

    public function testWrapperCompare()
    {
        $uuid1 = $this->uuid->generate('v7');
        usleep(1000);
        $uuid2 = $this->uuid->generate('v7');

        $this->assertLessThanOrEqual(0, $uuid1->compare($uuid2));
    }

    //
    // Convenience method tests
    //

    public function testUuid1Method()
    {
        $uuid = $this->uuid->uuid1();

        $this->assertInstanceOf(UuidV1::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testUuid3Method()
    {
        $uuid = $this->uuid->uuid3();

        $this->assertInstanceOf(UuidV3::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testUuid4Method()
    {
        $uuid = $this->uuid->uuid4();

        $this->assertInstanceOf(UuidV4::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testUuid5Method()
    {
        $uuid = $this->uuid->uuid5();

        $this->assertInstanceOf(UuidV5::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testUuid6Method()
    {
        $uuid = $this->uuid->uuid6();

        $this->assertInstanceOf(UuidV6::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testUuid7Method()
    {
        $uuid = $this->uuid->uuid7();

        $this->assertInstanceOf(UuidV7::class, $uuid->unwrap());
        $this->assertSame(36, strlen($uuid->toRfc4122()));
    }

    public function testUlidMethod()
    {
        $ulid = $this->uuid->ulid();

        $this->assertInstanceOf(Ulid::class, $ulid->unwrap());
        $this->assertSame(26, strlen($ulid->toBase32()));
    }
}
