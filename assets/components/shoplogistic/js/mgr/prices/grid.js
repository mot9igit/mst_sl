shopLogistic.grid.Prices = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-prices';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/prices/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.Prices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Prices, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'store_name', 'product_id', 'date_from', 'date_to', 'price_mrc', 'price_rrc', 'description', 'createdon', 'createdby', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_prices_store_id'),
                width: 50,
                dataIndex: 'store_name'
            },
            {
                header: _('shoplogistic_prices_price_mrc'),
                dataIndex: 'price_mrc',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_prices_price_rrc'),
                dataIndex: 'price_rrc',
                sortable: true,
                width: 100
            },
            {
                header: _('ms2_actions'),
                dataIndex: 'actions',
                id: 'actions',
                width: 50,
                renderer: shopLogistic.utils.renderActions
            }
        ];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_prices_create'),
            handler: this.createPrice,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updatePrice(grid, e, row);
            },
        };
    },

    createPrice: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-price-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-price-create',
            id: 'shoplogistic-window-price-create',
            record: this.menu.record,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues({});
        w.show(e.target);
    },

    updatePrice: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-price-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-price-update',
            id: 'shoplogistic-window-price-updater',
            record: this.menu.record,
            title: this.menu.record['store'],
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues(this.menu.record);
        w.show(e.target);
    }
});
Ext.reg('shoplogistic-grid-prices', shopLogistic.grid.Prices);