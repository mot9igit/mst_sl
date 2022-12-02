shopLogistic.window.CreateCFResource = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('add'),
        url: shopLogistic.config.connector_url,
        width: 700,
        autoHeight: true,
        action: 'mgr/city/resource/create',
        saveBtnText:_('add'),
        fields: [{
            xtype: 'hidden',
            name: 'resource',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'shoplogistic-combo-city',
            name: 'city',
            fieldLabel: _('shoplogistic_resource_grid_city'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textarea',
            name: 'content',
            fieldLabel: _('shoplogistic_resource_grid_content'),
            anchor: '100%',
            allowBlank: false,
            height: 400,
            id: config.id + '-content',
            listeners: {
                render: function (config) {
                    if (MODx.loadRTE && shopLogistic.config.richtext == 1) {
                        window.setTimeout(function() {
                            MODx.loadRTE(config.id);
                        }, 300);
                    }
                }
            }
        }]
    });
    shopLogistic.window.CreateCFResource.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateCFResource, MODx.Window);
Ext.reg('shoplogistic-window-resource', shopLogistic.window.CreateCFResource);

shopLogistic.window.UpdateResource = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-window-resource-update';
    }
    Ext.applyIf(config, {
        title: _('update'),
        autoHeight: true,
        fields: this.getFields(config),
        url: shopLogistic.config.connector_url,
        action: 'mgr/city/resource/update',
        width: 700
    });
    shopLogistic.window.UpdateResource.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateResource, MODx.Window, {
    getFields: function (config) {
        
        return [{
            xtype: 'hidden',
            name: 'id',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'shoplogistic-combo-city',
            name: 'city',
            fieldLabel: _('shoplogistic_resource_grid_city'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textarea',
            name: 'content',
            fieldLabel: _('shoplogistic_resource_grid_content'),
            anchor: '100%',
            allowBlank: false,
            height: 400,
            id: config.id + '-content',
            listeners: {
                render: function (config) {
                    if (MODx.loadRTE && shopLogistic.config.richtext == 1) {
                        window.setTimeout(function() {
                            MODx.loadRTE(config.id);
                        }, 300);
                    }
                }
            }
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-resource-window-update', shopLogistic.window.UpdateResource);