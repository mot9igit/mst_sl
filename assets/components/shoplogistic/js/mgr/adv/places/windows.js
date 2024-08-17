shopLogistic.window.CreatePlace = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_place_create'),
        width: 600,
        baseParams: {
            action: 'mgr/adv/places/create',
        },
    });
    shopLogistic.window.CreatePlace.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreatePlace, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'shoplogistic-combo-adv-pages',
            fieldLabel: _('shoplogistic_place_page_id'),
            name: 'page_id',
            id: config.id + '-page_id',
            anchor: '99%'
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
Ext.reg('shoplogistic-window-place-create', shopLogistic.window.CreatePlace);


shopLogistic.window.UpdatePlace = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_place_update'),
        baseParams: {
            action: 'mgr/adv/places/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdatePlace.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdatePlace, shopLogistic.window.CreatePlace, {

    getFields: function (config) {
        return shopLogistic.window.CreatePlace.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-place-update', shopLogistic.window.UpdatePlace);