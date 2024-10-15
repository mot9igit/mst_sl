shopLogistic.window.CreateOrgStores = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgstores_create'),
        width: 600,
        baseParams: {
            action: 'mgr/org/stores/create',
        },
    });
    shopLogistic.window.CreateOrgStores.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOrgStores, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'org_id',
            id: config.id + '-org_id'
        },{
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_orgstores_store_id'),
            name: 'store_id',
            id: config.id + '-store_id',
            anchor: '99%'
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_orgstores_description'),
            description: "Описание для команды подключения",
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-orgstores-create', shopLogistic.window.CreateOrgStores);


shopLogistic.window.UpdateOrgStores = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgstores_update'),
        baseParams: {
            action: 'mgr/org/stores/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOrgStores.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOrgStores, shopLogistic.window.CreateOrgStores, {

    getFields: function (config) {
        return shopLogistic.window.CreateOrgStores.prototype.getFields.call(this, config);
    }


});
Ext.reg('shoplogistic-window-orgstores-update', shopLogistic.window.UpdateOrgStores);