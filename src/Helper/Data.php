<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionCode\Helper;

use FeWeDev\Base\Arrays;
use Magento\Catalog\Api\Data\CustomOptionExtension;
use Magento\Catalog\Api\Data\ProductOptionExtension;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\Product\Option;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var Arrays */
    protected $arrays;

    /** @var Option */
    protected $productOption;

    /** @var array */
    protected $valueTypes;

    public function __construct(Arrays $arrays, Option $productOption, array $valueTypes)
    {
        $this->arrays = $arrays;
        $this->productOption = $productOption;
        $this->valueTypes = $valueTypes;
    }

    public function addCodeAndLabelToOrder(OrderInterface $order): void
    {
        foreach ($order->getItems() as $item) {
            if ($item instanceof Item) {
                $this->addCodeAndLabelToOrderItem($item);
            }
        }

        $shippingAssignments = $order->getExtensionAttributes()->getShippingAssignments();

        foreach ($shippingAssignments as $shippingAssignment) {
            foreach ($shippingAssignment->getItems() as $item) {
                if ($item instanceof Item) {
                    $this->addCodeAndLabelToOrderItem($item);
                }
            }
        }
    }

    public function addCodeAndLabelToOrderItem(Item $item): void
    {
        $itemProductOptions = $item->getProductOptions();

        $itemProductOptionsOptions = $this->arrays->getValue(
            $itemProductOptions,
            'options',
            []
        );

        foreach ($itemProductOptionsOptions as $itemProductOptionsOption) {
            $itemProductOptionsOptionId = $this->arrays->getValue(
                $itemProductOptionsOption,
                'option_id'
            );

            /** @var Option $productOptionData */
            $productOptionData = $item->getProductOption();

            /** @var ProductOptionExtension $productOptionDataAttributes */
            $productOptionDataAttributes = $productOptionData->getExtensionAttributes();

            $customOptions = $productOptionDataAttributes->getCustomOptions();

            /** @var CustomOption $customOption */
            foreach ($customOptions as $customOption) {
                if ($customOption->getOptionId() == $itemProductOptionsOptionId) {
                    /** @var CustomOptionExtension $customOptionExtensionAttributes */
                    $customOptionExtensionAttributes = $customOption->getExtensionAttributes();

                    $itemProductOptionsOptionCode = $this->arrays->getValue(
                        $itemProductOptionsOption,
                        'option_code'
                    );
                    if ($itemProductOptionsOptionCode !== null) {
                        $customOptionExtensionAttributes->setOptionCode(
                            $itemProductOptionsOptionCode
                        );
                    }

                    $itemProductOptionsOptionLabel = $this->arrays->getValue(
                        $itemProductOptionsOption,
                        'label'
                    );
                    if ($itemProductOptionsOptionLabel !== null) {
                        $customOptionExtensionAttributes->setOptionLabel(
                            $itemProductOptionsOptionLabel
                        );
                    }

                    $itemProductOptionsOptionType = $this->arrays->getValue(
                        $itemProductOptionsOption,
                        'option_type'
                    );

                    $group = $this->productOption->getGroupByType($itemProductOptionsOptionType);

                    if (in_array(
                        $group,
                        $this->valueTypes
                    )) {
                        $itemProductOptionsOptionValueCode = $this->arrays->getValue(
                            $itemProductOptionsOption,
                            'option_value_code'
                        );
                        if ($itemProductOptionsOptionValueCode !== null) {
                            $customOptionExtensionAttributes->setOptionValueCode(
                                $itemProductOptionsOptionValueCode
                            );
                        }

                        $itemProductOptionsOptionValueLabel = $this->arrays->getValue(
                            $itemProductOptionsOption,
                            'value'
                        );
                        if ($itemProductOptionsOptionValueLabel !== null) {
                            $customOptionExtensionAttributes->setOptionValueLabel(
                                $itemProductOptionsOptionValueLabel
                            );
                        }
                    }

                    $customOption->setExtensionAttributes($customOptionExtensionAttributes);
                }
            }
        }
    }
}
