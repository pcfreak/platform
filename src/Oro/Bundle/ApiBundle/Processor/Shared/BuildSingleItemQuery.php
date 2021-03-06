<?php

namespace Oro\Bundle\ApiBundle\Processor\Shared;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\SingleItemContext;
use Oro\Bundle\ApiBundle\Util\CriteriaConnector;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;

/**
 * Builds ORM QueryBuilder object that will be used to get an entity by its identifier.
 */
class BuildSingleItemQuery implements ProcessorInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var CriteriaConnector */
    protected $criteriaConnector;

    /**
     * @param DoctrineHelper    $doctrineHelper
     * @param CriteriaConnector $criteriaConnector
     */
    public function __construct(DoctrineHelper $doctrineHelper, CriteriaConnector $criteriaConnector)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->criteriaConnector = $criteriaConnector;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var SingleItemContext $context */

        if ($context->hasQuery()) {
            // a query is already built
            return;
        }

        $criteria = $context->getCriteria();
        if (null === $criteria) {
            // the criteria object does not exist
            return;
        }

        $entityClass = $context->getClassName();
        if (!$this->doctrineHelper->isManageableEntityClass($entityClass)) {
            // only manageable entities or resources based on manageable entities are supported
            $entityClass = $context->getConfig()->getParentResourceClass();
            if (!$entityClass || !$this->doctrineHelper->isManageableEntityClass($entityClass)) {
                return;
            }
        }

        $query = $this->doctrineHelper->getEntityRepositoryForClass($entityClass)->createQueryBuilder('e');
        $this->doctrineHelper->applyEntityIdentifierRestriction($query, $entityClass, $context->getId());
        $this->criteriaConnector->applyCriteria($query, $criteria);

        $context->setQuery($query);
    }
}
