shopLogistic.grid.StoresRemainsPrices = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-store-remains-prices';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains_prices/getlist',
            sort: 'id',
            dir: 'desc',
            remain_id: config.record.id
        },
        stateful: true
    });
    shopLogistic.grid.StoresRemainsPrices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.StoresRemainsPrices, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'remain_id', 'name', 'description', 'key', 'active', 'price', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: "Наименование",
                width: 50,
                dataIndex: 'name'
            },
            {
                header: "GUID",
                width: 50,
                dataIndex: 'key'
            },
            {
                header: "Цена",
                dataIndex: 'price',
                sortable: true,
                width: 100,
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
            text: '<i class="icon icon-plus"></i> Создать цену',
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
        var w = Ext.getCmp('shoplogistic-window-store-remains-prices-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-store-remains-prices-create',
            id: 'shoplogistic-window-store-remains-prices-create',
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
        w.fp.getForm().setValues({
            remain_id: this.config.record.id
        });
        w.show(e.target);
    },

    updatePrice: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-store-remains-prices-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-store-remains-prices-update',
            id: 'shoplogistic-window-store-remains-prices-updater',
            record: this.menu.record,
            title: this.menu.record['name'],
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
    },

    removePrice: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_store_remains_pricess_remove')
                : _('shoplogistic_store_remains_prices_remove'),
            text: ids.length > 1
                ? _('shoplogistic_store_remains_pricess_remove_confirm')
                : _('shoplogistic_store_remains_prices_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/storeremains_prices/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },
});
Ext.reg('shoplogistic-grid-store-remains-prices', shopLogistic.grid.StoresRemainsPrices);