shopLogistic.grid.CardRequestStatuses = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-card-request-statuses';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/card_request_status/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.CardRequestStatuses.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.CardRequestStatuses, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'description', 'color', 'active', 'has_action', 'has_submit', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_card_request_status_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_card_request_status_color'),
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_card_request_status_create'),
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
        var w = Ext.getCmp('shoplogistic-window-card-request-status-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-card-request-status-create',
            id: 'shoplogistic-window-card-request-status-create',
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

        var w = Ext.getCmp('shoplogistic-window-card-request-status-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-card-request-status-update',
            id: 'shoplogistic-window-card-request-status-updater',
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
Ext.reg('shoplogistic-grid-card-request-statuses', shopLogistic.grid.CardRequestStatuses);