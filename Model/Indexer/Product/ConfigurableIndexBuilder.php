<?php
/**
 * @author      Benjamin Rosenberger <rosenberger@e-conomix.at>
 * @package
 * @copyright   Copyright (c) 2017 E-CONOMIX GmbH (http://www.e-conomix.at)
 */
namespace Faonni\SmartCategoryConfigurable\Model\Indexer\Product;
use Faonni\SmartCategory\Model\Indexer\IndexBuilder;
use Faonni\SmartCategory\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Faonni\SmartCategory\Model\Rule;
use Faonni\SmartCategoryConfigurable\Model\ConfigurableProductsProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Psr\Log\LoggerInterface;

class ConfigurableIndexBuilder extends IndexBuilder
{
    /**
     * @var ConfigurableProductsProvider
     */
    private $configurableProductsProvider;

    private $productCache=[];

    public function __construct(
        ConfigurableProductsProvider $configurableProductsProvider,
        RuleCollectionFactory $ruleCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($ruleCollectionFactory, $resource, $logger, $productFactory, $indexerRegistry);
        $this->configurableProductsProvider = $configurableProductsProvider;
    }

    protected function doReindexByIds($ids)
    {
        $this->productCache = $this->configurableProductsProvider->getDisplayIds($ids);
        parent::doReindexByIds($this->productCache);
    }

    protected function validateProduct($rule, $product)
    {
        if ($rule->getReplaceOnConfigurable()) {
            return parent::validateProduct($rule, $this->getBasedOnElement($product->getId()));
        } else {
            return parent::validateProduct($rule, $product);
        }
    }

    protected function getBasedOnElement($productId) {
        foreach ($this->productCache as $id => $displayId) {
            if ($productId==$displayId) {
                $productId = $id;
                break;
            }
        }

        return parent::getProduct($productId);
    }
}