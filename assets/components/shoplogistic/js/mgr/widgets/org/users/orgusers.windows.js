shopLogistic.window.CreateOrgUsers = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgusers_create'),
        width: 600,
        baseParams: {
            action: 'mgr/org/users/create',
        },
    });
    shopLogistic.window.CreateOrgUsers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOrgUsers, shopLogistic.window.Default, {

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
            xtype: 'shoplogistic-combo-user',
            fieldLabel: _('shoplogistic_orgusers_user_id'),
            name: 'user_id',
            id: config.id + '-user_id',
            anchor: '99%'
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_orgusers_description'),
            description: "Описание для команды подключения",
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-orgusers-create', shopLogistic.window.CreateOrgUsers);


shopLogistic.window.UpdateOrgUsers = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgusers_update'),
        baseParams: {
            action: 'mgr/org/users/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOrgUsers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOrgUsers, shopLogistic.window.CreateOrgUsers, {

    getFields: function (config) {
        return shopLogistic.window.CreateOrgUsers.prototype.getFields.call(this, config);
    }


});
Ext.reg('shoplogistic-window-orgusers-update', shopLogistic.window.UpdateOrgUsers);