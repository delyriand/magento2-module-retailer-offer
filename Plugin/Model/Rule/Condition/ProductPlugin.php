<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Maxime Leclercq <maxime.leclercq@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin\Model\Rule\Condition;

use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteVirtualCategory\Model\Rule\Condition\Product;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Settings;

/**
 * AttributeList plugin for offer price condition.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Maxime Leclercq <maxime.leclercq@smile.fr>
 */
class ProductPlugin
{
    /**
     * Query factory.
     *
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Current store.
     *
     * @var CurrentStore
     */
    private $currentStore;

    /**
     * Offer settings helper.
     *
     * @var Settings
     */
    private $settings;

    /**
     * ProductPlugin constructor.
     *
     * @param QueryFactory $queryFactory Query factory.
     * @param CurrentStore $currentStore Current sotre.
     * @param Settings     $settings     Offer settings helper.
     */
    public function __construct(
        QueryFactory $queryFactory,
        CurrentStore $currentStore,
        Settings $settings
    ) {
        $this->queryFactory = $queryFactory;
        $this->currentStore = $currentStore;
        $this->settings = $settings;
    }

    /**
     * Change search query for price
     *
     * @param Product        $source      Product instance.
     * @param QueryInterface $searchQuery Search query.
     *
     * @return QueryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetSearchQuery(Product $source, $searchQuery)
    {
        if ($source->getAttribute() == 'price' && $this->settings->useStoreOffers()) {
            $mustClause = [];
            if ($this->getRetailerId() !== 0) {
                $mustClause = ['must' => $this->getStoreLimitationMustClauses()];
            }
            if ($searchQuery instanceof \Smile\ElasticsuiteCore\Search\Request\Query\Nested) {
                $searchQuery = $searchQuery->getQuery();
            }
            $mustClause['must'][] = $searchQuery;

            $boolFilter   = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause);
            $searchQuery = $this->queryFactory->create(QueryInterface::TYPE_NESTED, ['path' => 'offer', 'query' => $boolFilter]);
        }

        return $searchQuery;
    }

    /**
     * Return the must clauses store limitation.
     *
     * @return array
     */
    public function getStoreLimitationMustClauses()
    {
        $sellerIdFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.seller_id', 'value' => $this->getRetailerId()]
        );

        return [$sellerIdFilter];
    }

    /**
     * Retrieve current retailer Id.
     *
     * @return int
     */
    private function getRetailerId()
    {
        $retailerId = 0;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailerId = (int) $this->currentStore->getRetailer()->getId();
        }

        return $retailerId;
    }
}
