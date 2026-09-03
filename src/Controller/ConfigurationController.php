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

namespace PrestaShop\Module\DashProducts\Controller;

use Configuration;
use PrestaShop\Module\DashProducts\Type\ConfigurationType;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends FrameworkBundleAdminController
{
    private const FIELDS = [
        'DASHPRODUCT_NBR_SHOW_LAST_ORDER',
        'DASHPRODUCT_NBR_SHOW_BEST_SELLER',
        'DASHPRODUCT_NBR_SHOW_MOST_VIEWED',
        'DASHPRODUCT_NBR_SHOW_TOP_SEARCH',
    ];

    public function indexAction(Request $request): Response
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $data[$field] = (int) Configuration::get($field);
        }

        $form = $this->createForm(ConfigurationType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData() as $field => $value) {
                Configuration::updateValue($field, (int) $value);
            }
            $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

            return $this->redirectToRoute('dashproducts_configuration');
        }

        return $this->render('@Modules/dashproducts/views/templates/admin/configuration.html.twig', [
            'configurationForm' => $form->createView(),
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink('AdminDashproductsConfiguration'),
        ]);
    }
}
