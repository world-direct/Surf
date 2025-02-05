<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Domain\Model\Clock;

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Clock\ClockException;
use TYPO3\Surf\Domain\Clock\SystemClock;

class SystemClockTest extends TestCase
{
    private SystemClock $subject;

    protected function setUp(): void
    {
        $this->subject = new SystemClock();
    }

    /**
     * @param string $string
     *
     * @test
     * @dataProvider invalidStringsCannotBeConvertedToTimestamp
     */
    public function stringCouldNotBeConvertedCorrectlyExceptionIsThrown($string): void
    {
        $this->expectException(ClockException::class);
        $this->subject->stringToTime($string);
    }

    /**
     * @param string $string
     * @param int $base
     * @test
     * @dataProvider validStringCanBeConvertedToTimestamp
     */
    public function stringCanBeConvertedToValidTimestamp($string, $base): void
    {
        self::assertEquals(strtotime($string, $base), $this->subject->stringToTime($string, $base));
    }

    /**
     * @param string $format
     * @param string $time
     * @param int $expected
     * @test
     * @dataProvider validFormatCanBeConvertedToTimestamp
     */
    public function successFullyCreateTimestampFromFormat($format, $time, $expected): void
    {
        self::assertSame($expected, $this->subject->createTimestampFromFormat($format, $time));
    }

    /**
     * @return array
     */
    public function validFormatCanBeConvertedToTimestamp(): array
    {
        return [
            ['YmdHis', strftime('%Y%m%d%H%M%S', 1535216980), 1535216980],
        ];
    }

    /**
     * @return array
     */
    public function validStringCanBeConvertedToTimestamp(): array
    {
        return [
            ['1 day ago', 1535216980],
            ['2 days ago', 1535216980],
            ['1 second ago', 1535216980],
        ];
    }

    /**
     * @return array
     */
    public function invalidStringsCannotBeConvertedToTimestamp(): array
    {
        return [
            ['2 apples ago'],
            ['One second and half'],
        ];
    }
}
