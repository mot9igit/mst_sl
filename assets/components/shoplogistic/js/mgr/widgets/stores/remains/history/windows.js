shopLogistic.window.CreateStoreRemainsHistory = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: "История остатков",
        width: 600,
        baseParams: {
            action: 'mgr/storeremains/history/create',
        },
    });
    shopLogistic.window.CreateStoreRemainsHistory.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoreRemainsHistory, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'remain_id',
            id: config.id + '-remain_id'
        },{
            xtype: 'xdatetime',
            fieldLabel: "Дата",
            name: 'date',
            anchor: '99%',
            id: config.id + '-date'
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
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_storeremains_description'),
            name: 'description',
            anchor: '99%',
            id: config.id + '-description'
        }];
    },
});
Ext.reg('shoplogistic-window-storeremains-history-create', shopLogistic.window.CreateStoreRemainsHistory);


shopLogistic.window.UpdateStoreRemainsHistory = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/history/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoreRemainsHistory.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreRemainsHistory, shopLogistic.window.CreateStoreRemainsHistory, {

    getFields: function (config) {
        return shopLogistic.window.CreateStoreRemainsHistory.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-storeremains-history-update', shopLogistic.window.UpdateStoreRemainsHistory);