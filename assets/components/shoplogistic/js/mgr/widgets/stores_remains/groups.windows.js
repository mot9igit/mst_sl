shopLogistic.window.CreateStoresRemainsGroups = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: "Создать группу товаров",
        width: 600,
        baseParams: {
            action: 'mgr/storeremains/groups/create',
        },
    });
    shopLogistic.window.CreateStoresRemainsGroups.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoresRemainsGroups, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'store_id',
            id: config.id + '-store_id'
        }, {
            xtype: 'textfield',
            fieldLabel: "Наименование",
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        },{
            xtype: 'textarea',
            fieldLabel: "Описание",
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: "Активна",
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-store-remains-groups-create', shopLogistic.window.CreateStoresRemainsGroups);


shopLogistic.window.UpdateStoresRemainsGroups = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/groups/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoresRemainsGroups.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoresRemainsGroups, shopLogistic.window.CreateStoresRemainsGroups, {

    getFields: function (config) {
        return shopLogistic.window.CreateStoresRemainsGroups.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-store-remains-groups-update', shopLogistic.window.UpdateStoresRemainsGroups);