<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionCode\Plugin\Catalog\Model\Product\Type;

use Magento\Catalog\Model\Product;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AbstractType
{
    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetOrderOptions(
        Product\Type\AbstractType $subject,
        array $result,
        Product $product
    ): array {
        $optionIds = $product->getCustomOption('option_ids');

        if ($optionIds) {
            foreach (explode(
                ',',
                $optionIds->getValue() ?? ''
            ) as $optionId) {
                $option = $product->getOptionById($optionId);

                if ($option) {
                    $optionCode = $option->getData('code');

                    if ($optionCode) {
                        foreach ($result[ 'options' ] as $optionKey => $optionData) {
                            if ($optionData[ 'option_id' ] === $optionId) {
                                $result[ 'options' ][ $optionKey ][ 'option_code' ] = $optionCode;
                            }
                        }
                    }

                    $optionValues = $option->getValues();

                    if ($optionValues) {
                        $confItemOption =
                            $product->getCustomOption(Product\Type\AbstractType::OPTION_PREFIX . $option->getId());

                        $valueId = $confItemOption->getValue();

                        foreach ($optionValues as $optionValue) {
                            if ($optionValue->getOptionTypeId() == $valueId) {
                                $optionValueCode = $optionValue->getData('code');

                                if ($optionValueCode) {
                                    foreach ($result[ 'options' ] as $optionKey => $optionData) {
                                        if ($optionData[ 'option_id' ] === $optionId) {
                                            $result[ 'options' ][ $optionKey ][ 'option_value_code' ] =
                                                $optionValueCode;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}
