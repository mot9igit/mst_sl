shopLogistic.grid.OrgUsers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-orgusers';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/org/users/getlist',
            sort: 'id',
            dir: 'desc',
            org_id: config.record.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.OrgUsers.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.OrgUsers, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'org_id', 'description', 'user_id', 'actions', 'user_name'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_orgusers_create'),
            handler: this.createOrgUsers,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_orgusers_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_orgusers_user_id'),
                dataIndex: 'user_name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_orgusers_description'),
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
                this.updateOrgUsers(grid, e, row);
            },
        };
    },

    createOrgUsers: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-orgusers-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-orgusers-create',
            id: 'shoplogistic-window-orgusers-create',
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

    removeOrgUsers: function () {
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
                action: 'mgr/org/users/remove',
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

    disableOrgUsers: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/users/disable',
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

    enableOrgUsers: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/users/enable',
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

    updateOrgUsers: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-orgusers-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-orgusers-update',
            id: 'shoplogistic-window-orgusers-updater',
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
Ext.reg('shoplogistic-grid-orgusers', shopLogistic.grid.OrgUsers);