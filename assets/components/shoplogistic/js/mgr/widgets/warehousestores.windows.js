shopLogistic.window.CreateWarehouseStores = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_warehousestore_create'),
        width: 600,
        baseParams: {
            action: 'mgr/warehousestores/create',
        },
    });
    shopLogistic.window.CreateWarehouseStores.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateWarehouseStores, shopLogistic.window.Default, {

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
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_warehousestores_store_id'),
            name: 'store_id',
            anchor: '99%',
            id: config.id + '-store_id',
            allowBlank: false
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_warehouse_sync'),
            name: 'sync',
            id: config.id + '-sync',
            checked: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_warehousestores_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-warehousestores-create', shopLogistic.window.CreateWarehouseStores);


shopLogistic.window.UpdateWarehouseStores = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/warehousestores/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateWarehouseStores.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateWarehouseStores, shopLogistic.window.CreateWarehouseStores, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_warehousestores'),
                layout: 'form',
                items: shopLogistic.window.CreateWarehouseStores.prototype.getFields.call(this, config),
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-warehousestores-update', shopLogistic.window.UpdateWarehouseStores);