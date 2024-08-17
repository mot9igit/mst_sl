shopLogistic.panel.Adv = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>' + _('shoplogistic') + ' :: ' + _('shoplogistic_adv') + '</h2>',
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
                title: _('shoplogistic_adv_page'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_adv_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-page',
                    cls: 'main-wrapper',
                }]
            },
            {
                title: _('shoplogistic_adv_place'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_adv_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-place',
                    cls: 'main-wrapper',
                }
                ]
            },
            {
                title: _('shoplogistic_adv_request'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_adv_desc'),
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-request',
                    cls: 'main-wrapper',
                }
                ]
            },
                {
                    title: _('shoplogistic_adv_placeB2B'),
                    layout: 'anchor',
                    items: [{
                        html: _('shoplogistic_adv_desc'),
                        bodyCssClass: 'panel-desc',
                    }, {
                        xtype: 'shoplogistic-grid-placeB2B',
                        cls: 'main-wrapper',
                    }
                    ]
                },
                {
                    title: _('shoplogistic_adv_requestB2B'),
                    layout: 'anchor',
                    items: [{
                        html: _('shoplogistic_adv_desc'),
                        bodyCssClass: 'panel-desc',
                    }, {
                        xtype: 'shoplogistic-grid-requestB2B',
                        cls: 'main-wrapper',
                    }
                    ]
                }
            ]
        }]
    });
    shopLogistic.panel.Adv.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Adv, MODx.Panel);
Ext.reg('shoplogistic-panel-adv', shopLogistic.panel.Adv);