shopLogistic.grid.StoreRegistry = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-storeregistry';
    }
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/registry/getlist',
            sort: 'id',
            dir: 'desc',
            store_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.StoreRegistry.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.StoreRegistry, shopLogistic.grid.Default, {
    downloadRegistry: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        var file = this.menu.record.file;
        var num = this.menu.record.num;
        var store = this.menu.record.store_id;
        if(file){
            var link = document.createElement('a');
            link.setAttribute('href', '/'+file);
            link.setAttribute('download','registry_'+num+'_'+store+'.xlsx');
            link.click();
        }
    },
    createStoreRegistry: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-storeregistry-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-storeregistry-create',
            id: 'shoplogistic-window-storeregistry-create',
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
            store_id: this.config.record.object.id
        });
        w.show(e.target);
    },

    removeRegistry: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_storeregistrys_remove')
                : _('shoplogistic_storeregistry_remove'),
            text: ids.length > 1
                ? _('shoplogistic_storeregistrys_remove_confirm')
                : _('shoplogistic_storeregistry_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/registry/remove',
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

    getFields: function () {
        return ['id', 'num', 'store_id', 'file', 'date_from', 'date_to', 'createdon', 'description', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('shoplogistic_storeregistry_id'),
            dataIndex: 'id',
            sortable: true,
            width: 100,
        },{
            header: _('shoplogistic_storeregistry_num'),
            dataIndex: 'num',
            sortable: true,
            width: 100,
        },{
            header: _('shoplogistic_storeregistry_datefrom'),
            dataIndex: 'date_from',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_storeregistry_dateto'),
            dataIndex: 'date_to',
            sortable: true,
            width: 200,
        }, {
            header: _('shoplogistic_grid_actions'),
            dataIndex: 'actions',
            renderer: shopLogistic.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('shoplogistic_storeregistry_create'),
            handler: this.createStoreRegistry,
            scope: this
        }];
    },
});
Ext.reg('shoplogistic-grid-storeregistry', shopLogistic.grid.StoreRegistry);
