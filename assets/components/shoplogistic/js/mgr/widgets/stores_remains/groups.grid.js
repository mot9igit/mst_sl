shopLogistic.grid.StoresRemainsGroups = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-store-remains-groups';
    }
    console.log(config)
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/groups/getlist',
            sort: 'id',
            dir: 'desc',
            store_id: config.record.object.id
        },
        stateful: true
    });
    shopLogistic.grid.StoresRemainsGroups.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.StoresRemainsGroups, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'name', 'description', 'active', 'actions'];
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
            text: '<i class="icon icon-plus"></i> Создать группу',
            handler: this.createStoreGroup,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStoreGroup(grid, e, row);
            },
        };
    },

    createStoreGroup: function (btn, e) {
        console.log(this.config)
        var w = Ext.getCmp('shoplogistic-window-store-remains-groups-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-store-remains-groups-create',
            id: 'shoplogistic-window-store-remains-groups-create',
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
            store_id: this.config.record.object.id,
            active: 1
        });
        w.show(e.target);
    },

    updateStoreGroup: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-store-remains-groups-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-store-remains-groups-update',
            id: 'shoplogistic-window-store-remains-groups-updater',
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

    removeStoreGroup: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_confirm')
                : _('shoplogistic_confirm'),
            text: ids.length > 1
                ? _('shoplogistic_confirm_remove')
                : _('shoplogistic_confirm_remove'),
            url: this.config.url,
            params: {
                action: 'mgr/storeremains/groups/remove',
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
Ext.reg('shoplogistic-grid-store-remains-groups', shopLogistic.grid.StoresRemainsGroups);