shopLogistic.window.CreateStatusConnection = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_connection_status_create'),
        width: 600,
        baseParams: {
            action: 'mgr/connection_status/create',
        },
    });
    shopLogistic.window.CreateStatusConnection.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStatusConnection, shopLogistic.window.Default, {

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
            fieldLabel: _('shoplogistic_export_file_status_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_export_file_status_anchor'),
            name: 'anchor',
            id: config.id + '-anchor',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_export_file_status_anchor_description'),
            name: 'anchor_description',
            id: config.id + '-anchor_description',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_export_file_status_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_export_file_status_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'colorpalette',
            fieldLabel: _('shoplogistic_export_file_status_color'),
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
Ext.reg('shoplogistic-window-connection-status-create', shopLogistic.window.CreateStatusConnection);


shopLogistic.window.UpdateStatusConnection = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/connection_status/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStatusConnection.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStatusConnection, shopLogistic.window.CreateStatusConnection, {

    getFields: function (config) {
        return shopLogistic.window.CreateStatusConnection.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-connection-status-update', shopLogistic.window.UpdateStatusConnection);