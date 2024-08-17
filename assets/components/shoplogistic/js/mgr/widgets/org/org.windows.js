shopLogistic.window.CreateOrg = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_org_create'),
        width: 1000,
        baseParams: {
            action: 'mgr/org/create',
        },
    });
    shopLogistic.window.CreateOrg.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOrg, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_org_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_org_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            title: 'Роли',
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .3,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    fieldLabel: _('shoplogistic_store_store'),
                    name: 'store',
                    id: config.id + '-store',
                    anchor: '99%',
                    checked: true
                }]
            },{
                columnWidth: .3,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    fieldLabel: _('shoplogistic_store_warehouse'),
                    name: 'warehouse',
                    id: config.id + '-warehouse',
                    anchor: '99%',
                    checked: false
                }]
            },{
                columnWidth: .3,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    fieldLabel: _('shoplogistic_store_vendor'),
                    name: 'vendor',
                    id: config.id + '-vendor',
                    anchor: '99%',
                    checked: false
                }]
            }]
        },
        {
            xtype: 'dart-image-field',
            fieldLabel: _('shoplogistic_org_image'),
            name: 'image',
            id: config.id + '-image',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_org_contact'),
            name: 'contact',
            id: config.id + '-contact',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_org_email'),
            name: 'email',
            id: config.id + '-email',
            anchor: '99%'
        },
        {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_org_phone'),
            name: 'phone',
            id: config.id + '-phone',
            anchor: '99%'
        },];
    },
});
Ext.reg('shoplogistic-window-org-create', shopLogistic.window.CreateOrg);


shopLogistic.window.UpdateOrg = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_org_update'),
        width: 1000,
        baseParams: {
            action: 'mgr/org/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOrg.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOrg, shopLogistic.window.CreateOrg, {

    // getFields: function (config) {
    //     return shopLogistic.window.CreateOrg.prototype.getFields.call(this, config);
    // }

    getFields: function (config) {

        var default_tabs = [{
            title: _('shoplogistic_org_update'),
            layout: 'form',
            items: shopLogistic.window.CreateOrg.prototype.getFields.call(this, config)
        }, {
            title: _('shoplogistic_org_requisites'),
            items: [{
                xtype: 'shoplogistic-grid-orgrequisites',
                record: config.record,
            }]
        },
            {
                title: _('shoplogistic_stores'),
                items: [{
                    xtype: 'shoplogistic-grid-orgstores',
                    record: config.record,
                }]
            },
            {
                title: _('shoplogistic_org_users'),
                items: [{
                    xtype: 'shoplogistic-grid-orgusers',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_storebalance'),
                items: [{
                    xtype: 'shoplogistic-grid-storebalance',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_storeregistry'),
                items: [{
                    xtype: 'shoplogistic-grid-storeregistry',
                    record: config.record,
                }]
            }
        ];

        return [{
            xtype: 'modx-tabs',
            items: default_tabs
        }];
    }

});
Ext.reg('shoplogistic-window-org-update', shopLogistic.window.UpdateOrg);