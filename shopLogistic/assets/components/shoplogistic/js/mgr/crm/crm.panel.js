shopLogistic.panel.CRM = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>' + _('shoplogistic') + ' :: ' + _('shoplogistic_crm') + '</h2>',
            cls: 'modx-page-header',
        }, {
            xtype: 'modx-tabs',
            id: 'shoplogistic-crm-tabs',
            stateful: true,
            stateId: 'shoplogistic-crm-tabs',
            stateEvents: ['tabchange'],
            cls: 'shoplogistic-panel',
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            items: [{
                title: _('shoplogistic_crm_products'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_crm_products_intro'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-crm-grid-products',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_crm_deal'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_crm_deal_intro'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-crm-grid-deal',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_crm_deal_categories'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_crm_deal_categories_intro'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-crm-grid-category',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    shopLogistic.panel.CRM.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.CRM, MODx.Panel);
Ext.reg('shoplogistic-panel-crm', shopLogistic.panel.CRM);