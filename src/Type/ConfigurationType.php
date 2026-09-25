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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Plain AbstractType (no need for TranslatorAwareType's locales), with TranslatorInterface
 * constructor-injected directly so label strings go through an explicit trans() call —
 * required for the translation extractor to pick them up, since it only extracts ChoiceType
 * "choices", not "label" option strings.
 */
class ConfigurationType extends AbstractType
{
    private const ROW_COUNT_CHOICES = [5, 10, 20, 50];

    private const DOMAIN = 'Modules.Dashproducts.Admin';

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = array_combine(self::ROW_COUNT_CHOICES, self::ROW_COUNT_CHOICES);

        $builder
            ->add('DASHPRODUCT_NBR_SHOW_LAST_ORDER', ChoiceType::class, [
                'label' => $this->translator->trans('Number of "Recent Orders" to display', [], self::DOMAIN),
                'choices' => $choices,
            ])
            ->add('DASHPRODUCT_NBR_SHOW_BEST_SELLER', ChoiceType::class, [
                'label' => $this->translator->trans('Number of "Best Sellers" to display', [], self::DOMAIN),
                'choices' => $choices,
            ])
            ->add('DASHPRODUCT_NBR_SHOW_MOST_VIEWED', ChoiceType::class, [
                'label' => $this->translator->trans('Number of "Most Viewed" to display', [], self::DOMAIN),
                'choices' => $choices,
            ])
            ->add('DASHPRODUCT_NBR_SHOW_TOP_SEARCH', ChoiceType::class, [
                'label' => $this->translator->trans('Number of "Top Searches" to display', [], self::DOMAIN),
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
