<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\DashProducts\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{
    private const ROW_COUNT_CHOICES = [5, 10, 20, 50];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = array_combine(self::ROW_COUNT_CHOICES, self::ROW_COUNT_CHOICES);

        $builder
            ->add('DASHPRODUCT_NBR_SHOW_LAST_ORDER', ChoiceType::class, [
                'label' => 'Number of "Recent Orders" to display',
                'choices' => $choices,
            ])
            ->add('DASHPRODUCT_NBR_SHOW_BEST_SELLER', ChoiceType::class, [
                'label' => 'Number of "Best Sellers" to display',
                'choices' => $choices,
            ])
            ->add('DASHPRODUCT_NBR_SHOW_MOST_VIEWED', ChoiceType::class, [
                'label' => 'Number of "Most Viewed" to display',
                'choices' => $choices,
            ])
            ->add('DASHPRODUCT_NBR_SHOW_TOP_SEARCH', ChoiceType::class, [
                'label' => 'Number of "Top Searches" to display',
                'choices' => $choices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => true,
            'translation_domain' => 'Modules.Dashproducts.Admin',
        ]);
    }
}
