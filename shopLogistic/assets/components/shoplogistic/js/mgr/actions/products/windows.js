shopLogistic.window.CreateActionProduct = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_action_product_create'),
        width: 900,
        baseParams: {
            action: 'mgr/actions/products/create',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.CreateActionProduct.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateActionProduct, shopLogistic.window.Default, {
    getFields: function (config) {
        return shopLogistic.window.CreateActionProduct.prototype.getFormFields.call(this, config);
    },
    getFormFields: function (config) {
        // console.log(config)
        var default_fields = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        }, {
            xtype: 'hidden',
            name: 'action_id',
            id: config.id + '-action_id'
        }, {
            xtype: 'shoplogistic-combo-product',
            fieldLabel: _('shoplogistic_action_product_product_name'),
            name: 'product_id',
            id: config.id + '-product_id',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_action_product_new_price'),
            name: 'new_price',
            id: config.id + '-new_price',
            blankText: '0.00',
            decimalPrecision: 2,
            anchor: '99%',
            allowBlank: false,
        },{
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_action_product_old_price'),
            name: 'old_price',
            id: config.id + '-old_price',
            blankText: '0.00',
            decimalPrecision: 2,
            anchor: '99%',
            allowBlank: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_action_product_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_action_product_force'),
            name: 'force',
            id: config.id + '-force',
            checked: false,
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_action_product_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
        return default_fields
    },
});
Ext.reg('shoplogistic-actions-products-window-create', shopLogistic.window.CreateActionProduct);

shopLogistic.window.UpdateActionProduct = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_product_update'),
            width: 900,
            action: 'mgr/actions/products/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateActionProduct.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateActionProduct, shopLogistic.window.CreateActionProduct, {

    getFields: function (config) {
        return shopLogistic.window.CreateActionProduct.prototype.getFormFields.call(this, config);
    }

});
Ext.reg('shoplogistic-actions-products-window-update', shopLogistic.window.UpdateActionProduct);
