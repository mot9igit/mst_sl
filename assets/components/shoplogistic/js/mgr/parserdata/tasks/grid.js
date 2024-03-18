shopLogistic.grid.ParserdataTasks = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-parserdata-tasks';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parserdata/tasks/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.ParserdataTasks.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ParserdataTasks, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'external_id', 'description', 'data', 'updatedon', 'url', 'status', 'status_name', 'color', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_parserdata_tasks_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_parserdata_tasks_url'),
                dataIndex: 'url',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parserdata_tasks_external_id'),
                dataIndex: 'external_id',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parserdata_tasks_status'),
                dataIndex: 'status_name',
                sortable: true,
                width: 100,
                renderer: function (val, cell, row) {
                    return shopLogistic.utils.renderBadge(val, cell, row);
                }
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_parserdata_tasks_create'),
            handler: this.createTask,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateTask(grid, e, row);
            },
        };
    },

    createTask: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-parserdata-tasks-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-parserdata-tasks-create',
            id: 'shoplogistic-window-parserdata-tasks-create',
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
            status: 1
        });
        w.show(e.target);
    },

    updateTask: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-parserdata-tasks-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-parserdata-tasks-update',
            id: 'shoplogistic-window-parserdata-tasks-updater',
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

    removeTask: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_parserdata_tasks_remove')
                : _('shoplogistic_parserdata_task_remove'),
            text: ids.length > 1
                ? _('shoplogistic_parserdata_tasks_remove_confirm')
                : _('shoplogistic_parserdata_task_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parserdata/tasks/remove',
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
    }
});
Ext.reg('shoplogistic-grid-parserdata-tasks', shopLogistic.grid.ParserdataTasks);