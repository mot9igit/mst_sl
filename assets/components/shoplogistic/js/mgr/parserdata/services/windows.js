shopLogistic.window.CreateParserdataServices = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_service_create'),
        width: 600,
        baseParams: {
            action: 'mgr/parserdata/services/create',
        },
    });
    shopLogistic.window.CreateParserdataServices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateParserdataServices, shopLogistic.window.Default, {

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
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_parserdata_service_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_parserdata_service_key'),
            name: 'service_key',
            id: config.id + '-service_key',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_parserdata_service_url'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parserdata_service_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_parserdata_service_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-parserdata-service-create', shopLogistic.window.CreateParserdataServices);


shopLogistic.window.UpdateParserdataServices = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_service_update'),
        width: 600,
        maxHeight: 400,
        baseParams: {
            action: 'mgr/parserdata/service/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateParserdataServices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateParserdataServices, shopLogistic.window.CreateParserdataServices, {

    getFields: function (config) {

        return {
            xtype: 'modx-tabs',
            autoHeight: true,
            deferredRender: false,
            forceLayout: true,
            width: '98%',
            bodyStyle: 'padding: 10px 10px 10px 10px;',
            border: true,
            defaults: {
                border: false,
                autoHeight: true,
                bodyStyle: 'padding: 5px 8px 5px 5px;',
                layout: 'form',
                deferredRender: false,
                forceLayout: true
            },
            items: [{
                title: _('shoplogistic_parserdata_service_update'),
                layout: 'form',
                items: shopLogistic.window.CreateParserdataServices.prototype.getFields.call(this, config)
            }, {
                title: _('shoplogistic_parserdata_cats'),
                layout: 'form',
                items: [{
                    xtype: 'shoplogistic-grid-parserdata-cats',
                    record: config.record,
                }]
            }],
            listeners: {
                'tabchange': {
                    fn: function(panel) {
                        this.syncSize();
                        panel.doLayout();
                    },
                    scope: this
                }
            }
        };
    }

});
Ext.reg('shoplogistic-window-parserdata-service-update', shopLogistic.window.UpdateParserdataServices);