shopLogistic.grid.ParserdataServices = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-parserdata-services';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parserdata/services/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.ParserdataServices.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ParserdataServices, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'description', 'service_key', 'url', 'active', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_parserdata_service_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_parserdata_service_service_key'),
                dataIndex: 'color',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parserdata_service_url'),
                dataIndex: 'url',
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
        return [/*{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_parserdata_service_create'),
            handler: this.createService,
            scope: this
        }*/];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateService(grid, e, row);
            },
        };
    },

    createService: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-parserdata-service-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-parserdata-service-create',
            id: 'shoplogistic-window-parserdata-service-create',
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

    updateService: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-parserdata-service-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-parserdata-service-update',
            id: 'shoplogistic-window-parserdata-service-updater',
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

    removeService: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_parserdata_services_remove')
                : _('shoplogistic_parserdata_service_remove'),
            text: ids.length > 1
                ? _('shoplogistic_parserdata_services_remove_confirm')
                : _('shoplogistic_parserdata_service_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parserdata/services/remove',
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
Ext.reg('shoplogistic-grid-parserdata-services', shopLogistic.grid.ParserdataServices);