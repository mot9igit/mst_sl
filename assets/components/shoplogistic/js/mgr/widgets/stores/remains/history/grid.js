shopLogistic.grid.StoreRemainsHistory = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-storeremains-history';
    }
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/history/getlist',
            sort: 'id',
            dir: 'asc',
            remain_id: config.record.id
        },
        stateful: true,
        stateId: config.record.id,
        topbar: 1
    });
    shopLogistic.grid.StoreRemainsHistory.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.StoreRemainsHistory, shopLogistic.grid.Default, {
    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStoreRemain(grid, e, row);
            },
        };
    },
    createStoreRemain: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-storeremains-history-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-storeremains-history-create',
            id: 'shoplogistic-window-storeremains-history-create',
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

    updateStoreRemain: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-storeremains-history-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-storeremains-history-update',
            id: 'shoplogistic-window-storeremains-history-update',
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

    removeStoreRemain: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_storeremains_remove')
                : _('shoplogistic_storeremain_remove'),
            text: ids.length > 1
                ? _('shoplogistic_storeremains_remove_confirm')
                : _('shoplogistic_storeremain_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/storeremains/history/remove',
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
        return ['id', 'date', 'remain_id', 'remains', 'reserved', 'available', 'price', 'description', 'actions'];
    },

    getColumns: function () {
        return [{
            header: "Дата",
            dataIndex: 'date',
            sortable: true,
            width: 200
        },{
            header: _('shoplogistic_storeremains_remains'),
            dataIndex: 'remains',
            sortable: true,
            width: 70,
        },{
            header: _('shoplogistic_storeremains_available'),
            dataIndex: 'available',
            sortable: true,
            width: 70,
        },{
            header: _('shoplogistic_storeremains_reserved'),
            dataIndex: 'reserved',
            sortable: true,
            width: 70,
        },{
            header: _('shoplogistic_storeremains_price'),
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

    getTopBar: function (config) {
        if(config.topbar){
            return [{
                text: '<i class="icon icon-plus"></i>&nbsp;Создать объект истории',
                handler: this.createStoreRemain,
                scope: this
            }];
        }else{
            return [];
        }
    }
});
Ext.reg('shoplogistic-grid-storeremains-history', shopLogistic.grid.StoreRemainsHistory);
