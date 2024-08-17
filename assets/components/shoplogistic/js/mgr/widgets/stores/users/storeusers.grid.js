shopLogistic.grid.StoreUsers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-storeusers';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeusers/getlist',
            sort: 'id',
            dir: 'asc',
            store_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.StoreUsers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.StoreUsers, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'user_id', 'store_id', 'store', 'user', 'user_name', 'description', 'actions'];
    },

    getColumns: function () {
        return [
            {header: _('shoplogistic_storeuser_id'), dataIndex: 'id', width: 20},
            {header: _('shoplogistic_storeuser_user_id'), width: 100, renderer: function (val, cell, row) {
                    return shopLogistic.utils.userLink(row.data['user_name'], row.data['user_id'], true);
                }},
            {header: _('shoplogistic_storeuser_store_id'), dataIndex: 'store', width: 50},
            {header: _('shoplogistic_storeuser_description'), dataIndex: 'description', width: 50},
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_storeuser_create'),
            handler: this.createStoreusers,
            scope: this
        }, '->', this.getSearchField()];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStoreusers(grid, e, row);
            },
        };
    },

    createStoreusers: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-storeusers-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-storeusers-create',
            id: 'shoplogistic-window-storeusers-create',
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

    updateStoreusers: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-storeusers-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-storeusers-update',
            id: 'shoplogistic-window-storeusers-update',
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

    removeStoreusers: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_storeusers_remove')
                : _('shoplogistic_storeuser_remove'),
            text: ids.length > 1
                ? _('shoplogistic_storeusers_remove_confirm')
                : _('shoplogistic_storeuser_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/storeusers/remove',
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
Ext.reg('shoplogistic-grid-storeusers', shopLogistic.grid.StoreUsers);