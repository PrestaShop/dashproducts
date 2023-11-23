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
if (!defined('_PS_VERSION_')) {
    exit;
}

class dashproducts extends Module
{
    public function __construct()
    {
        $this->name = 'dashproducts';
        $this->tab = 'administration';
        $this->version = '2.1.4';
        $this->author = 'PrestaShop';

        parent::__construct();
        $this->displayName = $this->trans('Dashboard Products', [], 'Modules.Dashproducts.Admin');
        $this->description = $this->trans('Enrich your stats: display a table of your latest orders and a ranking of your products.', [], 'Modules.Dashproducts.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        Configuration::updateValue('DASHPRODUCT_NBR_SHOW_LAST_ORDER', 10);
        Configuration::updateValue('DASHPRODUCT_NBR_SHOW_BEST_SELLER', 10);
        Configuration::updateValue('DASHPRODUCT_NBR_SHOW_MOST_VIEWED', 10);
        Configuration::updateValue('DASHPRODUCT_NBR_SHOW_TOP_SEARCH', 10);

        return parent::install()
            && $this->registerHook('dashboardZoneTwo')
            && $this->registerHook('dashboardData')
        ;
    }

    public function hookDashboardZoneTwo($params)
    {
        $this->context->smarty->assign(
            [
                'DASHACTIVITY_CART_ACTIVE' => Configuration::get('DASHACTIVITY_CART_ACTIVE'),
                'DASHACTIVITY_VISITOR_ONLINE' => Configuration::get('DASHACTIVITY_VISITOR_ONLINE'),
                'DASHPRODUCT_NBR_SHOW_LAST_ORDER' => Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'),
                'DASHPRODUCT_NBR_SHOW_BEST_SELLER' => Configuration::get('DASHPRODUCT_NBR_SHOW_BEST_SELLER'),
                'DASHPRODUCT_NBR_SHOW_TOP_SEARCH' => Configuration::get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'),
                'date_from' => Tools::displayDate($params['date_from']),
                'date_to' => Tools::displayDate($params['date_to']),
                'dashproducts_config_form' => $this->getPermission('configure') ? $this->renderConfigForm() : null,
            ]
        );

        return $this->display(__FILE__, 'dashboard_zone_two.tpl');
    }

    public function hookDashboardData($params)
    {
        $table_recent_orders = $this->getTableRecentOrders();
        $table_best_sellers = $this->getTableBestSellers($params['date_from'], $params['date_to']);
        $table_most_viewed = $this->getTableMostViewed($params['date_from'], $params['date_to']);
        $table_top_10_most_search = $this->getTableTop10MostSearch($params['date_from'], $params['date_to']);

        return [
            'data_table' => [
                'table_recent_orders' => $table_recent_orders,
                'table_best_sellers' => $table_best_sellers,
                'table_most_viewed' => $table_most_viewed,
                'table_top_10_most_search' => $table_top_10_most_search,
            ],
        ];
    }

    public function getTableRecentOrders()
    {
        $header = [
            ['title' => $this->trans('Customer Name', [], 'Modules.Dashproducts.Admin'), 'class' => 'text-left'],
            ['title' => $this->trans('Products', [], 'Admin.Global'), 'class' => 'text-center'],
            ['title' => $this->trans('Total', [], 'Admin.Global') . ' ' . $this->trans('Tax excl.', [], 'Admin.Global'), 'class' => 'text-center'],
            ['title' => $this->trans('Date', [], 'Admin.Global'), 'class' => 'text-center'],
            ['title' => $this->trans('Status', [], 'Admin.Global'), 'class' => 'text-center'],
            ['title' => '', 'class' => 'text-right'],
        ];

        $limit = (int) Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER') ? (int) Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER') : 10;
        $orders = Order::getOrdersWithInformations($limit);

        $body = [];
        foreach ($orders as $order) {
            /** @var array $currency */
            $currency = Currency::getCurrency((int) $order['id_currency']);
            $tr = [];
            $tr[] = [
                'id' => 'firstname_lastname',
                'value' => !empty($order['id_customer']) ? sprintf(
                    '<a href="%s">%s %s</a>',
                    $this->context->link->getAdminLink('AdminCustomers', true, [
                        'route' => 'admin_customers_view',
                        'customerId' => $order['id_customer'],
                    ]),
                    Tools::htmlentitiesUTF8($order['firstname']),
                    Tools::htmlentitiesUTF8($order['lastname'])
                ) : '',
                'class' => 'text-left',
            ];
            $tr[] = [
                'id' => 'total_products',
                'value' => count(OrderDetail::getList((int) $order['id_order'])),
                'class' => 'text-center',
            ];
            $tr[] = [
                'id' => 'total_paid',
                'value' => $this->context->getCurrentLocale()->formatPrice((float) $order['total_paid_tax_excl'], $currency['iso_code']),
                'class' => 'text-center',
                'wrapper_start' => $order['valid'] ? '<span class="badge badge-success">' : '',
                'wrapper_end' => '<span>',
            ];
            $tr[] = [
                'id' => 'date_add',
                'value' => Tools::displayDate($order['date_add']),
                'class' => 'text-center',
            ];
            $tr[] = [
                'id' => 'status',
                'value' => Tools::htmlentitiesUTF8($order['state_name']),
                'class' => 'text-center',
            ];
            $tr[] = [
                'id' => 'details',
                'value' => '',
                'class' => 'text-right',
                'wrapper_start' => '<a class="btn btn-default" href="index.php?controller=AdminOrders&id_order=' . (int) $order['id_order'] . '&vieworder&token=' . Tools::getAdminTokenLite('AdminOrders') . '" title="' . $this->trans('Details', [], 'Modules.Dashproducts.Admin') . '"><i class="icon-search"></i>',
                'wrapper_end' => '</a>',
            ];

            $body[] = $tr;
        }

        return ['header' => $header, 'body' => $body];
    }

