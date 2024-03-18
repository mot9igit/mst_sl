shopLogistic.grid.ParserdataStatuses = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-parserdata-statuses';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parserdata/statuses/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.ParserdataStatuses.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ParserdataStatuses, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'description', 'color', 'active', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_parserdata_status_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_parserdata_status_color'),
                dataIndex: 'color',
                sortable: true,
                width: 100,
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_parserdata_status_create'),
            handler: this.createStatus,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStatus(grid, e, row);
            },
        };
    },

    createStatus: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-parserdata-status-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-parserdata-status-create',
            id: 'shoplogistic-window-parserdata-status-create',
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

    updateStatus: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-parserdata-status-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-parserdata-status-update',
            id: 'shoplogistic-window-parserdata-status-updater',
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

    removeStatus: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_parserdata_statuses_remove')
                : _('shoplogistic_parserdata_status_remove'),
            text: ids.length > 1
                ? _('shoplogistic_parserdata_statuses_remove_confirm')
                : _('shoplogistic_parserdata_status_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parserdata/statuses/remove',
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
Ext.reg('shoplogistic-grid-parserdata-statuses', shopLogistic.grid.ParserdataStatuses);