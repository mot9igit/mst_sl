shopLogistic.panel.ResourceStores = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'shoplogistic-panel-resource-stores',
        autoHeight: true,
        layout: 'form',
        anchor: '99%',
        items: [{
            xtype: 'shoplogistic-grid-resource-stores',
            cls: 'main-wrapper',
            record: config.record
        }]
    });
    shopLogistic.panel.ResourceStores.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.ResourceStores, MODx.Panel);
Ext.reg('shoplogistic-panel-resource-stores', shopLogistic.panel.ResourceStores);

shopLogistic.panel.ResourceWarehouse = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'shoplogistic-panel-resource-warehouse',
        autoHeight: true,
        layout: 'form',
        anchor: '99%',
        items: [{
            xtype: 'shoplogistic-grid-resource-warehouse',
            cls: 'main-wrapper',
            record: config.record
        }]
    });
    shopLogistic.panel.ResourceWarehouse.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.ResourceWarehouse, MODx.Panel);
Ext.reg('shoplogistic-panel-resource-warehouse', shopLogistic.panel.ResourceWarehouse);

shopLogistic.panel.ResourcePrices = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'shoplogistic-panel-resource-prices',
        autoHeight: true,
        layout: 'form',
        anchor: '99%',
        items: [{
            xtype: 'shoplogistic-grid-prices',
            cls: 'main-wrapper',
            record: config.record
        }]
    });
    shopLogistic.panel.ResourcePrices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.ResourcePrices, MODx.Panel);
Ext.reg('shoplogistic-panel-resource-prices', shopLogistic.panel.ResourcePrices);