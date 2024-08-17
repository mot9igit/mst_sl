shopLogistic.grid.ReportTypeFields = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-reporttypefields';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/reportstypefields/getlist',
            sort: 'id',
            dir: 'desc',
            report_id: config.record.id
        },
        stateful: true
    });
    shopLogistic.grid.ReportTypeFields.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ReportTypeFields, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'type', 'field_type', 'key', 'active', 'toplan', 'description', 'createdon', 'createdby', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_stores_docs_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_reporttypefield_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_reporttypefield_field_type'),
                width: 50,
                dataIndex: 'field_type',
                renderer: shopLogistic.utils.renderFieldType
            },
            {
                header: _('shoplogistic_reporttypefield_key'),
                width: 50,
                dataIndex: 'key'
            },
            {
                header: _('shoplogistic_reporttypefield_active'),
                dataIndex: 'active',
                sortable: true,
                width: 100,
                renderer: shopLogistic.utils.renderBoolean
            },
            {
                header: _('shoplogistic_reporttypefield_toplan'),
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_reporttypefield_create'),
            handler: this.createReportTypeField,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateReportTypeField(grid, e, row);
            },
        };
    },

    createReportTypeField: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-report-type-field-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-report-type-field-create',
            id: 'shoplogistic-window-report-type-field-create',
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
            type: this.config.record.id
        });
        w.show(e.target);
    },

    updateReportTypeField: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-report-type-field-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-report-type-field-update',
            id: 'shoplogistic-window-report-type-field-updater',
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

    removeReportTypeField: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_reporttypefields_remove')
                : _('shoplogistic_reporttypefield_remove'),
            text: ids.length > 1
                ? _('shoplogistic_reporttypefields_remove_confirm')
                : _('shoplogistic_reporttypefield_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/reportstypefields/remove',
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
Ext.reg('shoplogistic-grid-reporttypefields', shopLogistic.grid.ReportTypeFields);