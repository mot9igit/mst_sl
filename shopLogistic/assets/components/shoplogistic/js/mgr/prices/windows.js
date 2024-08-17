shopLogistic.window.CreatePrices = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_prices_create'),
        width: 600,
        baseParams: {
            action: 'mgr/prices/create',
        },
    });
    shopLogistic.window.CreatePrices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreatePrices, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'product_id',
            id: config.id + '-product_id',
        }, {
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_prices_store_id'),
            name: 'store_id',
            anchor: '99%',
            id: config.id + '-store_id'
        },{
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_prices_price_rrc'),
            name: 'price_rrc',
            id: config.id + '-price_rrc',
            anchor: '99%'
        },{
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_prices_price_mrc'),
            name: 'price_mrc',
            id: config.id + '-price_mrc',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_prices_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'xdatetime',
            fieldLabel: _('shoplogistic_prices_date_from'),
            name: 'date_from',
            id: config.id + '-date_from',
            anchor: '99%'
        },{
            xtype: 'xdatetime',
            fieldLabel: _('shoplogistic_prices_date_to'),
            name: 'date_to',
            id: config.id + '-date_to',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-price-create', shopLogistic.window.CreatePrices);


shopLogistic.window.UpdatePrices = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_prices_update'),
        baseParams: {
            action: 'mgr/prices/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdatePrices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdatePrices, shopLogistic.window.CreatePrices, {

    getFields: function (config) {
        return shopLogistic.window.CreatePrices.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-price-update', shopLogistic.window.UpdatePrices);