shopLogistic.grid.OrgRequisites = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-orgrequisites';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/org/requisites/getlist',
            sort: 'id',
            dir: 'desc',
            org_id: config.record.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.OrgRequisites.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.OrgRequisites, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'org_id', 'ogrn', 'inn', 'kpp', 'ur_address', 'fact_address', 'marketplace', 'send_request', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_orgrequisites_create'),
            handler: this.createOrgRequisites,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_orgrequisites_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_orgrequisites_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_orgrequisites_ogrn'),
                dataIndex: 'ogrn',
                width: 100
            },
            {
                header: _('shoplogistic_orgrequisites_inn'),
                dataIndex: 'inn',
                width: 100
            },
            {
                header: _('shoplogistic_orgrequisites_kpp'),
                dataIndex: 'kpp',
                width: 100
            },
            {
                header: _('shoplogistic_orgrequisites_send_request'),
                dataIndex: 'send_request',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_orgrequisites_marketplace'),
                dataIndex: 'marketplace',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
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
                this.updateOrgRequisites(grid, e, row);
            },
        };
    },

    createOrgRequisites: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-orgrequisites-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-orgrequisites-create',
            id: 'shoplogistic-window-orgrequisites-create',
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

    removeOrgRequisites: function () {
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
                action: 'mgr/org/requisites/remove',
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

    disableOrgRequisites: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/requisites/disable',
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

    enableOrgRequisites: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/requisites/enable',
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

    updateOrgRequisites: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-orgrequisites-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-orgrequisites-update',
            id: 'shoplogistic-window-orgrequisites-updater',
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
Ext.reg('shoplogistic-grid-orgrequisites', shopLogistic.grid.OrgRequisites);