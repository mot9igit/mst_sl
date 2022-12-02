shopLogistic.window.CreateStoreBalance = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_storebalance_create'),
        width: 600,
        baseParams: {
            action: 'mgr/storebalance/create',
        },
    });
    shopLogistic.window.CreateStoreBalance.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoreBalance, shopLogistic.window.Default, {

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
            xtype: 'combo-balance_type',
            fieldLabel: _('shoplogistic_storebalance_type'),
            name: 'type',
            hiddenName: 'type',
            anchor: '99%',
            id: config.id + '-type',
            allowBlank: false
        }, {
            xtype: 'shoplogistic-combo-registry',
            class: 'registry',
            fieldLabel: _('shoplogistic_storebalance_registry_id'),
            name: 'registry_id',
            hiddenName: 'registry_id',
            anchor: '99%',
            id: config.id + '-registry_id',
            allowBlank: true
        },  {
            xtype: 'numberfield',
            decimalPrecision: 2,
            fieldLabel: _('shoplogistic_storebalance_value'),
            name: 'value',
            anchor: '99%',
            id: config.id + '-value',
            allowBlank: false
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_storebalance_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-storebalance-create', shopLogistic.window.CreateStoreBalance);


shopLogistic.window.UpdateStoreBalance = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_storebalance_update'),
        baseParams: {
            action: 'mgr/storebalance/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoreBalance.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreBalance, shopLogistic.window.CreateStoreBalance, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_storebalance'),
                layout: 'form',
                items: shopLogistic.window.CreateStoreBalance.prototype.getFields.call(this, config),
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-storebalance-update', shopLogistic.window.UpdateStoreBalance);