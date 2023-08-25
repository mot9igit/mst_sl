shopLogistic.window.CreateCardRequest = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_card_request_create'),
        width: 600,
        baseParams: {
            action: 'mgr/card_request/create',
        },
    });
    shopLogistic.window.CreateCardRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateCardRequest, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_card_request_store_id'),
            name: 'store_id',
            anchor: '99%',
            id: config.id + '-store_id'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_card_request_remain_id'),
            name: 'remain_id',
            anchor: '99%',
            id: config.id + '-remain_id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_card_request_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-cardrequeststatus',
            fieldLabel: _('shoplogistic_card_request_status'),
            name: 'status',
            anchor: '99%',
            id: config.id + '-status'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_card_request_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_card_request_url'),
            name: 'url',
            id: config.id + '-url',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-card-request-create', shopLogistic.window.CreateCardRequest);


shopLogistic.window.UpdateCardRequest = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_card_request_update'),
        baseParams: {
            action: 'mgr/card_request/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateCardRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateCardRequest, shopLogistic.window.CreateCardRequest, {

    getFields: function (config) {
        return shopLogistic.window.CreateCardRequest.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-card-request-update', shopLogistic.window.UpdateCardRequest);