    public function getTableBestSellers($date_from, $date_to)
    {
        $header = [
            [
                'id' => 'image',
                'title' => $this->trans('Image', [], 'Admin.Global'),
                'class' => 'text-center',
            ],
            [
                'id' => 'product',
                'title' => $this->trans('Product', [], 'Admin.Global'),
                'class' => 'text-center',
            ],
            [
                'id' => 'category',
                'title' => $this->trans('Category', [], 'Admin.Catalog.Feature'),
                'class' => 'text-center',
            ],
            [
                'id' => 'total_sold',
                'title' => $this->trans('Total sold', [], 'Modules.Dashproducts.Admin'),
                'class' => 'text-center',
            ],
            [
                'id' => 'sales',
                'title' => $this->trans('Sales', [], 'Admin.Global'),
                'class' => 'text-center',
            ],
            [
                'id' => 'net_profit',
                'title' => $this->trans('Net profit', [], 'Modules.Dashproducts.Admin'),
                'class' => 'text-center',
            ],
        ];

        $products = Db::getInstance()->ExecuteS(
            '
					SELECT
						product_id,
						product_name,
						SUM(product_quantity-product_quantity_refunded-product_quantity_return-product_quantity_reinjected) as total,
						p.price as price,
						pa.price as price_attribute,
						SUM(total_price_tax_excl / conversion_rate) as sales,
						SUM(product_quantity * purchase_supplier_price / conversion_rate) as expenses
					FROM `' . _DB_PREFIX_ . 'orders` o
		LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` od ON o.id_order = od.id_order
		LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = product_id
		LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.id_product_attribute = od.product_attribute_id
		WHERE `invoice_date` BETWEEN "' . pSQL($date_from) . ' 00:00:00" AND "' . pSQL($date_to) . ' 23:59:59"
		AND valid = 1
		' . Shop::addSqlRestriction(false, 'o') . '
		GROUP BY product_id, product_attribute_id
		ORDER BY total DESC
		LIMIT ' . (int) Configuration::get('DASHPRODUCT_NBR_SHOW_BEST_SELLER')
        );

        $body = [];
        foreach ($products as $product) {
            $product_obj = new Product((int) $product['product_id'], false, $this->context->language->id);
            if (!Validate::isLoadedObject($product_obj)) {
                continue;
            }

            $productCategoryId = $product_obj->getDefaultCategory();
            if (is_array($productCategoryId) && isset($productCategoryId['id_category_default'])) {
                $productCategoryId = $productCategoryId['id_category_default'];
            }
            $category = new Category($productCategoryId, $this->context->language->id);

            $img = '';
            if (($row_image = Product::getCover($product_obj->id)) && $row_image['id_image']) {
                $image = new Image((int) $row_image['id_image']);
                $path_to_image = (defined('_PS_PRODUCT_IMG_DIR_') ? _PS_PRODUCT_IMG_DIR_ : _PS_PROD_IMG_DIR_) . $image->getExistingImgPath() . '.' . $this->context->controller->imageType;
                $img = ImageManager::thumbnail($path_to_image, 'product_mini_' . $row_image['id_image'] . '.' . $this->context->controller->imageType, 45, $this->context->controller->imageType);
            }

            $productPrice = $product['price'];
            if (isset($product['price_attribute']) && $product['price_attribute'] != '0.000000') {
                $productPrice = $product['price_attribute'];
            }

            $body[] = [
                [
                    'id' => 'product',
                    'value' => $img,
                    'class' => 'text-center',
                ],
                [
                    'id' => 'product',
                    'value' => '<a href="' . $this->context->link->getAdminLink('AdminProducts', true, ['id_product' => $product_obj->id, 'updateproduct' => 1]) . '">' . Tools::htmlentitiesUTF8($product['product_name']) . '</a>' . '<br/>' .
            $this->context->getCurrentLocale()->formatPrice($productPrice, $this->context->currency->iso_code),
                    'class' => 'text-center',
                ],
                [
                    'id' => 'category',
                    'value' => $category->name,
                    'class' => 'text-center',
                ],
                [
                    'id' => 'total_sold',
                    'value' => $product['total'],
                    'class' => 'text-center',
                ],
                [
                    'id' => 'sales',
                    'value' => $this->context->getCurrentLocale()->formatPrice($product['sales'], $this->context->currency->iso_code),
                    'class' => 'text-center',
                ],
                [
                    'id' => 'net_profit',
                    'value' => $this->context->getCurrentLocale()->formatPrice(($product['sales'] - $product['expenses']), $this->context->currency->iso_code),
                    'class' => 'text-center',
                ],
            ];
        }

        return ['header' => $header, 'body' => $body];
    }

    public function getTableMostViewed($date_from, $date_to)
    {
        $header = [
            [
                'id' => 'image',
                'title' => $this->trans('Image', [], 'Admin.Global'),
                'class' => 'text-center',
            ],
            [
                'id' => 'product',
                'title' => $this->trans('Product', [], 'Admin.Global'),
                'class' => 'text-center',
            ],
            [
                'id' => 'views',
                'title' => $this->trans('Views', [], 'Modules.Dashproducts.Admin'),
                'class' => 'text-center',
            ],
            [
                'id' => 'added_to_cart',
                'title' => $this->trans('Added to cart', [], 'Modules.Dashproducts.Admin'),
                'class' => 'text-center',
            ],
            [
                'id' => 'purchased',
                'title' => $this->trans('Purchased', [], 'Modules.Dashproducts.Admin'),
                'class' => 'text-center',
            ],
            [
                'id' => 'rate',
                'title' => $this->trans('Percentage', [], 'Admin.Global'),
                'class' => 'text-center',
            ],
        ];

        if (Configuration::get('PS_STATSDATA_PAGESVIEWS')) {
            $products = $this->getTotalViewed($date_from, $date_to, (int) Configuration::get('DASHPRODUCT_NBR_SHOW_MOST_VIEWED'));
            $body = [];
            if (is_array($products) && count($products)) {
                foreach ($products as $product) {
                    $product_obj = new Product((int) $product['id_object'], true, $this->context->language->id);
                    if (!Validate::isLoadedObject($product_obj)) {
                        continue;
                    }

                    $img = '';
                    if (($row_image = Product::getCover($product_obj->id)) && $row_image['id_image']) {
                        $image = new Image((int) $row_image['id_image']);
                        $path_to_image = (defined('_PS_PRODUCT_IMG_DIR_') ? _PS_PRODUCT_IMG_DIR_ : _PS_PROD_IMG_DIR_) . $image->getExistingImgPath() . '.' . $this->context->controller->imageType;
                        $img = ImageManager::thumbnail(
                            $path_to_image,
                            'product_mini_' . $product_obj->id . '.' . $this->context->controller->imageType,
                            45,
                            $this->context->controller->imageType
                        );
                    }

                    $tr = [];
                    $tr[] = [
                        'id' => 'product',
                        'value' => $img,
                        'class' => 'text-center',
                    ];
                    $tr[] = [
                        'id' => 'product',
                        'value' => Tools::htmlentitiesUTF8($product_obj->name) . '<br/>' . $this->context->getCurrentLocale()->formatPrice(Product::getPriceStatic((int) $product_obj->id), $this->context->currency->iso_code),
                        'class' => 'text-center',
                    ];
                    $tr[] = [
                        'id' => 'views',
                        'value' => $product['counter'],
                        'class' => 'text-center',
                    ];
                    $added_cart = $this->getTotalProductAddedCart($date_from, $date_to, (int) $product_obj->id);
                    $tr[] = [
                        'id' => 'added_to_cart',
                        'value' => $added_cart,
                        'class' => 'text-center',
                    ];
                    $purchased = $this->getTotalProductPurchased($date_from, $date_to, (int) $product_obj->id);
                    $tr[] = [
                        'id' => 'purchased',
                        'value' => $this->getTotalProductPurchased($date_from, $date_to, (int) $product_obj->id),
                        'class' => 'text-center',
                    ];
                    $tr[] = [
                        'id' => 'rate',
                        'value' => ($product['counter'] ? round(100 * $purchased / $product['counter'], 1) . '%' : '-'),
                        'class' => 'text-center',
                    ];
                    $body[] = $tr;
                }
            }
        } else {
            $body = '<div class="alert alert-info">' . $this->trans('You must enable the "Save global page views" option from the "Data mining for statistics" module in order to display the most viewed products, or use the Google Analytics module.', [], 'Modules.Dashproducts.Admin') . '</div>';
        }

        return ['header' => $header, 'body' => $body];
    }

    public function getTableTop10MostSearch($date_from, $date_to)
    {
        $header = [
            [
                'id' => 'reference',
                'title' => $this->trans('Term', [], 'Modules.Dashproducts.Admin'),
                'class' => 'text-left',
            ],
            [
                'id' => 'name',
                'title' => $this->trans('Search', [], 'Admin.Shopparameters.Feature'),
                'class' => 'text-center',
            ],
            [
                'id' => 'totalQuantitySold',
                'title' => $this->trans('Results', [], 'Modules.Dashproducts.Admin'),
                'class' => 'text-center',
            ],
        ];

        $terms = $this->getMostSearchTerms($date_from, $date_to, (int) Configuration::get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'));
        $body = [];
        if (is_array($terms) && count($terms)) {
            foreach ($terms as $term) {
                $tr = [];
                $tr[] = [
                    'id' => 'product',
                    'value' => $term['keywords'],
                    'class' => 'text-left',
                ];
                $tr[] = [
                    'id' => 'product',
                    'value' => $term['count_keywords'],
                    'class' => 'text-center',
                ];
                $tr[] = [
                    'id' => 'product',
                    'value' => $term['results'],
                    'class' => 'text-center',
                ];
                $body[] = $tr;
            }
        }

        return ['header' => $header, 'body' => $body];
    }

    public function getTotalProductSales($date_from, $date_to, $id_product)
    {
        $sql = 'SELECT SUM(od.`product_quantity` * od.`product_price`) AS total
				FROM `' . _DB_PREFIX_ . 'order_detail` od
				JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
				WHERE od.`product_id` = ' . (int) $id_product . '
					' . Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o') . '
					AND o.valid = 1
					AND o.`date_add` BETWEEN "' . pSQL($date_from) . '" AND "' . pSQL($date_to) . '"';

        return (int) Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public function getTotalProductAddedCart($date_from, $date_to, $id_product)
    {
        return Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue('
		SELECT count(`id_product`) as count
		FROM `' . _DB_PREFIX_ . 'cart_product` cp
		WHERE cp.`id_product` = ' . (int) $id_product . '
		' . Shop::addSqlRestriction(false, 'cp') . '
		AND cp.`date_add` BETWEEN "' . pSQL($date_from) . '" AND "' . pSQL($date_to) . '"');
    }

    public function getTotalProductPurchased($date_from, $date_to, $id_product)
    {
        return Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue('
		SELECT count(`product_id`) as count
		FROM `' . _DB_PREFIX_ . 'order_detail` od
		JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
		WHERE od.`product_id` = ' . (int) $id_product . '
		' . Shop::addSqlRestriction(false, 'od') . '
		AND o.valid = 1
		AND o.`date_add` BETWEEN "' . pSQL($date_from) . '" AND "' . pSQL($date_to) . '"');
    }

    public function getTotalViewed($date_from, $date_to, $limit = 10)
    {
        return Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS('
        SELECT p.id_object, pv.counter
        FROM `' . _DB_PREFIX_ . 'page_viewed` pv
        LEFT JOIN `' . _DB_PREFIX_ . 'date_range` dr ON pv.`id_date_range` = dr.`id_date_range`
        LEFT JOIN `' . _DB_PREFIX_ . 'page` p ON pv.`id_page` = p.`id_page`
        LEFT JOIN `' . _DB_PREFIX_ . 'page_type` pt ON pt.`id_page_type` = p.`id_page_type`
        WHERE pt.`name` = \'product\'
        ' . Shop::addSqlRestriction(false, 'pv') . '
        AND dr.`time_start` BETWEEN "' . pSQL($date_from) . '" AND "' . pSQL($date_to) . '"
        AND dr.`time_end` BETWEEN "' . pSQL($date_from) . '" AND "' . pSQL($date_to) . '"
        ORDER BY pv.counter DESC
        LIMIT ' . (int) $limit);
    }

    public function getMostSearchTerms($date_from, $date_to, $limit = 10)
    {
        if (!Module::isInstalled('statssearch')) {
            return [];
        }

        return Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS('
		SELECT `keywords`, count(`id_statssearch`) as count_keywords, `results`
		FROM `' . _DB_PREFIX_ . 'statssearch` ss
		WHERE ss.`date_add` BETWEEN "' . pSQL($date_from) . '" AND "' . pSQL($date_to) . '"
		' . Shop::addSqlRestriction(false, 'ss') . '
		GROUP BY ss.`keywords`
		ORDER BY `count_keywords` DESC
		LIMIT ' . (int) $limit);
    }

    public function renderConfigForm()
    {
        $fields_form = [
            'form' => [
                'input' => [],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                    'class' => 'btn btn-default pull-right submit_dash_config',
                    'reset' => [
                        'title' => $this->trans('Cancel', [], 'Admin.Actions'),
                        'class' => 'btn btn-default cancel_dash_config',
                    ],
                ],
            ],
        ];

        $inputs = [
            [
                'label' => $this->trans('Number of "Recent Orders" to display', [], 'Modules.Dashproducts.Admin'),
                'config_name' => 'DASHPRODUCT_NBR_SHOW_LAST_ORDER',
            ],
            [
                'label' => $this->trans('Number of "Best Sellers" to display', [], 'Modules.Dashproducts.Admin'),
                'config_name' => 'DASHPRODUCT_NBR_SHOW_BEST_SELLER',
            ],
            [
                'label' => $this->trans('Number of "Most Viewed" to display', [], 'Modules.Dashproducts.Admin'),
                'config_name' => 'DASHPRODUCT_NBR_SHOW_MOST_VIEWED',
            ],
            [
                'label' => $this->trans('Number of "Top Searches" to display', [], 'Modules.Dashproducts.Admin'),
                'config_name' => 'DASHPRODUCT_NBR_SHOW_TOP_SEARCH',
            ],
        ];

        foreach ($inputs as $input) {
            $fields_form['form']['input'][] = [
                'type' => 'select',
                'label' => $input['label'],
                'name' => $input['config_name'],
                'options' => [
                    'query' => [
                        ['id' => 5, 'name' => 5],
                        ['id' => 10, 'name' => 10],
                        ['id' => 20, 'name' => 20],
                        ['id' => 50, 'name' => 50],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ];
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDashConfig';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues()
    {
        return [
            'DASHPRODUCT_NBR_SHOW_LAST_ORDER' => Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'),
            'DASHPRODUCT_NBR_SHOW_BEST_SELLER' => Configuration::get('DASHPRODUCT_NBR_SHOW_BEST_SELLER'),
            'DASHPRODUCT_NBR_SHOW_MOST_VIEWED' => Configuration::get('DASHPRODUCT_NBR_SHOW_MOST_VIEWED'),
            'DASHPRODUCT_NBR_SHOW_TOP_SEARCH' => Configuration::get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'),
        ];
    }

    /**
     * Validate dashboard configuration
     *
     * @param array $config
     *
     * @return array
     */
    public function validateDashConfig(array $config)
    {
        $errors = [];
        $possibleValues = [5, 10, 20, 50];
        foreach (array_keys($this->getConfigFieldsValues()) as $fieldName) {
            if (!isset($config[$fieldName]) || !in_array($config[$fieldName], $possibleValues)) {
                $errors[$fieldName] = $this->trans('The %s field is invalid.', [$fieldName], 'Admin.Notifications.Error');
            }
        }

        return $errors;
    }

    /**
     * Save dashboard configuration
     *
     * @param array $config
     *
     * @return bool determines if there are errors or not
     */
    public function saveDashConfig(array $config)
    {
        if (!$this->getPermission('configure')) {
            return true;
        }

        foreach (array_keys($this->getConfigFieldsValues()) as $fieldName) {
            Configuration::updateValue($fieldName, (int) $config[$fieldName]);
        }

        return false;
    }
}
