shopLogistic.grid.CardRequest = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-card-request';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/card_request/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.CardRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.CardRequest, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'name', 'url', 'products', 'file', 'remain_id', 'status', 'status_name', 'color', 'date', 'description', 'createdon', 'createdby', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_card_request_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_card_request_description'),
                dataIndex: 'description',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_card_request_url'),
                dataIndex: 'url',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_card_request_remain_id'),
                dataIndex: 'remain_id',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_card_request_status'),
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_card_request_create'),
            handler: this.createRequest,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateRequest(grid, e, row);
            },
        };
    },

    createRequest: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-card-request-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-card-request-create',
            id: 'shoplogistic-window-card-request-create',
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

    updateRequest: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-card-request-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-card-request-update',
            id: 'shoplogistic-window-card-request-updater',
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
Ext.reg('shoplogistic-grid-card-request', shopLogistic.grid.CardRequest);