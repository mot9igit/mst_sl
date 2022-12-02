shopLogistic.window.CreateStoreRemains = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_storeremains_create'),
        width: 600,
        baseParams: {
            action: 'mgr/storeremains/create',
        },
    });
    shopLogistic.window.CreateStoreRemains.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoreRemains, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'store_id',
            id: config.id + '-store_id'
        }, {
            xtype: 'shoplogistic-combo-product',
            fieldLabel: _('shoplogistic_storeremains_product_name'),
            name: 'product_id',
            anchor: '99%',
            id: config.id + '-product_id',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_storeremains_remains'),
            name: 'remains',
            anchor: '99%',
            id: config.id + '-remains',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            decimalPrecision: 2,
            fieldLabel: _('shoplogistic_storeremains_price'),
            name: 'price',
            anchor: '99%',
            id: config.id + '-price',
            allowBlank: true
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_storeuser_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-storeremains-create', shopLogistic.window.CreateStoreRemains);


shopLogistic.window.UpdateStoreRemains = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoreRemains.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreRemains, shopLogistic.window.CreateStoreRemains, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_storeremains'),
                layout: 'form',
                items: shopLogistic.window.CreateStoreRemains.prototype.getFields.call(this, config),
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-storeremains-update', shopLogistic.window.UpdateStoreRemains);