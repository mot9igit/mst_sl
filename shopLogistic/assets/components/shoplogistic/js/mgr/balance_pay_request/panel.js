shopLogistic.panel.BalancePayRequestPage = function (config) {
    config = config || {};

    Ext.apply(config, {
        cls: 'container',
        items: [{
            xtype: 'modx-tabs',
            id: 'shoplogistic-balance-pay-request-tabs',
            stateful: true,
            stateId: 'shoplogistic-balance-pay-request-tabs',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            deferredRender: false,
            items: [{
                title: _('shoplogistic_balance_pay_request'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-balance-pay-request',
                    id: 'shoplogistic-grid-balance-pay-request',
                }]
            }, {
                title: _('shoplogistic_balance_pay_request_statuses'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-balance-pay-request-statuses',
                    id: 'shoplogistic-grid-balance-pay-request-statuses',
                }]
            }]
        }]
    });
    shopLogistic.panel.BalancePayRequestPage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.BalancePayRequestPage, MODx.Panel);
Ext.reg('shoplogistic-panel-balance-pay-request', shopLogistic.panel.BalancePayRequestPage);