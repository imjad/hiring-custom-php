<?php

declare(strict_types=1);

namespace Tests\Unit\Storage;

use App\Storage\EpgStorage;
use Carbon\CarbonImmutable;
use League\Flysystem\FilesystemWriter;
use Tests\TestCase;

final class EpgStorageTest extends TestCase
{
    public function test_storeEpg()
    {
        $writer = $this->createMock(FilesystemWriter::class);

        $valueDate = CarbonImmutable::create(2021,7,15,1,2,3);
        $receivedAt = CarbonImmutable::create(2021,7,15,18,7,0);

        $filename = "{$valueDate->format('Ymd')}_{$receivedAt->format('Ymdhis')}.json.gz";
        $path = "epg-api/test/{$valueDate->format('Y/m/d')}/$filename";

        $writer->expects($this->once())
            ->method('write')
            ->with(
                $this->equalTo($path),
                $this->callback(function ($value) {
                    // $value is gzdlated json content
                    // str below = bin2hex(gzencode(json_encode(['some' => 'data'])))

                    $re = false;
                    if (is_string(bin2hex($value))) {
                        $re = true;
                    }

                    return $re;
                })
            );

        $sut = new EpgStorage($writer, 'epg-api');
        $sut->storeEpg( 'test', $valueDate, $receivedAt, ['some' => 'data']);
    }
}
