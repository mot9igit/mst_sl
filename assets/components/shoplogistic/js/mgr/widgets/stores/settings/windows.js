shopLogistic.window.UpdateStoreSetting = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_setting_update'),
        baseParams: {
            action: 'mgr/store/parameters/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoreSetting.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreSetting,  shopLogistic.window.Default, {
    getFields: function (config) {
        const default_fields = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'store_id',
            id: config.id + '-store_id',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_setting_key'),
            name: 'key',
            anchor: '99%',
            id: config.id + '-key'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_setting_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_setting_label'),
            name: 'label',
            id: config.id + '-label',
            anchor: '99%'
        }];
        if(config.record.type == 1){
            default_fields.push({
                xtype: 'textfield',
                fieldLabel: _('shoplogistic_setting_value'),
                name: 'value',
                id: config.id + '-value',
                anchor: '99%'
            });
        }
        if(config.record.type == 2){
            default_fields.push({
                xtype: 'shoplogistic-combo-type-price',
                fieldLabel: _('shoplogistic_setting_value'),
                hiddenName: "value",
                store_id: config.store_id,
                name: 'value',
                id: config.id + '-value',
                anchor: '99%'
            });
        }
        if(config.record.type == 3){
            default_fields.push({
                xtype: 'xcheckbox',
                boxLabel: config.record.label,
                name: 'value',
                id: config.id + '-value',
                checked: true,
            });
        }
        if(config.record.type == 4){
            default_fields.push({
                xtype: 'numberfield',
                fieldLabel: _('shoplogistic_setting_value'),
                name: 'value',
                id: config.id + '-value',
                anchor: '99%'
            });
        }
        return default_fields;
    }
});
Ext.reg('shoplogistic-window-store-setting-update', shopLogistic.window.UpdateStoreSetting);