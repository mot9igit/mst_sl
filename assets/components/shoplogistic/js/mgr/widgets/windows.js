shopLogistic.window.CreateGift = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_gift_create'),
        width: 600,
        baseParams: {
            action: 'mgr/bonus/gift/create',
        },
    });
    shopLogistic.window.CreateGift.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateGift, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_gift_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_gift_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'modx-combo-browser',
            fieldLabel: _('shoplogistic_gift_image'),
            name: 'image',
            id: config.id + '-image',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-gift-create', shopLogistic.window.CreateGift);


shopLogistic.window.UpdateGift = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_gift_update'),
        baseParams: {
            action: 'mgr/bonus/gift/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateGift.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateGift, shopLogistic.window.CreateGift, {

    getFields: function (config) {
        return shopLogistic.window.CreateGift.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-gift-update', shopLogistic.window.UpdateGift);