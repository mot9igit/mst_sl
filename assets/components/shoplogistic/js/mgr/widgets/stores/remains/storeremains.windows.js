shopLogistic.window.CreateStoreRemains = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_storeremains_create'),
        width: 600,
        baseParams: {
            action: 'mgr/storeremains/create',
        },
    });
    shopLogistic.window.CreateStoreRemains.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoreRemains, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'store_id',
            id: config.id + '-store_id'
        },{
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_storeremains_product_name'),
                    name: 'name',
                    anchor: '99%',
                    id: config.id + '-name',
                    allowBlank: false
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_storeremains_catalog'),
                    name: 'catalog',
                    anchor: '99%',
                    id: config.id + '-catalog',
                    allowBlank: true
                }]
            }]
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_storeuser_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        },{
            xtype: 'textarea',
            fieldLabel: "Теги",
            name: 'tags',
            anchor: '99%',
            id: config.id + '-tags'
        },{
            title: "Идентификаторы товара",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: "GUID каталога",
                    name: 'catalog_guid',
                    anchor: '99%',
                    id: config.id + '-catalog_guid',
                    allowBlank: true
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_storeremains_guid'),
                    name: 'guid',
                    anchor: '99%',
                    id: config.id + '-guid',
                    allowBlank: false
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_storeremains_article'),
                    name: 'article',
                    anchor: '99%',
                    id: config.id + '-article',
                    allowBlank: false
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_storeremains_base_guid'),
                    name: 'base_guid',
                    anchor: '99%',
                    id: config.id + '-base_guid',
                    allowBlank: true
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_storeremains_barcode'),
                    name: 'barcode',
                    anchor: '99%',
                    id: config.id + '-barcode',
                    allowBlank: true
                }]
            }]
        },{
            title: "Наличие",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'numberfield',
                    fieldLabel: _('shoplogistic_storeremains_remains'),
                    name: 'remains',
                    anchor: '99%',
                    id: config.id + '-remains',
                    allowBlank: false
                }, {
                    xtype: 'numberfield',
                    fieldLabel: _('shoplogistic_storeremains_reserved'),
                    name: 'reserved',
                    anchor: '99%',
                    id: config.id + '-reserved',
                    allowBlank: false
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'numberfield',
                    fieldLabel: _('shoplogistic_storeremains_available'),
                    name: 'available',
                    anchor: '99%',
                    id: config.id + '-available',
                    allowBlank: false
                }, {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    fieldLabel: _('shoplogistic_storeremains_price'),
                    name: 'price',
                    anchor: '99%',
                    id: config.id + '-price',
                    allowBlank: true
                }]
            }]
        }, {
            xtype: 'shoplogistic-combo-product',
            fieldLabel: _('shoplogistic_storeremains_product_id'),
            description: "Привязка к товару из маркетплейса",
            name: 'product_id',
            anchor: '99%',
            id: config.id + '-product_id',
            allowBlank: false
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_storeremains_published'),
            name: 'published',
            id: config.id + '-published',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_storeremains_checked'),
            name: 'checked',
            id: config.id + '-checked',
            checked: false,
        }];
    },
});
Ext.reg('shoplogistic-window-storeremains-create', shopLogistic.window.CreateStoreRemains);


shopLogistic.window.UpdateStoreRemains = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoreRemains.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreRemains, shopLogistic.window.CreateStoreRemains, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_storeremains'),
                layout: 'form',
                items: shopLogistic.window.CreateStoreRemains.prototype.getFields.call(this, config),
            },{
                title: "Цены",
                items: [{
                    xtype: 'shoplogistic-grid-store-remains-prices',
                    record: config.record,
                }]
            },{
                title: "История изменения остатков",
                items: [{
                    xtype: 'shoplogistic-grid-storeremains-history',
                    record: config.record,
                }]
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-storeremains-update', shopLogistic.window.UpdateStoreRemains);