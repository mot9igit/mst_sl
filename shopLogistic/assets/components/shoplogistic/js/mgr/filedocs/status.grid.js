shopLogistic.grid.FileDocsStatuses = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-files-docs-statuses';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/docsstatus/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.FileDocsStatuses.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.FileDocsStatuses, shopLogistic.grid.Default, {

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
                header: _('shoplogistic_doc_status_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_doc_status_color'),
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_doc_status_create'),
            handler: this.createDoc,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateDoc(grid, e, row);
            },
        };
    },

    createDoc: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-files-docs-statuses-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-files-docs-statuses-create',
            id: 'shoplogistic-window-files-docs-statuses-create',
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

    updateDoc: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-files-docs-statuses-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-files-docs-statuses-update',
            id: 'shoplogistic-window-files-docs-statuses-updater',
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
Ext.reg('shoplogistic-grid-files-docs-statuses', shopLogistic.grid.FileDocsStatuses);