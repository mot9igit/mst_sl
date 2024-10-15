shopLogistic.window.CreateSettingGroup = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_settings_group_create'),
        width: 600,
        baseParams: {
            action: 'mgr/parameters/groups/create',
        },
    });
    shopLogistic.window.CreateSettingGroup.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateSettingGroup, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_settings_group_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_settings_group_label'),
            name: 'label',
            id: config.id + '-label',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_settings_group_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_settings_group_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_settings_group_rank'),
            name: 'rank',
            id: config.id + '-rank',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_settings_group_profile_hidden'),
            name: 'profile_hidden',
            id: config.id + '-profile_hidden',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-setting-group-create', shopLogistic.window.CreateSettingGroup);


shopLogistic.window.UpdateSettingGroup = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_settings_group_update'),
        baseParams: {
            action: 'mgr/parameters/groups/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateSettingGroup.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateSettingGroup, shopLogistic.window.CreateSettingGroup, {

    getFields: function (config) {
        return shopLogistic.window.CreateSettingGroup.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-setting-group-update', shopLogistic.window.UpdateSettingGroup);