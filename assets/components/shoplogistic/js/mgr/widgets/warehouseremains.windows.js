shopLogistic.window.CreateWarehouseRemains = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_warehouseremains_create'),
        width: 600,
        baseParams: {
            action: 'mgr/warehouseremains/create',
        },
    });
    shopLogistic.window.CreateWarehouseRemains.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateWarehouseRemains, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'warehouse_id',
            id: config.id + '-warehouse_id'
        }, {
            xtype: 'shoplogistic-combo-product',
            fieldLabel: _('shoplogistic_warehouseremains_product_name'),
            name: 'product_id',
            anchor: '99%',
            id: config.id + '-product_id',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_warehouseremains_remains'),
            name: 'remains',
            anchor: '99%',
            id: config.id + '-remains',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            decimalPrecision: 2,
            fieldLabel: _('shoplogistic_warehouseremains_price'),
            name: 'price',
            anchor: '99%',
            id: config.id + '-price',
            allowBlank: true
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_warehouseuser_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-warehouseremains-create', shopLogistic.window.CreateWarehouseRemains);


shopLogistic.window.UpdateWarehouseRemains = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/warehouseremains/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateWarehouseRemains.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateWarehouseRemains, shopLogistic.window.CreateWarehouseRemains, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_warehouseremains'),
                layout: 'form',
                items: shopLogistic.window.CreateWarehouseRemains.prototype.getFields.call(this, config),
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-warehouseremains-update', shopLogistic.window.UpdateWarehouseRemains);