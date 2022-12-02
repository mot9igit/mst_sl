shopLogistic.panel.Settings = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>' + _('shoplogistic') + ' :: ' + _('shoplogistic_settings') + '</h2>',
            cls: 'modx-page-header',
        }, {
            xtype: 'modx-tabs',
            id: 'shoplogistic-settings-tabs',
            stateful: true,
            stateId: 'shoplogistic-settings-tabs',
            stateEvents: ['tabchange'],
            cls: 'shoplogistic-panel',
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            items: [{
                title: _('shoplogistic_statuses'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_statuses_intro'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-status',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    shopLogistic.panel.Settings.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Settings, MODx.Panel);
Ext.reg('shoplogistic-panel-settings', shopLogistic.panel.Settings);