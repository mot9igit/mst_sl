shopLogistic.window.CreateStoresRemainsStatuses = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_store_remains_status_create'),
        width: 600,
        baseParams: {
            action: 'mgr/storeremains_status/create',
        },
    });
    shopLogistic.window.CreateStoresRemainsStatuses.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoresRemainsStatuses, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'color',
            id: config.id + '-color'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_remains_status_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_store_remains_status_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_store_remains_status_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'colorpalette',
            fieldLabel: _('shoplogistic_store_remains_status_color'),
            id: config.id + '-color-palette',
            listeners: {
                select: function (palette, color) {
                    Ext.getCmp(config.id + '-color').setValue(color)
                },
                beforerender: function (palette) {
                    if (config.record['color'] != undefined) {
                        palette.value = config.record['color'];
                    }
                }
            },
        }];
    },
});
Ext.reg('shoplogistic-window-store-remains-status-create', shopLogistic.window.CreateStoresRemainsStatuses);


shopLogistic.window.UpdateStatusBalancePayRequest = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains_status/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStatusBalancePayRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStatusBalancePayRequest, shopLogistic.window.CreateStoresRemainsStatuses, {

    getFields: function (config) {
        return shopLogistic.window.CreateStoresRemainsStatuses.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-store-remains-status-update', shopLogistic.window.UpdateStatusBalancePayRequest);