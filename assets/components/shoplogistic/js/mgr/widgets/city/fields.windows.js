shopLogistic.window.Fields = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('add'),
        url: shopLogistic.config.connector_url,
        width:600,
        action: 'mgr/city/fields/create',
        saveBtnText:_('add'),
        fields: [{
            xtype: 'hidden',
            name: 'city',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'name',
            fieldLabel: _('shoplogistic_fields_grid_name'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'key',
            fieldLabel: _('shoplogistic_fields_grid_key'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textarea',
            name: 'value',
            fieldLabel: _('shoplogistic_fields_grid_value'),
            anchor: '99%',
            allowBlank: false
        }]
    });
    shopLogistic.window.Fields.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.Fields, MODx.Window);
Ext.reg('shoplogistic-window-fields', shopLogistic.window.Fields);

shopLogistic.window.UpdateFields = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-window-fields-update';
    }
    Ext.applyIf(config, {
        title: _('update'),
        autoHeight: true,
        fields: this.getFields(config),
        url: shopLogistic.config.connector_url,
        action: 'mgr/city/fields/update',
        width: 600
    });
    shopLogistic.window.UpdateFields.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateFields, MODx.Window, {
    getFields: function (config) {
        
        return [{
            xtype: 'hidden',
            name: 'id',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'name',
            fieldLabel: _('shoplogistic_fields_grid_name'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'key',
            fieldLabel: _('shoplogistic_fields_grid_key'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textarea',
            name: 'value',
            fieldLabel: _('shoplogistic_fields_grid_value'),
            anchor: '99%',
            allowBlank: false
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-fields-window-update', shopLogistic.window.UpdateFields);