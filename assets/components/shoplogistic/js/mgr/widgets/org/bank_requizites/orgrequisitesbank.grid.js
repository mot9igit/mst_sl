shopLogistic.grid.OrgRequisitesBank = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-orgrequisitesbank';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/org/requisites/bank/getlist',
            sort: 'id',
            dir: 'desc',
            org_requisite_id: config.record.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.OrgRequisitesBank.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.OrgRequisitesBank, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'bank_number', 'org_requisite_id', 'bank_knumber', 'bank_bik', 'bank_name', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_orgrequisitesbank_create'),
            handler: this.createOrgRequisitesBank,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_orgrequisitesbank_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_orgrequisitesbank_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_orgrequisitesbank_bank_name'),
                dataIndex: 'bank_name',
                width: 100
            },
            {
                header: _('shoplogistic_orgrequisitesbank_bank_number'),
                dataIndex: 'bank_number',
                width: 100
            },
            {
                header: _('shoplogistic_orgrequisitesbank_bank_knumber'),
                dataIndex: 'bank_knumber',
                width: 100
            },
            {
                header: _('shoplogistic_orgrequisitesbank_bank_bik'),
                dataIndex: 'bank_bik',
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
                this.updateOrgRequisitesBank(grid, e, row);
            },
        };
    },

    createOrgRequisitesBank: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-orgrequisitesbank-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-orgrequisitesbank-create',
            id: 'shoplogistic-window-orgrequisitesbank-create',
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
            org_requisite_id: this.config.record.id
        });
        w.show(e.target);
    },

    removeOrgRequisitesBank: function () {
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
                action: 'mgr/org/requisites/bank/remove',
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

    disableOrgRequisitesBank: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/requisites/bank/disable',
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

    enableOrgRequisitesBank: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/org/requisites/bank/enable',
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

    updateOrgRequisitesBank: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-orgrequisitesbank-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-orgrequisitesbank-update',
            id: 'shoplogistic-window-orgrequisitesbank-updater',
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
Ext.reg('shoplogistic-grid-orgrequisitesbank', shopLogistic.grid.OrgRequisitesBank);