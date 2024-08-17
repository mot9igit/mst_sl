shopLogistic.grid.WarehouseStores = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-warehousestores';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/warehousestores/getlist',
            sort: 'id',
            dir: 'asc',
            warehouse_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.WarehouseStores.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.WarehouseStores, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'warehouse_id', 'store', 'warehouse', 'description', 'sync', 'actions'];
    },

    getColumns: function () {
        return [
            {header: _('shoplogistic_warehousestores_id'), dataIndex: 'id', width: 20},
            {header: _('shoplogistic_warehousestores_store_id'), width: 50, dataIndex: 'store'},
            {header: _('shoplogistic_warehousestores_warehouse_id'), width: 50, dataIndex: 'warehouse'},
            {
                header: _('shoplogistic_warehouse_sync'),
                dataIndex: 'sync',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {header: _('shoplogistic_warehousestores_description'), dataIndex: 'description', width: 50},
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_warehousestores_create'),
            handler: this.createWarehouseStores,
            scope: this
        }, '->', {
            xtype: 'button'
            ,text: '<i class="icon icon-refresh"></i> ' + _('shoplogistic_sync_all')
            ,listeners: {
                click: {fn: this.syncAll, scope:this}
            }
        }];
    },

    syncAll: function(btn,e) {
        MODx.msg.confirm({
            title: _('shoplogistic_sync_all')
            ,text: _('shoplogistic_sync_all_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/warehousestores/sync_all',
                warehouse_id: this.config.record.object.id
            }
            ,listeners: {
                success: {
                    fn:function(r) {
                        this.refresh();
                        var title = 'Операция выполнена';
                        var msg = 'Синхронизация остатков произведена успешно';
                        Ext.MessageBox.alert(title,msg);
                    }, scope:this}
            }
        });
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateWarehouseUsers(grid, e, row);
            },
        };
    },

    createWarehouseStores: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-warehousestores-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-warehousestores-create',
            id: 'shoplogistic-window-warehousestores-create',
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

    updateWarehouseStores: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-warehousestores-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-warehousestores-update',
            id: 'shoplogistic-window-warehousestores-updater',
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

    removeWarehouseStores: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_warehousestores_remove')
                : _('shoplogistic_warehousestore_remove'),
            text: ids.length > 1
                ? _('shoplogistic_warehousestores_remove_confirm')
                : _('shoplogistic_warehousestore_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/warehousestores/remove',
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
Ext.reg('shoplogistic-grid-warehousestores', shopLogistic.grid.WarehouseStores);