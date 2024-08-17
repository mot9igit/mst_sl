shopLogistic.window.CreatePage = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_page_create'),
        width: 600,
        baseParams: {
            action: 'mgr/adv/pages/create',
        },
    });
    shopLogistic.window.CreatePage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreatePage, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_page_resource_id'),
            name: 'resource_id',
            id: config.id + '-resource_id',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_page_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_page_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_page_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-page-create', shopLogistic.window.CreatePage);


shopLogistic.window.UpdatePage = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_page_update'),
        baseParams: {
            action: 'mgr/adv/pages/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdatePage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdatePage, shopLogistic.window.CreatePage, {

    getFields: function (config) {
        return shopLogistic.window.CreatePage.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-page-update', shopLogistic.window.UpdatePage);