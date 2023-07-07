shopLogistic.window.CreateMatrix = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_matrix_create'),
        width: 600,
        baseParams: {
            action: 'mgr/matrix/create',
        },
    });
    shopLogistic.window.CreateMatrix.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateMatrix, shopLogistic.window.Default, {

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
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_matrix_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_matrix_percent'),
            name: 'percent',
            anchor: '99%',
            id: config.id + '-percent',
            allowBlank: false
        }, {
            xtype: 'xdatetime',
            fieldLabel: _('shoplogistic_matrix_date_from'),
            name: 'date_from',
            anchor: '99%',
            id: config.id + '-date_from',
            allowBlank: false
        }, {
            xtype: 'xdatetime',
            fieldLabel: _('shoplogistic_matrix_date_to'),
            name: 'date_to',
            anchor: '99%',
            id: config.id + '-date_to',
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
Ext.reg('shoplogistic-window-matrix-create', shopLogistic.window.CreateMatrix);


shopLogistic.window.UpdateMatrix = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/matrix/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateMatrix.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateMatrix, shopLogistic.window.CreateMatrix, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_matrix'),
                layout: 'form',
                items: shopLogistic.window.CreateMatrix.prototype.getFields.call(this, config),
            },{
                title: _('shoplogistic_matrix_products'),
                xtype: 'shoplogistic-grid-matrix-product',
                record: config.record,
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-matrix-update', shopLogistic.window.UpdateMatrix);

shopLogistic.window.CreateMatrixProduct = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_matrix_product_create'),
        width: 600,
        baseParams: {
            action: 'mgr/matrix/products/create',
        },
    });
    shopLogistic.window.CreateMatrixProduct.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateMatrixProduct, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'matrix_id',
            id: config.id + '-matrix_id'
        }, {
            xtype: 'shoplogistic-combo-product',
            fieldLabel: _('shoplogistic_matrix_product'),
            name: 'product_id',
            anchor: '99%',
            id: config.id + '-product_id',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_matrix_count'),
            name: 'count',
            anchor: '99%',
            id: config.id + '-count',
            allowBlank: true
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_vendorbrands_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-matrix-product-create', shopLogistic.window.CreateMatrixProduct);


shopLogistic.window.UpdateMatrixProduct = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/matrix/products/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateMatrixProduct.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateMatrixProduct, shopLogistic.window.CreateMatrixProduct, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_matrix'),
                layout: 'form',
                items: shopLogistic.window.CreateMatrixProduct.prototype.getFields.call(this, config),
            },{
                title: _('shoplogistic_matrix_products'),
                xtype: 'shoplogistic-grid-matrix-product',
                record: config.record,
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-matrix-product-update', shopLogistic.window.UpdateMatrixProduct);