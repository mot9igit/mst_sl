shopLogistic.panel.Params = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>' + _('shoplogistic') + ' :: ' + _('shoplogistic_params') + '</h2>',
            cls: 'modx-page-header',
        }, {
            xtype: 'modx-tabs',
            id: 'shoplogistic-params-tabs',
            stateful: true,
            stateId: 'shoplogistic-params-tabs',
            stateEvents: ['tabchange'],
            cls: 'shoplogistic-panel',
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            items: [{
                title: _('shoplogistic_setting'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_setting_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-params-setting-grid',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_settings_group'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_settings_group_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-params-setting-group-grid',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    shopLogistic.panel.Params.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Params, MODx.Panel);
Ext.reg('shoplogistic-panel-params', shopLogistic.panel.Params);