shopLogistic.panel.OrderDelivery = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-order-delivery-panel';
    }
    Ext.applyIf(config, {
        layout: 'form',
        cls: 'main-wrapper',
        defaults: {msgTarget: 'under', border: false},
        anchor: '100% 100%',
        border: false,
        url: shopLogistic.config.connector_url,
        items: this.getFields(config),
        listeners: {
            afterrender: function() {
                this.getData(config)
            }
        },
        pageSize:10,
        paging: true,
        remoteSort: true,
        autoHeight: true
    });

    shopLogistic.panel.OrderDelivery.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.panel.OrderDelivery, MODx.Panel, {

    getData: function(config) {
        const _self = this,
            fields = ['price','time','service','mode','address']

        MODx.Ajax.request({
            url: shopLogistic.config.connector_url,
            params: {
                action: 'mgr/order',
                order_id: config['order_id'] || 0,
            },
            listeners: {
                success: {
                    fn: function (response) {
                        for (var i in fields) {
                            if (!fields.hasOwnProperty(i)) {
                                continue;
                            }
                            if(typeof response.delivery[fields[i]] !== 'undefined') {
                                let elem = Ext.getCmp('shoplogistic-order-delivery-' + fields[i])
                                elem.setValue(response.delivery[fields[i]])
                            }
                        }
                    }, scope: this
                }
            }
        });
    },

    getFields: function (config) {
        return [{
            layout: 'column',
            items: [{
                columnWidth: 1,
                layout: 'form',
                items: [{
                    xtype: 'displayfield',
                    id: 'shoplogistic-order-info',
                    html: '<p>'+_('shoplogistic_order_delivery_info')+'</p>'
                }]
            },{
                columnWidth: .2,
                layout: 'form',
                defaults: {anchor: '100%', hideLabel: false},
                items:[{
                    xtype: 'textfield',
                    id: 'shoplogistic-order-delivery-price',
                    readOnly: true,
                    //emptyText: '?',
                    value: '1000 h',
                    fieldLabel: _('shoplogistic_order_delivery_info_price')
                },{
                    xtype: 'textfield',
                    id: 'shoplogistic-order-delivery-time',
                    readOnly: true,
                    fieldLabel: _('shoplogistic_order_delivery_info_time')
                }]
            },{
                columnWidth: .8,
                layout: 'form',
                defaults: {anchor: '100%', hideLabel: false},
                items:[{
                    xtype: 'textfield',
                    id: 'shoplogistic-order-delivery-service',
                    readOnly: true,
                    fieldLabel: _('shoplogistic_order_delivery_info_service')
                },{
                    xtype: 'textfield',
                    id: 'shoplogistic-order-delivery-mode',
                    readOnly: true,
                    fieldLabel: _('shoplogistic_order_delivery_info_mode')
                },{
                    xtype: 'textfield',
                    id: 'shoplogistic-order-delivery-address',
                    readOnly: true,
                    fieldLabel: _('shoplogistic_order_delivery_info_pvz')
                }]
            }]
        }];
    },

});
Ext.reg('shoplogistic-order-delivery-panel', shopLogistic.panel.OrderDelivery);
