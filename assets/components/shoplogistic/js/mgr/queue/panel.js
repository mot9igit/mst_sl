shopLogistic.panel.Queue = function (config) {
    config = config || {};

    Ext.apply(config, {
        cls: 'container',
        items: [{
            xtype: 'modx-tabs',
            id: 'shoplogistic-queue-tabs',
            stateful: true,
            stateId: 'shoplogistic-queue-tabs',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            deferredRender: false,
            items: [{
                title: _('shoplogistic_queue'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-queue',
                    id: 'shoplogistic-grid-queue',
                }]
            }, {
                title: _('shoplogistic_apirequest'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_apirequest_desc'),
                    cls: 'panel-desc'
                },{
                    xtype: 'shoplogistic-store-apirequest-grid',
                    id: 'shoplogistic-store-apirequest-grid',
                }]
            }]
        }]
    });
    shopLogistic.panel.Queue.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Queue, MODx.Panel);
Ext.reg('shoplogistic-panel-queue', shopLogistic.panel.Queue);