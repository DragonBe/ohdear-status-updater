<?php

namespace OhDearTest\Status;

use DateTimeImmutable;
use OhDear\Status\SlashCommand\StatusUpdate;
use OhDear\Status\SlashCommand\StatusUpdateInterface;
use PHPUnit\Framework\TestCase;

class StatusUpdateTest extends TestCase
{
    /**
     * Test that we implement Status Update Interface
     *
     * @covers \OhDear\Status\SlashCommand\StatusUpdate::__construct
     */
    public function testStatusUpdateImplementsStatusUpdateInterface(): StatusUpdate
    {
        $statusUpdate = new StatusUpdate();
        $this->assertInstanceOf(StatusUpdateInterface::class, $statusUpdate);
        return $statusUpdate;
    }

    /**
     * Test that the timestamp implements DateTimeImmutable at construct
     *
     * @param StatusUpdate $statusUpdate
     * @depends testStatusUpdateImplementsStatusUpdateInterface
     * @covers \OhDear\Status\SlashCommand\StatusUpdate
     */
    public function testStatusUpdateDefaultValuesAreSetCorrectly(StatusUpdate $statusUpdate): void
    {
        $this->assertSame(StatusUpdate::OHDEAR_LEVEL_INFO, $statusUpdate->getSeverity());
        $this->assertSame('', $statusUpdate->getTitle());
        $this->assertSame('', $statusUpdate->getText());
        $this->assertInstanceOf(DateTimeImmutable::class, $statusUpdate->getTime());
        $this->assertFalse($statusUpdate->isPinned());
    }

    /**
     * Test to see if we can set values at construct
     *
     * @covers \OhDear\Status\SlashCommand\StatusUpdate
     */
    public function testUpdateValuesCanBeSetAtConstruct(): void
    {
        $data = [
            'severity' => StatusUpdate::OHDEAR_LEVEL_INFO,
            'title' => 'Foo Bar',
            'message' => 'This is complete foobar!',
            'time' => new DateTimeImmutable('2020-03-12 08:30:00'),
            'pinned' => false,
        ];
        $statusUpdate = new StatusUpdate(
            $data['severity'],
            $data['title'],
            $data['message'],
            $data['time'],
            $data['pinned']
        );
        $this->assertSame($data['severity'], $statusUpdate->getSeverity());
        $this->assertSame($data['title'], $statusUpdate->getTitle());
        $this->assertSame($data['message'], $statusUpdate->getText());
        $this->assertSame($data['time'], $statusUpdate->getTime());
        $this->assertSame($data['pinned'], $statusUpdate->isPinned());
    }
}
