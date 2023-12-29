shopLogistic.window.CreateShipmentStatus = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_shipment_status_create'),
        width: 600,
        baseParams: {
            action: 'mgr/shipment_status/create',
        },
    });
    shopLogistic.window.CreateShipmentStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateShipmentStatus, shopLogistic.window.Default, {

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
            fieldLabel: _('shoplogistic_shipment_status_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_shipment_status_anchor'),
            name: 'anchor',
            id: config.id + '-anchor',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_shipment_status_anchor_description'),
            name: 'anchor_description',
            id: config.id + '-anchor_description',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_shipment_status_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_shipment_status_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'colorpalette',
            fieldLabel: _('shoplogistic_shipment_status_color'),
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
Ext.reg('shoplogistic-window-shipment-status-create', shopLogistic.window.CreateShipmentStatus);


shopLogistic.window.UpdateShipmentStatus = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/shipment_status/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateShipmentStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateShipmentStatus, shopLogistic.window.CreateShipmentStatus, {

    getFields: function (config) {
        return shopLogistic.window.CreateShipmentStatus.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-shipment-status-update', shopLogistic.window.UpdateShipmentStatus);