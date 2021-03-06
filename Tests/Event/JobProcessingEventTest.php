<?php

namespace SfCod\QueueBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use SfCod\QueueBundle\Event\JobProcessingEvent;
use SfCod\QueueBundle\Job\JobContractInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class JobProcessingEventTest
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\QueueBundle\Tests\Event
 */
class JobProcessingEventTest extends TestCase
{
    /**
     * Test event
     */
    public function testEvent()
    {
        $connectionName = uniqid('connection_');
        $job = $this->createMock(JobContractInterface::class);

        $event = new JobProcessingEvent($connectionName, $job);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals($connectionName, $event->getConnectionName());
        $this->assertEquals($job, $event->getJob());
    }
}
