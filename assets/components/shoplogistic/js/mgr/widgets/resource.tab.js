setTimeout(function () {
    Ext.override(miniShop2.panel.Product, {
        getSLProductFields: miniShop2.panel.Product.prototype.getFields,

        getFields: function (config) {
            var parentFields = this.getSLProductFields.call(this, config);

            for (var i in parentFields) {
                if (!parentFields.hasOwnProperty(i)) {
                    continue;
                }
                var item = parentFields[i];
                if (item.id == "modx-resource-tabs") {
                    for (var i2 in item.items) {
                        if (!item.items.hasOwnProperty(i2)) {
                            continue;
                        }
                        var tab = item.items[i2];
                        if (tab.id == "minishop2-product-tab" && tab.items[0]) {
                            tab.items[0].items.push({
                                id: 'shoplogistic-resource-tab-stores'
                                , autoHeight: true
                                , title: _('shoplogistic_resource_tab_store')
                                , layout: 'anchor'
                                , anchor: '100%'
                                , items: [{
                                    html: '<p>' + _('shoplogistic_resource_tab_store_desc') + '</p>'
                                    , bodyCssClass: 'panel-desc'
                                    , border: false
                                }, {
                                    xtype: 'shoplogistic-panel-resource-stores',
                                    anchor: '99%',
                                    record: config.record
                                }]
                            }, {
                                id: 'shoplogistic-resource-tab-prices'
                                , autoHeight: true
                                , title: _('shoplogistic_resource_tab_prices')
                                , layout: 'anchor'
                                , anchor: '100%'
                                , items: [{
                                    html: '<p>' + _('shoplogistic_resource_tab_prices_desc') + '</p>'
                                    , bodyCssClass: 'panel-desc'
                                    , border: false
                                }, {
                                    xtype: 'shoplogistic-panel-resource-prices',
                                    anchor: '99%',
                                    record: config.record
                                }]
                            });
                        }
                    }
                }
            }
            return parentFields;
        }
    });
}, 2);
