<?php

declare(strict_types=1);

namespace Tests;

use CodeIgniter\Database\RawSql;
use Michalsn\CodeIgniterUuid\Database\BinaryLiteralConverterFactory;
use Michalsn\CodeIgniterUuid\Database\MySqliBinaryConverter;
use Michalsn\CodeIgniterUuid\Database\OCI8BinaryConverter;
use Michalsn\CodeIgniterUuid\Database\PostgreBinaryConverter;
use Michalsn\CodeIgniterUuid\Database\SQLite3BinaryConverter;
use Michalsn\CodeIgniterUuid\Exceptions\CodeIgniterUuidException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class BinaryLiteralConverterTest extends TestCase
{
    private string $testBinary;
    private string $testHex;

    protected function setUp(): void
    {
        parent::setUp();

        // A sample 16-byte UUID binary
        $this->testHex    = '550e8400e29b41d4a716446655440000';
        $this->testBinary = hex2bin($this->testHex);
    }

    public function testFactoryReturnsMySqliConverter()
    {
        $converter = BinaryLiteralConverterFactory::get('MySQLi');

        $this->assertInstanceOf(MySqliBinaryConverter::class, $converter);
    }

    public function testFactoryReturnsSqlsrvConverter()
    {
        $converter = BinaryLiteralConverterFactory::get('SQLSRV');

        $this->assertInstanceOf(MySqliBinaryConverter::class, $converter);
    }

    public function testFactoryReturnsPostgreConverter()
    {
        $converter = BinaryLiteralConverterFactory::get('Postgre');

        $this->assertInstanceOf(PostgreBinaryConverter::class, $converter);
    }

    public function testFactoryReturnsSqlite3Converter()
    {
        $converter = BinaryLiteralConverterFactory::get('SQLite3');

        $this->assertInstanceOf(SQLite3BinaryConverter::class, $converter);
    }

    public function testFactoryReturnsOci8Converter()
    {
        $converter = BinaryLiteralConverterFactory::get('OCI8');

        $this->assertInstanceOf(OCI8BinaryConverter::class, $converter);
    }

    public function testFactoryThrowsExceptionForUnknownDriver()
    {
        $this->expectException(CodeIgniterUuidException::class);

        BinaryLiteralConverterFactory::get('Unknown');
    }

    public function testFactoryCachesConverters()
    {
        $converter1 = BinaryLiteralConverterFactory::get('MySQLi');
        $converter2 = BinaryLiteralConverterFactory::get('MySQLi');

        $this->assertSame($converter1, $converter2);
    }

    public function testMySqliToBinaryLiteral()
    {
        $converter = new MySqliBinaryConverter();
        $result    = $converter->toBinaryLiteral($this->testBinary);

        $this->assertSame("0x{$this->testHex}", (string) $result);
    }

    public function testMySqliFromBinaryLiteral()
    {
        $converter = new MySqliBinaryConverter();
        $literal   = new RawSql("0x{$this->testHex}");
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }

    public function testMySqliFromBinaryLiteralInvalidFormat()
    {
        $this->expectException(RuntimeException::class);

        $converter = new MySqliBinaryConverter();
        $converter->fromBinaryLiteral(new RawSql('invalid'));
    }

    public function testPostgreToBinaryLiteral()
    {
        $converter = new PostgreBinaryConverter();
        $result    = $converter->toBinaryLiteral($this->testBinary);

        $this->assertSame("decode('{$this->testHex}', 'hex')", (string) $result);
    }

    public function testPostgreFromBinaryLiteral()
    {
        $converter = new PostgreBinaryConverter();
        $literal   = new RawSql("decode('{$this->testHex}', 'hex')");
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }

    public function testPostgreFromBinaryLiteralInvalidFormat()
    {
        $this->expectException(RuntimeException::class);

        $converter = new PostgreBinaryConverter();
        $converter->fromBinaryLiteral(new RawSql('invalid'));
    }

    public function testSqlite3ToBinaryLiteral()
    {
        $converter = new SQLite3BinaryConverter();
        $result    = $converter->toBinaryLiteral($this->testBinary);

        $this->assertSame("X'{$this->testHex}'", (string) $result);
    }

    public function testSqlite3FromBinaryLiteral()
    {
        $converter = new SQLite3BinaryConverter();
        $literal   = new RawSql("X'{$this->testHex}'");
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }

    public function testSqlite3FromBinaryLiteralInvalidFormat()
    {
        $this->expectException(RuntimeException::class);

        $converter = new SQLite3BinaryConverter();
        $converter->fromBinaryLiteral(new RawSql('invalid'));
    }

    public function testOci8ToBinaryLiteral()
    {
        $converter = new OCI8BinaryConverter();
        $result    = $converter->toBinaryLiteral($this->testBinary);

        $this->assertSame("HEXTORAW('{$this->testHex}')", (string) $result);
    }

    public function testOci8FromBinaryLiteral()
    {
        $converter = new OCI8BinaryConverter();
        $literal   = new RawSql("HEXTORAW('{$this->testHex}')");
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }

    public function testOci8FromBinaryLiteralInvalidFormat()
    {
        $this->expectException(RuntimeException::class);

        $converter = new OCI8BinaryConverter();
        $converter->fromBinaryLiteral(new RawSql('invalid'));
    }

    public function testRoundTripMySqli()
    {
        $converter = new MySqliBinaryConverter();
        $literal   = $converter->toBinaryLiteral($this->testBinary);
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }

    public function testRoundTripPostgre()
    {
        $converter = new PostgreBinaryConverter();
        $literal   = $converter->toBinaryLiteral($this->testBinary);
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }

    public function testRoundTripSqlite3()
    {
        $converter = new SQLite3BinaryConverter();
        $literal   = $converter->toBinaryLiteral($this->testBinary);
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }

    public function testRoundTripOci8()
    {
        $converter = new OCI8BinaryConverter();
        $literal   = $converter->toBinaryLiteral($this->testBinary);
        $result    = $converter->fromBinaryLiteral($literal);

        $this->assertSame($this->testBinary, $result);
    }
}
