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
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_storeremains_guid'),
            name: 'guid',
            anchor: '99%',
            id: config.id + '-guid',
            allowBlank: false
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_storeremains_base_guid'),
            name: 'base_guid',
            anchor: '99%',
            id: config.id + '-base_guid',
            allowBlank: true
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_storeremains_catalog'),
            name: 'catalog',
            anchor: '99%',
            id: config.id + '-catalog',
            allowBlank: true
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_storeremains_article'),
            name: 'article',
            anchor: '99%',
            id: config.id + '-article',
            allowBlank: false
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_storeremains_barcode'),
            name: 'barcode',
            anchor: '99%',
            id: config.id + '-barcode',
            allowBlank: true
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_storeremains_product_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name',
            allowBlank: false
        }, {
            xtype: 'shoplogistic-combo-product',
            fieldLabel: _('shoplogistic_storeremains_product_id'),
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
            fieldLabel: _('shoplogistic_storeremains_reserved'),
            name: 'reserved',
            anchor: '99%',
            id: config.id + '-reserved',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_storeremains_available'),
            name: 'available',
            anchor: '99%',
            id: config.id + '-available',
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
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_storeremains_published'),
            name: 'published',
            id: config.id + '-published',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_storeremains_checked'),
            name: 'checked',
            id: config.id + '-checked',
            checked: false,
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
            },{
                title: _('shoplogistic_store_remains_prices'),
                items: [{
                    xtype: 'shoplogistic-grid-store-remains-prices',
                    record: config.record,
                }]
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-storeremains-update', shopLogistic.window.UpdateStoreRemains);