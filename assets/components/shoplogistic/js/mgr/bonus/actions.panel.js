shopLogistic.panel.Actions = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>' + _('shoplogistic') + ' :: ' + _('shoplogistic_actions') + '</h2>',
            cls: 'modx-page-header',
        }, {
            xtype: 'modx-tabs',
            id: 'shoplogistic-actions-tabs',
            stateful: true,
            stateId: 'shoplogistic-actions-tabs',
            stateEvents: ['tabchange'],
            cls: 'shoplogistic-panel',
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            items: [{
                title: _('shoplogistic_actions'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_actions_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-actions-grid',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    shopLogistic.panel.Actions.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Actions, MODx.Panel);
Ext.reg('shoplogistic-panel-actions', shopLogistic.panel.Actions);