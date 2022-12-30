shopLogistic.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'shoplogistic-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('shoplogistic') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('shoplogistic_stores'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-stores',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('shoplogistic_warehouses'),
                layout: 'anchor',
                items: [{
                    html: _('shoplogistic_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'shoplogistic-grid-warehouses',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    shopLogistic.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Home, MODx.Panel);
Ext.reg('shoplogistic-panel-home', shopLogistic.panel.Home);
