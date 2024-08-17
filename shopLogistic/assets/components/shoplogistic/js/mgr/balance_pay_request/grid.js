shopLogistic.grid.BalancePayRequest = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-balance-pay-request';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/balance_pay_request/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.BalancePayRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.BalancePayRequest, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'store_name', 'name', 'phone', 'value', 'file', 'status', 'status_name', 'color', 'date', 'description', 'createdon', 'createdby', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_balance_pay_request_store_id'),
                width: 50,
                dataIndex: 'store_name'
            },
            {
                header: _('shoplogistic_balance_pay_request_description'),
                dataIndex: 'description',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_balance_pay_request_value'),
                dataIndex: 'value',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_balance_pay_request_status'),
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_balance_pay_request_create'),
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
        var w = Ext.getCmp('shoplogistic-window-balance-pay-request-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-balance-pay-request-create',
            id: 'shoplogistic-window-balance-pay-request-create',
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

        var w = Ext.getCmp('shoplogistic-window-balance-pay-request-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-balance-pay-request-update',
            id: 'shoplogistic-window-balance-pay-request-updater',
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

    removeRequest: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_balance_pay_requests_remove')
                : _('shoplogistic_balance_pay_request_remove'),
            text: ids.length > 1
                ? _('shoplogistic_balance_pay_requests_remove_confirm')
                : _('shoplogistic_balance_pay_request_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/balance_pay_request/remove',
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
Ext.reg('shoplogistic-grid-balance-pay-request', shopLogistic.grid.BalancePayRequest);