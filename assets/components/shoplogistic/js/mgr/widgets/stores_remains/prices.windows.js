shopLogistic.window.CreateStoresRemainsPrices = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_store_remains_prices_create'),
        width: 600,
        baseParams: {
            action: 'mgr/storeremains_prices/create',
        },
    });
    shopLogistic.window.CreateStoresRemainsPrices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoresRemainsPrices, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'remain_id',
            id: config.id + '-remain_id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_remains_prices_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_remains_prices_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%'
        }, {
            xtype: 'numberfield',
            decimalPrecision: 2,
            fieldLabel: _('shoplogistic_store_remains_prices_price'),
            name: 'price',
            anchor: '99%',
            id: config.id + '-price',
            allowBlank: true
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_store_remains_status_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-store-remains-prices-create', shopLogistic.window.CreateStoresRemainsPrices);


shopLogistic.window.UpdateStoresRemainsPrices = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains_prices/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoresRemainsPrices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoresRemainsPrices, shopLogistic.window.CreateStoresRemainsPrices, {

    getFields: function (config) {
        return shopLogistic.window.CreateStoresRemainsPrices.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-store-remains-prices-update', shopLogistic.window.UpdateStoresRemainsPrices);