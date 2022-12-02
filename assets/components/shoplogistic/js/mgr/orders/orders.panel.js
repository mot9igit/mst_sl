shopLogistic.panel.Orders = function (config) {
    config = config || {};

    Ext.apply(config, {
        cls: 'container',
        items: [{
            xtype: 'modx-tabs',
            id: 'shoplogistic-orders-tabs',
            stateful: true,
            stateId: 'shoplogistic-orders-tabs',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            deferredRender: false,
            items: [{
                title: _('shoplogistic_orders'),
                layout: 'anchor',
                items: [/*{
                    xtype: 'shoplogistic-form-orders',
                    id: 'shoplogistic-form-orders',
                },*/ {
                    xtype: 'shoplogistic-grid-orders',
                    id: 'shoplogistic-grid-orders',
                }],
            }]
        }]
    });
    shopLogistic.panel.Orders.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Orders, MODx.Panel);
Ext.reg('shoplogistic-panel-orders', shopLogistic.panel.Orders);