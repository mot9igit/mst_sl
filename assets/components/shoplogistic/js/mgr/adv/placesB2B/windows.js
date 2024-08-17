shopLogistic.window.CreatePlaceB2B = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_place_create'),
        width: 600,
        baseParams: {
            action: 'mgr/adv/placesB2B/create',
        },
    });
    shopLogistic.window.CreatePlaceB2B.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreatePlaceB2B, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_place_name'),
            name: 'name',
            anchor: '99%',
            id: config.id + '-name'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_place_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_place_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_place_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-placeB2B-create', shopLogistic.window.CreatePlaceB2B);


shopLogistic.window.UpdatePlaceB2B = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_place_update'),
        baseParams: {
            action: 'mgr/adv/placesB2B/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdatePlaceB2B.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdatePlaceB2B, shopLogistic.window.CreatePlaceB2B, {

    getFields: function (config) {
        return shopLogistic.window.CreatePlaceB2B.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-placeB2B-update', shopLogistic.window.UpdatePlaceB2B);