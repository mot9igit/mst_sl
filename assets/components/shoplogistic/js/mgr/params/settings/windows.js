shopLogistic.window.CreateSetting = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_setting_create'),
        width: 600,
        baseParams: {
            action: 'mgr/parameters/params/create',
        },
    });
    shopLogistic.window.CreateSetting.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateSetting, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_setting_key'),
            name: 'key',
            anchor: '99%',
            id: config.id + '-key'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_setting_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name'
        }, {
            xtype: 'shoplogistic-combo-setting-group',
            fieldLabel: _('shoplogistic_setting_group'),
            hiddenName: 'group',
            anchor: '99%',
            id: config.id + '-group'
        }, {
            xtype: 'shoplogistic-combo-setting-type',
            fieldLabel: _('shoplogistic_setting_type'),
            hiddenName: 'type',
            anchor: '99%',
            id: config.id + '-type'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_setting_label'),
            name: 'label',
            id: config.id + '-label',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_setting_default'),
            name: 'default',
            id: config.id + '-default',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_setting_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_setting_profile_hidden'),
            name: 'profile_hidden',
            id: config.id + '-profile_hidden',
            checked: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_setting_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-setting-create', shopLogistic.window.CreateSetting);


shopLogistic.window.UpdateSetting = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_setting_update'),
        baseParams: {
            action: 'mgr/parameters/params/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateSetting.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateSetting, shopLogistic.window.CreateSetting, {

    getFields: function (config) {
        return shopLogistic.window.CreateSetting.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-setting-update', shopLogistic.window.UpdateSetting);