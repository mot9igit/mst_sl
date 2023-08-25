shopLogistic.grid.ReportType = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-report-type';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/reportstype/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.ReportType.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ReportType, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'toplan', 'active', 'description', 'createdon', 'createdby', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_stores_docs_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_reporttype_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_reporttype_active'),
                dataIndex: 'active',
                sortable: true,
                width: 100,
                renderer: shopLogistic.utils.renderBoolean
            },
            {
                header: _('shoplogistic_reporttype_toplan'),
                dataIndex: 'toplan',
                sortable: true,
                width: 100,
                renderer: shopLogistic.utils.renderBoolean
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_reporttype_create'),
            handler: this.createReportType,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateReportType(grid, e, row);
            },
        };
    },

    createReportType: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-report-type-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-report-type-create',
            id: 'shoplogistic-window-report-type-create',
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

    updateReportType: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-report-type-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-report-type-update',
            id: 'shoplogistic-window-report-type-updater',
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

    removeReportType: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_reporttypes_remove')
                : _('shoplogistic_reporttype_remove'),
            text: ids.length > 1
                ? _('shoplogistic_reporttypes_remove_confirm')
                : _('shoplogistic_reporttype_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/reportstype/remove',
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
Ext.reg('shoplogistic-grid-report-type', shopLogistic.grid.ReportType);