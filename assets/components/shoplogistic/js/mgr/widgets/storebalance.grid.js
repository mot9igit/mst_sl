shopLogistic.grid.StoreBalance = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-storebalance';
    }
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storebalance/getlist',
            sort: 'id',
            dir: 'desc',
            store_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.StoreBalance.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.StoreBalance, shopLogistic.grid.Default, {
    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStoreBalance(grid, e, row);
            },
        };
    },
    createStoreBalance: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-storebalance-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-storebalance-create',
            id: 'shoplogistic-window-storebalance-create',
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

    updateStoreBalance: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-storebalance-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-storebalance-update',
            id: 'shoplogistic-window-storebalance-update',
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

    removeStoreBalance: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_storebalances_remove')
                : _('shoplogistic_storebalance_remove'),
            text: ids.length > 1
                ? _('shoplogistic_storebalances_remove_confirm')
                : _('shoplogistic_storebalance_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/storebalance/remove',
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
        return ['id', 'createdon', 'type', 'type_name', 'store_id', 'value', 'description', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('shoplogistic_storebalance_id'),
            dataIndex: 'id',
            sortable: true,
            width: 100,
        },{
            header: _('shoplogistic_storebalance_type'),
            dataIndex: 'type_name',
            sortable: true,
            width: 100,
        },{
            header: _('shoplogistic_storebalance_createdon'),
            dataIndex: 'createdon',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_storebalance_value'),
            dataIndex: 'value',
            sortable: true,
            width: 200,
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
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('shoplogistic_storebalance_create'),
            handler: this.createStoreBalance,
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
Ext.reg('shoplogistic-grid-storebalance', shopLogistic.grid.StoreBalance);
