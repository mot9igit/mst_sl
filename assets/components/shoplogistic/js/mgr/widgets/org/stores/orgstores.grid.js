shopLogistic.grid.OrgStores = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-orgstores';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/org/stores/getlist',
            sort: 'id',
            dir: 'desc',
            org_id: config.record.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.OrgStores.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.OrgStores, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'org_id', 'description', 'store_id', 'store_name', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_orgstores_create'),
            handler: this.createOrgStores,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_orgstores_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_orgstores_store_id'),
                dataIndex: 'store_name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_orgstores_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_grid_orgrequisites'),
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
                this.updateOrgStores(grid, e, row);
            },
        };
    },

    createOrgStores: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-orgstores-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-orgstores-create',
            id: 'shoplogistic-window-orgstores-create',
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
            org_id: this.config.record.id
        });
        w.show(e.target);
    },

    removeOrgStores: function () {
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
                action: 'mgr/org/stores/remove',
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

    disableOrgStores: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/stores/disable',
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

    enableOrgStores: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/stores/enable',
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

    updateOrgStores: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-orgstores-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-orgstores-update',
            id: 'shoplogistic-window-orgstores-updater',
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
    }
});
Ext.reg('shoplogistic-grid-orgstores', shopLogistic.grid.OrgStores);