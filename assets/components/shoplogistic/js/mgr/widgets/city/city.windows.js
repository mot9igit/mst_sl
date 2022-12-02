shopLogistic.window.City = function (config) {
    config = config || {};
    config.record = config.record || {object: {id: 0}};
    Ext.applyIf(config, {
        title: _('add'),
        url: shopLogistic.config.connector_url,
        width:800,
        action: 'mgr/city/city/create',
        saveBtnText:_('add'),
        fields: [{
            xtype: 'textfield',
            name: 'key',
            fieldLabel: _('shoplogistic_city_grid_key'),
            emptyText: _('shoplogistic_city_grid_key_empty'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'fias_id',
            fieldLabel: _('shoplogistic_city_grid_fias_id'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'city',
            fieldLabel: _('shoplogistic_city_grid_city'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'city_r',
            fieldLabel: _('shoplogistic_city_grid_city_r'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'phone',
            fieldLabel: _('shoplogistic_city_grid_phone'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'textfield',
            name: 'email',
            fieldLabel: _('shoplogistic_city_grid_email'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'textarea',
            name: 'address',
            fieldLabel: _('shoplogistic_city_grid_address'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'textarea',
            name: 'address_full',
            fieldLabel: _('shoplogistic_city_grid_address_full'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'xcheckbox',
            name: 'default',
            boxLabel: _('shoplogistic_city_grid_default'),
            anchor: '99%',
            allowBlank: true
        }]
    });
    shopLogistic.window.City.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.City, MODx.Window);
Ext.reg('shoplogistic-window-city', shopLogistic.window.City);

shopLogistic.window.UpdateCity = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-window-city';
    }
    Ext.applyIf(config, {
        title: _('update'),
        autoHeight: true,
        fields: this.getFields(config),
        url: shopLogistic.config.connector_url,
        action: 'mgr/city/city/update',
        width: 800
    });
    shopLogistic.window.UpdateCity.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateCity, MODx.Window, {
    getFields: function (config) {
        var tabs = [{
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items:[{
                title: _('shoplogistic_window_main'),
                layout: 'anchor',
                items: [{
                    layout: 'column',
                    border: 'false',
                    anchor: '100%',
                    items:[{
                        columnWidth: 1,
                        layout: 'form',
                        defaults: {msTarget: 'under'},
                        border: 'false',
                        items: [{
                            xtype: 'hidden',
                            name: 'id',
                            id: config.id + '-id',
                        }, {
                            xtype: 'textfield',
                            name: 'key',
                            fieldLabel: _('shoplogistic_city_grid_key'),
                            anchor: '99%',
                            allowBlank: false
                        }, {
                            xtype: 'textfield',
                            name: 'city',
                            fieldLabel: _('shoplogistic_city_grid_city'),
                            anchor: '99%',
                            allowBlank: false
                        },{
                            xtype: 'textfield',
                            name: 'fias_id',
                            fieldLabel: _('shoplogistic_city_grid_fias_id'),
                            anchor: '99%',
                            allowBlank: false
                        }, {
                            xtype: 'textfield',
                            name: 'city_r',
                            fieldLabel: _('shoplogistic_city_grid_city_r'),
                            anchor: '99%',
                            allowBlank: false
                        },{
                            xtype: 'textfield',
                            name: 'phone',
                            fieldLabel: _('shoplogistic_city_grid_phone'),
                            anchor: '99%',
                            allowBlank: true
                        },{
                            xtype: 'textfield',
                            name: 'email',
                            fieldLabel: _('shoplogistic_city_grid_email'),
                            anchor: '99%',
                            allowBlank: true
                        },{
                            xtype: 'textarea',
                            name: 'address',
                            fieldLabel: _('shoplogistic_city_grid_address'),
                            anchor: '99%',
                            allowBlank: true
                        },{
                            xtype: 'textarea',
                            name: 'address_full',
                            fieldLabel: _('shoplogistic_city_grid_address_full'),
                            anchor: '99%',
                            allowBlank: true
                        },{
                            xtype: 'textfield',
                            name: 'address_coordinats',
                            fieldLabel: _('shoplogistic_city_grid_address_coordinats'),
                            anchor: '99%',
                            allowBlank: true
                        },{
                            xtype: 'textfield',
                            fieldLabel: _('shoplogistic_city_grid_lat'),
                            name: 'lat',
                            id: config.id + '-lat',
                            anchor: '99%'
                        },{
                            xtype: 'textfield',
                            fieldLabel: _('shoplogistic_city_grid_lng'),
                            name: 'lng',
                            id: config.id + '-lng',
                            anchor: '99%'
                        },{
                            xtype: 'xcheckbox',
                            name: 'default',
                            boxLabel: _('shoplogistic_city_grid_default'),
                            anchor: '99%',
                            allowBlank: true
                        }]
                    }]
                }]
            },{
                title: _('shoplogistic_window_fields'),
                layout: 'anchor',
                items:[{
                    layout: 'column',
                    border: 'false',
                    anchor: '100%',
                    items:[{
                        xtype: 'shoplogistic-grid-fields',
                        preventRender: true,
                        record: config.record.object
                    }]
                }]
            }]
        }];
    
        return tabs;
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-city-window-update', shopLogistic.window.UpdateCity);

shopLogistic.window.DuplicateCity = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-window-city-duplicate';
    }
    Ext.applyIf(config, {
        title: _('duplicate'),
        autoHeight: true,
        fields: this.getFields(config),
        url: shopLogistic.config.connector_url,
        action: 'mgr/city/city/duplicate',
        width: 800
    });
    shopLogistic.window.DuplicateCity.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.DuplicateCity, MODx.Window, {
    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-duplicate_id',
        },{
            xtype: 'textfield',
            name: 'key',
            fieldLabel: _('shoplogistic_city_grid_key'),
            emptyText: _('shoplogistic_city_grid_key_empty'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'fias_id',
            fieldLabel: _('shoplogistic_city_grid_fias_id'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'city',
            fieldLabel: _('shoplogistic_city_grid_city'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'city_r',
            fieldLabel: _('shoplogistic_city_grid_city_r'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            name: 'phone',
            fieldLabel: _('shoplogistic_city_grid_phone'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'textfield',
            name: 'email',
            fieldLabel: _('shoplogistic_city_grid_email'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'textarea',
            name: 'address',
            fieldLabel: _('shoplogistic_city_grid_address'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'textarea',
            name: 'address_full',
            fieldLabel: _('shoplogistic_city_grid_address_full'),
            anchor: '99%',
            allowBlank: true
        },{
            xtype: 'xcheckbox',
            name: 'default',
            boxLabel: _('shoplogistic_city_grid_default'),
            anchor: '99%',
            allowBlank: true
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-city-window-duplicate', shopLogistic.window.DuplicateCity);