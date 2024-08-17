shopLogistic.grid.RequestsB2B = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-requestB2B';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/adv/requestsB2B/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.RequestsB2B.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.RequestsB2B, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'description', 'name', 'active', 'actions', 'date_to', 'date_from', 'moderator_comment', 'store', 'status', 'page_places', 'image', 'image_inner', 'page_place_position', 'status_name', 'color', 'image_small'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_request_create'),
            handler: this.createRequestsB2B,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_request_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_request_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_request_description'),
                dataIndex: 'description',
                width: 100
            },

            {
                header: _('shoplogistic_request_status'),
                dataIndex: 'status_name',
                sortable: true,
                width: 100,
                renderer: function (val, cell, row) {
                    return shopLogistic.utils.renderBadge(val, cell, row);
                }
            },
            {
                header: _('shoplogistic_request_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_grid_request'),
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
                this.updateRequestsB2B(grid, e, row);
            },
        };
    },

    createRequestsB2B: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-requestB2B-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-requestB2B-create',
            id: 'shoplogistic-window-requestB2B-create',
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

    removeRequestsB2B: function () {
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
                action: 'mgr/adv/requestsB2B/remove',
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

    disableRequestsB2B: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/requestsB2B/disable',
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

    statusApproveRequestsB2B: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/requestsB2B/approve',
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

    statusDenyRequestsB2B: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/requestsB2B/deny',
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

    enableRequestsB2B: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/requestsB2B/enable',
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

    updateRequestsB2B: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-requestB2B-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-requestB2B-update',
            id: 'shoplogistic-window-requestB2B-updater',
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
Ext.reg('shoplogistic-grid-requestB2B', shopLogistic.grid.RequestsB2B);