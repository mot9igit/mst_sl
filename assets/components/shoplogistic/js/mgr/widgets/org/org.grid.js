shopLogistic.grid.Org = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-org';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/org/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.Org.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Org, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'description', 'balance', 'store', 'warehouse', 'vendor', 'name', 'image', 'active', 'email', 'phone', 'contact', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_org_create'),
            handler: this.createOrg,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_org_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_org_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_store_balance'),
                dataIndex: 'balance',
                width: 100
            },
            {
                header: _('shoplogistic_org_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_store_store'),
                dataIndex: 'store',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_store_warehouse'),
                dataIndex: 'warehouse',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_store_vendor'),
                dataIndex: 'vendor',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_grid_org'),
                dataIndex: 'actions',
                renderer: shopLogistic.utils.renderActions,
                sortable: false,
                width: 100,
                id: 'actions'
            }
        ];
    },


    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateOrg(grid, e, row);
            },
        };
    },

    createOrg: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-org-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-org-create',
            id: 'shoplogistic-window-org-create',
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

    removeOrg: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_actions_remove')
                : _('shoplogistic_action_remove'),
            text: ids.length > 1
                ? _('shoplogistic_actions_remove_confirm')
                : _('shoplogistic_action_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/org/remove',
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

    disableOrg: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/disable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    enableOrg: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/enable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    updateOrg: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-org-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-org-update',
            id: 'shoplogistic-window-org-updater',
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
    }
});
Ext.reg('shoplogistic-grid-org', shopLogistic.grid.Org);