Ext.namespace('shopLogistic.functions');

shopLogistic.window.CreateDelivery = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_delivery_create'),
        width: 900,
        baseParams: {
            action: 'mgr/delivery/create',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.CreateDelivery.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateDelivery, shopLogistic.window.Default, {
    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_delivery_create'),
                layout: 'form',
                items: shopLogistic.window.CreateDelivery.prototype.getFormFields.call(this, config),
            }]
        }]
    },
    getFormFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_delivery_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_delivery_class'),
            name: 'class',
            anchor: '99%',
            id: config.id + '-class'
        }, {
            xtype: 'modx-combo-browser',
            fieldLabel: _('shoplogistic_delivery_logo'),
            name: 'logo',
            anchor: '99%',
            id: config.id + '-logo'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_delivery_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        },{
            xtype: 'modx-texteditor',
            fieldLabel: _('shoplogistic_delivery_pack_requirements'),
            name: 'pack_requirements',
            anchor: '99%',
            id: config.id + '-pack_requirements'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_delivery_active'),
            hideLabel: true,
            name: 'active',
            id: config.id + '-active'
        }];
    }
});
Ext.reg('shoplogistic-delivery-window-create', shopLogistic.window.CreateDelivery);

shopLogistic.window.UpdateDelivery = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_delivery_update'),
            width: 900,
            action: 'mgr/delivery/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateDelivery.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateDelivery, shopLogistic.window.CreateDelivery, {

    getFields: function (config) {
        var title = _('shoplogistic_delivery_update');
        var default_tabs = [{
            title: title,
            layout: 'form',
            items: shopLogistic.window.CreateDelivery.prototype.getFormFields.call(this, config),
        }];
        return [{
            xtype: 'modx-tabs',
            items: default_tabs
        }];
    }

});
Ext.reg('shoplogistic-delivery-window-update', shopLogistic.window.UpdateDelivery);
