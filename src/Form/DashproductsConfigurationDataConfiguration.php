<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\Dashproducts\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

/**
 * Handles configuration data for dashproducts module configuration.
 */
final class DashproductsConfigurationDataConfiguration implements DataConfigurationInterface
{
    private const ALLOWED_VALUES = [5, 10, 20, 50];

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        return [
            'show_last_order' => (int) $this->configuration->get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'),
            'show_best_seller' => (int) $this->configuration->get('DASHPRODUCT_NBR_SHOW_BEST_SELLER'),
            'show_most_viewed' => (int) $this->configuration->get('DASHPRODUCT_NBR_SHOW_MOST_VIEWED'),
            'show_top_searches' => (int) $this->configuration->get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration): array
    {
        if ($this->validateConfiguration($configuration)) {
            $this->configuration->set('DASHPRODUCT_NBR_SHOW_LAST_ORDER', (int) $configuration['show_last_order']);
            $this->configuration->set('DASHPRODUCT_NBR_SHOW_BEST_SELLER', (int) $configuration['show_best_seller']);
            $this->configuration->set('DASHPRODUCT_NBR_SHOW_MOST_VIEWED', (int) $configuration['show_most_viewed']);
            $this->configuration->set('DASHPRODUCT_NBR_SHOW_TOP_SEARCH', (int) $configuration['show_top_searches']);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(array $configuration): bool
    {
        return (
            // Last orders
            isset($configuration['show_last_order'])
            && is_numeric($configuration['show_last_order'])
            && in_array((int) $configuration['show_last_order'], self::ALLOWED_VALUES)
            // Best sellers
            && isset($configuration['show_best_seller'])
            && is_numeric($configuration['show_best_seller'])
            && in_array((int) $configuration['show_last_order'], self::ALLOWED_VALUES)
            // Most viewed
            && isset($configuration['show_most_viewed'])
            && is_numeric($configuration['show_most_viewed'])
            && in_array((int) $configuration['show_last_order'], self::ALLOWED_VALUES)
            // Top searches
            && isset($configuration['show_top_searches'])
            && is_numeric($configuration['show_top_searches'])
            && in_array((int) $configuration['show_last_order'], self::ALLOWED_VALUES)
        );
    }
}
