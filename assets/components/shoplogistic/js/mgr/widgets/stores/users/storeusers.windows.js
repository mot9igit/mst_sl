shopLogistic.window.CreateStoreusers = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_storeuser_create'),
        width: 600,
        baseParams: {
            action: 'mgr/storeusers/create',
        },
    });
    shopLogistic.window.CreateStoreusers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoreusers, shopLogistic.window.Default, {

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
            xtype: 'shoplogistic-combo-user',
            fieldLabel: _('shoplogistic_storeuser_user_id'),
            name: 'user_id',
            anchor: '99%',
            id: config.id + '-user_id',
            allowBlank: false
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_storeuser_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-storeusers-create', shopLogistic.window.CreateStoreusers);


shopLogistic.window.UpdateStoreusers = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeusers/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoreusers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreusers, shopLogistic.window.CreateStoreusers, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_storeusers'),
                layout: 'form',
                items: shopLogistic.window.CreateStoreusers.prototype.getFields.call(this, config),
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-storeusers-update', shopLogistic.window.UpdateStoreusers);