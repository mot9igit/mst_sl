shopLogistic.grid.MatrixGrid = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-matrix';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/matrix/getlist',
            sort: 'id',
            dir: 'asc',
            store_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.MatrixGrid.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.MatrixGrid, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'name', 'store', 'date_from', 'date_to', 'description', 'actions'];
    },

    getColumns: function () {
        return [
            {header: _('shoplogistic_matrix_id'), dataIndex: 'id', width: 20},
            {header: _('shoplogistic_matrix_name'), width: 50, dataIndex: 'name'},
            {header: _('shoplogistic_matrix_store'), width: 50, dataIndex: 'store'},
            {header: _('shoplogistic_matrix_date_from'), width: 50, dataIndex: 'date_from'},
            {header: _('shoplogistic_matrix_date_to'), width: 50, dataIndex: 'date_to'},
            {header: _('shoplogistic_matrix_description'), dataIndex: 'description', width: 50},
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_matrix_create'),
            handler: this.createMatrix,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateMatrix(grid, e, row);
            },
        };
    },

    createMatrix: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-matrix-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-matrix-create',
            id: 'shoplogistic-window-matrix-create',
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
            store_id: this.config.record.object.id
        });
        w.show(e.target);
    },

    updateMatrix: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-matrix-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-matrix-update',
            id: 'shoplogistic-window-matrix-updater',
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
    },

    removeMatrix: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_matrix_remove')
                : _('shoplogistic_matrix_remove'),
            text: ids.length > 1
                ? _('shoplogistic_matrix_remove_confirm')
                : _('shoplogistic_matrix_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/matrix/remove',
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
Ext.reg('shoplogistic-grid-matrix', shopLogistic.grid.MatrixGrid);

shopLogistic.grid.MatrixProductGrid = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-matrix';
    }
    console.log(config)
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/matrix/products/getlist',
            sort: 'id',
            dir: 'asc',
            matrix_id: config.record.id
        },
        stateful: true,
        stateId: config.record.id,
    });
    shopLogistic.grid.MatrixProductGrid.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.MatrixProductGrid, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'product_id', 'product', 'matrix', 'matrix_id', 'count', 'percent', 'description', 'actions'];
    },

    getColumns: function () {
        return [
            {header: _('shoplogistic_matrix_id'), dataIndex: 'id', width: 20},
            {header: _('shoplogistic_matrix_product'), width: 50, dataIndex: 'product'},
            {header: _('shoplogistic_matrix_matrix'), width: 50, dataIndex: 'matrix'},
            {header: _('shoplogistic_matrix_percent'), width: 50, dataIndex: 'percent'},
            {header: _('shoplogistic_matrix_count'), width: 50, dataIndex: 'count'},
            {header: _('shoplogistic_matrix_description'), dataIndex: 'description', width: 50},
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_matrix_product_create'),
            handler: this.createMatrixProduct,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateMatrix(grid, e, row);
            },
        };
    },

    createMatrixProduct: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-matrix-product-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-matrix-product-create',
            id: 'shoplogistic-window-matrix-product-create',
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
            matrix_id: this.config.record.id
        });
        w.show(e.target);
    },

    updateMatrixProduct: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-matrix-product-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-matrix-product-update',
            id: 'shoplogistic-window-matrix-product-updater',
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

    removeMatrixProduct: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_matrix_product_remove')
                : _('shoplogistic_matrix_product_remove'),
            text: ids.length > 1
                ? _('shoplogistic_matrix_product_remove_confirm')
                : _('shoplogistic_matrix_product_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/matrix/products/remove',
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
Ext.reg('shoplogistic-grid-matrix-product', shopLogistic.grid.MatrixProductGrid);