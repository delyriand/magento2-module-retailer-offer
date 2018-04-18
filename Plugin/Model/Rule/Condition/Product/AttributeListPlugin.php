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
namespace Smile\RetailerOffer\Plugin\Model\Rule\Condition\Product;

use Smile\ElasticsuiteCatalogRule\Model\Rule\Condition\Product\AttributeList;
use Smile\RetailerOffer\Helper\Settings;

/**
 * AttributeList plugin for offer price condition.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Maxime Leclercq <maxime.leclercq@smile.fr>
 */
class AttributeListPlugin
{
    /**
     * Specific offer field name mapping.
     *
     * @var @array
     */
    private $fieldNameMapping = [
        'price' => 'offer.price',
    ];

    /**
     * settings offer helper.
     *
     * @var Settings
     */
    private $settingsHelper;

    /**
     * AttributeListPlugin constructor.
     *
     * @param Settings $settingsHelper Offer settings helper.
     */
    public function __construct(Settings $settingsHelper)
    {
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * If use store offers, change price field name.
     *
     * @param AttributeList $source        AttributeList instance.
     * @param string        $attributeName Current attribute name.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetField(AttributeList $source, $attributeName)
    {
        if (array_key_exists($attributeName, $this->fieldNameMapping) && $this->settingsHelper->useStoreOffers()) {
            $attributeName = $this->fieldNameMapping[$attributeName];
        }

        return [$attributeName];
    }
}
