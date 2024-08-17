shopLogistic.grid.ParserTasks = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-parser-tasks';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parser/tasks/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.ParserTasks.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ParserTasks, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'exclude', 'description', 'config_id', 'config', 'url', 'status', 'status_name', 'color', 'file', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                sortable: true,
                width: 20
            },
            {
                header: _('shoplogistic_parser_tasks_name'),
                width: 50,
                sortable: true,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_parser_tasks_url'),
                dataIndex: 'url',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parser_tasks_config_id'),
                dataIndex: 'config',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parser_tasks_status'),
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_parser_tasks_create'),
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
        var w = Ext.getCmp('shoplogistic-window-parser-tasks-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-parser-tasks-create',
            id: 'shoplogistic-window-parser-tasks-create',
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

    updateTask: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-parser-tasks-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-parser-tasks-update',
            id: 'shoplogistic-window-parser-tasks-updater',
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
                ? _('shoplogistic_parser_tasks_remove')
                : _('shoplogistic_parser_task_remove'),
            text: ids.length > 1
                ? _('shoplogistic_parser_tasks_remove_confirm')
                : _('shoplogistic_parser_task_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parser/tasks/remove',
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

    downloadTaskFile: function () {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        if(this.menu.record.file){
            var link = document.createElement('a');
            link.setAttribute('href', this.menu.record.file);
            link.setAttribute('download', this.menu.record.file);
            link.click();
        }
    }
});
Ext.reg('shoplogistic-grid-parser-tasks', shopLogistic.grid.ParserTasks);