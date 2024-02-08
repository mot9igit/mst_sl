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
                title: _('shoplogistic_brand_association'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_brand_association_intro'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-association',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_delivery'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_delivery_intro'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-delivery',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_connection_statuses'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_connection_statuses_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-connection-statuses',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_shipment_statuses'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_shipment_statuses_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-shipment-statuses',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_reporttypes'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_reporttypes_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-report-type',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    shopLogistic.panel.Settings.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Settings, MODx.Panel);
Ext.reg('shoplogistic-panel-settings', shopLogistic.panel.Settings);