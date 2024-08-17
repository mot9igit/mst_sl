shopLogistic.grid.Queue = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-queue';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/queue/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.Queue.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Queue, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'action', 'slaction', 'fixed','description', 'createdon', 'startedon', 'finishedon', 'createdby', 'processing', 'processed', 'response', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_queue_action'),
                width: 50,
                dataIndex: 'slaction'
            },
            {
                header: _('shoplogistic_createdon'),
                dataIndex: 'createdon',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_queue_processing'),
                dataIndex: 'processing',
                sortable: true,
                width: 100,
                renderer: shopLogistic.utils.renderBoolean,
            },
            {
                header: _('shoplogistic_queue_processed'),
                dataIndex: 'processed',
                sortable: true,
                width: 100,
                renderer: shopLogistic.utils.renderBoolean,
            },
            {
                header: _('shoplogistic_queue_fixed'),
                dataIndex: 'fixed',
                sortable: true,
                width: 100,
                renderer: shopLogistic.utils.renderBoolean,
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_queue_create'),
            handler: this.createQueue,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateQueue(grid, e, row);
            },
        };
    },

    createQueue: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-queue-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-queue-create',
            id: 'shoplogistic-window-queue-create',
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

    updateQueue: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-queue-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-queue-update',
            id: 'shoplogistic-window-queue-updater',
            record: this.menu.record,
            title: this.menu.record['action'],
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

    removeQueue: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_queues_remove')
                : _('shoplogistic_queue_remove'),
            text: ids.length > 1
                ? _('shoplogistic_queues_remove_confirm')
                : _('shoplogistic_queue_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/queue/remove',
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
Ext.reg('shoplogistic-grid-queue', shopLogistic.grid.Queue);