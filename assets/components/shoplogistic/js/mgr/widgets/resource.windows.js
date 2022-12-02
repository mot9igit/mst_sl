shopLogistic.window.CreateSLResourceStore = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_add'),
        url: shopLogistic.config.connector_url,
        width: 700,
        autoHeight: true,
        action: 'mgr/resource/stores/create',
        saveBtnText:_('shoplogistic_add'),
        fields: [{
            xtype: 'hidden',
            name: 'product_id',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'shoplogistic-combo-store',
            name: 'store_id',
            fieldLabel: _('shoplogistic_remains_store_id'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'numberfield',
            name: 'remains',
            fieldLabel: _('shoplogistic_remains_remains'),
            anchor: '99%',
            default: 0
        },{
            xtype: 'numberfield',
            name: 'price',
            fieldLabel: _('shoplogistic_remains_price'),
            anchor: '99%',
            default: 0
        }]
    });
    shopLogistic.window.CreateSLResourceStore.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateSLResourceStore, MODx.Window);
Ext.reg('shoplogistic-resource-window-remains-stores-create', shopLogistic.window.CreateSLResourceStore);

shopLogistic.window.UpdateSLResourceStore = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-resource-window-remains-stores-update';
    }
    Ext.applyIf(config, {
        title: _('update'),
        autoHeight: true,
        fields: this.getFields(config),
        url: shopLogistic.config.connector_url,
        action: 'mgr/resource/stores/update',
        width: 700
    });
    shopLogistic.window.UpdateSLResourceStore.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateSLResourceStore, MODx.Window, {
    getFields: function (config) {

        return [{
            xtype: 'hidden',
            name: 'id',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'shoplogistic-combo-store',
            name: 'store_id',
            fieldLabel: _('shoplogistic_remains_store_id'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'numberfield',
            name: 'remains',
            fieldLabel: _('shoplogistic_remains_remains'),
            anchor: '99%',
            default: 0
        },{
            xtype: 'numberfield',
            name: 'price',
            fieldLabel: _('shoplogistic_remains_price'),
            anchor: '99%',
            default: 0
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-resource-window-remains-stores-update', shopLogistic.window.UpdateSLResourceStore);

shopLogistic.window.CreateSLResourceWarehouse = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_add'),
        url: shopLogistic.config.connector_url,
        width: 700,
        autoHeight: true,
        action: 'mgr/resource/warehouse/create',
        saveBtnText:_('shoplogistic_add'),
        fields: [{
            xtype: 'hidden',
            name: 'product_id',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'shoplogistic-combo-warehouse',
            name: 'warehouse_id',
            fieldLabel: _('shoplogistic_warehouse_warehouse_id'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'numberfield',
            name: 'remains',
            fieldLabel: _('shoplogistic_warehouse_remains'),
            anchor: '99%',
            default: 0
        },{
            xtype: 'numberfield',
            name: 'price',
            fieldLabel: _('shoplogistic_remains_price'),
            anchor: '99%',
            default: 0
        }]
    });
    shopLogistic.window.CreateSLResourceWarehouse.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateSLResourceWarehouse, MODx.Window);
Ext.reg('shoplogistic-resource-window-remains-warehouse-create', shopLogistic.window.CreateSLResourceWarehouse);

shopLogistic.window.UpdateSLResourceWarehouse = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-resource-window-remains-warehouse-update';
    }
    Ext.applyIf(config, {
        title: _('update'),
        autoHeight: true,
        fields: this.getFields(config),
        url: shopLogistic.config.connector_url,
        action: 'mgr/resource/warehouse/update',
        width: 700
    });
    shopLogistic.window.UpdateSLResourceWarehouse.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateSLResourceWarehouse, MODx.Window, {
    getFields: function (config) {

        return [{
            xtype: 'hidden',
            name: 'id',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'shoplogistic-combo-warehouse',
            name: 'warehouse_id',
            fieldLabel: _('shoplogistic_warehouse_warehouse_id'),
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'numberfield',
            name: 'remains',
            fieldLabel: _('shoplogistic_warehouse_remains'),
            anchor: '99%',
            default: 0
        },{
            xtype: 'numberfield',
            name: 'price',
            fieldLabel: _('shoplogistic_remains_price'),
            anchor: '99%',
            default: 0
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-resource-window-remains-warehouse-update', shopLogistic.window.UpdateSLResourceWarehouse);