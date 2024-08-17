shopLogistic.panel.Bonus = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>' + _('shoplogistic') + ' :: ' + _('shoplogistic_bonus') + '</h2>',
            cls: 'modx-page-header',
        }, {
            xtype: 'modx-tabs',
            id: 'shoplogistic-bonus-tabs',
            stateful: true,
            stateId: 'shoplogistic-bonus-tabs',
            stateEvents: ['tabchange'],
            cls: 'shoplogistic-panel',
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            items: [{
                title: _('shoplogistic_bonus_gift'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_bonus_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-gift',
                    cls: 'main-wrapper',
                }]
            },
            {
                title: _('shoplogistic_bonus_motivation'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_bonus_motivation_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-motivation',
                    cls: 'main-wrapper',
                }]
            }
            ]
        }]
    });
    shopLogistic.panel.Bonus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Bonus, MODx.Panel);
Ext.reg('shoplogistic-panel-bonus', shopLogistic.panel.Bonus);