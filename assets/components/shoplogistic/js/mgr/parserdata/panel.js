shopLogistic.panel.Parserdata = function (config) {
    config = config || {};

    Ext.apply(config, {
        cls: 'container',
        items: [{
            xtype: 'modx-tabs',
            id: 'shoplogistic-parserdata-tabs',
            stateful: true,
            stateId: 'shoplogistic-parserdata-tabs',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            deferredRender: false,
            items: [{
                title: _('shoplogistic_parserdata_tasks'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-parserdata-tasks',
                    id: 'shoplogistic-grid-parserdata-tasks',
                }]
            }, {
                title: _('shoplogistic_parserdata_services'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-parserdata-services',
                    id: 'shoplogistic-grid-parserdata-services',
                }]
            }, {
                title: _('shoplogistic_parserdata_statuses'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-parserdata-statuses',
                    id: 'shoplogistic-grid-parserdata-statuses',
                }]
            }]
        }]
    });
    shopLogistic.panel.Parserdata.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Parserdata, MODx.Panel);
Ext.reg('shoplogistic-panel-parserdata', shopLogistic.panel.Parserdata);