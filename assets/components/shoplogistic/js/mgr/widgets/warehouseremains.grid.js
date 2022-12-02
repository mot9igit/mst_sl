shopLogistic.grid.WarehouseRemains = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-warehouseremains';
    }
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/warehouseremains/getlist',
            sort: 'id',
            dir: 'asc',
            warehouse_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.WarehouseRemains.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.WarehouseRemains, shopLogistic.grid.Default, {
    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateWarehouseRemain(grid, e, row);
            },
        };
    },
    createWarehouseRemain: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-warehouseremains-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-warehouseremains-create',
            id: 'shoplogistic-window-warehouseremains-create',
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
            warehouse_id: this.config.record.object.id
        });
        w.show(e.target);
    },

    updateWarehouseRemain: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-warehouseremains-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-warehouseremains-update',
            id: 'shoplogistic-window-warehouseremains-update',
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

    removeWarehouseRemain: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_warehouseremains_remove')
                : _('shoplogistic_warehouseremain_remove'),
            text: ids.length > 1
                ? _('shoplogistic_warehouseremains_remove_confirm')
                : _('shoplogistic_warehouseremain_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/warehouseremains/remove',
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

    getFields: function () {
        return ['id', 'product_article', 'product_name', 'product_id', 'warehouse_name', 'warehouse_id', 'remains', 'price', 'description', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('shoplogistic_warehouseremains_product_article'),
            dataIndex: 'product_article',
            sortable: true,
            width: 200
        },{
            header: _('shoplogistic_warehouseremains_product_name'),
            dataIndex: 'product_name',
            sortable: true,
            width: 200
        },{
            header: _('shoplogistic_warehouseremains_warehouse_name'),
            dataIndex: 'warehouse_name',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_warehouseremains_remains'),
            dataIndex: 'remains',
            sortable: true,
            width: 70,
        },{
            header: _('shoplogistic_warehouseremains_price'),
            dataIndex: 'price',
            sortable: true,
            width: 70,
        }, {
            header: _('shoplogistic_grid_actions'),
            dataIndex: 'actions',
            renderer: shopLogistic.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('shoplogistic_warehouseremain_create'),
            handler: this.createWarehouseRemain,
            scope: this
        }, '->', {
            xtype: 'shoplogistic-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
    },
});
Ext.reg('shoplogistic-grid-warehouseremains', shopLogistic.grid.WarehouseRemains);
