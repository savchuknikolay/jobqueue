<?php

namespace SfCod\QueueBundle\Tests\Job;

use Exception;
use MongoDB\BSON\ObjectID;
use PHPUnit\Framework\TestCase;
use SfCod\QueueBundle\Base\JobInterface;
use SfCod\QueueBundle\Base\JobResolverInterface;
use SfCod\QueueBundle\Entity\Job;
use SfCod\QueueBundle\Job\JobContract;
use SfCod\QueueBundle\Queue\QueueInterface;

/**
 * Class JobContractTest
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\QueueBundle\Tests\Job
 */
class JobContractTest extends TestCase
{
    /**
     * Test job
     */
    public function testJob(): void
    {
        $job = $this->mockJob();
        $contract = $this->mockJobContract($job);

        self::assertEquals($job->getId(), $contract->getJobId());
        self::assertEquals($job->getAttempts(), $contract->attempts());
        self::assertEquals($job->isReserved(), $contract->reserved());
        self::assertEquals($job->getReservedAt(), $contract->reservedAt());
        self::assertEquals($job->getPayload(), $contract->payload());
    }

    /**
     * Test job payload
     */
    public function testJobPayload(): void
    {
        $job = $this->mockJob();
        $contract = $this->mockJobContract($job);

        self::assertEquals($job->getPayload(), $contract->payload());
        self::assertEquals($job->getPayload()['job'], $contract->getName());
        self::assertEquals($job->getPayload()['data'], $contract->getData());
        self::assertEquals($job->getPayload()['maxTries'], $contract->maxTries());
        self::assertEquals($job->getPayload()['timeout'], $contract->timeout());
        self::assertEquals($job->getPayload()['timeoutAt'], $contract->timeoutAt());
    }

    /**
     * Test contract
     */
    public function testContract(): void
    {
        $job = $this->mockJob();
        $contract = $this->mockJobContract($job);

        self::assertFalse($contract->isDeleted());
        self::assertFalse($contract->isDeletedOrReleased());

        $contract->delete();

        self::assertTrue($contract->isDeleted());
        self::assertTrue($contract->isDeletedOrReleased());

        self::assertFalse($contract->isReleased());

        $contract->release();

        self::assertTrue($contract->isReleased());

        self::assertFalse($contract->hasFailed());

        $contract->markAsFailed();

        self::assertTrue($contract->hasFailed());
    }

    /**
     * Test contract main actions
     */
    public function testActions(): void
    {
        $job = $this->mockJob();

        $exception = new Exception(uniqid('message_', true));

        $jobInstance = $this->createMock(JobInterface::class);
        $jobInstance
            ->expects(self::once())
            ->method('fire')
            ->with(self::anything(), self::equalTo($job->getPayload()['data']));
        $jobInstance
            ->expects(self::once())
            ->method('failed')
            ->with(self::equalTo($job->getPayload()['data']), self::equalTo($exception));

        $contract = $this->mockJobContract($job, $jobInstance);

        $contract->fire();

        self::assertFalse($contract->hasFailed());

        $contract->failed($exception);

        self::assertTrue($contract->hasFailed());
    }

    /**
     * Mock job
     *
     * @return Job
     */
    private function mockJob(): Job
    {
        $job = new Job();
        $job->setId(new ObjectID());
        $job->setQueue(uniqid('queue_', true));
        $job->setAttempts(random_int(1, 10));
        $job->setReserved((bool)random_int(0, 1));
        $job->setReservedAt(time());
        $job->setPayload([
            'job' => uniqid('job_', true),
            'data' => range(1, 10),
            'maxTries' => random_int(1, 10),
            'timeout' => random_int(1, 1000),
            'timeoutAt' => time() + random_int(1, 1000),
        ]);

        return $job;
    }

    /**
     * Mock mongo job contract
     *
     * @param Job $jobData
     * @param JobInterface|null $jobInstance
     *
     * @return JobContract
     */
    private function mockJobContract(Job $jobData, ?JobInterface $jobInstance = null): JobContract
    {
        $queueName = uniqid('queue_name_', true);
        $queue = $this->createMock(QueueInterface::class);

        if (is_null($jobInstance)) {
            $jobInstance = $this->createMock(JobInterface::class);
        }

        $resolver = $this->createMock(JobResolverInterface::class);
        $resolver
            ->method('resolve')
            ->with(self::equalTo($jobData->getPayload()['job']))
            ->willReturn($jobInstance);

        /** @var JobContract $contract */
        $contract = $this->getMockBuilder(JobContract::class)
            ->setConstructorArgs([$resolver, $queue, $jobData, $queueName])
            ->setMethods(null)
            ->getMock();

        return $contract;
    }
}
