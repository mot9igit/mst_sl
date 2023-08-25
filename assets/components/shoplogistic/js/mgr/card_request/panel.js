shopLogistic.panel.CardRequestPage = function (config) {
    config = config || {};

    Ext.apply(config, {
        cls: 'container',
        items: [{
            xtype: 'modx-tabs',
            id: 'shoplogistic-card-request-tabs',
            stateful: true,
            stateId: 'shoplogistic-card-request-tabs',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            deferredRender: false,
            items: [{
                title: _('shoplogistic_card_request'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-card-request',
                    id: 'shoplogistic-grid-card-request',
                }]
            }, {
                title: _('shoplogistic_card_request_statuses'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-card-request-statuses',
                    id: 'shoplogistic-grid-card-request-statuses',
                }]
            }]
        }]
    });
    shopLogistic.panel.CardRequestPage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.CardRequestPage, MODx.Panel);
Ext.reg('shoplogistic-panel-card-request', shopLogistic.panel.CardRequestPage);