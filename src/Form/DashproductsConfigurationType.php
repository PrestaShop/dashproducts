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

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Defines dashproducts configuration form.
 */
class DashproductsConfigurationType extends TranslatorAwareType
{
    private const CHOICES = [
        5 => 5,
        10 => 10,
        20 => 20,
        50 => 50,
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('show_last_order', ChoiceType::class, [
                'label' => $this->trans('Number of "Recent Orders" to display', 'Modules.Dashproducts.Admin'),
                'choices' => self::CHOICES,
                'required' => false,
                'placeholder' => false,
            ])
            ->add('show_best_seller', ChoiceType::class, [
                'label' => $this->trans('Number of "Best Sellers" to display', 'Modules.Dashproducts.Admin'),
                'choices' => self::CHOICES,
                'required' => false,
                'placeholder' => false,
            ])
            ->add('show_most_viewed', ChoiceType::class, [
                'label' => $this->trans('Number of "Most Viewed" to display', 'Modules.Dashproducts.Admin'),
                'choices' => self::CHOICES,
                'required' => false,
                'placeholder' => false,
            ])
            ->add('show_top_searches', ChoiceType::class, [
                'label' => $this->trans('Number of "Top Searches" to display', 'Modules.Dashproducts.Admin'),
                'choices' => self::CHOICES,
                'required' => false,
                'placeholder' => false,
            ]);
    }
}
