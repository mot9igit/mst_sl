shopLogistic.window.CreateParserdataStatuses = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_status_create'),
        width: 600,
        baseParams: {
            action: 'mgr/parserdata/statuses/create',
        },
    });
    shopLogistic.window.CreateParserdataStatuses.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateParserdataStatuses, shopLogistic.window.Default, {

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
            fieldLabel: _('shoplogistic_parserdata_status_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parserdata_status_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_parserdata_status_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'colorpalette',
            fieldLabel: _('shoplogistic_parserdata_status_color'),
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
Ext.reg('shoplogistic-window-parserdata-status-create', shopLogistic.window.CreateParserdataStatuses);


shopLogistic.window.UpdateParserdataStatuses = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parser_tasks_status_update'),
        width: 600,
        baseParams: {
            action: 'mgr/parserdata/statuses/update',
        },
    });
    shopLogistic.window.UpdateParserdataStatuses.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateParserdataStatuses, shopLogistic.window.CreateParserdataStatuses, {

    getFields: function (config) {
        return shopLogistic.window.CreateParserdataStatuses.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-parserdata-status-update', shopLogistic.window.UpdateParserdataStatuses);