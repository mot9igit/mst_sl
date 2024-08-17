shopLogistic.window.CreateStoreRegistry = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_storeregistry_create'),
        width: 600,
        baseParams: {
            action: 'mgr/org/registry/create',
        },
    });
    shopLogistic.window.CreateStoreRegistry.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoreRegistry, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'org_id',
            id: config.id + '-org_id'
        }, {
            xtype: 'xdatetime',
            fieldLabel: _('shoplogistic_storeregistry_datefrom'),
            name: 'date_from',
            anchor: '99%',
            id: config.id + '-date_from',
            allowBlank: false
        },  {
            xtype: 'xdatetime',
            fieldLabel: _('shoplogistic_storeregistry_dateto'),
            name: 'date_to',
            anchor: '99%',
            id: config.id + '-date_to',
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
Ext.reg('shoplogistic-window-storeregistry-create', shopLogistic.window.CreateStoreRegistry);