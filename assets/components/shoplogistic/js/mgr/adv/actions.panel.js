shopLogistic.panel.Adv = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>' + _('shoplogistic') + ' :: ' + _('adv') + '</h2>',
            cls: 'modx-page-header',
        }, {
            xtype: 'modx-tabs',
            id: 'shoplogistic-adv-tabs',
            stateful: true,
            stateId: 'shoplogistic-adv-tabs',
            stateEvents: ['tabchange'],
            cls: 'shoplogistic-panel',
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            items: [{
                title: _('shoplogistic_adv'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_adv_desc'),
                    bodyCssClass: 'panel-desc',
                }
                    // , {
                //     xtype: 'shoplogistic-adv-grid',
                //     cls: 'main-wrapper',
                // }
                    ]
            }]
        }]
    });
    shopLogistic.panel.Adv.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Adv, MODx.Panel);
Ext.reg('shoplogistic-panel-adv', shopLogistic.panel.Adv);