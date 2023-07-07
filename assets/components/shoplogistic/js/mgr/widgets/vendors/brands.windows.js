shopLogistic.window.CreateVendorBrands = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_vendorbrands_create'),
        width: 600,
        baseParams: {
            action: 'mgr/vendorbrands/create',
        },
    });
    shopLogistic.window.CreateVendorBrands.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateVendorBrands, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'store_id',
            id: config.id + '-store_id'
        }, {
            xtype: 'shoplogistic-combo-vendor',
            fieldLabel: _('shoplogistic_vendorbrands_vendor'),
            name: 'brand_id',
            anchor: '99%',
            id: config.id + '-brand_id',
            allowBlank: false
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_vendorbrands_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-vendorbrands-create', shopLogistic.window.CreateVendorBrands);


shopLogistic.window.UpdateVendorBrands = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/vendorbrands/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateVendorBrands.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateVendorBrands, shopLogistic.window.CreateVendorBrands, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_vendorbrands'),
                layout: 'form',
                items: shopLogistic.window.CreateVendorBrands.prototype.getFields.call(this, config),
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-vendorbrands-update', shopLogistic.window.UpdateVendorBrands);