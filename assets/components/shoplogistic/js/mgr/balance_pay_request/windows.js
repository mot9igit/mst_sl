shopLogistic.window.CreateBalancePayRequest = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_balance_pay_request_create'),
        width: 600,
        baseParams: {
            action: 'mgr/balance_pay_request/create',
        },
    });
    shopLogistic.window.CreateBalancePayRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateBalancePayRequest, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_balance_pay_request_store_id'),
            name: 'store_id',
            anchor: '99%',
            id: config.id + '-store_id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_balance_pay_request_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_balance_pay_request_phone'),
            name: 'phone',
            id: config.id + '-phone',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-balancepayrequeststatus',
            fieldLabel: _('shoplogistic_balance_pay_request_status'),
            name: 'status',
            anchor: '99%',
            id: config.id + '-status'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_balance_pay_request_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-balance-pay-request-create', shopLogistic.window.CreateBalancePayRequest);


shopLogistic.window.UpdateBalancePayRequest = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_balance_pay_request_update'),
        baseParams: {
            action: 'mgr/balance_pay_request/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateBalancePayRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateBalancePayRequest, shopLogistic.window.CreateBalancePayRequest, {

    getFields: function (config) {
        return shopLogistic.window.CreateBalancePayRequest.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-balance-pay-request-update', shopLogistic.window.UpdateBalancePayRequest);