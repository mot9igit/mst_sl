shopLogistic.grid.WarehouseUsers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-warehouseusers';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/warehouseusers/getlist',
            sort: 'id',
            dir: 'asc',
            warehouse_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.WarehouseUsers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.WarehouseUsers, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'user_id', 'warehouse_id', 'user', 'user_name', 'warehouse','description', 'actions'];
    },

    getColumns: function () {
        return [
            {header: _('shoplogistic_warehouseuser_id'), dataIndex: 'id', width: 20},
            {header: _('shoplogistic_warehouseuser_user_id'), width: 100, renderer: function (val, cell, row) {
                    return shopLogistic.utils.userLink(row.data['user_name'], row.data['user_id'], true);
                }},
            {header: _('shoplogistic_warehouseuser_warehouse_id'), width: 50, dataIndex: 'warehouse'},
            {header: _('shoplogistic_warehouseuser_description'), dataIndex: 'description', width: 50},
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_warehouseuser_create'),
            handler: this.createWarehouseUsers,
            scope: this
        }, '->', this.getSearchField()];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateWarehouseUsers(grid, e, row);
            },
        };
    },

    createWarehouseUsers: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-warehouseusers-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-warehouseusers-create',
            id: 'shoplogistic-window-warehouseusers-create',
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

    updateWarehouseUsers: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-warehouseusers-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-warehouseusers-update',
            id: 'shoplogistic-window-warehouseusers-update',
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

    removeWarehouseUsers: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_warehouseusers_remove')
                : _('shoplogistic_warehouseuser_remove'),
            text: ids.length > 1
                ? _('shoplogistic_warehouseusers_remove_confirm')
                : _('shoplogistic_warehouseuser_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/warehouseusers/remove',
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
Ext.reg('shoplogistic-grid-warehouseusers', shopLogistic.grid.WarehouseUsers);