shopLogistic.panel.Parser = function (config) {
    config = config || {};

    Ext.apply(config, {
        cls: 'container',
        items: [{
            xtype: 'modx-tabs',
            id: 'shoplogistic-parser-tabs',
            stateful: true,
            stateId: 'shoplogistic-parser-tabs',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            deferredRender: false,
            items: [{
                title: _('shoplogistic_parser_tasks'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-parser-tasks',
                    id: 'shoplogistic-grid-parser-tasks',
                }]
            }, {
                title: _('shoplogistic_parser_config'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-parser-configs',
                    id: 'shoplogistic-grid-parser-configs',
                }]
            }, {
                title: _('shoplogistic_parser_tasks_statuses'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-window-parser-tasks-statuses',
                    id: 'shoplogistic-window-parser-tasks-statuses',
                }]
            }]
        }]
    });
    shopLogistic.panel.Parser.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Parser, MODx.Panel);
Ext.reg('shoplogistic-panel-parser', shopLogistic.panel.Parser);