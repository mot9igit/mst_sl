shopLogistic.grid.APIRequest = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-store-apirequest-grid';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/store/apirequest/getlist',
            sort: 'id',
            dir: 'desc',
            store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.APIRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.APIRequest, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'method', 'store_id', 'createdon', 'version','request', 'ip', 'file','request_content', 'response', 'response_content', 'status', 'status_name', 'color', 'description', 'actions'];
    },

    getTopBar: function () {
        return [{
            xtype: 'shoplogistic-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
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
                header: _('shoplogistic_apirequest_method'),
                dataIndex: 'method',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_apirequest_createdon'),
                dataIndex: 'createdon',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_apirequest_ip'),
                dataIndex: 'ip',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_apirequest_file'),
                dataIndex: 'file',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_apirequest_status'),
                dataIndex: 'status_name',
                sortable: true,
                renderer: function (val, cell, row) {
                    return shopLogistic.utils.renderBadge(val, cell, row);
                },
                width: 100,
            },
            {
                header: _('shoplogistic_apirequest_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_col_actions'),
                dataIndex: 'actions',
                renderer: shopLogistic.utils.renderActions,
                sortable: false,
                width: 150,
                id: 'actions'
            }
        ];
    },


    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateAPIRequest(grid, e, row);
            },
        };
    },

    downloadAPIRequest: function (btn, e, row) {
        if (typeof (row) != 'undefined') {
            this.menu.record = row.data;
        }
        if (this.menu.record.file) {
            window.open(this.menu.record.file, '_blank');
        }
    }
    ,

    removeAPIRequest: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_apirequest_remove')
                : _('shoplogistic_apirequests_remove'),
            text: ids.length > 1
                ? _('shoplogistic_apirequest_remove_confirm')
                : _('shoplogistic_apirequests_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/store/apirequest/remove',
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

    updateAPIRequest: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        var w = Ext.getCmp('shoplogistic-window-store-apirequest-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-store-apirequest-update',
            class: "textarea_to_ace",
            id: 'shoplogistic-window-store-apirequest-updater',
            record: this.menu.record,
            title: this.menu.record['name'],
            store_id: this.config.baseParams.store_id,
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
Ext.reg('shoplogistic-store-apirequest-grid', shopLogistic.grid.APIRequest);