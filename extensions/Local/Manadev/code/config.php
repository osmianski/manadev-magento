<?php

return array(
    // downloadable_link_purchased_item DB table columns
    'columns' => array(
        // Git branch <-> DB column group prefix
        'master' => 'link',
        'magento24' => 'm_link24',
        'magento23' => 'm_link23',
    ),

    // The following is merged into each product configuration
    'defaults' => array(
        // Git branch <-> download button label
        'master' => 'Download',
    ),

    // Custom configuration for each product. If not listed here,
    // product will have a configuration from defaults section
    'products' => array(
        // product SKU <-> product configuration, ordered by product ID
        'layered-navigation-filters-multiple-select-magento-2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'multiple-select-checkboxes-for-layered-navigation-magento2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'mobile-layered-navigation-magento2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'horizontal-layered-navigation-positioning-magento2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'price-slider-for-layered-navigation-magento2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'search-engine-friendly-layered-navigation-links-magento-2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'seo-layered-navigation-plus-magento-2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'filter-specific-content-magento2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'seo-layered-navigation-enterprise-magento-2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
        'shop-by-brand-magento-2' => array(
            // Git branch <-> download button label
            'master' => 'Download for Magento 2.4.4 and later',
            'magento24' => 'Download for Magento 2.4.0 - 2.4.3',
            'magento23' => 'Download for Magento 2.2 - 2.3',
        ),
    ),
);