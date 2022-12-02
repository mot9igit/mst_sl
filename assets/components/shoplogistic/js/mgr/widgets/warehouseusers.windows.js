shopLogistic.window.CreateWarehouseUsers = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_warehouseuser_create'),
        width: 600,
        baseParams: {
            action: 'mgr/warehouseusers/create',
        },
    });
    shopLogistic.window.CreateWarehouseUsers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateWarehouseUsers, shopLogistic.window.Default, {

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
            xtype: 'shoplogistic-combo-user',
            fieldLabel: _('shoplogistic_warehouseuser_user_id'),
            name: 'user_id',
            anchor: '99%',
            id: config.id + '-user_id',
            allowBlank: false
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_warehouseuser_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-warehouseusers-create', shopLogistic.window.CreateWarehouseUsers);


shopLogistic.window.UpdateWarehouseUsers = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/warehouseusers/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateWarehouseUsers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateWarehouseUsers, shopLogistic.window.CreateWarehouseUsers, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_warehouseusers'),
                layout: 'form',
                items: shopLogistic.window.CreateWarehouseUsers.prototype.getFields.call(this, config),
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-warehouseusers-update', shopLogistic.window.UpdateWarehouseUsers);