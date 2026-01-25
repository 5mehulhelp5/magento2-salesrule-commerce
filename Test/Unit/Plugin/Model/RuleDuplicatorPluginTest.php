<?php
declare(strict_types=1);

namespace SchrammelCodes\SalesRuleCommerce\Test\Unit\Plugin\Model;

use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SchrammelCodes\SalesRule\Model\RuleDuplicator;
use SchrammelCodes\SalesRuleCommerce\Plugin\Model\RuleDuplicatorPlugin;

class RuleDuplicatorPluginTest extends TestCase
{
    private RuleDuplicatorPlugin $plugin;
    private RuleResource|MockObject $ruleResource;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->ruleResource = $this->createMock(RuleResource::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->plugin = new RuleDuplicatorPlugin(
            $this->ruleResource,
            $this->logger
        );
    }

    public function testAfterDuplicateResetsStagingFields(): void
    {
        $subject = $this->createMock(RuleDuplicator::class);
        $result = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCreatedIn', 'setUpdatedIn', 'unsRowId'])
            ->onlyMethods(['getId', 'getData', 'getStoreLabels'])
            ->getMock();

        $result->expects($this->once())
            ->method('setCreatedIn')
            ->with(1)
            ->willReturnSelf();

        $result->expects($this->once())
            ->method('setUpdatedIn')
            ->with(2147483647)
            ->willReturnSelf();

        $result->expects($this->once())
            ->method('unsRowId')
            ->willReturnSelf();

        $result->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->ruleResource->expects($this->once())
            ->method('save')
            ->with($result)
            ->willReturnSelf();

        $this->ruleResource->expects($this->once())
            ->method('load')
            ->with($result, 123)
            ->willReturnSelf();

        $result->expects($this->once())
            ->method('getData')
            ->willReturn(456);

        $result->expects($this->once())
            ->method('getStoreLabels')
            ->willReturn([1 => 'Label 1']);

        $this->ruleResource->expects($this->once())
            ->method('getLinkField')
            ->willReturn('row_id');

        $this->ruleResource->expects($this->once())
            ->method('saveStoreLabels')
            ->with(456, [1 => 'Label 1']);

        $pluginResult = $this->plugin->afterDuplicate($subject, $result);

        $this->assertSame($result, $pluginResult);
    }

    public function testAfterDuplicateHandlesExceptions(): void
    {
        $subject = $this->createMock(RuleDuplicator::class);
        $result = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCreatedIn'])
            ->getMock();

        $exception = new \Exception('Test exception');

        $result->expects($this->once())
            ->method('setCreatedIn')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Failed to apply staging modifications'),
                ['exception' => $exception]
            );

        $pluginResult = $this->plugin->afterDuplicate($subject, $result);

        $this->assertSame($result, $pluginResult);
    }
}
