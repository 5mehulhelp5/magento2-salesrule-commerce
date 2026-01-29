<?php
declare(strict_types=1);

namespace SchrammelCodes\SalesRuleCommerce\Plugin\Model;

use Exception;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use Psr\Log\LoggerInterface;
use SchrammelCodes\SalesRule\Model\RuleDuplicator;

class RuleDuplicatorPlugin
{
    private const STAGING_VERSION_MAIN = 1;
    private const STAGING_VERSION_MAX = 2147483647;

    public function __construct(
        private readonly RuleResource $ruleResource,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param RuleDuplicator $subject
     * @param Rule $result
     * @return Rule
     *
     * @suppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDuplicate(
        RuleDuplicator $subject,
        Rule $result
    ): Rule {
        try {
            $result->setCreatedIn(self::STAGING_VERSION_MAIN);
            $result->setUpdatedIn(self::STAGING_VERSION_MAX);
            $result->unsRowId();

            // Unset actions and conditions, as they get populated in \Magento\Rule\Model\AbstractModel::beforeSave
            // and are duplicated if not reset here
            $result->unsetData('conditions_serialized');
            $result->unsetData('actions_serialized');
            $this->ruleResource->save($result);
            $this->ruleResource->load($result, $result->getId());

            if ($storeLabels = $result->getStoreLabels()) {
                $this->ruleResource->saveStoreLabels(
                    $result->getData($this->ruleResource->getLinkField()),
                    $storeLabels
                );
            }
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to apply staging modifications to duplicated rule: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }

        return $result;
    }
}
